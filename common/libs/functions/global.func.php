<?php
/**
 ++++++++++++++++++++++++++++++++++++
 *  -> 公共函数库
 ++++++++++++++++++++++++++++++++++++
 */

/**
  +----------------------------------------------------------
  * 对列表数组进行排序
  +----------------------------------------------------------
  * @param array $list 列表数组
  * @param string $field 排序的字段名
  * 例：$list 为二维时 $field='["field_name"]' $list为三维时 $field='["field_name"]['field_name']';
  * @param array $sortby 排序类型
  * asc正向排序 desc逆向排序 nat自然排序
  +----------------------------------------------------------
  * @return array
  +----------------------------------------------------------
 */
function list_sort_by($list, $field, $sortby = 'asc') {
    if (is_array($list)) {
        $refer = $resultSet = array();
        foreach ($list as $i => $data)
            eval('$refer[$i]=&$data' . $field . ';');
        switch ($sortby) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc':// 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ($refer as $key => $val)
            $resultSet[$key] = &$list[$key]; //维持原来索引
        return $resultSet;
    }
    return false;
}

//*************************************字符串处理函数集***********************************
/* utf8中文字串截取函数 */
function strcut($str, $start, $len) {
    if ($start < 0)
        $start = strlen($str) + $start;
    $retstart = $start + getOfFirstIndex($str, $start);
    $retend = $start + $len - 1 + getOfFirstIndex($str, $start + $len);
    return substr($str, $retstart, $retend - $retstart + 1);
}

//判断字符开始的位置
function getOfFirstIndex($str, $start) {
    $char_aci = ord(substr($str, $start - 1, 1));
    if (223 < $char_aci && $char_aci < 240)
        return -1;
    $char_aci = ord(substr($str, $start - 2, 1));
    if (223 < $char_aci && $char_aci < 240)
        return -2;
    return 0;
}

/* 用户输入内容过滤函数 */

function getStr($str) {
    $tmpstr = trim($str);
    $tmpstr = strip_tags($tmpstr);
    $tmpstr = htmlspecialchars($tmpstr);
    $tmpstr = addslashes($tmpstr);
    return $tmpstr;
}

/* 简单防SQL注入函数 */

function getSQL($feild) {
    $tmpfeild = mysql_real_escape_string($feild);
    return $tmpfeild;
}

/* 判断是否ajax请求 */

function isAjax() {
    //jquery设定ajax请求的$_SERVER['HTTP_X_REQUESTED_WITH'] = XMLHttpRequest
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
        return true;
    return false;
}

/**
 * ajax异步返回
 * 输出前不能有其他输出内容,包括错误提示
 * 已替换至下方的版本
 */
/*
function ajaxReturn($info, $status = 200, $keyval = 0) {//exit('xxxxxxxxxxxxxxxxxxx');
    $result = array();
    $result['status'] = $status;
    $result['statusCode'] = $status;
    $result['message'] = $info;
    $result['navTabId'] = $_REQUEST['navTabId'];
    $result['rel'] = $info;
    $result['callbackType'] = $_REQUEST['callbackType']; // closeCurrent
    $result['forwardUrl'] = $_REQUEST['forwardUrl'];
    $result['pageNum'] = $_GET['pageNum'];
    if (is_array($keyval)) {
        foreach ($keyval as $key => $val) {
            $result[$key] = $val;
        }
    }
    // 返回JSON数据格式到客户端 包含状态信息
    header("Content-Type:text/html; charset=utf-8");
    exit(json_encode($result));
}
*/

/* ajax异步返回*/
function ajaxReturn(
    $info         = '',
    $status       = 300,
    $keyval       = 0,
    $navTabId     = '',
    $callbackType = '',
    $rel          = '',
    $forwardUrl   = '',
    $plat_id      = '',
    $agent_id     = '',
    $adid         = '',
    $id           = '',
    $selids       = '',
    $site_id      = '',
    $opeid        = ''
)
//友情提示：请勿随意改变原有参数次序 15-04-28
{
    $result                 =  array();
    $result['status']       = $status;
    $result['statusCode']   =  $status;
    $result['message']      =  $info;
    $result['navTabId']     =  $_REQUEST['navTabId']? $_REQUEST['navTabId'] : $navTabId ;
    $result['callbackType'] =  $callbackType;   // closeCurrent
    $result['rel']          =  $rel;
    $result['forwardUrl']   =  $forwardUrl;
    $result['plat_id']      =  $plat_id;
    $result['agent_id']     =  $agent_id;
    $result['adid']         =  $adid;
    $result['id']           =  $id;
    $result['selids']       =  $selids;
    $result['site_id']      =  $site_id;
    $result['opeid']        =  $opeid;
    if(is_array($keyval)){
        foreach ($keyval as $key=>$val){
            $result[$key]=$val;
        }
    }
    // 返回JSON数据格式到客户端 包含状态信息
    header("Content-Type:text/html; charset=utf-8");
    exit(json_encode($result));
}


//数据检查过滤
function checkData($data) {
    if (is_array($data)) {
        foreach ($data as $key => $v) {
            $data[$key] = checkData($v);
        }
    } else {
        $data = getStr($data);
    }
    return $data;
}

//检查权限
function checkgrant($module, $option) {
    return true;
}

/** 页面跳转 */
function toUrl($url, $memo = '') {
    switch ($url) {
        case 'LOGIN':
            header("Location: index.php?m=admin&c=admin_user&a=login");
            break;
        case 'INDEX':
            header("Location: index.php?m=admin&c=admin_manage&a=index");
            break;
        case 'ERROR':
            header("Location: /?action=sys&opt=error&memo=" . urldecode($memo));
            break;
        default:
            header("Location: " . $url);
            break;
    }
}

//数据返回
function show($status, $message, $data=null, $type='json'){
    /*if(!is_numeric($status)){
        return 0;
    }*/
    if($type == 'json'){
        $re['status'] = "$status";
        $re['message'] = $message;
        if(!empty($data))
            $re['data'] = $data;
        echo json_encode($re);
    }else{

    }

    exit();
}

//打印返回信息
function show_message($status){
    $message = array('status'=>$status);
    echo json_encode($message);
    exit();
}

/**
 +----------------------------------------------------------
 * 判断日期格式是否正确
 +----------------------------------------------------------
 * 判断格式 yyyy-mm-dd | yyyy-mm-dd hh:ii:ss
 +----------------------------------------------------------
 * @param $tdate 要判断日期
 * @param $dateformat 要判断的日期格式 "Y-m-d"或"Y-m-d H:i:s"
 +----------------------------------------------------------
 */
function is_date($tdate,$dateformat="Y-m-d"){
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

// * 把返回的数据集转换成Tree
function list_to_tree($list, $pk='id',$pid = 'pid',$child = '_child',$root=0){
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach($list as $key => $data){
            $refer[$data[$pk]] =& $list[$key];
        }

        foreach($list as $key => $data){
            // 判断是否存在parent
            $parentId = $data[$pid];
            if($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if(isset($refer[$parentId])){
                    $parent =& $refer[$parentId];
                    //$parent = $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                    //$parent[$child][] = $list[$key];
                }
            }
        }
    }
    return $tree;
}

function microtime_float(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}


/**
 * 判断是否SSL协议
 * @return boolean
 */
function is_ssl(){
    if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
        return true;
    }elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
        return true;
    }
    return false;
}

/**
 * URL 组装
 * @param
 * @return
 */
function redirect(){
    array('admin','admin_manage','user_add');

    return false;
}