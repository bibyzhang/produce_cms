<?php
/**
 ++++++++++++++++++++++++++++++++++++
 * 缓存管理
 ++++++++++++++++++++++++++++++++++++
 */
namespace modules\common;

defined('_ACCESS_GRANT') or exit('没有访问权限!');

require_once(MODULE_PATH."admin/classes/admin.class.php");

class cache_admin extends \modules\admin\classes\admin{
    public function __construct(){
        parent::__construct();
    }

    /** 系统缓存设置 */
    public function system_options_cache(){
        if( $this->system_options(1,1) )
            ajaxReturn('缓存系统设置成功',200);
    }
}