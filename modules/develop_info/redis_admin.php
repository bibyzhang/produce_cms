<?php
// +----------------------------------------------------------------------
// |
// +----------------------------------------------------------------------
// | Redis监控
// +----------------------------------------------------------------------
namespace modules\develop_info;

defined('_ACCESS_GRANT') or exit('没有访问权限!');

class redis_admin extends \common\libs\classes\Base{
    public function __construct(){
        parent::__construct();
    }

    /** Redis集合列表 */
    public function redis_set_list(){
        $allKeys = $this->redis->keys('*');

        $this->s->assign('allKeys',$allKeys);
        $this->s->display('interface_admin/redis_set_list.html');
    }

}