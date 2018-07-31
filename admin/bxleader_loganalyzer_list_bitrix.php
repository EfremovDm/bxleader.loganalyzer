<?php defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Config\Option,
    \Bitrix\Main\Type\DateTime,
    \BxLeader\LogAnalyzer\Parser\Parser,
    \BxLeader\LogAnalyzer\Provider\DataProvider,
    \BxLeader\LogAnalyzer\Utils\AdminListHelper,
    \BxLeader\LogAnalyzer\Utils\MainHelper,
    \BxLeader\LogAnalyzer\Utils\SqlFormatter;

require_once(__DIR__ . '/prolog.php');

/**
 * Документация:
 * https://dev.1c-bitrix.ru/api_help/main/functions/debug/addmessage2log.php
 * https://dev.1c-bitrix.ru/learning/course/?COURSE_ID=43&LESSON_ID=2795
 * https://dev.1c-bitrix.ru/api_help/main/general/magic_vars.php#dbdebugtofile
 */

$obAdminListHelper = new AdminListHelper();
$obAdminListHelper->setTitle(Loc::getMessage('BM_LA_BITRIX_TITLE'));

//  получение настроек из модуля

$sFileProvider = Option::get(ADMIN_MODULE_NAME, 'main_file_provider');
$bShowQueries  = Option::get(ADMIN_MODULE_NAME, 'main_show_queries') == 'Y';

// дата-провайдер

$obDataProvider = new DataProvider($sFileProvider, $bShowQueries);
$obDataProvider->setLogSettings('bitrix');
$arFiles = $obDataProvider->getFiles();

$obAdminListHelper->setFilter(array(
    'log'           => array('TITLE' => Loc::getMessage('BM_LA_FILTER_SELECT_LOGFILE'), 'TYPE' => 'select', 'VARIANTS' => $arFiles),
    'date'          => array('TITLE' => Loc::getMessage('BM_LA_FILTER_DATE'), 'TYPE' => 'calendar'),
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
    'QUERY' => function($val, $arRec) {
        return SqlFormatter::format($val);
    },
    'MESSAGE' => function($val, $arRec) {
        return MainHelper::prepareMessage($val);
    },
    'STACK_TRACE' => function($val, $arRec) {
        return MainHelper::prepareMessage($val);
    },
));
$obAdminListHelper->output();
