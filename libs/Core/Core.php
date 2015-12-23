<?php
// +----------------------------------------------------------------------
// | @name Core.php
// +----------------------------------------------------------------------
// | @desc 系统核心类
// +----------------------------------------------------------------------
// | @author bibyzhang90@gmail.com
// +----------------------------------------------------------------------

//加载核心的文件
//require_once __DIR__ . '/Loader.php';
//require_once __DIR__ . '/ModelLoader.php';
//require_once __DIR__ . '/PluginLoader.php';
//
//use Swoole\Exception\NotFound;
namespace Core;

/**
 * 系统核心类，外部使用全局变量$php引用
 * Swoole框架系统的核心类，提供一个swoole对象引用树和基础的调用功能
 */
class Core
{
    public $db;
    public $db1;
    public $db2;
    public $redis;

    protected $smarty;
    protected $tpl;

    /**
     * 参数设置
     */
    private $ParmArr = array(
        'fileUpSize' => '2097152',//2M byte 10 * 1024 * 1024 //允许上传文件大小
        'timeout' => 120//远程图片下载超时时间
    );

    /** 设置操作数据表 */
    private $OperateTable = array(
        'AdminUserTable' => 'app_new_admin_user',//管理员表
        'AdminUserRoleTable' => 'app_new_admin_user_role',//管理员权限表
        'AdminMenuTable' => 'app_new_admin_menu',//管理员权限表
        'BaseCommonSettingTable' => 'app_new_base_common_setting',//公用信息设置
        'GameInfoDetailTable' => 'app_new_game_info_detail',//游戏详细信息表
        'PlatformListTable' => 'app_new_platform_list',//平台列表
        'CompilationTypeTable' => 'app_new_compilation_type',//合集类型
        'GuidesCategoryTable' => 'app_guides_category',//栏目表
        'GamepassageContentTable' => 'app_new_passage_content',//文章内容表
        'GameVideoTable' => 'app_new_game_video',//游戏视频
    );

    public function __construct(){
        $this->db = new Mysql();

        //Redis
        $this->redis = new \Redis();
        $this->redis->connect('localhost', 6379);

        global $smarty;
        $this->s = $smarty;

        $this->tpl = new \tpl() ;
        $this->tpl->debug = DEBUG_MODE ;                         //是否开启调试模式
        $this->tpl->tpl_dir = TPL_PATH ;                         //模板目录
        $this->tpl->tpl_cache = CACHE_PATH.'compile/' ;    //模板编译缓存目录

        //路径替换
        $path_replace = array(
            0=>array('search'=>'./js/','replace'=>TPL_PATH.'html/'.'js/'),
            1=>array('search'=>'./css/','replace'=>TPL_PATH.'html/'.'css/'),
            2=>array('search'=>'./images/','replace'=>TPL_PATH.'html/'.'images/'),
            3=>array('search'=>'./img/','replace'=>TPL_PATH.'html/'.'img/'),
            4=>array('search'=>'../common/','replace'=>TPL_PATH.'html/common/'),
        );
        $this->tpl->tpl_replace = array_merge($this->tpl->tpl_replace,$path_replace);
    }

    /**
     * 重载
     */
    public function __call($name, $arguments){
        if( APP_DEBUG )
            throw new Exception('方法不存在{函数:'.$name.',(参数:<br />'. $arguments . ')}');
        else
            die('Error!');
    }

    /**
    +--------------------------------------------------
     * 文件上传
    +--------------------------------------------------
     * @param $data 文件域 {eg:$_FILES}
     * @param $key 文件域名 {eg:img_upload}
     * @param $fileType 存放文件类型 [image||data]
     * @param $fileCate 文件分类存放 {即存放目录,eg:game|guides}
     * @param $img_type 图片类型{eg:图标:icon,截图:screenshot}
     * @param $upload_id 上传ID,区分游戏或文章{eg:game_id||passage_id}
     * @param $height 上传文件高度
     * @param $width 上传文件宽度
     * @param $allowFileFormat 允许文件格式
    +--------------------------------------------------
     * @return string[$file_path][成功] 上传文件相对地址
     * eg:'http://www.xxx.com/images/xxx/20140901/201409015555_$heightX$width.jpg'
     * @return num -1:未提交任何内容 -2:非POST上传 -3:文件上传异常 -4:文件格式错误 -5:文件大小超出限制 -6:创建文件目录失败 -7:文件移动失败 -8:缺少上传文件类型 -9:缺少文件分类存放目录 -10:缺少文件高度 -11:缺少文件宽度
    +--------------------------------------------------
     */
    protected function file_upload($data,$key,$fileType,$fileCate,$img_type,$upload_id,$height,$width,$allowFileFormat=array('jpg','jpeg','gif','bmp','png')){
        //是否有上传内容
        if( empty($data[$key]['name']) )
            return -1;

        //文件是否是通过 HTTP POST 上传的
        $is_post = is_uploaded_file($data[$key]['tmp_name']);
        if( !$is_post )
            return -2;

        //文件是否上传成功
        if( $data[$key]['error'] != 0 )
            return -3;

        //获取上传文件名称
        $upload_file_name = $data[$key]['name'];

        //判断文件扩展名
        $extend_name = strtolower(str_replace(".","",strrchr(trim($data[$key]['name']),".")));
        if( !in_array($extend_name,$allowFileFormat) )
            return -4;

        //判断文件大小
        $file_size = $_FILES[$key]['size'];
        $sys_upload_size = ini_get('upload_max_filesize'); //php.ini配置允许文件上传大小[M]
        $sys_upload_size = $sys_upload_size * 1024 * 1024; //[byte]
        $file_upload_size = $this->ParmArr['fileUpSize']; //限制文件大小
        if( $file_size > $sys_upload_size || $file_size > $file_upload_size )
            return -5;

        if( !$fileCate )
            return -9;

        if($fileType=='image' && !$height)
            return -10;

        if($fileType=='image' && !$width)
            return -11;


        //文件上传路径
        if(!$fileType){
            return -8;
        }elseif($fileType=='image'){
            $dir = 'images' . DIRECTORY_SEPARATOR;
        }elseif($fileType=='data'){
            $dir = 'packages' . DIRECTORY_SEPARATOR;
        }

        //路径&&图片名称
        $year = date("Y",time());
        $month = date("m",time());
        $date = date("d",time());
        $hour = date("H",time());
        $minute = date("i",time());
        $second = date("s",time());

        //上传目录不存在，创建目录
        $img_path = $dir . $fileCate . DIRECTORY_SEPARATOR;
        $dir_path = IMAGE_PATH . $dir;
        if( !file_exists($dir_path) )
        {
            $mdir = @mkdir($dir_path, 0777);
            if( !$mdir ){
                return -3;
            }
        }
        $dir_path = $dir_path . $fileCate . DIRECTORY_SEPARATOR;
        if( !file_exists($dir_path) )
        {
            $mdir = @mkdir($dir_path, 0777);
            if( !$mdir ){
                return -3;
            }
        }
        if($upload_id>0){
            $dir_path .= $upload_id . DIRECTORY_SEPARATOR;
            $img_path .= $upload_id . DIRECTORY_SEPARATOR;
            if( !file_exists($dir_path) )
            {
                $mdir = @mkdir($dir_path, 0777);
                if( !$mdir ){
                    return -3;
                }
            }
        }
        $dir_path .= $img_type . DIRECTORY_SEPARATOR;
        $img_path .= $img_type . DIRECTORY_SEPARATOR;
        if( !file_exists($dir_path) )
        {
            $mdir = @mkdir($dir_path, 0777);
            if( !$mdir ){
                return -3;
            }
        }

        //保存文件名称
        if($fileType=='image'){
            if( $img_type=='icon' )
                //$r_name = $upload_file_name;
                $r_name = substr($upload_file_name,0,strrpos($upload_file_name, '.'));
            else
                $r_name = MD5($year . $month . $date . $hour . $minute . $second . rand()) . '_' . $height . 'X' . $width;
        }elseif($fileType=='data'){
            $r_name = MD5($year . $month . $date . $hour . $minute . $second . rand());
        }

        $r_name .= '.' . $extend_name;
        $file_path = $dir_path . $r_name;

        //将文件移动到指定上传目录
        $tmp_path = $_FILES[$key]['tmp_name'];
        $is_upload = @move_uploaded_file($tmp_path,$file_path);

        if( !$is_upload ) {
            return -7;
        }else {
            $icon_path = DIRECTORY_SEPARATOR . $img_path . $r_name;
            return $icon_path;
        }
    }

