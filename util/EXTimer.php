<?php
/**
 * @file EXTimer.php
 * @date 2008/04/29 10:59:59
 * @version $Revision: 1.1 $ 
 * @brief 
 *  
 **/

final class EXTimer
{
	private $start = 0;
	private $result = array();
	private $lasttime  = 0;
	private $stats = array();

	function __construct()
	{
		$this->start = microtime(true);
	}

	/**
	 * begin 开始一个计时段,本函数会结束上一个时段的计时 
	 * 
	 * @param string $phase 计时时段的名称
	 * @access public
	 * @return mixed 返回当前时间
	 */
	function begin()
	{
		$phases = func_get_args();
		$now = microtime(true);
		foreach($phases as $phase) {
			if (!isset($this->stats[$phase])) {
				//array($now,$end);
				$this->stats[$phase] = array($now,0);
			}
		}
	}

	function end()
	{
		$phases = func_get_args();
		$now = microtime(true);
		foreach($phases as $phase) {
			if (isset($this->stats[$phase])) {
				$this->stats[$phase][1] = $now;
			}
		}
	}

	function endAll()
	{
		$now = microtime(true);
		foreach($this->stats as $phase=>&$stat) {
			if ($stat[1] === 0) {
				$stat[1] = $now;
			}
		}
	}

	/**
	 * getResult 得到最终的描述字符串 
	 *			 
	 * @return array array(
	 *  array(timername,cost)
	 * )
	 */
	function getResult($end=true)
	{
		if ($end) {
			$this->endAll();
		}
		$result = array();
		foreach($this->stats as $phase=>$stat) {
			if ($stat[1]) {
				$result[] = array($phase,intval(($stat[1]-$stat[0])*1000000));
			}
		}
		return $result;
	}

}


/*/


/* vim: set ts=4 sw=4 sts=4 tw=100 noet: */
