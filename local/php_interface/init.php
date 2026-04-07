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

if (file_exists(__DIR__ . "/agent.php"))
{
    require_once(__DIR__ . "/agent.php");
}

$eventManager = \Bitrix\Main\EventManager::getInstance();

//ex2-590
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

//ex2-600
$eventManager->AddEventhandler("main","OnBeforeUserUpdate", [
    "MyUserEventHandlers",
    "OnBeforeUserUpdateHandler"
]);
$eventManager->AddEventhandler("main","OnAfterUserUpdate", [
    "MyUserEventHandlers",
    "OnAfterUserUpdateHandler"
]);

//ex2_610
$resAgent = Cagent::GetList(
    ["ID" => "DESC"],
    ["NAME" => "MyAgentHandlers::Agent_ex2_610();"]
);
if(!$resAgent->Fetch())
{
    CAgent::AddAgent(
        "MyAgentHandlers::Agent_ex2_610();",
        "main",
        'N',
        86400,
        "",
        "Y",
        ConvertTimeStamp(time() + 86400, "FULL"),
        10
    );
}

//ex2-620
$eventManager->AddEventhandler("main", "OnBeforeEventAdd", [
    "MyUserEventHandlers",
    "OnBeforeEventAddHandler"
]);


// Перехват функции mail и запись в лог-файл
if (!function_exists('custom_mail')) {
    function custom_mail($to, $subject, $message, $additional_headers) {
        $log = "\n" . str_repeat("-", 20) . "\n";
        $log .= "Кому: $to\nТема: $subject\nСообщение: $message\nЗаголовки: $additional_headers\n";
        file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/mail_debug.log", $log, FILE_APPEND);
        return true;
    }
}