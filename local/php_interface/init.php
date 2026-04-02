<?php 
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (file_exists(__DIR__ . "/const.php"))
{
    require_once(__DIR__ . "/const.php");
}

if (file_exists(__DIR__ . "/eventHandlers.php"))
{
    require_once(__DIR__ . "/eventHandlers.php");
}
$eventManager = \Bitrix\Main\EventManager::getInstance();

$eventManager->AddEventhandler("iblock", "OnBeforeIBlockElementAdd", [
    "MyIBlockEventHandlers", 
    "OnBeforeIBlockElementHandler"
]);
$eventManager->AddEventhandler("iblock", "OnBeforeIBlockElementUpdate", [
    "MyIBlockEventHandlers", 
    "OnBeforeIBlockElementHandler"
]);
$eventManager->AddEventhandler("iblock", "OnAfterIBlockElementUpdate", [
    "MyIBlockEventHAndlers",
    "OnAfterIBlockElementHandler"
]);