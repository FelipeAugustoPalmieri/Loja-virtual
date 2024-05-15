<?php
// HTTP
define('HTTP_SERVER', 'https://loja.jedax.com.br/');
define('URL_APITBEST','https://sistema.jedax.com.br/api/');
define('TOKENTBEST','tbestsistema');
define('TOKENASAAS','f6b758c498aa2cc090f8939fa29175edb2c0707e1d0617611c68245bdeaf10f2');
define('URLASAAS','https://www.asaas.com/api');

// HTTPS
define('HTTPS_SERVER', 'https://loja.jedax.com.br/');

// DIR
define('DIR_APPLICATION', $_SERVER['DOCUMENT_ROOT'].'catalog/');
define('DIR_SYSTEM', $_SERVER['DOCUMENT_ROOT'].'system/');
define('DIR_IMAGE', $_SERVER['DOCUMENT_ROOT'].'image/');
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
define('DB_DRIVER', 'mysql');
define('DB_HOSTNAME', 'mysql.jedax.com.br');
define('DB_USERNAME', 'jedax0303_add2');
define('DB_PASSWORD', 'Db@2024');
define('DB_DATABASE', 'jedax03');
define('DB_PORT', '3306');
define('DB_PREFIX', 'onix_');