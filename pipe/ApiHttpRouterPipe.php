<?php
/**
 * @file ApiHttpRouterPipe.php
 * @author wanghe (hihu@qq.com)
 **/

class ApiHttpRouterPipe implements PiIpipe {
	public $app = null;
	
	public function execute(PiApp $app){
		$this->app = $app;
 		$router = Pcf::get('global.router_file','ApiRouter.php');
		$router_class = Pcf::get('global.router_class','PiApiRouter');
		if(is_readable(PI_CORE.$router)){
			Pi::inc(PIPE_HELPER.$router);
		}else{
			throw new Exception('api.router can not find the api router : '.$router,1030);
		}
		if(class_exists($router_class)){
			$cls = new $router_class($app);
			$cls->dispatch();
		}else{
			throw new Exception('api.router can not find the router class : '.$router_class,1031);
		}
	}

//end of class
}