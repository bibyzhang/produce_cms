<?php
/**
 ++++++++++++++++++++++++++++++++++++
 *  -> 管理员操作
 ++++++++++++++++++++++++++++++++++++
 */
namespace modules\admin;

defined('_ACCESS_GRANT') or exit('没有访问权限!');

class admin_user extends \common\libs\classes\Base{
    public function __construct(){
        parent::__construct();
    }

    /** 设置操作数据表 */
    private $OperateTable = array(
    );

    /** 用户登录 */
    public function login(){
        $pData = checkData($_POST);
        $AdminUserTable = $this->OperateTable['AdminUserTable'];
        $AdminRoleTable = $this->OperateTable['AdminRoleTable'];

        $user_name = $pData['user_name'];
        $user_pass = $pData['user_pass'];
        $verify_code = $pData['verify_code'];

        //已经登录,跳转到管理首页
        $user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        if($user_id)
            toUrl('INDEX');

        if($user_name && !$user_pass)
            $this->s->assign('error','请同时输入账号、密码和动态密码');

        if($pData){
            if (MD5($verify_code) != $_SESSION['verify_code']) {
                $this->s->assign('error', '验证码错误!');
                $this->s->display('admin/login.html');
                exit();
            }
        }

        if($pData && $user_name && $user_pass){
            /*
            //获取密保key
            $sql = "SELECT mb_key FROM $AdminUserTable WHERE user_name='{$user_name}' AND status=1";
            $userData = $this->db->get($sql);
            $mb_key = $userData['mb_key'];

            $pass = MD5(MD5($mb_key . $user_pass));
            */
            $pass = MD5($user_pass);

            $sql = "SELECT user_id,user_name,role_id,auth FROM $AdminUserTable WHERE user_name='{$user_name}' AND user_pass='{$pass}' AND status=1";
            $data = $this->db->get($sql);

            if($data){
                $ip = $_SERVER['REMOTE_ADDR'];
                $ltime = time();
                $login_sql = "UPDATE $AdminUserTable SET lastlogin_ip='$ip',lastlogin_time=$ltime,login_times=login_times+1 WHERE user_name='" . $data['user_name'] . "'";
                $this->db->query($login_sql);

                $_SESSION['user_id'] = array(
                    'user_id' => $data['user_id'],
                    'user_name' => $data['user_name'],
                );

                //权限记录[菜单操作权限]
                if( $data['user_id']==1 ){//超级管理员,所有权限
                    $_SESSION['user_id']['actionid']='all';
                }else{
                    //权限组权限+用户单独权限
                    $auth_sql = "SELECT auth FROM $AdminRoleTable WHERE role_id=" . $data['role_id'];
                    $auth_data = $this->db->get($auth_sql);
                    $_SESSION['user_id']['actionid']=array_merge((array)unserialize($data['auth']),(array)unserialize($auth_data['auth']));
                }
                toUrl('INDEX');//跳转至首页
            }else{
                $this->s->assign('error',"账号或密码错误");
            }
        }

        $this->s->display('admin/login.html');
    }

    //用户退出
    public function logout(){
        $this->setLog('用户退出');
        session_destroy();
        unset($_SESSION);
        toUrl('LOGIN');
        exit;
    }

    //生成验证码
    public function verify(){
        \common\libs\classes\Image::buildAnimateVerify(4,1,48,24,'verify_code');
    }
}