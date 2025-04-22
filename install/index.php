<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class rest_monitor extends CModule
{
    public $MODULE_ID = "rest.monitor";
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;

    public function __construct()
    {
        include __DIR__ . "/version.php";
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("REST_MONITOR_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("REST_MONITOR_MODULE_DESC");
        $this->PARTNER_NAME = Loc::getMessage("REST_MONITOR_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("REST_MONITOR_PARTNER_URI");
    }

    public function DoInstall()
    {
        global $APPLICATION;
        ModuleManager::registerModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(Loc::getMessage("REST_MONITOR_INSTALL_TITLE"), __DIR__ . "/step.php");
    }

    public function DoUninstall()
    {
        global $APPLICATION;
        $request = \Bitrix\Main\Context::getCurrent()->getRequest();
        $step = (int)$request->get("step");

        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("REST_MONITOR_UNINSTALL_TITLE"),
                __DIR__ . "/uninstall.php"
            );
        } else {
            if ($request->get("delete_entities") === "Y") {
                // Здесь можно добавить удаление данных
            }
            ModuleManager::unRegisterModule($this->MODULE_ID);
        }
    }
}
