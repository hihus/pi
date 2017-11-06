<?php
/**
 * @file db.php
 * @author hihu (hihu@qq.com)
 **/

//实现框架自己的db功能
pi::inc(dirname(__FILE__).DOT.'db'.DOT.'Medoo.php');

class PiDb {
	private static $instance = null;
	public static function init($name = 's'){
		if(empty($name) && !is_string($name)){
			return null;
		}
		$conf = self::getConfig($name);
		if($conf == null){
			throw new Exception("check your db config for dabase :".$name, 6001);
		}
		if(!isset(self::$instance[$name])){
			self::$instance[$name] = new InnerPiDb($conf);
		}
		return self::$instance[$name];
	}
	public static function delDb($name){
		if(isset(self::$instance[$name])){
			unset(self::$instance[$name]);
		}
	}
	//解析配置文件
	private static function getConfig($name){
		$c = pi::get('db.'.$name,null);
		$conf = array();
		if(empty($c) || !is_array($c)) return null;
		//允许只有从库的情况
		// if(!isset($c['master']) && isset($c['slave'])){
		// 	$conf['master'] = $c['slave'];
		// }
		if(!isset($c['master']) && !isset($c['slave'])){
			return null;
		}else{
			$conf = $c;
		}
		foreach($conf as $k => $v){
			if($k != 'master' && $k != 'slave'){
				unset($conf[$k]);
				continue;
			}
			//端口默认 3306
			if(!isset($conf[$k]['port'])){
				$conf[$k]['port'] = 3306;
			}
			//类型默认 mysql
			if(!isset($conf[$k]['database_type'])){
				$conf[$k]['database_type'] = 'mysql';
			}
			//默认字符集
			if(!isset($conf[$k]['charset'])){
				$conf[$k]['charset'] = 'utf8';
			}
			if(!isset($conf[$k]['prefix'])){
				$conf[$k]['prefix'] = '';
			}
		}
		if(empty($conf)) return null;
		return $conf;
	}

}

class InnerPiDb {
	public $master_pdo = null;
	public $slave_pdo = null;
	public $current_pdo = null;
	public $conf = null;
	public $error_handle = false;
	private $use_master = false;
	private $read_only = true;
	private $master_methods = array('lastInsertId'=>1,'rollBack'=>1,'beginTransaction'=>1,'commit'=>1,'insert'=>1,'update'=>1,'delete'=>1,'replace'=>1);
	private $slave_methods = array('select'=>1,'get'=>1,'has'=>1,'count'=>1,'max'=>1,'min'=>1,'avg'=>1,'sum'=>1);
	public function __construct($conf){
		$this->conf = $conf;
		//如果有主库配置，关闭read_only
		if(isset($this->conf['master'])){
			$this->read_only = false;
		}
		//如果只有主库配置，强制开启使用主库
		if(isset($this->conf['master']) && !isset($this->conf['slave'])){
			$this->use_master = true;
		}
	}
	public function enableMaster(){
		if($this->read_only){
			return false;
		}
		$this->use_master = true;
	}
	public function disableMaster(){
		$this->use_master = false;
	}
	public function isEnableMaster(){
		return $this->use_master;
	}
	//错误开关决定是不是有错误就异常中断
	public function switch_error($is_open = false){
		$this->error_handle = $is_open;
	}
	//主从库，可能导致logs的值存储在不同的master和slave实例中，影响log()和last_query()
	//提供以下方法获取
	public function getLogs($type = 'master'){
		if($type == 'master' && $this->master_pdo != null){
			return $this->master_pdo->log();
		}else if($type == 'slave' && $this->slave_pdo != null){
			return $this->slave_pdo->log();
		}
		return array();
	}
	public function getLastQuery($type = 'master'){
		if($type == 'master' && $this->master_pdo != null){
			return $this->master_pdo->last_query();
		}else if($type == 'slave' && $this->slave_pdo != null){
			return $this->slave_pdo->last_query();
		}
		return '';
	}
	private function initDb($type = 'slave'){
		if($type == ''){
			return false;
		}
		if($type == 'master'){
			if($this->master_pdo != null){
				$this->current_pdo = $this->master_pdo;
				return true;
			}elseif(isset($this->conf['master'])){
				$this->master_pdo = new Util_Db_Medoo($this->conf['master']);
				$this->current_pdo = $this->master_pdo;
				return true;
			}
		}else if($type == 'slave'){
			if($this->slave_pdo != null){
				$this->current_pdo = $this->slave_pdo;
				return true;
			}else if(isset($this->conf['slave'])){
				$this->slave_pdo = new Util_Db_Medoo($this->conf['slave']);
				$this->current_pdo = $this->slave_pdo;
				return true;
			}
		}

		if($this->current_pdo == null){
			throw new Exception('can not init db with conf:'.var_export($this->conf,true),6033);
		}
	}
	//exec() 暂时不开放给外面
	//action() 函数考虑兼容性暂时不开放使用 用 commit rollback beginTransaction 代替
	//query quote select insert update delete replace get has count max min avg sum action last_query
	public function __call($method,$args){
		//action() 处理
		if($method == 'action'){
			return false;
		}
		//query() 处理
		if($method == 'query' || $method == 'exec'){
			if(!isset($args[0]) || !is_string($args[0])){
				throw new Exception("db.Error params for method query!",6055);
			}

			$pre = preg_match("/^(\s*)select/i", $args[0]);
			if($pre == 0 && $this->read_only){
				throw new Exception("db.Error can not use read only stat for sql :".var_export($args[0],true),6022);
			}
			if($pre == 0 || $this->isEnableMaster()){
				$this->initDb('master');
			}else{
				$this->initDb('slave');
			}
			
			$res = $this->current_pdo->$method($args[0]);
			$this->err();
			return $res;
		}
		//其他函数处理
		if($this->isEnableMaster()){
			$this->initDb('master');
		}else{
			//默认走salve - select get has count max min avg sum
			if(isset($this->slave_methods[$method])){
				$this->initDb('slave');
			}
			//默认走master - insert update delete replace 
			if(isset($this->master_methods[$method])){
				if($this->read_only){
					throw new Exception("db.Error can not use read only for method:".$method,6011);
				}
				$this->initDb('master');
			}
		}
		
		$res = call_user_func_array(array($this->current_pdo, $method), $args);
		$this->err();
		return $res;
	}
	public function err(){
		if($this->error_handle && $this->current_pdo != null){
			$err_code = $this->current_pdo->errorCode();
			if($err_code != '00000'){
				throw new Exception('pidb.err : '.$err_code.' - '.var_export($this->current_pdo->error(),true),6044);
			}
		}
	}
	public function __destruct(){
		// if($this->master_pdo != null && $this->master_pdo->inTransaction()){
		// 	$this->master_pdo->rollBack();
		// }
	}
//end of class
}