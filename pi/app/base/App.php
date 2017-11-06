<?php
/**
 * 用pi框架的app基类
 * @author hihu (hihu@qq.com)
 **/

class PiApp{
	public $debug = false;  //true false
	public $appId = 0;
	public $version = '1_0'; //exp: 1_0 2_0 3_1
	public $mode = null;    //exp: web task api com
	public $app_env = '';     //exp: dev test pre online
	public $com_env = '';     //exp: dev test pre online
	public $pipe = null;
	public $pipeContainer = array();
	public $timer = null;
	public $status = 'ok';

	public function __construct(){
		$this->piInit();
		$this->appInit();
	}

	public function piInit(){
		$this->checkConfig();
		pi::inc(PI_ROOT.'core/pi.php');
	}

	public function appInit(){
		$this->piInit();
		$this->begin();
		$this->initConst();
		$this->initPhpEnv();
		$this->initDb();
		$this->initCache();
		$this->initLogger();
		$this->initEnv();
		$this->initTimer();
		$this->initPipes();
	}
	public function setVersion($version){
		$this->version = $version;
	}
	public function getVersion(){
		return $this->version;
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
	protected function initConst(){
		define("PIPE_HELPER",PI_ROOT.'app'.DOT.'pipe'.DOT.'helper'.DOT);
		define("ADD_PIPE_HELPER",PI_COM_ROOT.'pipe'.DOT.'helper'.DOT);
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
		pi::$appId = $this->appId;

		//对PI_COM_ROOT的目录要求
		$com_need_dirs = pi::get('COM_DIR',array('service','lib','model','conf'));
		$com_dirs = array_flip(scandir(PI_COM_ROOT));
		foreach($com_need_dirs as $d){
			if(!isset($com_dirs[$d])){
				die('pi.err com root need init the dir: '.$d);
			}
		}
	}
	protected function initLogger(){
		//获得log path
		if(!defined("LOG_PATH")) define("LOG_PATH",pi::get('log.path',''));
		if(!is_dir(LOG_PATH)){
			die('pi.err can not find the log path');
		}
		if(!pi::inc(pi::get('LogLib'))){
			die('pi.err can not read the Log Lib');
		}
		$logFile = pi::get('global.logFile','pi');
		$logSeg = pi::get('global.logSeg',Logger::NONE_ROLLING);
        $logLevel = ($this->debug === true) ? Logger::LOG_DEBUG : pi::get('log.level',Logger::LOG_TRACE);
		$roll = pi::get('log.roll',Logger::NONE_ROLLING);
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
		$app_no_need_log_code = pi::get('global.nolog_exception',array());
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

	protected function checkConfig(){
		if(!is_dir(COM_CONF_PATH)){
			die('can not find the com config path:'.COM_CONF_PATH);
		}
	}
	
	protected function initDb(){
		$db_lib = pi::get('DbLib');
		pi::inc($db_lib);
	}

	protected function initCache(){
		$is_enable_memcache = pi::get('global.enable_memcache',true);
		$is_enable_redis = pi::get('global.enable_redis',true);
		if($is_enable_memcache){
			pi::inc(pi::get('MemcacheLib'));
		}
		if($is_enable_redis){
			pi::inc(pi::get('RedisLib'));
		}
	}
	
	protected function begin(){}

	protected function end(){}

//end of class
}

//如果是嵌入式的方式调用框架，实例化全局变量
if(!defined('PI_APP_ROOT')){
	$_G_PI_INC = new PiApp();
}