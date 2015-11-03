//checkbox全选
function allSelect(){
    var name=arguments[0]?arguments[0]:'id\\[\\]'; // jquery换高版本后，[]这个特殊字符要这样写\\[\\]
    var parent=arguments[1]?arguments[1]:'';
    $(parent+' input[name='+name+']').attr('checked','true');
}
//checkbox全不选
function allUnSelect(){
   var name=arguments[0]?arguments[0]:'id\\[\\]';
   var parent=arguments[1]?arguments[1]:'';
   $(parent+' input[name='+name+']').removeAttr('checked');
}
//checkbox返选
function InverSelect(){
    var name=arguments[0]?arguments[0]:'id\\[\\]';
    var parent=arguments[1]?arguments[1]:'';
    $(parent+' input[name='+name+']').each(function(index){
        if($(this).attr("checked")){
            $(this).removeAttr('checked');
        }else{
            $(this).attr('checked','true');
        }
    });
}
//就地编辑
//id:要编辑的ID;field:要编辑的字段;myurl:提交地址;obj:要编辑的对象,一般为this;
function editNow(id,field,myurl,obj){
    var mythis = $(obj);
    var dataType = mythis.attr('dataType'); 
    mythis = mythis.find('div')[0] ? mythis.find('div') : mythis;
    if(mythis.hasClass('editing')){//有"正在编辑"标识,返回
        return false;
    }
    var input = $("<input type='text' class='editnow' />");//创建文本框
    //var oldText = mythis.html();//旧值
    var oldText=mythis.text();//上面那句会将&变成&amp;反正都不是编辑html的，就用这句吧。
    input.val(oldText);//设置文本框值
    inputWidth=mythis.width();//文本框长度等于编辑对象长度
    input.width(inputWidth);//设置文本框长度
    mythis.html('');//清空编辑对象
    input.appendTo(mythis).focus().select();//将文本框加到编辑对象
    mythis.width(inputWidth);//追加文本框后，编辑对象如td的长度可能会变大，重新设置一下。
    mythis.addClass('editing');//将编辑对象标识为"正在编辑",这样再次点击时,如果有此标识,就直接返回
    input.keypress( function(e){//避免有表单时回车提交表单。换成回车后提交本操作
        var key = window.event ? e.keyCode : e.which;
        if(key.toString() == "13"){
            input.blur();
            return false;
        }
    });
    input.blur(function(event){//文本框失去焦点后提交更新
        var newText = input.val();//得到新值
        mythis.html(newText);//将编辑对象设置为新值
        mythis.removeClass('editing');//取消"正在编辑"标识
        if(oldText!=newText){//只有新值跟旧值不一样时才提交服务器更新
            if(dataType){ //编辑对象设置dataType属性，进行数据合法性检验
                if(!reg_rule[dataType].test(newText)){
                    showTips('所填写数据不符合要求！','error');
                    mythis.html(oldText);
                    return false;
                }
            }
            $.ajax({
                url: myurl,
                type: "POST",
                data: 'id='+id+'&'+field+'='+encodeURIComponent(newText),//这里应该编码一下，否则如果有特殊字符如&会出现问题。注意不能用escape编码，否则中文会出问题。
                dataType: "json",
                timeout: 20000,
                global : false,
                beforeSend: function(){
                    showTips('请求提交中。。。');
                },
                error: function(res){
                    showTips('请求失败 ！');
                    mythis.html(oldText);//如果没有更新成功，编辑对象还原为旧值
                },
                success:function(response){
                    showTips(response.msg,response.type);
                    if(response.type=='error'){
                        mythis.html(oldText);//如果没有更新成功，编辑对象还原为旧值
                    }
                }
            });
        }
    });
}
//就地编辑（下拉框选择）
//id:要编辑的ID;field:要编辑的字段;myurl:提交地址;obj:要编辑的对象,一般为this;data:下拉框数据源（ID）
function selectEdit(id,field,myurl,obj,data){
    var mythis = $(obj);
    if(mythis.hasClass('editing')){//有"正在编辑"标识,返回
        return false;
    }
    var select = $('#'+data).clone();//创建下拉框
    //var oldText = mythis.html();//旧值
    var oldText=mythis.text();//上面那句会将&变成&amp;反正都不是编辑html的，就用这句吧。
    select.find('option[value=""]').remove();//移除值为空的选项
    //select.find('option[text='+oldText+']').attr("selected", true);  //设置下拉框选中
    select.children('option').each(function(){//二级下拉列表以上会有空格，故用以上方法不适用
        var val = $(this).text();
        val = $.trim(val);
        if(val==oldText) $(this).attr("selected", true);
    });
    selectWidth=mythis.width();//下拉框长度等于编辑对象长度
    select.width(selectWidth);//设置下拉框长度
    mythis.html('');//清空编辑对象
    select.appendTo(mythis).focus();//将文本框加到编辑对象
    mythis.width(selectWidth);//追加文本框后，编辑对象如td的长度可能会变大，重新设置一下。
    mythis.addClass('editing');//将编辑对象标识为"正在编辑",这样再次点击时,如果有此标识,就直接返回
    select.change(function(){//文本框失去焦点后提交更新
        var newValue = select.val();//得到新值（保存的值）
        var newText = select.find("option:selected").text();//得到新值（显示的值）
        newText = $.trim(newText); //二级下拉列表以上会有空格
        mythis.html(newText);//将编辑对象设置为新值
        mythis.removeClass('editing');//取消"正在编辑"标识
        if(oldText!=newText){//只有新值跟旧值不一样时才提交服务器更新
            $.ajax({
                url: myurl,
                type: "POST",
                data: 'id='+id+'&'+field+'='+encodeURIComponent(newValue),//这里应该编码一下，否则如果有特殊字符如&会出现问题。注意不能用escape编码，否则中文会出问题。
                dataType: "json",
                timeout: 20000,
                global : false,
                beforeSend: function(){
                    showTips('请求提交中。。。');
                },
                error: function(res){
                    showTips('请求失败 ！');
                    mythis.html(oldText);//如果没有更新成功，编辑对象还原为旧值
                },
                success:function(response){
                    showTips(response.msg,response.type);
                    if(response.type=='error'){
                        mythis.html(oldText);//如果没有更新成功，编辑对象还原为旧值
                    }
                }
            });
        }
    });
    select.blur(function(){
        mythis.removeClass('editing');//取消"正在编辑"标识
        mythis.html(oldText);
    });
}