<?php
/**
 ++++++++++++++++++++++++++++++++++++
 *  帐号权限管理
 ++++++++++++++++++++++++++++++++++++
 */
namespace modules\admin;

defined('_ACCESS_GRANT') or exit('没有访问权限!');

require_once(MODULE_PATH."admin/classes/admin.class.php");

class user_grant_admin extends  \modules\admin\classes\admin{
    public function __construct(){
        parent::__construct();
    }

    /** 设置操作数据表 */
    private $OperateTable = array(
    );

    /** 系统帐号列表 */
    public function user_list(){
        $pData=checkData($_POST);
        $AdminUserTable = $this->OperateTable['AdminUserTable'];
        $AdminRoleTable = $this->OperateTable['AdminRoleTable'];

        //权限组
        $sql_grant = "SELECT role_id,rolename FROM $AdminRoleTable";
        $pmData = $this->db->find($sql_grant);
        $pmData[-1]['role_id'] = -1;
        $pmData[-1]['rolename'] = '--所有--';
        ksort($pmData);
        $this->s->assign('pmData',$pmData);

        //生成SELECT选项
        $selectActionArr = array(
            //帐号状态
            'status' => array(
                -1 => array(
                    'val' => -1,
                    'desc' => '--所有--',
                ),
                0 => array(
                    'val' => 0,
                    'desc' => '禁用',
                ),
                1 => array(
                    'val' => 1,
                    'desc' => '开启',
                )
            ),
        );
        $this->s->assign('selectActionArr',$selectActionArr);

        $post_arr = array();
        $where = "1";
        $post_arr['status'] = isset($pData['status']) ? $pData['status'] : -1;//默认所有状态
        if( $post_arr['status']!=-1 )
            $where .= " AND au.status=" . $post_arr['status'];
        $post_arr['role_id'] = isset($pData['role_id']) ? $pData['role_id'] : -1;//默认所有权限
        if( $post_arr['role_id']!=-1 )
            $where .= " AND au.role_id=" . $post_arr['role_id'];
        if($pData){
            $post_arr['user_name'] = trim($pData['user_name']);
            if($post_arr['user_name'])
                $where .= " AND au.user_name LIKE '%" . $post_arr['user_name'] . "%'";
        }

        $order = " ORDER BY au.user_id ASC";

        $numPerPage = !empty($pData['numPerPage']) ? $pData['numPerPage'] : 30;//每页显示条数
        $pageNum = !empty($pData['pageNum']) ? $pData['pageNum'] : 1;//当前页

        //数据总条数
        $sql = "SELECT count(au.user_id) AS ct FROM $AdminUserTable AS au WHERE $where";
        $count = $this->db->get($sql);
        $totalCount = $count['ct'];

        $totalPage = ceil($totalCount/$numPerPage);//总页数

        if( $pageNum > $totalPage )
            $pageNum = $totalPage;
        if($pageNum < 1)
            $pageNum = 1;

        $skip = ($pageNum-1)*$numPerPage;

        $param = array('totalCount'=>$totalCount,'numPerPage'=>$numPerPage,'currentPage'=>$pageNum);
        $this->s->assign('param',$param);

        $sql = "SELECT au.`user_id`, au.`user_name`, au.`email`, au.`role_id`, au.`lastlogin_time`, au.`lastlogin_ip`, au.`login_times`, au.`create_time`, au.`status`,ar.rolename FROM $AdminUserTable AS au LEFT JOIN $AdminRoleTable AS ar ON au.role_id=ar.role_id WHERE $where $order LIMIT $skip,$numPerPage";
        $userData = $this->db->find($sql);

        $this->s->assign('post_arr',$post_arr);
        $this->s->assign('userData',$userData);
        $this->s->display('admin/user_list.html');
    }

