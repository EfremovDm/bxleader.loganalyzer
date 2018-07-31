<?

// install
$MESS['BM_LA_MODULE_INSTALL']         = 'Installing the Log Analyzer Module';
$MESS['BM_LA_MODULE_NOT_INSTALLED']   = 'Module #MODULE_ID# is not installed!';
$MESS['BM_LA_INSTALL_SUCCESS']        = 'Module successfully installed';
$MESS['BM_LA_UNINSTALL_SUCCESS']      = 'The module was successfully removed from the system';
$MESS['BM_LA_INSTALL_LAST_MSG']       = '<b>We will be grateful to suggestions and donates for improving the module. For all questions please contact to email: <a href="mailto:#EMAIL#">#EMAIL#</a>';
$MESS['BM_LA_UNINSTALL_LAST_MSG']     = 'You have uninstalled the module. We will be very grateful if you write the reason for the deletion. <br>This will help us in the future to be better. Thank you!';
$MESS['BM_LA_MODULE_INSTALL_ADMIN']   = 'Installation of the module is allowed only for the site administrator!';
$MESS['BM_LA_MODULE_INSTALL_ALREADY'] = 'This module is already installed!';
$MESS['BM_LA_MODULE_INSTALL_PHP']     = 'This module can not be run on the PHP version below #VERSION#';
$MESS['BM_LA_MODULE_INSTALL_BX']      = 'This module can not be run on the Bitrix main module version below #VERSION#';
$MESS['BM_LA_MODULE_INSTALL_EXEC']    = 'To use the Linux data provider, enable exec ()!';
$MESS['BM_LA_MODULE_INSTALL_ZLIB']    = 'When using a PHP provider, you need to connect the ZLib library to work with .gz files!';
$MESS['BM_LA_MODULE_INSTALL_MLIMIT']  = 'For the correct operation of page navigation with large logs file size, you need to increase the php_value memory_limit to 1024M!';
$MESS['BM_LA_MODULE_INSTALL_DENIED']  = 'Access denied';
$MESS['BM_LA_MODULE_INSTALL_OPENED']  = 'Access opened';
$MESS['BM_LA_MODULE_INSTALL_INSTALL'] = 'Install module';
$MESS['BM_LA_MODULE_INSTALL_BACK']    = 'Back to modules';

// menu
$MESS['BM_LA_MAIN']                  = 'Log Analyzer';
$MESS['BM_LA_DESCRIPTION']           = 'The module allows a clever interface for log analysis';
$MESS['BM_LA_APACHE']                = 'Apache Web Server';
$MESS['BM_LA_NGINX']                 = 'Nginx Web Server';
$MESS['BM_LA_MYSQL']                 = 'MySQL database server';
$MESS['BM_LA_MARIADB']               = 'Database Server MariaDB';
$MESS['BM_LA_PERCONA']               = 'Database Percona Server';
$MESS['BM_LA_PHP']                   = 'PHP Interpreter';
$MESS['BM_LA_CRON']                  = 'Cron Scheduler';
$MESS['BM_LA_MAIL']                  = 'Mail daemon';
$MESS['BM_LA_BITRIX']                = '1C-Bitrix CMS';
$MESS['BM_LA_SYMFONY']               = 'Symfony Framework';
$MESS['BM_LA_YII']                   = 'Yii Framework';

// settings
$MESS['BM_LA_SETTINGS_MAIN']          = 'Main settings';
$MESS['BM_LA_SETTINGS_DOCUMENTATION'] = 'Documentation';
$MESS['BM_LA_SETTINGS_DONATE']        = 'Danete';
$MESS['BM_LA_SETTINGS_ACCESS']        = 'Access';

