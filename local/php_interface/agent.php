<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class MyAgentHandlers
{
    public static function Agent_ex2_610()
    {

        if (!\Bitrix\Main\Loader::includeModule("iblock"))
        {
            return "MyAgentHandlers::Agent_ex2_610();";
        }
        
        $lastRun = \COption::GetOptionString("main", "last_agent_run_ex2_610","");
        $currentRun = ConvertTimeStamp(time(), "FULL");

        $arFilter = [
            "IBLOCK_ID" => IBLOCK_REVIEWS_ID,
            "CHECK_PERMISSIONS" => "N",
        ];

        if ($lastRun)
        {
            $arFilter[">TIMESTAMP_X"] = $lastRun;
        }

        $res = \CIBlockElement::GetList(
            [],
            $arFilter,
            []
        );

        $count = (int)$res;

        $logText = "Запуск агента ex2_610.";
        if ($lastRun)
        {
            $logText .= "С " . $lastRun . "изменилось " . $count . " Рецензий.";
        }
        else 
        {
            $logText .= "Это первый запуск. Изменено всего: " . $count;
        }

        \CEventLog::Add([
            "SEREVITY" => "INFO",
            "AUDIT_TYPE_ID" => "ex2_610",
            "MODULE_ID" => "main",
            "ITEM_ID" => "Agent",
            "DESCRIPTION" => $logText,
        ]);

        \COption::SetOptionString("main", "last_agent_run_ex2_610", $currentRun);

        return "MyAgentHandlers::Agent_ex2_610();";
    }
}