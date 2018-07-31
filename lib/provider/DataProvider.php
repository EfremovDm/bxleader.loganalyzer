<?php

namespace BxLeader\LogAnalyzer\Provider;

use \BxLeader\LogAnalyzer\Parser\Parser,
    \BxLeader\LogAnalyzer\Parser\RegExLib,
    \BxLeader\LogAnalyzer\Utils\BxHelper,
    \BxLeader\LogAnalyzer\Utils\MainHelper;

/**
 * Провайдер получения данных
 *
 * @package BxLeader\LogAnalyzer\Provider
 */
class DataProvider {

    private $sType, $arFilter = array(), $arOrder = array(), $iPageSize, $iPageNumber; // выходные параметры
    private $sFileProvider, $bShowQueries, $sDirectory, $bRecursive, $sFilePrefix;     // настройки
    private $obFileProvider, $obParser;                                                // провайдер и парсер
    private $arLogFiles, $bErrorLog;                                                   // хранение данных
    private $arNotice = array(), $arError = array();                                   // предупреждения и ошибки
    private $iLinesCount, $bReverse, $arLim, $arStrings = array();                     // результат

    public function __construct($sFileProvider, $bShowQueries = false) {
        $this->sFileProvider = $sFileProvider;
        $this->bShowQueries  = $bShowQueries;
        $this->setProvider();
    }

    /**
     * Установка настроек лога
     *
     * @param $sType
     * @param string $sDirectory
     * @param bool $bRecursive
     * @param string $sFilePrefix
     */
    public function setLogSettings($sType, $sDirectory = '', $bRecursive = false, $sFilePrefix = '') {
        $this->sType         = $sType;
        $this->sDirectory    = $sDirectory;
        $this->bRecursive    = $bRecursive;
        $this->sFilePrefix   = $sFilePrefix;
    }

    /**
     * Установка фильтра
     *
     * @param $arFilter
     */
    public function setFilter($arFilter) {
        $this->arFilter = array_map('trim', $arFilter);
    }

    /**
     * Установка сортировки
     *
     * @param $arOrder
     */
    public function setOrder($arOrder) {
        $this->arOrder = array_map('trim', $arOrder);
    }

    /**
     * Установка количества элементов на страницу
     *
     * @param int $iPageSize
     */
    public function setPageSize($iPageSize = 20) {
        $this->iPageSize = $iPageSize;
    }

    /**
     * Установка номера текущей страницы
     *
     * @param int $iPageNumber
     */
    public function setPageNumber($iPageNumber = 1) {
        $this->iPageNumber = $iPageNumber;
    }

    /**
     * Выбор информации из логов
     */
    public function getData() {
        $this->setFiltration();
        $this->setPagenCount();
        $this->getStrFromLog();
    }

    /**
     * Получение массива файлов для формирования списка в фильтрах
     *
     * @return array
     */
    public function getFiles() {

        $arResult = array();

        // получение списка файлов
        if ('bitrix' == $this->sType) {
            $this->arLogFiles = array_values(BxHelper::getBitrixLogFiles());
        } else {
            $this->arLogFiles = $this->obFileProvider->getDirList($this->sDirectory, $this->bRecursive, $this->sFilePrefix);
        }

        // сообщение о пустой дириктории
        if (empty($this->arLogFiles)) {
            $this->arError[] = 'Dir "' . $this->sDirectory . '" is empty!';
        }

        // фомрирование результата
        foreach ($this->arLogFiles as $arItem) {
            $arResult[$arItem['file']] = $arItem['file'].' ['.$arItem['size'].']';
        }

        return $arResult;
    }

    public function getNotice() {
        return $this->arNotice;
    }

    public function getError() {
        return $this->arError;
    }

    public function getStrings() {
        return $this->arStrings;
    }

    public function getLinesCount() {
        return $this->iLinesCount;
    }

    /**
     * Объекты работы с файловой системой и парсером
     */
    private function setProvider() {
        $this->obFileProvider = FileProvider::init($this->sFileProvider);
        $this->obParser       = new Parser();
    }