    /** 系统帐号操作 */
    public function user_operate(){
        $gData=checkData($_GET);
        $pData=checkData($_POST);
        $AdminUserTable = $this->OperateTable['AdminUserTable'];
        $AdminRoleTable = $this->OperateTable['AdminRoleTable'];

        $user_id = !empty($gData['user_id']) ? $gData['user_id'] : $pData['user_id'];
        if(!$user_id)
            ajaxReturn('非法操作[缺少操作id]',300);

        //帐号信息
        $getname_sql = "SELECT user_name,status FROM $AdminUserTable WHERE user_id=" . $user_id . " LIMIT 1";
        $getname_data = $this->db->get($getname_sql);
        $user_name = $getname_data['user_name'];
        $status = $getname_data['status'];

        $opt = $gData['opt'];
        if(!$opt)
            ajaxReturn('非法操作[缺少操作参数]',300);

        //恢复
        if( $opt=='user_open' ){
            if($status==1)
                ajaxReturn('当前帐号为已启用状态',300);

            $sql = "UPDATE $AdminUserTable SET status=1 WHERE user_id=" . $user_id . " LIMIT 1";
            if( $this->db->query($sql) ){
                $this->setLog('恢复系统用户{'.$user_name.'}成功！');
                $back_json = '{
                    "statusCode":"200",
                    "message":"恢复系统用户{'.$user_name.'}成功！",
                    "navTabId":"page12"
                }';
                echo $back_json; exit;
            } else {
                $this->setLog('恢复系统用户{'.$user_name.'}失败！');
                ajaxReturn('恢复系统用户失败',300);
            }
        }

