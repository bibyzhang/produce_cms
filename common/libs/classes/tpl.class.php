<?php
class tpl{

    public $debug = false;          //是否开启调试模式
    public $tpl_dir = '';           //模板目录
    public $tpl_cache = '';         //模板编译缓存目录
    public $tpl_replace = array();  //需要替换的模板字符串
    private $tpl_content;           //模板内容
    private $tpl_var = array();     //模板变量
    
    private function parse_include(){
        $reg = '/<include file=[\"\'](.*?)[\"\']\s*\/>/is';
        preg_match_all($reg,$this->tpl_content,$matches);		
        if(!empty($matches[1])){
            foreach($matches[0] as $k=>$v){
                $tpl_file = $this->tpl_dir.$matches[1][$k];
                if(is_file($tpl_file)){
                    $replace = file_get_contents($tpl_file);
                    $this->tpl_content = str_replace($v,$replace,$this->tpl_content);
                    //检查被包含文件是否又有包含
                    preg_match($reg,$replace,$nested);
                    if(!empty($nested)){
                        $check_nested = true;
                    }
                }
            }
            if($check_nested){//被包含文件又有包含，则再解析一次，直到全部解析完毕
                $this->parse_include();
            }
        }
    }

    /** 解析开始标签 **/
    private function parse_tag_start($name){
        preg_match_all('/[{<]'.$name.'(\s.*?)[}>]/is',$this->tpl_content,$matches);
        if(!empty($matches[1])){
            foreach($matches[0] as $k=>$v){
                $params_arr = array(); //参数
                /*
                preg_match_all('/\s(\w+)=[\"\'](.*?)[\"\']/',$matches[1][$k],$params); //参数值不能有单引号
                preg_match_all('/\s(\w+)=[\"\'](\S*)[\"\']/',$matches[1][$k],$params); //参数值不能有空格
                if(!empty($params[1])){
                    $size = sizeof($params[1]);
                    for($i=0;$i<$size;$i++){
                        $params_arr[$params[1][$i]] = $params[2][$i];
                    }
                }
                // 参数值中有单引号，空格，以上有问题
                */
                preg_match_all('/\s(\w+)=\'(.*?)\'/',$matches[1][$k],$params1);
                preg_match_all('/\s(\w+)=\"(.*?)\"/',$matches[1][$k],$params2);
                if(!empty($params1[1])){
                    $size = sizeof($params1[1]);
                    for($i=0;$i<$size;$i++){
                        $params_arr[$params1[1][$i]] = $params1[2][$i];
                    }
                }
                if(!empty($params2[1])){
                    $size = sizeof($params2[1]);
                    for($i=0;$i<$size;$i++){
                        $params_arr[$params2[1][$i]] = $params2[2][$i];
                    }
                }
                $tag_start = new tag_start();
                $fun = '_'.$name;
                $search = $v;
                $replace = $tag_start->$fun($params_arr);
                $this->tpl_content = str_replace($search,$replace,$this->tpl_content);
            }
        }
    }

    /** 解析结束标签 **/
    private function parse_tag_end($name){
        preg_match_all('/[{<]\/'.$name.'[}>]/is',$this->tpl_content,$matches);
        if(!empty($matches[0])){
            foreach($matches[0] as $v){
                $tag_end = new tag_end();
                $fun = '_'.$name;
                $search = $v;
                $replace = $tag_end->$fun();
                $this->tpl_content = str_replace($search,$replace,$this->tpl_content);
            }
        }
    }

    private function parse_tag_sigle($name){
        preg_match_all('/[{<]'.$name.'\s*\/?[}>]/is',$this->tpl_content,$matches);
        if(!empty($matches[0])){
            foreach($matches[0] as $v){
                $tag_sigle = new tag_sigle();
                $fun = '_'.$name;
                $search = $v;
                $replace = $tag_sigle->$fun();
                $this->tpl_content = str_replace($search,$replace,$this->tpl_content);
            }
        }
    }

    private function parse_fun(){
        $this->tpl_content = preg_replace('/\{~(\w+)\((.*?)\)}/','<?php echo \\1(\\2); ?>',$this->tpl_content);
        $this->tpl_content = preg_replace('/\{:(\w+)\((.*?)\)}/','<?php \\1(\\2); ?>',$this->tpl_content);
    }

