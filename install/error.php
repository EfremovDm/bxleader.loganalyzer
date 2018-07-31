<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

if (!check_bitrix_sessid()) {
    return;
}

global $APPLICATION;

$arMess = array('MESSAGE' => $GLOBALS['arError'], 'TYPE' =>'ERROR');
$obAdminMessage = new \CAdminMessage($arMess);
$obAdminMessage->ShowMessage($arMess);

?>
<form action="<?= $APPLICATION->GetCurPage(); ?>">
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="submit" name="" value="<?= GetMessage('BM_LA_MODULE_INSTALL_BACK'); ?>">
</form>
