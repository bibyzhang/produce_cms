<?php

namespace model;

require_once(MODEL_PATH."BaseModel.class.php");

class OptionsModel extends BaseModel{

    /** 设置操作数据表 */
    private $OperateTable = array(
        'OptionsTable' => 'app_options'
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
    public function update_data($data, $_field='id'){
        $OptionsTable = $this->OperateTable['OptionsTable'];

        if( !is_array($data) )
            return false;

        $status = $this->db->update($OptionsTable, $data, $_field);
        if( $status )
            return true;
        else
            return false;
    }


    /** 添加数据 */
    public function add_data($data){
        $OptionsTable = $this->OperateTable['OptionsTable'];

        if( !is_array($data) )
            return false;

        $sql = "SELECT id,game_id FROM $XxAidFileUrlTable WHERE game_name='" . $data['game_name'] . "' AND platform=". $data['platform'] ." LIMIT 1";
        $ret = $this->db->get($sql);
        if( empty($ret) ){
            $status = $this->db->save($XxAidFileUrlTable,$data);
            if( $status )
                return true;
            else
                return false;
        }else{
            if($ret['game_id']==0){//该游戏之前不存在 尝试更新
                $updateData['id'] = $ret['id'];
                $updateData['game_id'] = $data['game_id'];
                $status = $this->db->update($XxAidFileUrlTable, $updateData, 'id');
            }

            return true;
        }
    }

    /** 获取操作列表总条数 */
    public function get_list_total($_file='id',$where=1){
        $XxAidFileUrlTable = $this->OperateTable['XxAidFileUrlTable'];

        $sql = "SELECT count($_file) AS ct FROM " . $XxAidFileUrlTable . " WHERE $where";
        $count = $this->db->get($sql);
        $totalCount = $count['ct'];//数据总条数
        return $totalCount;
    }

    /** 获取数据列表 */
    public function get_list($where=1,$skip=0,$numPerPage=10){
        $XxAidFileUrlTable = $this->OperateTable['XxAidFileUrlTable'];

        $sql = "SELECT `id`, `platform`, `game_id`, `game_name`, `file_url`, `qiniu_url`, `status` FROM $XxAidFileUrlTable WHERE $where LIMIT $skip,$numPerPage";
        $data = $this->db->find($sql);

        if( !empty($data) )
            return $data;
        else
            return false;
    }
}