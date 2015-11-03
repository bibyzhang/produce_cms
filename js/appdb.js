/*!
 * FileName   : appdb.js
 * WebSite    : http://app..com
 * Desc       :
 * Author     : zwj
 * version    : 2.1.1
 * */

var APPDB = {
  	/**
	 * 获取主页排行版
	 */
	getindex:function(type,act){
		var act = act?act:0;
		var str = '',i,url,name,jumpUrl,list,list2;		
		var paras='act='+act;
		url = "http://app..com/api/app_www.php?";
		url = url + paras;

        $.getJSON(url+ '&callback=?',function(data){
	        if(data.status==1){
        		host = data.host;
        		switch(act){
        			case 'index_android':
        				list = data.list;
	        			list2 = data.list2;	        			
	        			if(type==1)
		        			for(i in list){
			            		str += '<li><a href="'+list[i]['url']+'" target="_blank"><img src="'+list[i]['thumb']+'"><p>'+list[i]['title']+'</p></a></li>';
			            	}
		            	if(type==2)
			            	for(i in list2){
			            		jumpUrl =list2[i]['jumpUrl']?list2[i]['jumpUrl']:host+'game/'+list2[i]['whId']+'.html';
			            		name = list2[i]['zhName']?list2[i]['zhName']:list2[i]['usName'];
			            		str += '<li><b>'+(Number(i)+1)+'</b><a href="'+jumpUrl+'" class="gotoGame" target="_blank">查看游戏</a><a href="'+jumpUrl+'" class="gameName" target="_blank">'+name+'</a></li>';
			            	}
        				break;
        			case 'index_ios':
        				list = data.list;
	        			list2 = data.list2;
	        			if(type==1)
		        			for(i in list){			            		
			            		str += '<li><a href="'+list[i]['url']+'" target="_blank"><img src="'+list[i]['thumb']+'"><p>'+list[i]['title']+'</p></a></li>';
			            	}
		            	if(type==2)
			            	for(i in list2){
			            		jumpUrl =list2[i]['jumpUrl']?list2[i]['jumpUrl']:host+'game/'+list2[i]['whId']+'.html';
			            		name = list2[i]['zhName']?list2[i]['zhName']:list2[i]['usName'];
			            		str += '<li><b>'+(Number(i)+1)+'</b><a href="'+jumpUrl+'" class="gotoGame" target="_blank">查看游戏</a><a href="'+jumpUrl+'" class="gameName" target="_blank">'+name+'</a></li>';
			            	}		            	
        				break;
        			case 'index_activity':
        				list = data.list;
	        			list2 = data.list2;
	        			list3 = data.list3;
	        			if(type==1)
		        			for(i in list){
			            		str += '<li><a target="_blank" href="'+list[i]['url']+'">'+list[i]['data']+'</a></li>';
			            	}
		            	if(type==2)
			            	for(i in list2){
			            		str += '<li>'+list2[i]['title']+' . '+list2[i]['data']+'<span>&gt;</span><a href="'+list2[i]['url']+'" target="_blank">领取</a></li>';
			            	}
			            if(type==3)
			            	for(i in list3){
			            		jumpUrl =list3[i]['jumpUrl']?list3[i]['jumpUrl']:host+'game/'+list3[i]['whId']+'.html';
			            		name = list3[i]['zhName']?list3[i]['zhName']:list3[i]['usName'];
			            		str += '<li><strong>'+(Number(i)+1)+'.&nbsp;</strong><a href="'+jumpUrl+'" target="_blank" class="enterGame">进入游戏</a><a href="'+jumpUrl+'" target="_blank" class="gameName">'+name+'</a></li>';
			            	}
		            	
        				break;

        		}
        		$('#'+act+'_'+type).html(str);
        		//document.write(str);
        	}                        
        	return true;     
	    });       
    }
};