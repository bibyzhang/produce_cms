<?php
/**
++++++++++++++++++++++++++++++++++++
 *  公用方法
++++++++++++++++++++++++++++++++++++
 */
namespace modules\common;

defined('_ACCESS_GRANT') or exit('没有访问权限!');

class common_action extends  \modules\admin\classes\admin{
    public $ftp;

    public function __construct(){
        parent::__construct();

    }

    /**
    +--------------------------------------------------
     * xhEditor 编辑器图片上传
     * 通过ftp上传至图片服务器
    +--------------------------------------------------
     * @return [json] 图片绝对地址
    +--------------------------------------------------
     */
    public function xheditor_image_up(){
        $gData=checkData($_GET);

        require(CACHE_PATH . 'configs/system.php');

        list($width, $height, $type, $attr) = getimagesize($_FILES['filedata']['tmp_name']);

        $img_type = $gData['img_type'];
        if(!$img_type){
            $back = '{"err":"缺少上传的图片类型"}';
            echo $back;exit();
        }

        $img_category = $gData['img_category'];
        if(!$img_category){
            $back = '{"err":"300","msg":"缺少图片分类"}';
            echo $back; exit();
        }

        $upload_id = $gData['upload_id'];
        if(!$upload_id){
            $back = '{"err":"300","msg":"缺少上传图片ID值"}';
            echo $back; exit();
        }
        $allow_images = array('jpg','jpeg','gif','bmp','png');
        $iconurl_path = $this->file_upload($_FILES,'filedata','image',$img_category,$img_type,$upload_id,$height,$width,$allow_images);

        switch($iconurl_path){
            case -1:
                $back = '{"err":"您未提交任何内容，请重新提交!","msg":"您未提交任何内容，请重新提交!"}';
                break;
            case -2:
                $back = '{"err":"错误的文件上传方式，请重新上传文件!","msg":"错误的文件上传方式，请重新上传文件!"}';
                break;
            case -3:
                $back = '{"err":"错误的文件上传错误，请重新上传文件!","msg":"错误的文件上传错误，请重新上传文件!"}';
                break;
            case -4:
                $back = '{"err":"文件格式错误，请选择正确文件!","msg":"文件格式错误，请选择正确文件!"}';
                break;
            case -5:
                $back = '{"err":"文件太大，请重新整理上传文件!","msg":"文件太大，请重新整理上传文件!"}';
                break;
            case -6:
                $back = '{"err":"目录创建失败!","msg":"目录创建失败!"}';
                break;
            case -7:
                $back = '{"err":"文件上传失败，请重新提交!","msg":"文件上传失败，请重新提交!"}';
                break;
            default:
                $back = '';
                break;
        }

        if($back) {
            echo $back;
            exit();
        }

        //$back = '{"err":"","msg":"http://app..com/'.$iconurl_path.'"}';
        $back = '{"err":"","msg":"'.IMAGES_PATH.$iconurl_path.'"}';
        echo $back;
    }

    //图片上传
    public function img_up(){
        $allow_images = array('jpg','jpeg','gif','bmp','png');
        $iconurl_path = $this->file_upload($_FILES,'filedata',$allow_images);

        switch($iconurl_path){
            case -1:
                $back = '{"err":"您未提交任何内容，请重新提交!","msg":"错误的文件上传错误，请重新上传文件!"}';
                break;
            case -2:
                $back = '{"err":"错误的文件上传方式，请重新上传文件!","msg":"错误的文件上传错误，请重新上传文件!"}';
                break;
            case -3:
                $back = '{"err":"错误的文件上传错误，请重新上传文件!","msg":"错误的文件上传错误，请重新上传文件!"}';
                break;
            case -4:
                $back = '{"err":"文件格式错误，请选择正确文件!","msg":"文件格式错误，请选择正确文件!"}';
                break;
            case -5:
                $back = '{"err":"文件太大，请重新整理上传文件!","msg":"文件太大，请重新整理上传文件!"}';
                break;
            case -6:
                $back = '{"err":"目录创建失败!","msg":"目录创建失败!"}';
                break;
            case -7:
                $back = '{"err":"文件上传失败，请重新提交!","msg":"文件上传失败，请重新提交!"}';
                break;
            default:
                $back = '{"err":"","msg":"'.$iconurl_path.'"}';
                break;
        }

        //$back = '{"err":"","msg":"'.APP_PATH.$iconurl_path.'"}';
        //$back = '{"err":"","msg":"'.$iconurl_path.'"}';
        echo $back;
    }

