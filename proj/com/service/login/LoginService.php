<?php

class LoginService extends PiService{

	function dologin($str = array()){
		var_dump($str);
		//测试自动加载util
		$q = new EXQueue();
		$q->push("hihu");
		var_dump($q->top());
		$userlogin_mod = pi::mod('login','userlogin');
		$res = $userlogin_mod->doLogin();
		var_dump($res);
		//测试自动加载lib
		$hi = new TestLib();
		$s = $hi->hihu();
		var_dump($s);
		//测试自动加载composer类
		$log = new \Monolog\Logger('name');
		$log->pushHandler(new \Monolog\Handler\StreamHandler('/tmp/hihu.log', \Monolog\Logger::WARNING));

		// add records to the log
		$log->warning('Foo');
		$log->error('Bar');
	}
}
