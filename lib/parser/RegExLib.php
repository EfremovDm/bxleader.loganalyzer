<?

namespace BxLeader\LogAnalyzer\Parser;

/**
 * Библиотека регулярных выражений для логов стандартного вида.
 *
 * Если Вы хотите внести свои паттерны регулярных выражений, сделайте это в исключенном из обновлений классе RegExLibCustom.
 *
 * @package    BxLeader\LogAnalyzer\Parser
 * @author     Efremov Dmitriy
 * @copyright  2018 Efremov Dmitriy
 * @license    https://opensource.org/licenses/MIT
 * @link       http://github.com/efremovdm/loganalyzer
 * @version    1.0.0
 */
class RegExLib extends RegExLibCustom
{
    const PSEUDO_BREAK   = '#PB#';

    /**
     * Массив всех регулярных выражений
     *
     * @return array
     */
    public static function getRegEx() {
        return array_merge_recursive(
            parent::getRegEx(),                      // пользовательские регулярные выражения в приоритете
            self::_getMainRexEx(),                   // основной блок регулярных выражений
            self::_getOpenServerApacheNginxAccess(), // open server apache + nginx (доступы)
            self::_getOpenServerApacheNginxError(),  // open server apache + nginx (ошибки)
            self::_getUniversal()                    // универсальное регулярное выражение
        );
    }

    /**
     * Массив форматов дат для формирования шаблонов при фильтрации
     *
     * @return array
     */
    public static function getDateFormat() {

        $arResult = array(
            'apache'  => array('access' => 'd/M/Y', 'error' => 'M d'),
            'nginx'   => array('access' => 'd/M/Y', 'error' => 'M d'),
            'php'     => 'd-M-Y',
            'bitrix'  => 'Y-m-d',
            'symfony' => 'Y-m-d',
            'yii'     => 'Y-m-d'
        );

        return array_replace_recursive($arResult, parent::getDateFormat());
    }

