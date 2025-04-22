<?php
use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
    "rest.monitor",
    [
        "RestMonitor\\Module" => "lib/module.php",
    ]
);
