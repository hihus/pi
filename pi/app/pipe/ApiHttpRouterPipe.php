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
		if(!pi::inc(PIPE_HELPER.$router)){
			throw new Exception('api.router can not find the api router : '.$router,1030);
		}
		if(class_exists($router_class)){
			$cls = new $router_class($app);
			$res = $cls->dispatch();
			//线上环境请处理输出做加密
			$cls->output($res);
		}else{
			throw new Exception('api.router can not find the router class : '.$router_class,1031);
		}
	}

//end of class
}