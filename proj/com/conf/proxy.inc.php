<?php
//pi::com('login') - loginService #all代表所有接口走远程调用,配置优先级最高
pi::set('proxy.login',
	array(
		//'#all'=>array('net'=>'http','data'=>'serialize','ip'=>'api.hihus.com','timeout'=>4),
		//'dologin'=>array('net'=>'http','data'=>'serialize','ip'=>'api.hihus.com','timeout'=>6),
	)
);
//pi::com('search') - SearchService
pi::set('proxy.search',
	array(
		'dosearch'=>array('net'=>'http','data'=>'serialize','ip'=>'api.hihus.com','timeout'=>5),
		'beauty_search'=>array('net'=>'http','data'=>'serialize','ip'=>'api.hihus.com','timeout'=>5),
	)
);
//pi::com('search','more') - SearchMoreService
pi::set('proxy.search#more',
	array(
		'dosearch'=>array('net'=>'http','data'=>'serialize','ip'=>'api.hihus.com','timeout'=>5),
		'beauty_search'=>array('net'=>'http','data'=>'serialize','ip'=>'api.hihus.com','timeout'=>5),
	)
);