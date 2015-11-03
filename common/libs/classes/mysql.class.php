<?php
/**
 ++++++++++++++++++++++++++++++++++++
 * 公共 -> 数据库类文件
 ++++++++++++++++++++++++++++++++++++
 */
namespace common\libs\classes;

class Mysql{
    private $conn;

    public function __construct($n=0){
        include(BASE_PATH . 'config/config.php');
        $c=$db_config[$n];
        $port=isset($c['db_port'])?$c['db_port']:3306;
        $charset=isset($c['db_charset'])?$c['db_charset']:'utf8';
        $server = $c['db_host'] . ':' . $port;

        $this->conn = mysql_connect($server, $c['db_user'], $c['db_pass'], true) or die('connect db error');
        mysql_select_db($c['db_name'], $this->conn) or die('select db error');
        mysql_query("set names " . $charset, $this->conn);
    }

    /**
     * 执行 mysql_query 并返回其结果.
     */
    public function query($sql){
        $result = mysql_query($sql, $this->conn);
        $this->sql = $sql;
        return $result;
    }

    /**
     * 执行 SQL 语句, 返回结果的第一条记录(是一个对象).
     */
    public function get($sql){
        $result = $this->query($sql);
        if($row = mysql_fetch_assoc($result)){
            return $row;
        }else{
            return false;
        }
    }

    /**
     * 返回查询结果集, 以 key 为键组织成关联数组, 每一个元素是一个对象.
     * 如果 key 为空, 则将结果组织成普通的数组.
     */
    public function find($sql, $key=null){
        $data = array();
        $result = $this->query($sql);
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
            if(!empty($key)){
                $data[$row->{$key}] = $row;
            }else{
                $data[] = $row;
            }
        }
        return $data;
    }

    public function last_insert_id(){
        return mysql_insert_id($this->conn);
    }

    /**
     * 执行一条带有结果集计数的 count SQL 语句, 并返该计数.
     */
    public function count($sql){
        $result = $this->query($sql);
        if($row = mysql_fetch_array($result)){
            return (int)$row[0];
        }else{
            return 0;
        }
    }

    /**
     * 获取指定编号的记录.
     * @param int $id 要获取的记录的编号.
     * @param string $field 字段名, 默认为'id'.
     */
    public function load($table, $id, $field='id'){
        $sql = "SELECT * FROM {$table} WHERE `{$field}`='{$id}'";
        $row = $this->get($sql);
        return $row;
    }

    /**
     * 保存一条记录
     * @param object $row
     */
    public function save($table, &$row){
        $sqlA = '';
        foreach($row as $k=>$v){
            //$sqlA .= "`$k` = '".getSQL($v)."',";
            $sqlA .= "`$k` = '".$v."',";
        }
        $sqlA = substr($sqlA, 0, -1);
        $sql  = "INSERT INTO {$table} SET $sqlA";
        if($this->query($sql)){
            return $this->last_insert_id();
        }else{
            return false;
        }
    }

    /**
     * 更新$arr[id]所指定的记录.
     * @param array $row 要更新的记录, 键名为id的数组项的值指示了所要更新的记录.
     * @return int 影响的行数.
     * @param string $field 字段名, 默认为'id'.
     */
    public function update($table, &$row, $field='id'){
        $sqlF = '';
        foreach($row as $k=>$v){
            //$sqlF .= "`$k` = '$v',";
            //$sqlF .= "`$k` = '".getSQL($v)."',";
            $sqlF .= "`$k` = '".$v."',";
        }
        $sqlF = substr($sqlF, 0, -1);
        if(is_object($row)){
            $id = $row->{$field};
        }else{
            $id = $row[$field];
        }
        $sql  = "UPDATE {$table} SET $sqlF WHERE `{$field}`='$id'";
        return $this->query($sql);
    }

    /**
     * 删除一条记录.
     * @param int $id 要删除的记录编号.
     * @return int 影响的行数.
     * @param string $field 字段名, 默认为'id'.
     */
    public function remove($table, $id, $field='id'){
        $sql  = "DELETE FROM {$table} WHERE `{$field}`='{$id}'";
        return $this->query($sql);
    }

    //返回sql语句
    public function getSql(){
        return $this->sql;
    }

    /*开始一个事务.*/
    public function begin(){
        mysql_query('begin');
    }
    /* 提交一个事务.*/
    public function commit(){
        mysql_query('commit');
    }
    /*回滚一个事务.*/
    public function rollback(){
        mysql_query('rollback');
    }
    public function __destruct(){     //析构函数释放连接资源
        mysql_close($this->conn);
    }

    /*通用sql处理*/
    public function Q($sql){
        return  mysql_query($sql);
    }
}
?>