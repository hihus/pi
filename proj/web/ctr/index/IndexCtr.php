<?php

class IndexCtr extends PiPageCtr {

	public function index(){
		
		//$this->jump('/login',true);
		echo "<br>";
		echo "in index";
		echo "<br>";
		$xz = new Xcrypt();
		$num = rand(10000,20000).rand(10000,20000).rand(10000,20000);
		$res = $xz->encode($num);
		echo $res;
		$login = picom('login');
		$res = $login->dologin(array('111'=>1241,'hihu'=>1241));
		echo ($res);

		// $q = new Queue("hihu",'users',600);
		// $q->push(array("queue"=>'真的'));
		// $q->push(array("queue"=>'真的'));
		// $q->push(array("queue"=>'真的'));
		// var_dump($q->getSize());
		// var_dump($q->pop());
		// var_dump($q->pop());
		// var_dump($q->getSize());
		// var_dump($q->clear());
		// var_dump($q->pop());
	}

	public function _before(){
		echo "before index";
	}
	
	public function _after(){
		echo "after index";
	}
} 
