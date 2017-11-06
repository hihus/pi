<?php
//酌情去掉的配置
define('TIMEZONE','Asia/Shanghai');
//define("__PI_EN_DEBUG",1);
//必要配置
define('PI_APP_NAME','web');
define('PI_ROOT',dirname(dirname(dirname(dirname(__FILE__)))).'/pi/');
define('PI_APP_ROOT',dirname(dirname(__FILE__)).'/');
define('PI_COM_ROOT',dirname(dirname(dirname(__FILE__))).'/com/');
define('APP_CONF_PATH',PI_APP_ROOT.PI_APP_NAME.'/conf/');
define('APP_CTR_ROOT',PI_APP_ROOT.PI_APP_NAME.'/ctr/');
define('COMPOSER_LOADER',dirname(dirname(dirname(__FILE__))).'/vendor/autoload.php');
define('LOG_PATH','/tmp/');

include(PI_ROOT.'app/Web.php');

//web项目需要的框架配置
pi::set('global.logFile','web');
//代码环境 - 可选
//pi::set('com_env','dev');
//pi::set('app_env','dev');

//自定义类可以重构提供的基础WebApp功能
class PWebApp extends WebApp {}

$app = new PWebApp();
$app->run();