    /**
    +--------------------------------------------------
     * 下载远程图片
    +--------------------------------------------------
     * @param $remote_url 远程图片地址
     * @param $file_type 图片类别{即图片存放目录,eg:game|guides}
     * @param $upload_id 上传ID,区分游戏或文章{eg:game_id||passage_id}
     * @param $img_type 图片类型{eg:图标:icon,截图:screenshot}
     * @param $allowFileFormat 允许文件格式
    +--------------------------------------------------
     * @return string[$file_path][成功] 上传文件相对地址
     * eg:'/images/xxx/20140901/201409015555_$heightX$width.jpg'
     * @return num -1:未设置下载超时限制 -2:文件格式错误 -3:创建目录失败 -4:图片下载失败
    +--------------------------------------------------
     */
    public function get_remote_img($remote_url, $file_type, $upload_id, $img_type, $allowFileFormat=array('jpg','jpeg','gif','bmp','png')){
        if ( empty($this->ParmArr['timeout']) ){
            return -1;
        }

        //获取图片名称
        $img = substr($remote_url,strrpos($remote_url, '/')+1);
        $img_name = substr($img,0,strrpos($img, '.'));

        //检测文件类型
        $extend_name = strtolower(str_replace(".","",strrchr(trim($remote_url),".")));//扩展名
        if( !in_array($extend_name,$allowFileFormat) ){
            return -2;
        }

        //图片尺寸
        list($width, $height, $type, $attr) = getimagesize($remote_url);

        $dir = 'images' . DIRECTORY_SEPARATOR;
        $fileCate = $file_type;
        //图片名称：年月日时分秒加随机数 . '_' . '宽度*高度' . '后缀'
        //其他数据包名称
        $year = date("Y",time());
        $month = date("m",time());
        $date = date("d",time());
        $hour = date("H",time());
        $minute = date("i",time());
        $second = date("s",time());

        //上传目录不存在，创建目录
        $img_path = $dir . $fileCate . DIRECTORY_SEPARATOR;
        $dir_path = IMAGE_PATH . $dir;
        if( !file_exists($dir_path) )
        {
            $mdir = @mkdir($dir_path, 0777);
            if( !$mdir ){
                return -3;
            }
        }
        $dir_path = $dir_path . $fileCate . DIRECTORY_SEPARATOR;
        if( !file_exists($dir_path) )
        {
            $mdir = @mkdir($dir_path, 0777);
            if( !$mdir ){
                return -3;
            }
        }
        $dir_path .= $upload_id . DIRECTORY_SEPARATOR;
        $img_path .= $upload_id . DIRECTORY_SEPARATOR;
        if( !file_exists($dir_path) )
        {
            $mdir = @mkdir($dir_path, 0777);
            if( !$mdir ){
                return -3;
            }
        }
        $dir_path .= $img_type . DIRECTORY_SEPARATOR;
        $img_path .= $img_type . DIRECTORY_SEPARATOR;
        if( !file_exists($dir_path) )
        {
            $mdir = @mkdir($dir_path, 0777);
            if( !$mdir ){
                return -3;
            }
        }
        if( $img_type=='icon' )
            $r_name = $r_name = $img_name;
        else
            $r_name = MD5($year . $month . $date . $hour . $minute . $second . rand()) . '_' . $height . 'X' . $width;
        $r_name .= '.' . $extend_name;
        $file_path = $dir_path . $r_name;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->ParmArr['timeout']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $temp = curl_exec($ch);
        if(@file_put_contents($file_path, $temp) && !curl_error($ch)) {
            $icon_path = DIRECTORY_SEPARATOR . $img_path . $r_name;
            return $icon_path;
        }else{
            return -4;
        }
    }

    /**
    +--------------------------------------------------
     * 游戏下载类型
     * 下载类型 [1:iOS2:iOS越狱3:本地下载(APK)4:Google Play5:WP6:安卓特别版]
    +--------------------------------------------------
     * @return $downloadTypeArr 下载类型数组
    +--------------------------------------------------
     */
    public function game_download_type(){
        $downloadTypeArr = array();
        $downloadTypeArr[0]['type'] = 0;
        $downloadTypeArr[0]['type_name'] = '请选择下载类型';
        $downloadTypeArr[1]['type'] = 1;
        $downloadTypeArr[1]['type_name'] = 'iOS';
        $downloadTypeArr[2]['type'] = 2;
        $downloadTypeArr[2]['type_name'] = 'iOS越狱';
        $downloadTypeArr[3]['type'] = 3;
        $downloadTypeArr[3]['type_name'] = '本地下载(APK)';
        $downloadTypeArr[4]['type'] = 4;
        $downloadTypeArr[4]['type_name'] = 'Google Play';
        $downloadTypeArr[5]['type'] = 5;
        $downloadTypeArr[5]['type_name'] = 'WP';
        $downloadTypeArr[6]['type'] = 6;
        $downloadTypeArr[6]['type_name'] = '安卓特别版';

        return $downloadTypeArr;
    }

