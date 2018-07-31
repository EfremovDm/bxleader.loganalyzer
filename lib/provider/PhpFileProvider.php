<?php

namespace BxLeader\LogAnalyzer\Provider;

use \BxLeader\LogAnalyzer\Parser\RegExLib,
    \BxLeader\LogAnalyzer\Utils\BxHelper;

/**
 * Дата-провайдер работы с файлами через PHP
 *
 * @package    BxLeader\LogAnalyzer\Provider
 * @author     Efremov Dmitriy
 * @copyright  2018 Efremov Dmitriy
 * @license    https://opensource.org/licenses/MIT
 * @link       http://github.com/efremovdm/loganalyzer
 * @version    1.0.0
 */
class PhpFileProvider extends FileProvider
{
    private $bFileReadable = true;

    /**
     * Конструктор.
     */
    public function __construct() {
        set_time_limit(0);
    }

    /**
     * Получение списка не пустых файлов в дириктории
     *
     * @param string $sDirectory
     * @param bool $bRecursive
     * @param string $sFilter
     * @return array
     */
    public function getDirList($sDirectory, $bRecursive = false, $sFilter = '') {
        $arResult = array();
        if (!is_dir($sDirectory)) {
            $this->arError[] = 'This is not dir "' . $sDirectory . '"!';
        } elseif(!is_readable($sDirectory)) {
            $this->arError[] = 'Dir "' . $sDirectory . '" doesn\'t readable!';
        } else {
            $arResult = $this->_getDirFiles($sDirectory, $bRecursive, $sFilter);
        }
        return $arResult;
    }

    /**
     * Установка пути к файлу
     *
     * @param $sFilePath
     * @param $sLogEntity
     */
    public function setFilePath($sFilePath, $sLogEntity) {
        $this->sFilePath     = $sFilePath;
        $this->sLogEntity    = $sLogEntity;
        $this->bGzip         = false !== stripos($this->sFilePath, '.gz');
        $this->bErrorLog     = false !== stripos($this->sFilePath, 'error');
        $this->bFileReadable = $this->_checkFileReadable();
    }

    /**
     * Добавляет значения для фильтра
     *
     * @param $value
     * @param string $sLogic
     */
    public function addFilter($value, $sLogic = 'AND') {
        $value = is_array($value) ? array_map('trim', $value) : trim($value);
        if (!empty($value)) {
            if (is_array($value)) {
                $value = join('|', $value);
            }
            $this->arFilter[$sLogic][] = '/('. str_replace('/', '\/', $value) .')/i';
        }
    }

