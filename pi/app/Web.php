<?php
/**
 * @file Web.php
 * @author hihu (hihu@qq.com)
 **/

include(PI_ROOT.'app/base/App.php');

class WebApp extends PiApp {
	public function __construct(){
		if(!defined("APP_CTR_ROOT")){
			die('please define APP_CTR_ROOT const');
		}
		if(!defined("PI_APP_NAME")){
			die('please define PI_APP_NAME const');
		}
		$this->mode = 'web';
		$this->app_env = pi::get('app_env','');
		$this->com_env = pi::get('com_env','');
		parent::__construct();
	}

	protected function begin(){
		parent::begin();
		$this->initHttp();
		$this->initTemplate();
	}

	protected function initHttp(){
		//初始化session,php>5.4 换成 if(session_status() !== PHP_SESSION_ACTIVE) {session_start();}
		if(!isset($_SESSION)) {session_start();}
	}

	protected function initTemplate(){
		$views = Pcf::get('global.view_lib_path');
		$views = PI_UTIL.$views;
		if(!pi::inc($views)){
			die('can not find the web view libs ');
		}
		$cls = Pcf::get('global.view_engine');
		if(!class_exists($cls)){
			die('can not init the template engine class');
		}
	}
	
	function errorHandler(){
		if(true  === $this->debug){
			print_r(func_get_args());
			print_r(debug_backtrace());
			exit;
		}

		parent::errorHandler();
		self::page_5xx();
	}
	
	function exceptionHandler($ex){
		if(true  === $this->debug){
			echo '<br><br>';
			echo $ex->getMessage();
			echo '<br><br>';
			exit;
		}
		parent::exceptionHandler($ex);
		self::page_4xx();
	}
	
	//webserver 配置html访问不走框架
	function page_4xx(){
		echo '<br><br>400<br><br>';
		exit;
		// $url = Pcf::get('global.404',PI_APP_ROOT.PI_APP_NAME.'/4xx.html');
		// Comm::jump($url);
	}

	//webserver 配置html访问不走框架
	function page_5xx(){
		echo '<br><br>500<br><br>';
		exit;
		// $url = Pcf::get('global.404',PI_APP_ROOT.PI_APP_NAME.'/5xx.html');
		// Comm::jump($url);
	}

	public function run(){
		//初始化pipe
		$default_pipe = array('WebReqPipe'=>'default','WebRouterPipe'=>'default');
		$pipes = pi::get('global.pipes',array());
		if(empty($pipes)){
			$pipes = $default_pipe;
		}
		$this->pipeLoadContainer = $pipes;
		parent::run();
	}
}
