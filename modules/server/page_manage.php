<?php
// +----------------------------------------------------------------------
// |
// +----------------------------------------------------------------------
// | page缓存接口
// +----------------------------------------------------------------------

namespace modules\server;

defined('_ACCESS_GRANT') or exit('Forbidden!');

class page_manage extends \common\libs\classes\Base
{
    private $gameInfoDetai = '';
    private $cdn_url = 'http://app.static..com';
    private $url_path = '';

    private $pData = '';
    private $gData = '';

    public function __construct(){
        parent::__construct();

        $this->pData = checkData($_POST);
        $this->gData = checkData($_GET);

        global $action;

        if($action=='cache_index')
            $this->url_path = '/server/page_manage/cache_index';
        elseif($action=='game_data')
            $this->url_path = '/server/page_manage/game_data';

        self::check_token($this->url_path);//请求验证

        $this->library('game_library/GameInfoDetailModel');
        $this->gameInfoDetai = $this->model('GameInfoDetailModel');
    }

    /** 设置操作数据表 */
    private $OperateTable = array(
        'GameTopTable' => 'app_gameTop',//游戏排行
        'WebhookGamePushTable' => 'app_webhook_game_push',//定时生成游戏静态
        'GamepassageContentTable' => 'app_new_passage_content',//文章内容表
        'GameInfoBaseTable' => 'app_new_game_info_base',//游戏基本信息表
        'GameInfoDetailTable' => 'app_new_game_info_detail',//游戏详细信息表
    );

    /** 生成首页 */
    public function cache_index(){
        $pData = checkData($_POST);
        $auth = $pData['auth']+0;

        $this->tpl->assign('headSelect','home');
        $this->tpl->assign('host',APP_PATH);
        $this->tpl->assign('images_path',IMAGES_PATH);
        $this->tpl->assign('db',$this->db);
        $this->tpl->createHtml('html/index', HTML_PATH . 'index.html');

        if($auth==1)
            ajaxReturn('生成首页成功', 200);
        else
            echo 1;
    }

    /** 批量更新栏目页 */
    public function cache_category(){
        $pData = checkData($_POST);
        $auth = $pData['auth']+0;

        $this->tpl->assign('host',APP_PATH);
        $this->tpl->assign('images_path',IMAGES_PATH);
        $this->tpl->assign('db',$this->db);

        $ios = $pData['ios']?$pData['ios']:'';
        $android = $pData['android']?$pData['android']:'';
        $wp = $pData['wp']?$pData['wp']:'';
        $search = $pData['search']?$pData['search']:'';
        $common = $pData['common']?$pData['common']:'';

        if($ios){
            $this->tpl->assign('headSelect','ios');
            $this->tpl->createHtml('html/ios', HTML_PATH . 'ios.html');
            $this->setLog('生成IOS栏目成功');
        }
        if($android){
            $this->tpl->assign('headSelect','android');
            $this->tpl->createHtml('html/android', HTML_PATH . 'android.html');
            $this->setLog('生成android栏目成功');
        }
        if($wp){
            $this->tpl->assign('headSelect','wp');
            $this->tpl->createHtml('html/wp', HTML_PATH . 'wp.html');
            $this->setLog('生成wp栏目成功');
        }
        if($search){
            $this->tpl->assign('headSelect','search');
            $this->tpl->createHtml('html/search_game', HTML_PATH . 'search.html');
            $this->setLog('生成search栏目成功');
        }
        //生成静态头部&&底部
        if($common){
            $this->tpl->createHtml('html/header', BASE_PATH . 'templates/api/header.html');
            $this->tpl->createHtml('html/footer', BASE_PATH . 'templates/api/footer.html');
            $this->setLog('生成静态头部&&底部成功');
        }

        ajaxReturn('栏目更新成功', 200, 0, 'page68', '');
    }