    /** 解析变量 **/
    private function parse_var(){
        $this->tpl_content = preg_replace('/\$(\w+)\.(\w+)\.(\w+)\.(\w+)/is','$\\1[\'\\2\'][\'\\3\'][\'\\4\']',$this->tpl_content);
        $this->tpl_content = preg_replace('/\$(\w+)\.(\w+)\.(\w+)/is','$\\1[\'\\2\'][\'\\3\']',$this->tpl_content);
        $this->tpl_content = preg_replace('/\$(\w+)\.(\w+)/is','$\\1[\'\\2\']',$this->tpl_content);
    }

    /** 解析输出变量 **/
    private function parse_echo_var(){
        $this->tpl_content = preg_replace('/\{\$(\w|\[|\]+)\.(\w+)\.(\w+)\.(\w+)\.(\w+)\}/is','<?php echo $\\1[\'\\2\'][\'\\3\'][\'\\4\'][\'\\5\'];?>',$this->tpl_content);
        $this->tpl_content = preg_replace('/\{\$(\w+)\.(\w+)\.(\w+)\.(\w+)\}/is','<?php echo $\\1[\'\\2\'][\'\\3\'][\'\\4\'];?>',$this->tpl_content);
        $this->tpl_content = preg_replace('/\{\$(\w+)\.(\w+)\.(\w+)\}/is','<?php echo $\\1[\'\\2\'][\'\\3\'];?>',$this->tpl_content);
        $this->tpl_content = preg_replace('/\{\$(\w+)\.(\w+)\}/is','<?php echo $\\1[\'\\2\'];?>',$this->tpl_content);
        $this->tpl_content = preg_replace('/\{\$(\w+\[*.*?\]*)\}/is','<?php echo $\\1;?>',$this->tpl_content);//{$name},{$name[$k]}
        $this->tpl_content = preg_replace('/\{__([A-Z_]+)__\}/is','<?php echo \\1;?>',$this->tpl_content);//增加对常量输出的支持：{__NAME__}
    }

    /** 编译模板 **/
    private function compile(){//注意解析顺序
        //解析模板包含
        $this->parse_include();
        //解析输出变量
        $this->parse_echo_var();//这个放最前
        //解析开始标签
        $tag_start = new tag_start();
        $fun = get_class_methods($tag_start);
        foreach($fun as $name){
            if(substr($name,0,1)!=='_') continue; //非下横线开头的不算
            $name = substr($name,1);
            $this->parse_tag_start($name);
        }
        //解析结束标签
        $tag_end = new tag_end();
        $fun = get_class_methods($tag_end);
        foreach($fun as $name){
            $name = substr($name,1);
            $this->parse_tag_end($name);
        }
        //解析单标签
        $tag_sigle = new tag_sigle();
        $fun = get_class_methods($tag_sigle);
        foreach($fun as $name){
            $name = substr($name,1);
            $this->parse_tag_sigle($name);
        }
        //解析函数
        $this->parse_fun();
        //解析变量
        $this->parse_var();//这个放最后
        //替换特定的字符串
        if($this->tpl_replace){
            foreach($this->tpl_replace as $v){
                $this->tpl_content = str_replace($v['search'],$v['replace'],$this->tpl_content);
            }
        }
    }

    /** 模板变量赋值 **/
    public function assign($name,$value){
        $this->tpl_var[$name] = $value;
    }

    /** 输出模板内容 **/
    public function display($file,$ext='.html'){
        if(!is_dir($this->tpl_cache)){
            if(!@mkdir($this->tpl_cache,0777)) $this->throw_error('模板编译缓存目录'.$this->tpl_cache.'不可写！');
        }
        $file = $file.substr(md5(ACTION_NAME),0,16); //保证编译模版的唯一性，不然多台服务器包含cache文件可能会包含到相同模版的其他文件
        extract($this->tpl_var,EXTR_OVERWRITE);
        $compile_file = $this->tpl_cache.'/'.str_replace('/','@',$file).'.php';
        $file = substr($file, 0,-16);
        $tpl_file = $this->tpl_dir.$file.$ext;
        if(!is_file($tpl_file)) $this->throw_error('模板文件'.$tpl_file.'不存在！');
        if( time() < filemtime($tpl_file) + 600 ){//模板文件修改时间在10分钟以内，不包含模板编译缓存
            $modify = true;
        }else{
            $modify = false;
        }
        if(is_file($compile_file) && !$this->debug && !$modify) return include($compile_file);
        $this->tpl_content = file_get_contents($tpl_file);
        $this->compile();
        /*file_put_contents($compile_file,"<?php if(!defined('APP_NAME')) exit();?>\r\n".$this->tpl_content);*/
        file_put_contents($compile_file,"<?php if(!defined('_ACCESS_GRANT')) exit('没有访问权限!');?>\r\n".$this->tpl_content);
        include($compile_file);
    }
	
