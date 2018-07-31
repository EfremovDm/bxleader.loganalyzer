<?php

namespace BxLeader\LogAnalyzer\Utils;

use \Bitrix\Main\Application,
    \Bitrix\Main\Loader,
    \Bitrix\Main\Config\Option,
    \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Config\Configuration;

/**
 * Утилиты взаимодействия с Битриксом
 *
 * @package    BxLeader\LogAnalyzer\Utils
 * @author     Efremov Dmitriy
 * @copyright  2018 Efremov Dmitriy
 * @license    https://opensource.org/licenses/MIT
 * @link       http://github.com/efremovdm/loganalyzer
 * @version    1.0.0
 */
class BxHelper extends MainHelper
{
    public function __construct()
    {
        define('ADMIN_MODULE_NAME', self::MODULE_ID);
    }

    /**
     * Проверка минимальных требований к конфигурации
     *
     * @param bool $bInstall
     * @throws \Exception
     */
    public static function checkMinRequirements($bInstall = false) {
        global $USER;
        if ($bInstall && !$USER->IsAdmin()) {
            throw new \Exception(GetMessage('BM_LA_MODULE_INSTALL_ADMIN'));
        }
        if ($bInstall && IsModuleInstalled(self::MODULE_ID)) {
            throw new \Exception(GetMessage('BM_LA_MODULE_INSTALL_ALREADY'));
        }
        if (!CheckVersion(SM_VERSION, '14.00.00')) {
            throw new \Exception(str_replace('#VERSION#', '14.0', GetMessage('BM_LA_MODULE_INSTALL_BX')));
        }
    }

    public static function checkOtherRequirements() {
        $arNotice = array();
        if (!function_exists('exec') && !MainHelper::isWindows()) {
            $arNotice[] = Loc::getMessage('BM_LA_MODULE_INSTALL_EXEC');
        }
        if (!MainHelper::checkLoadedLib('zlib')) {
            $arNotice[] = Loc::getMessage('BM_LA_MODULE_INSTALL_ZLIB');
        }
        if (!self::setMemoryLimit()) {
            $arNotice[] = Loc::getMessage('BM_LA_MODULE_INSTALL_MLIMIT');
        }
        return $arNotice;
    }

    /**
     * Получение настроек модуля.
     * В новых версиях Битрикса есть метод getForModule, который работает с кешем, но для старых написан прямой запрос.
     *
     * @param $sModuleId
     * @return array
     */
    public static function getModuleOptions($sModuleId) {

        $arResult = array();

        if (empty($sModuleId)) {
            return $arResult;
        }

        // main module > 14.X.X
        if (method_exists('\Bitrix\Main\Config\Option', 'getForModule')) {
            $arResult = Option::getForModule($sModuleId);
        }
        // main module == 14.X.X
        else {
            $connection = Application::getConnection();
            $sModuleId = $connection->getSqlHelper()->forSql($sModuleId);

            $rsOptions = $connection->query("SELECT NAME, VALUE FROM b_option WHERE MODULE_ID = '$sModuleId'");
            while ($arItem = $rsOptions->fetch()) {
                $arResult[$arItem['NAME']] = $arItem['VALUE'];
            }
        }

        return $arResult;
    }

    /**
     * Вывод уведомления
     *
     * @param $arNotice
     * @return string
     */
    public static final function showNotice($arNotice) {
        $sResult = '';
        if (!empty($arNotice) && self::isBxCodeIncluded()) {
            $arNotice = join(PHP_EOL.PHP_EOL, $arNotice);
            $sResult  = BeginNote() . nl2br($arNotice) . EndNote();
        }
        return $sResult;
    }

    /**
     * Определение форка MySQL
     *
     * @return string
     */
    public static function getDbFork() {

        $sResult = 'mysql'; $sServerInfo = '';

        if (self::isBxCodeIncluded()) {

            // полчение версии БД
            $connection = Application::getConnection();
            $arConfiguration = $connection->getConfiguration();

            if (false !== stripos($arConfiguration['className'], 'mysqli')) { // MySQL Improved
                $sServerInfo = $connection->getResource()->server_info;
            }
            elseif (false !== stripos($arConfiguration['className'], 'mysql')) { // MySQL Original
                $arVersion = $connection->query('SELECT VERSION();')->fetch();
                $sServerInfo = $arVersion['VERSION()'];
            }

            // определение форка
            if (false !== stripos($sServerInfo, '-MariaDB')) {
                $sResult = 'mariadb';
            }
            elseif (!empty($sServerInfo)) {
                $arVersion = explode('-', $sServerInfo);
                if (isset($arVersion[1]) && is_numeric($arVersion[1])) {
                    $sResult = 'percona';
                }
            }
        }
        return $sResult;
    }

