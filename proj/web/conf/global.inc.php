<?php

//全局web配置
//Pcf::Set("hihu","hello");

Pcf::set('global.view_lib_path','util/views/smarty-3.1.27/libs/Smarty.class.php');
Pcf::set('global.view_engine','Smarty');
Pcf::set('global.view_path',APP_ROOT.'view/views/');
//Pcf::set('global.dispatcher_path',PI_CORE.'RouteDispatcher.php');
Pcf::set('global.dispatcher_path',PI_CORE.'RouteDispatcher.php');
Pcf::set('global.nolog_exception',array(1022=>1,1025=>1));