<?php
/**
 ++++++++++++++++++++++++++++++++++++
 *  -> ajax异步
 ++++++++++++++++++++++++++++++++++++
 */
namespace modules\ajax;

defined('_ACCESS_GRANT') or exit('没有访问权限!');

class ajax extends \modules\admin\classes\admin{
    public function __construct(){
        parent::__construct();
    }

    /** 设置操作数据表 */
    private $OperateTable = array(
        'AdminMenuTable' => 'app_new_admin_menu',//管理菜单
    );

    /** 获取系统菜单 */
    public function get_menu_m(){
        $gData = checkData($_GET);
        $AdminMenuTable = $this->OperateTable['AdminMenuTable'];

        $mid=intval($gData['mid']);
        if(!$mid){
            $list[]=array(0,'--请选择项目--');
        } else {
            $list[]=array(0,'--请选择项目--');

            $sql = "SELECT * FROM $AdminMenuTable WHERE mid=$mid";
            $data = $this->db->find($sql);

            foreach($data as $v){
                switch($v['type']){
                    case 1:
                        $menuData[$v['id']]['id']=$v['id'];
                        $menuData[$v['id']]['name']=$v['name'];
                        $menuData[$v['id']]['m']=$v['m'];
                        $menuData[$v['id']]['order_id']=$v['order_id'];
                        $menuData[$v['id']]['state']=$v['state'];
                        break;
                    case 2:
                        $menuData[$v['pid']]['list'][$v['id']]['id']=$v['id'];
                        $menuData[$v['pid']]['list'][$v['id']]['name']=$v['name'];
                        $menuData[$v['pid']]['list'][$v['id']]['m']=$v['m'];
                        $menuData[$v['pid']]['list'][$v['id']]['c']=$v['c'];
                        $menuData[$v['pid']]['list'][$v['id']]['order_id']=$v['order_id'];
                        $menuData[$v['pid']]['list'][$v['id']]['state']=$v['state'];
                        break;
                    case 3:
                        $menuData[$v['pid']]['list'][$v['cid']]['list'][$v['id']]['id']=$v['id'];
                        $menuData[$v['pid']]['list'][$v['cid']]['list'][$v['id']]['name']=$v['name'];
                        $menuData[$v['pid']]['list'][$v['cid']]['list'][$v['id']]['m']=$v['m'];
                        $menuData[$v['pid']]['list'][$v['cid']]['list'][$v['id']]['c']=$v['c'];
                        $menuData[$v['pid']]['list'][$v['cid']]['list'][$v['id']]['a']=$v['a'];
                        $menuData[$v['pid']]['list'][$v['cid']]['list'][$v['id']]['order_id']=$v['order_id'];
                        $menuData[$v['pid']]['list'][$v['cid']]['list'][$v['id']]['state']=$v['state'];
                        break;
                }
            }

            foreach($menuData as $v)
                $list[]=array($v['id'],$v['name']."(".$v['m'].")");
        }

        echo json_encode($list);exit;
    }

    /* 获取系统菜单 */
    public function get_menu_c(){
        $gData = checkData($_GET);
        $AdminMenuTable = $this->OperateTable['AdminMenuTable'];

        $pid=intval($gData['pid']);
        if(!$pid){
            $list[]=array(0,'--请选择栏目--');
        } else {
            $list[]=array(0,'--请选择栏目--');
            $sql = "SELECT * FROM $AdminMenuTable";
            $data = $this->db->find($sql);

            foreach($data as $v){
                switch($v['type']){
                    case 1:
                        $menuData[$v['id']]['id']=$v['id'];
                        $menuData[$v['id']]['name']=$v['name'];
                        $menuData[$v['id']]['m']=$v['m'];
                        $menuData[$v['id']]['order_id']=$v['order_id'];
                        $menuData[$v['id']]['state']=$v['state'];
                        break;
                    case 2:
                        $menuData[$v['pid']]['list'][$v['id']]['id']=$v['id'];
                        $menuData[$v['pid']]['list'][$v['id']]['name']=$v['name'];
                        $menuData[$v['pid']]['list'][$v['id']]['m']=$v['m'];
                        $menuData[$v['pid']]['list'][$v['id']]['c']=$v['c'];
                        $menuData[$v['pid']]['list'][$v['id']]['order_id']=$v['order_id'];
                        $menuData[$v['pid']]['list'][$v['id']]['state']=$v['state'];
                        break;
                    case 3:
                        $menuData[$v['pid']]['list'][$v['cid']]['list'][$v['id']]['id']=$v['id'];
                        $menuData[$v['pid']]['list'][$v['cid']]['list'][$v['id']]['name']=$v['name'];
                        $menuData[$v['pid']]['list'][$v['cid']]['list'][$v['id']]['m']=$v['m'];
                        $menuData[$v['pid']]['list'][$v['cid']]['list'][$v['id']]['c']=$v['c'];
                        $menuData[$v['pid']]['list'][$v['cid']]['list'][$v['id']]['a']=$v['a'];
                        $menuData[$v['pid']]['list'][$v['cid']]['list'][$v['id']]['order_id']=$v['order_id'];
                        $menuData[$v['pid']]['list'][$v['cid']]['list'][$v['id']]['state']=$v['state'];
                        break;
                }
            }

            foreach($menuData[$pid]['list'] as $v){
                $list[]=array($v['id'],$v['name']."(".$v['c'].")");
            }
        }
        echo json_encode($list);exit;
    }