    private static function _getMainRexEx() {

        $pb = self::PSEUDO_BREAK;

        return array(
            'command' => array( // команды линукса
                'ls' => array(
                    '/^(\s+)?(?<BLOK_SIZE>\S+)\s+(?<PERMISSIONS>\S+)\s+(?<LINK>\S+)\s+(?<OWNER>\S+)\s+(?<GROUP>\S+)\s+(?<SIZE>\S+)\s+(?<MONTH>\S+)\s+(?<DATE>\S+)\s+(?<TIME>\S+)\s+(?<FILE>\S+)/'
                )
            ),
            'beginning' => array( // начало линий
                'apache' => array(
                    'error' => array(
                        '((\S+\s)?\[)', // классический лог и таймвеб
                    )
                ),
                'nginx' => array(
                    'error' => array(
                        '(\S{10}\s\S{8})\s(\[\S+\])',
                    )
                ),
                'mysql' => array(
                    '([0-9]{4}-[0-9]{1,2}-[0-9]{1,2}(T|\s+)[0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2})',
                    '(\d+\s+[0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2})',
                ),
                'php' => array(
                    '(\[\S+\s\S+\s\S+\])(?!(\s+PHP\s+\d+\.\s+?))',
                ),
                'bitrix' => array(
                    '([0-9]{4}-[0-9]{1,2}-[0-9]{1,2}\s[0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2})',
                    '(Host:\s)',
                    '(TIME:\s\S+\sSESSION:)',
                ),
                'yii' => array(
                    '([0-9]{4}-[0-9]{1,2}-[0-9]{1,2}\s[0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}\s\[)',
                )
            ),
            'log' => array( // непосредственно строки логов
                'apache' => array(
                    'access' => array(
                        '/^(?<HOST>\S+\s)?(?<IP>\S+)\s(?<USER>\S+)\s(?<USER_ID>\S+)\s\[(?<DATE>.+?)\]\s\"((?<METHOD>\S+)\s)?(?<URI>.+?)(\s(?<PROTOCOL>\S+))?\"\s(?<STATUS>\d+)\s(?<BYTES>\S+)\s\"(?<REFERER>.+)\"\s\"(?<USER_AGENT>.+)\"$/',
                    ),
                    'error' => array(
                        '/^(?<HOST>\S+ )?\[(?<DATE>.+?)\] \[(?<ERROR>\S+)\] (\[pid (?<PID>\S+( \S+)?)\] )?(?<MOD>\S+: )?(\[client (?<CLIENT>\S+)\] )?(?<MESSAGE>.+?)(, referer: (?<REFERER>\S+))?$/',
                    )
                ),
                'nginx' => array(
                    'access' => array(
                        '/^(?<IP>\S+)\s(?<USER>\S+)\s(?<USER_ID>\S+)\s\[(?<DATE>.+)\]\s\"((?<METHOD>\S+)\s)?(?<URI>.+?)(\s(?<PROTOCOL>\S+))?\"\s(?<STATUS>\d+)\s(?<BYTES>\S+)\s\"(?<REFERER>\S+)\"\s\"(?<USER_AGENT>.+?)\"(\s\"(?<HTTP_X_FORWARDED_FOR>.+?)\")?$/',
                        '/^(?<IP>\S+)\s(?<USER>\S+)\s(?<USER_ID>\S+)\s\[(?<DATE>.+)\s-\s(?<LABEL>\S+)\]\s(?<STATUS>\d+)\s\"((?<METHOD>\S+)\s)?(?<URI>.+?)(\s(?<PROTOCOL>\S+))?\"\s(?<BYTES>\S+)\s\"(?<REFERER>\S+)\"\s\"(?<USER_AGENT>.+)\"\s\"(?<HTTP_X_FORWARDED_FOR>.+)\"$/',
                    ),
                    'error' => array(
                        '/^(?<DATE>.+) \[(?<ERROR>\S+)\] (?<MESSAGE>.+?)(, client: (?<CLIENT>\S+))?(, server: (?<SERVER>\S+))?(, request: \"((?<METHOD>\S+)\s)?(?<URI>.+?)(\s(?<PROTOCOL>\S+))?\")?(, upstream: \"(?<UPDTREAM>\S+)\")?(, host: \"(?<HOST>\S+)\")?(, referrer: \"(?<REFERER>\S+)\")?$/',
                    )
                ),
                'mysql' => array(
                    '/^(?<PROCESS>\d+)(\s+)(?<TIME>[0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2})\s(\[(?<ERROR>\S+)\]\s)?(?<MESSAGE>.+?)$/',
                    '/^(?<DATE>\S+(T|\s)\S+)\s(?<PROCESS>\d+)\s(\[(?<ERROR>\S+)\]\s)?(?<MESSAGE>.+?)$/',
                ),
                'php' => array(
                    '/^\[(?<DATE>\S+\s\S+\s\S+)\]\s(?<MESSAGE>.+?)$/',
                ),
                'cron' => array(
                    '/^(?<DATE>.+)\s(?<SERVER>\S+)\s(?<UTILITY>\S+)\[(?<PROCESS>\d+)\]:\s(?<USER>\(\S+\)\s)?(?<MESSAGE>.+?)$/',
                ),
                'bitrix' => array(
                    "/^Host: (?<HOST>\S+)Date: (?<DATE>.+)Module: (?<MODULE>[a-zA-Z0-9\-\_\.]+)(?<MESSAGE>.+?)$/",
                    "/^(?<DATE>.+) - Host: (?<HOST>\S+)? - (?<TYPE>\S+) - \[(?<ERROR>\S+)\] (?<MESSAGE>.+?)$/",
                    "/^TIME:\s+(?<TIME>\S+)\s+SESSION:\s+(?<SESSION>\S+)?\s+(CONN: (?<CONN>\d+|(Resource id #\d+)))?$pb$pb(?<QUERY>.+)($pb){2,}( from?(?<STACK_TRACE>.+))?($pb)+(.+)$/", // mysql debug
                    "/^TIME:\s+(?<TIME>\S+)\s+SESSION:\s+(?<SESSION>\S+)?\s+(CONN: (?<CONN>\d+|(Resource id #\d+)))?($pb)+(?<QUERY>.+)($pb){1,}(.+)$/", // old version
                ),
                'symfony' => array(
                    '/^\[(?<DATE>\S+\s\S+)\]\s(?<ERROR>\S+):\s(?<MESSAGE>.+?)$/',
                ),
                'yii' => array(
                    '/^(?<DATE>\S+\s\S+)\s\[(?<IP>\S+)\]\[(?<LABEL1>\S+)\]\[(?<LABEL2>\S+)\]\[(?<LEVEL>\S+)\]\[(?<INFO>\S+)\]\s(?<MESSAGE>.+?)$/',
                )
            ),
            'date' => array( // форматы дат для парсера
                'apache' => array(
                    'error' => array(
                        '/^(?<WEEKDAY>\S+)\s(?<MONTH>\S+)\s(?<DAY>\S+)\s(?<TIME>[0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}).(?<LABEL>\S+)\s(?<YEAR>\d+)$/'
                    )
                ),
            )
        );
    }

