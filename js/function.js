$(function(){
	
	/*首页 新游推荐*/
	$('.list-2 b').hover(function(e) {
    var index=$(this).index();
		$(this).parent().find('b').removeClass('select');
		$(this).addClass('select');
		$(this).parent().parent().parent().find('.ngrTab').eq(index).show().siblings('.ngrTab').hide();
  });
	
	/*首页最新 热门 赞*/
	$('.list-4 b').hover(function(e) {
    var index=$(this).index();
		$(this).parent().find('b').removeClass('select');
		$(this).addClass('select');
		$('.tab3').eq(index).show().siblings('.tab3').hide();
  });
	
	/*首页 统计表*/
	$('.gameStatistics b').attr('data',function(){
		var dataName=$(this).parent().attr('data-name');
		var dataHot=$(this).parent().attr('data-hot');
		var dataHeight=dataHot/10000*200+'px';
		var dataTop=-dataHot/10000*200+30+'px';
		$(this).parent().find('span').text(dataName);
		$(this).find('i').text(dataHot);
		$(this).css('height',dataHeight);
		/*$(this).css('bottom',dataTop);
		$(this).hover(function(e) {
			$(this).animate({bottom:0},500,function(){
				$(this).parent().parent().find('strong').hide();
				$(this).parent().find('strong').show();
				});
		});*/
	});
	$('.gameStatistics strong').attr('data',function(){
		var dataTop=200-$(this).parent().find('b').height()-10-30;
		$(this).css('top',dataTop);
		});
	$('.gameStatistics b').hover(function(e) {
		$('.gameStatistics strong').hide();
		$(this).parent().find('strong').show();
	});
	
	
	/*游戏单页面 截图*/
	$('.gbb-Screenshot img').load(function(e) {
  	var imgNum=$('.gbb-Screenshot img').index();
		var imgForm=0;
		for(var i=0;i<=imgNum;i++){
			imgForm+=$('.gbb-Screenshot').find('img').eq(i).width();
			}
		imgForm+=(imgNum+1)*3;
		$('.gbb-Screenshot').css('width',imgForm);
  });
		
	/*排行榜交互*/
	$('.list-9 li').mouseenter(function(e) {
		$(this).parent().find('li').removeClass('rankingSelect').find('.rankingImg,.rankingName,.rankingType,.rankingDownloadIcon').removeClass('rankingSelect');;
		$(this).addClass('rankingSelect').find('.rankingImg,.rankingName,.rankingType,.rankingDownloadIcon').addClass('rankingSelect');;
	});
	
	/*排行榜切换*/
	$('.list-9 .aglsHead b').hover(function(e) {
    var index=$(this).index();
		$(this).parent().find('b').removeClass('select').find('strong').removeClass('rankingIcon');
		$(this).addClass('select').find('strong').addClass('rankingIcon');
		$(this).parent().parent().find('.rankingUl').hide();
		$(this).parent().parent().find('.rankingUl').eq(index).show();
  });
	
	/*搜索页 相关资讯分页
	$('.searchpaging').kkPages({	
		PagesClass:'li', //需要分页的元素
		PagesMth:20, //每页显示个数		
		PagesNavMth:5 //显示导航个数
	});*/
	
	/*ios游戏 分页
	$('.iosGameUl').kkPages({	
		PagesClass:'li', //需要分页的元素
		PagesMth:24, //每页显示个数		
		PagesNavMth:5 //显示导航个数
	});*/
	
	/*游戏列表 分页
	$('.gameListUl').kkPages({	
		PagesClass:'li', //需要分页的元素
		PagesMth:20, //每页显示个数		
		PagesNavMth:5 //显示导航个数
	});*/
	
	/*游戏列表 切换
	$('#gameKindUl li a').click(function(e) {
		$('#gameKindUl li a').removeClass('select');
		$(this).addClass('select');
    var classN=$(this).attr("gameKind");
		$('.aboutInformation').hide();
		document.getElementById(classN).style.display="block";
  });*/
	
	
	});