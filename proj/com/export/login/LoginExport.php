<?php

class LoginExport extends PiExport{

	function dologin($str = array()){
		$cls = new Logic_Login_Login();
		$res = $cls->login();
		return var_export($str,true).$res;
	}
}
