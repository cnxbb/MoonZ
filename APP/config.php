<?php  //CODE BY ZMZ
// MoonZ Framework Config

//定义日志文件
define( 'MZ_Global_Log_File', $_SERVER['DOCUMENT_ROOT'] . '/../APP/logs/app_log.txt' );

//定义数据库日志文件
define( 'MZ_Global_DBLog_File', $_SERVER['DOCUMENT_ROOT'] . '/../APP/logs/db.error.log' );

//是否打开调试日志
define( 'MZ_EnableDebugLog', true );

//预定义数据库访问相关
require_once 'config.db.php';

// 路由白名单
$route = array('/','logout/','login/','signin/','/api/','/user/menu/','/user/switch/','/user/main/');

define('WL_ROUTE', $route);

//预定义Memcache访问相关
define( 'MZ_MEMCACHE_Host', '127.0.0.1' );
define( 'MZ_MEMCACHE_Port', '11211' );

//定义错误页面
define( 'MZ_Error_Page_404', MZ_Root_Path . 'webroot/404.html' );
define( 'MZ_Error_Page_400', MZ_Root_Path . 'webroot/400.html' );
define( 'MZ_Error_Page_500', MZ_Root_Path . 'webroot/500.html' );

//添加相关路径到包含路径
set_include_path( get_include_path() . PATH_SEPARATOR . MZ_Root_Path );
set_include_path( get_include_path() . PATH_SEPARATOR . MZ_Root_Path . 'APP/.MoonZ/');
set_include_path( get_include_path() . PATH_SEPARATOR . MZ_Root_Path . 'APP/controllers/' );
set_include_path( get_include_path() . PATH_SEPARATOR . MZ_Root_Path . 'APP/models/' );
set_include_path( get_include_path() . PATH_SEPARATOR . MZ_Root_Path . 'APP/models/dbclass/' );
set_include_path( get_include_path() . PATH_SEPARATOR . MZ_Root_Path . 'APP/models/Function/' );
set_include_path( get_include_path() . PATH_SEPARATOR . MZ_Root_Path . 'APP/models/wxpay/' );
set_include_path( get_include_path() . PATH_SEPARATOR . MZ_Root_Path . 'APP/view/' );
set_include_path( get_include_path() . PATH_SEPARATOR . MZ_Root_Path . 'APP/vendor/' );

//引入全局常量定义文件
require_once 'GlobalDefine.php';

//引入全局函数
require_once 'GlobalFnc.php';