	/*
	 * 输出模板内容,生成静态 
     * @param $file
	 * @param $toFile 生成静态路径
     * @param $ext 静态格式
	 */
    public function createHtml($file,$toFile,$ext='.html'){

        //var_dump($toFile);exit;

        //缓存目录
        if(!is_dir($this->tpl_cache)){
            if(!@mkdir($this->tpl_cache,0777)) $this->throw_error('模板编译缓存目录'.$this->tpl_cache.'不可写！');
        }
        //var_dump($_GET['a']);exit();
        $file = $file.substr(md5($_GET['a']),0,16); //保证编译模版的唯一性，不然多台服务器包含cache文件可能会包含到相同模版的其他文件
        //var_dump($file);exit();
        extract($this->tpl_var,EXTR_OVERWRITE);
        //$compile_file = $this->tpl_cache.'/'.str_replace('/','@',$file).'.php';
        $compile_file = $this->tpl_cache.str_replace('/','@',$file).'.php';
        //var_dump($compile_file);exit();
        $file = substr($file, 0,-16);
        $tpl_file = $this->tpl_dir.$file.$ext;
        //var_dump($tpl_file);exit();

        if(!is_file($tpl_file)) $this->throw_error('模板文件'.$tpl_file.'不存在！');
        if( time() < filemtime($tpl_file) + 600 ){//模板文件修改时间在10分钟以内，不包含模板编译缓存
            $modify = true;
        }else{
            $modify = false;
        }

        //var_dump($compile_file);exit();

        ob_start();//var_dump($compile_file);exit();

        //var_dump(file_get_contents($tpl_file));exit();
        //将缓存内容写入到缓存临时文件
        if(is_file($compile_file) && !$this->debug && !$modify){//echo 123123;exit();
            include($compile_file);
        }else{//echo 'xxxxx';
            $this->tpl_content = file_get_contents($tpl_file);
            //var_dump($this->tpl_content);
            $this->compile();
            //var_dump('fff');
            /*file_put_contents($compile_file,"<?php if(!defined('APP_NAME')) exit('');?>\r\n".$this->tpl_content);*/
            file_put_contents($compile_file,"<?php if(!defined('_ACCESS_GRANT')) exit('没有访问权限!');?>\r\n".$this->tpl_content);

            //var_dump('bbbbbb');
            //var_dump($compile_file);exit();
            include($compile_file);
            //require_once("/vagrant//git//caches/compile/html@mobile_game_infofd550970fab9350a.php");
            //var_dump('jjjjj');exit();
        }
        //var_dump('jjjjj');
        //var_dump($compile_file);exit();
        //var_dump(ob_end_flush() );
        //var_dump($toFile);exit;

        $dir = dirname($toFile);//echo $dir; exit();
        if(!is_dir($dir)){
            if(!@mkdir($dir,0777)) $this->throw_error('生成目录'.$dir.'不可写！');
        }        
        $ctx=ob_get_contents();# 获取缓存

        ob_end_clean();#清空缓存

        //var_dump($toFile);

        $strlen = file_put_contents($toFile,$ctx);
        return $strlen;
    }
	

