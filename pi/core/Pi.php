<?php
/**
 * @file Pi.php
 * @author hihu (hihu@qq.com)
 **/
if(!defined('PI_ROOT'))  die('you must define the pi root first.');
if(!defined('PI_COM_ROOT')) die('you must define PI_COM_ROOT first.');

//核心类，为了方便调用书写，默认小写类名
class pi {

	private static $saIncludeFiles = array();
	private static $saIsLoaded = array();
	private static $saConfData = array();
	static $appId = 0;//全局id

	//框架内包含文件统一入口 pi::inc($file_name)
	static function inc($sFile){
		if(isset(self::$saIncludeFiles[$sFile])){
			return true;
		}else{
			if(is_readable($sFile)){
				include($sFile);
				self::$saIncludeFiles[$sFile] = 1;
				return true;
			}
		}
		return false;
	}

	//利用反射调用指定类的公用方法
	static function piCallMethod($class,$method,$args = array(),&$err = 0){
		if (is_callable(array($class,$method))){
	        $reflection = new ReflectionMethod($class,$method);
	        $argnum = $reflection->getNumberOfParameters();
	        //参数个数
	        if ($argnum > count($args)) {
	               $err = 1;
	               return false;
	        }
	        //公共方法才允许被调用
	        return $reflection->invokeArgs($class,$args);
	    }
	    $err = 2;
	    return false;
	}
	//加载com模块的函数
	static function com($mod,$add = '',$is_server = false){
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
		$proxy_conf = self::get($conf_name,array());
		if($is_server === false && !empty($proxy_conf)){
			//proxy代理类,根据更详细的配置选择哪个接口走远程
			$class = new PiProxy($mod,$add,$proxy_conf);
			$loaded_mod[$mod.$add] = $class;
		}else{
			//直接加载本地逻辑接口
			$loaded_mod[$mod.$add] = self::LoadServiceFile($mod,$add);
		}
		return $loaded_mod[$mod.$add];
	}

	//加载service文件的公用方法
	static function LoadServiceFile($mod,$add){
		if($add == ''){
			$cls = ucfirst($mod).'Service';
			$file = SERVICE_ROOT.$mod.DOT.$cls.'.php';
		}else if(is_string($add)){
			$cls = ucfirst($mod).ucfirst($add).'Service';
			$file = SERVICE_ROOT.$mod.DOT.$cls.'.php';
		}else{
			throw new Exception('picom can not find mod:'.$mod,',add:'.$add,1001);
		}

		if(!self::inc($file)){
			throw new Exception('can not read mod file: '.$file.' from picom func',1004);
		}

		if(class_exists($cls)){
			$class = new $cls();
			if(!is_subclass_of($class,'PiService')){
				throw new Exception('the class '.$cls.' is not the subclass of Service',1002);
			}
			$class->service_name = $cls;
			return $class;
		}else{
			throw new Exception('can not find picom class '.$cls.' from '.$file,1003);
		}
	}
	//得到配置,配置加载请自定义COM_CONF_PATH目录
	static function get($key,$default=null){
		if(isset(self::$saConfData[$key])){
			return self::$saConfData[$key];
		}
		//没有的自动加载文件和配置项,先看有没有本环境的配置文件，如果没有加载默认路径下的配置文件
		if(defined("COM_CONF_PATH") && strpos($key,'.') !== false){
			$file = explode('.',$key);
			if(!empty($file)){
				array_pop($file);
				$file_name = array_pop($file);
				$file = (count($file) == 0) ? '' : implode('/',$file).'/';
				$env = self::get('com_env','');
				if($env != '' && is_readable($file)){
					$file = COM_CONF_PATH.$env.'/'.$file.$file_name.'.inc.php';
				}else{
					$file = COM_CONF_PATH.$file.$file_name.'.inc.php';
				}
				if(self::inc($file) && isset(self::$saConfData[$key])){
					return self::$saConfData[$key];
				}
			}
		}
		return $default;
	}

