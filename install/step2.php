<?php

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

global $APPLICATION;

use \Bitrix\Main\Localization\Loc,
    \BxLeader\LogAnalyzer\Utils\BxHelper;

if (!check_bitrix_sessid()) {
    return;
}

$arMess = array('MESSAGE' => Loc::getMessage('BM_LA_INSTALL_SUCCESS'), 'TYPE' => 'OK');
$obAdminMessage = new \CAdminMessage($arMess);
$obAdminMessage->ShowMessage($arMess);

echo BxHelper::showNotice(array(str_replace(
    '#EMAIL#', Loc::getMessage('BM_LA_DEVELOPER_EMAIL'), Loc::getMessage('BM_LA_INSTALL_LAST_MSG'))
));

?>

<form action="<?= $APPLICATION->GetCurPage(); ?>">
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="submit" name="" value="<?= Loc::getMessage('BM_LA_MODULE_INSTALL_BACK'); ?>">
</form>
