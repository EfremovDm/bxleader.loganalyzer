<?php defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Config\Option,
    \Bitrix\Main\Type\DateTime,
    \BxLeader\LogAnalyzer\Parser\Parser,
    \BxLeader\LogAnalyzer\Provider\DataProvider,
    \BxLeader\LogAnalyzer\Utils\AdminListHelper,
    \BxLeader\LogAnalyzer\Utils\MainHelper;

require_once(__DIR__ . '/prolog.php');

$obAdminListHelper = new AdminListHelper();
$obAdminListHelper->setTitle(Loc::getMessage('BM_LA_MAIL_TITLE'));

//  получение настроек из модуля

$sFileProvider = Option::get(ADMIN_MODULE_NAME, 'main_file_provider');
$bShowQueries  = Option::get(ADMIN_MODULE_NAME, 'main_show_queries') == 'Y';
$sDirectory    = Option::get(ADMIN_MODULE_NAME, 'mail_log_path');
$bRecursive    = Option::get(ADMIN_MODULE_NAME, 'mail_log_recursive') == 'Y';
$sFilePrefix   = Option::get(ADMIN_MODULE_NAME, 'mail_log_prefix');

// дата-провайдер

$obDataProvider = new DataProvider($sFileProvider, $bShowQueries);
$obDataProvider->setLogSettings('mail', $sDirectory, $bRecursive, $sFilePrefix);
$arFiles = $obDataProvider->getFiles();

$obAdminListHelper->setFilter(array(
    'log'           => array('TITLE' => Loc::getMessage('BM_LA_FILTER_SELECT_LOGFILE'), 'TYPE' => 'select', 'VARIANTS' => $arFiles),
    'other_include' => array('TITLE' => Loc::getMessage('BM_LA_FILTER_INCLUDE')),
    'other_exclude' => array('TITLE' => Loc::getMessage('BM_LA_FILTER_EXCLUDE')),
));

$obDataProvider->setFilter($obAdminListHelper->makeFilter());
$obDataProvider->setOrder($obAdminListHelper->makeOrder($by, $order));
$obDataProvider->setPageSize($obAdminListHelper->makePageSize());
$obDataProvider->setPageNumber($obAdminListHelper->makePageNumber());
$obDataProvider->getData();

// вывод результатов

$obAdminListHelper->setNotice($obDataProvider->getNotice());
$obAdminListHelper->setError($obDataProvider->getError());
$obAdminListHelper->setHeaders($obDataProvider->getStrings());
$obAdminListHelper->setList($obDataProvider->getStrings(), $obDataProvider->getLinesCount(), array(
    'N' => function($val, $arRec) {
        return '<nobr>' . number_format($val, 0, '', ' ') . '</nobr>';
    },
    'DATE' => function($val, $arRec) {
        $sResult = '';
        if (!empty($val)) {
            $obDateTime = new DateTime($val, Parser::DATETIME_FORMAT);
            $sResult = $obDateTime->toString();
        }
        return $sResult;
    },
    'MESSAGE' => function($val, $arRec) {
        return MainHelper::prepareMessage($val);
    },
));
$obAdminListHelper->output();
