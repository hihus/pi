<?php
/**
 * @file ApiReqPipe.php
 * @author hihu (hihu@qq.com)
 **/

class ApiReqPipe implements PiIpipe {
	public $app = null;
	
	public function execute(PiApp $app){
		$this->app = $app;
		$this->filterInput();
	}
	//线上环境可以在这里对输入进行解密处理
	public function filterInput(){
		//Comm::reqFilter();
	}
//end of class
}