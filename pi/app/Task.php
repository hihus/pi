<?php
/**
 * @file Task.php
 * @author wanghe (hihu@qq.com)
 **/

include(PI_ROOT.'core/Pi.php');

class TaskApp extends PiApp {
	public $task_name = '';
	public $argv = array();

	public function __construct($argv){
		if(!defined("PI_APP_NAME")){
			die('please define PI_APP_NAME const');
		}
		define('TASK_PATH',PI_APP_ROOT.PI_APP_NAME.DOT.'logic'.DOT);

		$this->mode = 'task';
		$this->app_env = Pi::get('app_env','');
		$this->com_env = Pi::get('com_env','');
		//得到参数
		if(!empty($argv)){
			array_shift($argv);
			$this->task_name = array_shift($argv);
		}
		if(empty($this->task_name)){
			die('please input the task name for this process');
		}
		$this->argv = $argv;
		parent::__construct();
	}

	protected function begin(){
		parent::begin();
	}

	protected function initLogger(){
		//获得log path
		if(!defined("LOG_PATH")) define("LOG_PATH",Pi::get('log.path',''));
		if(!is_dir(LOG_PATH)) die('pi.err can not find the log path');

        Pi::inc(Pi::get('LogLib'));

		$logFile = $this->task_name;
        $logLevel = ($this->debug === true) ? Logger::LOG_DEBUG : Pi::get('log.level',Logger::LOG_TRACE);
		$roll = Pi::get('log.roll',Logger::DAY_ROLLING);
		$basic = array('logid'=>$this->appId);

		Logger::init(LOG_PATH,$logFile,$logLevel,array(),$roll);
		Logger::addBasic($basic);
	}

	public function run(){
		//初始化pipe
		$default_pipe = array('TaskProcessPipe'=>'default');
		$pipes = Pcf::get('global.pipes',array());
		if(empty($pipes)){
			$pipes = $default_pipe;
		}

		$this->pipeLoadContainer = $pipes;
		//后台脚本方便日志记录,把所有输出全部定位到日志目录
		ob_start();
		echo("\n---------------------".date("Y-m-d H:i:s")."--------------------\n");
		echo("\nrun result:\n");
		
		$this->timer->begin('task_run');

		parent::run();
		
		$this->timer->end('task_run');
		$time = $this->timer->getResult();
		
		echo("\nrun time : ".($time[0]['1']/1000)." s \n");
		echo("\n---------------------".date("Y-m-d H:i:s")."--------------------\n");
		
		$res = ob_get_clean();
		Logger::trace("%s",var_export($res,true));

	}
}