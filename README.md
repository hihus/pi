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
	主要框架流程：
		1 解释一个概念：pipe 管道的意思。算作框架插件化的体现点，换不同的管子，实现不同的功能
		2 request -> PiApp -> 初始化设置 -> 加载主要类库 -> 执行各种pipe(每个项目配置自己处理输入输出的插件) -> response
		3 执行pipe的概念：比如web项目,我们一般使用mvc的模式。其中的路由器在pi框架就是一个pipe，这个pipe根据输入，找到需要执行的
		  controller.
		4 常见的pipe插件已经实现在 pi/pipe 里面了，方便使用
	目录结构：
		1 pi/   核心框架内容
		2 proj/ web,task,api 三种常见的项目示例 (proj/com 目录是实现所有业务逻辑的地方)
		3 tiny/ 全部远程接口调用（不需要暴露核心业务代码）的最小框架示例(里面有pi框架下核心代码拷贝,看tiny可以了解核心工作的模式)
	进一步解释：
		1 pi/core 框架核心逻辑
		2 pi/misc 框架的一些工具和文档，比如nginx配置，异常代码说明等等
		3 pi/pipe 框架级别的管道插件，比如比较通用的web路由器，这些pipe比较通用，可以跟着框架到处分发
		4 pi/util 框架级别的工具类，比如验证码生成类，可以跟着框架到处分发
		5 pi/app  框架级别的示例类，比如app/Web.php 就是web-mvc项目的一个入口示例。虽说是示例，但是基本通用.
				  结合proj/web/index.php 两个一起看。其他的文件依此类推

		1 proj/api proj/web proj/task
			三个目录为具体项目示例：api是常见的给移动端提供接口的项目，task是定时脚本的项目，web是pc端的项目
		2 proj/com 这个目录比较重要，当然，com的地址不一定在proj下，可以自定义。这个目录是实现所有业务逻辑，操作缓存，数据库的地方
			比如你的项目叫做 滴滴打人，那么关于滴滴打人的所有业务逻辑都在这实现，如登陆注册之类。通过目录下的export目录提供对外接口
			做到一处实现，类似task,api,web项目都可以使用这块的代码。同时通过代码管理配置，可以使业务逻辑与不同项目的隔离，方便分层开发

		3 proj/com/conf   com模块的配置文件
		4 proj/com/export 接口文件，对外提供接口在此处实现
		5 proj/com/lib    项目级别的通用类库
		6 proj/com/logic  实现业务逻辑，比如登陆逻辑
		7 proj/com/model  操作数据的地方，比如操作数据库查询
		8 proj/com/pipe   项目级别的pipe插件

	api 运行方式：
		php proj/api/index.php login/new dologin -> 对应文件 proj/api/logic/login/new/LoginNew.api.php function dologin
	
	task 运行方式：
		php proj/task/index.php login_stat 1231 1234 -> 对应文件 proj/task/logic/login/Stat.php function execute($argv)
	
	web 运行方式：
		www.hihus.com/index/index -> 对应文件 proj/web/ctr/index/IndexCtr.php function index()

	接口使用方式：
		$login = Pi::com('login'); //根据配置走远程或者本地逻辑
		$users = $login->dologin($name,$pass,$type);
		print_r($users);


	更详细信息可以阅读源代码



