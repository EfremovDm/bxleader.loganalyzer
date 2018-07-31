<?

namespace BxLeader\LogAnalyzer\Parser;

/**
 * Парсер строк логов с использованием библиотек регулярок
 *
 * @package    BxLeader\LogAnalyzer\Parser
 * @author     Efremov Dmitriy
 * @copyright  2018 Efremov Dmitriy
 * @license    https://opensource.org/licenses/MIT
 * @link       http://github.com/efremovdm/loganalyzer
 * @version    1.0.0
 */
class Parser
{
    const DATETIME_FORMAT = \DateTime::W3C;
    private $arError = array();

    /**
     * Парсер логов Apache
     *
     * @param $arLog - Массив строк логов
     * @param $bErrorLog - Лог ошибок
     * @return array
     */
    public function apacheLog($arLog, $bErrorLog = false)
    {
        $arResult = array();

        $arRegEx     = RegExLib::getRegEx();
        $arRegExDate = $arRegEx['date']['apache']['error'];
        $arRegEx     = $arRegEx['log']['apache'][$bErrorLog ? 'error' : 'access'];

        foreach ($arLog as $i => $val) {
            $arParse = $this->parseString($arRegEx, $val);
            if (!empty($arParse)) {
                $arFields = array('N' => $i);
                foreach ($arParse as $k => $v) {
                    switch ($k) {
                        case 'DATE':
                            if ($bErrorLog) {
                                $arDate = $this->parseString($arRegExDate, $v);
                                $arDateFields = array('DAY', 'MONTH', 'YEAR', 'TIME');
                                foreach ($arDateFields as $key => $val) {
                                    if (isset($arDate[$val])) {
                                        $v = ($key ? $v . ' ' : '') . $arDate[$val];
                                    }
                                }
                            }
                            $v = date(self::DATETIME_FORMAT, @strtotime($v));
                            break;
                        case 'URI':
                        case 'REFERER':
                            $v = urldecode($v);
                            break;
                        case 'CLIENT':
                            $arClient = explode(':', $arParse['CLIENT']);
                            $arFields['IP'] = $arClient[0];
                            $arFields['PORT'] = isset($arClient[1]) ? $arClient[1] : '';
                            $v = '';
                            break;
                        case 'MESSAGE':
                            $v = str_replace(array(RegExLib::PSEUDO_BREAK, ' in '), array(PHP_EOL, ' in ' . PHP_EOL), $v);
                            break;
                    }
                    $arFields[$k] = ('-' == $v) ? '' : trim($v);
                }
                $arResult[$i] = $arFields;
            } else {
                $this->arError[] = $val;
            }
        }

        return $arResult;
    }

    /**
     * Парсер логов Nginx
     *
     * @param $arLog - Массив строк логов
     * @param $bErrorLog - Лог ошибок
     * @return array
     */
    public function nginxLog($arLog, $bErrorLog = false)
    {
        $arResult = array();

        $arRegEx = RegExLib::getRegEx();
        $arRegEx = $arRegEx['log']['nginx'][$bErrorLog ? 'error' : 'access'];

        foreach ($arLog as $i => $val) {
            $arParse = $this->parseString($arRegEx, $val);
            if (!empty($arParse)) {
                $arFields = array('N' => $i);
                foreach ($arParse as $k => $v) {
                    switch ($k) {
                        case 'LABEL':
                            $v = '';
                            break;
                        case 'DATE':
                            $v = date(self::DATETIME_FORMAT, @strtotime($v));
                            break;
                        case 'URI':
                        case 'REFERER':
                            $v = urldecode($v);
                            break;
                        case 'CLIENT':
                            $arFields['IP'] = $v;
                            $v = '';
                            break;
                        case 'MESSAGE':
                            $v = str_replace(RegExLib::PSEUDO_BREAK, PHP_EOL, $v);
                            break;
                    }
                    $arFields[$k] = ('-' == $v || '_' == $v) ? '' : trim($v);
                }
                $arResult[$i] = $arFields;
            } else {
                $this->arError[] = $val;
            }

            unset($arLog[$i]);
        }

        return $arResult;
    }

