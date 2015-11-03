/*!
 * FileName   : appdb.js
 * WebSite    : http://a..com
 * Desc       :
 * Author     : zwj
 * version    : 2.1.1
 * */

var APPDB = {
  	/**
	 * 获取主页排行版
	 */
	getAappIndex:function(act){
		var act = act?act:0;
		var str = '',i,url,name,jumpUrl,list,sele,ord;		
		var paras='act='+act;
		url = "http://app..com/api/app_a.php?";
		url = url + paras;

        $.getJSON(url+ '&callback=?',function(data){
	        if(data.status==1){
        		host = data.host;
        		switch(act){
        			case 'index_android_single':
        				list = data.list;			            
			            
			            for(i in list){
			            	jumpUrl =list[i]['jumpUrl']?list[i]['jumpUrl']:host+'game/'+list[i]['whId']+'.html';
			            	name = list[i]['zhName']?list[i]['zhName']:list[i]['usName'];
			            	if(i==0)
			            		sele = ' rankingSelect',ord = ' gOne';
			            	else if(i==1)
			            		sele = '',ord = ' gTwo';
			            	else if(i==2)
			            		sele = '',ord = ' gThree';
			            	else
			            		sele = '',ord = '';
			            	str += '<li class="rankingLi'+sele+'" onmouseover="APPDB.rankingLi(this)"> <span class="rankingNum'+ord+'">'+(Number(i)+1)+'</span> <img src="'+list[i]['downIcon']+'" class="rankingImg'+sele+'"> <b class="rankingName'+sele+'">'+name+'</b> <strong class="rankingType'+sele+'">'+list[i]['types']+'</strong> <a href="'+jumpUrl+'" class="rankingDownloadIcon'+sele+'" target="_blank">下载</a> </li>';
			        	}

        				break;
        			case 'index_android_inter':
        				list = data.list;			            
			            
			            for(i in list){
			            	jumpUrl =list[i]['jumpUrl']?list[i]['jumpUrl']:host+'game/'+list[i]['whId']+'.html';
			            	name = list[i]['zhName']?list[i]['zhName']:list[i]['usName'];
			            	if(i==0)
			            		sele = ' rankingSelect',ord = ' bOne';
			            	else if(i==1)
			            		sele = '',ord = ' bTwo';
			            	else if(i==2)
			            		sele = '',ord = ' bThree';
			            	else
			            		sele = '',ord = '';
			            	str += '<li class="rankingLi'+sele+'" onmouseover="APPDB.rankingLi(this)"> <span class="rankingNum'+ord+'">'+(Number(i)+1)+'</span> <img src="'+list[i]['downIcon']+'" class="rankingImg'+sele+'"> <b class="rankingName'+sele+'">'+name+'</b> <strong class="rankingType'+sele+'">'+list[i]['types']+'</strong> <a href="'+jumpUrl+'" class="rankingDownloadIcon'+sele+'" target="_blank">下载</a> </li>';
			        	}
	            	
        				break;
        		}
        		//str += "<script>$('.rankingLi').mouseenter(function(e) {$('.rankingLi').removeClass('rankingSelect').find('.rankingImg,.rankingName,.rankingType,.rankingDownloadIcon').removeClass('rankingSelect');$(this).addClass('rankingSelect').find('.rankingImg,.rankingName,.rankingType,.rankingDownloadIcon').addClass('rankingSelect');});</script>";
        		$('#'+act).html(str);
        		//document.write(str);
        	}                        
        	return true;     
	    });       
    },
    rankingLi:function(tt){    	
    		//$('.rankingLi').removeClass('rankingSelect').find('.rankingImg,.rankingName,.rankingType,.rankingDownloadIcon').removeClass('rankingSelect');$(tt).addClass('rankingSelect').find('.rankingImg,.rankingName,.rankingType,.rankingDownloadIcon').addClass('rankingSelect');

    		$(tt).parent().find('.rankingLi').removeClass('rankingSelect').find('.rankingImg,.rankingName,.rankingType,.rankingDownloadIcon').removeClass('rankingSelect');
			$(tt).addClass('rankingSelect').find('.rankingImg,.rankingName,.rankingType,.rankingDownloadIcon').addClass('rankingSelect');


    }
};