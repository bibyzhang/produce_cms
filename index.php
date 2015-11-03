<?php
// +----------------------------------------------------------------------
// |
// +----------------------------------------------------------------------
// | 管理后台入口
// +----------------------------------------------------------------------
date_default_timezone_set('Asia/Chongqing');

//系统根目录
define('BASE_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

if(!empty($_POST['user_auth']) && $_POST['user_auth']){//兼容uploaduify图片上传
    session_id($_POST['user_auth']);
    session_start();
}else{
    session_start();
}

include BASE_PATH.'/common/base.php';

$getData = checkData($_GET);

$module = empty($getData['m']) ? '' : strtolower($getData['m']); //文件夹
$control = empty($getData['c'])? 'index' : strtolower($getData['c']); //文件{类}
$action = empty($getData['a'])? 'index' : strtolower($getData['a']); //方法

$app_id = strchr($module,'admin') ? 1 : 2;//区分操作所属应用

if($module){
    $controlFile = MODULE_PATH . $module . DIRECTORY_SEPARATOR . $control . '.php';
    if( !file_exists($controlFile) ){
        echo $module . DIRECTORY_SEPARATOR . $control . '.php'.'类文件不存在'; exit();
    }

    include($controlFile);
    $name = '\modules\\' . $module . '\\' .$control;
    $c = new $name;
    $c->$action();
    $c->setLog('select',$app_id);//记录操作日志
}else{
    //默认首页
    $smarty->display('index.html');
}