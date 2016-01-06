<?php
/**
 * @file Config.inc.php
 * @author wanghe (hihu@qq.com)
 **/

//rpc内部调用或者网络错误的返回err_code标识
if(!defined('INNER_ERR')) define('INNER_ERR','_pi_inner_err_code');
if(!defined('INNER_RES_PACK')) define('INNER_RES_PACK','_pi_inner_err_code');

//inner api sign
Pi::set('global.innerapi_sign','kjsdgiu3kiusdf982o3sdfo034s');
Pi::set('global.innerapi_sign_name','_pi_inner_nm');

//db and cache and log
Pi::set('DbLib',PI_UTIL.'db'.DOT.'db.php');
Pi::set('MemcacheLib',PI_UTIL.'cache'.DOT.'Memcache.php');
Pi::set('RedisLib',PI_UTIL.'cache'.DOT.'Redis.php');
Pi::set('LogLib',PI_UTIL.'log'.DOT.'Log.php');

//其他配置