    /**
     * Фильтры
     */
    private function setFiltration() {

        // путь до читаемого файла
        $sFilePath = !empty($this->arFilter['log']) ? $this->arFilter['log']
                        : (isset($this->arLogFiles[0]['file']) ? $this->arLogFiles[0]['file'] : '');
        $sFilePath = !empty($sFilePath) ? ($this->sDirectory ? $this->sDirectory . '/' : '') . $sFilePath : '';
        $this->obFileProvider->setFilePath($sFilePath, $this->sType);
        $this->bErrorLog = $this->obFileProvider->checkErrorLog();

        // параметры фильтрации
        $arParamFilter = array();
        if (!empty($this->arFilter['other_exclude'])) {
            $arParamFilter['NOT']['EXCLUDE'] = $this->arFilter['other_exclude'];
        }
        if (!empty($this->arFilter['other_include'])) {
            $arParamFilter['AND']['INCLUDE'] = $this->arFilter['other_include'];
        }
        if (!empty($this->arFilter['ip'])) {
            $arParamFilter['AND']['IP'] = $this->arFilter['ip'];
        }

        $iFilterFrom = $iFilterTo = 0; $sDateFormat = '';
        if (
            (!empty($this->arFilter['date_from']) || !empty($this->arFilter['date_to']))
            && false === strpos($sFilePath, '_debug.sql') // в данном логе датавремя отсутствует
        ) {
            // преобразование дат в timestamp
            $iFilterFrom = @strtotime($this->arFilter['date_from'] . ' midnight');
            $iFilterTo   = @strtotime($this->arFilter['date_to'] . ' midnight');

            // получение формата дат
            $arDateFormat = RegExLib::getDateFormat();
            if (isset($arDateFormat[$this->sType][$this->bErrorLog ? 'error' : 'access'])) {
                $sDateFormat = $arDateFormat[$this->sType][$this->bErrorLog ? 'error' : 'access'];
            } elseif (isset($arDateFormat[$this->sType])) {
                $sDateFormat = $arDateFormat[$this->sType];
            }
        }
        
        if (($iFilterFrom || $iFilterTo) && !empty($sDateFormat)) {

            $arStrLog = $this->obFileProvider->getHeadTailStrings();
            switch ($this->sType) {
                case 'apache':  $arStrLog = $this->obParser->apacheLog($arStrLog, $this->bErrorLog); break;
                case 'nginx':   $arStrLog = $this->obParser->nginxLog($arStrLog, $this->bErrorLog); break;
                case 'php':     $arStrLog = $this->obParser->phpLog($arStrLog); break;
                case 'bitrix':  $arStrLog = $this->obParser->bitrixLog($arStrLog); break;
                case 'symfony': $arStrLog = $this->obParser->symfonyLog($arStrLog); break;
                case 'yii':     $arStrLog = $this->obParser->yiiLog($arStrLog); break;
            }

            $iLogFrom = isset($arStrLog['head']['DATE']) ? @strtotime($arStrLog['head']['DATE'] . ' midnight') : 0;
            $iLogTo   = isset($arStrLog['tail']['DATE']) ? @strtotime($arStrLog['tail']['DATE'] . ' midnight') : 0;

            $iLimitFrom = $iLimitTo = 0;

            // если начальная дата заполнена в фильтре или логе
            if (!empty($this->arFilter['date_from'])) {
                $iLimitFrom = (!empty($arStrLog['head']['DATE']) && $iLogFrom > $iFilterFrom) ? $iLogFrom : $iFilterFrom;
            } elseif (!empty($arStrLog['head']['DATE'])) {
                $iLimitFrom = $iLogFrom;
            }

            // если конечная дата заполнена в фильтре или логе
            if (!empty($this->arFilter['date_to'])) {
                $iLimitTo = (!empty($arStrLog['tail']['DATE']) && $iLogTo < $iFilterTo) ? $iLogTo : $iFilterTo;
            } elseif (!empty($arStrLog['tail']['DATE'])) {
                $iLimitTo = $iLogTo;
            }

            // случай когда не прочиталась первая или последняя строка логов
            if ($iLimitFrom && !$iLimitTo) {
                $iLimitTo = $iLimitFrom + 86400 * 365;
            } elseif (!$iLimitFrom && $iLimitTo) {
                $iLimitFrom = $iLimitTo - 86400 * 365;
            }

            while ($iLimitFrom <= $iLimitTo) {
                $arParamFilter['AND']['DATE'][] = date($sDateFormat, $iLimitFrom);
                $iLimitFrom += 86400;
            }

            if (!isset($arParamFilter['AND']['DATE'])) {
                $this->obFileProvider->setSearchEnable(false);
            }
        }

        foreach ($arParamFilter as $sLogic => $arParams) {
            foreach ($arParams as $obFilter) {
                $this->obFileProvider->addFilter($obFilter, $sLogic);
            }
        }
    }

