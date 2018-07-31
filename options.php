<?
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

global $APPLICATION;
use \Bitrix\Main\Application,
    \Bitrix\Main\Localization\Loc,
    \BxLeader\LogAnalyzer\Provider\DataProvider,
    \BxLeader\LogAnalyzer\Utils\MainHelper,
    \BxLeader\LogAnalyzer\Utils\BxHelper;

require_once(__DIR__ . '/admin/prolog.php');

/**
 * Настройки модуля
 */
class LogSettings {

    private
        $arFileProviders = array(),
        $arBxFiles       = array(),
        $arMainOptions   = array(),
        $aTabs           = array(),
        $arError         = array(),
        $arNotice        = array(),
        $tabControl      = null;

    public function __construct() {}

    /**
     * Получение локализаций формы настроек
     */
    public function loadMessages()
    {
        Loc::loadMessages(Application::getDocumentRoot() . BX_ROOT . '/modules/main/options.php');
    }

    /**
     * Получение списка провайдеров файлов
     */
    public function getFileProviders()
    {
        $this->arFileProviders = MainHelper::getFileProviders();
        foreach ($this->arFileProviders as $sName => &$val) {
            if ($val) {
                $this->arFileProviders[$sName] = Loc::getMessage('BM_LA_FILE_PROVIDER_' . strtoupper($sName));
            } else {
                unset($this->arFileProviders[$sName]);
            }
        }
    }

    /**
     * Получение списка файлов логов Битрикса
     */
    public function getBitrixLogFiles()
    {
        $arBxLogFiles = BxHelper::getBitrixLogFiles();
        foreach ($arBxLogFiles as $k => $arFile) {
            $this->arBxFiles[$k] = str_replace(Application::getDocumentRoot(), '', $arFile['view']);
        }
    }

    /**
     * Вкладки
     */
    public function getTabs()
    {
        $this->aTabs = array(
            array(
                'DIV'     => 'main_settings',
                'TAB'     => Loc::getMessage('BM_LA_SETTINGS_MAIN'),
                'TITLE'   => Loc::getMessage('BM_LA_SETTINGS_MAIN'),
                'ICON'    => 'main_settings',
                'OPTIONS' => array(),
            ),
            array(
                'DIV'     => 'documentation',
                'TAB'     => Loc::getMessage('BM_LA_SETTINGS_DOCUMENTATION'),
                'TITLE'   => Loc::getMessage('BM_LA_SETTINGS_DOCUMENTATION'),
                'ICON'    => 'main_documentation',
                'OPTIONS' => array(),
            ),
            array(
                'DIV'     => 'donate',
                'TAB'     => Loc::getMessage('BM_LA_SETTINGS_DONATE'),
                'TITLE'   => Loc::getMessage('BM_LA_SETTINGS_DONATE'),
                'ICON'    => 'main_user_edit',
                'OPTIONS' => array(),
            ),
            array(
                'DIV'     => 'security_settings',
                'TAB'     => Loc::getMessage('BM_LA_SETTINGS_ACCESS'),
                'TITLE'   => Loc::getMessage('BM_LA_SETTINGS_ACCESS'),
                'ICON'    => 'security_settings',
                'OPTIONS' => array(),
            ),
        );
    }