    /**
    +--------------------------------------------------
     * 生成文章id[passage_id]
     * @return $passage_id 文章id
    +--------------------------------------------------
     * ID生成规则
     * 生成ID位数 >=11位
     * 站点来源域名ASCII 组成数字数组 去重 取各ASCII前一位数字
     * 反转字符串 截取字符串前5位数字
     * 如果有 article_id 则补全为6位 passage_id为 ASCII+6位article_id
     * 如果 生成passage_id 已经存在 || article_id为空 则进行自动生成
    +--------------------------------------------------
     */
    public function get_passage_id($site_from,$article_id=0){

        $site_from = !empty($site_from) ? $site_from : 'http://www.appgame.com';
        $strArr = str_split($site_from);
        $string = '';
        for($i=0; $i<sizeof($strArr) ;$i++){
            $string .= ord($strArr[$i]) . ',';
        }
        $arr = explode(',', $string);
        $arr = array_unique($arr);
        $pre_id = '';
        for($j=0; $j<sizeof($arr); $j++){
            if(!empty($arr[$j] )){
                $pre_id .= substr($arr[$j], 0 ,1);
            }
        }
        $pre_id = strrev($pre_id);
        $pre_id = substr($pre_id, 0, 5);

        if( $article_id ){
            $article_id = str_pad($article_id, 6, "0", STR_PAD_LEFT);
            $passage_id = (int)($pre_id . $article_id);
            $result = $this->db->get("SELECT passage_id FROM app_new_passage_content WHERE passage_id=$passage_id LIMIT 1");

            //重复则自动生成 passage_id
            if ($result)
                $passage_id = $this->get_uniq_passage_id($pre_id);
        }else{
            $passage_id = $this->get_uniq_passage_id($pre_id);
        }

        return $passage_id;
    }

    /**
    +--------------------------------------------------
     * 文章ID递归生成
     * @return $passage_id 文章id
    +--------------------------------------------------
     * ID生成规则
     * 生成ID位数 11位
    +--------------------------------------------------
     */
    public function get_uniq_passage_id($pre_id=0){

        if(!empty($pre_id)) {
            $random_id = substr(strtotime(date("Y-m-d", time())), 0, 2) . substr(strrev(microtime()), 0, 2) . substr(rand(), 0, 2);
            $passage_id = $pre_id . $random_id;
        }else{
            $passage_id = substr(strtotime(date("Y-m-d", time())), 0, 2) . substr(strrev(microtime()), 0, 2) . substr(mt_rand(), 0, 5) . substr(rand(), 0, 2);
        }

        $result = $this->db->get("SELECT passage_id FROM app_new_passage_content WHERE passage_id=$passage_id LIMIT 1");

        if ($result)
            return self::get_uniq_passage_id(0);
        else
            return $passage_id;
    }

    /**
    +--------------------------------------------------
     * 游戏ID递归生成
     * @return $game_id 游戏id
    +--------------------------------------------------
     * ID生成规则
     * 生成ID位数 11位
    +--------------------------------------------------
     */
    public function get_game_id(){
        $GameInfoDetailTable = $this->OperateTable['GameInfoDetailTable'];

        $game_id = substr(strtotime(date("Y-m-d", time())), 0, 2) . substr(strrev(microtime()), 0, 2) . substr(mt_rand(), 0, 5) . substr(rand(), 0, 2);

        $sql = "SELECT game_id FROM $GameInfoDetailTable WHERE game_id=" . $game_id . " LIMIT 1";
        if ( $this->db->get($sql) )
            return $this->get_game_id();
        else
            return $game_id;
    }

    /**
    +--------------------------------------------------
     * 视频ID递归生成
     * @return $video_id 视频ID
    +--------------------------------------------------
     * ID生成规则
     * 生成ID位数 11位
    +--------------------------------------------------
     */
    public function get_video_id(){
        $GameVideoTable = $this->OperateTable['GameVideoTable'];

        $video_id = substr(strtotime(date("Y-m-d", time())), 0, 2) . substr(strrev(microtime()), 0, 2) . substr(mt_rand(), 0, 5) . substr(rand(), 0, 2);

        $sql = "SELECT video_id FROM $GameVideoTable WHERE video_id=" . $video_id . " LIMIT 1";
        if ( $this->db->get($sql) )
            return $this->get_video_id();
        else
            return $video_id;
    }

    /**
    +--------------------------------------------------
     * 七牛视频ID递归生成
     * @return $video_id 视频ID
    +--------------------------------------------------
     * ID生成规则
     * 生成ID位数 11位
    +--------------------------------------------------
     */
    public function get_qiniu_video_id(){
        $video_id = substr(strtotime(date("Y-m-d", time())), 0, 2) . substr(strrev(microtime()), 0, 2) . substr(mt_rand(), 0, 5) . substr(rand(), 0, 2);

        $sql = "SELECT video_id FROM app_game_video_qiniu WHERE video_id=" . $video_id . " LIMIT 1";
        if ( $this->db->get($sql) )
            return $this->get_qiniu_video_id();
        else
            return $video_id;
    }

    /**
     * 文本编辑器图片上传图片上传
     */
    public function img_upload(){
        $filename = $_FILES['filedata']['tmp_name'];
        if( empty($_FILES['filedata']['name']) )
            ajaxReturn('您未提交任何内容，请重新提交!',300);
        //判断文件是否是通过 HTTP POST 上传的
        $is_post = is_uploaded_file($filename);
        if( !$is_post )
            ajaxReturn('错误的文件上传方式，请重新上传文件!',300);
        //判断文件是否上传成功
        $err_info = $_FILES['filedata']['error'];
        if( $err_info != 0 )
            return -4;//ajaxReturn('文件上传异常，请重新提交!',300);

        $allow_images = array('jpg','jpeg','gif','bmp','png');
        $iconurl_path = $this->file_upload($_FILES,'filedata',$allow_images);

        $back = '{"err":"","msg":"'.$iconurl_path.'"}';
        echo $back;
    }

