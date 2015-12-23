<?php
// +----------------------------------------------------------------------
// | @file daemon.php
// +----------------------------------------------------------------------
// | @desc 守护进程入口
// +----------------------------------------------------------------------
// | @author bibyzhang90@gmail.com
// +----------------------------------------------------------------------

/** 根路径 */
define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

/** 入口定义 */
define('PRODUCE_DAEMON_GRANT', true);

//调试模式 开发阶段开启 True 部署阶段注释或者设为 False
define('APP_DEBUG', True);

/** 访问限制 只能通过命令行执行 */
if (PHP_SAPI != "cli")
    die('Forbidden!');

/** 必须参数 */
$param_arr = getopt('t:');
$type = $param_arr['t'];
if( empty($type) )
    die('Forbidden!');

//必须函数
if ( !function_exists('pcntl_fork') )
{
    if (APP_DEBUG)
        throw new Exception('PCNTL functions not available on this PHP installation');
    else
        die('Error!');
}

//包含项目入口文件
require_once(BASE_PATH . '/common/base.php');

/** 自动加载 modules */
function autoload_modules($class)
{
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    include BASE_PATH . $class . '.php';
}
spl_autoload_register('autoload_modules');

/** test */
if( $type=='test' )
{
    $client = new \app\daemon\modules\test_term();
}