    /**
     * Парсер логов MySQL
     *
     * @param $arLog
     * @return array
     */
    public function mysqlLog($arLog)
    {
        $arResult = array();

        $arRegEx = RegExLib::getRegEx();
        $arRegEx = $arRegEx['log']['mysql'];

        foreach ($arLog as $i => $val) {
            $arParse = $this->parseString($arRegEx, $val);
            if (!empty($arParse)) {
                $arFields = array('N' => $i);
                foreach ($arParse as $k => $v) {
                    switch ($k) {
                        case 'LABEL':
                            $v = '';
                            break;
                        case 'DATE':

                            // если датавремя содержит ещё какую-то хрень-метку
                            $iMaxLen = stripos($v, '.') ?: strlen($v);
                            $v = substr($v, 0, $iMaxLen);

                            // преобразование в формат сайта + сдвиг time часов
                            $v = date(self::DATETIME_FORMAT, @strtotime($v));
                            break;
                        case 'MESSAGE':
                            $v = str_replace(RegExLib::PSEUDO_BREAK, PHP_EOL, $v);
                            break;
                    }
                    $arFields[$k] = trim($v);
                }
                $arResult[$i] = $arFields;
            } else {
                $this->arError[] = $val;
            }
        }

        return $arResult;
    }

    /**
     * Парсер логов Php
     *
     * @param $arLog
     * @return array
     */
    public function phpLog($arLog) {

        $arResult = array();

        $arRegEx      = RegExLib::getRegEx();
        $arRegExBegin = $arRegEx['beginning']['php'];
        $arRegEx      = $arRegEx['log']['php'];

        foreach ($arLog as $i => $val) {
            $arParse = $this->parseString($arRegEx, $val);
            if (!empty($arParse)) {
                $arFields = array('N' => $i);
                foreach ($arParse as $k => $v) {
                    switch ($k) {
                        case 'LABEL':
                            $v = '';
                            break;
                        case 'DATE':
                            $v = date(self::DATETIME_FORMAT, @strtotime($v));
                            break;
                        case 'MESSAGE':
                            $v = str_replace(RegExLib::PSEUDO_BREAK, PHP_EOL, $v);
                            foreach ($arRegExBegin as $sPattern) {
                                $sPattern = '/'. str_replace('?!', '' , $sPattern) . '/';
                                $v = preg_replace($sPattern, '', $v);
                            }
                            break;
                    }
                    $arFields[$k] = trim($v);
                }
                $arResult[$i] = $arFields;
            } else {
                $this->arError[] = $val;
            }
        }

        return $arResult;
    }

    /**
     * Парсер логов Cron
     *
     * @param $arLog
     * @return array
     */
    public function cronLog($arLog) {

        $arResult = array();

        $arRegEx = RegExLib::getRegEx();
        $arRegEx = $arRegEx['log']['cron'];

        foreach ($arLog as $i => $val) {
            $arParse = $this->parseString($arRegEx, $val);
            if (!empty($arParse)) {
                $arFields = array('N' => $i);
                foreach ($arParse as $k => $v) {
                    switch ($k) {
                        case 'LABEL':
                            $v = '';
                            break;
                        case 'DATE':

                            // если датавремя содержит ещё какую-то хрень-метку
                            $iMaxLen = stripos($v, '.') ?: strlen($v);
                            $v = substr($v, 0, $iMaxLen);

                            // преобразование в формат сайта + сдвиг time часов
                            $v = date(self::DATETIME_FORMAT, @strtotime($v));
                            break;
                        case 'MESSAGE':
                            $v = strip_tags(urldecode(str_replace(RegExLib::PSEUDO_BREAK, PHP_EOL, $v)));
                            break;
                    }
                    $arFields[$k] = trim($v);
                }
                $arResult[$i] = $arFields;
            } else {
                $this->arError[] = $val;
            }
        }

        return $arResult;
    }

    /**
     * Maillog идентичен cron
     * @param $arLog
     * @return array
     */
    public function mailLog($arLog) {
        return $this->cronLog($arLog);
    }