    /**
     * 储存数据缓存
     * @param string $name 储存缓存的名字
     * @param string or array  $data 缓存数据内容
     * @param string $time  缓存有效时间，时间：秒，不填为永久
     * +--------------------------------------------------
     * @return  bool
     **/
    public function makeCache($name, $data, $time=0){
        $filename=CACHE_PATH.$name.'.php';
        $content='<?php if(CACHE_APPGAME!=\'yes\'){die("Forbidden Access");} ';
        if($time>0){
            $content.='$cache_time='.(time()+$time).'; ';
        }
        if(is_array($data)){
            $content.='$cache_type=\'array\'; ';
            $data=serialize($data);
        }
        $content.='$cache_data=\''.$data.'\'; ';
        $content.='?>';

        if(!$fp = fopen($filename, "wa")){
            return 'open fail';
        }
        if(!fwrite($fp, $content)){
            fclose($fp);
            return 'write fail';
        }
        fclose($fp);
        return true;
    }

    /**
     * 读取数据缓存
     * @param string $name 读取缓存的名字
     * +----------------------------------
     * @return  array or string
     **/
    public function getCache($name){
        $filename=CACHE_PATH.$name.'.php';
        define('CACHE_APPGAME','yes');
        if(!file_exists($filename)){
            return false;
        }
        include($filename);
        if($cache_time>0 && $cache_time<time()){
            return false;
        }
        if(!$cache_data){
            return false;
        }
        if($cache_type=="array"){
            $cache_data=unserialize($cache_data);
        }
        return $cache_data;
    }

    /**
    +--------------------------------------------------
     * 获取栏目菜单
    +--------------------------------------------------
     * @param $mid 模块id[1:任玩堂游戏库管理,2:数据管理,3:系统管理]
     * @param $auth 1:获取所有菜单[包括隐藏菜单]
     * @param $state 1:缓存菜单
    +--------------------------------------------------
     * @return array $menuData菜单三维数组
    +--------------------------------------------------
     */
    public function getMenuList($mid=1,$auth=0,$state=0){
        $AdminMenuTable = $this->OperateTable['AdminMenuTable'];

        if($auth){
            $menuAllData = $this->getCache('menuAllData');
            if(!$menuAllData || $state==1){
                unset($menuAllData);//清除历史缓存菜单
                $sql = "SELECT id,name,order_id,type,m,c,a,state,mid,pid,cid FROM $AdminMenuTable order by pid,cid,order_id";
                $data = $this->db->find($sql);

                foreach($data as $v){
                    switch($v['type']){
                        case 1://项目
                            $menuAllData[$v['mid']][$v['id']]['id']=$v['id'];
                            $menuAllData[$v['mid']][$v['id']]['name']=$v['name'];
                            $menuAllData[$v['mid']][$v['id']]['m']=$v['m'];
                            $menuAllData[$v['mid']][$v['id']]['order_id']=$v['order_id'];
                            $menuAllData[$v['mid']][$v['id']]['status']=$v['state'];
                            break;
                        case 2://栏目
                            $menuAllData[$v['mid']][$v['pid']]['list'][$v['id']]['id']=$v['id'];
                            $menuAllData[$v['mid']][$v['pid']]['list'][$v['id']]['name']=$v['name'];
                            $menuAllData[$v['mid']][$v['pid']]['list'][$v['id']]['m']=$v['m'];
                            $menuAllData[$v['mid']][$v['pid']]['list'][$v['id']]['c']=$v['c'];
                            $menuAllData[$v['mid']][$v['pid']]['list'][$v['id']]['order_id']=$v['order_id'];
                            $menuAllData[$v['mid']][$v['pid']]['list'][$v['id']]['status']=$v['state'];
                            break;
                        case 3://菜单
                            $menuAllData[$v['mid']][$v['pid']]['list'][$v['cid']]['list'][$v['id']]['id']=$v['id'];
                            $menuAllData[$v['mid']][$v['pid']]['list'][$v['cid']]['list'][$v['id']]['name']=$v['name'];
                            $menuAllData[$v['mid']][$v['pid']]['list'][$v['cid']]['list'][$v['id']]['m']=$v['m'];
                            $menuAllData[$v['mid']][$v['pid']]['list'][$v['cid']]['list'][$v['id']]['c']=$v['c'];
                            $menuAllData[$v['mid']][$v['pid']]['list'][$v['cid']]['list'][$v['id']]['a']=$v['a'];
                            $menuAllData[$v['mid']][$v['pid']]['list'][$v['cid']]['list'][$v['id']]['order_id']=$v['order_id'];
                            $menuAllData[$v['mid']][$v['pid']]['list'][$v['cid']]['list'][$v['id']]['status']=$v['state'];
                            break;
                    }
                }
                $this->makeCache('menuAllData',$menuAllData);
            }
            return $menuAllData[$mid];
        }else{
            $menuData = $this->getCache('menuData');
            if(!$menuData || $state==1){
                unset($menuData);//清除历史缓存菜单
                $sql = "SELECT id,name,order_id,type,m,c,a,state,mid,pid,cid FROM $AdminMenuTable WHERE state=1 order by pid,cid,order_id";
                $data = $this->db->find($sql);

                foreach($data as $v){
                    switch($v['type']){
                        case 1://项目
                            $menuData[$v['mid']][$v['id']]['id']=$v['id'];
                            $menuData[$v['mid']][$v['id']]['name']=$v['name'];
                            $menuData[$v['mid']][$v['id']]['m']=$v['m'];
                            $menuData[$v['mid']][$v['id']]['order_id']=$v['order_id'];
                            $menuData[$v['mid']][$v['id']]['status']=$v['state'];
                            break;
                        case 2://栏目
                            $menuData[$v['mid']][$v['pid']]['list'][$v['id']]['id']=$v['id'];
                            $menuData[$v['mid']][$v['pid']]['list'][$v['id']]['name']=$v['name'];
                            $menuData[$v['mid']][$v['pid']]['list'][$v['id']]['m']=$v['m'];
                            $menuData[$v['mid']][$v['pid']]['list'][$v['id']]['c']=$v['c'];
                            $menuData[$v['mid']][$v['pid']]['list'][$v['id']]['order_id']=$v['order_id'];
                            $menuData[$v['mid']][$v['pid']]['list'][$v['id']]['status']=$v['state'];
                            break;
                        case 3://菜单
                            $menuData[$v['mid']][$v['pid']]['list'][$v['cid']]['list'][$v['id']]['id']=$v['id'];
                            $menuData[$v['mid']][$v['pid']]['list'][$v['cid']]['list'][$v['id']]['name']=$v['name'];
                            $menuData[$v['mid']][$v['pid']]['list'][$v['cid']]['list'][$v['id']]['m']=$v['m'];
                            $menuData[$v['mid']][$v['pid']]['list'][$v['cid']]['list'][$v['id']]['c']=$v['c'];
                            $menuData[$v['mid']][$v['pid']]['list'][$v['cid']]['list'][$v['id']]['a']=$v['a'];
                            $menuData[$v['mid']][$v['pid']]['list'][$v['cid']]['list'][$v['id']]['order_id']=$v['order_id'];
                            $menuData[$v['mid']][$v['pid']]['list'][$v['cid']]['list'][$v['id']]['status']=$v['state'];
                            break;
                    }
                }
                $this->makeCache('menuData',$menuData);
            }
            return $menuData[$mid];
        }
    }

