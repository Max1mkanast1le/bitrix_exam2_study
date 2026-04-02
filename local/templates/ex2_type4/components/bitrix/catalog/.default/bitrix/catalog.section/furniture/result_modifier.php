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
$filter = ["ACTIVE" => "Y", "UF_AUTHOR_STATUS" => STATUS_PUBLISHED_ID, "GROUPS_ID" => [GROUP_AUTHORS_ID]];

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
            "IBlock_ID" => IBLOCK_REVIEWS_ID,
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

