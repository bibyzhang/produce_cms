/* 下拉框获取二级联动内容列表
	field: 查询条件
	opt: 异步操作函数
	fid: 赋值对象id
	调用例子： get_select_data('id=1','get_menu_c','gid');
*/
function get_select_data(field,opt,fid){
	 $.ajax({
		type:"POST",
		url:"/?action=ajax&opt="+opt,
		dataType:"json",
		data:field+"&t="+Math.random(),
		success:function(msg)
		{
			if(msg.status==200){
				$('#'+fid).html(msg.message);
			} else {
				alert('error');
			}
		},
		complete:function(XMLHttpRequest,textStatus){},
		error:function(){}
	});
}

/*  下拉框 内容搜索筛选
	data: 下拉内容数组
	tid:  搜索框对象id
	fid:  下拉框对象id
	调用例子： search_select_data(get_search_array('pid'),'pkey','pid');
*/
function get_search_array(fid){
	var select_data={};
	$("select[id="+fid+"] option").each(function(){
		select_data[$(this).val()]=$(this).text();
	})
	return select_data;
}
function search_select_data(data,tid,fid){
	$('#'+tid).keyup(function(){
		var phtml='';
		var keyw=$(this).val();
		if(keyw!=''){
			$.each(data,function(i,n){
				if(n.indexOf(keyw) != -1){
					phtml+='<option value="'+i+'">'+n+'</option>';
				}
			})
		} else {
			$.each(data,function(i,n){
					phtml+='<option value="'+i+'">'+n+'</option>';
			})
			$(this).val('关键字搜索');
		}
		$('#'+fid).html(phtml);
	});
}

/**
 * uploadify插件上传成功后，回传文件路径地址，以便处理
 */
function uploadifyFileUrl(file, data, response){
	var obj = eval('(' + data + ')');
	$("#uploadifyFileUrl").val(obj.url);
	$("#uploadifyFileUrlToken").val(obj.token);
	alert(data);
}