    /**
    +--------------------------------------------------
     * 获取公用类型列表
    +--------------------------------------------------
     * @param $type 0:获取所有类型1:类型2:风格3:题材4:年龄段5:网络6:画面7:国别
     * @param $type -1:k-v 所有
     * @param $state 1:缓存公用类型列表
    +--------------------------------------------------
     * @return array $commonTypeArr 公用类型列表数组
    +--------------------------------------------------
     */
    public function getCommonTypeList($type=0,$state=0){
        $BaseCommonSettingTable = $this->OperateTable['BaseCommonSettingTable'];

        $commonTypeList = json_decode($this->redis->get('gamedb_common_msg_cache'),true);
        if( empty($commonTypeList) )
            $commonTypeList = $this->getCache('commonTypeList');

        if(empty($commonTypeList) || $state==1) {
            unset($commonTypeList);//清除历史缓存

            $sql = "SELECT `id`, `name`, `type`, `status` FROM $BaseCommonSettingTable WHERE status=1";
            $commonData = $this->db->find($sql);
            $commonTypeList = array();
            foreach($commonData as $key=>$value){
                $commonTypeList[$value['type']][$value['id']]['id'] = $value['id'];
                $commonTypeList[$value['type']][$value['id']]['name'] = $value['name'];
                $commonTypeList[-1][$value['type']][$value['id']] = $value['name'];
            }

            //写文件
            $this->makeCache('commonTypeList',$commonTypeList);

            //写redis
            $this->redis->set('gamedb_common_msg_cache', json_encode($commonTypeList));
        }

        if(!empty($type))
            return $commonTypeList[$type];
        else
            return $commonTypeList;
    }

    /**
    +--------------------------------------------------
     * 公用信息类型设置
    +--------------------------------------------------
     * @return array $commonTypeArr 公用类型数组
    +--------------------------------------------------
     */
    public function getCommonType(){
        $commonTypeArr = array();
        $commonTypeArr[0]['type'] = 0;
        $commonTypeArr[0]['type_name'] = '请选择类型';
        $commonTypeArr[1]['type'] = 1;
        $commonTypeArr[1]['type_name'] = '类型';
        $commonTypeArr[2]['type'] = 2;
        $commonTypeArr[2]['type_name'] = '风格';
        $commonTypeArr[3]['type'] = 3;
        $commonTypeArr[3]['type_name'] = '题材';
        $commonTypeArr[4]['type'] = 4;
        $commonTypeArr[4]['type_name'] = '年龄段';
        $commonTypeArr[5]['type'] = 5;
        $commonTypeArr[5]['type_name'] = '网络';
        $commonTypeArr[6]['type'] = 6;
        $commonTypeArr[6]['type_name'] = '画面';
        $commonTypeArr[7]['type'] = 7;
        $commonTypeArr[7]['type_name'] = '国别';
        $commonTypeArr[8]['type'] = 8;
        $commonTypeArr[8]['type_name'] = '设备';
        $commonTypeArr[9]['type'] = 9;
        $commonTypeArr[9]['type_name'] = '渠道';
        return $commonTypeArr;
    }

    /**
    +--------------------------------------------------
     * 获取游戏列表
    +--------------------------------------------------
     * @param $type 1:游戏id+游戏名称 二维数组 2:游戏id对应游戏名称一维数组
     * @param $state 1:获取游戏列表
    +--------------------------------------------------
     * @return array $gameDataList 游戏列表数组
    +--------------------------------------------------
     */
    public function getGameDataList($type=1,$state=0){
        $GameInfoDetailTable = $this->OperateTable['GameInfoDetailTable'];
        $gameDataList = $this->getCache('gameDataList');

        if(!$gameDataList || $state==1) {
            unset($gameDataList);//清除历史缓存

            $game_sql = "SELECT `game_id`, `game_name_cn` FROM $GameInfoDetailTable WHERE 1";
            $gameData = $this->db->find($game_sql);
            foreach($gameData as $key=>$value){
                $gameDataList[1][$key+1]['game_id'] = $value['game_id'];
                $gameDataList[1][$key+1]['game_name_cn'] = $value['game_id'] . ':' . $value['game_name_cn'];
                $gameDataList[2][$value['game_id']] = $value['game_name_cn'];
            }

            $this->makeCache('gameDataList',$gameDataList);
        }

        return $gameDataList[$type];
    }

    /**
    +--------------------------------------------------
     * 游戏列表 redis cache
    +--------------------------------------------------
     * @param $state 1:更新cache
    +--------------------------------------------------
     * @return array $gameDataList 游戏列表数组
    +--------------------------------------------------
     */
    public function RedisGameDataList($state=0){
        $GameInfoDetailTable = $this->OperateTable['GameInfoDetailTable'];
        $game_num = $this->redis->lSize('app.appgame.com:game_list');

        if($game_num<0 || $state==1) {

            $this->redis->del('app.appgame.com:game_list');//LIST允许重复元素

            $game_sql = "SELECT `game_id`, `game_name_cn`, `game_name_en`, `icon`, `add_time`, `modified_time`, `intro` FROM $GameInfoDetailTable WHERE status=1 ORDER BY add_time ASC";
            $gameData = $this->db->find($game_sql);

            if( empty($gameData) )
                return false;

            foreach($gameData as $key=>$value){
                $info = array();

                $info['game_id'] = $value['game_id'];
                $info['game_name_cn'] = $value['game_name_cn'];
                $info['game_name_en'] = $value['game_name_en'];
                $info['icon'] = !empty($value['icon']) ? IMAGES_PATH . $value['icon'] : '';
                $info['create_time'] = (!empty($value['add_time']) && $value['add_time']!='0000-00-00 00:00:00') ? strtotime($value['add_time'])+0 : 0;
                $info['update_time'] = (!empty($value['modified_time']) && $value['modified_time']!='0000-00-00 00:00:00') ? strtotime($value['modified_time'])+0 : 0;
                $info['intro'] = !empty($value['intro']) ? $value['intro'] : '';

                $this->redis->lPush('app.appgame.com:game_list', json_encode($info));

                unset($info);
            }
        }

        return true;
    }

