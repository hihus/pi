<?php

class Model_Login_UserLogin extends PiBaseModel {
	function doLogin(){
		// $db = PiDb::init('hihus');
		// $sql = 'select * from users where 1=1';
		// $res = $db->query($sql);
		// while($l = $res->fetch(PDO::FETCH_ASSOC)){
		// 	print_r($l);
		// }
		$mem = PiMc::get('users');
		$mem->set("hihu","100",10*60);
		$s = $mem->get("hihu");
		var_dump($s);
	}
}
