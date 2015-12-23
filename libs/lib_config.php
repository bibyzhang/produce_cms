<?php
// +----------------------------------------------------------------------
// | @file lib_config.php
// +----------------------------------------------------------------------
// | @desc 系统入口文件
// +----------------------------------------------------------------------
// | @author bibyzhang90@gmail.com
// +----------------------------------------------------------------------

define('APPGAME_ACCESS_GRANT', TRUE);

//系统配置
require_once( BASE_PATH . DIRECTORY_SEPARATOR . 'app/configs/app.php');
//常量定义
define('APP_DEBUG', $conf['app_debug']);

//自动加载 modules
function autoload_modules($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    $start = substr($class, 0, strrpos($class, '/'));
    $end = substr($class, strrpos($class, '/')+1, strlen($class));

    include BASE_PATH . $start . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $end . '.php';
}
spl_autoload_register('autoload_modules');

if( APP_DEBUG )
{
    ini_set("display_errors", 1);
    error_reporting(E_ALL^E_NOTICE);
} else {
    ini_set("display_errors", 0);
    error_reporting(0);
}

if( !ini_get('date.timezone') )
{
    date_default_timezone_set('Asia/Chongqidng');
}


//系统核心类
require_once( BASE_PATH . DIRECTORY_SEPARATOR . 'libs/Core/Core.php');

//系统函数
require_once( BASE_PATH . DIRECTORY_SEPARATOR . 'libs/function/functions.php');