    /**
    +--------------------------------------------------
     * 获取平台列表
    +--------------------------------------------------
     * @param $type 列表类型[1:一维数组2:二维数组]
     * @param $state 1:缓存平台列表
    +--------------------------------------------------
     * @return array $PlatformList 平台列表
    +--------------------------------------------------
     */
    public function getPlatformDataList($type=1,$state=0){
        $PlatformListTable = $this->OperateTable['PlatformListTable'];
        $PlatformList = $this->getCache('platformList');

        if(!$PlatformList || $state==1){
            unset($PlatformList);

            $sql = "SELECT platform_id,platform_name FROM $PlatformListTable";
            $data = $this->db->find($sql);
            $PlatformList = array();
            foreach($data as $k=>$v){
                $PlatformList[1][$v['platform_id']] = $v['platform_name'];
                $PlatformList[2][$v['platform_id']]['platform_id'] = $v['platform_id'];
                $PlatformList[2][$v['platform_id']]['platform_name'] = $v['platform_name'];
            }
            $this->makeCache('platformList',$PlatformList);
        }

        return $PlatformList[$type];
    }

    /**
    +--------------------------------------------------
     * 获取合集列表
    +--------------------------------------------------
     * @param $type 列表类型[1:一维数组2:二维数组]
     * @param $state 1:缓存合集列表
    +--------------------------------------------------
     * @return array $CompilationList 合集列表
    +--------------------------------------------------
     */
    public function getCompilationDataList($type=1,$state=0){
        $CompilationTypeTable = $this->OperateTable['CompilationTypeTable'];
        $CompilationList = $this->getCache('compilationList');

        if(!$CompilationList || $state==1){
            unset($CompilationList);

            $sql = "SELECT `id`, `com_name` FROM $CompilationTypeTable WHERE 1";
            $data = $this->db->find($sql);
            $CompilationList = array();
            foreach($data as $k=>$v){
                $CompilationList[1][$v['id']] = $v['com_name'];
                $CompilationList[2][$v['id']]['id'] = $v['id'];
                $CompilationList[2][$v['id']]['com_name'] = $v['com_name'];
            }
            $this->makeCache('compilationList',$CompilationList);
        }

        return $CompilationList[$type];
    }

    /**
    +--------------------------------------------------
     * 获取游戏类型
    +--------------------------------------------------
     * @param $type 列表类型[1:一维数组2:二维数组]
     * @param $state 1:缓存游戏类型列表
    +--------------------------------------------------
     * @return array $AdTypeList 游戏类型列表
    +--------------------------------------------------
     */
    public function getGameTypeList($type,$state=0){
        $GameTypeList = $this->getCache('GameTypeList');
        if(!$GameTypeList || $state==1){
            unset($GameTypeList);//清除历史缓存广告类型列表
            $sql = "SELECT `type_id`, `type_name` FROM forgame_game_type WHERE 1";
            $data = $this->db->find($sql);
            $GameTypeList = array();
            foreach($data as $k=>$v){
                $GameTypeList[1][$v['type_id']] = $v['type_name'];
                $GameTypeList[2][$v['type_id']]['type_id'] = $v['type_id'];
                $GameTypeList[2][$v['type_id']]['type_name'] = $v['type_name'];
            }
            $this->makeCache('GameTypeList',$GameTypeList);
        }
        return $GameTypeList[$type];
    }

    /**
    +--------------------------------------------------
     * 记录系统操作日志
    +--------------------------------------------------
     * @param string $memo 日志操作内容
     * @param int $app_id 所属应用 [1:{默认},2:任玩堂游戏库]
    +--------------------------------------------------
     * @return  null
    +--------------------------------------------------
     */
    public function setLog($memo,$app_id=2){
        //日志数据库
        $this->db1 = new \common\libs\classes\Mysql(1);

        $getData = checkData($_GET);
        $data = $this->getMenuList($app_id,1);

        //只记录菜单选项
        $menuLogList = array();
        foreach ($data as $key => $value) {
            $menuLogList[$value['m']] = $value['name'];
            foreach ($value['list'] as $ke => $va) {
                $menuLogList[$va['m'].'.'.$va['c']] = $value['name'] . '>>' . $va['name'];
                foreach ($va['list'] as $k => $v) {
                    $menuLogList[$v['m'].'.'.$v['c'].'.'.$v['a']] = $value['name'] . '>>' . $va['name'] . '>>' . $v['name'];
                }
            }
        }

        if( $menuLogList[$getData['m'].'.'.$getData['c'].'.'.$getData['a']] )
            $menu = $menuLogList[$getData['m'].'.'.$getData['c'].'.'.$getData['a']];
        else
            $menu = $getData['m'].'.'.$getData['c'].'.'.$getData['a'];

        $ip = $_SERVER['REMOTE_ADDR'];

        if(!$_SESSION['user_id']['user_name'] && !in_array($getData['action'],array('api','ajax'))){
            $uName = urldecode($_COOKIE['uName']);
            $memo .= ',SESSION已过期';
        }else{
            $uName = $_SESSION['user_id']['user_name'];
        }

        $ctime=time();
        $tb="appgame_logs_".date('Ym',$ctime);

        $sql="insert into $tb(uName,ip,logtime,app_id,m,c,a,menu,memo) values('".$uName."','".$ip."','".time()."',$app_id,'".$getData['m']."','".$getData['c']."','".$getData['a']."','".$menu."','".$memo."');";
        if(!$this->db1->query($sql)){
            $csSql='CREATE TABLE IF NOT EXISTS `'.$tb.'` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `uName` varchar(20) NOT NULL,
					  `ip` varchar(15) NOT NULL,
					  `logtime` int(11) NOT NULL,
					  `app_id` int(11) NOT NULL,
					  `m` varchar(20) NOT NULL,
					  `c` varchar(20) NOT NULL,
					  `a` varchar(20) NOT NULL,
					  `menu` varchar(100) NOT NULL,
					  `memo` text NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `uName` (`uName`),
					  KEY `ip` (`ip`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';
            $this->db1->query($csSql);
            $this->db1->query($sql);
        }
    }

    /**
    +----------------------------------------------------------
     * 以POST 方式执行请求
    +----------------------------------------------------------
     * @param string $post_url 提交接口地址
     * @param string $post_arr 参数列表
     * 例：$post_url 为http://www.91wan.com/api/open.php
     *     $post_arr 为"method=$method&user_name=$user_name";
    +----------------------------------------------------------
     * @return result 请求得到结果
    +----------------------------------------------------------
     */
    public function post_curl ($post_url,$post_arr) {
        $postdata = $this->get_urlencoded_string($post_arr);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_REFERER, "www.appgame.com");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $result = curl_exec($ch);
        curl_close($ch);
        unset($ch);
        return $result;
    }

