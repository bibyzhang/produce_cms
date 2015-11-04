<?php
/**
 ++++++++++++++++++++++++++++++++++++
 *  -> 系统操作管理
 ++++++++++++++++++++++++++++++++++++
 */
namespace modules\admin;

defined('_ACCESS_GRANT') or exit('没有访问权限!');

class opmanage_admin extends \modules\admin\classes\admin{
    public function __construct(){
        parent::__construct();

        $this->db1 = new \common\libs\classes\Mysql(1);//访问日志数据库
    }

    public function oplogs_list(){
        $pData=checkData($_POST);

        //生成SELECT选项
        $selectActionArr = array(
            //所属应用
            'app_name' => array(
                0 => array(
                    'val' => 0,
                    'desc' => '--所有--',
                ),
                1 => array(
                    'val' => 1,
                    'desc' => '',
                )
            )
        );
        $this->s->assign('selectActionArr',$selectActionArr);

        $tb="_logs_".date('Ym',time());

        $where = 1;
        $post_arr = array();
        if(!empty($pData)){
            $post_arr['user_admin'] = !empty($pData['user_admin']) ? $pData['user_admin'] : '';
            $post_arr['ip'] = !empty($pData['ip']) ? $pData['ip'] : '';
            $post_arr['keyword'] = !empty($pData['keyword']) ? $pData['keyword'] : '';
            $post_arr['app_id'] = !empty($pData['app_id']) ? $pData['app_id'] : 0;
            //不支持跨月份查询
            $post_arr['startdate'] = !empty($pData['startdate']) ? $pData['startdate'] : '';
            $post_arr['enddate'] = !empty($pData['enddate']) ? $pData['enddate'] : '';

            if( $post_arr['startdate'] && $post_arr['enddate'] ){
                if(!strtotime($post_arr['startdate']) || !strtotime($post_arr['enddate']))
                    ajaxReturn('请输入正确的日期格式',300);
                if( date("Y-m",strtotime($post_arr['startdate'])) != date("Y-m",strtotime($post_arr['enddate'])))
                    ajaxReturn('不支持跨月份查询',300);
                $where .= " AND logtime>=" . strtotime($post_arr['startdate'] . '00:00:00') . " AND logtime<=" . strtotime($post_arr['enddate'] . '23:59:59');
            }

            if( !empty($post_arr['user_admin']) )
                $where .= " AND uName='" . $post_arr['user_admin'] . "'";
            if( !empty($post_arr['ip']) )
                $where .= " AND ip='" . $post_arr['ip'] . "'";
            if( !empty($post_arr['keyword']) )
                $where .= " AND menu like '%". $post_arr['keyword'] ."%'";
            if( !empty($post_arr['app_id']) )
                $where .= " AND app_id =". $post_arr['app_id'];
        }

        $numPerPage = !empty($pData['numPerPage']) ? $pData['numPerPage'] : 30;//每页显示条数
        $pageNum = !empty($pData['pageNum']) ? $pData['pageNum'] : 1;//当前页

        $sql = "SELECT count(id) AS ct FROM $tb WHERE $where";
        $count = $this->db1->get($sql);
        $totalCount = $count['ct'];//数据总条数

        $totalPage = ceil($totalCount/$numPerPage);//总页数

        if( $pageNum > $totalPage )
            $pageNum = $totalPage;
        if($pageNum < 1)
            $pageNum = 1;

        $skip = ($pageNum-1)*$numPerPage;

        $param = array('totalCount'=>$totalCount,'numPerPage'=>$numPerPage,'currentPage'=>$pageNum);
        $this->s->assign('param',$param);

        $sql = "SELECT `id`, `uName`, `ip`, `logtime`, `app_id`, `m`, `c`, `a`, `menu`, `memo` FROM $tb WHERE $where ORDER BY logtime DESC LIMIT ". $skip . "," . $numPerPage;
        $data = $this->db1->find($sql);
        $this->s->assign('post_arr',$post_arr);

        $this->s->assign('data',$data);
        $this->s->display('admin/oplogs_list.html');
    }
}