	static function set($key,$value){self::$saConfData[$key] = $value; }
	static function has($key){return isset(self::$saConfData[$key]); }
	static function clear(){self::$saIsLoaded = array(); self::$saConfData = array(); }
	static function delItem($key){if(self::has($key)){unset(self::$saConfData[$key]); } } 
}

//app应用配置加载类,应用如：web,api,task...
class Pcf {
	private static $saIsLoaded = array();
	private static $saConfData = array();

	//得到配置,配置加载请自定义APP_CONF_PATH目录
	static function get($key,$default=null){
		if(isset(self::$saConfData[$key])){
			return self::$saConfData[$key];
		}
		//没有的自动加载文件和配置项
		if(defined("APP_CONF_PATH") && strpos($key,'.') !== false){
			$file = explode('.',$key);
			if(!empty($file)){
				array_pop($file);
				$file_name = array_pop($file);
				$file = (count($file) == 0) ? '' : implode('/',$file).'/';
				$env = self::get('app_env','');
				if($env != '' && is_readable($file)){
					$file = APP_CONF_PATH.$env.'/'.$file.$file_name.'.inc.php';
				}else{
					$file = APP_CONF_PATH.$file.$file_name.'.inc.php';
				}
				if(pi::inc($file) && isset(self::$saConfData[$key])){
					return self::$saConfData[$key];
				}
			}
		}
		return $default;
	}

	static function set($key,$value){self::$saConfData[$key] = $value; }
	static function has($key){return isset(self::$saConfData[$key]); }
	static function clear(){self::$saIsLoaded = array(); self::$saConfData = array(); }
	static function delItem($key){if(self::has($key)){unset(self::$saConfData[$key]); } } 
}


//加载基础配置
pi::inc(PI_ROOT.'core/Config.inc.php');
//加载自动加载方法
pi::inc(PI_CORE.'Loader.php');
//加载proxy.php
pi::inc(PI_CORE.'Proxy.php');

//如果有composer第三方库，加载composer自动加载方法
if(defined('COMPOSER_LOADER') && is_readable(COMPOSER_LOADER)){
	pi::inc(COMPOSER_LOADER);
}

//服务的基类
class PiService {
	public $service_name = '';
	public function __construct(){}
}
//pi管道接口
interface PiIpipe {
	public function execute(PiApp $app);
}

//管道加载器
class PipeExecutor {
	private $arr_pipe = array();
	private $app = null;

	function __construct(PiApp $app){
		$this->app = $app;
	}

	function loadPipes($pipes = null,$root = null){
		//pipe 数组格式 path => class_name
		//加载默认的处理管道
		if($pipes == null){
			return false;
		}else{
			if(is_string($pipes)){
				$pipes = array($pipes);
			}
			if(!empty($pipes)){
				//加载管道位置
				$root = ($root == 'default') ? PI_ROOT : PI_COM_ROOT;
				foreach ($pipes as $k => $cls){
					$pipes[$cls] = $root.'pipe'.DOT.$cls.'.php';
					unset($pipes[$k]);
				}
			}
		}
		foreach($pipes as $cls => $path){
			if(isset($this->arr_pipe[$cls])) continue;
			if(pi::inc($path)){
				if(class_exists($cls)){
					$this->arr_pipe[$cls] = new $cls();
				}else{
					throw new Exception('the pipe class '.$cls.' do not exists,check pipe file',1020);
				}
			}else{
				throw new Exception('the pipe '.$cls.' can not load,check pipe file',1020);
			}
		}
	}

	function execute($pipe){
		if(!isset($this->arr_pipe[$pipe])){
			throw new Exception('pipe.err not run the pipe: '.$pipe);
		}
		$pipe_obj = $this->arr_pipe[$pipe];
		if ($pipe_obj->execute($this->app) === false) {
			return false;
		}
		return true;
	}
}
