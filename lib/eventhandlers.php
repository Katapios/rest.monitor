<?php
namespace Rest\Monitor;

use Bitrix\Main\Context;
use Bitrix\Main\Web\Json;

class EventHandlers
{
    public static function onPageStart()
    {
        $request = Context::getCurrent()->getRequest();
        $uri = $request->getRequestUri();
        if (stripos($uri, "/rest/") !== false) {
            $GLOBALS["REST_MONITOR_START_TIME"] = microtime(true);
        }
    }

    public static function onEndBufferContent(&$content)
    {
        if (!isset($GLOBALS["REST_MONITOR_START_TIME"])) return;

        $duration = microtime(true) - $GLOBALS["REST_MONITOR_START_TIME"];
        $request = Context::getCurrent()->getRequest();
        $method = preg_replace('/\.(json|xml|php)$/i', '', basename($request->getRequestedPage()));
        $method = $request->get("method") ?: $method;

        global $USER;
        $userId = is_object($USER) ? (int)$USER->GetID() : 0;
        $ip = $request->getRemoteAddress();

        $result = "success";
        try {
            $data = Json::decode($content);
            if (isset($data["error"])) {
                $result = "error - " . $data["error"];
                if (isset($data["error_description"])) {
                    $result .= ": " . $data["error_description"];
                }
            }
        } catch (\Exception $e) {}

        LogTable::add([
            "TIMESTAMP_X" => new \Bitrix\Main\Type\DateTime(),
            "METHOD" => $method,
            "USER_ID" => $userId,
            "IP" => $ip,
            "DURATION" => $duration,
            "RESULT" => $result,
            "SENT" => "N"
        ]);
    }
}
