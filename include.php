<?
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

if (CheckVersion(SM_VERSION, '14.00.00')) {

    \Bitrix\Main\Loader::registerAutoLoadClasses('bxleader.loganalyzer', array(
        'BxLeader\LogAnalyzer\Provider\FileProvider'      => 'lib/provider/FileProvider.php',
        'BxLeader\LogAnalyzer\Provider\LinuxFileProvider' => 'lib/provider/LinuxFileProvider.php',
        'BxLeader\LogAnalyzer\Provider\PhpFileProvider'   => 'lib/provider/PhpFileProvider.php',
        'BxLeader\LogAnalyzer\Provider\DataProvider'      => 'lib/provider/DataProvider.php',

        'BxLeader\LogAnalyzer\Parser\Parser'              => 'lib/parser/Parser.php',
        'BxLeader\LogAnalyzer\Parser\RegExLib'            => 'lib/parser/RegExLib.php',
        'BxLeader\LogAnalyzer\Parser\RegExLibCustom'      => 'lib/parser/RegExLibCustom.php',

        'BxLeader\LogAnalyzer\Utils\AdminListHelper'      => 'lib/utils/AdminListHelper.php',
        'BxLeader\LogAnalyzer\Utils\BxHelper'             => 'lib/utils/BxHelper.php',
        'BxLeader\LogAnalyzer\Utils\MainHelper'           => 'lib/utils/MainHelper.php',
        'BxLeader\LogAnalyzer\Utils\SqlFormatter'         => 'lib/utils/SqlFormatter.php',
    ));
}