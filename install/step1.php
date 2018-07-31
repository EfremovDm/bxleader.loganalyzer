<?
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

if (!check_bitrix_sessid()) {
    return;
}

global $APPLICATION;
use \Bitrix\Main\Localization\Loc,
    \BxLeader\LogAnalyzer\Utils\MainHelper,
    \BxLeader\LogAnalyzer\Utils\BxHelper;

// notice & error
echo BxHelper::showNotice($GLOBALS['arNote']);

if (!empty($GLOBALS['arError'])) {
    $arMess = array(
        'TYPE'    => 'ERROR',
        'MESSAGE' => Loc::getMessage('MOD_INST_ERR'),
        'DETAILS' => join('<br />', $GLOBALS['arError']),
        'HTML'    => true
    );
    $obAdminMessage = new \CAdminMessage($arMess);
    $obAdminMessage->ShowMessage($arMess);
}

// main
$arOptions       = BxHelper::getModuleOptions($GLOBALS['sBmLaModuleId']);
$arFileProviders = MainHelper::getFileProviders();
$sTimewebLog     = MainHelper::getTimewebLog();
$arBxLogFiles    = BxHelper::getBitrixLogFiles();
$arBxFiles       = array();
foreach ($arBxLogFiles as $k => $arFile) {
    $arBxFiles[$k] = $arFile['view'];
}

$arTech = array();
$arTmp = MainHelper::getTech();
foreach ($arTmp as $sTech) {
    $arTech[$sTech] = '';
}

if (!empty($sTimewebLog)) {
    $arTech['apache'] = $arTech['nginx'] = $sTimewebLog;
}
elseif (MainHelper::isWindows()) { // для windows автоматически можно получить только путь до логов php
    $arPath = explode('/', ini_get('error_log'));
    array_pop($arPath);
    $arTech['php'] = !empty($arPath) ? join('/', $arPath) : '';
}
else {
    $arTech['apache']  = '/var/log/httpd';
    $arTech['nginx']   = '/var/log/nginx';
    $arTech['mysql']   = '/var/log/mysql';
    $arTech['php']     = '/var/log/php';
    $arTech['cron']    = '/var/log';
    $arTech['mail']    = '/var/log';
}

foreach ($arTech as $sTech => $sPath) {
    if (!is_dir($sPath)) {
        $arTech[$sTech] = '';
    }
}

$arSiteTabs = array(
    array('DIV' => 'opt_log_0', 'TAB' => Loc::getMessage('BM_LA_SETTINGS_MAIN'), 'TITLE' => ''),
    array('DIV' => 'opt_log_1', 'TAB' => Loc::getMessage('BM_LA_SETTINGS_DOCUMENTATION'), 'TITLE' => '')
);

$arSiteTabControl = new CAdminViewTabControl('logTabControl', $arSiteTabs);