    /**
     * Содержимое основных настроек
     */
    public function getMainOptions()
    {
        $this->arMainOptions[] = Loc::getMessage('BM_LA_COMMON');
        $this->arMainOptions[] = array('main_file_provider',    Loc::getMessage('BM_LA_FILE_PROVIDER'), false, array('selectbox', $this->arFileProviders));
        if (isset($this->arFileProviders['linux'])) {
            $this->arMainOptions[] = array('main_show_queries', Loc::getMessage('BM_LA_SHOW_QUERIES'), false, array('checkbox'), '1');
        }

        $this->arMainOptions[] = Loc::getMessage('BM_LA_APACHE');
        $this->arMainOptions[] = array('apache_log_view',       Loc::getMessage('BM_LA_ENABLE'), false, array('checkbox'), '1');
        $this->arMainOptions[] = array('apache_log_path',       Loc::getMessage('BM_LA_LOG_DIR'), false, array('text', 50));
        $this->arMainOptions[] = array('apache_log_recursive',  Loc::getMessage('BM_LA_LOG_DIR_RECURSIVE'), false, array('checkbox'), '1');

        $this->arMainOptions[] = Loc::getMessage('BM_LA_NGINX');
        $this->arMainOptions[] = array('nginx_log_view',        Loc::getMessage('BM_LA_ENABLE'), false, array('checkbox'), '1');
        $this->arMainOptions[] = array('nginx_log_path',        Loc::getMessage('BM_LA_LOG_DIR'), false, array('text', 50));
        $this->arMainOptions[] = array('nginx_log_recursive',   Loc::getMessage('BM_LA_LOG_DIR_RECURSIVE'), false, array('checkbox'), '1');

        $this->arMainOptions[] = Loc::getMessage('BM_LA_' . strtoupper(BxHelper::getDbFork()));
        $this->arMainOptions[] = array('mysql_log_view',        Loc::getMessage('BM_LA_ENABLE'), false, array('checkbox'), '1');
        $this->arMainOptions[] = array('mysql_log_path',        Loc::getMessage('BM_LA_LOG_DIR'), false, array('text', 50));
        $this->arMainOptions[] = array('mysql_log_recursive',   Loc::getMessage('BM_LA_LOG_DIR_RECURSIVE'), false, array('checkbox'), '1');

        $this->arMainOptions[] = Loc::getMessage('BM_LA_PHP');
        $this->arMainOptions[] = array('php_log_view',          Loc::getMessage('BM_LA_ENABLE'), false, array('checkbox'), '1');
        $this->arMainOptions[] = array('php_log_path',          Loc::getMessage('BM_LA_LOG_DIR'), false, array('text', 50));
        $this->arMainOptions[] = array('php_log_recursive',     Loc::getMessage('BM_LA_LOG_DIR_RECURSIVE'), false, array('checkbox'), '1');

        $this->arMainOptions[] = Loc::getMessage('BM_LA_CRON');
        $this->arMainOptions[] = array('cron_log_view',         Loc::getMessage('BM_LA_ENABLE'), false, array('checkbox'), '1');
        $this->arMainOptions[] = array('cron_log_path',         Loc::getMessage('BM_LA_LOG_DIR'), false, array('text', 50));
        $this->arMainOptions[] = array('cron_log_prefix',       Loc::getMessage('BM_LA_LOG_PREFIX'), false, array('text', 50));
        $this->arMainOptions[] = array('cron_log_recursive',    Loc::getMessage('BM_LA_LOG_DIR_RECURSIVE'), false, array('checkbox'), '1');

        $this->arMainOptions[] = Loc::getMessage('BM_LA_MAIL');
        $this->arMainOptions[] = array('mail_log_view',         Loc::getMessage('BM_LA_ENABLE'), false, array('checkbox'), '1');
        $this->arMainOptions[] = array('mail_log_path',         Loc::getMessage('BM_LA_LOG_DIR'), false, array('text', 50));
        $this->arMainOptions[] = array('mail_log_prefix',       Loc::getMessage('BM_LA_LOG_PREFIX'), false, array('text', 50));
        $this->arMainOptions[] = array('mail_log_recursive',    Loc::getMessage('BM_LA_LOG_DIR_RECURSIVE'), false, array('checkbox'), '1');

        $arBxMess =  BxHelper::getBitrixLogTypes($this->arBxFiles);

        $this->arMainOptions[] = Loc::getMessage('BM_LA_BITRIX');
        if (!empty($this->arBxFiles)) {
            $this->arMainOptions[] = array('bitrix_log_view', Loc::getMessage('BM_LA_ENABLE'), false, array('checkbox'), '1');
        }
        $this->arMainOptions[] = array('note' => join('<br />', $arBxMess));

        $this->arMainOptions[] = Loc::getMessage('BM_LA_SYMFONY');
        $this->arMainOptions[] = array('symfony_log_view',      Loc::getMessage('BM_LA_ENABLE'), false, array('checkbox'), '1');
        $this->arMainOptions[] = array('symfony_log_path',      Loc::getMessage('BM_LA_LOG_DIR'), false, array('text', 50));
        $this->arMainOptions[] = array('symfony_log_recursive', Loc::getMessage('BM_LA_LOG_DIR_RECURSIVE'), false, array('checkbox'), '1');

        $this->arMainOptions[] = Loc::getMessage('BM_LA_YII');
        $this->arMainOptions[] = array('yii_log_view',          Loc::getMessage('BM_LA_ENABLE'), false, array('checkbox'), '1');
        $this->arMainOptions[] = array('yii_log_path',          Loc::getMessage('BM_LA_LOG_DIR'), false, array('text', 50));
        $this->arMainOptions[] = array('yii_log_recursive',     Loc::getMessage('BM_LA_LOG_DIR_RECURSIVE'), false, array('checkbox'), '1');
    }

