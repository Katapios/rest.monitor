<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Diag\Debug;

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

        if (!$GLOBALS['USER']->IsAdmin()) {
            $APPLICATION->ThrowException(Loc::getMessage("REST_MONITOR_ACCESS_DENIED"));
            return false;
        }

            // AJAX-валидация URL
        if ($_REQUEST['ajax_action'] === 'validate_url' && check_bitrix_sessid()) {
            $this->validateUrlAjax();
            return;
        }

        $step = (int)($_REQUEST["step"] ?? 1);
        $errorMessage = '';
        $savedUrl = '';

        try {
            if ($step < 2) {
                // Шаг 1 - форма ввода
                $APPLICATION->IncludeAdminFile(
                    Loc::getMessage("REST_MONITOR_INSTALL_TITLE"),
                    __DIR__ . "/step1.php",
                    [
                        'ERROR' => $errorMessage,
                        'SAVED_URL' => $savedUrl
                    ]
                );
            } else {
                // Шаг 2 - обработка
                $url = trim($_POST['opensearch_url'] ?? '');
                $savedUrl = htmlspecialcharsbx($url);

                // Валидация URL
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    throw new \Exception(Loc::getMessage("REST_MONITOR_INVALID_URL_FORMAT"));
                }

                // URL для проверки (корень OpenSearch)
                $checkUrl = rtrim($url, '/') . '/';

                $ch = curl_init($checkUrl);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                if ($curlError) {
                    throw new \Exception(Loc::getMessage("REST_MONITOR_CONNECTION_ERROR") . ": " . $curlError);
                }

                if ($httpCode >= 400) {
                    throw new \Exception(Loc::getMessage("REST_MONITOR_INVALID_RESPONSE") . " (HTTP $httpCode)");
                }

                // Доп. проверка, что ответ от OpenSearch
                $data = json_decode($response, true);
                if (empty($data['version']['number'])) {
                    throw new \Exception(Loc::getMessage("REST_MONITOR_INVALID_SERVER"));
                }

                // Сохраняем исходный URL с /rest_api_logs/_doc
                $fullUrl = rtrim($url, '/') . '/rest_api_logs/_doc';
                Option::set($this->MODULE_ID, 'OPENSEARCH_URL', $fullUrl);

                // Установка компонентов
                $this->InstallDB();
                $this->InstallEvents();
                $this->InstallAgents();

        if (!ModuleManager::isModuleInstalled($this->MODULE_ID)) {
            ModuleManager::registerModule($this->MODULE_ID);
        }

        // И замените вывод финального шага:
        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("REST_MONITOR_INSTALL_TITLE"),
            __DIR__ . "/step2.php",
            [
                'MODULE_ID' => $this->MODULE_ID // Передаем ID модуля в шаблон
            ]
        );
            }
        } catch (\Exception $e) {
            Debug::writeToFile($e->getMessage(), "Installation Error", "/rest_monitor_debug.log");

            // Возвращаем на шаг 1 с ошибкой
            $step = 1;
            $errorMessage = $e->getMessage();

            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("REST_MONITOR_INSTALL_TITLE"),
                __DIR__ . "/step1.php",
                [
                    'ERROR' => $errorMessage,
                    'SAVED_URL' => $savedUrl
                ]
            );
        }
    }


        public function DoUninstall()
    {
        global $APPLICATION;

        $this->UnInstallAgents();
        $this->UnInstallEvents();
        $this->UnInstallDB();
        $request = \Bitrix\Main\Context::getCurrent()->getRequest();
        $step = (int)$request->get("step");

        $crmStats = [];

        if (\Bitrix\Main\Loader::includeSharewareModule('crm')) {
            try {
                $crmStats = [
                    'DEALS' => \Bitrix\Crm\DealTable::getCount(),
                    'LEADS' => \Bitrix\Crm\LeadTable::getCount(),
                    'CONTACTS' => \Bitrix\Crm\ContactTable::getCount(),
                    'COMPANIES' => \Bitrix\Crm\CompanyTable::getCount(),
                    'PRODUCTS' => \Bitrix\Crm\ProductTable::getCount(),
                ];

                if (defined("REST_MONITOR_DEBUG") && REST_MONITOR_DEBUG === true) {
                    \Bitrix\Main\Diag\Debug::writeToFile($crmStats, "CRM статистика получена", "/rest_monitor_debug.log");
                }
            } catch (\Exception $e) {
                if (defined("REST_MONITOR_DEBUG") && REST_MONITOR_DEBUG === true) {
                    \Bitrix\Main\Diag\Debug::writeToFile($e->getMessage(), "Ошибка при сборе CRM статистики", "/rest_monitor_debug.log");
                }
            }
        }

        if ($step < 2) {
            $_SESSION['REST_MONITOR_CRM_STATS'] = $crmStats;
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("REST_MONITOR_UNINSTALL_TITLE"),
                __DIR__ . "/uninstall.php"
            );
        } else {
            // Проверка: удалять ли CRM-сущности
            $hasEntityFlags = $request->get("delete_deals") === "Y"
                || $request->get("delete_leads") === "Y"
                || $request->get("delete_contacts") === "Y"
                || $request->get("delete_companies") === "Y"
                || $request->get("delete_products") === "Y";

            if ($hasEntityFlags && \Bitrix\Main\Loader::includeModule('crm')) {
                $typesToDelete = [
                    'delete_deals' => ['class' => \Bitrix\Crm\DealTable::class, 'delete' => \CCrmDeal::class],
                    'delete_leads' => ['class' => \Bitrix\Crm\LeadTable::class, 'delete' => \CCrmLead::class],
                    'delete_contacts' => ['class' => \Bitrix\Crm\ContactTable::class, 'delete' => \CCrmContact::class],
                    'delete_companies' => ['class' => \Bitrix\Crm\CompanyTable::class, 'delete' => \CCrmCompany::class],
                    'delete_products' => ['class' => \Bitrix\Crm\ProductTable::class, 'delete' => \CCrmProduct::class],
                ];

                $start = time();
                $limit = 100;

                foreach ($typesToDelete as $requestKey => $handlers) {
                    if ($request->get($requestKey) === "Y") {
                        $className = $handlers['class'];
                        $deleteClass = $handlers['delete'];

                        $res = $className::getList([
                            'select' => ['ID'],
                            'limit' => $limit,
                            'order' => ['ID' => 'ASC'],
                        ]);

                        $counter = 0;
                        while ($item = $res->fetch()) {
                            $entity = new $deleteClass();
                            $result = $entity->Delete($item['ID'], false);
                            $counter++;

                            if (defined("REST_MONITOR_DEBUG") && REST_MONITOR_DEBUG === true) {
                                \Bitrix\Main\Diag\Debug::writeToFile(
                                    "Удалено: {$deleteClass} #{$item['ID']}, результат: " . ($result ? "успешно" : "ошибка"),
                                    "Удаление CRM",
                                    "/rest_monitor_debug.log"
                                );
                            }

                            if ((time() - $start) > 10) {
                                break;
                            }
                        }

                        if ($counter > 0 && defined("REST_MONITOR_DEBUG") && REST_MONITOR_DEBUG === true) {
                            \Bitrix\Main\Diag\Debug::writeToFile(
                                "Успешно удалено {$counter} сущностей типа {$requestKey}",
                                "CRM Удаление завершено",
                                "/rest_monitor_debug.log"
                            );
                        }

                        if ($counter >= $limit) {
                            \Bitrix\Main\Diag\Debug::writeToFile(
                                "Удалено $counter элементов, возможно нужно повторить удаление",
                                "CRM Удаление неполное",
                                "/rest_monitor_debug.log"
                            );
                        }
                    }
                }
            }

            \Bitrix\Main\Config\Option::delete($this->MODULE_ID, ['name' => 'OPENSEARCH_URL']);
            \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
        }
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

    private function validateUrlAjax()
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();
        header('Content-Type: application/json');

        $url = trim($_POST['url'] ?? '');

        try {
            // Повторяем логику валидации из DoInstall
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new \Exception(Loc::getMessage("REST_MONITOR_INVALID_URL_FORMAT"));
            }

            $checkUrl = rtrim($url, '/') . '/';

            $ch = curl_init($checkUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                throw new \Exception(Loc::getMessage("REST_MONITOR_CONNECTION_ERROR") . ": " . $curlError);
            }

            if ($httpCode >= 400) {
                throw new \Exception(Loc::getMessage("REST_MONITOR_INVALID_RESPONSE") . " (HTTP $httpCode)");
            }

            $data = json_decode($response, true);
            if (empty($data['version']['number'])) {
                throw new \Exception(Loc::getMessage("REST_MONITOR_INVALID_SERVER"));
            }

            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }

        die();
    }
}
