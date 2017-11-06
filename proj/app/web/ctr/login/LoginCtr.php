<?php
class LoginCtr extends PiPageCtr{
	public function index(){
		echo "<br>to login<br>";
		var_dump($this->req('userid'));
		$this->jump('/index/index',true);
	}
	public function _after(){
		echo "<br>login after<br>";
	}
}