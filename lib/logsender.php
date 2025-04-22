<?php
namespace Rest\Monitor;

use Bitrix\Main\Loader;

class LogSender
{
    public static function sendToOpenSearchAgent()
    {
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
            $ch = curl_init('http://opensearch:9200/rest_api_logs/_doc');
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
