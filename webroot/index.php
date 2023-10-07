<?php  //CODE BY ZMZ
// MoonZ Framework APP
header('X-Powered-By:MoonZ');
ini_set('date.timezone','Asia/Shanghai');
ini_set('display_errors', 0 );
session_start();

//预定义程序目录
define( 'MZ_Root_Path', $_SERVER['DOCUMENT_ROOT'] . '/../' );
define( 'MZ_Conrtoller_Path', MZ_Root_Path . 'APP/controllers/' );
define( 'MZ_View_Path', MZ_Root_Path . 'APP/views/' );

set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'] . '/../APP/' );
set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'] . '/../APP/.MoonZ/' );
set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'] . '/../APP/controllers/' );
set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'] . '/../APP/models/' );
set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'] . '/../APP/views/' );

require_once $_SERVER['DOCUMENT_ROOT'] . '/../APP/.MoonZ/core.class.php';

$MZ = new MZ_APP();

$MZ->run();