    /**
     * uploadify控件图片上传
     * +--------------------------------------------------
     * @param $img_type 图片类型
     */
    public function img_up2(){
        require(CACHE_PATH . 'configs/system.php');

        $gData=checkData($_GET);
        list($width, $height, $type, $attr) = getimagesize($_FILES['img_upload']['tmp_name']);

        $img_type = $gData['img_type'];
        if(!$img_type){
            $back = '{"err":"300","msg":"缺少要上传的图片类型"}';
            echo $back; exit();
        }

        $allow_images = array('jpg','jpeg','gif','bmp','png');
        $iconurl_path = $this->file_upload($_FILES,'img_upload',$allow_images);

        switch($iconurl_path){
            case -1:
                $back = '{"err":"300","msg":"您未提交任何内容，请重新提交!"}';
                break;
            case -2:
                $back = '{"err":"300","msg":"错误的文件上传方式，请重新上传文件!"}';
                break;
            case -3:
                $back = '{"err":"300","msg":"错误的文件上传错误，请重新上传文件!"}';
                break;
            case -4:
                $back = '{"err":"300","msg":"文件格式错误，请选择正确文件!"}';
                break;
            case -5:
                $back = '{"err":"300","msg":"文件太大，请重新整理上传文件!"}';
                break;
            case -6:
                $back = '{"err":"300","msg":"目录创建失败!"}';
                break;
            case -7:
                $back = '{"err":"300","msg":"文件上传失败，请重新提交!"}';
                break;
            default:
                $arr = array();
                $arr['img_url'] = $iconurl_path;
                $back = json_encode($arr);
                break;
        }

        echo $back;
    }

    /**
    +--------------------------------------------------
     * 游戏截图上传
    +--------------------------------------------------
     * @return [json] 图片绝对地址&&图片宽度&&图片高度
    +--------------------------------------------------
     */
    public function multi_img(){
        require(CACHE_PATH . 'configs/system.php');
        $gData=checkData($_GET);

        list($width, $height, $type, $attr) = getimagesize($_FILES['game_pic']['tmp_name']);

        $img_type = $gData['img_type'];
        if(!$img_type){
            $back = '{"err":"300","msg":"缺少要上传的图片类型"}';
            echo $back; exit();
        }
        $game_type = 'mobile_game';
        if(!$game_type){
            $back = '{"err":"300","msg":"缺少要上传的游戏类型"}';
            echo $back; exit();
        }
        /*
         $gid = !empty($gData['gid']) ? $gData['gid'] : 0;
         if(!$gid){
         $back = '{"err":"300","msg":"非法操作"}';
         echo $back; exit();
         }*/

        //验证图片数量[前端删除操作未修改数据库,只作最终添加数量验证]
        /*
         $sql = "SELECT count(img_url) AS ct FROM box_game_img_list WHERE game_id=".$gid;
         $result = $this->db->get($sql);
         //var_dump($result['ct']); exit;
         if($result['ct']>=5){
         $back = '{"err":"300","msg":"最多只能上传5张图片"}';
         $arr = array();
         $arr['img_url'] = '';
         $back = json_encode($arr);
         echo $back; exit();
    
         }
         */

        $allow_images = array('jpg','jpeg','gif','bmp','png');
        $iconurl_path = $this->file_upload($_FILES,'game_pic',$allow_images);

        switch($iconurl_path){
            case -1:
                $back = '{"err":"300","msg":"您未提交任何内容，请重新提交!"}';
                break;
            case -2:
                $back = '{"err":"300","msg":"错误的文件上传方式，请重新上传文件!"}';
                break;
            case -3:
                $back = '{"err":"300","msg":"错误的文件上传错误，请重新上传文件!"}';
                break;
            case -4:
                $back = '{"err":"300","msg":"文件格式错误，请选择正确文件!"}';
                break;
            case -5:
                $back = '{"err":"300","msg":"文件太大，请重新整理上传文件!"}';
                break;
            case -6:
                $back = '{"err":"300","msg":"目录创建失败!"}';
                break;
            case -7:
                $back = '{"err":"300","msg":"文件上传失败，请重新提交!"}';
                break;
            default:
                $arr = array();
                $arr['img_url'] = $iconurl_path;
                $arr['width'] = $width;
                $arr['height'] = $height;
                $back = json_encode($arr);
                break;
        }

        echo $back;
    }

