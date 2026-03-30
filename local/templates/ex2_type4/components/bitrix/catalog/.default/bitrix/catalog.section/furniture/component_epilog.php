<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;

if(!empty($arResult["COUNT_REVIEWS"]))
{
    $metaPropertyValue = $APPLICATION->GetDirProperty("ex2_meta");

    if ($metaPropertyValue && strpos($metaPropertyValue, "#count#") !== false) {
        
        $fixedMeta = str_replace("#count#", $arResult["COUNT_REVIEWS"], $metaPropertyValue);
        
        $APPLICATION->SetPageProperty("ex2_meta", $fixedMeta);
        
    }
}