    /**
     * Форматирование размера файла
     *
     * @param $size
     * @param int $precision
     * @return string
     */
    public static function formatFileSize($size, $precision = 2) {

        $a = array('b', 'Kb', 'Mb', 'Gb', 'Tb');
        $pos = 0;
        while ($size >= 1024 && $pos < 4) {
            $size /= 1024;
            $pos++;
        }

        $sResult = round($size, $precision);
        if (self::isBxCodeIncluded()) {
            $sResult .= ' '. Loc::getMessage('BM_LA_FILE_SIZE_' . $a[$pos]);
        }

        return $sResult;
    }

    /**
     * Получение адресов файлов логов Битрикса
     * @return array
     */
    public static function getBitrixLogFiles() {

        $arFiles = $arResult = array();

        if (!self::isBxCodeIncluded()) {
            return $arResult;
        }

        // стандартный лог Битрикс
        if (defined('LOG_FILENAME') && null != LOG_FILENAME && file_exists(LOG_FILENAME)) {
            $arFiles['LOG_FILENAME'] = LOG_FILENAME;
        }

        // лог ошибок Битрикса
        $arExceptionHandling = Configuration::getValue('exception_handling');
        if (isset($arExceptionHandling['log']['settings']['file']) && !empty($arExceptionHandling['log']['settings']['file'])) {
            $sFile = Application::getDocumentRoot()  .'/' . $arExceptionHandling['log']['settings']['file'];
            foreach (array($sFile, $sFile . '.old') as $k => $sFile) {
                if (file_exists($sFile)) {
                    $arFiles['exception_handling_' . $k] = $sFile;
                }
            }
        }

        // лог запросов к БД Битрикса
        $sSqlLog = Application::getDocumentRoot()  .'/' . Application::getConnection()->getType() . '_debug.sql';
        if (file_exists($sSqlLog)) {
            $arFiles['DBDebugToFile'] = $sSqlLog;
        }

        foreach ($arFiles as $k => $sFile) {
            $arResult[$k] = array(
                'file' => self::path2Abs($sFile),
                'size' => self::formatFileSize(filesize($sFile)),
                'view' => str_replace(Application::getDocumentRoot(), '', $sFile),
            );
        }

        return $arResult;
    }

    /**
     * Получение типов логов
     *
     * @param $arBxFiles
     * @return array
     */
    public static function getBitrixLogTypes($arBxFiles) {

        $arTypes = array(
            'LOG_FILENAME'       => '<a href="https://dev.1c-bitrix.ru/api_help/main/functions/debug/addmessage2log.php" target="_blank">LOG_FILENAME</a>',
            'exception_handling' => '<a href="https://dev.1c-bitrix.ru/learning/course/?COURSE_ID=43&LESSON_ID=2795#exception_handling" target="_blank">exception_handling</a>',
            'DBDebugToFile'      => '<a href="https://dev.1c-bitrix.ru/api_help/main/general/magic_vars.php" target="_blank">DBDebugToFile</a>'
        );
        foreach ($arTypes as $sType => $sLink) {
            $arFiles = array();
            foreach ($arBxFiles as $sFileType => $sFile) {
                if (stripos($sFileType, $sType) !== false) {
                    $arFiles[] = $sFile;
                }
            }
            $arTypes[$sType] .= ': ' . (!empty($arFiles) ? join(', ', $arFiles) : '-');
        }

        return $arTypes;
    }

    /**
     * Установка предельного размера выделяемой памяти для корректной работы постраничкой навигации Битрикса
     *
     * @param string $sLimit
     * @return bool|string
     */
    public static function setMemoryLimit($sLimit = '1024M') {

        $bResult = true;

        if (self::isBxCodeIncluded() && \CUtil::Unformat(ini_get('memory_limit')) < \CUtil::Unformat($sLimit)) {
            $bResult = false !== @ini_set('memory_limit', $sLimit);
        }

        return $bResult;
    }

