<?php
/**
 * @file CoreBase.php
 * @author wanghe (hihu@qq.com)
 * 如果有扩展需求单独拆分
 **/

abstract class PiBaseApi {
	
}

class PiBaseModel {
	public function __construct(){}
}

abstract class PiBaseTask {
	abstract public function execute($argv);
}

abstract class PiBaseTask {
	abstract public function execute($argv);
}

class PiExport {
	public $export_name = '';
	public function __construct(){}
}

interface PiIpipe {
	public function execute(PiApp $app);
}
