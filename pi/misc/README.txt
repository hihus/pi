*************************************************
* A PHP FRAMEWORK DEVELOP BY HIHU @ INC.JIAYUAN *
* AUTHOR: HIHU (hihu@qq.com)                    *
*   DATE: 2015-12-08                            *
*************************************************
代码命名规则：

	强规范：
		1 类定义开头大写，驼峰               class LoginStat {}
		2 方法和函数开头小写，驼峰			  function initLogin()
		3 变量全部小写+下划线分割			  $nice_girl
		3 protected方法 "_" 开头小写，驼峰   protected function _initLogin()
		4 private方法 "__" 开头小写，驼峰    private function __initLogin()
		5 常量全部大写					  define("PI_CORE","A");
		6 静态变量 "s" 开头，驼峰	          $sLoginName
		7 文件名大写开头
	弱规范：
		1 数组变量使用 "a" 开头，驼峰，静态变量 "sa"，常量"ca"
		2 数值变量使用 "n" 开头，驼峰，静态变量 "sn"，常量"cn"
		3 字符串变量用 "t" 开头，驼峰，静态变量 "st"，常量"ct"
		4 类变量使用   "o" 开头，驼峰

数据库规范：

	1 每个表，每个字段需要有详细注释，
	  每个项目建表语句需要保存在 com目录下的tables目录下，规范：库名目录/表名.txt
	2 重要业务表一律不允许自增字段出现，增量id靠统计接口给出(独立的inc_id库表)
	3 每个表都要有一个主键
	4 业务代码内尽量避免join
	5 库名小写。表名小写，表名可用下划线区分
	6 如果没有特殊要求，使用innodb存储引擎

全局注意事项：
	1 框架路由依赖开启webserver的path_info配置
	2 需要保存日志和模板的目录给适当的可写权限
	3 使用pi框架的所有文件包含使用统一的框架函数，不允许出现 include ,require, *_once
	4 禁止代码内出现任何域名和ip地址，都需要走配置文件
	5 禁止直接使用外来变量_GET,_POST,_REQUEST,_SERVER,_HTTP_*，全部需要用公共库获取
	6 不同的配置项目尽可能新建配置文件，保证拆分，修改减少错误影响的范围
	7 代码上线前需要review代码规范，注释。
	8 最小化加载pi框架，即只使用core功能，需要加上util的HttpClient的类，或者自行替代

TODO:
	1 注释检测方法
	2 文档生成方法
	3 一键生成工程目录结构