    /**
     * Расчет страниц для постраничной навигации
     */
    private function setPagenCount() {
        $this->iLinesCount = $this->obFileProvider->getFileLinesCount();
        $this->bReverse    = isset($this->arOrder['order']) && $this->arOrder['order'] != 'asc';
        $this->arLim       = self::getOffsetLimit($this->iLinesCount, $this->iPageSize, $this->iPageNumber, $this->bReverse);
    }

    /**
     * Формирование offset и limit
     *
     * @param $iLinesCount
     * @param $iPageSize
     * @param $iPageNumber
     * @param $bReverse
     * @return array
     */
    private static function getOffsetLimit($iLinesCount, $iPageSize, $iPageNumber, $bReverse) {
        $iLimit      = $iPageSize > 500 ? 500 : $iPageSize;
        $iCorrectNum = ($iPageNumber - 1) * $iLimit + 1;
        $iOffset     = $bReverse ? ($iLinesCount - $iLimit - $iCorrectNum + 2) : $iCorrectNum;
        $iLimit     += $iOffset-1;
        $iOffset     = $iOffset < 1 ? 1 : $iOffset; // при обратном проходе на последней странице
        return array('offset' => $iOffset, 'limit' => $iLimit);
    }

    /**
     * Получение массива строк лога и преобразование его в результат
     */
    private function getStrFromLog() {

        $this->arStrings = $this->obFileProvider->getStringsFromLog($this->arLim['offset'], $this->arLim['limit'], $this->bReverse);
        $this->arError   = array_merge($this->arError,  $this->obFileProvider->getErrorList());
        $this->arNotice  = array_merge($this->arNotice, $this->obFileProvider->getNoticeList());

        $arQueryList = $this->obFileProvider->getQueryList();
        if ($this->bShowQueries && !empty($arQueryList)) {
            array_unshift($arQueryList, BxHelper::getMessage('BM_LA_MESS_QUERIES'));
            $this->arNotice = array_merge($this->arNotice, $arQueryList);
        }

        switch ($this->sType) {
            case 'apache':  $this->arStrings = $this->obParser->apacheLog($this->arStrings, $this->bErrorLog); break;
            case 'nginx':   $this->arStrings = $this->obParser->nginxLog($this->arStrings, $this->bErrorLog); break;
            case 'mysql':   $this->arStrings = $this->obParser->mysqlLog($this->arStrings); break;
            case 'php':     $this->arStrings = $this->obParser->phpLog($this->arStrings); break;
            case 'cron':    $this->arStrings = $this->obParser->cronLog($this->arStrings); break;
            case 'mail':    $this->arStrings = $this->obParser->mailLog($this->arStrings); break;
            case 'bitrix':  $this->arStrings = $this->obParser->bitrixLog($this->arStrings); break;
            case 'symfony': $this->arStrings = $this->obParser->symfonyLog($this->arStrings); break;
            case 'yii':     $this->arStrings = $this->obParser->yiiLog($this->arStrings); break;
        }
        
        foreach ($this->arStrings as $arItem) {
            if (2 == count($arItem)) {
                $this->arError[] = BxHelper::getMessage('BM_LA_MESS_PARSER_ERROR')
                                    . ': ' . BxHelper::getMessage('BM_LA_MESS_UNIQUE_LOG');
                break;
            }
        }

        $arParseError = $this->obParser->getErrorList();
        if (!empty($arParseError)) {
            array_unshift($arParseError, BxHelper::getMessage('BM_LA_MESS_PARSER_ERROR') . ': ');
            $this->arError = array_merge($this->arError, $arParseError);
        }
    }
}