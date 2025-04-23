<?php

namespace Rest\Monitor;

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Diag\Debug;

class LogSender
{
    public static function sendToOpenSearchAgent()
    {

        $opensearchUrl = Option::get('rest.monitor', 'OPENSEARCH_URL');
        Debug::writeToFile("Забрали из OPTIONS url, переданный при установке модуля: $opensearchUrl", "Зашли в агент битрикс", "/rest_monitor_debug.log");

        if (!Loader::includeModule('rest.monitor')) return "";

        $list = LogTable::getList(['filter' => ['SENT' => 'N']]);
        foreach ($list as $row) {
            $data = [
                'timestamp' => $row['TIMESTAMP_X']->format('c'),
                'method' => $row['METHOD'],
                'user_id' => (int)$row['USER_ID'],
                'ip' => $row['IP'],
                'duration' => round((float)$row['DURATION'], 6),
                'result' => $row['RESULT']
            ];
            $json = json_encode($data);
            $ch = curl_init($opensearchUrl);
            Debug::writeToFile("Постучались в Opensearch по URL: $opensearchUrl", "Сделали действие", "/rest_monitor_debug.log");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_exec($ch);
            curl_close($ch);
            LogTable::update($row['ID'], ['SENT' => 'Y']);
        }

        return "\\Rest\\Monitor\\LogSender::sendToOpenSearchAgent();";
    }
}