    /**
     * Билдер меню в админитстративной части
     *
     * @param $aGlobalMenu
     * @param $arMenu
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function onBuildGlobalMenu(&$aGlobalMenu, &$arMenu) {

        if (!self::isBxCodeIncluded() || !self::checkModuleAccess(true)) {
            return ;
        }

        self::loadMessages();

        $arOptions = self::getModuleOptions(self::MODULE_ID);

        $arItems = array();
        if (isset($arOptions['apache_log_view']) && $arOptions['apache_log_view'] == 'Y') {
            $arItems[] = array(
                'text'      => Loc::getMessage('BM_LA_APACHE'),
                'title'     => Loc::getMessage('BM_LA_APACHE'),
                'url'       => BX_ROOT . '/admin/bxleader_loganalyzer_list_apache.php',
                'sort'      => '100',
                'icon'      => 'bmloganalyzer_menu_apache_icon',
                'page_icon' => 'default_page_icon',
            );
        }
        if (isset($arOptions['nginx_log_view']) && $arOptions['nginx_log_view'] == 'Y') {
            $arItems[] = array(
                'text'      => Loc::getMessage('BM_LA_NGINX'),
                'title'     => Loc::getMessage('BM_LA_NGINX'),
                'url'       => BX_ROOT . '/admin/bxleader_loganalyzer_list_nginx.php',
                'sort'      => '110',
                'icon'      => 'bmloganalyzer_menu_nginx_icon',
                'page_icon' => 'default_page_icon',
            );
        }
        if (isset($arOptions['mysql_log_view']) && $arOptions['mysql_log_view'] == 'Y') {
            $sDbFork = self::getDbFork();
            $arItems[] = array(
                'text'      => Loc::getMessage('BM_LA_'. strtoupper($sDbFork)),
                'title'     => Loc::getMessage('BM_LA_'. strtoupper($sDbFork)),
                'url'       => BX_ROOT . '/admin/bxleader_loganalyzer_list_mysql.php',
                'sort'      => '120',
                'icon'      => 'bmloganalyzer_menu_' . $sDbFork . '_icon',
                'page_icon' => 'default_page_icon',
            );
        }
        if (isset($arOptions['php_log_view']) && $arOptions['php_log_view'] == 'Y') {
            $arItems[] = array(
                'text'      => Loc::getMessage('BM_LA_PHP'),
                'title'     => Loc::getMessage('BM_LA_PHP'),
                'url'       => BX_ROOT . '/admin/bxleader_loganalyzer_list_php.php',
                'sort'      => '130',
                'icon'      => 'bmloganalyzer_menu_php_icon',
                'page_icon' => 'default_page_icon',
            );
        }
        if (isset($arOptions['cron_log_view']) && $arOptions['cron_log_view'] == 'Y') {
            $arItems[] = array(
                'text'      => Loc::getMessage('BM_LA_CRON'),
                'title'     => Loc::getMessage('BM_LA_CRON'),
                'url'       => BX_ROOT . '/admin/bxleader_loganalyzer_list_cron.php',
                'sort'      => '140',
                'icon'      => 'bmloganalyzer_menu_cron_icon',
                'page_icon' => 'default_page_icon',
            );
        }
        if (isset($arOptions['mail_log_view']) && $arOptions['mail_log_view'] == 'Y') {
            $arItems[] = array(
                'text'      => Loc::getMessage('BM_LA_MAIL'),
                'title'     => Loc::getMessage('BM_LA_MAIL'),
                'url'       => BX_ROOT . '/admin/bxleader_loganalyzer_list_mail.php',
                'sort'      => '150',
                'icon'      => 'bmloganalyzer_menu_mail_icon',
                'page_icon' => 'default_page_icon',
            );
        }
        if (isset($arOptions['bitrix_log_view']) && $arOptions['bitrix_log_view'] == 'Y') {
            $arItems[] = array(
                'text'      => Loc::getMessage('BM_LA_BITRIX'),
                'title'     => Loc::getMessage('BM_LA_BITRIX'),
                'url'       => BX_ROOT . '/admin/bxleader_loganalyzer_list_bitrix.php',
                'sort'      => '160',
                'icon'      => 'bmloganalyzer_menu_bitrix_icon',
                'page_icon' => 'default_page_icon',
            );
        }
        if (isset($arOptions['symfony_log_view']) && $arOptions['symfony_log_view'] == 'Y') {
            $arItems[] = array(
                'text'      => Loc::getMessage('BM_LA_SYMFONY'),
                'title'     => Loc::getMessage('BM_LA_SYMFONY'),
                'url'       => BX_ROOT . '/admin/bxleader_loganalyzer_list_symfony.php',
                'sort'      => '170',
                'icon'      => 'bmloganalyzer_menu_symfony_icon',
                'page_icon' => 'default_page_icon',
            );
        }
        if (isset($arOptions['yii_log_view']) && $arOptions['yii_log_view'] == 'Y') {
            $arItems[] = array(
                'text'      => Loc::getMessage('BM_LA_YII'),
                'title'     => Loc::getMessage('BM_LA_YII'),
                'url'       => BX_ROOT . '/admin/bxleader_loganalyzer_list_yii.php',
                'sort'      => '180',
                'icon'      => 'bmloganalyzer_menu_yii_icon',
                'page_icon' => 'default_page_icon',
            );
        }

        if (!empty($arItems)) {
            $arMenu[] = array(
                'parent_menu' => 'global_menu_services',
                'icon'        => 'bmloganalyzer_menu_log_icon',
                'page_icon'   => 'bmloganalyzer_menu_log_icon',
                'sort'        => '1000',
                'text'        => Loc::getMessage('BM_LA_MAIN'),
                'title'       => Loc::getMessage('BM_LA_MAIN'),
                'items_id'    => 'menu_bxleader_loganalyzer',
                'section'     => 'bxleader_loganalyzer',
                'more_url'    => array(),
                'items'       => $arItems
            );
        }
    }

    /**
     * Проверка минимальных требований для работы модуля
     */
    public function checkRequirements()
    {
        try {
            $this->checkMinRequirements();
        }
        catch (\Exception $e) {
            require_once(Application::getDocumentRoot() . BX_ROOT . '/modules/main/include/prolog_admin_after.php');
            $sMess = $e->getMessage();
            $obAdminMessage = new \CAdminMessage($sMess);
            $obAdminMessage->ShowMessage($sMess);
            require_once(Application::getDocumentRoot() . BX_ROOT . '/modules/main/include/epilog_admin.php');
        }
    }