    /**
     * Регулярные выражения Open Server Apache + Nginx (access)
     *
     * @return array
     */
    private static function _getOpenServerApacheNginxAccess() {
        $arPatterns = array(
            '/^(?<HOST>\S+):\s(?<IP>\S+)\s\[(?<DATE>.+)\]\s\"((?<METHOD>\S+)\s)?(?<URI>.+?)(\s(?<PROTOCOL>\S+))?\"\s\"(?<REFERER>.+)\"\s(?<STATUS>\d+)\s(?<BYTES>\S+)\s\"(?<USER_AGENT>.+)\"\s\"(?<LABEL>.+)\"$/',
            '/^(?<HOST>\S+):\s(?<IP>\S+)\s\[(?<DATE>.+)\]\s\"((?<METHOD>\S+)\s)?(?<URI>.+?)(\s(?<PROTOCOL>\S+))?\"\s(?<STATUS>\d+)\s(?<BYTES>\S+)\s"(?<REFERER>.+)\"\s"(?<USER_AGENT>.+)\"$/'
        );
        return array('log' => array('apache' => array('access' => $arPatterns), 'nginx' => array('access' => $arPatterns)));
    }

    /**
     * Регулярные выражения Open Server Apache + Nginx (error)
     *
     * @return array
     */
    private static function _getOpenServerApacheNginxError() {
        $arPatterns = array(
            '/^(?<DATE>.+)\s\[(?<ERROR>\S+)\]\s(?<MESSAGE>.+?)(, client: (?<CLIENT>\S+))(, server: (?<SERVER>\S+))(, request: \"((?<METHOD>\S+)\s)?(?<URI>.+?)(\s(?<PROTOCOL>\S+))?\")(, host: \"(?<HOST>\S+)\")?(, referrer: \"(?<REFERER>\S+)\")$/'
        );
        $arNginx = array(
            '/^(?<DATE>.+)\s\[(?<ERROR>.+)\]\s(?<MESSAGE>.+?)$/' // простые ошибки
        );
        return array('log' => array('apache' => array('error' => $arPatterns), 'nginx' => array('error' => array_merge($arPatterns, $arNginx))));
    }

    /**
     * Универсальное регулярное выражение для логов неизвестного типа
     *
     * @return array
     */
    private static function _getUniversal() {

        $arPatterns = array(
            '/^(?<MESSAGE>.*)$/'
        );

        $arResult = array();
        $arMain = self::_getMainRexEx();
        foreach ($arMain['log'] as $key => $arItem) {
            if (isset($arItem['access']) && isset($arItem['error'])) {
                $arResult['log'][$key]['access'] = $arResult['log'][$key]['error'] = $arPatterns;
            }
            else {
                $arResult['log'][$key] = $arPatterns;
            }
        }

        return $arResult;
    }
}