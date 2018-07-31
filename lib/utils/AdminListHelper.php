<?php

namespace BxLeader\LogAnalyzer\Utils;

use \Bitrix\Main\Application,
    \Bitrix\Main\Localization\Loc,
    \BxLeader\LogAnalyzer\Utils\BxHelper;

/**
 * Хелпер отрисовки страницы в административном разделе Битрикса
 *
 * @package BxLeader\LogAnalyzer\Utils
 */
class AdminListHelper {

	private $file = '';
	private $tableID = '';
	private $obAdminFilter = null;
	private $arFilter = array();
    private $arOrder = array();
	private $title = '';
	private $obAdminList = null;
	private $obAdminSorting = null;
	private $arNotice = array();

	public function __construct($by = 'N') {

		\CUtil::JSPostUnescape();
        BxHelper::setMemoryLimit(); // memory limit to array_fill in hardcore & hardporn bitrix pagination

        $arDebugBackTrace = debug_backtrace();
		$this->file = basename($arDebugBackTrace[0]['file']);
		$this->tableID = 'tbl_bm_la_'.substr($this->file, 0, -4);

		$this->obAdminSorting = new \CAdminSorting($this->tableID, $by, 'DESC');
		$this->obAdminList = new \CAdminList($this->tableID, $this->obAdminSorting);
	}

	public function getAdminList() {
		return $this->obAdminList;
	}

	public function getTableID() {
		return $this->tableID;
	}

	public function setTitle($title) {
		$this->title = $title;
		$this->Application()->SetTitle($this->title);
	}

	public function setNotice($arNotice = array()) {
        $this->arNotice = $arNotice;
    }

    public function setError($arError = array()) {
	    if (!empty($arError)) {
            $this->obAdminList->AddFilterError(join(PHP_EOL, $arError));
        }
    }

    /**
     * Установка заголовков
     *
     * @param array $arStrings
     */
    public function setHeaders($arStrings) {

        // сортировка строк по количеству полей от большей к меньшей
        usort($arStrings, function ($a, $b) {
            return count($a) > count($b) ? -1 : 1;
        });

        $arTmpCells = $arHeaders = array();
        foreach ($arStrings as $arEntity) {
            foreach ($arEntity as $k => $v) {
                $arTmpCells[$k] = (isset($arTmpCells[$k]) ? $arTmpCells[$k] : 0) + ($v != '');
            }
        }

        foreach ($arTmpCells as $k => $v) {
            if ($v) {
                if ('N' == $k) {
                    $arHeaders[] = array('id' => $k, 'content' => Loc::getMessage('BM_LA_NUMBER'),
                                        'sort' => $k, 'default' => true, 'align' => 'right');
                } else {
                    $arHeaders[] = array('id' => $k, 'content' => $k, 'default' => true);
                }
            }
        }

        if (!empty($arHeaders)) {
            $this->obAdminList->AddHeaders($arHeaders);
        }
    }

    /**
     * Формирование результирующего списка
     *
     * @param $arStrings
     * @param $iLinesCount
     * @param array $arVisual
     */
	public function setList($arStrings, $iLinesCount, $arVisual = array()) {

        $obPagen = new \CDBResult;
        $obPagen->InitFromArray($iLinesCount ? array_fill(0, $iLinesCount, null) : array());

		$obAdminResult = new \CAdminResult($obPagen, $this->tableID);
        $obAdminResult->NavStart();
		$this->obAdminList->NavText($obAdminResult->GetNavPrint(Loc::getMessage('BM_LA_PAGEN_TITLE')));
		unset($obPagen);

        foreach ($arStrings as $iStrNum => $arRes) {

			$row = &$this->obAdminList->AddRow($iStrNum, $arRes);

			if (!empty($arVisual)) { // визуальное оформление
				foreach ($arVisual as $code => $action) {
	                if (isset($arRes[$code])) {
                        $row->AddViewField($code, call_user_func($action, $arRes[$code], $arRes));
                    }
				}
			}
		}
	}

    /**
     * Установка количества элементов на страницу
     *
     * @return int
     */
    public final function makePageSize() {
        $obAdminResult = new \CAdminResult(new \CDBResult(), $this->tableID);
        return $obAdminResult->GetNavSize();
    }

    /**
     * Установка номера страницы
     *
     * @return int
     */
    public final function makePageNumber() {
        return isset($_REQUEST['PAGEN_1']) ? intval($_REQUEST['PAGEN_1']) : 1;
    }

    /**
     * Установка сортировки
     *
     * @param $by
     * @param $order
     * @return array
     */
    public final function makeOrder($by, $order) {
        return $this->arOrder = array('by' => $by, 'order' => $order);
    }

    /**
     * Установка фильтров
     *
     * @return array
     */
	public function makeFilter() {
		$arFilter = array();
		foreach ($this->arFilter as $k => $arItem) {
			if (isset($arItem['TYPE']) && 'calendar' == $arItem['TYPE']) {
				if (isset($arItem['VALUE1']) && strlen($arItem['VALUE1'])) {
					$arFilter[$k . '_from'] = $arItem['VALUE1'];
				}
				if (isset($arItem['VALUE2']) && strlen($arItem['VALUE2'])) {
					$arFilter[$k . '_to'] = $arItem['VALUE2'];
				}
			}
			elseif ('content_type' == $k) {
				if(isset($arItem['VALUE']) && strlen($arItem['VALUE']) <= 0){
					$arItem['VALUE'] = array_keys($arItem['VARIANTS']);
				}
				$arFilter[$k] = $arItem['VALUE'];
			}
			else {
				if (isset($arItem['VALUE']) && strlen($arItem['VALUE'])) {
					$arFilter[$k] = $arItem['VALUE'];
				}
			}
		}

		return $arFilter;
	}

