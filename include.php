<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
    "rest.monitor",
    [
        "RestMonitor\\Module" => "lib/module.php",
        'Rest\\Monitor\\EventHandlers' => 'lib/eventhandlers.php',
        'Rest\\Monitor\\LogTable'      => 'lib/logtable.php',
        'Rest\\Monitor\\LogSender'     => 'lib/logsender.php'
    ]
);