    /** 批量更新内容页 */
    public function contents_update(){
        $gData = checkData($_GET);
        $pData = checkData($_POST);

        $GameTopTable = $this->OperateTable['GameTopTable'];
        $WebhookGamePushTable = $this->OperateTable['WebhookGamePushTable'];
        $GamepassageContentTable = $this->OperateTable['GamepassageContentTable'];

        //游戏总数
        $totalCount = $this->gameInfoDetai->get_list_total('id', 'status=1');
        $this->s->assign('totalCount',$totalCount);

        //需要更新游戏数
        $sql = "SELECT COUNT(id) AS c FROM $WebhookGamePushTable WHERE state=0";
        $sum_webhook = $this->db->get($sql);
        $this->s->assign('sum_webhook',$sum_webhook['c']);

        if($pData) {

//            $numPerPage = 10;//每次条数
//            $totalPage = ceil($totalCount / $numPerPage);//总页数
//            //$totalPage=1;
//            for ($pageNum = 1; $pageNum <= $totalPage; $pageNum++) {
//                if ($pageNum > $totalPage)
//                    $pageNum = $totalPage;
//                if ($pageNum < 1)
//                    $pageNum = 1;
//
//                $skip = ($pageNum - 1) * $numPerPage;

            //var_dump($pData);exit();
//            die(json_encode(array('code' => -202, "msg" => $pData)));

            $skip = $pData['skip'];
            $numPerPage = $pData['numPerPage'];

            $gameDetailData = $this->gameInfoDetai->get_list('status=1', $skip, $numPerPage);

            //if(empty($gameDetailData))// //continue;
            //die(json_encode(array('code' => -202, "msg" => "1111")));

            if(empty($gameDetailData)){
                echo '数据不存在';exit();
            }

            foreach($gameDetailData as $_v){
                //赞
                $sql = "SELECT zan FROM $GameTopTable WHERE whId=".$_v['game_id']." LIMIT 1";
                $zanData = $this->db->get($sql);
                $zan = $zanData['zan']+0;

                //游戏截图
                $sql = "SELECT `game_id`, `img_url` FROM `app_new_game_img` WHERE game_id=" . $_v['game_id'] . " AND status=1";
                $imgArr = $this->db->find($sql);
                $this->tpl->assign('imgArr',$imgArr);

                //下载地址{下载类型[1:iOS2:iOS越狱3:本地下载(APK)4:Google Play5:WP6:安卓特别版]}
                $sql = "SELECT `game_id`, `download_url`, `type` FROM `app_new_game_download_url` WHERE game_id=" . $_v['game_id'];
                $downArr = $this->db->find($sql);
                $downIphone = array();
                $downWindows = array();
                $downAndroid = array();
                foreach($downArr as $key=>$value){
                    if( $value['type']==1 || $value['type']==2 || $value['type']==7 && !empty($value['download_url']) ){
                        $downIphone['download_url'] = $value['download_url'];
                        $downIphone['type_name'] = ($value['type']==1) ? 'APP Store 下载' : '越狱版下载';
                    }elseif( $value['type']==5 && !empty($value['download_url']) ){
                        $downWindows['download_url'] = $value['download_url'];
                        $downWindows['type_name'] = 'Windows Phone 下载';
                    }elseif( $value['type']==3 || $value['type']==4 || $value['type']==6 || $value['type']==8 && !empty($value['download_url']) ){
                        $downAndroid['download_url'] = $value['download_url'];
                        $downAndroid['type_name'] = '本地下载';
                    }
                }
                $this->tpl->assign('downIphone',$downIphone);
                $this->tpl->assign('downWindows',$downWindows);
                $this->tpl->assign('downAndroid',$downAndroid);

                //资讯新闻
                $newsData = $this->get_game_category_passage($_v['game_id'],4);
                $this->tpl->assign('newsData',$newsData);

                //攻略
                $raiders = $this->get_game_category_passage($_v['game_id'],5);
                $this->tpl->assign('raiders',$raiders);

                //专题
                $special = $this->get_game_category_passage($_v['game_id'],7);
                $this->tpl->assign('special',$special);

                //评测
                $assess = $this->get_game_category_passage($_v['game_id'],6);
                $this->tpl->assign('assess',$assess);

                //新手攻略
                $new_raiders = $this->get_game_category_passage($_v['game_id'],16);
                $this->tpl->assign('new_raiders',$new_raiders);

                //高手进阶
                $advance = $this->get_game_category_passage($_v['game_id'],18);
                $this->tpl->assign('advance',$advance);

                //游戏视频
                $sql = "SELECT article_url,title,icon FROM $GamepassageContentTable WHERE game_id=" . $_v['game_id'] . " AND term_id=3";
                $video = $this->db->find($sql);
                foreach($video as $ka=>&$va){
                    $va['icon'] = IMAGES_PATH . $va['icon'];
                }
                $this->tpl->assign('video',$video);

                $_v['modified_time'] = date("Y-m-d", strtotime($_v['modified_time']));

                $this->tpl->assign('db',$this->db);
                $this->tpl->assign('headSelect','game_info');
                $this->tpl->assign('host',APP_PATH);
                $this->tpl->assign('images_path',IMAGES_PATH);
                $this->tpl->assign('list',$_v);

                $this->tpl->createHtml('html/game_info', HTML_PATH . 'game/'.$_v['game_id'].'.html');
                //游戏攻略详情也
                $this->tpl->createHtml('html/game_message', HTML_PATH . 'game_message/'.$_v['game_id'].'.html');

                //更新生成状态
                $sql = "UPDATE $WebhookGamePushTable SET `state`=1 WHERE whId=" . $_v['game_id'];
                $this->db->query($sql);

                //记录未生成静态记录到Redis
                $sql = "SELECT whId FROM app_webhook_game_push WHERE state=0 AND whId>0";
                $gameList = $this->db->find($sql);
                $gameListData = '(0,';
                if( !empty($gameList) ) {
                    foreach ($gameList as $v)
                        $gameListData .= $v['whId'] . ',';
                }
                $gameListData = substr($gameListData,0,-1);
                $gameListData .= ')';

                $this->redis->set('webhook_game_push', json_encode($gameListData));

                //echo '更新游戏:<span style="color: #cd5c5c;">' . $_v['game_name_cn'] . '</span>&nbsp;&nbsp;成功,前端页面为:&nbsp;&nbsp;<a href="http:///game/'.$_v['game_id'].'.html" style="color: orange;" target="_blank;">http:///game/'.$_v['game_id'].'.html</a>' . "<br />";
                echo '更新游戏:<span style="color: #cd5c5c;">' . $_v['game_name_cn'] . '</span>&nbsp;&nbsp;成功,前端页面为:&nbsp;&nbsp;<a href="http://app..com/game/'.$_v['game_id'].'.html" style="color: orange;" target="_blank;">http://app..com/game/'.$_v['game_id'].'.html</a>' . "<br />";
            }
            //}

            $this->setLog('批量生成游戏内容页成功!');
        }else{
            $this->s->display('pc_manage/contents_update.html');
        }
    }

