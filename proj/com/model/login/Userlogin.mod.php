<?php

class UserloginMod {
	function doLogin(){
		
		$mem = PiMc::get('users');
		$mem->set("hihu","sai289220ks",10*60*4);
		$s = $mem->get("hihu");
		var_dump($s);

		$db = PiDb::init('ronghe');
		$sql = 'select * from userlogin where 1=1';
		$res = $db->query($sql);
		$re = array();
		while($l = $res->fetch(PDO::FETCH_ASSOC)){
			var_dump($l);
		}
		return "finish";
		
	}
}
