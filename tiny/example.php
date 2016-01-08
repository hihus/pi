<?php

//酌情去掉的配置
define('TIMEZONE','Asia/Shanghai');
define("__PI_EN_DEBUG",1);
//必要配置
define('PI_ROOT',dirname(__FILE__).'/');
define('PI_COM_ROOT',PI_ROOT.'com/');
define('LOG_PATH','/tmp/');

include(PI_ROOT.'core/Pi.php');

$login = Pi::com('login');
$res = $login->dologin(array('111'=>1241,'hihu'=>1241));
print_r($res);