    /**
    +--------------------------------------------------
     * uploadify控件图片上传
    +--------------------------------------------------
     * @param img_type:上传图片类型
     * @param img_category:上传图片分类
    +--------------------------------------------------
     * @return [json] 图片绝对地址 图片宽度 图片高度
    +--------------------------------------------------
     */
    public function uploadify_image_up(){
        $gData=checkData($_GET);

        require(CACHE_PATH . 'configs/system.php');

        list($width, $height, $type, $attr) = getimagesize($_FILES['img_upload']['tmp_name']);

        $img_type = $gData['img_type'];
        if(!$img_type){
            $back = '{"err":"300","msg":"缺少上传的图片类型"}';
            echo $back; exit();
        }

        $img_category = $gData['img_category'];
        if(!$img_category){
            $back = '{"err":"300","msg":"缺少图片分类"}';
            echo $back; exit();
        }

        $upload_id = $gData['upload_id'];
        if(!$upload_id){
            $back = '{"err":"300","msg":"缺少上传图片ID值"}';
            echo $back; exit();
        }

        $allow_images = array('jpg','jpeg','gif','bmp','png');//允许图片类型
        $iconurl_path = $this->file_upload($_FILES,'img_upload','image',$img_category,$img_type,$upload_id,$height,$width,$allow_images);

        switch($iconurl_path){
            case -1:
                $back = '{"err":"300","msg":"您未提交任何内容，请重新提交!"}';
                break;
            case -2:
                $back = '{"err":"300","msg":"错误的文件上传方式，请重新上传文件!"}';
                break;
            case -3:
                $back = '{"err":"300","msg":"错误的文件上传错误，请重新上传文件!"}';
                break;
            case -4:
                $back = '{"err":"300","msg":"文件格式错误，请选择正确文件!"}';
                break;
            case -5:
                $back = '{"err":"300","msg":"文件太大，请重新整理上传文件!"}';
                break;
            case -6:
                $back = '{"err":"300","msg":"目录创建失败!"}';
                break;
            case -7:
                $back = '{"err":"300","msg":"文件上传失败，请重新提交!"}';
                break;
            default:
                $arr = array();
                $arr['prepath'] = IMG_PATH;
                $arr['img_url'] = $iconurl_path;
                $arr['width'] = $width;
                $arr['height'] = $height;
                $back = json_encode($arr);
                break;
        }

        echo $back;
    }

    public function get_term(){
        $sql = "SELECT `id`,`pid` FROM app_guides_category WHERE pid=5";//攻略
        $termData = $this->db->find($sql);
        $pids = '(5,';
        foreach($termData as $k=>$v){
            $pids .= $v['id'] . ',';
        }
        $pids = substr($pids, 0, -1);
        $pids .= ')';

        $sql = "SELECT `id`, `title`, `pid`, `orderid` FROM app_guides_category WHERE pid in $pids AND status=1";
        $list = $this->db->find($sql);
        foreach($list as $key=>&$value){
            if($value['pid']==5)
                $value['pid'] = 0;
        }

        $list = list_to_tree($list);
        $this->s->assign('list',$list);
        $this->s->display('common/get_term.html');
    }


    public function get_all_term(){
        $sql = "SELECT `id`, `title`, `pid`, `orderid` FROM app_guides_category WHERE status=1";
        $list = $this->db->find($sql);

        $list = list_to_tree($list);
        $this->s->assign('list',$list);
        $this->s->display('common/get_term.html');
    }

    public function get_category(){
        $sql = "SELECT `id`,`pid` FROM app_guides_category WHERE pid=5";//攻略
        $termData = $this->db->find($sql);
        $pids = '(5,';
        foreach($termData as $k=>$v){
            $pids .= $v['id'] . ',';
        }
        $pids = substr($pids, 0, -1);
        $pids .= ')';

        $sql = "SELECT `id`, `title`, `pid`, `orderid` FROM app_guides_category WHERE pid in $pids AND status=1";
        $list = $this->db->find($sql);
        foreach($list as $key=>&$value){
            if($value['pid']==5)
                $value['pid'] = 0;
        }

        $list = list_to_tree($list);
        $this->s->assign('list',$list);
        $this->s->display('common/get_category.html');
    }

