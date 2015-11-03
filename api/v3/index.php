<?php
// +----------------------------------------------------------------------
// |
// +----------------------------------------------------------------------
// |
// +----------------------------------------------------------------------

ini_set("display_errors", 0);
error_reporting(1);
date_default_timezone_set('Asia/Chongqing');

define('_ACCESS_GRANT', TRUE);

// URL 模式定义
const URL_COMMON        =   0;  //普通模式
const URL_PATHINFO      =   1;  //PATHINFO模式
const URL_REWRITE       =   2;  //REWRITE模式
const URL_COMPAT        =   3;  // 兼容模式

define('BASE_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR.'../../'.DIRECTORY_SEPARATOR);
define('MODULE_PATH', BASE_PATH.'modules'.DIRECTORY_SEPARATOR);//模块文件夹地址
define('MODEL_PATH', BASE_PATH.'model'.DIRECTORY_SEPARATOR);//模型文件夹地址
define('CLASS_PATH', BASE_PATH.'common/libs/classes'.DIRECTORY_SEPARATOR);//公共类文件夹地址
define('FUNC_PATH', BASE_PATH.'common/libs/functions'.DIRECTORY_SEPARATOR);//公共函数文件夹地址
define('CACHE_PATH', BASE_PATH.'caches'.DIRECTORY_SEPARATOR);//缓存文件夹路径

require_once(FUNC_PATH . 'global.func.php');
require_once(CLASS_PATH . 'mysql.class.php');
require_once(CLASS_PATH . 'tpl.class.php');
require_once(CLASS_PATH . 'Base.class.php');

$getData = checkData($_GET);

$module = 'v3';

if( isset($_SERVER["PATH_INFO"]) ){
    list($n, $c, $a) = explode('/', $_SERVER["PATH_INFO"]);

    $control = empty($c)? 'index' : strtolower($c);
    $action = empty($a)? 'index' : strtolower($a);
}else{
    $control = !empty($getData['c']) ? $getData['c'] : 'index';
    $action = !empty($getData['a']) ? $getData['a'] : 'index';
}

$controlFile = MODULE_PATH . $module . DIRECTORY_SEPARATOR . $control . '.php';
if( !file_exists($controlFile) )
    die('Forbidden!');

require_once($controlFile);
$name = '\modules\\' . $module . '\\' . $control;
$client = new $name;

if( !method_exists($client,$action) )
    die('Forbidden!');

$client->$action();