    /**
     * Проверка прав доступа
     *
     * @param bool $bReturn - возвращать результат / требовать результат и убивать скрипт
     * @return bool
     */
    public static function checkModuleAccess($bReturn = false)
    {
        global $APPLICATION;
        $bAccessDenied = $APPLICATION->GetGroupRight(self::MODULE_ID) < 'R';

        if ($bReturn) {
            return !$bAccessDenied;
        }
        elseif ($bAccessDenied) {
            $APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
            require(Application::getDocumentRoot() . BX_ROOT . '/modules/main/include/epilog_admin.php');
            die();
        }
    }

    /**
     * Получение локализаций
     */
    public static function loadMessages()
    {
        if (self::isBxCodeIncluded()) {
            Loc::loadMessages(__FILE__);
        }
    }


    public static function getMessage($sName)
    {
        return self::isBxCodeIncluded() ? Loc::getMessage($sName) : $sName;
    }

    /**
     * Получение модуля
     */
    public static function includeModule()
    {
        if (self::isBxCodeIncluded()) {
            Loader::includeModule(self::MODULE_ID);
        }
    }

    /**
     * Проверяет подключено ли ядро Битрикса
     *
     * @return bool
     */
    private static function isBxCodeIncluded() {
        return defined('B_PROLOG_INCLUDED') && B_PROLOG_INCLUDED === true;
    }

    /**
     * Заменяет символы перехода . и .. между папок на прямые абсолютные пути
     *
     * @param $sFilePath
     * @return string
     */
    private static function path2Abs($sFilePath)
    {
        $arPath = explode('/', $sFilePath);

        $arResult = array();
        foreach ($arPath as $v) {
            switch ($v) {
                case '.':  continue; break;
                case '..': array_pop($arResult); break;
                default:   array_push($arResult, $v);
            }
        }

        return join('/', $arResult);
    }
}