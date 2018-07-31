<?php

namespace BxLeader\LogAnalyzer\Provider;

use \BxLeader\LogAnalyzer\Parser\Parser,
    \BxLeader\LogAnalyzer\Parser\RegExLib;

/**
 * Дата-провайдер работы с файлами через Linux
 *
 * @package    BxLeader\LogAnalyzer\Provider
 * @author     Efremov Dmitriy
 * @copyright  2018 Efremov Dmitriy
 * @license    https://opensource.org/licenses/MIT
 * @link       http://github.com/efremovdm/loganalyzer
 * @version    1.0.0
 */
class LinuxFileProvider extends FileProvider
{
    /**
     * Получение списка файлов в дириктории
     *
     * @param $sDirectory - дириктория
     * @param bool $bRecursive - рекурсивно обходить список поддирикторий
     * @param string $sFilter
     * @return array
    */
    public function getDirList($sDirectory, $bRecursive = false, $sFilter = '')
    {
        $arResult = array();

        $sCommand = 'ls -sh -B -F -l ' . ($bRecursive ? '-R ' : '') . $sDirectory . ' 2>&1';
        $sCommand .= !empty($sFilter) ? " | grep -i '" . $sFilter . "'" : '';
        $arStrings = $this->_execCommand($sCommand);

        if (!empty($arStrings) && false !== stripos($arStrings[0], 'ls:')) {
            $this->arError[] = trim(str_replace('ls:', '', $arStrings[0]));
            $arStrings = array();
        } else {
            $sPrefix = '';
            $arRegEx = RegExLib::getRegEx();
            foreach ($arStrings as $sDir) {
                $arFile = Parser::parseString($arRegEx['command']['ls'], $sDir);
                if (!empty($arFile)) {
                    if ($arFile['SIZE'] > 0 && '/' != substr($arFile['FILE'], -1)) {
                        $arResult[] = array(
                            'file' => $sPrefix . '/' . str_replace('*', '', $arFile['FILE']),
                            'size' => $arFile['SIZE']
                        );
                    }
                }
                elseif (!empty($sDir) && false === stripos($sDir, 'total')) {
                    $sPrefix  = str_replace(array($sDirectory, ':'), '', $sDir);
                }
            }
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
        $this->sFilePath      = $sFilePath;
        $this->sLogEntity     = $sLogEntity;
        $this->bGzip          = false !== stripos($this->sFilePath, '.gz');
        $this->bErrorLog      = false !== stripos($this->sFilePath, 'error');
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
            $this->arFilter[$sLogic][] = $value;
        }
    }

    /**
     * Получение массива первых и последних строк файла
     *
     * @return array
     */
    public function getHeadTailStrings()
    {
        $arResult = array_map('end', array(
            'head' => $this->getStringsFromLog(0, 1, false),
            'tail' => $this->getStringsFromLog(0, -1, false)
        ));

        return $arResult;
    }

    /**
     * Подсчет количества строк в файле
     *
     * @return int
     */
    public function getFileLinesCount() {
        if (!$this->iLinesCount && $this->bSearchEnable) {
            $sCommand  = $this->_buildCommandCore();
            $sCommand .= "wc -l";

            $arFile = $this->_execCommand($sCommand);
            if (!empty($arFile)) {
                $arFile = explode(' ', $arFile[0]);
                $this->iLinesCount = intval($arFile[0]);
            }
        }
        return $this->iLinesCount;
    }

    /**
     * Получение последних или первых $iLimit строк из файла
     *
     * @param int $iOffset
     * @param int $iLimit
     * @param bool $bReverse
     * @return array
     */
    public function getStringsFromLog($iOffset, $iLimit, $bReverse = true)
    {
        $arStrings = $arResult = array();

        if (!$this->bSearchEnable) {
            return $arStrings;
        }

        $sCommand  = $this->_buildCommandCore();

        if (!$iOffset) {
            if ($iLimit > 0) {
                $sCommand .= 'head -n ' . escapeshellcmd($iLimit);
            } else {
                $sCommand .= 'tail -n ' . escapeshellcmd(-$iLimit);
            }
        } else {
            $sCommand .= "sed -n '" . escapeshellcmd($iOffset) . ', ' . escapeshellcmd($iLimit) . "p'";
        }

        $arStrings = $this->_execCommand($sCommand);

        // ошибка
        if (!empty($arStrings) && (false !== stripos($arStrings[0], 'cat:') || false !== stripos($arStrings[0], 'gzip:'))) {

            $this->arError[] = trim(str_replace(array('cat:', 'gzip:'), '', join("\n", $arResult)));
        }
        // корректная работа
        else {
            // установка номеров строк в ключи массива
            $i = $iOffset;
            foreach ($arStrings as $k => $v) {
                $arResult[$i] = $this->limitStrLen($v);
                $i++;
                unset($arStrings[$k]);
            }
            if ($bReverse) {
                $arResult = array_reverse($arResult, true);
            }
        }

        return $arResult;
    }

    /**
     * Исполнение коменды в ОС
     *
     * @param $sCommand
     * @return array
     */
    private function _execCommand($sCommand) {

        $this->arQuery[] = $sCommand;

        if (function_exists('exec')) {
            exec($sCommand, $arResult);
        } else {
            $arResult = array();
        }

        return $arResult;
    }

    /**
     * Сборка основной части команды
     *
     * @return string
     */
    private function _buildCommandCore() {

        // получение / распаковка файла
        $sCommand = ($this->bGzip ? 'zcat ' : 'cat ') . escapeshellcmd($this->sFilePath) . ' 2>&1 | ';
        $sCommand .= $this->_getRexExPattern();

        // применение фильтров
        $arFind = array();
        foreach ($this->arFilter as $sLogic => $arFields) {
            foreach ($arFields as $obFilter) {
                $obFilter = ($obFilter);
                switch ($sLogic) {
                    case 'NOT':
                        $arFind[] = "grep -i -v '" . $obFilter . "'"; // НЕ
                        break;
                    case 'AND':
                    default:
                        if (is_array($obFilter)) {
                            $obFilter = join("\|", $obFilter); // И (ИЛИ|ИЛИ)
                        }
                        $arFind[] = "grep -i '" . $obFilter . "'"; // И
                }
            }
        }

        if (!empty($arFind)) {
            $sCommand .= join(' | ', $arFind) .' | ';
        }

        return $sCommand;
    }

    /**
     * Получение регулярного выражения для определения начала новой строки
     * @return string
     */
    private function _getRexExPattern() {

        $sResult = '';

        $arPatterns = $this->getBegginingPatterns();
        if (!empty($arPatterns)) {
            $sPseudo = RegExLib::PSEUDO_BREAK;
            $sResult = join('|', $arPatterns);
            $sResult = "perl -p -i -e 's/\r?\n/{$sPseudo}/g' | "      // замена переносов строк на псевдопереносы
                . "perl -p -i -e 's/{$sPseudo}$/\n/g' | "             // добавление реального переноса в конец строки
                . "perl -p -i -e 's/{$sPseudo}($sResult)/\n$1/g' | "; // замена псевдопереносов на реальные переносы
        }

        return $sResult;
    }
}