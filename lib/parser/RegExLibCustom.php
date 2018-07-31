<?

namespace BxLeader\LogAnalyzer\Parser;

/**
 * Библиотека пользовательских регулярных выражений
 *
 * В данный класс вы можете поместить ваши пользовательские уникальные регулярные выражения для уникально настроенных логов.
 * Данный файл не будет обновляться вместе с обновлением модуля, соответственно все ваши регулярки сохранятся.
 *
 * @package    BxLeader\LogAnalyzer\Parser
 * @author     Efremov Dmitriy
 * @copyright  2018 Efremov Dmitriy
 * @license    https://opensource.org/licenses/MIT
 * @link       http://github.com/efremovdm/loganalyzer
 * @version    1.0.0
 */
class RegExLibCustom
{
    public static function getRegEx() {
        return array(); // add your pattern
    }

    /**
     * Массив форматов дат для формирования шаблонов при фильтрации
     *
     * @return array
     */
    public static function getDateFormat() {
        return array(); // add your pattern
    }
}