<?php

use Bitrix\Main\Localization\Loc;
?>
<p><?php echo Loc::getMessage("REST_MONITOR_UNINSTALL_WARNING"); ?></p>
<ul>
    <li><?php echo Loc::getMessage("REST_MONITOR_ENTITY_DEALS"); ?></li>
    <li><?php echo Loc::getMessage("REST_MONITOR_ENTITY_CONTACTS"); ?></li>
</ul>
<form action="<?php echo $APPLICATION->GetCurPage(); ?>" method="get">
    <?php echo bitrix_sessid_post(); ?>
    <input type="hidden" name="lang" value="<?php echo LANG; ?>">
    <input type="hidden" name="id" value="rest.monitor">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <label><input type="checkbox" name="delete_entities" value="Y"> <?php echo Loc::getMessage("REST_MONITOR_DELETE_ENTITIES"); ?></label>
    <br><br>
    <input type="submit" value="<?php echo Loc::getMessage("MOD_UNINSTALL"); ?>">
</form>