    /** 更新最新添加&&修改游戏 */
    public function showUpdate(){
        $GameTopTable = $this->OperateTable['GameTopTable'];
        $WebhookGamePushTable = $this->OperateTable['WebhookGamePushTable'];
        $GameInfoBaseTable = $this->OperateTable['GameInfoBaseTable'];
        $GameInfoDetailTable = $this->OperateTable['GameInfoDetailTable'];
        $GamepassageContentTable = $this->OperateTable['GamepassageContentTable'];

        //需要更新游戏数
        $sql = "SELECT whId FROM $WebhookGamePushTable WHERE state=0";
        $webhookData = $this->db->find($sql);

        foreach ($webhookData as $k => $v) {
            if( empty($v['whId']) )
                continue;

            //游戏基本信息
            $sql = "SELECT `game_id`,`icon`,`remote_icon`,`game_name_cn`,`game_name_en`,`modified_time`,`type`,`is_network`,`size`,`intro`,`describe`,`special_url`,`grade` FROM $GameInfoDetailTable WHERE game_id=".$v['whId']." LIMIT 1";
            $list = $this->db->get($sql);

            if( empty($list) )
                continue;

            if($list) {
                //赞
                $sql = "SELECT zan FROM $GameTopTable WHERE whId=" . $v['whId'] . " LIMIT 1";
                $zanData = $this->db->get($sql);
                $zan = $zanData['zan'];

                //游戏截图
                $sql = "SELECT `game_id`, `img_url` FROM `app_new_game_img` WHERE game_id=" . $v['whId'] . " AND status=1";
                $imgArr = $this->db->find($sql);
                $this->tpl->assign('imgArr', $imgArr);

                //下载地址{下载类型[1:iOS2:iOS越狱3:本地下载(APK)4:Google Play5:WP6:安卓特别版]}
                $sql = "SELECT `game_id`, `download_url`, `type` FROM `app_new_game_download_url` WHERE game_id=" . $v['whId'];
                $downArr = $this->db->find($sql);
                $downIphone = array();
                $downWindows = array();
                $downAndroid = array();
                foreach($downArr as $key=>$value){
                    if( $value['type']==1 || $value['type']==2 || $value['type']==7 && !empty($value['download_url']) ){
                        $downIphone['download_url'] = $value['download_url'];
                        $downIphone['type_name'] = ($value['type']==1) ? 'APP Store 下载' : '越狱版下载';
                    }elseif( $value['type']==5 && !empty($value['download_url']) ){
                        $downWindows['download_url'] = $value['download_url'];
                        $downWindows['type_name'] = 'Windows Phone 下载';
                    }elseif( $value['type']==3 || $value['type']==4 || $value['type']==6 || $value['type']==8 && !empty($value['download_url']) ){
                        $downAndroid['download_url'] = $value['download_url'];
                        $downAndroid['type_name'] = '本地下载';
                    }
                }
                $this->tpl->assign('downIphone', $downIphone);
                $this->tpl->assign('downWindows', $downWindows);
                $this->tpl->assign('downAndroid', $downAndroid);

                //资讯新闻
                $newsData = $this->get_game_category_passage($v['whId'],4);
                $this->tpl->assign('newsData',$newsData);

                //攻略
                $raiders = $this->get_game_category_passage($v['whId'],5);
                $this->tpl->assign('raiders',$raiders);

                //专题
                $special = $this->get_game_category_passage($v['whId'],7);
                $this->tpl->assign('special',$special);

                //评测
                $assess = $this->get_game_category_passage($v['whId'],6);
                $this->tpl->assign('assess',$assess);

                //新手攻略
                $new_raiders = $this->get_game_category_passage($v['whId'],16);
                $this->tpl->assign('new_raiders',$new_raiders);

                //高手进阶
                $advance = $this->get_game_category_passage($v['whId'],18);
                $this->tpl->assign('advance',$advance);

                //游戏视频
                $sql = "SELECT article_url,title,icon FROM $GamepassageContentTable WHERE game_id=" . $v['whId'] . " AND term_id=3";
                $video = $this->db->find($sql);
                foreach($video as $ka=>&$va){
                    $va['icon'] = IMAGES_PATH . $va['icon'];
                }
                $this->tpl->assign('video',$video);

                $list['modified_time'] = date("Y-m-d", strtotime($list['modified_time']));

                $this->tpl->assign('db', $this->db);
                //$this->tpl->assign('host',IMAGES_PATH . DIRECTORY_SEPARATOR);
                $this->tpl->assign('host',APP_PATH);
                $this->tpl->assign('images_path',IMAGES_PATH);
                $this->tpl->assign('headSelect', 'game_info');
                $this->tpl->assign('list', $list);

                //游戏详情页
                $this->tpl->createHtml('html/game_info', HTML_PATH . 'game/'.$v['whId'].'.html');
                //游戏攻略详情也
                $this->tpl->createHtml('html/game_message', HTML_PATH . 'game_message/'.$v['whId'].'.html');

                //更新生成状态
                $sql = "UPDATE $WebhookGamePushTable SET `state`=1 WHERE whId=" . $v['whId'];
                $this->db->query($sql);

                //记录未生成静态记录到Redis
                $sql = "SELECT whId FROM app_webhook_game_push WHERE state=0 AND whId>0";
                $gameList = $this->db->find($sql);
                $gameListData = '(0,';
                if( !empty($gameList) ) {
                    foreach ($gameList as $v)
                        $gameListData .= $v['whId'] . ',';
                }
                $gameListData = substr($gameListData,0,-1);
                $gameListData .= ')';

                $this->redis->set('webhook_game_push', json_encode($gameListData));

                $this->setLog('生成游戏:{'.$v['whId'].'},内容页成功');
            }
        }

        ajaxReturn('更新手机游戏内容页成功', 200, 0, 'page67', '');
    }