    /**
     * Сохранение настроек
     *
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function saveOptions()
    {
        if ('POST' == $_SERVER['REQUEST_METHOD'] && !empty($_REQUEST['mid']) && check_bitrix_sessid()
            && (isset($_REQUEST['Update']) && !empty($_REQUEST['Update'])
                || isset($_REQUEST['RestoreDefaults']) && !empty($_REQUEST['RestoreDefaults']))
        ) {
            global $APPLICATION;

            if (isset($_REQUEST['RestoreDefaults']) && !empty($_REQUEST['RestoreDefaults'])) {

                COption::RemoveOption($_REQUEST['mid']);

                $arDelGroups = array();
                $obGroup = new \CGroup();
                $rsGroups = $obGroup->GetList($v1='id',$v2='asc', array('ACTIVE' => 'Y', 'ADMIN' => 'N'));
                while($arGroup = $rsGroups->Fetch()) {
                    $arDelGroups[] = $arGroup['ID'];
                }
                $APPLICATION->DelGroupRight($_REQUEST['mid'], $arDelGroups);
            }
            else {

                function delSaveOpt(&$arMainSaveOptions, $sTech) {
                    foreach ($arMainSaveOptions as $key => $arTech) {
                        if (is_array($arTech) && in_array($sTech, $arTech)) {
                            unset($arMainSaveOptions[$key]);
                        }
                    }
                }

                $arMainSaveOptions = $this->arMainOptions;

                // проверки перед сохранением
                $arOptions = BxHelper::getModuleOptions($_REQUEST['mid']);
                $arTech = MainHelper::getTech();
                unset($arTech[array_search('bitrix', $arTech)]); // битрикс не участвует в валидации

                $sFileProvider =  isset($_POST['main_file_provider']) && !empty($_POST['main_file_provider'])
                                    ? $_POST['main_file_provider'] : $arOptions['main_file_provider'];

                $obDataProvider = new DataProvider($sFileProvider);

                foreach ($arTech as $sTech) {
                    if (isset($_POST[$sTech . '_log_view']) && 'Y' == $_POST[$sTech . '_log_view']) {
                        if (isset($_POST[$sTech . '_log_path']) && !empty($_POST[$sTech . '_log_path'])) {

                            $obDataProvider->setLogSettings(
                                $sTech,
                                isset($_POST[$sTech . '_log_path']) ? $_POST[$sTech . '_log_path'] : '',
                                isset($_POST[$sTech . '_log_recursive']) ? 'Y' == $_POST[$sTech . '_log_recursive'] : false
                            );
                            $arDirList = $obDataProvider->getFiles();
                            $arErrorList = $obDataProvider->getError();

                            if (empty($arDirList) && !empty($arErrorList)) {
                                delSaveOpt($arMainSaveOptions, $sTech);
                            }
                        } else {
                            $this->arError[] = Loc::getMessage('BM_LA_SELECT_DIR') . $sTech . PHP_EOL;
                            delSaveOpt($arMainSaveOptions, $sTech);
                        }
                    }
                }
                $this->arError = array_merge($this->arError, $obDataProvider->getError());

                if (empty($this->arError)) {

                    // сохранение настроек
                    __AdmSettingsSaveOptions($_REQUEST['mid'], $arMainSaveOptions);

                    // сохранение прав доступа
                    $arOldGroups = $arNewGroups = array();
                    $rsRights = $this->application()->GetGroupRightList(array('MODULE_ID' => $_REQUEST['mid']));
                    while ($row = $rsRights->Fetch()) {
                        $arOldGroups[$row['GROUP_ID']] = $row['GROUP_ID'];
                    }

                    if (isset($_REQUEST['GROUPS']) && !empty($_REQUEST['GROUPS'])
                        && isset($_REQUEST['RIGHTS']) && !empty($_REQUEST['RIGHTS'])
                    ) {
                        foreach ($_REQUEST['GROUPS'] as $i => $iGroup) {
                            if ((!empty($iGroup) || $iGroup == '0')
                                && isset($_REQUEST['RIGHTS'][$i]) && !empty($_REQUEST['RIGHTS'][$i])
                            ) {
                                $this->application()->SetGroupRight($_REQUEST['mid'], $iGroup, $_REQUEST['RIGHTS'][$i], false);
                                $arNewGroups[$iGroup] = $iGroup;
                            }
                        }
                    }

                    foreach ($arOldGroups as $iGroup) {
                        if (!isset($arNewGroups[$iGroup])) {
                            $APPLICATION->DelGroupRight($_REQUEST['mid'], array($iGroup));
                        }
                    }
                }
            }
        }
    }

    /**
     * Получение прочих оповещений
     */
    public function getNotice()
    {
        $this->arNotice = BxHelper::checkOtherRequirements();
    }

