<?php
//路由名字的首尾不加下划线！

Pcf::set('route.custom_router',array(
	'login/(:p)' => array('url'=>'login/index','p'=>array('userid')),
	'index/(:p)' => array('url'=>'index/index','p'=>array('userid')),
	'(:p)' => array('url'=>'index/index','p'=>array('userid')),
	'search/login/(:p)/new' => array('url'=>'index/index','p'=>array('userid')),
	));