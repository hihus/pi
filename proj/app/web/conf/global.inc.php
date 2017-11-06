<?php

//全局web配置

Pcf::set('global.view_lib_path','views/smarty-3.1.27/libs/Smarty.class.php');
Pcf::set('global.view_engine','Smarty');
Pcf::set('global.view_path',PI_APP_ROOT.PI_APP_NAME.'/views/');
//Pcf::set('global.dispatcher_path',PI_CORE.'RouteDispatcher.php');
Pcf::set('global.nolog_exception',array(1022=>1,1025=>1));
Pcf::set('global.host_ip',array('10.240.210.41'=>'www.hihus.com'));