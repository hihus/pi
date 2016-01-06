<?php
/**
 * @file InputPipe.php
 * @author wanghe (hihu@qq.com)
 * @date 2015/12/08
 * @version 1.0 
 **/

class WebTestPipe implements PiIpipe {
	public function execute(App $app){
		echo " \n web test pipe run ~ \n";
	}
}