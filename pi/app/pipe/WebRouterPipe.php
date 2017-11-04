<?php
/**
 * @file WebRouterPipe.php
 * @author hihu (hihu@qq.com)
 **/

class WebRouterPipe implements PiIpipe {
	public function __construct(){
		$dispatcher = Pcf::get('global.dispatcher_path',PIPE_HELPER.'RouteDispatcher.php');
		if(!pi::inc($dispatcher)){
			throw new Exception('can not find the dispatcher config : global.dispatcher_path',1032);
		}
	}

	public function execute(PiApp $app){
		//开始路由,query参数需要有url和param两个变量。方便路由选择
		$dispatcher = new PiRouteDispatcher();
		$dispatcher->run();
	}
//end of class
}