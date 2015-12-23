<?php
// +----------------------------------------------------------------------
// | @file Core.php
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
    );



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


}