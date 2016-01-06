<?php

$__log = null;

final class Logger {
	//逻辑日志级别
	const LOG_FATAL = 1;
	const LOG_WARNING = 2;
	const LOG_NOTICE = 4;
	const LOG_TRACE = 8;
	const LOG_DEBUG = 16;
	
	//日志切分方式
	const NONE_ROLLING = 0;
	const HOUR_ROLLING = 1;
	const DAY_ROLLING = 2;
	const MONTH_ROLLING = 3;
	
	private static function __log__($type, $arr, $sms_monitor = false) {
		global $__log;
		$format = $arr [0];
		array_shift ( $arr );
		
		$pid = posix_getpid ();
		
		$bt = debug_backtrace ();
		if (isset ( $bt [1] ) && isset ( $bt [1] ['file'] )) {
			$c = $bt [1];
		} else if (isset ( $bt [2] ) && isset ( $bt [2] ['file'] )) { //为了兼容回调函数使用log
			$c = $bt [2];
		} else if (isset ( $bt [0] ) && isset ( $bt [0] ['file'] )) {
			$c = $bt [0];
		} else {
			$c = array ('file' => 'faint', 'line' => 'faint' );
		}
		$line_no = '[' . $c ['file'] . ':' . $c ['line'] . '] ';
		
		if (! empty ( $__log [$pid] )) {
			$log = $__log [$pid];
			$log->writeLog ( $type, $format, $line_no, $arr, $sms_monitor );
		} else {
		}
	}
	static function init($dir, $file, $level = Logger::LOG_DEBUG, $info = array(), $roll = Logger::NONE_ROLLING, $flush = false) {
		global $__log;
		
		$pid = posix_getpid ();
		
		if (! empty ( $__log [$pid] )) {
			unset ( $__log [$pid] );
		}
		
		$__log [posix_getpid ()] = new PiLog ( );
		$log = $__log [posix_getpid ()];
		if ($log->init ( $dir, $file, $level, $info, $roll, $flush )) {
			return true;
		} else {
			unset ( $__log [$pid] );
			return false;
		}
	}
	static function debug() {
		$arg = func_get_args ();
		Logger::__log__ ( Logger::LOG_DEBUG, $arg );
	}
	
	static function trace() {
		$arg = func_get_args ();
		Logger::__log__ ( Logger::LOG_TRACE, $arg );
	}
	
	static function notice() {
		$arg = func_get_args ();
		Logger::__log__ ( Logger::LOG_NOTICE, $arg );
	}
	
	static function warning() {
		$arg = func_get_args ();
		Logger::__log__ ( Logger::LOG_WARNING, $arg );
	}
	
	static function fatal() {
		$arg = func_get_args ();
		Logger::__log__ ( Logger::LOG_FATAL, $arg );
	}
	
	static function monitor() {
		$arg = func_get_args ();
		Logger::__log__ ( Logger::LOG_FATAL, $arg, true );
	}
	
	static function pushNotice() {
		global $__log;
		$arr = func_get_args ();
		
		$pid = posix_getpid ();
		
		if (! empty ( $__log [$pid] )) {
			$log = $__log [$pid];
			$format = $arr [0];
			/* shift $type and $format, arr_data left */
			array_shift ( $arr );
			$log->pushNotice ( $format, $arr );
		} else {
			/* nothing to do */
		}
	}
	
	static function clearNotice() {
		global $__log;
		$pid = posix_getpid ();
		
		if (! empty ( $__log [$pid] )) {
			$log = $__log [$pid];
			$log->clearNotice ();
		} else {
			/* nothing to do */
		}
	}
	
	static function addBasic($arr_basic) {
		global $__log;
		$pid = posix_getpid ();
		
		if (! empty ( $__log [$pid] )) {
			$log = $__log [$pid];
			$log->addBasicInfo ( $arr_basic );
		} else {
			/* nothing to do */
		}
	}
	
	static function exception($e) {
		Logger::warning ( 'caught exception [%s]', $e );
	}
	
	static function flush() {
		global $__log;
		$pid = posix_getpid ();
		if (! empty ( $__log [$pid] )) {
			$log = $__log [$pid];
			$log->checkFlushLog ( true );
		} else {
			/* nothing to do */
		}
	}
}
/* vim: set ts=4 sw=4 sts=4 tw=100 noet: */
