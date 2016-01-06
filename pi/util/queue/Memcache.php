<?php
/* 
 * 本文件定义了一个消息队列
 *
 * 消息队列支持以下特性
 * - 多进程并发写入
 * - 多进程读
 */

define('JYMQ_EMPTY_QUEUE', 1);
define('JYMQ_LOCK_FAILED', 2);

class Util_Queue_Memcache {
	var $_cache = null;
	var $_name;
	var $_front_key;
	var $_rear_key;
	var $_lock_key;
	var $_queue_item_cache_time = 300;
	var $_last_error = 0;
	var $_last_error_msg = '';

	function __construct($name, $server = null, $cache_time = 300){
		if($server == null){
			return false;
		}
		$this->_cache = $server;
		if ($cache_time != 0) {
			$this->_queue_item_cache_time = $cache_time;
		}
		$this->_name = $name;
		$this->_front_key = $name.'_front';
		$this->_rear_key = $name.'_rear';
		$this->_lock_key = $name.'_lock';
	}

	public function clear(){
		//TODO lock
		$this->_cache->delete($this->_front_key);
		$this->_cache->delete($this->_rear_key);
		$this->_cache->delete($this->_lock_key);
	}

	

	public function push($msg){
		$rear = $this->getNextRear();
		$this->_cache->set($this->getItemKey($rear), $msg, 0, $this->_queue_item_cache_time);
		return true;
	}

	public function getSize(){
		$rear = $this->_cache->get($this->_rear_key);
		if ($rear === false) {
			return 0;
		}
		$front = $this->_cache->get($this->_front_key);
		if ($front === false) {
			$front = 0;
			$this->_cache->add($this->_front_key, $front);
		}
		//TODO 修改，当front > rear 时自动修复队列,目前不能跟multideque保证同步性，没加锁
		if ($front > $rear) {
			if ($this->lock()) {
				$this->clear();
				$this->unlock();
			}
			return 0;
		} else {
			return $rear - $front;
		}
	}

	public function pop($count = 1, $do_delete = true){
		$front = $this->getFront($count);
		if ($front !== false) {
			$cache_keys = array_map(array($this, 'getItemKey'), $front);
			$ret = $this->_cache->get($cache_keys);
			if ($do_delete) {
				foreach ($cache_keys as $key) {
					$this->_cache->delete($key);
				}
			}
			return array_values($ret);
		}
		return false;
	}

	protected function getItemKey($index){
		return $this->_name.'_item_'.$index;
	}

	protected function getRear(){
		$rear = $this->_cache->get($this->_rear_key);
		return $rear ? $rear : 0;
	}

	protected function getNextRear(){
		$rear = $this->_cache->increment($this->_rear_key, 1);
		if ($rear === false) {
			$rear = 0;
			$this->_cache->add($this->_rear_key, $rear);
			$rear = $this->_cache->increment($this->_rear_key, 1);
		}
		return $rear;
	}

	protected function getFront($count = 1){
		$rear = $this->_cache->get($this->_rear_key);
		if ($rear === false) {
			$this->setError(JYMQ_EMPTY_QUEUE, 'empty queue');
			return false;
		}
		//因为多线程，所以需要加锁
		if ($this->lock() === false) {
			$this->setError(JYMQ_LOCK_FAILED, 'lock failed');
			return false;
		}
		$front = $this->_cache->get($this->_front_key);
		if ($front === false) {
			$front = 0;
			$this->_cache->add($this->_front_key, $front);
		}
		if ($front == $rear) {
			$this->setError(JYMQ_EMPTY_QUEUE, 'empty queue');
			$this->unlock();
			return false;
		}
		/*当队列front>rear时，出了问题，clear队列*/
		if ($front > $rear) {
			$this->clear();
			$this->unlock();
			$this->setError(JYMQ_EMPTY_QUEUE, 'empty queue');
			return false;
		}
		if ($rear - $front < $count) {
			$count = $rear - $front;
		}
		$front = $this->_cache->increment($this->_front_key, $count);
		$this->unlock();
		return $this->range($front - $count + 1, $front);
	}

	protected function lock($time = 60){
		return $this->_cache->add($this->_lock_key, '1', 0, $time);
	}

	protected function unlock(){
		$this->_cache->delete($this->_lock_key);
	}

	protected function setError($code, $msg){
		$this->_last_error = $code;
		$this->_last_error_msg = $msg;
	}

	protected function getLastError(){
		return $this->_last_error;
	}

	protected function range($start, $end){
		$ret = array();
	    for ($i = $start; $i <= $end; $i++) {
			$ret[] = $i; 
		}   
		return $ret;
	}
}
