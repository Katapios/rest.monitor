<?php
use Bitrix\Main\Localization\Loc;
?>
<form action="<?php echo $APPLICATION->GetCurPage(); ?>" method="get">
    <?php echo bitrix_sessid_post(); ?>
    <input type="hidden" name="lang" value="<?php echo LANG; ?>">
    <input type="hidden" name="id" value="rest.monitor">
    <input type="hidden" name="install" value="Y">
    <input type="hidden" name="step" value="2">
    <label for="opensearch_url"><?php echo Loc::getMessage("REST_MONITOR_OPENSEARCH_URL"); ?>:</label>
    <input type="text" name="opensearch_url" id="opensearch_url" size="50" value="http://localhost:9200">
    <br><br>
    <input type="submit" value="<?php echo Loc::getMessage("MOD_INSTALL"); ?>">
</form>