    /** 接口验证 */
    static public function check_token($url_path=''){
        $gData = checkData($_GET);
        $pData = checkData($_POST);
        $appkey = "T25MqEPV7vEeL2v7";

        $paramsArray = $gData;

        $paramsArray['timestamp'] = $gData['timestamp']+0;
        //var_dump($paramsArray);//exit();
        if( empty($paramsArray['timestamp']) )
            die(json_encode(array('code' => -101, "msg" => "缺少参数")));
        //请求限制在当前5分钟内
        if( time()-$paramsArray['timestamp']>300 )
            die(json_encode(array('code' => -102, "msg" => "参数错误")));

        $sig = !empty($pData['sig']) ? $pData['sig'] : '';
        //签名生成串
        $token = self::makeSig('GET', $url_path, $paramsArray, $appkey);
        //echo $token;

        if($sig!=$token)
            die(json_encode(array('code' => -103, "msg" => "参数错误")));

        return true;
    }

    static public function makeSig($method, $url_path, $params, $secret){
        $mk = self::makeSource($method, $url_path, $params);
        $my_sign = hash_hmac("sha1", $mk, strtr($secret, '-_', '+/'), true);
        $my_sign = base64_encode($my_sign);
        return $my_sign;
    }

    static private function makeSource($method, $url_path, $params){
        $strs = strtoupper($method) . '&' . rawurlencode($url_path) . '&';

        ksort($params);
        $query_string = array();
        foreach ($params as $key => $val ){
            array_push($query_string, $key . '=' . $val);
        }
        $query_string = join('&', $query_string);

        return $strs . str_replace('~', '%7E', rawurlencode($query_string));
    }
}