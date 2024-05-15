<?php
// HTTP
define('HTTP_SERVER', 'http://loja.jedax.com.br/');
define('URL_APITBEST','http://sistema.jedax.com.br/api/');
define('TOKENTBEST','tbestsistema');

// HTTPS
define('HTTPS_SERVER', 'https://loja.jedax.com.br/');

// DIR
define('DIR_APPLICATION', '/home/jedax/www/jedax/loja2/catalog/');
define('DIR_SYSTEM', '/home/jedax/www/jedax/loja2/system/');
define('DIR_IMAGE', '/home/jedax/www/jedax/loja2/image/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'mysql.jedax.com.br');
define('DB_USERNAME', 'jedax123');
define('DB_PASSWORD', 'jedax123');
define('DB_DATABASE', 'jedax03');
define('DB_PORT', '3306');
define('DB_PREFIX', 'onix_');