    /**
    +--------------------------------------------------
     * 获取应用宝大视频栏目
    +--------------------------------------------------
     * @return [json]
     * @return code 状态码
     * @return msg 状态消息
    +--------------------------------------------------
     */
    public function get_yingyongbao_video_term(){
        $sql = "SELECT `id`,`pid`,`title`,`cooperate_id` FROM app_guides_category WHERE pid=3 AND agent_id=1 ORDER BY cooperate_id";//攻略
        $list = $this->db->find($sql);

        foreach($list as $key=>&$value){
            if($value['pid']==3)
                $value['pid'] = 0;
            $value['id'] = $value['cooperate_id'];
        }

        $list = list_to_tree($list);
        $this->s->assign('list',$list);
        $this->s->display('common/get_term.html');
    }

    /**
    +--------------------------------------------------
     * 获取腾讯视频组栏目
    +--------------------------------------------------
     * @return [json]
     * @return code 状态码
     * @return msg 状态消息
    +--------------------------------------------------
     */
    public function get_tencent_run_term(){
        $sql = "SELECT `id`,`pid`,`title`,`cooperate_id` FROM app_guides_category WHERE pid=3 AND agent_id=2 ORDER BY cooperate_id";//攻略
        $list = $this->db->find($sql);

        foreach($list as $key=>&$value){
            if($value['pid']==3)
                $value['pid'] = 0;
            $value['id'] = $value['cooperate_id'];
        }

        $list = list_to_tree($list);
        $this->s->assign('list',$list);
        $this->s->display('common/get_tencent_run_term.html');
    }

    /**
    +--------------------------------------------------
     * product_id 唯一限制
    +--------------------------------------------------
     * @param $type:查询类型
     * @param $product_id:查询值
    +--------------------------------------------------
     * @return [json]
     * @return code 状态码
     * @return msg 状态消息
    +--------------------------------------------------
     */
    public function check_product(){
        $pData = checkData($_POST);
        $gData = checkData($_GET);

        $GameInfoDetailTable = 'app_new_game_info_detail';

        $type = $gData['type'];
        if( !$type )
            die(json_encode(array('code'=>-100,'msg'=>'缺少查询类型')));

        $product_id = $pData['product_id'];
        if( !$product_id )
            die(json_encode(array('code'=>-100,'msg'=>'缺少必须查询参数')));

        if($type=='apple_id'){
            $sql = "SELECT id FROM $GameInfoDetailTable WHERE apple_id LIKE '%" . $product_id . "%' LIMIT 1";
        }
        if($type=='product_id'){
            $sql = "SELECT id FROM $GameInfoDetailTable WHERE product_id LIKE '%" . $product_id . "%' LIMIT 1";
        }
        if($type=='bundle_id' || $type=='game_bundle_id[]'){
            $sql = "SELECT id FROM $GameInfoDetailTable WHERE bundle_id LIKE '%" . $product_id . "%' OR game_bundle_id LIKE '%" . $product_id ."%' LIMIT 1";
        }

        if( $this->db->get($sql) ){
            $resultData = array(
                'code' => 200,
                'msg' => 1
            );
        }else{
            $resultData = array(
                'code' => 200,
                'msg' => 0
            );
        }

        echo json_encode($resultData);exit();
    }

    /**
    +--------------------------------------------------
     * 获取游戏列表
     * 推荐位管理快速获取
    +--------------------------------------------------
     */
    public function get_game_data(){
        $pData = checkData($_POST);
        /*$gameData = $this->getGameDataList(2);
        echo json_encode($gameData);*/
        $GameInfoDetailTable = 'app_new_game_info_detail';

        $game_id = $pData['game_id'];
        if(empty($game_id)){
            echo json_encode(array('code'=>-100,'msg'=>'缺少游戏ID'));
            exit();
        }

        $sql = "SELECT game_id,icon,game_name_cn,game_name_en FROM $GameInfoDetailTable WHERE game_id=$game_id LIMIT 1";
        $data = $this->db->get($sql);

        if( !empty($data) ){
            $resultData = array(
                'code' => 200,
                'result' => array(
                    'thumb' => IMAGES_PATH . $data['icon'],
                    'title' => !empty($data['game_name_cn']) ? $data['game_name_cn'] : $data['game_name_en'],
                    'url' => 'http://app..com/game/' . $data['game_id'] . '.html',
                )
            );
        }else{
            $resultData = array(
                'code' => -100,
                'msg' => '没有找到对应游戏'
            );
        }
        echo json_encode($resultData);exit();
    }

