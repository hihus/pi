<?php
/**
 * @file Proxy.php
 * @author wanghe (hihu@qq.com)
 **/

class PiProxy {
	public $mod = '';
	public $add = '';
	public $conf = '';
	public $instance = null;
	
	public function __construct($mod,$add,$conf){
		$this->mod = $mod;
		$this->add = $add;
		$this->conf = $conf;
	}
	
	//如果接口方法不需要走远程调用，实例化本地类
	public function __call($method,$args){
		//在远程调用配置里面的接口走远程调用
		if(isset($this->conf['#all']) || isset($this->conf[$method])){
			$conf = isset($this->conf['#all']) ? $this->conf['#all'] : $this->conf[$method];
			$rpc = new PiRPC();
			return $rpc->call($method,$args,$this->mod,$this->add,$conf);
		}else{
			pi_load_export_file($this->mod,$this->add);
			if (!is_callable(array($this->instance,$method))){
				throw new Exception("proxy.err $mod $add no method $method",5009);
			}
			return pi_call_method($this->instance,$method,$args);
		}
	}
	
	public function __set($n,$v){
		throw new Exception('proxy.err the com that support rpc can not set var', 5001);
	}

	public function __get($n){
		throw new Exception('proxy.err the com that support rpc can not get var', 5002);
	}
//end of class
}

//proxy server
class PiProxyServer {
	static function Server(){
		$mod = Comm::req('mod');
		$add = Comm::req('add');
		$method = Comm::req('method');
		$args = Comm::req('param',array());
		try {
			$class = picom($mod,$add,true);
			if(is_callable(array($class,$method))){
	            $reflection = new ReflectionMethod($class,$method);
	            $argnum = $reflection->getNumberOfParameters();
	            if ($argnum > count($args)) {
	                self::output("inner api call the $method from $mod $add with err args",5010);
	            }
	            //公共方法才允许被调用
	            $res = $reflection->invokeArgs($class,$args);
	            self::output($res);
	        }
			self::output("inner api not callable the $mod $add from $method fail",5009);
		} catch (Exception $e) {
			self::output("inner api execute the $mod $add from $method fail",5008);
		}
	}

	static function output($info,$err_code = false){
		ob_end_clean();
		if($err_code === false){
			echo serialize(array(INNER_RES_PACK=>$info));
		}else{
			echo serialize(array(INNER_ERR=>$err_code,'msg'=>$info));
		}
		exit;
	}
}

//RPC网络操作
class PiRPC {
	public function call($method,$params,$mod,$add,$conf){
		$sign = Pi::get('global.innerapi_sign','');
		$sign_name = Pi::get('global.innerapi_sign_name','_pi_inner_nm');
		if(isset($conf['ip']) && isset($conf['net']) && $conf['net'] == 'http'){
			$args = array();
			$args['mod'] = $mod;
			$args['add'] = $add;
			$args['method'] = $method;
			$args['param'] = $params;
			$args[$sign_name] = $sign;
			try {
				$curl = new HttpClient();
				$timeout = (isset($conf['timeout'])) ? intval($conf['timeout']) : 10;
				$res = $curl->sendPostData($conf['ip'],$args,$timeout);
				if($curl->hasError() === false){
					$data = unserialize($res);
					$data = isset($data[INNER_RES_PACK]) ? $data[INNER_RES_PACK] : $data;
					return $data;
				}else{
					throw new Exception('curl error',5011);
				}
			} catch (Exception $e) {
				return array(INNER_ERR=>5011,'msg'=>$curl->getErrorMsg());
			}
		}
		throw new Exception('inner api err conf : '.var_export($conf),5004);
	}
//end of class
}