?>
<form action="<?= $APPLICATION->GetCurPage()?>" method="post">

    <?=bitrix_sessid_post()?>
    <input type="hidden" name="lang" value="<?= LANG ?>" />
    <input type="hidden" name="id" value="<?= $GLOBALS["sBmLaModuleId"] ?>" />
    <input type="hidden" name="install" value="Y" />
    <input type="hidden" name="step" value="2" />

    <?
    $arSiteTabControl->Begin();
    $arSiteTabControl->BeginNextTab();
    ?>
    <table class="list-table">

        <col width="30%" />
        <col width="70%" />

        <!-- Main Settings -->
        <tr class="heading">
            <td colspan="2"><?=Loc::getMessage('BM_LA_COMMON');?></td>
        </tr>
        <tr>
            <td><?=Loc::getMessage('BM_LA_FILE_PROVIDER');?>:</td>
            <td>
                <select name="BM_LA_FIELDS[main_file_provider]">
                    <? foreach ($arFileProviders as $sName => $bAccess): ?>
                        <option value="<?=$sName?>"<?if (
                            empty($_REQUEST['step']) && $sName == $arOptions['main_file_provider']
                                || 2 == $_REQUEST['step'] && $sName == $_REQUEST['BM_LA_FIELDS']['main_file_provider']
                        ): ?> selected="selected"<? endif;?><? if (!$bAccess): ?> disabled="disabled" <?endif;
                        ?>><?=Loc::getMessage('BM_LA_FILE_PROVIDER_' . strtoupper($sName));?></option>
                    <? endforeach; ?>
                </select>
            </td>
        </tr>
        <? if ($arFileProviders['linux']): ?>
            <tr>
                <td><?=Loc::getMessage('BM_LA_SHOW_QUERIES');?>:</td>
                <td><input type="checkbox" name="BM_LA_FIELDS[main_show_queries]" value="Y" <?if (
                        empty($_REQUEST['step'])  && 'Y' == $arOptions['main_show_queries']
                        || 2 == $_REQUEST['step'] && 'Y' == $_REQUEST['BM_LA_FIELDS']['main_show_queries']
                ): ?>checked="checked" <? endif;?> /></td>
            </tr>
        <? endif; ?>

        <!-- Tech Settings -->
        <? foreach ($arTech as $sName => $sDefaultPath):
            $bCheck = (empty($_REQUEST['step']) && 'Y' == $arOptions[$sName.'_log_view']
                        || 2 == $_REQUEST['step'] && 'Y' == $_REQUEST['BM_LA_FIELDS'][$sName.'_log_view']
                        || in_array($sName, array('apache', 'nginx', 'mysql')) && is_dir($sDefaultPath) // default tech
                        ) && !('bitrix' == $sName && empty($arBxFiles))
            ;
            ?>
            <tr class="heading">
                <td colspan="2"><?=Loc::getMessage('BM_LA_' . strtoupper('mysql' == $sName ? $GLOBALS["sDbFork"] : $sName));?></td>
            </tr>

            <tr>
                <td><?=Loc::getMessage('BM_LA_ENABLE');?>:</td>
                <td><input type="checkbox" name="BM_LA_FIELDS[<?=$sName?>_log_view]" value="Y"
                        onclick="ChangeInstallPublic(this.checked, '<?=CUtil::JSEscape($sName)?>')"
                        <?if ($bCheck): ?>
                            checked="checked"
                        <? endif;?>
                        <? if ('bitrix' == $sName && empty($arBxFiles)): ?>
                            disabled="disabled"
                        <? endif; ?>
                    /></td>
            </tr>
            <? if ('bitrix' == $sName):

                $arBxMess =  BxHelper::getBitrixLogTypes($arBxFiles);
                ?>
                <tr>
                    <td><?=Loc::getMessage('BM_LA_LOG_BITRIX');?>:</td>
                    <td>
                        <? if (!empty($arBxFiles)): ?>
                            <?=join('<br />', $arBxMess)?>
                        <? endif; ?>
                    </td>
                </tr>
            <? else: ?>
                <tr>
                    <td><?=Loc::getMessage('BM_LA_LOG_DIR');?>:</td>
                    <td><input type="text" name="BM_LA_FIELDS[<?=$sName?>_log_path]" value="<?=isset($_REQUEST['BM_LA_FIELDS'][$sName.'_log_path'])
                            ? $_REQUEST['BM_LA_FIELDS'][$sName.'_log_path'] : ($arOptions[$sName.'_log_path'] ? : $sDefaultPath)?>"
                            <?if (!$bCheck): ?> disabled="disabled"<? endif;?>
                            style="width: 40%" id="public_path_<?=$sName?>" /></td>
                </tr>
                <? if (in_array($sName, array('cron', 'mail'))): ?>
                    <tr>
                        <td><?=Loc::getMessage('BM_LA_LOG_PREFIX');?>:</td>
                        <td><input type="text" name="BM_LA_FIELDS[<?=$sName?>_log_prefix]" value="<?=isset($_REQUEST['BM_LA_FIELDS'][$sName.'_log_prefix'])
                                ? $_REQUEST['BM_LA_FIELDS'][$sName.'_log_prefix'] : ($arOptions[$sName.'_log_prefix'] ? : ($sName == 'cron' ? 'cron' : 'maillog'))?>"
                                <?if (!$bCheck): ?> disabled="disabled"<? endif;?>
                                   style="width: 40%" id="public_prefix_<?=$sName?>" /></td>
                    </tr>
                <? endif; ?>
                <tr>
                    <td><?=Loc::getMessage('BM_LA_LOG_DIR_RECURSIVE');?>:</td>
                    <td><input type="checkbox" name="BM_LA_FIELDS[<?=$sName?>_log_recursive]" value="Y" <?if (
                                empty($_REQUEST['step']) && 'Y' == $arOptions[$sName.'_log_recursive']
                                || 2 == $_REQUEST['step'] && 'Y' == $_REQUEST['BM_LA_FIELDS'][$sName.'_log_recursive']
                            ): ?> checked="checked"<? endif;?>
                            <?if (!$bCheck): ?> disabled="disabled"<? endif;?>
                            id="public_recursive_<?=$sName?>"
                        /></td>
                </tr>
            <? endif; ?>
        <? endforeach; ?>

    </table><br />

    <?$arSiteTabControl->BeginNextTab();?>

    <?=MainHelper::getReadMeHtml(__DIR__.'/..');?>

    <? $arSiteTabControl->End();?><br />

    <input type="submit" name="inst" value="<?= Loc::getMessage('BM_LA_MODULE_INSTALL_INSTALL')?>" />

    <script language="JavaScript">
        function ChangeInstallPublic(val, lan) {
            var name1 = document.getElementById('public_path_'+lan);
            var name2 = document.getElementById('public_recursive_'+lan);
            var name3 = document.getElementById('public_prefix_'+lan);

            if (name1 != null)
                name1.disabled = !val;
            if (name2 != null)
                name2.disabled = !val;
            if (name3 != null)
                name3.disabled = !val;
        }
    </script>
</form>