$MESS['BM_LA_COMMON']                = 'Are common';
$MESS['BM_LA_FILE_PROVIDER']         = 'The file system provider';
$MESS['BM_LA_FILE_PROVIDER_LINUX']   = 'Linux (preferably)';
$MESS['BM_LA_FILE_PROVIDER_PHP']     = 'PHP (slow)';
$MESS['BM_LA_FILE_BITRIX_ERROR']     = 'To enable the analysis of 1C-Bitrix logs, add their addresses in the settings';

$MESS['BM_LA_SHOW_QUERIES']          = 'Show requests to the file system';
$MESS['BM_LA_ENABLE']                = 'Enable log analysis';
$MESS['BM_LA_LOG_DIR']               = 'Log storage directory';
$MESS['BM_LA_LOG_PREFIX']            = 'File name prefix';
$MESS['BM_LA_LOG_BITRIX']            = 'Log file addresses';
$MESS['BM_LA_LOG_DIR_RECURSIVE']     = 'Recursively subdirectories<br /><small>Use only if you are sure of a small nesting directory</small></s>';
$MESS['BM_LA_SELECT_DIR']            = 'Specify a directory for ';
$MESS['BM_LA_DEVELOPER_EMAIL']       = join('@', ['efremovdm', 'gmail.com']);
$MESS['BM_LA_NUMBER']                = '№';

// file size
$MESS['BM_LA_FILE_SIZE_b']  = 'b';
$MESS['BM_LA_FILE_SIZE_Kb'] = 'Kb';
$MESS['BM_LA_FILE_SIZE_Mb'] = 'Mb';
$MESS['BM_LA_FILE_SIZE_Gb'] = 'Gb';
$MESS['BM_LA_FILE_SIZE_Tb'] = 'Tb';

// filter
$MESS['BM_LA_FILTER_SELECT_LOGFILE'] = 'Select the log file';
$MESS['BM_LA_FILTER_IP']             = 'IP';
$MESS['BM_LA_FILTER_DATE']           = 'Date';
$MESS['BM_LA_FILTER_INCLUDE']        = 'Other value to include';
$MESS['BM_LA_FILTER_EXCLUDE']        = 'Other excluded value';

// title
$MESS['BM_LA_APACHE_TITLE']          = 'Analyzing the logs of the Apache web server';
$MESS['BM_LA_NGINX_TITLE']           = 'Analyzing the logs of the Nginx web server';
$MESS['BM_LA_MYSQL_TITLE']           = 'Analyzing the logs of the MySQL database server';
$MESS['BM_LA_MARIADB_TITLE']         = 'Analyzing the logs of the MariaDB database server';
$MESS['BM_LA_PERCONA_TITLE']         = 'Analyzing the logs of the Percona database server';
$MESS['BM_LA_PHP_TITLE']             = 'Analyzing the logs of the PHP';
$MESS['BM_LA_CRON_TITLE']            = 'Analyzing the logs of the Cron Task Scheduler';
$MESS['BM_LA_MAIL_TITLE']            = 'Analyzing the logs of the mail daemon';
$MESS['BM_LA_BITRIX_TITLE']          = 'Analyzing the logs of the 1C-Bitrix CMS';
$MESS['BM_LA_SYMFONY_TITLE']         = 'Analyzing the logs of the Symfony Framework';
$MESS['BM_LA_YII_TITLE']             = 'Analyzing the logs of the Yii Framework';
$MESS['BM_LA_QUERIES']               = 'Queries';
$MESS['BM_LA_PAGEN_TITLE']           = 'Rows';

// mess
$MESS['BM_LA_MESS_QUERIES']          = 'Queries';
$MESS['BM_LA_MESS_PARSER_ERROR']     = 'Parser error';
$MESS['BM_LA_MESS_UNIQUE_LOG']       = 'You have unique log construction! Please add custom reqular expression for MESSAGE lines in class RegExLibCustom.php!';
$MESS['BM_LA_MESS_DONATE']           = '<p>Donate developer:</p>';
$MESS['BM_LA_MESS_FEEDBACK']         = 'Feedback';
