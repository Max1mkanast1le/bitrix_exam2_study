<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$itemIDs = [];
foreach ($arResult["ITEMS"] as $key => $arItem)
{
    $itemIDs[] = $arItem["ID"];
}
$authorIDs = [];
$sortBy = "id";
$sortOrder = "asc";
$arParams["FIELDS"] = ["ID"];
$filter = ["ACTIVE" => "Y", "UF_AUTHOR_STATUS" => "35", "GROUPS_ID" => [6]];

$res = CUser::GetList(
    $sortBy,
    $sortOrder,
    $filter,
    $arParams
);
while ($row = $res->GetNext())
{
    $authorIDs[] = $row["ID"];
}

if( (count($itemIDs) > 0) && (count($authorIDs) > 0) )
{
    $arResult["EXTRA"] = [];
    $res = CIBlockElement::GetList(
        ["ID" => "ASC"],
        [
            "IBlock_ID" => "5",
            "ACTIVE" => "Y",
            "PROPERTY_product" => $itemIDs,
            "PROPERTY_author" => $authorIDs,
        ],
        false,
        false,
        [
            "ID",
            "NAME",
            "PROPERTY_PRODUCT",
        ]
    );
    while ($row = $res->GetNext())
    {
        $arResult["EXTRA"][$row["PROPERTY_PRODUCT_VALUE"]][] = $row;
    }

    $arResult["COUNT_REVIEWS"] = count($arResult["EXTRA"]);
    $this->__component->SetResultCacheKeys(['COUNT_REVIEWS']);

    
}


/*foreach ($arResult["ITEMS"] as $key => $arItem) 
{
    $arResult["ITEMS"][$key]["EXTRA"] = [];
    $res = CIBlockElement::GetList(
        ["ID" => "ASC"],
        [
            "IBlock_ID" => "5",
            "ACTIVE" => "Y",
            "PROPERTY_product" => $arItem["ID"]
        ],
        false,
        false,
        [
            "ID",
            "IBLOCK_ID",
            "NAME",
            "PROPERTY_author"
        ]
    );
    while ($row = $res->GetNext())
    {
        $rsUser = CUser::GetByID($row['PROPERTY_AUTHOR_VALUE']);
        while ($arUser = $rsUser->GetNext())
        {
            $userGroups = CUser::GetUserGroup($arUser["ID"]);
            if(
                $arUser["ACTIVE"] == "Y" && 
                $arUser["UF_AUTHOR_STATUS"] == "35" &&
                in_array(6, $userGroups)
            )
            {
                $arResult["ITEMS"][$key]["EXTRA"][] = $row;
            }
        }

    }
}*/
