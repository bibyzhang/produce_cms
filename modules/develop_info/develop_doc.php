<?php
/**
 ++++++++++++++++++++++++++++++++++++
 *  -> 开发规范管理
 ++++++++++++++++++++++++++++++++++++
 */
namespace modules\develop_info;

include_once(BASE_PATH.'Michelf/MarkdownExtra.inc.php');
use \Michelf\MarkdownExtra;

defined('_ACCESS_GRANT') or exit('没有访问权限!');

class develop_doc extends \common\libs\classes\Base{
    public function __construct(){
        parent::__construct();
    }

    /** markdown格式接口 */
    public function develop_public(){
        $gData = checkData($_GET);
        $int_opt = $gData['int_opt'];

        if(!$int_opt)
            ajaxReturn('非法操作[缺少必须参数]',300);

        //样式
        echo '<style>
            .markdown-here-wrapper h1{ font-size: 20px; font-weight:bold; margin-top: 10px;}
            .markdown-here-wrapper h2{ font-size: 18px; font-weight:bold; margin-top: 10px;}
            .markdown-here-wrapper h3{ font-size: 16px; font-weight:bold; margin-top: 10px;}
            .markdown-here-wrapper table{ border-collapse: collapse; border: 1px solid yellowgreen;}
            .markdown-here-wrapper th { vertical-align: baseline; border: 1px solid yellowgreen; font-weight:bold; font-size: 18px;}
            .markdown-here-wrapper td { vertical-align: middle; border: 1px solid yellowgreen; font-size: 18px;}
            .markdown-here-wrapper tr { border: 1px solid yellowgreen;}

            .markdown-here-wrapper p a{font-size: 16px;}
            </style>';

        $output = $text = file_get_contents(MODULE_PATH.'develop_info/'.$int_opt.'.md');
        $parser = new MarkdownExtra;
        $my_html = $parser->transform($output);

        $this->s->assign('my_html',$my_html);
        $this->s->display('interface_admin/interface_list.html');
    }

}