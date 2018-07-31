<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
$path = \Bitrix\Main\Loader::getLocal('modules/bxleader.loganalyzer/admin/bxleader_loganalyzer_list_yii.php');
if(file_exists($path)) {
    include $path;
} else {
    $arPath = pathinfo(__FILE__);
    ShowMessage($arPath['basename'] . ' not found!');
}
