<?php

use Bitrix\Main\Localization\Loc;

/** @var array $arParams */
$errorMessage = $arParams['ERROR'] ?? '';
$savedUrl = $arParams['SAVED_URL'] ?? 'http://localhost:9200';
?>

<?php if ($errorMessage): ?>
    <?php CAdminMessage::ShowMessage([
        'TYPE' => 'ERROR',
        'MESSAGE' => Loc::getMessage("REST_MONITOR_INSTALL_ERROR"),
        'DETAILS' => $errorMessage,
        'HTML' => true
    ]); ?>
<?php endif; ?>

<form action="<?= $APPLICATION->GetCurPage() ?>" method="post">
    <?= bitrix_sessid_post() ?>

    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="rest.monitor">
    <input type="hidden" name="install" value="Y">
    <input type="hidden" name="step" value="2">

    <div style="max-width: 600px; margin: 20px 0;">
        <div style="margin-bottom: 15px;">
            <label for="opensearch_url" style="display: block; margin-bottom: 5px;">
                <?= Loc::getMessage("REST_MONITOR_OPENSEARCH_URL") ?>:
            </label>
            <input
                type="text"
                name="opensearch_url"
                id="opensearch_url"
                value="<?= htmlspecialcharsbx($savedUrl) ?>"
                placeholder="http://localhost:9200"
                style="width: 100%; padding: 8px;">
            <div style="margin-top: 5px; font-size: 0.9em; color: #666;">
                <?= Loc::getMessage("REST_MONITOR_URL_HINT") ?>
            </div>
        </div>

        <input
            type="submit"
            value="<?= Loc::getMessage("MOD_INSTALL") ?>"
            class="adm-btn adm-btn-save">
    </div>
</form>