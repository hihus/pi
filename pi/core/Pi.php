<?php
/**
 * @file Pi.php
 * @author wanghe (hihu@qq.com)
 **/
if(!defined('PI_ROOT'))  die('you must define the pi root first');
if(!defined('PI_COM_ROOT')) die('you must define PI_COM_ROOT first~');

//工具类
class Pi {
	private static $saIncludeFiles = array();
	private static $saIsLoaded = array();
	private static $saConfData = array();
	static $appId = 0;//全局id

	//框架内包含文件统一入口
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
			$loaded_mod[$mod.$add] = self::pi_load_export_file($mod,$add);
		}
		return $loaded_mod[$mod.$add];
	}

	//加载export文件的公用方法
	static function pi_load_export_file($mod,$add){
		if($add == ''){
			$cls = ucfirst($mod).'Export';
			$file = EXPORT_ROOT.$mod.DOT.$cls.'.php';
		}else if(is_string($add)){
			$cls = ucfirst($mod).ucfirst($add).'Export';
			$file = EXPORT_ROOT.$mod.DOT.$cls.'.php';
		}else{
			throw new Exception('picom can not find mod:'.$mod,',add:'.$add,1001);
		}

		if(!is_readable($file) || !self::inc($file)){
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
				if(Pi::inc($file) && isset(self::$saConfData[$key])){
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
Pi::inc(PI_ROOT.'core/Config.inc.php');

//加载基础类库
Pi::inc(PI_CORE.'CoreBase.php');

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
			if(is_readable($path) && Pi::inc($path)){
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

//核心类
class PiApp{
	public $debug = false;  //true false
	public $appId = 0;
	public $mode = null;    //exp: web task api com
	public $app_env = '';     //exp: dev test pre online
	public $com_env = '';     //exp: dev test pre online
	public $pipe = null;
	public $pipeContainer = array();
	public $timer = null;
	public $status = 'ok';

	public function __construct(){
		if(!defined('PI_APP_ROOT')){
			$this->comInit();
		}else{
			$this->appInit();
		}
	}

	public function comInit(){
		$this->initLoader();
		$this->initProxy();
		$this->checkConfig();
		$this->initDb();
		$this->initCache();
	}

	public function appInit(){
		$this->comInit();
		$this->begin();
		$this->initEnv();
		$this->initPhpEnv();
		$this->initLogger();
		$this->initTimer();
		$this->initPipes();
	}

	//运行开始，执行流程
	public function run(){
		if(!empty($this->pipeLoadContainer)){
			foreach($this->pipeLoadContainer as $pipe => $from){
				if($from == 'default'){
					$this->pipe->loadPipes($pipe,$from);
				}else{
					$this->pipe->loadPipes($pipe);
				}
				$this->pipe->execute($pipe);
			}
		}
	}

	protected function initEnv(){
		//设置是否开启调试,线上环境不要开启
		if(defined('__PI_EN_DEBUG')){
			$this->debug = true;
		}
		
		if(true === $this->debug){
			ini_set('display_errors',1);
		}

		//必须先设置运行的类型和运行的环境
		if(empty($this->mode) || !is_string($this->mode)){
			die('pi.err not set or set a wrong mode');
		}

		//生成进程唯一标识
		$this->appId = $this->genAppid();
		Pi::$appId = $this->appId;

		//对PI_COM_ROOT的目录要求
		$com_need_dirs = Pi::get('COM_DIR',array('export','lib','logic','model','conf'));
		$com_dirs = array_flip(scandir(PI_COM_ROOT));
		foreach($com_need_dirs as $d){
			if(!isset($com_dirs[$d])){
				die('pi.err com root need init the dir: '.$d);
			}
		}
	}

	protected function initLogger(){
		//获得log path
		if(!defined("LOG_PATH")) define("LOG_PATH",Pi::get('log.path',''));
		if(!is_dir(LOG_PATH)){
			die('pi.err can not find the log path');
		}

		if(!Pi::inc(Pi::get('LogLib'))){
			die('pi.err can not read the Log Lib');
		}

		$logFile = Pi::get('global.logFile','pi');
		$logSeg = Pi::get('global.logSeg',Logger::NONE_ROLLING);
        $logLevel = ($this->debug === true) ? Logger::LOG_DEBUG : Pi::get('log.level',Logger::LOG_TRACE);
		$roll = Pi::get('log.roll',Logger::NONE_ROLLING);
		$basic = array('logid'=>$this->appId);

		Logger::init(LOG_PATH,$logFile,$logLevel,array(),$roll);
		Logger::addBasic($basic);
	}

	protected function initTimer(){
		$this->timer = new EXTimer();
	}

	protected function initPhpEnv(){
		if(defined('TIMEZONE')) ini_set('date.timezone',TIMEZONE);
		if(defined('ENCODE'))  ini_set('internal_encoding',ENCODE);

		ini_set('output_buffering','On');
		error_reporting(E_ALL|E_STRICT|E_NOTICE);
		
		set_error_handler(array($this,'errorHandler'));
		set_exception_handler(array($this,'exceptionHandler'));
		register_shutdown_function(array($this,'shutdownHandler'));
	}

	function errorHandler(){
		restore_error_handler();
		$error = func_get_args();
		$res = false;
		if (!($error[0] & error_reporting())) {
			Logger::debug('error info, errno:%d,errmsg:%s,file:%s,line:%d',$error[0],$error[1],$error[2],$error[3]);
		} elseif ($error[0] === E_USER_NOTICE) {
			Logger::trace('error trace, errno:%d,errmsg:%s,file:%s,line:%d',$error[0],$error[1],$error[2],$error[3]);
		} elseif ($error[0] === E_USER_WARNING) {
			Logger::warning('error warning, errno:%d,errmsg:%s,file:%s,line:%d',$error[0],$error[1],$error[2],$error[3]);
		} elseif ($error[0] === E_USER_ERROR) {
			Logger::fatal('error error, errno:%d,errmsg:%s,file:%s,line:%d',$error[0],$error[1],$error[2],$error[3]);
		} else {
			Logger::fatal('error error, errno:%d,errmsg:%s,file:%s,line:%d',$error[0],$error[1],$error[2],$error[3]);
			$this->status = 'error';
			$res = true;
		}
		set_error_handler(array($this,'errorHandler'));
		return $res;
	}

	function exceptionHandler($ex){
		restore_exception_handler();
		$errcode = $ex->getMessage();
		$code = $ex->getCode();
		if($this->needToLog($code)){
			$errmsg = sprintf('<< exception:%s, errcode:%s, trace: %s >>',$code,$errcode,$ex->__toString());
			if (($pos = strpos($errcode,' '))) {
				$errcode = substr($errcode,0,$pos); 
			}
			$this->status = $errcode;
			Logger::fatal($errmsg);
		}
	}

	//不需要记录日志的异常值代码，防止有些没有意义的记录冲刷日志,取核心代码和项目代码的两个配置
	protected function needToLog($code){
		$core_no_need_log_code = Pcf::get('global.nolog_exception',array());
		$app_no_need_log_code = Pi::get('global.nolog_exception',array());
		if(isset($core_no_need_log_code[$code]) || isset($app_no_need_log_code[$code])){
			return false;
		}
		return true;
	}
	function shutdownHandler(){
		$this->end();
	}

	protected function genAppid(){
		$time = gettimeofday();
		$time = $time['sec'] * 100 + $time['usec'];
		$rand = mt_rand(1, $time+$time);
		$id = ($time ^ $rand)  & 0xFFFFFFFF;
		return floor($id/100)*100;
	}

	protected function initPipes(){
		$this->pipe = new PipeExecutor($this);
	}

	protected function initLoader(){
		Pi::inc(PI_CORE.'Loader.php');
	}
	
	protected function initProxy(){
		Pi::inc(PI_CORE.'Proxy.php');
	}
	
	protected function checkConfig(){
		if(!is_dir(COM_CONF_PATH)){
			die('can not find the com config path:'.COM_CONF_PATH);
		}
	}
	
	protected function initDb(){
		$db_lib = Pi::get('DbLib');
		Pi::inc($db_lib);
	}

	protected function initCache(){
		$is_enable_memcache = Pi::get('global.enable_memcache',true);
		$is_enable_redis = Pi::get('global.enable_redis',true);
		if($is_enable_memcache){
			Pi::inc(Pi::get('MemcacheLib'));
		}
		if($is_enable_redis){
			Pi::inc(Pi::get('RedisLib'));
		}
	}
	
	protected function begin(){}

	protected function end(){}

//end of class
}

//如果是嵌入式的方式调用框架，实例化全局变量
if(!defined('PI_APP_ROOT')){
	$_G_PI_INC = new Piapp();
}
