<?php

class LoginStat extends PiBaseTask {
	public function execute($argv){
		//test com autoload
		$login = new Logic_Login_Login();
		$login->login();
		//test model autoload
		$log_table = new Model_login_UserLogin();
		$log_table->doLogin();
	}
}