    /**
     * Получение массива первых и последних строк файла
     *
     * @return array
     */
    public function getHeadTailStrings() {

        $arResult = array();

        if (!$this->bFileReadable) {
            return $arResult;
        }

        $sPattern = $this->_getRexExPattern();

        $handle = $this->_execFunc('open', $this->sFilePath, 'r');

        // для GZ-файла оптимальным является единое прямое построчное последовательное чтение всего файла
        // т.к. чтение с обратным сдвигом по байтам для tail является эмулируемым и очень медленным
        if ($this->bGzip) {

            $sFullStr = $sHead = '';

            while (!$this->_execFunc('eof', $handle)) {

                $sStr = trim($this->_execFunc('gets', $handle)); // построчное чтение

                $bLineBegin = !empty($sPattern) ? preg_match($sPattern, $sStr) : true; // определения начала новой строки

                if ($bLineBegin) {
                    if (!empty($sFullStr) && empty($sHead)) {
                        $sHead = $sFullStr;
                    }
                    $sFullStr = $sStr;
                } else {
                    $sFullStr .= (!empty($sFullStr) ? RegExLib::PSEUDO_BREAK : '') . $sStr;
                    $sFullStr = $this->limitStrLen($sFullStr); // ограничение
                }
            }

            $arResult = array('head' => $sHead ? : $sFullStr, 'tail' => $sFullStr);

        } else {

            // прямое построчное чтение
            $sFullStr = '';
            while (!$this->_execFunc('eof', $handle)) {

                $sStr = trim($this->_execFunc('gets', $handle));

                $bLineBegin = !empty($sPattern) ? preg_match($sPattern, $sStr) : true; // определения начала новой строки

                if ($bLineBegin) {
                    if (!empty($sFullStr)) {
                        break;
                    }
                    $sFullStr = $sStr;
                } else {
                    $sFullStr .= (!empty($sFullStr) ? RegExLib::PSEUDO_BREAK : '') . $sStr;
                    $sFullStr = $this->limitStrLen($sFullStr); // ограничение
                }
            }
            $arResult['head'] = $sFullStr;

            // обратное чтение
            $sFullStr = '';
            $linecounter = 10000; // лимит на чтения
            $pos = -2;
            $beginning = false;
            while ($linecounter > 0) {
                $t = " ";
                while ($t != "\n") {
                    if ($this->_execFunc('seek', $handle, $pos, SEEK_END) == -1) {
                        $beginning = true;
                        break;
                    }
                    $t = $this->_execFunc('getc', $handle);
                    $pos--;
                }
                $linecounter--;

                if ($beginning) {
                    $this->_execFunc('rewind', $handle);
                }

                $sStr = trim($this->_execFunc('gets', $handle));

                $bLineBegin = !empty($sPattern) ? preg_match($sPattern, $sStr) : true; // определения начала новой строки

                $sFullStr = $sStr . (!empty($sFullStr) ? RegExLib::PSEUDO_BREAK : '') . $sFullStr;
                $sFullStr = $this->limitStrLen($sFullStr); // ограничение

                if ($bLineBegin || $beginning) {
                    break;
                }
            }

            $arResult['tail'] = $sFullStr;
        }

        $this->_execFunc('close', $handle);

        return $arResult;
    }

    /**
     * Подсчет количества строк в файле
     *
     * @return int
     */
    public function getFileLinesCount() {
        $this->_readFile(true);
        return $this->iLinesCount;
    }

    public function getStringsFromLog($iOffset, $iLimit, $bReverse = true) {
        $arStrings = $this->_readFile(false, $iOffset, $iLimit, $bReverse);
        return $arStrings;
    }

    /**
     * Чтение файла
     *
     * @param $bLinesCount
     * @param int $iOffset
     * @param int $iLimit
     * @param boolean $bReverse
     * @return array
     */
    private function _readFile($bLinesCount, $iOffset = 1, $iLimit = 20, $bReverse = true) {

        $arStrings = array();
        $iStrNum = 0; $sFullStr = '';

        if (!$this->bFileReadable || !$this->bSearchEnable) {
            return $arStrings;
        }

        $i = $iOffset;
        $sPattern = $this->_getRexExPattern();
        $handle = $this->_execFunc('open', $this->sFilePath, "r");
        while ($handle && !$this->_execFunc('eof', $handle))  {

            // получение содержимого строки
            $str = $this->limitStrLen($this->_execFunc('gets', $handle));

            if (empty($str)) {
                continue;
            }

            // определения начала новой строки
            $bLineBegin = !empty($sPattern) ? preg_match($sPattern, $str) : true;

            if ($bLineBegin) {

                // фильтрация
                if (!empty($sFullStr) && $this->_filterStr($sFullStr)) {
                    $sFullStr = $str;
                    continue;
                }

                if ($bLinesCount) {
                    $this->iLinesCount++;
                }
                else {
                    if ($iStrNum >= $iOffset && $iStrNum <= $iLimit) { // получение нужных строк
                        if (!empty($sFullStr)) {
                            $arStrings[$i] = str_replace(array("\r\n", "\n"), '', $sFullStr); $sFullStr = '';
                            $i++;
                        }
                    } elseif ($iStrNum > $iLimit) { // выход из анализа в случае если получены нужные строки
                        $sFullStr = '';
                        break;
                    }
                    $iStrNum++;
                }
                $sFullStr = $str;
            } else {
                $sFullStr .= (!empty($sFullStr) ? RegExLib::PSEUDO_BREAK : '') . $str;
            }
        }
        if (!empty($sFullStr) && !$this->_filterStr($sFullStr)) {
            $arStrings[$i] = str_replace(array("\r\n", "\n"), '', $this->limitStrLen($sFullStr));
            $i++;
        }
        if ($bLinesCount && $this->iLinesCount > 1) { // мааасенький костыленыш
            $this->iLinesCount--;
        }

        $this->_execFunc('close', $handle);

        // реверс результата
        if (!$bLinesCount && $bReverse) {
            $arStrings = array_reverse($arStrings, true);
        }

        return $arStrings;
    }

