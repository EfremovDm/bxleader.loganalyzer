<?php

namespace BxLeader\LogAnalyzer\Provider;

use \BxLeader\LogAnalyzer\Parser\RegExLib;

/**
 * Фабрика, обеспечивающая универсальную работу с провайдерами файлов
 *
 * @package    BxLeader\LogAnalyzer\Provider
 * @author     Efremov Dmitriy
 * @copyright  2018 Efremov Dmitriy
 * @license    https://opensource.org/licenses/MIT
 * @link       http://github.com/efremovdm/loganalyzer
 * @version    1.0.0
 */
abstract class FileProvider
{
    protected $sFilePath     = '';
    protected $bErrorLog     = false;
    protected $sLogEntity    = '';
    protected $bGzip         = false;
    protected $iLinesCount   = 0;
    protected $arFilter      = array('NOT' => array(), 'AND' => array());
    protected $arQuery       = array();
    protected $arError       = array();
    protected $arNotice      = array();
    protected $bSearchEnable = true;

    /**
     * Фабрика, определяющая конечный провайдер данных
     *
     * @param $sProvider
     * @return LinuxFileProvider|PhpFileProvider
     */
    public static function init($sProvider) {

        return 'linux' == strtolower($sProvider) ? new LinuxFileProvider() : new PhpFileProvider();
    }

    /**
     * Отключение поиска для получения первой-последней строки
     *
     * @param bool $bEnable
     */
    public function setSearchEnable($bEnable) {
        $this->bSearchEnable = $bEnable;
    }

    /**
     * Проверка на лог ошибок
     *
     * @return bool
     */
    public function checkErrorLog() {
        return $this->bErrorLog;
    }

    /**
     * Получение массива предупреждений
     *
     * @return array
     */
    public function getNoticeList() {
        return $this->arNotice;
    }

    /**
     * Получение массива ошибок
     *
     * @return array
     */
    public function getErrorList() {
        return $this->arError;
    }

    /**
     * Возвращает полный список запросов
     *
     * @return array
     */
    public function getQueryList() {
        return array_map(function($n) { return str_replace(array("\r", "\n"), array('\r', '\n'), $n); }, $this->arQuery);
    }

    /**
     * Ограничение длины строки для парсера (строки более 300 000 символов парсер не съездает)
     *
     * @param $str
     * @param int $iLimit
     * @return string
     */
    protected static function limitStrLen($str, $iLimit = 300000) {
        return function_exists('mb_strlen')
            ? (mb_strlen($str) > $iLimit ? mb_substr($str, 0, $iLimit) : $str)
            : (strlen($str) > $iLimit ? substr($str, 0, $iLimit) : $str);
    }

    /**
     * Получение шаблонов начала строк технологий
     *
     * @return array
     */
    protected function getBegginingPatterns() {
        $arPatterns = RegExLib::getRegEx();
        switch ($this->sLogEntity) {
            case 'apache': $arPatterns = $this->bErrorLog ? $arPatterns['beginning']['apache']['error'] : array(); break;
            case 'nginx':  $arPatterns = $this->bErrorLog ? $arPatterns['beginning']['nginx']['error'] : array(); break;
            case 'mysql':  $arPatterns = $arPatterns['beginning']['mysql']; break;
            case 'php':    $arPatterns = $arPatterns['beginning']['php']; break;
            case 'bitrix': $arPatterns = $arPatterns['beginning']['bitrix']; break;
            case 'yii':    $arPatterns = $arPatterns['beginning']['yii']; break;
            default:       $arPatterns = array();
        }
        return $arPatterns;
    }

    /**
     * Абстрактные методы для реализации в классах-наследниках
     */
    abstract public function getDirList($sDirectory, $bRecursive, $sFilter);
    abstract public function setFilePath($sFilePath, $sLogEntity);
    abstract public function addFilter($value, $sLogic);
    abstract public function getFileLinesCount();
    abstract public function getStringsFromLog($iOffset, $iLimit, $bReverse);
    abstract public function getHeadTailStrings();
}