<?php
Bitrix\Main\Loader::registerAutoLoadClasses('rest.monitor', array(
    'Rest\\Monitor\\EventHandlers' => 'lib/eventhandlers.php',
    'Rest\\Monitor\\LogTable'      => 'lib/logtable.php',
    'Rest\\Monitor\\LogSender'     => 'lib/logsender.php'
));