    public function get_curl ($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $result = curl_exec($ch);
        curl_close($ch);
        unset($ch);
        return $result;
    }

    /**
     * @brief 对字符串进行URL编码，遵循rfc1738 urlencode
     * @param $params
     * @return URL编码后的字符串
     */
    public function get_urlencoded_string($params)
    {
        $normalized = array();
        foreach($params as $key => $val)
        {
            $normalized[] = $key."=".rawurlencode($val);
        }
        return implode("&", $normalized);
    }

    /**
    +--------------------------------------------------
     * 获取栏目ID
    +--------------------------------------------------
     * @param $term_id 顶级栏目ID
    +--------------------------------------------------
     * @return $category 栏目字符
    +--------------------------------------------------
     */
    public function get_term_id($term_id){
        $sql = "SELECT id FROM app_guides_category WHERE pid=" . $term_id;
        $categoryData = $this->db->find($sql);
        $category = '(';
        if( !empty($categoryData) ) {
            foreach ($categoryData as $k => $v) {
                $category .= $v['id'] . ',';
            }
            $category = substr($category, 0, -1);
            $sql = "SELECT id FROM app_guides_category WHERE pid IN $category" . ")";
            $categoryData = $this->db->find($sql);
            $category .= ',';
            foreach ($categoryData as $k => $v) {
                $category .= $v['id'] . ',';
            }
        }
        $category .= $term_id . ')';
        return $category;
    }

    /**
    +--------------------------------------------------
     * 获取顶级栏目ID
    +--------------------------------------------------
    +--------------------------------------------------
     */
    public function get_top_term($state=0){
        $TopTermData = $this->getCache('TopTermData');

        if(!$TopTermData || $state==1){
            $sql = "SELECT `id` AS term_id, `title` AS term_name, `pid`, `orderid` FROM app_guides_category WHERE pid=1 AND status=1";
            $TopTermData = $this->db->find($sql);
            $TopTermData[-1]['term_id'] = -1;
            $TopTermData[-1]['term_name'] = '-所有类型-';
            sort($TopTermData);
            $this->makeCache('TopTermData',$TopTermData);
        }
        return $TopTermData;
    }


    /**
    +--------------------------------------------------
     * 获取栏目ID
    +--------------------------------------------------
     * @param $term_id 顶级栏目ID
    +--------------------------------------------------
     * @return $category 栏目字符
    +--------------------------------------------------
     */
    public function get_all_term_id($term_id){
        $sql = "SELECT id FROM app_guides_category WHERE pid=" . $term_id;
        $categoryData = $this->db->find($sql);
        $category = '';
        if( !empty($categoryData) ) {
            foreach ($categoryData as $k => $v) {
                $category .= $v['id'] . ',';
            }
            $category = substr($category, 0, -1);
            $sql = "SELECT id FROM app_guides_category WHERE pid IN (" . $category . ")";
            $categoryData = $this->db->find($sql);
            $category .= ',';
            foreach ($categoryData as $k => $v) {
                $category .= $v['id'] . ',';
            }
        }
        $category .= $term_id;
        return $category;
    }

    /**
    +--------------------------------------------------
     * 判断日期格式是否正确
    +--------------------------------------------------
     * 判断格式 yyyy-mm-dd | yyyy-mm-dd hh:ii:ss
     * @param $tdate 要判断日期
     * @param $dateformat 要判断的日期格式 "Y-m-d"或"Y-m-d H:i:s"
    +--------------------------------------------------
     */
    public function is_date($tdate,$dateformat="Y-m-d"){
        $tdate = trim($tdate);
        //不能转换为时间戳
        if( !is_numeric(strtotime($tdate)) ) return false;
        //判断日期是否存在 && 年月日的格式为 Y-m-d
        $tdate_date = explode(" ", $tdate);
        $tdate_time = explode("-", $tdate_date[0]);
        if(isset($tdate_time[0]))
            $year = $tdate_time[0];
        else
            return false;
        if(isset($tdate_time[1]))
            $month = $tdate_time[1];
        else
            return false;
        if(isset($tdate_time[2]))
            $day = $tdate_time[2];
        else
            return false;
        if( !checkdate($month, $day, $year) ) return false;
        //判断日期是否为指定格式
        $tmpdate = date($dateformat,strtotime($tdate));
        if( $tmpdate==$tdate )
            return true;
        else
            return false;
    }

    /**
    +--------------------------------------------------
     * 获取栏目
    +--------------------------------------------------
     * @param $type 获取类型 1:完整列表 2:ID映射名称 3:名称映射ID
     * @param $state 1:缓存栏目列表
    +--------------------------------------------------
     * @return
    +--------------------------------------------------
     */
    public function term_id($type=1,$state){
        $GuidesCategoryTable = $this->OperateTable['GuidesCategoryTable'];

        $termDataList = $this->getCache('termDataList');
        if(!$termDataList || $state==1){
            unset($termDataList);//清除历史缓存
            $sql = "SELECT `id` AS term_id, `title` AS term_name, `pid`, `orderid` FROM $GuidesCategoryTable WHERE status=1";
            $data = $this->db->find($sql);

            foreach($data as $k=>$v){
                $termDataList[1][$v['term_id']]['term_id'] = $v['term_id'];
                $termDataList[1][$v['term_id']]['term_name'] = $v['term_name'];
                $termDataList[2][$v['term_id']] = $v['term_name'];
                $termDataList[3][$v['term_name']] = $v['term_id'];
            }
            unset($data);
            $this->makeCache('termDataList',$termDataList);

            //缓存攻略栏目到Redis
            $sql = "SELECT id FROM $GuidesCategoryTable WHERE pid=5";
            $categoryData = $this->db->find($sql);
            $category = '(';
            foreach($categoryData as $k=>$v){
                $category .= $v['id'] . ',';
            }
            $category = substr($category, 0, -1);
            $sql = "SELECT id FROM $GuidesCategoryTable WHERE pid IN $category" . ")";
            $categoryData = $this->db->find($sql);
            $category .= ',';
            foreach($categoryData as $k=>$v){
                $category .= $v['id'] . ',';
            }
            $category .= '5)';
            $this->redis->set('strategy_category', json_encode($category));
        }

        return $termDataList[$type];
    }