    /**
     * Фильтр
     *
     * @param $arFilter
     */
	public function setFilter($arFilter) {

		$this->arFilter = $arFilter;
		$arTitles = $arInit = array();
		foreach ($arFilter as $k => $arItem) {
			$arTitles[$k] = $arItem['TITLE'];
			if (isset($arItem['TYPE']) && 'calendar' == $arItem['TYPE']) {
				$arInit[] = 'find_'.$k.'1';
				$arInit[] = 'find_'.$k.'2';
			}
			else {
				$arInit[] = 'find_'.$k;
			}
		}
		$this->obAdminList->InitFilter($arInit);
		$this->obAdminFilter = new \CAdminFilter($this->tableID . '_filter', $arTitles);

		$arSessionVars = isset($_SESSION['SESS_ADMIN'][$this->tableID]) ? $_SESSION['SESS_ADMIN'][$this->tableID] : array();
		foreach ($this->arFilter as $k => &$arItem) {
            if (isset($arItem['TYPE']) && 'calendar' == $arItem['TYPE']) {

                $arItem['VALUE1'] = isset($_REQUEST['find_'.$k.'1']) && !isset($_REQUEST['del_filter'])
                    ? $_REQUEST['find_'.$k.'1']
                    : (isset($arSessionVars['find_'.$k.'1']) ? $arSessionVars['find_'.$k.'1'] : '');

                $arItem['VALUE2'] = isset($_REQUEST['find_'.$k.'2']) && !isset($_REQUEST['del_filter'])
                    ? $_REQUEST['find_'.$k.'2']
                    : (isset($arSessionVars['find_'.$k.'2']) ? $arSessionVars['find_'.$k.'2'] : '');
			}
			else {
				$value = isset($_REQUEST['find_'.$k]) && !isset($_REQUEST['del_filter'])
                    ? $_REQUEST['find_'.$k] : (isset($arSessionVars['find_'.$k]) ? $arSessionVars['find_'.$k] : '');
				$arItem['VALUE'] = $value;
			}
		}
	}

    /**
     * Вывод
     */
	public function output() {
	    
		global $APPLICATION, $adminPage, $USER, $adminMenu, $adminChain;

        if (!empty($this->arNotice) && isset($_REQUEST['mode']) && 'list' == $_REQUEST['mode']) { // вывод нотисов
            echo BeginNote() . nl2br(join(PHP_EOL.PHP_EOL, $this->arNotice)) . EndNote();
        }

        $this->obAdminList->AddAdminContextMenu(array(), true, false); // панель инструментов (только excel)
        $this->obAdminList->CheckListMode();

		require(Application::getDocumentRoot() . BX_ROOT . '/modules/main/include/prolog_admin_after.php');
		if ($this->obAdminFilter !== null) {
			$this->outputFilter();
		}

		$this->obAdminList->DisplayList();

		require(Application::getDocumentRoot() . BX_ROOT . '/modules/main/include/epilog_admin.php');
	}

    private function application() {
        return $GLOBALS['APPLICATION'];
    }

    private function outputFilter() {
        $url = $this->Application()->GetCurPage();
        ?><form name="find_form" method="get" action="<?= $url;?>"><?
        $this->obAdminFilter->Begin();
        foreach ($this->arFilter as $k => $arItem) {
            if (isset($arItem['TYPE'])) {
                $type = $arItem['TYPE'];
            } else {
                $type = 'text';
            }
            if ($type=='select' && !isset($arItem['VARIANTS']) && is_array($arItem['VARIANTS'])) {
                $type = 'text';
            }
            ?>
            <tr>
                <td nowrap><?= $arItem['TITLE']?>:</td>
                <td nowrap>
                    <? if ($type == 'select'): ?>
                        <select <?if(isset($arItem['MULTIPLE']) && $arItem['MULTIPLE'] == 'Y'):
                            ?> multiple="multiple"<? endif; ?> name="find_<?= $k?>">
                            <? if (isset($arItem['VARIANTS'])): ?>
                                <?foreach ($arItem['VARIANTS'] as $kv => $vv):?>
                                    <option value="<?= $kv?>"<?if ($arItem['VALUE'] == $kv){?> selected="selected"<?}?>><?= $vv?></option>
                                <?endforeach;?>
                            <? endif; ?>
                        </select>
                    <? elseif ($type == 'calendar'): ?>
                        <?= CalendarPeriod(
                            'find_'.$k.'1', $arItem['VALUE1'],
                            'find_'.$k.'2', $arItem['VALUE2'],
                            'find_form'
                        );?>
                    <? elseif($type == 'checkbox'): ?>
                        <input name="find_<?= $k?>" type="checkbox" value="Y" <? if($arItem["VALUE"] == "Y"){ echo "checked"; } ?>>
                    <? else: ?>
                        <input type="text" name="find_<?= $k?>" value="<?echo htmlspecialcharsbx($arItem['VALUE'])?>" />
                    <? endif; ?>
                </td>
            </tr>
            <?
        }
        $this->obAdminFilter->Buttons(array('table_id' => $this->tableID, 'url' => $url, 'form' => 'find_form'));
        $this->obAdminFilter->End();
        ?></form><?
    }
}