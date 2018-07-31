<?

// install
$MESS['BM_LA_MODULE_INSTALL']         = 'Установка модуля анализатора логов';
$MESS['BM_LA_MODULE_NOT_INSTALLED']   = 'Модуль #MODULE_ID# не установлен!';
$MESS['BM_LA_INSTALL_SUCCESS']        = 'Модуль успешно установлен';
$MESS['BM_LA_UNINSTALL_SUCCESS']      = 'Модуль успешно удален из системы';
$MESS['BM_LA_INSTALL_LAST_MSG']       = '<b>Будем благодарны предложениям и донатам для улучшеня модуля. По всем вопросам обращайтесь на почту: <a href="mailto:#EMAIL#">#EMAIL#</a>';
$MESS['BM_LA_UNINSTALL_LAST_MSG']     = 'Вы удалили модуль. Мы будем очень благодарны, если напишите причину удаления. <br>Это поможет нам в будущем быть лучше. Спасибо!';
$MESS['BM_LA_MODULE_INSTALL_ADMIN']   = 'Установка модуля разрешена только для администратора сайта!';
$MESS['BM_LA_MODULE_INSTALL_ALREADY'] = 'Данный модуль уже установлен!';
$MESS['BM_LA_MODULE_INSTALL_PHP']     = 'Данный модуль не может исполняться на версии PHP ниже #VERSION#';
$MESS['BM_LA_MODULE_INSTALL_BX']      = 'Данный модуль не может исполняться на версии главного модуля Битрикс ниже #VERSION#';
$MESS['BM_LA_MODULE_INSTALL_EXEC']    = 'Чтобы воспользовать провайдером данных Linux разрешите исполнение функции exec()!';
$MESS['BM_LA_MODULE_INSTALL_ZLIB']    = 'При использовании провайдера PHP необходимо подключить библиотеку ZLib для возможности работы с .gz-файлами!';
$MESS['BM_LA_MODULE_INSTALL_MLIMIT']  = 'Для корректной работы постраничной навигации на логах больших объемов требуется увеличение php_value memory_limit до 1024M!';
$MESS['BM_LA_MODULE_INSTALL_DENIED']  = 'Доступ закрыт';
$MESS['BM_LA_MODULE_INSTALL_OPENED']  = 'Доступ открыт';
$MESS['BM_LA_MODULE_INSTALL_INSTALL'] = 'Установить модуль';
$MESS['BM_LA_MODULE_INSTALL_BACK']    = 'Вернуться в список модулей';

// menu
$MESS['BM_LA_MAIN']                  = 'Анализатор логов';
$MESS['BM_LA_DESCRIPTION']           = 'Модуль позволяет анализировать содержимое логов через интерфейс Битрикса';
$MESS['BM_LA_APACHE']                = 'Веб-сервер Apache';
$MESS['BM_LA_NGINX']                 = 'Веб-сервер Nginx';
$MESS['BM_LA_MYSQL']                 = 'Сервер БД MySQL';
$MESS['BM_LA_MARIADB']               = 'Сервер БД MariaDB';
$MESS['BM_LA_PERCONA']               = 'Сервер БД Percona';
$MESS['BM_LA_PHP']                   = 'Интерпретатор PHP';
$MESS['BM_LA_CRON']                  = 'Планировщик Cron';
$MESS['BM_LA_MAIL']                  = 'Mail daemon';
$MESS['BM_LA_BITRIX']                = '1С-Битрикс CMS';
$MESS['BM_LA_SYMFONY']               = 'Symfony Framework';
$MESS['BM_LA_YII']                   = 'Yii Framework';

// settings
$MESS['BM_LA_SETTINGS_MAIN']          = 'Основные настройки';
$MESS['BM_LA_SETTINGS_DOCUMENTATION'] = 'Документация';
$MESS['BM_LA_SETTINGS_DONATE']        = 'Поблагодарить';
$MESS['BM_LA_SETTINGS_ACCESS']        = 'Доступ';

$MESS['BM_LA_COMMON']                = 'Общие';
$MESS['BM_LA_FILE_PROVIDER']         = 'Провайдер файловой системы';
$MESS['BM_LA_FILE_PROVIDER_LINUX']   = 'Linux (предпочтительно)';
$MESS['BM_LA_FILE_PROVIDER_PHP']     = 'PHP (медленно)';
$MESS['BM_LA_FILE_BITRIX_ERROR']     = 'Чтобы включить анализ логов 1С-Битрикс, добавьте их адреса в настройках';

