<?php
// +----------------------------------------------------------------------
// | 任玩堂游戏库
// +----------------------------------------------------------------------
// | 计划任务入口
// +----------------------------------------------------------------------

/** 计划任务根路径 */
define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

/** 入口定义 */
define('APPGAME_CRONTAB_GRANT', true);

//调试模式 开发阶段开启 True 部署阶段注释或者设为 False
define('APP_DEBUG',False);

/** 访问限制 只能通过命令行执行 */
if (PHP_SAPI != "cli")
    die('Forbidden!');

/** 必须参数 */
$param_arr = getopt('t:');
$type = $param_arr['t'];
if( empty($type) )
    die('Forbidden!');

/** 自动加载 class */
function autoload_modules($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    include BASE_PATH . $class . '.class.php';
}
spl_autoload_register('autoload_modules');

//包含项目入口文件
require_once(BASE_PATH . '/common/base.php');

//图片下载存放地址
define('IMAGE_PATH', BASE_PATH . '../');//图片下载存放地址

//执行
if( $type=='strategy_search' ){
    $client = new \test\test();
    $client->test_deal();
}