(function($) {
	var allSelectBox = [];

    var allKillFilter = function(bid){
        $.each(allSelectBox, function(i){ 
            if ($("#" + allSelectBox[i])[0]==bid) {
                $("#" + allSelectBox[i]).remove();
            }
        });
    };
	
	$.extend($.fn, {
		sfilter : function(flag){

            /* 清理下拉层 */
            var _selectBox = [];

			$.each(allSelectBox, function(i){ 
				/*if (allSelectBox[i] == bid) {
					$("#" + allSelectBox[i]).remove();
				}*/
			});
			
            allSelectBox = _selectBox;
            
			return this.each(function(i){
				var _select			= $(this);								//下拉菜单
				var s_width			= Math.round($(this).outerWidth());	//得到下拉菜单宽度
				var s_height		= Math.round($(this).outerHeight());
				var _select_size	= 0;								//用于下拉菜单选项数计数之用
				var _select_value	= new Array();						//存储下拉菜单的值的数组
				var _select_text	= new Array();						//存储下拉菜单的文本的数组
				var name_round		= Math.round(Math.random()*10000);	//防止变量冲突，在新建元素ID上加随机数
				var now_select_li;										//用于存储当前选中的LI选项的jquery变量
				var mouse_on_list	= 0;
				var mouse_on_text	= 0;
                var id = $(this).attr("id");
				var ref = $(this).attr("ref");
				var refUrl = $(this).attr("refUrl") || "";
				var refFrom = $(this).attr("refFrom")||"";

                var name_round = $(this).attr("id") || Math.round(Math.random()*10000000);
                var html_first = "<div class='cls_sfilter' id='sfilter_div"+name_round+"'>"+
                                "<input type='text' style='width:"+(s_width)+"px;' id='sfilter_text"+name_round+"' class='cls_sfilter_text' />"+
                                "<ul class='cls_sfilter_list' id='sfilter_list"+name_round+"'></ul>"+
                            "</div>";
				$(this).before(html_first);
				var sfilter_div			=	$("#sfilter_div"+name_round);
				var sfilter_text		=	$("#sfilter_text"+name_round);
				var sfilter_list		=	$('#sfilter_list'+name_round);
				var sfilter_text_offset;
				var sfilter_text_height;
				var sfilter_text_left;
				var sfilter_text_top;
				var sfilter_list_top;
				
				allSelectBox.push('sfilter_div'+name_round);
		
				sfilter_div.height(sfilter_text_height);
		
		
				//将选项缓存起来
				_select.children("option").each(function(){
					_select_size++;
					_select_value[_select_size]	= this.value;
					_select_text[_select_size]	= this.text;
				});
				//alert(_select_size);
		
				var sfilter_text_value	=	sfilter_text.val();
		
		
				//下拉菜单的事件绑定
				_select.bind("focus", function(){
                    start_select();
                });
		
				//文本框的事件绑定
				sfilter_text
					.blur(function(){
						if(mouse_on_list==0)
						{
							end_select();
						}
					})
					.mouseover(function(){
						mouse_on_text=1;
					})
					.mouseout(function(){
						mouse_on_text=0;
					})
					.keyup(function(){
						sfilter_text_new_value	=	sfilter_text.val();
						if(sfilter_text_new_value!=sfilter_text_value)
						{
							sfilter_text_value=sfilter_text_new_value;
							if(now_select_li!=undefined) {
								now_select_li=undefined;
							}
							filter_in();
						}
					})
					.keydown(function(event){
						var keycode = event.which;
						if(keycode == 38){ //up
							if(now_select_li!=undefined) {
								now_select_li.prev().mouseover();
							}else{
								sfilter_list.children("li:first").mouseover();
							}
		
						}else if(keycode == 40){//down
							if(now_select_li!=undefined) {
								now_select_li.next().mouseover();
							}else{
								sfilter_list.children("li:first").mouseover();
							}
						}else if(keycode == 13 || keycode==32){//enter/space
							if(now_select_li!=undefined) {
								to_select(now_select_li.attr('title'));
                                onchange();
								end_select();
							}
						}
		
		
						var sfilter_list_scrollTop = sfilter_list.scrollTop();
						if(now_select_li!=undefined){
							var now_select_li_top =now_select_li.position().top;
						}else{
							var now_select_li_top=0;
						}
						if(now_select_li_top < 0){
							sfilter_list.scrollTop(Math.round(sfilter_list_scrollTop+now_select_li_top));
						}else if(now_select_li_top>280){
							sfilter_list.scrollTop(Math.round(sfilter_list_scrollTop+now_select_li_top-280));
						}
						if(keycode ==13 || keycode==32){
							return false;
						}
					})
					;
		
		
				//列表li的事件绑定
				sfilter_list
					.mouseover(function(){
						mouse_on_list=1;
					})
					.mouseout(function(){
						mouse_on_list=0;
					});
		
				$(document).click(function(){
					end_select();
				});
				
		
				//开始选择
				function start_select(){
					_select.hide();
					sfilter_div.css({
						"display"	:	"inline"
					});
		
					sfilter_text_new_value	=	sfilter_text.val();

					if((sfilter_text_new_value!=sfilter_text_value) || (sfilter_list.children().size()==0))
					{
						filter_in();
					}
					sfilter_text.focus();
				}
				//结束选择
				function end_select(){
					sfilter_div.hide();
					_select.show();
				}
		
				//将select中的选项载入到ul中的li列表中
				function filter_in(){
					sfilter_list.empty();
		
					sfilter_text_offset	=	_select.offset();
					sfilter_text_height	=	Math.round(sfilter_text.outerHeight());
					sfilter_text_left	=	Math.round(sfilter_text_offset.left);
					sfilter_text_top	=	Math.round(sfilter_text_offset.top);
					sfilter_list_top	=	sfilter_text_top+sfilter_text_height;
		
					sfilter_text.css({
						"height"	:	(s_height-2)+"px"
					});
					
					
					sfilter_list.css({
						"width"		:	(s_width)+"px",
						"left"		:	0,
						"top"		:	(sfilter_text_height-1)+'px'
					});
		
					for(i=1;i<=_select_size;i++)
					{
						if((sfilter_text_value.length!=0) && (_select_text[i].toUpperCase().indexOf(sfilter_text_value.toUpperCase())==-1)){
							continue;
						} else {
							sfilter_list.append("<li title='"+_select_value[i]+"'>"+_select_text[i]+"</li>");
						}
					}
		
					sfilter_list.children("li")
						.mouseover(function(){
							if(now_select_li!=undefined) {
								now_select_li.removeClass("select_class");
							}
		
							$(this).addClass("select_class");
							now_select_li=$(this);
		
		
						})
						.click(function(){
							to_select(this.title);
							if(id=='dns_add_platform_id'){
                    			$('#dtest').html('.'+$(this).text());
							}
							onchange();
							end_select();
						});
		
				}
		
				//选定某个值
				function to_select(svalue){
					_select.val(svalue);
				}

				function onchange(){
                   if (ref && refUrl) {
    					var $ref = $("#"+ref);
						if(refFrom){//用于服务器联动
							var $refFrom = $("#"+refFrom);
							refUrl_new = refUrl.replace("{plat_value}",encodeURIComponent($refFrom.val()));
						}else{
							refUrl_new = refUrl;
						}
						if ($ref.size() == 0) return false;
						$.ajax({
							type:'POST', dataType:"json", url:refUrl_new.replace("{value}", encodeURIComponent(_select.attr("value"))), cache: false,
							data:{},
							success: function(json){
								if (!json) return;
								var html = '';
								$.each(json, function(i){
									if (json[i] && json[i].length > 1){
										html += '<option value="'+json[i][0]+'">' + json[i][1] + '</option>';
									}
								});
								$ref.prev().remove();
								$ref.html(html);
								$ref.sfilter(true);
							},
							error: DWZ.ajaxError
						});
					}
                  // $this.unbind("change", onchange).bind("change", onchange);
				};
                if (ref && refUrl && flag) {
                    onchange();
                }
			});
		}
	});
})(jQuery);


//报表最大化
function maxtodialog(tag){
	var title='数据报表全屏展示';
	if($('#'+tag).closest("div").find(".tabs").length>0){
		var ct=$('#'+tag).closest("div").find(".tabs").html();
	}
	if($('#'+tag).closest("div").find(".pageContent").length>0){
		var ct=$('#'+tag).closest("div").find(".pageContent").html();
	}
	if(!ct){
		return false;
	}
	ct=ct.replace(/layouth="[^"]*"/g,'layouth="20"');
	$("body").append(DWZ.frag["dialogFrag"]);
	dialog = $(">.dialog:last-child", "body");
	dialog.find(".dialogHeader").find("h1").html(title);
	$("a.close", dialog).click(function(event){ 
		$.pdialog.close(dialog);
		return false;
	});
	
	var jDContent = $(".dialogContent",dialog);
	jDContent.html(ct);
	jDContent.find("[layoutH]").layoutH(jDContent);
	jDContent.width($(dialog).width()-14);
	$.pdialog.maxsize(dialog);
	dialog.jresize("destroy").dialogDrag("destroy");
	$("a.minimize", dialog).hide();
	$("a.restore", dialog).hide();
}