    /*
     * 输出模板内容,生成静态 
     * @param $file 缓存路径
     * @param $toFile 生成静态路径
     * @param $ext 静态格式
     */
    public function createHtmlCrontab($file,$toFile,$ext='.html'){
        if(!is_dir($this->tpl_cache)){
            if(!@mkdir($this->tpl_cache,0777)) $this->throw_error('模板编译缓存目录'.$this->tpl_cache.'不可写！');
        }
        $file = $file.substr(md5(ACTION_NAME),0,16); //保证编译模版的唯一性，不然多台服务器包含cache文件可能会包含到相同模版的其他文件
        extract($this->tpl_var,EXTR_OVERWRITE);
        $compile_file = $this->tpl_cache.'/'.str_replace('/','@',$file).'.php';
        $file = substr($file, 0,-16);
        $tpl_file = $this->tpl_dir.$file.$ext;
        if(!is_file($tpl_file)) $this->throw_error('模板文件'.$tpl_file.'不存在！');
        if( time() < filemtime($tpl_file) + 600 ){//模板文件修改时间在10分钟以内，不包含模板编译缓存
            $modify = true;
        }else{
            $modify = false;
        }


        ob_start();

        if(is_file($compile_file) && !$this->debug && !$modify){
            include($compile_file);
        }else{
            $this->tpl_content = file_get_contents($tpl_file);
            $this->compile();
            /*file_put_contents($compile_file,"<?php if(!defined('APP_NAME')) exit();?>\r\n".$this->tpl_content);*/
            file_put_contents($compile_file,"<?php if(!defined('_ACCESS_GRANT')) exit('没有访问权限!');?>\r\n".$this->tpl_content);
            include($compile_file);
        }
        //var_dump('okkk');exit;
        $dir = dirname($toFile);
        if(!is_dir($dir)){
            if(!@mkdir($dir,0777)) $this->throw_error('生成目录'.$dir.'不可写！');
        }        
        $ctx=ob_get_contents();# 获取缓存

        ob_end_clean();#清空缓存
        $strlen = file_put_contents($toFile,$ctx);
        return $strlen;
    }


    private function throw_error($msg){
        header("Content-type: text/html; charset=utf-8");
        echo $msg;
        exit();
    }
}
////////////////////////////////////////////
class tag_start{
    public function _foreach($params=array()){
        $default = array('name'=>'$list','key'=>'key','id'=>'val');
        $params = array_merge($default,$params);
        $code = '<?php if(is_array([name])){foreach([name] as $[key]=>$[id]){?>';
        foreach($params as $k => $v){
            $code = str_replace('['.$k.']',$v,$code);
        }
        return $code;
    }
    public function _if($params=array()){
        return '<?php if('.$params['condition'].'){ ?>';
    }
    public function _notempty($params=array()){
        return '<?php if(!empty('.$params['name'].')){ ?>';
    }
    public function _url($params=array()){
        $arr = $this->parse_params($params,$has_var);
        if(!$has_var) return U($params); //没出现变量或常量，直接输出
        $str = implode(',',$arr);
        return '<?php echo U(array('.$str.')); ?>';
    }
    public function _model($params=array()){
        $return = isset($params['return']) ? $params['return'] : '$result';
        unset($params['return']);
        $arr = $this->parse_params($params);
        $str = implode(',',$arr);
        return '<?php '.$return.' = model(array('.$str.'));?>';
    }
    private function parse_params($params,&$has_var=false){
        if(empty($params)) return array();
        foreach($params as $k=>&$v){
            $v = preg_replace('/__([A-Z_]+)__/is','".\\1."',$v,-1,$count);// __NAME__ 这样表示常量
            if($count>0) $has_var = true;
            $v = preg_replace('/([^\\\]*?)(\$[\w\.]+)/is','\\1{\\2}',$v,-1,$count);// test_$var 变量存在，test_\$var 变量不存在
            if($count>0) $has_var = true;
            $v = preg_replace('/\{(\$[\w\.]+)\}\|(int|float)/is','".(\\2)\\1."',$v);//$_GET.id|int
            $tmp = array('gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=');
            foreach($tmp as $search=>$replace){ //无法使用尖括号 where="status gt 0"
                $v = str_replace(' '.$search.' ',$replace,$v);
            }
            $arr[] = '\''.$k.'\'=>"'.$v.'"';
        }
        return $arr;
    }
}
///////////////////////////////////////////
class tag_end{
    public function _foreach(){
        return '<?php } } ?>';
    }
    public function _if(){
        return '<?php } ?>';
    }
    public function _notempty(){
        return '<?php } ?>';
    }
}
///////////////////////////////////////////
class tag_sigle{
    public function _else(){
        return '<?php }else{ ?>';
    }
}
?>