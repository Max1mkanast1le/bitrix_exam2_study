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