        //禁用
        if( $opt=='user_delete' ){
            if($status!=1)
                ajaxReturn('当前帐号为未启用状态',300);

            $sql = "UPDATE $AdminUserTable SET status=0 WHERE user_id=" . $user_id . " LIMIT 1";
            if( $this->db->query($sql) ){
                $this->setLog('禁用系统用户{'.$user_name.'}成功！');
                $back_json = '{
                    "statusCode":"200",
                    "message":"禁用系统用户{'.$user_name.'}成功！",
                    "navTabId":"page12"
                }';
                echo $back_json; exit;
            } else {
                $this->setLog('禁用系统用户{'.$user_name.'}失败！');
                ajaxReturn('禁用系统用户失败',300);
            }
        }

        //删除
        if( $opt=='user_drop' ){
            $sql = "DELETE FROM $AdminUserTable WHERE user_id=" . $user_id . " LIMIT 1";
            if( $this->db->query($sql) ){
                $this->setLog('删除系统用户{'.$user_name.'}成功！');
                $back_json = '{
                    "statusCode":"200",
                    "message":"删除系统用户{'.$user_name.'}成功！",
                    "navTabId":"page12"
                }';
                echo $back_json; exit;
            } else {
                $this->setLog('删除系统用户{'.$user_name.'}失败！');
                ajaxReturn('删除系统用户失败',300);
            }
        }

        //编辑
        if( $opt=='user_edit' ){
            if($status!=1)
                ajaxReturn('当前帐号为未启用状态',300);

            if($pData){//修改
                $user_name = $pData['user_name'];
                $email = $pData['email'];
                $role_id = $pData['role_id'];

                if(!$user_name)
                    ajaxReturn('用户名不能为空',300);

                $sql = "UPDATE $AdminUserTable SET user_name='{$user_name}',email='{$email}',role_id=$role_id WHERE user_id=" . $user_id . " AND status=1 LIMIT 1";
                if( $this->db->query($sql) ){
                    $this->setLog('修改系统用户{'.$user_name.'}成功！');
                    $back_json = '{
                    "statusCode":"200",
                    "message":"修改系统用户{'.$user_name.'}成功！",
                    "navTabId":"page12"
                }';
                    echo $back_json; exit;
                } else {
                    $this->setLog('修改系统用户{'.$user_name.'}失败！');
                    ajaxReturn('修改系统用户失败',300);
                }
            }else{//回显
                //权限组
                $sql_grant = "SELECT role_id,rolename FROM $AdminRoleTable";
                $pmData = $this->db->find($sql_grant);
                $pmData[-1]['role_id'] = -1;
                $pmData[-1]['rolename'] = '--所有--';
                ksort($pmData);
                $this->s->assign('pmData',$pmData);

                //权限组
                $sql_grant = "SELECT role_id,rolename FROM $AdminRoleTable";
                $data_grant = $this->db->find($sql_grant);
                $this->s->assign('data_grant',$data_grant);

                $sql = "SELECT `user_id`, `user_name`, `email`, `role_id` FROM $AdminUserTable WHERE user_id=" . $user_id . " LIMIT 1";
                $gbdata = $this->db->get($sql);
                $this->s->assign('gbdata',$gbdata);
                $this->s->display('admin/user_add.html');
            }
        }
    }

    /** 增加[编辑]系统帐号 */
    public function user_add(){
        $pData=checkData($_POST);
        $AdminUserTable = $this->OperateTable['AdminUserTable'];
        $AdminRoleTable = $this->OperateTable['AdminRoleTable'];

        //权限组
        $sql_grant = "SELECT role_id,rolename FROM $AdminRoleTable";
        $pmData = $this->db->find($sql_grant);
        $pmData[-1]['role_id'] = -1;
        $pmData[-1]['rolename'] = '--所有--';
        unset($pmData[0]);//不能添加超级管理员
        ksort($pmData);
        $this->s->assign('pmData',$pmData);

        $opt = $pData['opt'];
        if($pData){
            if($opt=='user_edit'){//编辑

            }else{//增加
                if( empty($pData['user_name']) )
                    ajaxReturn('请输入用户名',300);
                if(!$pData['uPass'])
                    ajaxReturn('请输入用户密码',300);
                if(!$pData['role_id']||$pData['role_id']==-1)
                    ajaxReturn('请选择权限组',300);

                //验证帐号
                $sql = "SELECT user_id FROM $AdminUserTable WHERE user_name='" . $pData['user_name'] . "'";
                if($this->db->get($sql))
                    ajaxReturn('用户名已经存在',300);

                $userData = array(
                    'user_name' => $pData['user_name'],
                    'user_pass' => md5(md5($pData['uPass'])),
                    'email' => $pData['email'],
                    'role_id' => $pData['role_id'],
                    'create_time' => time(),
                    'status' => 1,
                );
                if($this->db->save($AdminUserTable,$userData)){
                    unset($pData);
                    $this->setLog('增加系统用户{'.$pData['user_name'].'}成功！');
                    ajaxReturn('增加系统用户成功',200);
                } else {
                    $this->setLog('增加系统用户{'.$pData['user_name'].'}失败！');
                    ajaxReturn('增加系统用户失败',300);
                }
            }
        }

        $this->s->display('admin/user_add.html');
    }

    /** 系统权限组管理 */
    public function role_manage(){
        $pData=checkData($_POST);
        $gData=checkData($_GET);
        $AdminRoleTable = $this->OperateTable['AdminRoleTable'];

        $opt = !empty($pData['opt']) ? $pData['opt'] : $gData['opt'];
        if(!$opt){//权限列表回显
            $role_sql = "SELECT role_id,rolename,description,order_num FROM $AdminRoleTable ORDER BY order_num";
            $role_data = $this->db->find($role_sql);
            $this->s->assign('role_data',$role_data);
        }elseif($pData){
            if($opt=='role_add'){//增加
                if(!$pData['rolename'] || !$pData['description'])
                    ajaxReturn('权限组名称和描述不能为空',300);

                $sql = "INSERT INTO $AdminRoleTable (`rolename`,`description`,`order_num`) VALUES ('".$pData['rolename']."','".$pData['description']."','".$pData['listorder']."')";

                if($this->db->query($sql)){
                    $this->setLog('增加权限组{'.$pData['rolename'].'}成功！');
                    ajaxReturn('增加权限组{'.$pData['rolename'].'}成功',200);
                    $back_json = '{
                        "statusCode":"200",
                        "message":"增加权限组{'.$pData['rolename'].'}成功",
                        "navTabId":"page21"
                    }';
                    echo $back_json; exit;
                } else {
                    $this->setLog('增加权限组{'.$pData['rolename'].'}失败！');
                    ajaxReturn('增加权限组{'.$pData['rolename'].'}失败',300);
                }
            }elseif($opt=='role_edit'){//编辑
                $role_id = $pData['role_id'];
                $rolename = $pData['rolename'];
                $description = $pData['description'];
                $listorder =$pData['listorder'];

                if(!$role_id)
                    ajaxReturn('非法操作[缺少操作id]',300);
                if(!$rolename || !$description)
                    ajaxReturn('权限组名称和描述不能为空',300);

                $sql = "UPDATE $AdminRoleTable SET rolename='{$rolename}',description='{$description}',order_num=$listorder WHERE role_id=$role_id";
                if($this->db->query($sql)){
                    $this->setLog('修改权限组{'.$pData['rolename'].'}成功！');
                    ajaxReturn('修改权限组{'.$pData['rolename'].'}成功',200);
                } else {
                    $this->setLog('修改权限组{'.$pData['rolename'].'}失败！');
                    ajaxReturn('修改权限组{'.$pData['rolename'].'}失败',300);
                }
            }
        }else{//编辑回显
            $role_id = $gData['role_id'];
            $sql = "SELECT role_id,rolename,description,order_num FROM $AdminRoleTable WHERE role_id='{$role_id}'";
            $data = $this->db->get($sql);
            $this->s->assign('data',$data);
        }

        $this->s->display('admin/role_manage.html');
    }

    /** 系统权限组操作 */
    public function role_operate(){
        $gData=checkData($_GET);
        $pData=checkData($_POST);
        $AdminRoleTable = $this->OperateTable['AdminRoleTable'];

        $role_id = $gData['role_id'];
        if(!$role_id)
            ajaxReturn('非法操作[缺少操作id]',300);

        $opt = $gData['opt'];
        if(!$opt)
            ajaxReturn('非法操作[缺少操作参数]',300);

        /** 排序 */
        if( $opt=='role_order' ){
            $order_num = $pData['order_num'];
            $sql = "UPDATE $AdminRoleTable SET order_num=$order_num WHERE role_id=$role_id";
            if( $this->db->query($sql) ){
                $this->setLog('更新权限列表排序成功！');
                $back_json = '{
                    "statusCode":"200",
                    "message":"更新权限列表排序成功！",
                    "navTabId":"page21",
                    "type":"success"
                }';
                echo $back_json; exit;
            } else {
                $this->setLog('更新权限列表排序成功！');
                ajaxReturn('更新权限列表排序成功',300);
            }
        }

    }

    /**
     +--------------------------------------------------
     * 用户权限管理[用户单独权限&&权限组权限]
     * [权限组共有权限+用户单独权限指定]
     * [根据项目跟菜单id确定权限]
     +--------------------------------------------------
     */
    public function user_permission(){
        $gData=checkData($_GET);
        $pData=checkData($_POST);
        $AdminUserTable = $this->OperateTable['AdminUserTable'];
        $AdminRoleTable = $this->OperateTable['AdminRoleTable'];

        $post_arr['user_id'] = !empty($gData['user_id']) ? $gData['user_id'] : $pData['user_id'];
        $post_arr['role_id'] = !empty($gData['role_id']) ? $gData['role_id'] : $pData['role_id'];

        if($post_arr['user_id']>0){//用户单独权限
            if($post_arr['user_id']==1 || $post_arr['user_id']==$_SESSION['user_id']['user_id'])
                ajaxReturn('不能修改超级管理员或者自己的账号权限',300);

            if(!empty($pData['stage']) && $pData['stage']=='yes'){
                $values=array();
                $values['auth']=serialize($pData['mids']);
                $key='user_id';
                $values[$key]=$post_arr['user_id'];
                $tables=$AdminUserTable;

                if($this->db->update($tables,$values,$key)){
                    $this->setLog('更改用户{'.$post_arr['user_id'].'}权限成功');
                    ajaxReturn('更改用户权限成功',200);
                } else {
                    $this->setLog('更改用户{'.$post_arr['user_id'].'}权限失败');
                    ajaxReturn('更改用户权限失败|',300);
                }
            }else{//权限回显
                $sql = "SELECT user_id,role_id,auth FROM $AdminUserTable WHERE user_id=" . $post_arr['user_id'];
                $userData=$this->db->get($sql);
                if(!$userData)
                    ajaxReturn('用户不存在！',300);

                $role_id = $userData['role_id'];
                $user_auth=unserialize($userData['auth']);

                //权限回显
                $sql = "SELECT role_id,rolename,auth FROM $AdminRoleTable WHERE role_id=" . $role_id;
                $pmData=$this->db->get($sql);
                if(!$pmData)
                    ajaxReturn('权限组不存在！',300);
                $role_auth=unserialize($pmData['auth']);
            }

        }elseif($post_arr['role_id']>0){//权限组权限
            if($post_arr['role_id']==1)
                ajaxReturn('不能修改超级管理员组权限',300);

            if(!empty($pData['stage']) && $pData['stage']=='yes'){
                $values=array();
                $values['auth']=serialize($pData['mids']);
                $key='role_id';
                $values[$key]=$post_arr['role_id'];
                $tables=$AdminRoleTable;

                if($this->db->update($tables,$values,$key)){
                    $this->setLog('更改权限组{'.$post_arr['role_id'].'}权限成功');
                    ajaxReturn('更改权限组权限成功',200);
                } else {
                    $this->setLog('更改权限组{'.$post_arr['role_id'].'}权限失败');
                    ajaxReturn('更改权限组权限失败|',300);
                }
            }else{//权限回显
                $sql = "SELECT role_id,rolename,auth FROM $AdminRoleTable WHERE role_id=" . $post_arr['role_id'];
                $pmData=$this->db->get($sql);
                if(!$pmData)
                    ajaxReturn('权限组不存在！',300);
                $role_auth=unserialize($pmData['auth']);
            }
        }else{
            $user_auth=array();
            $role_auth=array();
        }

        //系统控制权限管理
        $menuData = $this->getMenuList(1,1);
        $post_arr['num_menu']=0;
        foreach($menuData as $pid => $var){
            //过滤只有项目选项列表
            if( empty($var['list']) || count($var['list'])<=0 ){
                unset($menuData[$pid]);
                continue;
            }
            //项目
            $post_arr['num_menu']++;
            if(in_array($var['id'],$role_auth) && $post_arr['user_id']>0)//用户单独权限[用户继承自权限组权限则不可直接在用户权限修改]
                $menuData[$pid]['acstate']=2;
            else if(in_array($var['id'],$user_auth) || in_array($var['id'],$role_auth))
                $menuData[$pid]['acstate']=1;
            else
                $menuData[$pid]['acstate']=0;

            foreach($var['list'] as $cid => $va){
                if( empty($va['list']) || count($va['list'])<=0){
                    unset($menuData[$pid]['list'][$cid]);
                    continue;
                }
                //栏目
                $post_arr['num_menu']++;
                if(in_array($va['id'],$role_auth) && $post_arr['user_id']>0)
                    $menuData[$pid]['list'][$cid]['acstate']=2;
                else if(in_array($va['id'],$user_auth) || in_array($va['id'],$role_auth))
                    $menuData[$pid]['list'][$cid]['acstate']=1;
                else
                    $menuData[$pid]['list'][$cid]['acstate']=0;

                //菜单
                foreach($va['list'] as $mid => $v){
                    $post_arr['num_menu']++;
                    if(in_array($v['id'],$role_auth) && $post_arr['user_id']>0)
                        $menuData[$pid]['list'][$cid]['list'][$mid]['acstate']=2;
                    else if(in_array($v['id'],$user_auth) || in_array($v['id'],$role_auth))
                        $menuData[$pid]['list'][$cid]['list'][$mid]['acstate']=1;
                    else
                        $menuData[$pid]['list'][$cid]['list'][$mid]['acstate']=0;
                }
            }
        }

        //权限管理
        $groupMenuData = $this->getMenuList(2,1);
        $post_arr['num_menu2']=0;
        foreach($groupMenuData as $pid => $var){
            //过滤只有项目选项列表
            if( empty($var['list']) || count($var['list'])<=0 ){
                unset($groupMenuData[$pid]);
                continue;
            }
            //项目
            $post_arr['num_menu2']++;
            if(in_array($var['id'],$role_auth) && $post_arr['user_id']>0)//用户单独权限[用户继承自权限组权限则不可直接在用户权限修改]
                $groupMenuData[$pid]['acstate']=2;
            else if(in_array($var['id'],$user_auth) || in_array($var['id'],$role_auth))
                $groupMenuData[$pid]['acstate']=1;
            else
                $groupMenuData[$pid]['acstate']=0;

            foreach($var['list'] as $cid => $va){
                if( empty($va['list']) || count($va['list'])<=0){
                    unset($groupMenuData[$pid]['list'][$cid]);
                    continue;
                }
                //栏目
                $post_arr['num_menu2']++;
                if(in_array($va['id'],$role_auth) && $post_arr['user_id']>0)
                    $groupMenuData[$pid]['list'][$cid]['acstate']=2;
                else if(in_array($va['id'],$user_auth) || in_array($va['id'],$role_auth))
                    $groupMenuData[$pid]['list'][$cid]['acstate']=1;
                else
                    $groupMenuData[$pid]['list'][$cid]['acstate']=0;

                //菜单
                foreach($va['list'] as $mid => $v){
                    $post_arr['num_menu2']++;
                    if(in_array($v['id'],$role_auth) && $post_arr['user_id']>0)
                        $groupMenuData[$pid]['list'][$cid]['list'][$mid]['acstate']=2;
                    else if(in_array($v['id'],$user_auth) || in_array($v['id'],$role_auth))
                        $groupMenuData[$pid]['list'][$cid]['list'][$mid]['acstate']=1;
                    else
                        $groupMenuData[$pid]['list'][$cid]['list'][$mid]['acstate']=0;
                }
            }
        }

        //数据统计权限管理
        $statisticsDataMenu = $this->getMenuList(3,1);
        $post_arr['num_menu3']=0;
        foreach($statisticsDataMenu as $pid => $var){
            //过滤只有项目选项列表
            if( empty($var['list']) || count($var['list'])<=0 ){
                unset($statisticsDataMenu[$pid]);
                continue;
            }
            //项目
            $post_arr['num_menu3']++;
            if(in_array($var['id'],$role_auth) && $post_arr['user_id']>0)//用户单独权限[用户继承自权限组权限则不可直接在用户权限修改]
            $statisticsDataMenu[$pid]['acstate']=2;
            else if(in_array($var['id'],$user_auth) || in_array($var['id'],$role_auth))
                $statisticsDataMenu[$pid]['acstate']=1;
            else
                $statisticsDataMenu[$pid]['acstate']=0;

            foreach($var['list'] as $cid => $va){
                if( empty($va['list']) || count($va['list'])<=0){
                    unset($groupMenuData[$pid]['list'][$cid]);
                    continue;
                }
                //栏目
                $post_arr['num_menu3']++;
                if(in_array($va['id'],$role_auth) && $post_arr['user_id']>0)
                    $statisticsDataMenu[$pid]['list'][$cid]['acstate']=2;
                else if(in_array($va['id'],$user_auth) || in_array($va['id'],$role_auth))
                    $statisticsDataMenu[$pid]['list'][$cid]['acstate']=1;
                else
                    $statisticsDataMenu[$pid]['list'][$cid]['acstate']=0;

                //菜单
                foreach($va['list'] as $mid => $v){
                    $post_arr['num_menu3']++;
                    if(in_array($v['id'],$role_auth) && $post_arr['user_id']>0)
                        $statisticsDataMenu[$pid]['list'][$cid]['list'][$mid]['acstate']=2;
                    else if(in_array($v['id'],$user_auth) || in_array($v['id'],$role_auth))
                        $statisticsDataMenu[$pid]['list'][$cid]['list'][$mid]['acstate']=1;
                    else
                        $statisticsDataMenu[$pid]['list'][$cid]['list'][$mid]['acstate']=0;
                }
            }
        }

        $this->s->assign('menuData',$menuData);
        $this->s->assign('groupMenuData',$groupMenuData);
        $this->s->assign('statisticsDataMenu',$statisticsDataMenu);
        $this->s->assign('post_arr',$post_arr);
        $this->s->display('admin/user_permission.html');
    }

    /** 修改帐号密码 */
    public function chang_user_pass(){
        $pData=checkData($_POST);
        $AdminUserTable = $this->OperateTable['AdminUserTable'];

        $user_name = $_SESSION['user_id']['user_name'];
        $user_id = $_SESSION['user_id']['user_id'];

        if( $user_name=='weedong91admin' || $user_id==1 )
            ajaxReturn('不能修改超级管理员帐号密码',300);

        $info_sql = "SELECT user_id,user_pass,user_name FROM $AdminUserTable WHERE user_id=$user_id LIMIT 1";
        $infoData = $this->db->get($info_sql);
        $uData['user_id'] = $infoData['user_id'];
        $uData['user_name'] = $infoData['user_name'];
        $uData['user_pass'] = $infoData['user_pass'];

        if($user_name!=$uData['user_name'] || $user_id!=$uData['user_id'])
            ajaxReturn('非法操作[！！！]',300);

        if($pData){
            /*$old_pass = MD5(MD5($pData['old_pass']));
            $user_pass = MD5(MD5($pData['user_pass']));
            $auth_pass = MD5(MD5($pData['auth_pass']));*/

            $old_pass = MD5($pData['old_pass']);
            $user_pass = MD5($pData['user_pass']);
            $auth_pass = MD5($pData['auth_pass']);

            if($user_pass!=$auth_pass)
                ajaxReturn('两次密码输入不同,请重新输入',300);
            if($old_pass!=$uData['user_pass'])
                ajaxReturn('您的旧密码错误,请重新输入',300);


            $sql = "UPDATE $AdminUserTable SET user_pass='{$user_pass}' WHERE user_id=" . $uData['user_id'] . " AND user_pass='" . $uData['user_pass'] . "' LIMIT 1";
            if($this->db->query($sql)){
                $this->setLog('修改用户密码{'.$user_name.'}成功！');
                $back_json = '{
                        "statusCode":"200",
                        "message":"修改用户密码{'.$user_name.'}成功！",
                        "callbackType":"closeCurrent"
                    }';
                echo $back_json; exit;
            }else{
                $this->setLog('修改用户密码{'.$user_name.'}失败！');
                ajaxReturn('修改用户密码{'.$user_name.'}失败！',300);
            }
        }

        $this->s->assign('uData',$uData);
        $this->s->display('admin/chang_user_pass.html');
    }
}