    /**
     * Получение регулярного выражения для определения начала новой строки
     * @return string
     */
    private function _getRexExPattern() {

        $sResult = '';

        $arPatterns = $this->getBegginingPatterns();
        if (!empty($arPatterns)) {
            $arPatterns = array_map(function($s) { return $s = '^' . $s; }, $arPatterns);
            $sResult    = '[' . join('|', $arPatterns) . ']';
        }

        return $sResult;
    }

    /**
     * Проверка файла существование и возможность чтения
     *
     * @return bool
     */
    private function _checkFileReadable() {
        $bResult = true;
        if (empty($this->sFilePath)) {
            $bResult = false;
        }
        elseif (!file_exists($this->sFilePath)) {
            $this->arError[] = 'File "' . $this->sFilePath . '" doesn\'t exist!';
            $bResult = false;
        }
        else {
            $handler = $this->_execFunc('open', $this->sFilePath, "r");
            if (!$handler) {
                $this->arError[] = 'File "' . $this->sFilePath . '" doesn\'t readable!';
                $bResult = false;
            }
            $this->_execFunc('close', $handler);
        }
        return $bResult;
    }

    /**
     * Фильтрация строки
     *
     * @param $str
     * @return bool
     */
    private function _filterStr($str) {
        foreach ($this->arFilter as $sLogic => $arFilter) {
            foreach ($arFilter as $sPattern) {
                if ('NOT' != $sLogic && !preg_match($sPattern, $str) || 'NOT' == $sLogic && preg_match($sPattern, $str)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Выбор библиотеки, которая будет исполнять запросы к файловой системе: Zlib или File
     *
     * @param $fn - базовое наименование функции
     * @param $p1 - параметр 1
     * @param $p2 - параметр 2
     * @param $p3 - параметр 3
     * @return mixed
     */
    private function _execFunc($fn, $p1, $p2 = '', $p3 = '') {
        $fn = ($this->bGzip ? 'gz' : ('rewind' == $fn ? '' : 'f')) . $fn;
        if (!empty($p3)) {
            $res = $fn($p1, $p2, $p3);
        } elseif (!empty($p2)) {
            $res = $fn($p1, $p2);
        } else {
            $res = $fn($p1);
        }
        return $res;
    }

    /**
     * Получение списка файлов дириктории
     *
     * @param $sDirectory
     * @param bool $bRecursive
     * @param string $sFilter
     * @return array
     */
    private function _getDirFiles($sDirectory, $bRecursive, $sFilter) {
        $arResult = array();
        $arDirFiles = scandir($sDirectory);
        foreach ($arDirFiles as $key => $value) {
            if (!in_array($value, array('.', '..'))) {
                if (is_dir($sDirectory . DIRECTORY_SEPARATOR . $value)) {
                    if ($bRecursive) {
                        $arSub = $this->getDirList($sDirectory . DIRECTORY_SEPARATOR . $value, $bRecursive, $sFilter);
                        foreach ($arSub as $arFile) {
                            if (!empty($sFilter) && false === stripos($arFile['file'], $sFilter)) {
                                continue;
                            }
                            $arResult[] = array('file' => $value . DIRECTORY_SEPARATOR . $arFile['file'], 'size' => $arFile['size']);
                        }
                    }
                }
                else {
                    $iSize = filesize($sDirectory . DIRECTORY_SEPARATOR . $value);
                    if ($iSize) {
                        if (!empty($sFilter) && false === stripos($value, $sFilter)) {
                            continue;
                        }
                        $arResult[] = array('file' => $value, 'size' => BxHelper::formatFileSize($iSize));
                    }
                }
            }
        }
        return $arResult;
    }
}