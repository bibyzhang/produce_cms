<?php
// +----------------------------------------------------------------------
// | @name base.php
// +----------------------------------------------------------------------
// | @desc 配置文件
// +----------------------------------------------------------------------
// | @author bibyzhang90@gmail.com
// +----------------------------------------------------------------------

//入口定义
define('PRODUCE_ACCESS_GRANT', TRUE);

//调试模式
if( APP_DEBUG ){
    ini_set("display_errors", 1);
    error_reporting(E_ALL^E_NOTICE);
}else{
    ini_set("display_errors", 0);
    error_reporting(0);
}

//时区设置
date_default_timezone_set('Asia/Chongqidng');

//缓存文件夹地址
define('CACHE_PATH', BASE_PATH.'caches'.DIRECTORY_SEPARATOR);
//公共类文件夹地址
define('CLASS_PATH', BASE_PATH.'common/libs/classes'.DIRECTORY_SEPARATOR);
//公共函数文件夹地址
define('FUNC_PATH', BASE_PATH.'common/libs/functions'.DIRECTORY_SEPARATOR);
//模型文件夹地址
define('MODEL_PATH', BASE_PATH.'model'.DIRECTORY_SEPARATOR);
//模块文件夹地址
define('MODULE_PATH', BASE_PATH.'modules'.DIRECTORY_SEPARATOR);
//游戏库静态资源地址
define('STATIC_PATH',BASE_PATH . 'statics' . DIRECTORY_SEPARATOR);

//动态域名地址
//define('APP_PATH',pc_base::load_config('system','app_path'));
require(CACHE_PATH . 'configs/system.php');
define('APP_PATH',$systemArr['app_path']);

//图片地址
define('IMG_PATH',$systemArr['img_path']);

//图片下载存放地址
define('IMAGE_PATH', BASE_PATH . '../');//图片下载存放地址

//前端模板目录
define('TPL_PATH',BASE_PATH);

//图片展示域名
define('IMAGES_PATH',$systemArr['images_path']);
//生成文件路径
define('HTML_PATH', BASE_PATH.'..'.DIRECTORY_SEPARATOR);

//IOS PLIST 路径
define('XML_PATH', BASE_PATH . '..' . DIRECTORY_SEPARATOR);

//加载Smarty
/*
require_once(BASE_PATH . 'Smarty/Smarty.class.php');
$smarty = new Smarty();*/

require_once(BASE_PATH . 'Smarty/SmartyBC.class.php');
$smarty = new SmartyBC();

$smarty->compile_dir = BASE_PATH . "templates_c/"; //设置编译目录
$smarty->template_dir = BASE_PATH . "templates/"; //设置模板目录

//左右边界符
$smarty->left_delimiter = "<{";
$smarty->right_delimiter = "}>";

$smarty->caching = false; //设置缓存方式
$smarty->cache_lifetime = 86400; //设置缓存时间

$smarty->debugging = false;

//加载公用函数库
require_once(FUNC_PATH . 'global.func.php');

//加载公用类库
require_once(CLASS_PATH . 'mysql.class.php');
require_once(CLASS_PATH . 'Ftp.class.php');
require_once(CLASS_PATH . 'tpl.class.php');

//引入图片处理库
require(CLASS_PATH . 'ThinkImage.class.php');
require(CLASS_PATH . 'Gd.class.php');
require(CLASS_PATH . 'Image.class.php');

//beanstalk
require(CLASS_PATH . 'beanstalk.class.php');

require_once(CLASS_PATH . 'Base.class.php');