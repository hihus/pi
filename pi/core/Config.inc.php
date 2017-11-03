<?php
/**
 * @file Config.inc.php
 * @author hihu (hihu@qq.com)
 **/

define('DOT',DIRECTORY_SEPARATOR);
define('PI_CORE',PI_ROOT.'core'.DOT);
define('PI_UTIL',PI_ROOT.'util'.DOT);
define('SERVICE_ROOT',PI_COM_ROOT.'service'.DOT);

if(!defined('COM_CONF_PATH')) define('COM_CONF_PATH',PI_COM_ROOT.'conf'.DOT);

//rpc内部调用或者网络错误的返回err_code标识
if(!defined('PI_INNER_ERR')) define('PI_INNER_ERR','_pi_inner_err_code');
if(!defined('INNER_RES_PACK')) define('INNER_RES_PACK','_pi_inner_content');

//通用一次性salt
pi::set('global.inner_tmp_salt','ks92pi');

//inner api sign
pi::set('global.innerapi_sign','kjsdgiu3kiusdf982o3sdfo034s');
pi::set('global.innerapi_sign_name','_pi_inner_nm');

