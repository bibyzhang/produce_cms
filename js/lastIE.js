var imgNum=$('.gbb-Screenshot img').index();
var imgForm=0;
for(var i=0;i<=imgNum;i++){
	imgForm+=$('.gbb-Screenshot').find('img').eq(i).width();
	}
imgForm+=(imgNum+1)*3;
$('.gbb-Screenshot').css('width',imgForm);