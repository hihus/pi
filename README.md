###introduce:
	php framework PI （目前本项目未经作者允许不能应用于其他项目，也不能基于本框架二次开发）

###depends:
	php >= 5.2.0
	webserver open path_info

###features:   
	1 轻量+插件化：核心功能支持核心业务逻辑，不同插件支持不同项目逻辑(如app.php支持com模块,web.php+路由插件支持web-mvc项目)
	2 强调代码管理：追求严格的代码格式控制
	3 支持远程调用：每个接口的方法都可以配置是否远程调用，服务器和客户端无需做任何调整
	

###usage:
	目录结构：
		1 pi/   核心框架内容
		2 proj/ web,task,api 三种常见的项目示例
		3 tiny/ 全部远程接口调用（不需要暴露核心业务代码）的最小框架示例(里面有pi框架下核心代码拷贝)