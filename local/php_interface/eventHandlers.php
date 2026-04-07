<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class MyIBlockEventHandlers
{
    public static $oldAuthorId = null;

    public static function OnBeforeIBlockElementHandler(&$arFields)
    {
        if($arFields["IBLOCK_ID"] == IBLOCK_REVIEWS_ID)
        {
            $previewText = $arFields["PREVIEW_TEXT"];
            $textLength = mb_strlen($previewText);
            
            //проверка длинны текста анонса
            if ($textLength < 5)
            {
                $GLOBALS["APPLICATION"]->throwException("Текст анонса слишком короткий:" . $textLength . ". Минимум должно быть 5 символов.");
                return false;
                
            }
            //проверка наличия #del# в тексте анонса
            if (strpos($previewText, "#del#"))
            {
                $arFields["PREVIEW_TEXT"] = str_replace("#del#", "", $previewText);
            }

            //проверка изменения
            if($arFields["ID"] > 0)
            {
                $res= CIBlockElement::GetProperty(
                    $arFields["IBLOCK_ID"],
                    $arFields["ID"],
                    [],
                    ["CODE" => "author"]
                );
                
                if($row = $res->fetch())
                {
                    self::$oldAuthorId = $row["VALUE"];
                }
            }
        }
    }

    public static function OnAfterIBlockElementHandler($arFields)
    {
        if($arFields["IBLOCK_ID"] == IBLOCK_REVIEWS_ID && $arFields["RESULT"] && self::$oldAuthorId !== null)
        {
            $newAuthorId=null;

            $res = CIBlockElement::GetProperty(
                $arFields["IBLOCK_ID"],
                $arFields["ID"],
                [],
                ["CODE" => "author"] 
            );
            if($row = $res->fetch())
            {
                $newAuthorId = $row["VALUE"];
            }
            if($newAuthorId != self::$oldAuthorId)
            {
                $resOldAuthorName = CUser::GetByID(self::$oldAuthorId);
                if($rowOldAuthorName = $resOldAuthorName->Fetch())
                {
                    $oldAuthorName = $rowOldAuthorName["LOGIN"];
                }

                $resNewAuthorName = CUser::GetByID($newAuthorId);
                if($rowNewAuthorName = $resNewAuthorName->Fetch())
                {
                    $newAuthorName = $rowNewAuthorName["LOGIN"];
                }

                CEventLog::Add([
                    "SEVERITY" => "INFO",
                    "AUDIT_TYPE_ID" => LOG_AUDIT_TYPE_ID,
                    "MODULE_ID" => "main",
                    "ITEM_ID" => $arFields["ID"],
                    "DESCRIPTION" => "В рецензии ID=" . $arFields["ID"] . " изменился автор с " . $oldAuthorName . " на " . $newAuthorName,
                ]);
            }

        }
    }
}

class MyUserEventHandlers
{
    private static $oldUserClass = null;

    public static function OnBeforeUserUpdateHandler(&$arFields)
    {
        if($arFields["ID"] > 0)
        {
            $res = CUser::GetByID($arFields["ID"]);
            if ($row = $res->fetch())
            {
                self::$oldUserClass = $row["UF_USER_CLASS"];
            }
        }
    }

    public static function OnAfterUserUpdateHandler(&$arFields)
    {
        if($arFields["RESULT"] && self::$oldUserClass !== null)
        {
            if(isset($arFields["UF_USER_CLASS"]) && $arFields["UF_USER_CLASS"] != self::$oldUserClass)
            {
                $arEventfields = [
                    "OLD_USER_CLASS" => self::$oldUserClass,
                    "NEW_USER_CLASS" => $arFields["UF_USER_CLASS"],
                ];
                
                CEvent::Send(
                    "EX2_AUTHOR_INFO",
                    SITE_ID,
                    $arEventfields
                );
            }
        }
    }

    public static function OnBeforeEventAddHandler(&$event, &$lid, &$arFields)
    {
        if ($event === "USER_INFO")
        {
            if (isset($arFields["USER_ID"]) && intval($arFields["USER_ID"]) > 0)
            {
                $res = CUser::GetByID($arFields["USER_ID"]);
                if ($row = $res->Fetch())
                {
                    if($row["UF_USER_CLASS"] > 0)
                    {
                        $res = CUserFieldEnum::GetList([], ["ID" => $row["UF_USER_CLASS"]]);
                        if ($row = $res->Fetch())
                        {
                            $arFields["CLASS"] = $row["VALUE"];
                        }
                    }else
                    {
                        $arFields["CLASS"] = "Не назначен";
                    }
                }
            }
        }
    }
}

class MySearchEventHandlers
{
    public static function BeforeIndexHandler($arFields)
    {
        if ($arFields["MODULE_ID"] == "iblock" && $arFields["PARAM2"] == IBLOCK_REVIEWS_ID)
        {
            if (\Bitrix\Main\Loader::includeModule("iblock"))
            {
                $resReview = CIBlockElement::GetProperty(
                    $arFields["PARAM2"],
                    $arFields["ITEM_ID"],
                    [],
                    ["CODE" => "author"]
                );

                if($reviewAuthor = $resReview->Fetch())
                {
                    $userId = $reviewAuthor["VALUE"];
                    
                    if ($userId > 0)
                    {
                        $resUser = CUser::GetByID($userId);
                        if ($userParam = $resUser->Fetch())
                        {
                            $classId = $userParam["UF_USER_CLASS"];
                            
                            if ($userParam["UF_USER_CLASS"] > 0)
                            {
                                $resEnum = CUserFieldEnum::GetList([], ["ID" => $classId]);
                                if ($userClass = $resEnum->Fetch())
                                {
                                    $arFields["TITLE"] .= ". Класс:" . $userClass["VALUE"];
                                }
                            }
                        }
                    }
                }
            }
        }
        return $arFields;
    }
}

class MyAdminMenuhandlers
{
    public static function OnBuildGlobalMenuHandler(&$arGlobalMenu, &$arModuleMenu)
    {
        global $USER;

        $userGroups = $USER->GetUserGroupArray();

        if (in_array(5, $userGroups))
        {
            foreach ($arGlobalMenu as $key => $menu)
            {
                if ($key !== "global_menu_content")
                {
                    unset($arGlobalMenu["$key"]);
                }
            }
            foreach ($arModuleMenu as $key => $item)
            {
                if ($item["parent_menu"] !== "global_menu_content")
                {
                    unset($arModuleMenu[$key]);
                }
            }

            $arGlobalMenu["global_menu_fast_access"] = [
                "menu_id" => "fast_access",
                "text" => "Быстрый доступ",
                "title" => "Быстрый доступ",
                "sort" => 500,
                "item_id" => "global_menu_fast_access",
                "help_section" => "fast_access",
                "items" => [
                    [
                        "text" => "Ссылка 1",
                        "url" => "https://test1",
                        "title" => "перейти на тест 1",
                    ],
                    [
                        "text" => "Ссылка 2",
                        "url" => "https://test2",
                        "title" => "перейти на тест 2",
                    ],
                ],
            ];
        }
    }
}