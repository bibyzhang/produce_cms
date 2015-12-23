<?php
// +----------------------------------------------------------------------
// | @file Admin.php
// +----------------------------------------------------------------------
// | @desc 系统管理入口
// +----------------------------------------------------------------------
// | @author bibyzhang90@gmail.com
// +----------------------------------------------------------------------

namespace app\modules\apk;

defined('ACCESS_GRANT') or exit('Forbidden!');

class apk extends \common\libs\classes\Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /** 管理首页 */
    public function index()
    {
        echo 'index';
    }
}