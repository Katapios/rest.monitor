<?php
use Bitrix\Main\Application;
use Bitrix\Main\Entity\Base;

IncludeModuleLangFile(__FILE__);

class rest_monitor extends CModule
{
    public $MODULE_ID = "rest.monitor";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS = "N";

    public function __construct()
    {
        include(__DIR__ . "/version.php");
        if (is_array($arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
        $this->MODULE_NAME = GetMessage("REST_MONITOR_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("REST_MONITOR_MODULE_DESC");
    }

    public function DoInstall()
    {
        global $APPLICATION;
        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallAgents();
        RegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(GetMessage("REST_MONITOR_INSTALL_TITLE"), __DIR__ . "/step.php");
    }

    public function DoUninstall()
    {
        global $APPLICATION;
        $this->UnInstallAgents();
        $this->UnInstallEvents();
        $this->UnInstallDB();
        UnRegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(GetMessage("REST_MONITOR_UNINSTALL_TITLE"), __DIR__ . "/unstep.php");
    }

    public function InstallDB()
    {
        global $DB;
        if (!$DB->TableExists("b_rest_api_log")) {
            $DB->Query("CREATE TABLE b_rest_api_log (
                ID INT(11) NOT NULL AUTO_INCREMENT,
                TIMESTAMP_X DATETIME NOT NULL,
                METHOD VARCHAR(255) NOT NULL,
                USER_ID INT(11) NULL,
                IP VARCHAR(45) NULL,
                DURATION DECIMAL(12,6) NOT NULL,
                RESULT TEXT NULL,
                SENT CHAR(1) NOT NULL DEFAULT 'N',
                PRIMARY KEY (ID)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }
        return true;
    }

    public function UnInstallDB()
    {
        global $DB;
        $DB->Query("DROP TABLE IF EXISTS b_rest_api_log");
        return true;
    }

    public function InstallEvents()
    {
        RegisterModuleDependences("main", "OnPageStart", $this->MODULE_ID, "\\Rest\\Monitor\\EventHandlers", "onPageStart");
        RegisterModuleDependences("main", "OnEndBufferContent", $this->MODULE_ID, "\\Rest\\Monitor\\EventHandlers", "onEndBufferContent", 10000);
        return true;
    }

    public function UnInstallEvents()
    {
        UnRegisterModuleDependences("main", "OnPageStart", $this->MODULE_ID, "\\Rest\\Monitor\\EventHandlers", "onPageStart");
        UnRegisterModuleDependences("main", "OnEndBufferContent", $this->MODULE_ID, "\\Rest\\Monitor\\EventHandlers", "onEndBufferContent");
        return true;
    }

    public function InstallAgents()
    {
        \CAgent::AddAgent("\\Rest\\Monitor\\LogSender::sendToOpenSearchAgent();", $this->MODULE_ID, "N", 60);
        return true;
    }

    public function UnInstallAgents()
    {
        \CAgent::RemoveModuleAgents($this->MODULE_ID);
        return true;
    }
}
