<?php
/**
 * @file PageCtr.php
 * @author wanghe (hihu@qq.com)
 **/

class PiPageCtr {
	protected $tpl = null;
	protected $router = null;//路由的类变量
	protected $request = null;
	protected $response = null;
	protected $isAjax = false;
	static $kv = array();

	public function setRouter($router){
		$this->router = $router;
	}
	public function setAjax($isAjax){
		$this->isAjax = $isAjax;
	}
	//内部传递
	static function iget($name,$def = ''){
		if(isset(self::$kv[$name])){
			return self::$kv[$name];
		}
		return $def;
	}
	//内部传递
	static function iset($name,$value){
		$ret = 1;
		if(isset(self::$kv[$name])){
			$ret = 2;
		}
		self::$kv[$name] = $value;
		return $ret;
	}

	protected function get($k,$def = ''){
		return Comm::get($k,$def);
	}

	protected function post($k,$def = ''){
		return Comm::post($k,$def);
	}

	protected function req($k,$def = ''){
		return Comm::req($k,$def);
	}

	protected function getCookie($k,$def = ''){
		return Comm::getCookie($k,$def);
	}

	protected function _echo($str){
		echo $str;
	}
	protected function setCookie($name,$value,$expire = '',$path = '/'){
		return Comm::setCookie($name,$value,$expire,$path);
	}

	protected function setSession($name,$value){
		return Comm::setSession($name,$value);
	}

	protected function getSession($name,$def = ''){
		return Comm::getSession($name,$def);
	}

	public function assign($k,$v,$is_remove_xss = false){
		if($is_remove_xss !== false){
			$v = Comm::filterOutput($v);
		}
		$this->tpl->assign($k,$v);
	}

	protected function display($tmpl){
		$this->tpl->display($tmpl);
	}

	public function initTmpl(){
		$view_path = Pcf::get('global.view_path',PI_APP_ROOT.'view/views/');
		$view_cache_path = Pcf::get('global.view_cache_path',PI_APP_ROOT.'view/cache/');
		$view_compile_path = Pcf::get('global.view_compile_path',PI_APP_ROOT.'view/views_c/');
		if(null == $this->tpl){
			$cls = Pcf::get('global.view_engine');
			$this->tpl = new $cls();
			$this->tpl->setTemplateDir($view_path);
			$this->tpl->setCompileDir($view_compile_path);
			$this->tpl->setCacheDir($view_cache_path);
			$this->tpl->left_delimiter = '<{';
   			$this->tpl->right_delimiter = '}>';
		}
	}

	protected function jump($url,$inner = false){
		if($inner === false){
			Comm::jump($url);
		}else{
			$this->router->dispatch($url);
		}
	}
	//判断登陆，一般web项目都会用到的,预留
	protected function isLogin(){
		return false;
	}
	//预留
	public function _p_before(){

	}
	//预留
	public function _p_after(){

	}

}
