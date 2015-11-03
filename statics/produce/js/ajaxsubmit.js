var reg_rule = {
    'require'    :    /.+/,
    'email'      :    /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/,
    'url'        :    /^http|https:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/,
    'currency'   :    /^\d+(\.\d+)?$/,
    'number'     :    /^\d+$/,
    'zip'        :    /^\d{6}$/,
    'integer'    :    /^[-\+]?\d+$/,
    'double'     :    /^[-\+]?\d+(\.\d+)?$/,
    'english'    :    /^[A-Za-z]+$/
};

function showTips(msg,type){
    if(!document.getElementById('ajax_tips')){
        var div = document.createElement("div");
        div.id = 'ajax_tips';
        div.className = 'ajax_tips';
        document.body.insertBefore(div,document.body.firstChild);
        var obj = $('#ajax_tips');
        obj.css({
            'font-size' : '14px',
            'font-weight' : 'bold',
            'padding' : '12px 28px',
            'position' : 'absolute',
            'z-index' : '9999'
        });
    }else{
        var obj = $('#ajax_tips');
    }
    if(type=='error'){
        obj.css({
            'border' : '2px solid #FF0',
            'background-color' : '#F00',
            'color' : '#FF0'
        });
    }else{
        obj.css({
            'border' : '1px solid #F00',
            'background-color' : '#FF0',
            'color' : '#666'
        });
    }
    obj.html(msg);
    var winHeight = $(window).height();
    var eleHeight = obj.outerHeight();
    var scrollHeight = $(window).scrollTop();
    var winWidth = $(window).width();
    var eleWidth = obj.outerWidth();
    var scrollWidth = $(window).scrollLeft();
    var center_left = (winWidth-eleWidth)/2+scrollWidth;
    var center_top = (winHeight-eleHeight)/2+scrollHeight;
    obj.css({top:center_top,left:center_left+'px',display:'block'});
    obj.fadeIn("fast");
    window.setTimeout(function(){obj.fadeOut("slow");},5000);
}

function ajaxFormTips(form,msg,type){
    var ajax_tips = $(form).find('.ajax_tips');
    if(ajax_tips[0]){ //表单自定义提示样式
        var type_class = type=='error' ? 'ajax_tips ajax_error' : 'ajax_tips ajax_success';
        $(ajax_tips).html(msg).fadeIn('fast').attr('class',type_class);
        setTimeout(function(){
            $(ajax_tips).fadeOut(300);
        },2000);
        return;
    }
    //默认提示样式
    if(document.getElementById('ajax_form_mark')==null){
        var html;
        html = '<div id="ajax_form_mark" style="position:absolute;background:#FFF;filter:alpha(opacity=40);-moz-opacity:0.4;-khtml-opacity: 0.4;opacity: 0.4;border:none;z-index:100001">';
        html += '<iframe scrolling="no" frameborder="0" marginheight="0" marginwidth="0" style="width:100%;height:100%;border:none;filter:alpha(opacity=0);-moz-opacity:0;-khtml-opacity: 0;opacity:0;">';
        html += '</iframe>';
        html += '</div>';
        html += '<div class="ajax_tips" id="ajax_form_tips" style="font-size:14px;font-weight:bold;padding:12px 28px;position:absolute;z-index:100002;"></div>';
        $('body').prepend(html);
    }
    var formOffset = $(form).offset();
    var formWidth = Math.max( $(form)[0].clientWidth , $(form)[0].offsetWidth ); //用jquery的width()获取有点问题？
    var formHeight = Math.max( $(form)[0].clientHeight, $(form)[0].offsetHeight );
    $('#ajax_form_mark').css({'top':formOffset.top,'left':formOffset.left}).width(formWidth).height(formHeight).fadeIn('fast');
    var obj = $('#ajax_form_tips');
    obj.html(msg)
    if(type=='error'){
        obj.css({
            'border' : '2px solid #FF0',
            'background-color' : '#F00',
            'color' : '#FF0'
        });
    }else{
        obj.css({
            'border' : '1px solid #F00',
            'background-color' : '#FF0',
            'color' : '#666'
        });
    }
    obj.css('display','block');//加上这句才能获得clientHeight/clientWidth
    var objWidth = Math.max( $(obj)[0].clientWidth , $(obj)[0].offsetWidth );
    var objHeight = Math.max( $(obj)[0].clientHeight, $(obj)[0].offsetHeight );
    obj.css('display','none');//为了下面的fadeIn
    obj.css({
        'top' : formOffset.top+((formHeight-objHeight)/2),
        'left' : formOffset.left+((formWidth-objWidth)/2)
    }).fadeIn('fast');
    setTimeout(function(){
        $('#ajax_form_tips').fadeOut(300);
        $('#ajax_form_mark').fadeOut(300);
    },2000);
}

