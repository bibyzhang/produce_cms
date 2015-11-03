<?php
// +----------------------------------------------------------------------
// |
// +----------------------------------------------------------------------
// | 基本模型
// +----------------------------------------------------------------------

namespace model;

defined('_ACCESS_GRANT') or exit('没有访问权限!');

class BaseModel extends \common\libs\classes\Base{

    public function __construct(){
        parent::__construct();
    }
}