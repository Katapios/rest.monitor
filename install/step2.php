<?php
use Bitrix\Main\Localization\Loc;

/** @var array $arParams */
$moduleId = $arParams['MODULE_ID'] ?? $this->MODULE_ID ?? 'rest.monitor'; // Добавляем fallback
?>

<style>
.install-links {
    margin: 25px 0;
}
.install-links a {
    display: inline-block;
    padding: 12px 20px;
    background: #00a2e0;
    color: #fff !important;
    border-radius: 3px;
    text-decoration: none;
    margin-right: 15px;
}
.install-links a:hover {
    background: #008fc7;
}
</style>

<h2><?= Loc::getMessage("REST_MONITOR_INSTALL_COMPLETE") ?></h2>
<p><?= Loc::getMessage("REST_MONITOR_INSTALL_COMPLETE_DESC") ?></p>

<div style="margin: 20px 0; border-top: 1px solid #ddd; padding-top: 15px;">
    <a 
        href="/bitrix/admin/partner_modules.php?lang=<?= LANG ?>" 
        class="adm-btn adm-btn-save"
    >
        <?= Loc::getMessage("REST_MONITOR_BACK_TO_MODULES") ?>
    </a>
</div>