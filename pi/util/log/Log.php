<?php

if (!function_exists('posix_getpid')) {
	function posix_getpid() {
		return getmypid();
	}
}

Pi::inc(dirname(__FILE__).'/Logger.php');

final class PiLog {
	//日志缓冲
	const PAGE_SIZE   = 4096;

	//monitor日志特征串
	const MONTIR_STR  = '---LOG_MONITOR---';
    const SMS_MONTIR_STR = '<<<SMS_LOG_MONITOR>>>';

	static $LOG_NAME = array (
		Logger::LOG_FATAL   => 'FATAL',
		Logger::LOG_WARNING => 'WARNING',
		Logger::LOG_NOTICE  => 'NOTICE',
		Logger::LOG_TRACE   => 'TRACE',
		Logger::LOG_DEBUG   => 'DEBUG'
	);

	private $log_name   = '';
	private $log_path   = '';
	private $log_str    = '';
	private $wflog_str  = '';
	private $basic_info = '';
	private $notice_str = '';
	private $log_level	= 16;
	private $arr_basic  = null;
	private $force_flush = false;
	private $init_pid   = 0;

	private $roll = Logger::NONE_ROLLING;

	function __construct(){
	}

	function __destruct(){
		if ($this->init_pid==posix_getpid()) {
			/* 只在打出当前进程的日志 */
			$this->checkFlushLog(true);
		}
	}

	function init($dir, $name, $level = Logger::LOG_DEBUG, $arr_basic_info = array(), $roll = Logger::NONE_ROLLING, $flush=false){
		if (empty($dir) || empty($name)) {
			return false;
		}

		/* 使用的为绝对路径 */
		if ('/' != $dir{0}) {
			$dir = realpath($dir);
		}

		$dir  = rtrim($dir, ".");
		$name = rtrim($name, "/");
		$this->log_path = $dir;
		$this->log_name = $name;
		$this->log_level = $level;

		/* set basic info */
		$this->arr_basic = $arr_basic_info;
		/* 生成basic info的字符串 */
		$this->genBasicInfo();
		/* 记录初使化进程的id */
		$this->init_pid = posix_getpid();
		$this->force_flush = $flush;

		$this->roll = $roll;
		return true;
	}

	private function genLogPart($str){
		return "[" . $str . "]";
	}

	private function genBasicInfo(){
		$this->basic_info = '';
		foreach ($this->arr_basic as $key => $value){
			$this->basic_info .= $this->genLogPart("$key:$value");
		}
	}

	public function checkFlushLog($force_flush){
		if (strlen($this->log_str)>self::PAGE_SIZE || strlen($this->wflog_str)>self::PAGE_SIZE ) {
			$force_flush = true;
		}

		if ($force_flush) {
			$log_file_path = $this->checkLogFilePath(); 
			$wflog_path = $log_file_path . '.log.wf';
			$normal_log_path = $log_file_path . '.log';
			/* first write warning log */
			if (!empty($this->wflog_str)) {
				$str = str_replace("%s", $this->basic_info, $this->wflog_str);
				$this->writeFile($wflog_path, $str);
			}
			/* then common log */
			if (!empty($this->log_str)) {
				$str = str_replace("%s", $this->basic_info, $this->log_str);
				$this->writeFile($normal_log_path, $str);
			}

			/* clear the printed log*/
			$this->wflog_str = '';
			$this->log_str   = '';

		} /* force_flush */
	}

	private function checkLogFilePath(){
		if($this->roll == Logger::NONE_ROLLING){
			return $this->log_path . "/" . $this->log_name;
		}elseif ($this->roll == Logger::HOUR_ROLLING){
			return $this->log_path . "/" . $this->log_name . "." . date("YmdG");
		}elseif($this->roll == Logger::DAY_ROLLING){
			return $this->log_path . "/" . $this->log_name . "." . date("Ymd");
		}else {
			return $this->log_path . "/" . $this->log_name . "." . date("Ym");
		}
	}

	private function writeFile($path, $str){
		$fd = @fopen($path, "a+" );
		if (is_resource($fd)) {
			fputs($fd, $str);
			fclose($fd);
		}
		return;
	}

	public function addBasicInfo($arr_basic_info){
		$this->arr_basic = array_merge($this->arr_basic, $arr_basic_info);
		$this->genBasicInfo();
	}

	public function pushNotice($format, $arr_data){
		$this->notice_str .= " " .$this->genLogPart(vsprintf($format, $arr_data));
	}

	public function clearNotice(){
		$this->notice_str = '';
	}

	public function writeLog($type, $format, $line_no, $arr_data, $sms_monitor = false){
		if ($this->log_level<$type)
			return;

		$micro = microtime();
		$sec = intval(substr($micro, strpos($micro," ")));
		$ms = floor($micro*1000000);
		$str = sprintf( "%s: %s.%-06d: %s * %d %s", self::$LOG_NAME[$type], date("Y-m-d H:i:s",$sec), $ms, $this->log_name, posix_getpid(), $line_no);
		/* add monitor tag?	*/	
		if ($type==Logger::LOG_FATAL) {
			$str .= self::MONTIR_STR;
		}
		if ($sms_monitor) {
			$str .= self::SMS_MONTIR_STR;
		}
		/* add basic log */
		//$str .= $this->basic_info;
		$str .= "%s ";
        /* add detail log */
		if (null==$arr_data ) {
			$str .= vsprintf('%s', $format);
		} else {
			$str .= vsprintf($format, $arr_data);
		}

		switch ($type) {
		case Logger::LOG_WARNING :
		case Logger::LOG_FATAL :
			$this->wflog_str .= $str . "\n";
			break;
		case Logger::LOG_DEBUG :
		case Logger::LOG_TRACE :
			$this->log_str .= $str . "\n";
			break;
		case Logger::LOG_NOTICE : 	
			$this->log_str .= $str . $this->notice_str . "\n";
			$this->clearNotice();
			break;
		default : 
			break;	
		}
		$this->checkFlushLog($this->force_flush); 
	}
}
/* vim: set ts=4 sw=4 sts=4 tw=100 noet: */
