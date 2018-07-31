<?

require_once __DIR__ . '/../lib/utils/MainHelper.php';
require_once __DIR__ . '/../lib/utils/BxHelper.php';

use \BxLeader\LogAnalyzer\Utils\BxHelper;

$obBxHelper = new BxHelper();
$obBxHelper->checkRequirements();
$obBxHelper->checkModuleAccess();
$obBxHelper->loadMessages();
$obBxHelper->includeModule();
