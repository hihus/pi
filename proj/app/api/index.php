<?php
//酌情去掉的配置
define('TIMEZONE','Asia/Shanghai');
define("__PI_EN_DEBUG",1);
//必要配置
define('PI_ROOT',dirname(dirname(dirname(__FILE__))).'/pi/');
define('PI_APP_ROOT',dirname(dirname(__FILE__)).'/');
define('PI_COM_ROOT',PI_APP_ROOT.'com/');
define('PI_APP_NAME','api');
define('LOG_PATH','/tmp/');

include(PI_ROOT.'app/Api.php');

//api项目需要的框架配置
pi::set('global.logFile','api');

//代码环境 - 可选
// pi::set('com_env','dev');
// pi::set('app_env','dev');

//自定义类可以重构提供的基础ApiApp功能
class PApiApp extends ApiApp {}

$app = new PApiApp($argv);
$app->run();