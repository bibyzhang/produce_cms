window.TouchSliderBox=(function(){
	var stack=[],
		ready=false,
		SliderBox,
		fix=function(){
			SliderBox=function(box){
				
				
				
				this.container=typeof box=='string'?this.$(box):box;
				try{
					var mobileul=document.getElementById('mobileul'),ul=this.container.getElementsByTagName('ul')[0],
						lis=ul.getElementsByTagName('li');
					this.index=0;
					this.imgNum=lis.length;
					var lenght=0;
					for(i=0;i<this.imgNum;i++){
						lenght+=lis[i].offsetWidth+parseInt(this.css(lis[i],'margin-left'))+parseInt(this.css(lis[i],'margin-right'))+1;
						}
					/*this.imgWidth=lis[0].offsetWidth+parseInt(this.css(lis[0],'margin-left'))+parseInt(this.css(lis[0],'margin-right'));
					ul.style.width=this.imgNum*this.imgWidth+'px';*/
					ul.style.width=lenght+'px';
					this.setup();
					var currentLenghtF=0,currentLenghtB=0; 
					var num=getChildrenIndex(document.getElementsByClassName('current').item(0));
					
					function getChildrenIndex(ele){  
						//IE is simplest and fastest  
						if(ele.sourceIndex){  
								return ele.sourceIndex - ele.parentNode.sourceIndex - 1;  
						}  
						//other browsers  
						var i=0;  
						while(ele = ele.previousElementSibling){  
								i++;  
						}  
						return i;  
					}
					document.getElementsByClassName('touchscrollvertical').item(0).remove();
					//var tswWidth=$('.touchscrollwrapper').width();
					var tswWidth=document.getElementsByClassName('touchscrollwrapper').item(0).offsetWidth;
					for(i=0;i<num;i++){
						currentLenghtF+=lis[i].offsetWidth+parseInt(this.css(lis[i],'margin-left'))+parseInt(this.css(lis[i],'margin-right'));
					}
					for(i=num-1;i<this.imgNum;i++){
						currentLenghtB+=lis[i].offsetWidth+parseInt(this.css(lis[i],'margin-left'))+parseInt(this.css(lis[i],'margin-right'));
						}
					if(document.body.clientWidth<lenght){
						var childUl=document.getElementById('mobileul');
						if(currentLenghtB<tswWidth){
							//childUl.parentNode.style.left=-(lenght-tswWidth);
							document.getElementsByClassName('touchscrollelement').item(0).style.left=-(lenght-tswWidth)+'px';
							//$('.touchscrollelement').css('left',-(lenght-tswWidth));
							}
						else{
							//childUl.parentNode.style.left=-currentLenghtF;
							document.getElementsByClassName('touchscrollelement').item(0).style.left=-currentLenghtF+'px';
							//$('.touchscrollelement').css('left',-currentLenghtF);
						}
					}
					//alert(currentLenghtF);
				}catch(e){
					this.error=e.message;	
				}
			}
			SliderBox.prototype=new TouchScroll({id:null,width:7,opacity:0.7,color:'#555',minLength:10,ondrag:function(flag,distance){
				if(flag===0){
					var index,
						offset=parseInt(this.element.style.left),
						_num=Math.round(this.clientWidth/this.imgWidth);
					if(offset>0)index=0;
					else if(offset<this.clientWidth-this.scrollWidth){
						index=this.imgNum;
					}else{
						index=Math.round(-offset/this.imgWidth);
						if(distance){
							index-=distance/Math.abs(distance);	
						}
					}
					if(index<=this.imgNum-_num){
						this.slideTo(index);
						return false;
					}
				}
			}});
			SliderBox.prototype.slideTo=function(index){
				var _num=Math.floor(this.clientWidth/this.imgWidth);
				if(index<0)index=0;
				else if(index>this.imgNum-_num)index=this.imgNum-_num;
				var offset=parseInt(this.element.style.left),
					finalOffset=-this.imgWidth*index;
				if(!offset && !finalOffset)finalOffset=40;
				this._scroll(0,finalOffset-offset);
				this.index=index;
			}
			SliderBox.prototype.prev=function(){
				this.slideTo(this.index-1);
			}
			SliderBox.prototype.next=function(){
				this.slideTo(this.index+1);
			}
			SliderBox.prototype.mouseScroll=function(e){
				/*
				this.preventDefault(e);
				this.msNow=new Date();
				if(this.msOld && this.msNow-this.msOld<100)return;
				e=e||window.event;
				var wheelDelta=e.wheelDelta || e.detail && e.detail*-1 || 0,
					flag;//这里flag指鼠标滚轮的方向，1表示向上，-1向下
				if(this.wrapper && wheelDelta){
					flag=wheelDelta/Math.abs(wheelDelta);
					this.slideTo(this.index-flag);
					this.msOld=this.msNow;
				}
				*/
			}
		}
	
	if(typeof window.TouchScroll == 'undefined'){
		var s=document.createElement('script');
		s.src="http://www..com/source/js/touchScroll.js";
		s.onload=s.onerror=s.onreadystatechange=function(){
			if(s&&s.readyState&&s.readyState!='loaded'&&s.readyState!='complete'){
				return;
			}
			s.onload=onerror=s.onreadystatechange=null;
			fix(); s.parentNode.removeChild(s); ready=true;
			while(stack.length){
				(stack.shift())();
			}
		}
		document.getElementsByTagName("head")[0].appendChild(s);
	}else{
		fix();
		ready=true;
	}
	
	return function(box,t){
		if(!t)t='sliderbox';
		if(!ready){
			stack.push(function(){
				this[t]=new SliderBox(box);
			});	
		}else this[t]=new SliderBox(box);
	}
})();