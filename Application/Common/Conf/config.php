<?php
return array(
	/* 模块相关配置 */
	
	'DEFAULT_MODULE'     => 'Home',		// 设置默认访问的模块
	
	/* 调试配置 */
	'SHOW_PAGE_TRACE' => false,	

	/* 用户相关设置 */
	'USER_MAX_CACHE'     => 1000, //最大缓存用户数
	'USER_ADMINISTRATOR' => 1, //管理员用户ID


	/* 全局过滤配置 */
	'DEFAULT_FILTER' => '', //全局过滤函数

	/* URL配置 */
	'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
	'URL_MODEL'            => 2, //URL模式  默认关闭伪静态
	'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
	'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符
	'URL_HTML_SUFFIX'       => 'html',  // URL伪静态后缀设置


	/* 数据库配置 */
	'DB_TYPE'   => 'mysql', // 数据库类型
	//服务器数据库地址
	'DB_HOST'   => 'localhost', // 服务器地址
	'DB_NAME'   => 'yaj', // 数据库名
	'DB_USER'   => 'root', // 用户名
	'DB_PWD'    => '123456',  // 密码
	
	//本机测试数据库
	// 'DB_HOST'   => 'localhost', // 服务器地址
	// 'DB_NAME'   => 'yianju', // 数据库名
	// 'DB_USER'   => 'root', // 用户名
	// 'DB_PWD'    => '',  // 密码


	'DB_PORT'   => '3306', // 端口
	'DB_PREFIX' => 'yaj_', // 数据库表前缀
	'RBAC_SUPERADMIN'=>'admin',//超级管理员账户
    'ADMIN_AUTH_KEY'=>'superadmin',//存储超级管理员识别
    'USER_AUTH_ON'=>true,//是否需要认证
	'USER_AUTH_TYPE'=>'', // 1登陆认证2：实时认证认证类型
	'USER_AUTH_KEY'=>'uid', //用户认证识别号
	// 'REQUIRE_AUTH_MODULE'  //需要认证模块
	'NOT_AUTH_MODULE'=>'', //无需认证模块
	'NOT_AUTH_ACTION'=>'', //无需验资的方法
	'RBAC_ROLE_TABLE'=>'yaj_role',//角色表名称
	'RBAC_USER_TABLE'=>'yaj_role_admin', //用户表名称
	'RBAC_ACCESS_TABLE'=>'yaj_access', //权限表名称
	'RBAC_NODE_TABLE'=>'yaj_node', //节点表名称

	'huanxin' =>array(
	    'client_id' => 'YXA6xRNvEJeBEeenqx9qi9pkXA',
	    'client_secret' => 'YXA68VmVyrx7BIGxbKx5-5UtOfYF7PA',
	    'appkey' => '1179161102115367#test',
	    'app_name' => 'jiazhang',
	    'access_token' => '',
	    'expires_in' => '',
	    'application' => '',
	  ),
	// define(__PASE__, 'http://yianjukeji.com/Code/Uploads/');
	 //    /* 模板相关配置 */
	'PASE'=>'http://192.168.31.210/Jzc/Uploads/',
  //   'TMPL_PARSE_STRING' => array(
		// 'PASE'=>'http://yianjukeji.com/Code/Uploads/',
  //   ),
);