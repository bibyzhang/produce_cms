<?php
// +----------------------------------------------------------------------
// |
// +----------------------------------------------------------------------
// | 图片处理
// +----------------------------------------------------------------------
namespace modules\server;

defined('_ACCESS_GRANT') or exit('没有访问权限!');

class img extends \common\libs\classes\Base
{
    private $gameInfoDetai = '';
    private $cdn_url = 'http://app.static..com';
    protected $img_operate;

    public function __construct(){
        parent::__construct();

        $this->img_operate = new \ThinkImage();
    }

    /**
     +--------------------------------------------------
     * 生成图片缩略图
     +--------------------------------------------------
     */
    public function img_thumb(){
        $gData = checkData($_GET);

        $w = $gData['w']+0;
        $h = $gData['h']+0;

        $allow_width = array(300,720);
        $allow_height = array(168,405);

        if(!in_array($w,$allow_width))
            die(json_encode(array('code' => -201, "msg" => "参数错误")));
        if(!in_array($h,$allow_height))
            die(json_encode(array('code' => -202, "msg" => "参数错误")));

        $img_path = substr(HTML_PATH, 0 , -1);

        ///images/videos/14999572630/screenshot/1d1c67cd5c1926a6e379fb78c711b87b_360X640.png
        $img_file = MD5(xxx);
        if( !file_exists($img_file) ){//生成图片

        }else{//读取图片

        }

//        $this->img_operate->open($img_path.$addData['pic_url']);
//        if ($this->img_operate->width() == '135' && $this->img_operate->height() == '135') {
//            $this->img_operate->save(HTML_PATH . '/images/videos/' . $_v['video_id'] . '/yingyongbao/', $imageName);
//        } else {
//            $this->img_operate->thumb(300, 300, 3)->save(HTML_PATH . '/images/videos/' . $_v['video_id'] . '/yingyongbao/', $imageName);
//            $this->img_operate->open(HTML_PATH . '/images/videos/' . $_v['video_id'] . '/yingyongbao/' . $imageName);
//            $this->img_operate->thumb(135, 135, 1)->save(HTML_PATH . '/images/videos/' . $_v['video_id'] . '/yingyongbao/', $imageName);
//        }



//
//        $this->img_operate->thumb(300, 300, 3)->save(HTML_PATH . '/images/videos/' . $_v['video_id'] . '/yingyongbao/', $imageName);
//        $this->img_operate->open(HTML_PATH . '/images/videos/' . $_v['video_id'] . '/yingyongbao/' . $imageName);

    }
}