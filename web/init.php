<?php

define('DEVICE', 'pc');

define('APP_DIR',  dirname(dirname(__FILE__)));
define('WEB_DIR',  dirname(dirname(__FILE__)) . '/web');
define('DATA_DIR', dirname(dirname(__FILE__)) . '/data');
define('ICON_DIR', dirname(dirname(__FILE__)) . '/icon');

define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/');

define('TEMPLATE_DIR', WEB_DIR . '/templates');
define('COMPILE_DIR',  WEB_DIR . '/templates_c');

ini_set('include_path', ini_get('include_path') . ":" . APP_DIR . "/class");

if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
 }
 else {
     error_reporting(E_ALL ^ E_NOTICE);
     ini_set('display_errors', 0);
 }

mb_language("ja");
mb_internal_encoding("UTF-8");

session_start();

?>
