<?php
/**
 ++++++++++++++++++++++++++++++++++++
 *  -> 后台管理首页
 ++++++++++++++++++++++++++++++++++++
 */
namespace modules\admin;

defined('_ACCESS_GRANT') or exit('没有访问权限!');

class admin_manage extends \modules\admin\classes\admin{
    public function __construct(){
        parent::__construct();
    }

    /** 设置操作数据表 */
    private $OperateTable = array(
    );

    /** 后台首页 */
    public function index(){
        $getData = checkData($_GET);
        $AdminUserTable = $this->OperateTable['AdminUserTable'];
        $AdminUserRoleTable = $this->OperateTable['AdminUserRoleTable'];

        $user_admin = $_SESSION['user_id']['user_name'];
        $this->s->assign('user_admin',$user_admin);

        //管理菜单
        $mid = !empty($getData['mid']) ? $getData['mid'] : 2;//默认管理游戏库
        $menuData = $this->leftMenu($mid);
        $this->s->assign('menuData',$menuData);

        //管理员信息
        $sql = "SELECT au.user_name,au.lastlogin_time,au.lastlogin_ip,au.login_times,ar.rolename FROM $AdminUserTable AS au LEFT JOIN $AdminUserRoleTable AS ar ON au.role_id=ar.role_id WHERE au.user_name='" . $user_admin . "'";
        $adminUserData = $this->db->get($sql);
        $this->s->assign('adminUserData',$adminUserData);

        $this->s->assign('site_url',IMAGES_PATH . '//');
        $this->s->display('index.html');
    }

    /** 左侧菜单栏[横向导航条] */
    public function left(){
        $getData = checkData($_GET);

        //管理菜单
        $mid = !empty($getData['mid']) ? $getData['mid'] : 2;//默认管理游戏盒子
        $menuData = $this->leftMenu($mid);

        $this->s->assign('menuData',$menuData);
        $this->s->display('system/leftMenu.html');
    }

    /** 根据权限获取左侧菜单列表 */
    private function leftMenu($mid){
        $menuData = $this->getMenuList($mid);
        foreach($menuData as $key=>$val){
            if($val['status']==1 && ($_SESSION['user_id']['actionid']=='all' || in_array($val['id'],$_SESSION['user_id']['actionid'])) && !empty($val['list']) && count($val['list'])>0){
                if(!empty($val['list'])){
                    foreach($val['list'] as $ke=>$va){
                        if($va['status']==1 && ($_SESSION['user_id']['actionid']=='all' || in_array($va['id'],$_SESSION['user_id']['actionid'])) && count($va['list'])>0 ){
                            foreach($va['list'] as $k=>$v){
                                if($v['status']!=1 || ($_SESSION['user_id']['actionid']!='all' && !in_array($v['id'],$_SESSION['user_id']['actionid']))){
                                    unset($menuData[$key]['list'][$ke]['list'][$k]);
                                }
                            }
                        } else {
                            unset($menuData[$key]['list'][$ke]);
                        }
                    }
                }
            } else {
                unset($menuData[$key]);
            }
        }

        return $menuData;
    }
}