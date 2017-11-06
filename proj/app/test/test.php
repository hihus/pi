<?php
define('TIMEZONE','Asia/Shanghai');
define("__PI_EN_DEBUG",1);
define('PI_ROOT',dirname(dirname(dirname(dirname(__FILE__)))).'/pi/');
define('PI_COM_ROOT',dirname(dirname(dirname(dirname(__FILE__)))).'/proj/com/');
define('COMPOSER_LOADER',dirname(dirname(dirname(__FILE__))).'/vendor/autoload.php');

include(PI_ROOT.'core/Pi.php');
$login = pi::com('login');
$login->dologin(array("hihu","coldsolo"));


