<?php
use Bitrix\Main\Localization\Loc;

$CRM_STATS = $_SESSION['REST_MONITOR_CRM_STATS'] ?? null;

if (defined("REST_MONITOR_DEBUG") && REST_MONITOR_DEBUG === true) {
    \Bitrix\Main\Diag\Debug::writeToFile($CRM_STATS ?? 'CRM_STATS из SESSION не определен', "uninstall.php вызван", "/rest_monitor_debug.log");
}


if (isset($GLOBALS["arModuleParams"]) && is_array($GLOBALS["arModuleParams"])) {
    extract($GLOBALS["arModuleParams"], EXTR_OVERWRITE);
}

if (defined("REST_MONITOR_DEBUG") && REST_MONITOR_DEBUG === true) {
    \Bitrix\Main\Diag\Debug::writeToFile($CRM_STATS ?? 'Переменная CRM_STATS не определена', "uninstall.php вызван", "/rest_monitor_debug.log");
}
?>

<p><?= Loc::getMessage("REST_MONITOR_UNINSTALL_WARNING"); ?></p>

<?php if (!empty($CRM_STATS) && isset($CRM_STATS['DEALS'])): ?>
    <h3><?= Loc::getMessage("REST_MONITOR_CRM_STATS_TITLE"); ?></h3>
    <ul>
        <li><?= Loc::getMessage("REST_MONITOR_ENTITY_DEALS") ?>: <?= $CRM_STATS['DEALS'] ?></li>
        <li><?= Loc::getMessage("REST_MONITOR_ENTITY_LEADS") ?>: <?= $CRM_STATS['LEADS'] ?></li>
        <li><?= Loc::getMessage("REST_MONITOR_ENTITY_CONTACTS") ?>: <?= $CRM_STATS['CONTACTS'] ?></li>
        <li><?= Loc::getMessage("REST_MONITOR_ENTITY_COMPANIES") ?>: <?= $CRM_STATS['COMPANIES'] ?></li>
        <li><?= Loc::getMessage("REST_MONITOR_ENTITY_PRODUCTS") ?>: <?= $CRM_STATS['PRODUCTS'] ?></li>
    </ul>
<?php else: ?>
    <p><?= Loc::getMessage("REST_MONITOR_CRM_STATS_UNAVAILABLE"); ?></p>
<?php endif; ?>

<form action="<?= $APPLICATION->GetCurPage(); ?>" method="get">
    <?= bitrix_sessid_post(); ?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="rest.monitor">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">

    <fieldset style="border: 1px solid #ccc; padding: 10px;">
        <legend><?= Loc::getMessage("REST_MONITOR_DELETE_ENTITIES") ?></legend>
        <label><input type="checkbox" name="delete_deals" value="Y"> <?= Loc::getMessage("REST_MONITOR_ENTITY_DEALS") ?></label><br>
        <label><input type="checkbox" name="delete_leads" value="Y"> <?= Loc::getMessage("REST_MONITOR_ENTITY_LEADS") ?></label><br>
        <label><input type="checkbox" name="delete_contacts" value="Y"> <?= Loc::getMessage("REST_MONITOR_ENTITY_CONTACTS") ?></label><br>
        <label><input type="checkbox" name="delete_companies" value="Y"> <?= Loc::getMessage("REST_MONITOR_ENTITY_COMPANIES") ?></label><br>
        <label><input type="checkbox" name="delete_products" value="Y"> <?= Loc::getMessage("REST_MONITOR_ENTITY_PRODUCTS") ?></label><br>
    </fieldset>
    <br>
    <input type="submit" name="uninstall_button" value="<?= Loc::getMessage("MOD_UNINSTALL"); ?>">
</form>