$MESS['BM_LA_SHOW_QUERIES']          = 'Показывать запросы к файловой системе';
$MESS['BM_LA_ENABLE']                = 'Включить анализ логов';
$MESS['BM_LA_LOG_DIR']               = 'Директория хранения логов';
$MESS['BM_LA_LOG_PREFIX']            = 'Префикс названия файла';
$MESS['BM_LA_LOG_BITRIX']            = 'Адреса файлов логов';
$MESS['BM_LA_LOG_DIR_RECURSIVE']     = 'Рекурсивно обходить субдиректории<br /><small>используйте только если уверены в малой вложенности директории</small></s>';
$MESS['BM_LA_SELECT_DIR']            = 'Укажите директорию для ';
$MESS['BM_LA_DEVELOPER_EMAIL']       = join('@', array('efremovdm', 'gmail.com'));
$MESS['BM_LA_NUMBER']                = '№';

// file size
$MESS['BM_LA_FILE_SIZE_b']  = 'Б';
$MESS['BM_LA_FILE_SIZE_Kb'] = 'КБ';
$MESS['BM_LA_FILE_SIZE_Mb'] = 'МБ';
$MESS['BM_LA_FILE_SIZE_Gb'] = 'ГБ';
$MESS['BM_LA_FILE_SIZE_Tb'] = 'ТБ';

// filter
$MESS['BM_LA_FILTER_SELECT_LOGFILE'] = 'Выберите файл логов';
$MESS['BM_LA_FILTER_IP']             = 'IP';
$MESS['BM_LA_FILTER_DATE']           = 'Дата';
$MESS['BM_LA_FILTER_INCLUDE']        = 'Прочее включаемое значение';
$MESS['BM_LA_FILTER_EXCLUDE']        = 'Прочее исключаемое значение';

// title
$MESS['BM_LA_APACHE_TITLE']          = 'Анализ логов веб-сервера Apache';
$MESS['BM_LA_NGINX_TITLE']           = 'Анализ логов веб-сервера Nginx';
$MESS['BM_LA_MYSQL_TITLE']           = 'Анализ логов сервера БД MySQL';
$MESS['BM_LA_MARIADB_TITLE']         = 'Анализ логов сервера БД MariaDB';
$MESS['BM_LA_PERCONA_TITLE']         = 'Анализ логов сервера БД Percona Server';
$MESS['BM_LA_PHP_TITLE']             = 'Анализ логов интерпретатора PHP';
$MESS['BM_LA_CRON_TITLE']            = 'Анализ логов планировщика заданий Cron';
$MESS['BM_LA_MAIL_TITLE']            = 'Анализ логов службы отправки почты';
$MESS['BM_LA_BITRIX_TITLE']          = 'Анализ логов 1С-Битрикс CMS';
$MESS['BM_LA_SYMFONY_TITLE']         = 'Анализ логов Symfony Framework';
$MESS['BM_LA_YII_TITLE']             = 'Анализ логов Yii Framework';
$MESS['BM_LA_QUERIES']               = 'Запросы';
$MESS['BM_LA_PAGEN_TITLE']           = 'Строки';

// mess
$MESS['BM_LA_MESS_QUERIES']          = 'Запросы';
$MESS['BM_LA_MESS_PARSER_ERROR']     = 'Ошибка парсера';
$MESS['BM_LA_MESS_UNIQUE_LOG']       = 'Ваш лог имеет не стандартную конструкцию (строки MESSAGE), по-этому к нему не применима библиотека стандарных регулярных выражений!
Для того чтобы иметь качественный разбор лога добавьте соответствующее выражение в класс RegExLibCustom.php (при обновлениях модуля не перезатрется)!';
$MESS['BM_LA_MESS_DONATE']           = '<p>Вы используете бесплатный модуль.</p>
<p>Как известно на все в этом мире на реализацию любых проектов требуются средства, и если вы хотите
чтобы модуль обновлялся, а так же мог анализировать <br />новые формы логов, внедрялся новый функционал
по просьбам пользователей, поддерживате пожалуйста автора материально.
Ваша поддержка очень важна!</p>';
$MESS['BM_LA_MESS_FEEDBACK']         = 'Обратная связь';