    /**
    +--------------------------------------------------
     * 获取游戏指定栏目文章
    +--------------------------------------------------
     * @param $game_id 游戏ID
     * @param $term_id 顶级栏目
    +--------------------------------------------------
     * @return
    +--------------------------------------------------
     */
    public function get_game_category_passage($game_id,$term_id){
        if( empty($game_id) )
            return false;

        $GamepassageContentTable = $this->OperateTable['GamepassageContentTable'];

        $where = "pc.game_id=" . $game_id;

        //资讯新闻
        $category = $this->get_all_term_id($term_id);//新闻资讯及其子栏目

        $where .= " AND gc.category_id in (" . $category . ")";

        $sql = "SELECT DISTINCT(gc.passage_id) AS pgid FROM $GamepassageContentTable AS pc LEFT JOIN app_new_game_category AS gc ON pc.passage_id=gc.passage_id WHERE $where";
        $passageData = $this->db->find($sql);

        $passage_ids = '';
        foreach($passageData as $_k=>$_v){
            $passage_ids .= $_v['pgid'] . ',';
        }
        $passage_ids = substr($passage_ids, 0, -1);

        $sql = "SELECT article_url,title,icon,intro FROM $GamepassageContentTable WHERE passage_id in (" . $passage_ids . ") AND icon!=''";
        $data = $this->db->find($sql);

        foreach($data as $_k=>&$_v)
            $_v['icon'] = 'http://app.static.appgame.com' . $_v['icon'];

        if( empty($data) )
            return '';

        return $data;
    }

    public function library($name){
        require MODEL_PATH . $name . '.class.php';
    }

    public function model($model_name){
        $model = '\model\\' . $model_name;
        return new $model();
    }

    /**
     * 读取系统配置文件
     * @param string $name 读取缓存的名字
     * +----------------------------------
     * @return  array or string
     **/
    public function get_system_options_ache($name){
        $filename=CACHE_PATH.'configs/'.$name.'.php';
        define('CACHE_APPGAME','yes');
        if(!file_exists($filename)){
            return false;
        }
        include($filename);
        if($cache_time>0 && $cache_time<time()){
            return false;
        }
        if(!$cache_data){
            return false;
        }
        if($cache_type=="array"){
            $cache_data=unserialize($cache_data);
        }
        return $cache_data;
    }

    /**
    +--------------------------------------------------
     * 获取系统配置
    +--------------------------------------------------
     * @param $type 获取类型 1:
     * @param $state 1:缓存系统配置
    +--------------------------------------------------
     * @return
    +--------------------------------------------------
     */
    public function system_options($type=1,$state){
//        $this->library('OptionsModel');
//        $options = $this->model('OptionsModel');

        //$system_options = $this->get_system_options_ache('system');
        $system_options = '';

        if(!$system_options || $state==1){

            unset($system_options);//清除历史缓存

            //$system_options = $options->data_info();
            $sql = "SELECT option_id,option_name,option_value,autoload FROM app_options WHERE 1 AND autoload='yes'";
            $data = $this->db->find($sql);

            //var_dump($system_options);
            $system_options = array();


//            $systemArr = array(
//                'app_path' => 'http://appgame/',
//            );

            $systemArr = '<?php' . "\n" . '$systemArr=array(' . "\n";

            foreach($data as $_k=>$_v){
                $system_options[$_v['option_name']] = $_v['option_value'];
                $systemArr .= "'" . $_v['option_name'] . "'" . '=>' . "'" . $_v['option_value'] . "'," . "\n";
            }

            $systemArr .= "'" . 'image_path' . "'" . '=>' . "'" . 'http://app.appgame.com/' . "'," . "\n";
            $systemArr .= "'" . 'manage_path' . "'" . '=>' . "'" .'http://app.appgame.com/appgame/' . "'," . "\n";
            $systemArr .= "'" . 'img_path' . "'" . '=>' . "'" . 'http://app.appgame.com' . "'," . "\n";
            $systemArr .= "'" . 'images_path' . "'" . '=>' . "'" . 'http://app.static.appgame.com' . "'," . "\n";
            $systemArr .= "'" . 'app_path' . "'" . '=>' . "'" . 'http://app.appgame.com/' . "'," . "\n";

            $systemArr .= ')' . "\n";
            $systemArr .= '?>';
            unset($data);

            //文件缓存
            $filename=CACHE_PATH.'configs/system.php';
//            $content='<?php if(CACHE_APPGAME!=\'yes\'){die("Forbidden Access");} ';
//
//            if(is_array($system_options)){
//                $content.='$cache_type=\'array\'; ';
//                $data=serialize($systemArr);
//            }
//            $content.='$cache_data=\''.$data.'\'; ';
//            $content.='? >';

            if(!$fp = fopen($filename, "wa")){
                return 'open fail';
            }
            //if(!fwrite($fp, $content)){
            if(!fwrite($fp, $systemArr)){
                fclose($fp);
                return 'write fail';
            }
            fclose($fp);

            //$this->makeCache('system_options',$system_options);

        }

        //return $termDataList[$type];
        return true;
    }


    /**
     * 写入错误日志
     * @param $type 日志类型
     * @param $content 错误内容
     */
    public function write_log($type,$content){
        $file = BASE_PATH . '../crontab_new_game/log' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR;//按类型分目录
        if(!is_dir($file)) {
            if (!@mkdir($file, 0777)){die('写入目录失败!程序中止!');}
        }
        $file .= $type . '_log_'. date("Y-m",time()) .'.txt';

        $handle = fopen($file,"a+");
        flock($handle, LOCK_EX) ;
        fwrite( $handle,$content.',操作时间:'. date("Y-m-d H:i:s",time()) ."\n" );
        flock($handle, LOCK_UN);
        fclose($handle);
    }


    /**
     * 获取游戏分类
     * @$state 1:强制更新
     */
    public function get_v3_game_term($state=0){

        if (!$this->redis->socket || $state==1)//Redis服务异常 || 强制更新
        {
            $sql = "SELECT `id`, `term_name`, `icon`, `pid`, `font_color`, `order_id` FROM `app_v3_game_term` WHERE 1 ORDER BY order_id,id ASC";
            $data = $this->db->find($sql);
            $game_term_data = list_to_tree($data, $pk='id', $pid = 'pid');

            foreach($game_term_data as $_k=>&$_v){
                $_v['icon'] = !empty($_v['icon']) ? IMAGES_PATH . $_v['icon'] : '';
            }
            $this->redis->set('game_term', json_encode($game_term_data));//app.appgame.com后台使用

            foreach($data as $_k=>&$_v){
                $_v['icon'] = !empty($_v['icon']) ? IMAGES_PATH . $_v['icon'] : '';
            }
            $this->redis->set('game_term_data', json_encode($data));//Leancloud同步数据
        }
        else
        {
            $game_term_data = json_decode( $this->redis->get('game_term'), true);
        }

        return $game_term_data;
    }
}