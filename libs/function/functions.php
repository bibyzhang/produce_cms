<?php
// +----------------------------------------------------------------------
// | @file functions.php
// +----------------------------------------------------------------------
// | @desc 系统全局函数库
// +----------------------------------------------------------------------
// | @author bibyzhang90@gmail.com
// +----------------------------------------------------------------------

/**
 * 判断是否SSL协议
 *
 * @return bool True使用SSL, False未使用SSL
 */
function is_ssl() {
    if ( isset($_SERVER['HTTPS']) ) {
        if ( 'on' == strtolower($_SERVER['HTTPS']) )
            return true;
        if ( '1' == $_SERVER['HTTPS'] )
            return true;
    } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
        return true;
    }
    return false;
}

/**
 * 判断日期格式是否正确
 *
 * 判断格式 yyyy-mm-dd | yyyy-mm-dd hh:ii:ss
 *
 * @param $tdate 要判断日期
 * @param $dateformat 要判断的日期格式 "Y-m-d"或"Y-m-d H:i:s"
 *
 * @return bool True格式正确, False格式错误
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




/* 用户输入内容过滤函数 */

function getStr($str) {
    $tmpstr = trim($str);
    $tmpstr = strip_tags($tmpstr);
    $tmpstr = htmlspecialchars($tmpstr);
    $tmpstr = addslashes($tmpstr);
    return $tmpstr;
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