function get_options(form){
    var options = {
        dataType : 'json',
        timeout : 20000,
        global : false,
        beforeSubmit : function (){
            ajaxFormTips(form,'请求提交中。。。');
            $(form).find('.ajax_btn').attr("disabled","true");
        },
        complete:function(res,status){
            $(form).find('.ajax_btn').removeAttr("disabled");
            if(status!='success'){
                ajaxFormTips(form,'请求失败 ！','error');
            }
        },
        success:function(res){
            if(res.type=='success'){
                typeof(success)=='function' ? success(res) : ajaxFormTips(form,res.msg,'success');
             }else if(res.type=='error'){
                typeof(error)=='function' ? error(res) : ajaxFormTips(form,res.msg,'error');
            }else if(res.type=='refresh'){
                ajaxFormTips(form,res.msg,'success');
                if(typeof(navTab)=='object'){
                    //navTab.reload();
                    navTabPageBreak(); //用这句保证分页
                }else{
                    window.location.href=window.location.href;
                }
            }else if(res.type=='refresh_code'){
                ajaxFormTips(form,res.msg,'error');
                $('#verify_img').click();
            }else if(res.type=='reset'){
                ajaxFormTips(form,res.msg,'success');
                $(form)[0].reset();
            }else{
                ajaxFormTips(form, res.msg, res.type);
            }
        }
    };
    return options;
}

function submitByAjax(form){
    if(arguments[0]){//如果传入了form，马上对form进行ajax提交
        var form = typeof(form)=='object' ? $(form) : $('#'+form);
        if(checkForm(form)==false) return false;
        var options = get_options(form);
        form.ajaxSubmit(options);
    }else{//否则，对标志有class="ajax_form"的表单进行ajax提交的绑定操作
        $('.ajax_form').live('submit',function(){
            var form = $(this);
            if(checkForm(form)==false) return false;
            var options = get_options(form);
            form.ajaxSubmit(options);
            return false; //<-- important!
        });
    }
}

function checkForm(form){
    var check = true;
    $(form).find('input|textarea|select|checkbox|radio[dataType]').each(function(){
        var val = $.trim( $(this).val() );
        var type = $(this).attr('dataType');
        var title = $(this).attr('title');
        if(!reg_rule[type].test(val)){
            ajaxFormTips(form,title,'error');
            $(this).focus();
            check = false;
            return false;
        }
    });
    return check;
}

/*  百度编辑器调用示例。注意：1、第一个script要加class="ueditor"；2、第一个script的ID名称要和第二个script实例出来的名称相同。
<script type="text/plain" id="ueditor" name="content" style="width:99%;" class="ueditor"></script>
<script type="text/javascript" defer="defer">
    var ueditor = new baidu.editor.ui.Editor();
    ueditor.render("ueditor");
</script>
*/
function save_ueditor(form){ //百度编辑器同步内容
    $(form).find('.ueditor').each(function(){
        obj = $(this).attr('id');
        window[obj].sync();
    });
    return validateCallback(form,navTabAjaxDone)
}

//select的onchange提交表单
function selectSubmit(obj,form,url){
    var okFun = function(){
        document.getElementById(form).action = url ? url : obj.value;
        //document.getElementById(form).submit();//提交表单
        submitByAjax(form);
        obj.options[0].selected=true;//重置select，为了能够获取此select的值，故把它放后面
    }
    var cancelFun = function(){
        obj.options[0].selected=true;
    }
    if(obj.options[obj.selectedIndex].title!=''){//如果某选项设置了title，那么弹出确认。
        if(typeof(alertMsg) != 'undefined'){
            alertMsg.confirm(obj.options[obj.selectedIndex].title,{
                okCall : okFun,
                cancelCall : cancelFun
            });
            return;
        }else{
            var check = confirm(obj.options[obj.selectedIndex].title);
            if(check==false){
                cancelFun();
                return;
            }
        }
    }
    okFun();
}

function myAjax(myurl,mytype,mydata){
    $.ajax({
        url: myurl,    //要提交到的地址
        type: mytype,   //提交的方式，GET或POST
        data: mydata,
        dataType: "json",     //这里是返回数据的方式，可以是xml，text,html格式
        timeout: 20000,     //超时时间
        global : false,
        beforeSend: function(){ // 提交之前
            showTips('请求提交中。。。');
        },
        error: function(){
            showTips('请求失败 ！');
         },
        success:function(res){
            showTips(res.msg,res.type);
            if(res.type=='refresh'){
                if(typeof(navTab)=='object'){
                    //navTab.reload();
                    navTabPageBreak(); //用这句保证分页
                }else{
                    window.location.href=window.location.href;
                }
            }
        }
    });
}

//a链接以ajax方式提交
function linkAjax(){
    $('a[target="_ajax"]').live('click',function(){
        var url = $(this).attr('href');
        var title = $(this).attr('confirm');
        var form = $(this).attr('form');
        if(form){ //如果定义了form属性，则是表单提交
            var ajaxFun = function(){
                var act = $('#'+form).attr('action');//表单原来的action地址
                $('#'+form).attr('action',url);//用a链接的URL作为表单的action地址
                submitByAjax(form);
                $('#'+form).attr('action',act);//表单提交后，还原action地址
            };
        }else{
            var ajaxFun = function(){ myAjax(url,'GET',null); };
        }
        if(title){
            if(typeof(alertMsg) != 'undefined'){
                alertMsg.confirm(title,{
                    okCall : ajaxFun
                });
            }else{
                var check = confirm(title);
                if(check == true) ajaxFun();
            }
        }else{
            ajaxFun();
        }
        return false;
    });
}

$(document).ready(function(){
    submitByAjax();
    linkAjax();
});