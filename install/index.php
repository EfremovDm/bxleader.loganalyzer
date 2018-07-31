<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use \Bitrix\Main\Application,
    \Bitrix\Main\EventManager,
    \Bitrix\Main\Config\Option,
    \Bitrix\Main\ModuleManager,
    \BxLeader\LogAnalyzer\Provider\DataProvider,
    \BxLeader\LogAnalyzer\Utils\BxHelper,
    \BxLeader\LogAnalyzer\Utils\MainHelper;

$strPath2Lang = str_replace("\\", '/', __FILE__);
$strPath2Lang = str_replace('/install/index.php', '', $strPath2Lang);
IncludeModuleLangFile($strPath2Lang . '/lib/utils/BxHelper.php');

require_once __DIR__ . '/../lib/utils/MainHelper.php';
require_once __DIR__ . '/../lib/utils/BxHelper.php';

class bxleader_loganalyzer extends CModule
{
    public $MODULE_ID = 'bxleader.loganalyzer';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS;
    public $PARTNER_NAME;
    public $PARTNER_URI = 'https://bitrix-programmer.ru/';

    public $arNote = array(), $arError = array();

    public function __construct() {

        $this->MODULE_ID           = MainHelper::MODULE_ID;
        $this->MODULE_NAME         = GetMessage('BM_LA_MAIN');
        $this->MODULE_DESCRIPTION  = GetMessage('BM_LA_DESCRIPTION').': '.join(', ', MainHelper::getTech()).'.';
        $this->MODULE_GROUP_RIGHTS = 'Y';
        $this->PARTNER_NAME        = 'BxLeader';
        $this->PARTNER_URI         = 'https://bitrix-programmer.ru/';

        require_once __DIR__ . '/../include.php';
        include __DIR__ . '/version.php';

        if (isset($arModuleVersion) && is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)){
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        } else {
            $this->MODULE_VERSION = '1.0.0';
            $this->MODULE_VERSION_DATE = '2018-08-01 12:00:00';
        }
    }

    public function DoInstall() {
        global $APPLICATION;
        $iStep = intval($_REQUEST['step']);

        try {
            BxHelper::loadMessages(); // mess loader for old versions
            BxHelper::checkMinRequirements(true);
            $this->checkOtherRequirements();
            $this->checkOptions($iStep > 1);

            if ($iStep < 2 || !empty($this->arError)) {

                $GLOBALS['sBmLaModuleId'] = $this->MODULE_ID;
                $GLOBALS['sDbFork']       = BxHelper::getDbFork();
                $GLOBALS['arNote']        = $this->arNote;
                $GLOBALS['arError']       = $this->arError;

                $APPLICATION->IncludeAdminFile(GetMessage('BM_LA_MODULE_INSTALL'), __DIR__ . '/step1.php');

            } elseif ($iStep == 2) {

                $this->InstallFiles();
                $this->saveOptions();
                $this->InstallEvents();
                ModuleManager::registerModule($this->MODULE_ID);

                $APPLICATION->IncludeAdminFile(GetMessage('BM_LA_MODULE_INSTALL'), __DIR__.'/step2.php');
            }

        } catch (Exception $e) {

            $GLOBALS['arError'] = $e->getMessage();
            $APPLICATION->IncludeAdminFile(GetMessage('BM_LA_MODULE_INSTALL'),__DIR__.'/error.php');
            return false;
        }
    }

    public function DoUninstall() {
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        ModuleManager::unregisterModule($this->MODULE_ID);
    }

    public function InstallFiles() {
        CopyDirFiles(
            __DIR__ . '/admin',
            Application::getDocumentRoot() . BX_ROOT . '/admin',
            true,
            true
        );
        CopyDirFiles(
            __DIR__ . '/themes',
            Application::getDocumentRoot() . BX_ROOT . '/themes',
            true,
            true
        );
        return true;
    }

    public function UnInstallFiles() {
        DeleteDirFiles(
            __DIR__ . '/admin',
            Application::getDocumentRoot() . BX_ROOT . '/admin'
        );
        DeleteDirFiles(
            __DIR__ . '/themes/.default',
            Application::getDocumentRoot() . BX_ROOT . '/themes/.default'
        );
        DeleteDirFiles(
            __DIR__ . '/themes/.default/icons/' . $this->MODULE_ID,
            Application::getDocumentRoot() . BX_ROOT . '/themes/.default/icons/' . $this->MODULE_ID
        );
        return true;
    }

    public function InstallEvents() {
        $obEventManager = EventManager::getInstance();
        $obEventManager->registerEventHandler(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            'BxLeader\\LogAnalyzer\\Utils\\BxHelper', 'onBuildGlobalMenu'
        );
        return true;
    }

    public function UnInstallEvents() {
        $obEventManager = EventManager::getInstance();
        $obEventManager->unRegisterEventHandler(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            'BxLeader\\LogAnalyzer\\Utils\\BxHelper', 'onBuildGlobalMenu'
        );
        return true;
    }

    /**
     * Список прав доступа к модулю
     *
     * @return array
     */
    public function GetModuleRightList() {
        $arRightList = array(
            'reference_id' => array('D','R'),
            'reference'    => array(GetMessage('BM_LA_MODULE_INSTALL_DENIED'), GetMessage('BM_LA_MODULE_INSTALL_OPENED'))
        );
        return $arRightList;
    }

    /**
     * Проверка остальных параметров конфигурации
     *
     * @throws Exception
     */
    private function checkOtherRequirements() {
        $this->includeLibs();
        $this->arNote = BxHelper::checkOtherRequirements();
    }

    /**
     * Подключение необходимых библиотек (требуются при установке)
     */
    private function includeLibs() {
        $arLibs = array(
            'parser'   => array('RegExLibCustom', 'RegExLib', 'Parser'),
            'provider' => array('DataProvider', 'FileProvider', 'LinuxFileProvider', 'PhpFileProvider'),
            'utils'    => array('MainHelper', 'BxHelper')
        );
        foreach ($arLibs as $sPackage => $arItem) {
            foreach ($arItem as $sFile) {
                require_once __DIR__ . '/../lib/' . $sPackage . '/' . $sFile . '.php';
            }
        }
    }

    /**
     * Валидация полученных настроек от пользователя
     *
     * @param $bCheck
     * @return bool
     */
    private function checkOptions($bCheck) {

        if (!$bCheck) {
            return false;
        }

        $arFields = $_REQUEST['BM_LA_FIELDS'];
        $arTech = MainHelper::getTech();
        unset($arTech[array_search('bitrix', $arTech)]); // битрикс не участвует в валидации

        $obDataProvider = new DataProvider($arFields['main_file_provider']);

        foreach ($arTech as $sTech) {
            if (isset($arFields[$sTech . '_log_view']) && 'Y' == $arFields[$sTech . '_log_view']) {
                if (isset($arFields[$sTech . '_log_path']) && !empty($arFields[$sTech . '_log_path'])) {
                    $obDataProvider->setLogSettings(
                        $sTech,
                        isset($arFields[$sTech . '_log_path']) ? $arFields[$sTech . '_log_path'] : '',
                        isset($arFields[$sTech . '_log_recursive']) ? 'Y' == $arFields[$sTech . '_log_recursive'] : false
                    );
                    $arDir = $obDataProvider->getFiles();
                } else {
                    $this->arError[] = GetMessage('BM_LA_SELECT_DIR') . $sTech;
                }
            }
        }

        $arFileError = $obDataProvider->getError();

        if (!empty($arFileError)) {
            $this->arError = array_merge($this->arError, $arFileError);
        }
    }

    /**
     * Сохранение настроек
     */
    private function saveOptions() {
        if (empty($this->arError) && !empty($_REQUEST['BM_LA_FIELDS'])) {

            $arTech = MainHelper::getTech();

            // удаление уже сохраненных в базе значений
            foreach ($arTech as $sTech) {
                Option::delete($this->MODULE_ID, array('name' => $sTech. '_log_view'));
            }

            // сохранение новых значений
            foreach ($_REQUEST['BM_LA_FIELDS'] as $sName => $sValue) {
                Option::set($this->MODULE_ID, $sName, $sValue);
            }
        }
    }
}