    /** 获取广告 */
     public function getAdList(){
         $gData = checkData($_GET);
         $platformData = $this->getPlatformList(1);
         $platformData[0] = '所有';
         $id=intval($gData['id']);
         $platform=intval($gData['platform']);
         if(!$id){
             $list[]=array(0,'--所有--');
         }else{
             $list[]=array(0,'--所有--');
             $sql = "SELECT id, platform, ad_name FROM forgame_ad WHERE platform=$platform AND ad_position_id=$id AND status=1";
             $adData = $this->db->find($sql);
             foreach($adData as $v){
                 $list[]=array($v['id'], $platformData[$v['platform']] . ':' . $v['ad_name']);
             }
         }
         echo json_encode($list);exit;
     }


    /**
     +--------------------------------------------------
     * 获取栏目列表
     +--------------------------------------------------
     * @return [json] 栏目列表
     +--------------------------------------------------
     */
    public function get_term_id(){
        $gData = checkData($_GET);
        $pid = $gData['pid']+0;

        if(!$pid){
            $list[]=array(0,'--请选择栏目--');
        }else {
            $list[]=array(0,'--请选择栏目--');
            //$sql = "SELECT `term_id`, `name`, `pid`, `term_order`, `status` FROM `app_new_passage_terms` WHERE pid=$pid";
            $sql = "SELECT `id`, `title`, `pid`, `orderid`, `status` FROM `app_guides_category` WHERE pid=$pid";
            $termData = $this->db->find($sql);
            foreach($termData as $v){
                //$list[]=array($v['term_id'], $v['name']);
                $list[]=array($v['id'], $v['title']);
            }
        }
        echo json_encode($list);exit;
    }

    /** 获取广告位 */
    public function getAdPositionList(){
        $gData = checkData($_GET);
        $platformData = $this->getPlatformList(1);
        $platformData[0] = '所有';
        $id=intval($gData['id']);
        if(!$id){
            $list[]=array(0,'--所有--');
        }else{
            $list[]=array(0,'--所有--');
            $sql = "SELECT id, ad_position_name FROM forgame_ad_position";
            $adPosiData = $this->db->find($sql);
            foreach($adPosiData as $v){
                $list[]=array($v['id'], $platformData[$id] . ':' . $v['ad_position_name']);
            }
        }
        echo json_encode($list);exit;
    }


    //异步获取游戏 需要传平台ID  plat_id
    //选项1 是否只需要游戏选项  onlygame
    public function getPlatGames(){
        $gData = checkData($_REQUEST);
        $plat_id = $gData['plat_id'] = $gData['plat_id']? intval($gData['plat_id']+0) : 0;
        $onlygame = $gData['onlygame'];
        $is_open = $gData['is_open'];//开充值的游戏1
        $open_game = $gData['open_game'];//官网还在运营的游戏1
        $is_vip = $gData['is_vip'];//是否开通了超级用户的游戏
        $is_vip_limit = $gData['is_vip_limit'];//是否需要限制大客户部门游戏
        if($plat_id>0)
            $games = $this->getPlatsGamesServers(2,$plat_id,0,0,$is_open,$open_game,$is_vip,$is_vip_limit);
        else
            $games = array();

        if($onlygame){
            $data[] = array('');
        }else{
            $data[] = array('','全部');
        }
        if(is_array($games)){
            foreach($games as $key=>$val){
                $data[] = array($val['id'],$val['game_byname'].":".$val['name']);
            }
        }
        $_SESSION['getPlatGames']['plat_id'] = $gData['plat_id'];
        echo json_encode($data);
        exit;
    }

