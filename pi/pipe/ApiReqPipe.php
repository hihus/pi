<?php
/**
 * @file ApiReqPipe.php
 * @author wanghe (hihu@qq.com)
 **/

class ApiReqPipe implements PiIpipe {
	public $app = null;
	
	public function execute(PiApp $app){
		$this->app = $app;
		$this->filterInput();
	}
	//对于web,可以对 get post request cookie做一些过滤
	public function filterInput(){
		//Comm::reqFilter();
	}
//end of class
}