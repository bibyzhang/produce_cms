<?php
// +----------------------------------------------------------------------
// | @name AdminModel
// +----------------------------------------------------------------------
// | @desc 管理用户模型
// +----------------------------------------------------------------------
// | @author bibyzhang90@gmail.com
// +----------------------------------------------------------------------

namespace model;

require_once(MODEL_PATH."BaseModel.class.php");

class AdminModel extends BaseModel{

    /** 设置操作数据表 */
    private $OperateTable = array(
        'OptionsTable' => 'produce_admin'
    );

    /** 数据详情 */
    public function data_info(){
        $OptionsTable = $this->OperateTable['OptionsTable'];

        $sql = "SELECT option_id,option_name,option_value,autoload FROM $OptionsTable WHERE 1 AND autoload='yes'";
        $data = $this->db->find($sql);

        if( !empty($data) )
            return $data;
        else
            return false;
    }

    /** 数据更新 */
    public function data_update($data, $_field='id'){
        $OptionsTable = $this->OperateTable['OptionsTable'];

        if( !is_array($data) )
            return false;

        $status = $this->db->update($OptionsTable, $data, $_field);
        if( $status )
            return true;
        else
            return false;
    }

    /** 获取操作列表总条数 */
    public function data_total($_file='id',$where=1){
        $OptionsTable = $this->OperateTable['OptionsTable'];

        $sql = "SELECT count($_file) AS ct FROM " . $OptionsTable . " WHERE $where";
        $count = $this->db->get($sql);
        $totalCount = $count['ct'];
        return $totalCount;
    }

    /** 获取数据列表 */
    public function data_list($where=1,$skip=0,$numPerPage=10){
        $OptionsTable = $this->OperateTable['OptionsTable'];

        $sql = "SELECT `id`, `platform`, `game_id`, `game_name`, `file_url`, `qiniu_url`, `status` FROM $OptionsTable WHERE $where LIMIT $skip,$numPerPage";
        $data = $this->db->find($sql);

        if( !empty($data) )
            return $data;
        else
            return false;
    }
}