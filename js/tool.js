/*!
 * FileName   : tool.js
 * WebSite    : http://app..com
 * Desc       :
 * Author     : zwj
 * version    : 2.1.1
 * */

var TOOL = {
	// 切换器（jquery）
	switchable: function( selector, options ) {
		jQuery(selector).Switchable( options );
	},

	// Tab（jquery）
	tab: function( selector, eventType ) {
		var navLi = jQuery(selector).find(".J_nav > li"),
			contLi = jQuery(selector).find(".J_content > li");
		var classType = eventType == "hover" ? "hover" : "selected";

		if ( classType == "hover" ) {
			var timer;
			navLi.hover(function(){
				var i = navLi.index(this);
				timer = setTimeout(function(){navEvent(i, "hover");contEvent(i);}, 300);
			}, function() {
				if (timer) {
					clearTimeout(timer);
				}
			});
		} else {
			navLi.hover(function(){
				var i = navLi.index(this);
				navEvent(i, "hover");
			}, function(){
				jQuery(this).removeClass("hover");
			}).click(function(){
				var i = navLi.index(this);
				navEvent(i, "selected");
				contEvent(i);
			});
		}

		function navEvent(i, type) {
			navLi.eq(i).addClass(type).siblings().removeClass(type);
		}
		function contEvent(i) {
			contLi.eq(i).show().siblings().hide();
		}
	},

	// 导航树（jquery）
	navtree: function( showAll ) {
		if (jQuery(".sitenav").length) {
			// ie
			if (!-[1, ]) {
				jQuery(".sitenav ul").each(function(){
					jQuery(this).children("li:last-child").addClass("last-child");
				});
			}

			jQuery(".sitenav>ul>li").each(function(){
				var ul = jQuery(this).find("ul:first");
				if (!ul.length) return;

				if ( showAll ) {
					ul.attr("class", "show");
				}

				var span = jQuery("<span></span>");
				span.prependTo(ul.siblings("b"));
				span.height = ul.height();
				span.status = "visible";

				if (ul.attr("class") !== "show") {
					ul.css("height", "0");
					span.status = "hidden";
				}

				span.click(function(){
					if (span.status === "hidden") {
						span.status = "visible";
						ul.animate({height: span.height}, 500);
					}
					else {
						span.status = "hidden";
						ul.animate({height: 0}, 500);
					}
				});
			});
		}
	},

	// Tab默认日期（jquery）
	calendar: function( selector ) {
		var day = new Date().getDay(),
			dayNum = day == 0 ? 6 : day - 1;

		jQuery(selector).find(".J_nav > li").eq(dayNum).addClass("selected");
		jQuery(selector).find(".J_content > li").eq(dayNum).removeClass("hide");
	},

	// 弹框
	popupbox: function( id, options ) {
		var box = document.getElementById(id);
		if (!box) return;

		var _default = {
			existMask: true
		};
		var opts = jQuery.extend( {}, _default, options );

		// 创建遮罩，显示弹出框
		this.open = function(maskCss, boxCss) {
			this.box.style.cssText = boxCss;
			this.mask.style.cssText = maskCss;

			// 解决ie6 bug
			if(!window.XMLHttpRequest) {
				document.documentElement.scrollTop++;
				document.documentElement.scrollTop--;
			}

			if ( opts.existMask ) document.body.appendChild(this.mask);
		},

		// 关闭遮罩
		this.close = function() {
			document.getElementsByTagName("html")[0].style.backgroundImage = "";

			// ie6 清空css表达式
			this.box.style.cssText = "";
			this.box.style.display = "none";

			if ( opts.existMask ) document.body.removeChild(this.mask);
		}

		this.box = box;
		this.mask = document.createElement("div");

		// dom宽高
		this.box.style.display = "block";
		var boxWidth = this.box.clientWidth,
			boxHeight = this.box.clientHeight;

		// 创建遮罩，显示弹出框
		var maskCss = "position:fixed;left:0;top:0;z-index:32766;width:100%;height:100%;filter:alpha(opacity=70);-moz-opacity:0.7;opacity:0.7;background:#000;",
			boxCss = "display:block;position:fixed;left:50%;top:50%;z-index:32767;margin:-" + boxHeight / 2 + "px 0 0 -" + boxWidth / 2 + "px;";
		// ie6
		if(!window.XMLHttpRequest) {
			// ie6 css表达式
			maskCss += "position:absolute;top:expression(documentElement.scrollTop);height:expression(document.documentElement.clientHeight);";
			boxCss += "position:absolute;top:expression(documentElement.scrollTop + document.documentElement.clientHeight/2);";

			// 解决ie6 bug
			document.getElementsByTagName("html")[0].style.backgroundImage = "url(blank)";
		}
		this.open(maskCss, boxCss);

		// 关闭弹出框事件设置（约定关闭按钮classname为btn-close）
		var tags = this.box.getElementsByTagName("*");
		for (var i = 0; i < tags.length; i++) {
			if (tags[i].className == "btn-close") {
				var self = this;
				tags[i].onclick = function() {
					self.close();
					return false;
				}
				break;
			}
		}
	},

	// 特殊连接提示框
	datatip: function( imgsrc ) {
		if (imgsrc) {
			document.write('<style type="text/css">#data-tip b, #data-tip div{background-image:url(' + imgsrc + ')}</style>');
		}

		// 相对mouse位置
		var offset = {x : 15, y : 15};

		// 分配事件
		var aTags = document.getElementsByTagName("a");
		for (var i = 0; i < aTags.length; i++) {
			if (aTags[i].getAttribute("data-tip") != null) {
				aTags[i].onmouseover = mouseover;
				aTags[i].onmousemove = mousemove;
				aTags[i].onmouseout = mouseout;
			}
		}

		// 鼠标移上或离开
		function isMouseLeaveOrEnter(e, handler) {
			if (e.type != 'mouseout' && e.type != 'mouseover') return false;

			var reltg = e.relatedTarget ? e.relatedTarget : e.type == 'mouseout' ? e.toElement : e.fromElement;
			while (reltg && reltg != handler)
				reltg = reltg.parentNode;
			return (reltg != handler);
		}

		// 鼠标移上
		function mouseover() {
			var event = window.event || arguments[0],
				srcElement = event.srcElement || event.target;
			if(!isMouseLeaveOrEnter(event, this)) return;

			while (srcElement && !srcElement.getAttribute("data-tip")) srcElement = srcElement.parentNode;
			html = "<b></b><div>" + srcElement.getAttribute("data-tip") + "</div>";

			var tip = document.getElementById("data-tip");
			if (tip) {
				tip.innerHTML = html;
				tip.style.display = "block";
			} else {
				var tip = document.createElement("div");
				tip.id = "data-tip";
				tip.innerHTML = html;
				document.body.appendChild(tip);
			}
		}

		// 鼠标移动（在目标上）
		function mousemove() {
			var tip = document.getElementById("data-tip");
			if (!tip) return;

			var event = window.event || arguments[0];
			var pos = mousecoords(event);
			tip.style.left = (pos.x + offset.x) + "px";
			tip.style.top = (pos.y + offset.y) + "px";
		}

		// 鼠标离开
		function mouseout() {
			var event = window.event || arguments[0];
			if (!isMouseLeaveOrEnter(event, this)) return;

			var tip = document.getElementById("data-tip");
			if (!tip) return;

			tip.style.display = "none";
		}

		// 鼠标位置
		function mousecoords(event) {
			if (event.pageX) {
				return {x : event.pageX, y : event.pageY};
			} else {
				return {
					x : event.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft),
					y : event.clientY + (document.documentElement.scrollTop || document.body.scrollTop)
				}
			}
		}
	},

	// 设为首页
	setHomePage: function(url,title) {
		/*
		var aUrls=document.URL.split("/");
	    var vDomainName="http://"+aUrls[2]+"/";
	    try{//IE
	        obj.style.behavior="url(#default#homepage)";
	        obj.setHomePage(vDomainName);
	    }catch(e){//other
	        if(window.netscape) {//ff
	            try {
	                    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	            }
	            catch (e) {
	                    alert("此操作被浏览器拒绝！\n请在浏览器地址栏输入“about:config”并回车\n然后将[signed.applets.codebase_principal_support]设置为'true'");
	            }
	            var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch);
	            prefs.setCharPref('browser.startup.homepage',vDomainName);
	         };
	    };
	    */

	    if (document.all){
			document.body.style.behavior='url(#default#homepage)';
			document.body.setHomePage(url);
		}else if (window.sidebar){
			if(window.netscape){
				try{
					netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				}catch (e){
					alert( "该操作被浏览器拒绝，如果想启用该功能，请在地址栏内输入 about:config,然后将项 signed.applets.codebase_principal_support 值该为true" );
				}
			}
		var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components. interfaces.nsIPrefBranch);
		prefs.setCharPref(title,url);
} 
	},

	// 加入收藏
	addFavorite: function() {
		var aUrls=document.URL.split("/");
	    var vDomainName="http://"+aUrls[2]+"/";
	    var description=document.title;
	    try{//IE
	        window.external.AddFavorite(vDomainName,description);
	    }catch(e){//FF
	        window.sidebar.addPanel(description,vDomainName,"");
	    };
	},

	// 复制地址
	copyURL: function() {
		var myHerf=top.location.href;
		var title=document.title;
		if(window.clipboardData){
			var tempCurLink=title + "\n" + myHerf;
			var ok=window.clipboardData.setData("Text",tempCurLink);
			if(ok) alert("复制成功！按Ctrl+V ,粘贴到QQ或微博上发给你的好友们吧！");
		}else{prompt("按Ctrl+C复制当前网址", myHerf + " " + title);}
	},

	// 三级菜单
	jsmenu: function( selector ) {
		var jsMenu = jQuery(selector);

		/* 二级菜单top */
		var height = jQuery(">ul>li", jsMenu).height();
		jQuery(">ul>li>ul", jsMenu).css({top: height});

		/* 二三级菜单水平对齐 */
		var width = jQuery("ul>li>ul", jsMenu).width();
		jQuery("ul ul ul", jsMenu).css({left: width}).siblings("a").addClass("expand");
		jQuery(">ul>li:last>ul", jsMenu).css({left: "auto",right: 0}).find("ul").css({left: "auto", right: width});

		jQuery("li", jsMenu).hover(
			function () {
				jQuery(this).addClass("hover");
			},
			function () {
				jQuery(this).removeClass("hover");
			}
		);
	},

	// 提前加载图片
	preLoadImg: function(urls) {
		for ( var i = 0; i < urls.length; i++ ) {
			var img = new Image();
			img.src = urls[i];
		}
	},

	// 固定位置块
	/*
	 * vertical的值为"top"或"bottom"
	 * num为vertical相对应的数值（单位为整数）
	 * closeId为关闭固定位置块的按钮id
	 */
	fixedPosition: function( id, vertical, num, closeId ) {
		var timer,
			el = document.getElementById(id),
			elHeight = el.clientHeight,
			closeEl = document.getElementById(closeId);

		if ( !el ) return;
		if ( vertical != "top" && vertical != "bottom" ) return;
		if ( isNaN(num) ) return;

		// 关闭
		if ( !!closeEl ) {
			closeEl.onclick = function() {
				el.parentNode.removeChild(el);
			}
		}

		if ( window.XMLHttpRequest ) return;

		// ie6

		// 事件
		window.resize = function(){setTop()}
		window.onscroll = function(){setTop()}

		function setTop() {
			if ( !el ) return;
			el.style.display = "none";
			if ( timer ) clearTimeout(timer);
			timer = setTimeout(function(){
				el.style.top = getTop();
				jQuery(el).fadeIn();
			}, 100);
		}

		function getTop() {
			var _t = document.documentElement.scrollTop || document.body.scrollTop;

			if ( vertical == "top" ) {
				return _t + num;
			}  else if ( vertical == "bottom" ) {
				var _h = document.documentElement.clientHeight || document.body.clientHeight;
				return _t + _h - elHeight - num;
			}
		}
	},

    /**
     *写cookie
     */
	setCookie:function(cookieName, cookieValue, seconds) {
	    var expires = new Date();
	    expires.setTime(expires.getTime() + parseInt(seconds)*1000);
	    document.cookie = escape(cookieName) + '=' + escape(cookieValue) + (seconds ? ('; expires=' + expires.toGMTString()) : "") + '; path=/; domain=app..com;';
	},

	/**
     *获取cookie
     */
	getCookie:function(cname) {
	    var cookie_start = document.cookie.indexOf(cname);
	    var cookie_end = document.cookie.indexOf(";", cookie_start);
	    return cookie_start == -1 ? '' : decodeURI(document.cookie.substring(cookie_start + cname.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length)));
	},

	/**
	 * js时间戳转为日期格式 
   	 */
  	getLocalTime:function(nS) {     
   		return new Date(parseInt(nS) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ").substr(0,9);    
	} ,

  	
  	/**
   	 * js分页
   	 */
  	pageFun:function() {
  			var d_length = $('.decmt-box').length,
  				_count = 5,//每页数量
  				n = 1,
  				last = Math.ceil(d_length / _count),
  				setp = function(set) {
  					parseInt(set) > 0 ? n += parseInt(set) : n += parseInt(set);
  					if(n <= 0) {
  						n = 1;
  					} else if(n > last) {
  						n = last;
  					}
  				};
  			var pf = function(param, bb, nn) {
  					!bb || setp(bb);
  					!nn || setp(nn);
  					var page = param || n,
  						count = _count,
  						limit = page - 1,
  						offset = (page == 1) ? 0 : (limit * count);
  					$('.decmt-box').hide();
  					for(var i = offset; i <= offset + count - 1; i++) {
  						if($('.decmt-box')[i]) {
  							$($('.decmt-box')[i]).show();
  						}
  					}
  				}
  				/*
  			if(d_length >= 5) {
  				var d_dom = '<a href="javascript:pageF(1);" title="">首页</a><a href="javascript:pageF(\'\',-1,\'\');" title="">上一页</a><a href="javascript:pageF(\'\',\'\',1);" title="">下一页</a><a href="javascript:pageF(' + last + ');" title="">最后一页</a>';
  				$('.pages').append($(d_dom));
  			}*/
  			return pf;
  	},

  	/**
	 * 获取主页排行版
	 */
	getindex:function(dom,type,offset){
		var offset = offset?offset:0;
		var str = '',i,url,name,down,zan,time,post_url;		
		var paras='offset='+offset;
        var url = this.domainURI(window.location.href);
        switch(type){
        	case 'new':
                post_url = url + "index.php?m=api&c=search&a=get_category";
        		break;
        	case 'down':
                post_url = url + "index.php?m=api&c=search&a=get_category";
        		break;
        	case 'zan':
                post_url = url + "index.php?m=api&c=search&a=get_zan";
        		break;
        }
        if(post_url)
	        $.ajax({
	                type: "GET",
	                url: post_url,
	                data:paras,
	                async:true,
	                dataType: 'json',
	                success: function(data) {
	                	if(data.status==1){
	                		host = data.host;
	                		data = data.result;
		                	for(i in data){
		                		data[i]['jumpUrl']=data[i]['jumpUrl']?data[i]['jumpUrl']:host+'game/'+data[i]['whId']+'.html';
		                		url = data[i]['jumpUrl'];
		                		name = data[i]['game_name_cn']?data[i]['game_name_cn']:data[i]['game_name_en'];
		                		down = data[i]['down']?data[i]['down']:0;
		                		zan = data[i]['zan']?data[i]['zan']:0;
		                		//time = data[i]['createTime']?data[i]['createTime'].substr(0,7):'';
		                		time = data[i]['create_time']?data[i]['create_time'].substr(0,7):'';
		                		str += '<li><table width="100%" cellpadding="0" cellspacing="0"><tr><td width="62px"><a href="'+url+'" title="'+name+'"><img src="'+data[i]['downIcon']+'"/></a></td><td width="150px"><h3><a href="'+url+'" title="'+name+'">'+name+'</a></h3><p>'+data[i]['types']+' '+time+'</p></td><td></td><td>赞('+zan+')</td><td width="70px"><a href="'+url+'" class="downloadIcon"></a></td></tr></table></li>';
		                	}
		                	if(offset===0){
		                		str += '<li class="paginationLi"><a href="javascript:;" class="pagination">上一页</a><a href="javascript:;" onclick="TOOL.getindex(\''+dom+'\',\''+type+'\','+(offset+7)+')" class="pagination">下一页</a></li>';
		                	}else{
		                		str += '<li class="paginationLi"><a href="javascript:;" onclick="TOOL.getindex(\''+dom+'\',\''+type+'\','+(offset-7)+')" class="pagination">上一页</a><a href="javascript:;" onclick="TOOL.getindex(\''+dom+'\',\''+type+'\','+(offset+7)+')" class="pagination">下一页</a></li>';
		                	}
		                	//console.log(str);
	                		$('#'+dom).html(str);
	                	}                        
	                	return true;          
	                },
	                error: function() {
	                    //console.log('网络故障，验证失败！');
	                    return false;
	                }
	        });        
    },

  	/**
  	 * 游戏栏目页
   	 * 获取搜索数据
   	 */
  	getGame:function(e) {
  		if(e){
	  		if($(e.target).parent().find('.gameListDevice').length!=0){
		  		$(e.target).parent().find('.gameListDevice').removeClass('gameListDevice');
		  		$(e.target).addClass('gameListDevice');
	  		}
	  		if($(e.target).parent().find('.gameListInter').length!=0){
		  		$(e.target).parent().find('.gameListInter').removeClass('gameListInter');
		  		$(e.target).addClass('gameListInter');
	  		}

			if($(e.target).parent().find('.gameListOrder').length!=0){
		  		$(e.target).parent().find('.gameListOrder').removeClass('gameListOrder');
		  		$(e.target).addClass('gameListOrder');
	  		}
  		}

  		
  		var gameListDevice = $('.gameListDevice').attr('inc')
  		,gameListInter = $('.gameListInter').attr('inc')
  		,gameListOrder = $('.gameListOrder').attr('inc')
        ,url = this.domainURI(window.location.href)
        ,post_url = url + "index.php?m=api&c=search&a=get_category"
  		,offset = typeof(arguments[0])=='number'?arguments[0]:0
  		,str = ''
  		,i
  		,_offset = 0
  		,name
  		,url
  		,parm = 'device='+gameListDevice+'&inter='+gameListInter+'&order='+gameListOrder+'&offset='+offset;

  		//console.log(parm);
  		if(offset-24<0)
  			_offset=0;
  		else
  			_offset=offset-24;
  		
  		$.ajax({
                type: "POST",
                url: post_url,                
                data:parm,
                async:true,
                dataType: 'json',
                success: function(data) {
                	if(data && data.status==1){
                		host = data.host;
                		data = data.result;
	                	for(i in data){
	                		data[i]['jumpUrl']=data[i]['jumpUrl']?data[i]['jumpUrl']:host+'game/'+data[i]['game_id']+'.html';
	                		url = data[i]['jumpUrl'];
		                	name = data[i]['game_name_cn']?data[i]['game_name_cn']:data[i]['game_name_en'];
	                		str += '<li><a href="'+url+'" title="'+name+'"><span><img src="'+data[i]['icon']+'"></span><b>'+name+'</b><p>'+data[i]['types']+'</p></a></li>';
	                	}

	                	str += "<div class=\"Pagination\"><a href=\"javascript:;\" onclick=\"TOOL.getGame(0)\" class=\"homePage\">首页</a><a href=\"javascript:;\" onclick=\"TOOL.getGame("+_offset+")\" class=\"PagePrev\">上一页</a><div class=\"pagesnum\"></div><a href=\"javascript:;\" onclick=\"TOOL.getGame("+(offset+24)+")\" class=\"PageNext\">下一页</a>";
						$('.iosGameUl').html(str);                                   
                	}                                 
                },
                error: function() {
                    console.log('网络故障，验证失败！');
                    return false;
                }
        });
  	},

  	/**
  	 * 游戏搜索页
   	 * 获取搜索数据
   	 */
  	getSearch:function(e) {
  		if(e){
	  		if($(e.target).parent().find('.gameListDevice').length!=0){
		  		$(e.target).parent().find('.gameListDevice').removeClass('gameListDevice');
		  		$(e.target).addClass('gameListDevice');
	  		}
	  		if($(e.target).parent().find('.gameListInter').length!=0){
		  		$(e.target).parent().find('.gameListInter').removeClass('gameListInter');
		  		$(e.target).addClass('gameListInter');
	  		}

			if($(e.target).parent().find('.gameListType').length!=0){
		  		$(e.target).parent().find('.gameListType').removeClass('gameListType');
		  		$(e.target).addClass('gameListType');
	  		}
  		}

  		
  		var gameListDevice = $('.gameListDevice').attr('inc')
  		,gameListInter = $('.gameListInter').attr('inc')
  		,gameListType = $('.gameListType').attr('inc')
        ,url = this.domainURI(window.location.href)
        ,post_url = url + "index.php?m=api&c=search&a=game_search"
  		,offset = typeof(arguments[0])=='number'?arguments[0]:0
  		,str = ''
  		,i
  		,_offset = 0
  		,__offset = 0
  		,name
  		,url
  		,parm = 'device='+gameListDevice+'&inter='+gameListInter+'&types='+gameListType+'&offset='+offset;

  		if(offset-20<0)
  			_offset=0;
  		else
  			_offset=offset-20;
  		
  		$.ajax({
                type: "POST",
                url: post_url,                
                data:parm,
                async:true,
                dataType: 'json',
                success: function(data) {
                	if(data && data.status==1){
                		host = data.host;
                		data = data.result;
	                	for(i in data){
	                		data[i]['jumpUrl']=data[i]['jumpUrl']?data[i]['jumpUrl']:host+'game/'+data[i]['game_id']+'.html';
	                		url = data[i]['jumpUrl'];
		                	name = data[i]['game_name_cn']?data[i]['game_name_cn']:data[i]['game_name_en'];
	                		str += '<li><a href="'+url+'" title="'+name+'"><span><img src="'+data[i]['icon']+'"></span><b>'+name+'</b><p>'+data[i]['types']+'</p></a></li>';
	                	}
	                	__offset = offset+20;
                	}else{
                		__offset = offset;
                	}
                	str += "<div class=\"Pagination\"><a href=\"javascript:;\" onclick=\"TOOL.getSearch(0)\" class=\"homePage\">首页</a><a href=\"javascript:;\" onclick=\"TOOL.getSearch("+_offset+")\" class=\"PagePrev\">上一页</a><div class=\"pagesnum\"></div><a href=\"javascript:;\" onclick=\"TOOL.getSearch("+__offset+")\" class=\"PageNext\">下一页</a>";
					$('#searchContentUl').html(str);
					                                   
                },
                error: function() {
                    console.log('网络故障，验证失败！');
                    return false;
                }
        });
  	},
  	
  	/**
  	 * 游戏搜索页(游戏分类)
   	 * 获取搜索数据
   	 */
  	getGameType:function(e) {
  		$('#searchContent').hide();
  		$('#casualPuzzle').show();
  		if(typeof(e)!='number' && e){	  		
	  		$('#gameKindUl').find('.select').removeClass('select');
	  		$(e.target).addClass('select');	  		
	  	}
  		
  		var gameListType = $('#gameKindUl').find('.select').attr('incType')
        ,url = this.domainURI(window.location.href)
        ,post_url = url + "index.php?m=api&c=search&a=game_type"
  		,offset = typeof(arguments[0])=='number'?arguments[0]:0
  		,str = ''
  		,hotStr = ''
  		,i
  		,_offset = 0
  		,name
  		,url
  		,parm = 'types='+gameListType+'&offset='+offset;

  		$('#typeChange').html(gameListType);
  		if(offset-25<0)
  			_offset=0;
  		else
  			_offset=offset-25;
  		
  		$.ajax({
                type: "POST",
                url: post_url,                
                data:parm,
                async:true,
                dataType: 'json',
                success: function(data) {//console.log(data);
                	if(data && data.status==1){
                		host = data.host;
                		data = data.result;
	                	for(i in data){
	                		data[i]['jumpUrl']=data[i]['jumpUrl']?data[i]['jumpUrl']:host+'game/'+data[i]['game_id']+'.html';
	                		url = data[i]['jumpUrl'];
		                	name = data[i]['game_name_cn']?data[i]['game_name_cn']:data[i]['game_name_en'];
		                	if(i<5)
		                		hotStr += '<li><a href="'+url+'" title="'+name+'"><span><img src="'+data[i]['icon']+'"><strong></strong></span><b>'+name+'</b><p>'+data[i]['types']+'</p></a></li>';
		                	else
	                			str += '<li><a href="'+url+'" title="'+name+'"><span><img src="'+data[i]['icon']+'"></span><b>'+name+'</b><p>'+data[i]['types']+'</p></a></li>';
	                	}

	                	str += "<div class=\"Pagination\"><a href=\"javascript:;\" onclick=\"TOOL.getGameType(0)\" class=\"homePage\">首页</a><a href=\"javascript:;\" onclick=\"TOOL.getGameType("+_offset+")\" class=\"PagePrev\">上一页</a><div class=\"pagesnum\"></div><a href=\"javascript:;\" onclick=\"TOOL.getGameType("+(offset+25)+")\" class=\"PageNext\">下一页</a>";
						$('#hotCasualPuzzleUl').html(hotStr);
						$('#casualPuzzleUl').html(str);
                	}
                },
                error: function() {
                    console.log('网络故障，验证失败！');
                    return false;
                }
        });
  	},

    /**
     * 获取当前域名
     */
    getHost:function(url) {
        var host = "null";
        if(typeof url == "undefined" || null == url)
            url = window.location.href;
        var regex = /http.*\:\/\/([^\/]*).*/;
        var match = url.match(regex);
        if(typeof match != "undefined" && null != match)
            host = match[1];
        return host;
    },

    domainURI:function(str){
        var durl=/http:\/\/([^\/]+).*([\/$])/i;
        domain = str.match(durl);
        return domain[0];
    },

  	/**
  	 * 游戏搜索页(游戏必备)
   	 * 获取搜索数据
   	 */
  	getBibei:function(e) {
  		$('#searchContent').hide();
  		$('#casualPuzzle').show();
  		
	  	$('#gameKindUl').find('.select').removeClass('select');
  		
  		var position = typeof(arguments[1])=='number'?arguments[1]:8
        ,url = this.domainURI(window.location.href)
        ,post_url = url + "index.php?m=api&c=search&a=position"
  		,offset = typeof(arguments[0])=='number'?arguments[0]:0
  		,str = ''
  		,hotStr = ''
  		,i
  		,_offset = 0
  		,name
  		,url
  		,parm = 'posid='+position+'&offset='+offset;

  		if(typeof(e)=='object')
  			$('#typeChange').html($(e.target).html());

  		if(offset-25<0)
  			_offset=0;
  		else
  			_offset=offset-25;
  		$.ajax({
                type: "POST",
                url: post_url,                
                data:parm,
                async:true,
                dataType: 'json',

                success: function(data) {
                	if(data && data.status==1){
                		data = data.result;
	                	for(i in data){
	                		url = data[i]['url']?data[i]['url']:'';
		                	name = data[i]['title']?data[i]['title']:'';
		                	if(i<5)
		                		hotStr += '<li><a href="'+url+'"><span><img src="'+data[i]['thumb']+'"><strong></strong></span><b>'+name+'</b><p>'+data[i]['data']+'</p></a></li>';
		                	else
	                			str += '<li><a href="'+url+'"><span><img src="'+data[i]['thumb']+'"></span><b>'+name+'</b><p>'+data[i]['data']+'</p></a></li>';
	                	}

	                	str += "<div class=\"Pagination\"><a href=\"javascript:;\" onclick=\"TOOL.getBibei(0,"+position+")\" class=\"homePage\">首页</a><a href=\"javascript:;\" onclick=\"TOOL.getBibei("+_offset+","+position+")\" class=\"PagePrev\">上一页</a><div class=\"pagesnum\"></div><a href=\"javascript:;\" onclick=\"TOOL.getBibei("+(offset+25)+","+position+")\" class=\"PageNext\">下一页</a>";
						$('#hotCasualPuzzleUl').html(hotStr);
						$('#casualPuzzleUl').html(str);
                	}
				},
                error: function() {
                    console.log('网络故障，验证失败！');
                    return false;
                }
        });
  	},

  	/**
  	 * 游戏搜索页(游戏必备)
   	 * 获取赞
   	 */
   	getZan:function(whId){
   		var post_url = "http://app..com/api/app_art.php?act=zan"
   		,str
   		,up=arguments[1]?arguments[1]:0;

   		if(up!=0){
	   		if(TOOL.getCookie('app'+whId)){
	   			alert('已赞');return false;
	   		}else{
	   			TOOL.setCookie('app'+whId, 1, 1800);
	   		}
   		}
   		
   		$.ajax({
                type: "POST",
                url: post_url,                
                data:'whId='+whId+'&up='+up,
                async:true,
                dataType: 'json',
                success: function(data) {

                	if(data && data.status==1){
                		data = data.result;
                		str = data===0?data:data[0]['zan'];
                	}
					$('#zanNum').html(str);
                },
                error: function() {
                    console.log('网络故障，验证失败！');
                    return false;
                }
        });

   	},
   	/**
  	 * 游戏搜索页(游戏必备)
   	 * 获取赞
   	 */
   	getSoso:function(whId){
   		var post_url = "http://app..com/api/app_art.php?act=soso"
   		,str
   		,up=arguments[1]?arguments[1]:0;

   		if(up!=0){
	   		if(TOOL.getCookie('appSoso'+whId)){
	   			alert('已评');return false;
	   		}else{
	   			TOOL.setCookie('appSoso'+whId, 1, 1800);
	   		}
   		}
   		
   		$.ajax({
                type: "POST",
                url: post_url,                
                data:'whId='+whId+'&up='+up,
                async:true,
                dataType: 'json',
                success: function(data) {

                	if(data && data.status==1){
                		data = data.result;
                		str = data===0?data:data[0]['soso'];
                	}
					$('#sosoNum').html(str);
                },
                error: function() {
                    console.log('网络故障，验证失败！');
                    return false;
                }
        });

   	},
   	/**
  	 * 游戏搜索页(游戏必备)
   	 * 获取赞
   	 */
   	getLow:function(whId){
   		var post_url = "http://app..com/api/app_art.php?act=low"
   		,str
   		,up=arguments[1]?arguments[1]:0;

   		if(up!=0){
	   		if(TOOL.getCookie('appLow'+whId)){
	   			alert('已评');return false;
	   		}else{
	   			TOOL.setCookie('appLow'+whId, 1, 1800);
	   		}
   		}
   		
   		$.ajax({
                type: "POST",
                url: post_url,                
                data:'whId='+whId+'&up='+up,
                async:true,
                dataType: 'json',
                success: function(data) {

                	if(data && data.status==1){
                		data = data.result;
                		str = data===0?data:data[0]['low'];
                	}
					$('#lowNum').html(str);
                },
                error: function() {
                    console.log('网络故障，验证失败！');
                    return false;
                }
        });

   	},
   	/**
  	 * 游戏搜索页(游戏必备)
   	 * 获取搜索数据
   	 */
   	getDown:function(whId){
   		var post_url = "http://app..com/api/app_art.php?act=down";
   		$.ajax({
                type: "POST",
                url: post_url,                
                data:'whId='+whId,
                async:true,
                dataType: 'json',
                success: function(data) {
                	return true;
                },
                error: function() {
                    console.log('网络故障，验证失败！');
                    return false;
                }
        });

   	},
   	/**
  	 * 游戏搜索页(游戏必备)
   	 * 获取搜索数据
   	 */
   	getLike:function(whId,toId){
   		var post_url = "http://app..com/api/app_art.php?act=like";
   		$.ajax({
                type: "POST",
                url: post_url,                
                data:'fromGame='+whId+'&toGame='+toId,
                async:true,
                dataType: 'json',
                success: function(data) {
                	return true;
                },
                error: function() {
                    console.log('网络故障，验证失败！');
                    return false;
                }
        });

   	},
   	/**
  	 * 提交游戏
   	 */
   	pushGame:function(){
   		var post_url = "http://app..com/api/app_art.php?act=pushGame";
   		if($('.reportName').val()=='请输入游戏名称'){
   			alert('请输入游戏名称');
   			return false;
   		}
   		if($('.reportName').val()==''){
   			alert('请输入游戏名称');
   			return false;
   		}  

   		$.ajax({
                type: "POST",
                url: post_url,                
                data:'game=' + $('.reportName').val(),
                async:true,
                dataType: 'json',
                success: function(data) {
                	if(data.status==1)
                		alert('提交成功');
                	if(data.status==-1)
                		alert(data.reason);
                	return true;
                },
                error: function() {
                    console.log('网络故障，验证失败！');
                    return false;
                }
        });

   	},

   	getQueryString:function (str) {
	    var LocString=String(window.document.location.href); 
	    var rs = new RegExp("(^|)"+str+"=([^/&]*)(/&|$)","gi").exec(LocString), tmp;   
       
        if(tmp=rs){   
            return tmp[2];   
        }   
       
        // parameter cannot be found   
        return ""; 
    },

  	setMouse:function(a, b, c, d, e) {
		if (!document.getElementById(a + "_Clicks")) return;
		$("#" + a + "_Clicks " + b[0]).each(function(g) {
			var h = $(this);
			$(this).bind("mouseover",
			function() {
				if (typeof e == "object" && e.length > 0) $("#" + a + "_Link").attr("href", e[g]);	//改变链接地址
				
				//改变背景样式
				var f = $("#" + a + "_Clicks " + b[0] + "[re-class]")[0];
				if (f) {
					f.className = $(f).attr("re-class");
					$(f).removeAttr("re-class")
				}
				if (h[0].className && h[0].className != ""){
					h.attr("re-class", h[0].className);
				}else{
					h.attr("re-class", '');
				}

				if (d == "replace") {
					h[0].className = c[g]
				} else if (d == "add") {
					h.addClass(c[g])
				}
				
				//显示隐藏属性的更改
				if (document.getElementById(a + "_ShowOrHides")) {
					$("#" + a + "_ShowOrHides " + b[1]).hide();
					$($("#" + a + "_ShowOrHides " + b[1]).get(g)).show()
				}
				if (b.length > 2) {
					for (var j = 2; j < b.length; j++) {
						$(b[j]).hide();
						$(b[j]).eq(g).show()
					}
				}
			});
		})
	},

	/*登录回车提交*/
	InputKeyPress:function(aFrmObj) {
		var currKey=0,CapsLock=0;
		var e = arguments[1];
		e=e||window.event;
		var kCode=e.keyCode||e.which||e.charCode;
		if(kCode == '13'){
			if(document.getElementById(aFrmObj)!=null){
				document.getElementById(aFrmObj).onsubmit=function(){return false};
			}
			checkLogin(aFrmObj);
		} 
  	},

  	/**
     *系统判断
     */
    isSys:function(){
		var u = navigator.userAgent, app = navigator.appVersion,u_lower=u.toLowerCase();
		return {         //移动终端浏览器版本信息
		    trident: u.indexOf('Trident') > -1, //IE内核
		    presto: u.indexOf('Presto') > -1, //opera内核
		    webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
		    gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
		    mobile: u.indexOf('Mobile')> -1 || u.indexOf('Android')> -1 || u.indexOf('Silk/')> -1 || u.indexOf('Kindle')> -1 || u.indexOf('BlackBerry')> -1 || u.indexOf('Opera Mini')> -1 || u.indexOf('Opera Mobi')> -1, //是否为移动终端
		    ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
		    android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或uc浏览器
		    iPhone: u.indexOf('iPhone') > -1 , //是否为iPhone或者QQHD浏览器
		    iPad: u.indexOf('iPad') > -1, //是否iPad
		    iPod: u.indexOf('iPod') > -1, //是否iPod
		    webApp: u.indexOf('Safari') == -1, //是否web应该程序，没有头部与底部
		    windowsPhone: !!u.match(/Windows\sPhone.*/),
		    weixin: u_lower.match(/MicroMessenger/i)=="micromessenger"
		};
    }(),
    windowJump:function(){

    	var u = window.location.href,us='http://app..com/mobile/',t;
    	
    	if(u.indexOf('/game')>-1)//内页
    		t = u.replace('game/','mobile/game/');
    	else if(u.indexOf('search.h')>-1)//搜索页
    		t = u.replace('/search.html','/mobile/search.html');
    	else if(u.indexOf('act=search')>-1)//结果页
    		t = us+"search.html";
    	else if(u.indexOf('ios.html')>-1)
    		t = us+"?ssn=ios";
    	else if(u.indexOf('android.html')>-1)
    		t = us+"?ssn=android";
    	else if(u.indexOf('wp.html')>-1)
    		t = us+"?ssn=wp";
    	else
    		t = us;
    	return t;
    }

};
//console.log(TOOL.windowJump());
if(TOOL.isSys.mobile)
	window.location.href=TOOL.windowJump();