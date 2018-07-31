<?

namespace BxLeader\LogAnalyzer\Utils;

/**
 * Утилиты общего назначения для работы модуля
 *
 * @package    BxLeader\LogAnalyzer\Utils
 * @author     Efremov Dmitriy
 * @copyright  2018 Efremov Dmitriy
 * @license    https://opensource.org/licenses/MIT
 * @link       http://github.com/efremovdm/loganalyzer
 * @version    1.0.0
 */
class MainHelper
{
    const MODULE_ID = 'bxleader.loganalyzer';

    /**
     * Проверка операционной системы -> Windows?
     *
     * @return string
     */
    public static function isWindows() {
        return 'WIN' === strtoupper(substr(PHP_OS, 0, 3));
    }

    /**
     * Проверка библиотеки на подключение в PHP
     *
     * @param $sLib
     * @return bool
     */
    public static function checkLoadedLib($sLib) {
        return in_array($sLib, get_loaded_extensions());
    }

    /**
     * Подготовка поля 'MESSAGE' к выводу в административной таблице
     *
     * @param $str
     * @return mixed
     */
    public static final function prepareMessage($str) {
        return str_replace(array(" ", "\t", "\n"), array("&nbsp;", "&nbsp;&nbsp;&nbsp;", "<br />"), $str);
    }

    /**
     * Получение списка доступных провайдеров файлов
     *
     * @return array
     */
    public static function getFileProviders() {
        if (self::isWindows() || !function_exists('exec')) {
            return array('php' => true, 'linux' => false);
        } else {
            return array('linux' => true, 'php' => true);
        }
    }

    /**
     * Получение файлов логов хостера Timeweb
     *
     * @return string
     */
    public static function getTimewebLog() {
        $arPath = explode('/', $_SERVER['DOCUMENT_ROOT']);
        array_pop($arPath);
        $sPath = join('/', $arPath);
        if (!file_exists($sPath . '/access_log') && !file_exists($sPath . '/error_log')) {
            $sPath = '';
        }
        return $sPath;
    }

    /**
     * Получение массива технологий
     *
     * @return array
     */
    public static function getTech() {
        return array('apache', 'nginx', 'mysql', 'php', 'cron', 'mail', 'bitrix', 'symfony', 'yii');
    }

    /**
     * Преобразование README.md в html-вид
     *
     * @param $sDir
     * @return string
     */
    public static function getReadMeHtml($sDir) {
        $sReadMe = file_get_contents($sDir . '/README.md');
        if (defined('BX_UTF') && BX_UTF === true) {
            $sReadMe = @iconv('windows-1251', 'utf-8', $sReadMe);
        }
        $sReadMe = preg_replace('{^(\#{2,6})[ ]*(.+?)[ ]*\#*	\n+}xm','<h3>$2</h3>', $sReadMe);
        $sReadMe = nl2br(str_replace('```', '', $sReadMe));
        return $sReadMe;
    }
}