    /**
     * Парсер логов Битрикс
     *
     * @param $arLog - Массив строк логов
     * @return array
     */
    public final function bitrixLog($arLog)
    {
        $arResult = array();

        $arRegEx = RegExLib::getRegEx();
        $arRegEx = $arRegEx['log']['bitrix'];

        foreach ($arLog as $i => $val) {
            $arParse = $this->parseString($arRegEx, $val);
            if (!empty($arParse)) {
                $arFields = array('N' => $i);
                foreach ($arParse as $k => $v) {
                    switch ($k) {
                        case 'MESSAGE':
                        case 'QUERY':
                        case 'STACK_TRACE':
                            $v = str_replace(RegExLib::PSEUDO_BREAK, PHP_EOL, $v);
                            if ('STACK_TRACE' == $k) {
                                $v = str_replace(' from ', PHP_EOL, $v);
                            }
                            break;
                        case 'DATE':
                            $v = str_replace(RegExLib::PSEUDO_BREAK, '', $v);
                            $v = date(self::DATETIME_FORMAT, @strtotime($v));
                            break;
                        default:
                            $v = str_replace(RegExLib::PSEUDO_BREAK, '', $v);
                    }
                    $arFields[$k] = trim($v);
                }
                $arResult[$i] = $arFields;
            } else {
                $this->arError[] = $val;
            }
        }

        return $arResult;
    }

    /**
     * Парсер логов Symfony
     *
     * @param $arLog - Массив строк логов
     * @return array
     */
    public function symfonyLog($arLog)
    {
        $arResult = array();

        $arRegEx = RegExLib::getRegEx();
        $arRegEx = $arRegEx['log']['symfony'];

        foreach ($arLog as $i => $val) {
            $arParse = $this->parseString($arRegEx, $val);
            if (!empty($arParse)) {
                $arFields = array('N' => $i);
                foreach ($arParse as $k => $v) {
                    switch ($k) {
                        case 'DATE':
                            $v = date(self::DATETIME_FORMAT, @strtotime($v));
                            break;
                        case 'MESSAGE':
                            $v = strip_tags(urldecode(str_replace(RegExLib::PSEUDO_BREAK, PHP_EOL, $v)));
                            break;
                    }
                    $arFields[$k] = ('-' == $v) ? '' : trim($v);
                }
                $arResult[$i] = $arFields;
            } else {
                $this->arError[] = $val;
            }
        }

        return $arResult;
    }

    /**
     * Парсер логов Yii
     *
     * @param $arLog - Массив строк логов
     * @return array
     */
    public function yiiLog($arLog)
    {
        $arResult = array();

        $arRegEx = RegExLib::getRegEx();
        $arRegEx = $arRegEx['log']['yii'];

        foreach ($arLog as $i => $val) {
            $arParse = $this->parseString($arRegEx, $val);
            if (!empty($arParse)) {
                $arFields = array('N' => $i);
                foreach ($arParse as $k => $v) {
                    switch ($k) {
                        case 'DATE':
                            $v = date(self::DATETIME_FORMAT, @strtotime($v));
                            break;
                        case 'MESSAGE':
                            $v = strip_tags(urldecode(str_replace(RegExLib::PSEUDO_BREAK, PHP_EOL, $v)));
                            break;
                    }
                    $arFields[$k] = ('-' == $v) ? '' : trim($v);
                }
                $arResult[$i] = $arFields;
            } else {
                $this->arError[] = $val;
            }
        }

        return $arResult;
    }

    /**
     * Разбор строки по подобранному регулярному выражению
     *
     * @param $arPatterns - массив регулярных выражений
     * @param $str - разбираемая строка
     * @return array
     */
    public static function parseString($arPatterns, $str) {

        $arResult = array();

        if (empty($arPatterns) || empty($str)) {
            return $arResult;
        }

        foreach ($arPatterns as $sPattern) {
            if (preg_match($sPattern, $str, $matches)) {
                foreach ($matches as $k => $v) {
                    if (!empty($k) && !is_integer($k)) {
                        $arResult[$k] = $v;
                    }
                }
                break;
            }
        }

        return $arResult;
    }

    /**
     * Получение массива ошибок
     *
     * @return array
     */
    public function getErrorList() {
        return $this->arError;
    }
}