    /** 获取二级栏目对应文章 */
    public function getTermPassage(){
        $gData = checkData($_GET);
        $term_id = $gData['term_id']+0;
        //var_dump( $term_id );exit();

        //确定四层菜单
        //获取所有对应栏目ID
        $sql = "SELECT `term_id`, `name`, `pid` FROM `app_new_passage_terms` WHERE pid=". $term_id ." AND status=1";
        $termData = $this->db->find($sql);

        //$termId = array();
        foreach($termData as $k=>$v){
            $termId .= $v['term_id'] . ',';
            $sql = "SELECT `term_id`, `name`, `pid` FROM `app_new_passage_terms` WHERE pid=". $v['term_id'] ." AND status=1";
            $data = $this->db->find($sql);
            foreach($data as $ke=>$va){
                $termId .= $va['term_id'] . ',';
                $sql = "SELECT `term_id`, `name`, `pid` FROM `app_new_passage_terms` WHERE pid=". $va['term_id'] ." AND status=1";
                $data2 = $this->db->find($sql);
                foreach($data2 as $key=>$val){
                    $termId .= $val['term_id'] . ',';
                }
            }

        }

        $termId = '(' . substr($termId, 0, -1) . ')';
        //var_dump($termId);exit();

        //文章列表
        $sql = "SELECT `id`, `title` FROM `app_new_passage_content` WHERE term_id in $termId";
        $passData = $this->db->find($sql);
        //var_dump($passData);exit();

        if(!$term_id){
            $list[]=array(0,'--请选择对应文章1--');
        }else{
            $list[]=array(0,'--请选择对应文章2--');
            foreach($passData as $v){
                $list[]=array($v['id'], $v['id'] . ':' . $v['title']);
            }
        }
        echo json_encode($list);exit;
    }

    //异步获取游戏平台     plat_id
    //异步获取游戏服务器   game_id
    public function getPlatGameServers(){
        $gData = checkData($_REQUEST);
        $game_id= $gData['game_id']? $gData['game_id'] : '0';
        $plat_id = intval($gData['plat_id'])? $gData['plat_id'] : '1';
        if($game_id)
            $servers = $this->getPlatsGamesServers(3,$plat_id,$game_id,0,$gData['is_open']);
        $onlygame = $gData['onlygame'];
        if($onlygame){
            $data[] = array('');
        }else{
            $data[] = array('','全部');
        }
        if(is_array($servers)){
            foreach($servers as $key=>$val){
                $data[] = array($val['server_id'],$val['name']);
            }
        }
        $_SESSION['getPlatGames']['game_id'] = $game_id;

        echo json_encode($data);
        exit;
    }

    //xheditor 图片上传
    public function xheditorUpImg(){
        require_once(CLASS_DIR . 'UploadFile.class.php');
        $upload = new UploadFile();
        $upload->maxSize  = 1*1024*1024; //1M 设置上传大小
        $upload->allowExts = array('jpg','gif','png','jpeg','bmp'); //设置上传类型
        $upload->savePath = 'upload/xheditor/'.date('Ym').'/'; // 设置上传目录
        $upload->saveRule=date("YmdHis");//上传文件的保存规则

        if(!$upload->uploadOne($_FILES['filedata'])) {
            $error = $upload->getErrorMsg();
            $statusCode = 300;
            $message = '上传失败，请重新上传';
        }else{
            $message = '恭喜您，上传成功';
            $statusCode = 200;
            $uploadList = $upload->getUploadFileInfo();
            $img = '/'.$upload->savePath.$uploadList[0]['savename']; //返回上传的目录
        }

        $return = array();
        if($_REQUEST['type']=='uploadify'){
            //需要在jquery.uploadify.js中配置上传的文件
            $return['statusCode'] = $statusCode;
            $return['message'] = $message.$error;
            $return['url'] = $img;
            $return['navTabId'] = "";
            $return['rel'] = "";
            $return['callbackType'] = "";
            $return['forwardUrl'] = "";
            $return['confirmMsg'] = "";
        }else{
            $return['err'] = $error;
            $return['msg'] = $img;
        }
        echo json_encode($return);
        exit;
    }
}