    /**
    +--------------------------------------------------
     *
     *
    +--------------------------------------------------
     */
    public function get_tencent_video_info(){
        $pData = checkData($_POST);

        $vid = !empty($pData['tencent_id']) ? $pData['tencent_id'] : '';
        if( empty($vid) ){
            echo json_encode(array(code=>-100,'msg'=>'非法操作!'));exit();
        }

        $apiUrl = "113.108.76.191/cgi-bin/appstage/get_video_info?videoid=" . $vid;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        ob_start();
        curl_exec($ch);
        $result = ob_get_contents() ;
        ob_end_clean();
        curl_close($ch);
        $obj = new \DOMDocument();
        $obj->loadXML($result);
        if( $obj->getElementsByTagName("code")->item(0)->nodeValue==-200 ){
            echo json_encode(array(code=>-100,'msg'=>'无法读取数据,请检查视频ID是否正确!'));exit();
        }

        $info['title'] = $obj->getElementsByTagName("n")->item(0)->nodeValue;
        $info['duration'] = $obj->getElementsByTagName("l")->item(0)->nodeValue;
        $info['p120'] = $obj->getElementsByTagName("p120")->item(0)->nodeValue;//120*90
        $info['p160'] = $obj->getElementsByTagName("p160")->item(0)->nodeValue;//160*90
        $info['png'] = $obj->getElementsByTagName("png")->item(0)->nodeValue;//视频大小封面截图

        unset($result);
        $pnApiUrl = "http://sns.video.qq.com/tvideo/fcgi-bin/batchgetplaymount?id=" . $vid;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pnApiUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        ob_start();
        curl_exec($ch);
        $result = ob_get_contents() ;
        ob_end_clean();
        curl_close($ch);
        $obj->loadXML($result);
        if( $obj->getElementsByTagName("code")->item(0)->nodeValue==-200 ){
            echo json_encode(array(code=>-100,'msg'=>'无法读取数据,请检查视频ID是否正确!'));exit();
        }
        $info['num'] = $obj->getElementsByTagName("num")->item(0)->nodeValue;
        unset($result);

        echo json_encode(array('code'=>200,'data'=>$info));exit();
    }

    /**
    +--------------------------------------------------
     * 获取多游戏从属
    +--------------------------------------------------
     * @return [json]
     * @return code 状态码
     * @return msg 状态消息
    +--------------------------------------------------
     */
    public function get_game_ids(){

        //游戏列表
        $gameArr = $this->getGameDataList(1);
        $gameArr[0]['game_id'] = 0;
        $gameArr[0]['game_name_cn'] = '-请选择游戏-';
        sort($gameArr);
        $this->s->assign('gameArr',$gameArr);

        $this->s->display('common/get_game_ids.html');
    }

    /**
    +--------------------------------------------------
     * 获取多游戏从属
    +--------------------------------------------------
     * @return [json]
     * @return code 状态码
     * @return msg 状态消息
    +--------------------------------------------------
     */
    public function get_tags(){
        $this->s->display('common/get_tags.html');
    }

    public function get_tag_data(){
        $gData = checkData($_GET);

        $key = $gData['key'];
        if(empty($key)) {
            $gameData[0]['tag_id'] = 0;
            $gameData[0]['tag_name'] = '';
        }else{
            $sql = "SELECT tag_id,tag_name FROM app_tag WHERE tag_name LIKE'%" . $key . "%'";
            $data = $this->db->find($sql);

            $gameData = array();
            foreach ($data as $_k => $_v) {
                $gameData[$_k]['tag_id'] = $_v['tag_id'];
                $gameData[$_k]['tag_name'] = $_v['tag_name'];
            }
        }

        $array['result'] = $gameData;

        echo json_encode($array);
        exit();
    }

    public function get_game_list(){
        $gData = checkData($_GET);

        $gameArr = $this->getGameDataList(1);
        //var_dump($gameArr);exit();


        $key = $gData['key'];
        if(empty($key)) {
            $gameData[0]['game_id'] = 0;
            $gameData[0]['game_name'] = '';
        }else{
            $sql = "SELECT game_id,game_name_cn FROM app_new_game_info_detail WHERE game_name_cn LIKE'%" . $key . "%'";
            $data = $this->db->find($sql);

            $gameData = array();
            foreach ($data as $_k => $_v) {
                $gameData[$_k]['game_id'] = $_v['game_id'];
                $gameData[$_k]['game_name'] = $_v['game_name_cn'];
            }
        }

        $array['result'] = $gameData;

        echo json_encode($array);
        exit();
    }
}