<?php

class Queue {
	public $driver = null;
	static $driver_type = array('memcache'=>1,'mongo'=>1);

	public function __construct($name,$conf_name,$ttl = 86400,$driver = 'memcache'){
		if(!isset(self::$driver_type[$driver])){
			throw new Exception('queue.err can not find the driver:'.$driver,8088);
		}
		$instance = $this->getInstance($driver,$conf_name);
		$cls = 'Util_Queue_'.ucfirst($driver);
		$this->driver = new $cls($name,$instance,$ttl);
	}

	public function getInstance($driver,$conf_name){
		$instance = null;
		if($driver == 'memcache'){
			$instance = PIMem::get($conf_name);
		}else if($driver == 'mongo'){
			$instance = null;
		}else{
			throw new Exception('queue.err can not getInstance:'.$driver,8088);
		}
		return $instance;
	}

	public function clear(){
		return $this->driver->clear();
	}

	public function push($info){
		return $this->driver->push($info);
	}

	public function pop($cnt = 1,$is_delete = true){
		return $this->driver->pop($cnt,$is_delete);
	}

	public function getSize(){
		return $this->driver->getSize();
	}

	public function getLastError(){
		return $this->driver->getLastError();
	}
//end of class
}