    /**
     * Показ оповещений и ошибок
     */
    public function showMessages()
    {
        echo BxHelper::showNotice($this->arNotice);

        $sMess = join(PHP_EOL, $this->arError);
        if (!empty($sMess)) {
            $obAdminMessage = new \CAdminMessage($sMess);
            $obAdminMessage->ShowMessage($sMess);
        }
    }

    /**
     * Вывод
     */
    public function output()
    {
        global $APPLICATION;
        $this->tabControl = new \CAdminTabControl('tabControl', $this->aTabs);

        $this->tabControl->Begin();
        ?>
        <form method='post' action='<?= $APPLICATION->GetCurPage() ?>?lang=<?= LANGUAGE_ID ?>&amp;mid=<?= urlencode($_REQUEST['mid']) ?>'>
            <?
            $this->tabControl->BeginNextTab();

            __AdmSettingsDrawList($_REQUEST['mid'], $this->arMainOptions);

            $this->tabControl->BeginNextTab();
            ?>
            <tr>
                <td colspan="2" align="left">
                    <?=MainHelper::getReadMeHtml(__DIR__);?>
                </td>
            </tr>
            <?

            $this->tabControl->BeginNextTab();

            $arCss = array('components.cards.min.css', 'objects.grid.min.css',
                'objects.grid.responsive.min.css', 'objects.containers.min.css', 'components.tables.min.css');
            foreach ($arCss as $sItem) {
                $APPLICATION->SetAdditionalCSS('https://unpkg.com/blaze@4.0.0-6/scss/dist/' . $sItem);
            }

            ?>
            <tr>
                <td colspan="2" align="left">
                    <div class="o-container--super">

                        <div class="o-grid">
                            <div class="o-grid__cell o-grid__cell--width-100">
                                <div class="c-card">
                                    <div class="c-card__body">
                                        <?=Loc::getMessage('BM_LA_MESS_DONATE');?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="o-grid">
                            <div class="o-grid__cell o-grid__cell--width-50">
                                <div class="o-container--large">
                                    <h2 id="yaPay" class="c-heading u-large"></h2>
                                    <iframe src="https://money.yandex.ru/quickpay/shop-widget?writer=seller&targets=%D0%9F%D0%BE%D0%B4%D0%B4%D0%B5%D1%80%D0%B6%D0%BA%D0%B0%20%D0%BE%D0%B1%D0%BD%D0%BE%D0%B2%D0%BB%D0%B5%D0%BD%D0%B8%D0%B9%20%D0%B1%D0%B5%D1%81%D0%BF%D0%BB%D0%B0%D1%82%D0%BD%D1%8B%D1%85%20%D0%BC%D0%BE%D0%B4%D1%83%D0%BB%D0%B5%D0%B9&targets-hint=&default-sum=500&button-text=14&payment-type-choice=on&mobile-payment-type-choice=on&hint=&successURL=&quickpay=shop&account=41001191257272" width="100%" height="220" frameborder="0" allowtransparency="true" scrolling="no"></iframe>
                                </div>
                            </div>

                            <div class="o-grid__cell o-grid__cell--width-45">

                                <div class="o-container--large">
                                    <h2 id="morePay" class="c-heading u-large"></h2>
                                    <table class="c-table">
                                        <tbody class="c-table__body c-table--striped">
                                        <tr class="c-table__row">
                                            <td class="c-table__cell">Yandex.Money</td>
                                            <td class="c-table__cell">41001191257272</td>
                                        </tr>
                                        <tr class="c-table__row">
                                            <td class="c-table__cell">Webmoney WMR (rub)</td>
                                            <td class="c-table__cell">R147256485220</td>
                                        </tr>
                                        <tr class="c-table__row">
                                            <td class="c-table__cell">Webmoney WMZ (usd)</td>
                                            <td class="c-table__cell">Z884532893613</td>
                                        </tr>
                                        <tr class="c-table__row">
                                            <td class="c-table__cell">Webmoney WME (euro)</td>
                                            <td class="c-table__cell">E795061867883</td>
                                        </tr>
                                        <tr class="c-table__row">
                                            <td class="c-table__cell">PayPal</td>
                                            <td class="c-table__cell"><a href="https://www.paypal.me/efremovdm" target="_blank">paypal.me/efremovdm</a></td>
                                        </tr>
                                        </tbody>
                                    </table><br />
                                    <p>
                                        <?=Loc::getMessage('BM_LA_MESS_FEEDBACK');?>:
                                        <a href="mailto:<?=Loc::getMessage('BM_LA_DEVELOPER_EMAIL')?>"
                                            ><?=Loc::getMessage('BM_LA_DEVELOPER_EMAIL')?></a>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </td>
            </tr>

            <?
            $this->tabControl->BeginNextTab();

            global $module_id; $module_id = BxHelper::MODULE_ID;
            require_once(Application::getDocumentRoot() . BX_ROOT . '/modules/main/admin/group_rights.php');

            $this->tabControl->Buttons(); ?>

            <?= bitrix_sessid_post(); ?>

            <input type='submit' name='Update' value='<?= Loc::getMessage('MAIN_SAVE') ?>'
                   title='<?= Loc::getMessage('MAIN_OPT_SAVE_TITLE') ?>'
                   class='adm-btn-save' />

            <? if (isset($_REQUEST['back_url_settings']) && strlen($_REQUEST['back_url_settings']) > 0): ?>
                <input type='button' name='Cancel'
                       value='<?= Loc::getMessage('MAIN_OPT_CANCEL') ?>'
                       title='<?= Loc::getMessage('MAIN_OPT_CANCEL_TITLE') ?>'
                       onclick='window.location="<?=htmlspecialcharsbx(CUtil::addslashes($_REQUEST['back_url_settings'])) ?>"' />
                <input type='hidden' name='back_url_settings'
                       value='<?= htmlspecialcharsbx($_REQUEST['back_url_settings']) ?>' />
            <? endif ?>

            <input type='submit' name='RestoreDefaults'
                   title='<?= Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS') ?>'
                   onClick='return confirm("<?=AddSlashes(Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING')) ?>")'
                   value='<?= Loc::getMessage('MAIN_RESTORE_DEFAULTS') ?>' />

            <? $this->tabControl->End(); ?>
        </form>

        <?
    }

    /**
     * Редирект
     */
    public function redirect()
    {
        if (empty($this->arError) && 'POST' == $_SERVER['REQUEST_METHOD'] && check_bitrix_sessid()
            && (isset($_REQUEST['Update']) && !empty($_REQUEST['Update'])
                || isset($_REQUEST['RestoreDefaults']) && !empty($_REQUEST['RestoreDefaults']))
        ) {
            global $APPLICATION;

            if (isset($_REQUEST['Update']) && !empty($_REQUEST['Update'])
                && isset($_REQUEST['back_url_settings']) && !empty($_REQUEST['back_url_settings'])
            ) {
                LocalRedirect($_REQUEST['back_url_settings']);
            }
            else {
                LocalRedirect(
                    $APPLICATION->GetCurPage()
                    . '?mid=' . urlencode($_REQUEST['mid'])
                    . '&lang=' . urlencode(LANGUAGE_ID)
                    . '&back_url_settings=' . urlencode($_REQUEST['back_url_settings'])
                    . '&' . $this->tabControl->ActiveTabParam()
                );
            }
        }
    }

    private function application() {
        return $GLOBALS['APPLICATION'];
    }
}

$obLogSettings = new LogSettings();
$obLogSettings->loadMessages();
$obLogSettings->getFileProviders();
$obLogSettings->getBitrixLogFiles();
$obLogSettings->getTabs();
$obLogSettings->getMainOptions();
$obLogSettings->saveOptions();
$obLogSettings->getNotice();
$obLogSettings->showMessages();
$obLogSettings->output();
$obLogSettings->redirect();
