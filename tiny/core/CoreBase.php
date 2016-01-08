<?php
/**
 * @file CoreBase.php
 * @author wanghe (hihu@qq.com)
 * 如果有扩展需求单独拆分
 **/

//api逻辑基类
abstract class PiBaseApi {
	
}
//task定时任务脚本基类
abstract class PiBaseTask {
	abstract public function execute($argv);
}
//操作数据库逻辑基类
class PiBaseModel {
	public function __construct(){}
}
//接口基类
class PiExport {
	public $export_name = '';
	public function __construct(){}
}
//pipi接口
interface PiIpipe {
	public function execute(PiApp $app);
}
