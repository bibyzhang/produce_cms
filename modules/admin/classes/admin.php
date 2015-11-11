<?php
/**
 ++++++++++++++++++++++++++++++++++++
 *后台管理首页
 ++++++++++++++++++++++++++++++++++++
 */
namespace modules\admin\classes;

defined('_ACCESS_GRANT') or exit('没有访问权限!');

//定义在后台
define('IN_WEEDONG_ADMIN', true);

class admin extends \common\libs\classes\Base{
    public function __construct(){
        parent::__construct();

        self::check_admin();//检测登录
        //self::check_grant();//检测操作权限
    }

    /** 设置操作数据表 */
    private $OperateTable = array(
    );

    //用户登陆检测
    final public function check_admin() {
        if(!$_SESSION['user_id']){
            session_destroy();
            echo '<script>window.location.href="index.php?m=admin&c=admin_user&a=login"</script>';
            exit();
        }
    }

    /** 用户操作权限判断 */
    final public function check_grant(){
        $gData=checkData($_GET);
        $AdminUserTable = $this->OperateTable['AdminUserTable'];
        $AdminUserRoleTable = $this->OperateTable['AdminUserRoleTable'];
        $AdminMenuTable = $this->OperateTable['AdminMenuTable'];

        if( $_SESSION['user_id']['user_id']==1 )
            return true;//超级管理员拥有所有权限

        $user_admin = $_SESSION['user_id']['user_name'];
        $sql = "SELECT au.auth AS au_auth,ar.auth AS ar_auth FROM $AdminUserTable AS au LEFT JOIN $AdminUserRoleTable AS ar ON au.role_id=ar.role_id WHERE au.user_name='" . $user_admin . "' LIMIT 1";
        $authData = $this->db->get($sql);

        $au_auth = unserialize( $authData['au_auth'] );
        $ar_auth = unserialize( $authData['ar_auth'] );

        if(!$au_auth)
            $au_auth = array(82);
        if(!$ar_auth)
            $ar_auth = array(82);

        $authArr = array_unique( array_merge($au_auth,$ar_auth) );

        $m = !empty($gData['m']) ? $gData['m'] : '';
        $c = !empty($gData['c']) ? $gData['c'] : '';
        $a = !empty($gData['a']) ? $gData['a'] : '';
        $opt = !empty($gData['opt']) ? $gData['opt'] : '';
        if(!empty($mid))
            $a .= '&amp;mid=' . $mid;

        //菜单操作权限id
        $sql = "SELECT * FROM $AdminMenuTable order by order_id asc,id asc";
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

        $authIdArr = array();
        foreach($menuData as $k=>$v){
            $authIdArr[$v['m']] = $v['id'];
            foreach($v['list'] as $ke=>$va){
                $authIdArr[$va['m'].'.'.$va['c']] = $va['id'];
                foreach($va['list'] as $key=>$val){
                    $authIdArr[$val['m'].'.'.$val['c'].'.'.$val['a']] = $val['id'];
                }
            }
        }

        if($m && !$c && !$a){
            if( empty($authIdArr[$m]) )
                die('没有权限操作');
            elseif( !in_array($authIdArr[$m],$authArr) )
                die('没有权限操作');
        }elseif($m && $c && !$a){
            if( empty($authIdArr[$m.'.'.$c]) )
                die('没有权限操作');
            elseif( !in_array($authIdArr[$m.'.'.$c],$authArr) )
                die('没有权限操作');
        }elseif($m && $c && $a){
            if( empty($authIdArr[$m.'.'.$c.'.'.$a]) )
                die('没有权限操作');
            elseif( !in_array($authIdArr[$m.'.'.$c.'.'.$a],$authArr) )
                die('没有权限操作');
        }else{
            die('没有权限操作');
        }

        return true;
    }
}