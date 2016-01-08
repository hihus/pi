<?php
//Pi::com('login') - loginExport #all代表所有接口走远程调用,配置优先级最高
Pi::set('proxy.login',
	array(
		'#all'=>array('net'=>'http','data'=>'serialize','ip'=>'api.hihus.com','timeout'=>4),
		'dologin'=>array('net'=>'http','data'=>'serialize','ip'=>'api.hihus.com','timeout'=>6),
	)
);
//Pi::com('search') - SearchExport
Pi::set('proxy.search',
	array(
		'dosearch'=>array('net'=>'http','data'=>'serialize','ip'=>'api.hihus.com','timeout'=>5),
		'beauty_search'=>array('net'=>'http','data'=>'serialize','ip'=>'api.hihus.com','timeout'=>5),
	)
);
//Pi::com('search','more') - SearchMoreExport
Pi::set('proxy.search#more',
	array(
		'dosearch'=>array('net'=>'http','data'=>'serialize','ip'=>'api.hihus.com','timeout'=>5),
		'beauty_search'=>array('net'=>'http','data'=>'serialize','ip'=>'api.hihus.com','timeout'=>5),
	)
);