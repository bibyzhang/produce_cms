<?php
// +----------------------------------------------------------------------
// | @name BaseModel
// +----------------------------------------------------------------------
// | @desc 基本模型,所有模型文件继承基本模型
// +----------------------------------------------------------------------
// | @author bibyzhang90@gmail.com
// +----------------------------------------------------------------------

namespace model;

defined('ACCESS_GRANT') or exit('没有访问权限!');

class BaseModel extends \common\libs\classes\Base{

    public function __construct() {
    }

    public function selectSample() {
        return 'Hello World!';
    }

    public function insertSample($arrInfo) {
        return true;
    }
}