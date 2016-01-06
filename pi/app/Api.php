<?php
/**
 * @file Api.php
 * @author wanghe (hihu@qq.com)
 **/

include(PI_ROOT.'core/Pi.php');

class ApiApp extends PiApp {

	protected $data_types = array('json'=>1,'serialize'=>1);
	public $data_type = 'json';
	
	public function __construct($argv = array()){
		
		if(!defined("PI_APP_NAME")){
			die("app.err please define PI_APP_NAME const \n");
		}
		
		$this->mode = 'api';
		$this->app_env = Pi::get('app_env','');
		$this->com_env = Pi::get('com_env','');
		$data_type = Pcf::get("global.data_type",'json');
		
		if(isset($this->data_types[$data_type])){
			$this->data_type = $data_type;
		}
		
		parent::__construct();

		if(true === $this->debug && php_sapi_name() == 'cli'){
			$mod_name = Pcf::get("global.mod",'mod');
			$func_name = Pcf::get("global.func",'func');
			Comm::setReq($mod_name,$argv[1]);
			Comm::setReq($func_name,$argv[2]);
		}
	}

	protected function begin(){
		parent::begin();
		$this->initHttp();
	}

	protected function initHttp(){
		//初始化session,php>5.4 换成 if(session_status() !== PHP_SESSION_ACTIVE) {session_start();}
		if(!isset($_SESSION)) {session_start();}
	}

	function exceptionHandler($ex){
		restore_exception_handler();
		$errcode = $ex->getMessage();
		$code = $ex->getCode();
		if($this->needToLog($code)){
			$errmsg = sprintf('<<  exception:%s, errcode:%s, trace: %s >>',$code,$errcode,$ex->__toString());
			if (($pos = strpos($errcode,' '))) {
				$errcode = substr($errcode,0,$pos); 
			}
			$this->status = $errcode;
			Logger::fatal($errmsg);
		}
		//内部export调用不需要做异常输出处理. 和ApiRouter.php的output错误输出格式一致
		if(!defined('USE_INNER_API')){
			echo json_encode(array('msg'=>$errcode,PI_INNER_ERR=>$code),true);
		}
	}

	protected function checkInnerApi(){
		$sign = Pi::get('global.innerapi_sign','');
		$sign_name = Pi::get('global.innerapi_sign_name','_pi_inner_nm');
		if(Comm::req($sign_name) == $sign){
			return true;
		}
		return false;
	}

	public function run(){
		//内网api调用
		if($this->checkInnerApi()){
			//如果有其他调试输出忽略
			ob_start();
			define("USE_INNER_API",1);
			Pi::inc(PI_CORE.'Proxy.php');
			PiProxyServer::Server();
		}else{
			//初始化pipe
			$default_pipe = array('ApiReqPipe'=>'default','ApiHttpRouterPipe'=>'default');
			$pipes = Pi::get('global.pipes',array());
			if(empty($pipes)){
				$pipes = $default_pipe;
			}
			$this->pipeLoadContainer = $pipes;
			parent::run();
		}
	}
}