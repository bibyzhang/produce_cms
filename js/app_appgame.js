/*!
 * FileName   : app_.js
 * WebSite    : http://www..com
 * Desc       :
 * Author     : zwj
 * version    : 2.1.1
 * */

var APPDB = {
  	/**
	 * 获取主页排行版
	 */
	getIndexTop:function(act){
		var act = act?act:0;
		var str = '',i,url,name,jumpUrl,list,sele,ord;		
		var paras='act='+act;
		url = "http://app..com/api/app_.php?";
		url = url + paras;

        $.getJSON(url+ '&callback=?',function(data){
	        if(data.status==1){
        		host = data.host;
        		list = data.list;

        		for(i in list){
	            	jumpUrl =list[i]['jumpUrl']?list[i]['jumpUrl']:host+'game/'+list[i]['whId']+'.html';
	            	name = list[i]['zhName']?list[i]['zhName']:list[i]['usName'];

	            	switch(act){
        				case 'index_single':
        					ord = i==0?'no1':i==1?'no2':i==2?'no3':'nor';		            	
		            		break;
		            	case 'index_android':
        					ord = i==0?'andno1':i==1?'andno2':i==2?'andno3':'andnor';		            	
		            		break;
		            	case 'index_ios':
        					ord = i==0?'iosno1':i==1?'iosno2':i==2?'iosno3':'iosnor';		            	
		            		break;
		            	case 'index_inter':
        					ord = i==0?'no1':i==1?'no2':i==2?'no3':'nor';		            	
		            		break;
	            	}

	            	sele = Number(i)+1;
	            	sele = sele<10?'0'+sele:sele;
	            	str += '<li class="'+ord+'"><span class="no">'+sele+'</span> <span class="gamename"><a href="'+jumpUrl+'" target="_blank">'+name+'</a></span></li>';	        		
	        	}	

        		$('#'+act).html(str);
        	}                        
        	return true;     
	    });       
    }
};