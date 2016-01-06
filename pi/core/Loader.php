<?php
/**
 * @file Loader.php
 * @author wanghe (hihu@qq.com)
 **/

//加载com模块的函数
function picom($mod,$add = '',$is_server = false){
	$mod = strtolower($mod);
	$add = strtolower($add);
	//加载一次
	static $loaded_mod = array();
	if(isset($loaded_mod[$mod.$add])){
		return $loaded_mod[$mod.$add];
	}
	//先检测有没有远程配置，如果没有直接加载类，提高效率
	$conf_add = ($add == '') ? '' : '#'.$add;
	$conf_name = 'proxy.'.strtolower($mod).$conf_add;
	$proxy_conf = Pi::get($conf_name,array());
	if($is_server === false && !empty($proxy_conf)){
		//proxy代理类,根据更详细的配置选择哪个接口走远程
		$class = new PiProxy($mod,$add,$proxy_conf);
		$loaded_mod[$mod.$add] = $class;
	}else{
		//直接加载本地逻辑接口
		$loaded_mod[$mod.$add] = pi_load_export_file($mod,$add);
	}
	return $loaded_mod[$mod.$add];
}

//加载export文件的公用方法
function pi_load_export_file($mod,$add){
	if($add == ''){
		$cls = ucfirst($mod).'Export';
		$file = EXPORT_ROOT.$mod.DOT.$cls.'.php';
	}else if(is_string($add)){
		$cls = ucfirst($mod).ucfirst($add).'Export';
		$file = EXPORT_ROOT.$mod.DOT.$cls.'.php';
	}else{
		throw new Exception('picom can not find mod:'.$mod,',add:'.$add,1001);
	}

	if(!is_readable($file) || !Pi::inc($file)){
		throw new Exception('can not read mod file: '.$file.' from picom func',1004);
	}

	if(class_exists($cls)){
		$class = new $cls();
		if(!is_subclass_of($class,'PiExport')){
			throw new Exception('the class '.$cls.' is not the subclass of Export',1002);
		}
		$class->export_name = $cls;
		return $class;
	}else{
		throw new Exception('can not find picom class '.$cls.' from '.$file,1003);
	}
}

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
		if(is_readable($file)){
			Pi::inc($file);
		}
	}else{
		//优先加载工程中的lib,其次加载框架中的util
		if(is_readable(PI_UTIl.$class.'.php')){
			Pi::inc(PI_UTIl.$class.'.php');
		}else if(is_readable(COM_ROOT.'lib/'.$class.'.php')){
			Pi::inc(COM_ROOT.'lib/'.$class.'.php');
		}
	}
}

//注册自动加载函数
if(function_exists('spl_autoload_register')){
	spl_autoload_register('_pi_autoloader_core');
}