<?php
/**
 * @file Loader.php
 * @author wanghe (hihu@qq.com)
 **/

//自动加载,有下划线的类按照下划线目录加载，没有下划线的去util和lib一级目录加载
//如果lib和util需要很多倒出类，而且新建了二级目录，可以用下划线方式加载
function _pi_autoloader_core($class){
	if(($pos = strpos($class,'_')) !== false){
		$class = explode('_',$class);
		if(empty($class)) return false;
		$first_dir = strtolower($class[0]);
		$fileName = array_pop($class);
		$class = array_map('strtolower',$class);
		$root = ($first_dir == 'util') ? PI_ROOT : COM_ROOT;
		$file = $root.implode(DOT,$class).DOT.$fileName.'.php';
		Pi::inc($file);
	}else{
		//优先加载工程中的lib,其次加载框架中的util
		if(is_readable(PI_UTIL.$class.'.php')){
			Pi::inc(PI_UTIL.$class.'.php');
		}else if(is_readable(COM_ROOT.'lib/'.$class.'.php')){
			Pi::inc(COM_ROOT.'lib/'.$class.'.php');
		}
	}
}

//注册自动加载函数
if(function_exists('spl_autoload_register')){
	spl_autoload_register('_pi_autoloader_core');
}