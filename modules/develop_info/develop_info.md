开发规范
============

命名统一
-----------
*平台:platform,游戏id为:game_id

其他
-----------

*图片,游戏包等数据文件上传地址格式统一为:根目录/images/栏目(如:游戏、文章)/年/月/日/年月日随机数(13位)尺寸(长X宽).后缀{例:http://data.915.com/images/game/2014/11/28/201411281035181095441966_116X116.png}

*3.公用模板在 /template/common 下,可以用类似格式 &lt;{include file="common/input_action.html" input_decs='礼包原价积分' input_name='originalprice' size='9' custom='class="number"'}&gt;&lt;span style="color: #7a3b3f;"&gt;数字格式&lt;/span&gt; 调用,默认列表数组数项使用$gbdata,表单添加编辑数组项使用$post_arr做保存管理

*4.富文本框编辑器可用如下格式调用:&lt;{include file="common/online_editor.html" text_name='tipinfo' text_id="tipinfo_&lt;{$smarty.get.a}&gt;_&lt;{$commontmp}&gt;" text_desc='更新内容' width='700' height='100' text_tool='Cut,Copy,Paste,Pastetext,|,Blocktag,Fontface,FontSize,Bold,Italic,Underline,Strikethrough,FontColor,BackColor,SelectAll,Removeformat|,Align,List,Outdent,Indent|,Link,Unlink,Anchor,Img,Hr,Emot,Table|,Source,Preview,Print,Fullscreen'}

---------------------------------------

图片上传
-----------

普通图片上传调用格式示例:&lt;{include file="common/uploadify_file.html" upload_desc='广告图片' upload_button="upload_index_button_&lt;{$smarty.get.a}&gt;_&lt;{$commontmp}&gt;" upload_img_preview="upload_index_img_preview_&lt;{$smarty.get.a}>_&lt;{$commontmp}&gt;" upload_img_bac="upload_index_img_bac_&lt;{$smarty.get.a}&gt;_&lt;{$commontmp}&gt;" upload_text_name='iconurl_index' upload_text_id="upload_index_text_id_&lt;{$smarty.get.a}&gt;_&lt;{$commontmp}&gt;" img_type='indeximgurl' img_category='game' upload_img_size='图片尺寸:276[长]*144[宽]'}&gt;

*同时头部添加common定义:&lt;{if !empty($smarty.get.opt)}&gt;&lt;{assign var='commontmp' value=$smarty.get.opt}&gt;&lt;{else}&gt;&lt;{assign var='commontmp' value='common'}&gt;&lt;{/if}&gt;

*注意:[1:编辑返回变量命名默认为:$gbdata][2:调用方法要加上如下两句,指定读取session与上传路径,$this->s->assign('user_auth',session_id());$this->s->assign('IMAGE_PATH',IMAGE_PATH);]

*普通图片上传调用参数说明:

*upload_desc : 上传提示

*img_type='indeximgurl' : 上传图片类型,在 data.meitu.forgame.com/caches/system.php 文件配置尺寸限制

*img_category='game' : 上传路径根据该分类生成

*upload_img_size : 图片上传说明

*upload_button : 图片上传按钮id

*upload_img_preview : 图片上传预览id

*upload_img_bac : 图片上传回显id

*upload_text_name : 图片上传文本[图片地址]名称

*upload_text_id : 图片上传文本id

---------------------------------------