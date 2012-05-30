
/**===================**
  date:2011-11-07
  by:globalbreak@gmail.com
**===================**/
(function(w){
	var contxt = w.document,
		location = w.location,
	    Navigator = w.navigator,
		isIEtxt = '<style type="text/css">.isIE6{ background:#feb300; border-bottom:1px solid #dfdfdf; height:40px; line-height:40px; font-size:18px; font-weight:bold; color:#000; font-family:"微软雅黑"; padding:0 20px; text-align:center;width:100%;white-space:nowrap;}.isIE6 a{ color:blue;}.isIE6 a:hover{color:blue; text-decoration:underline;}</style><div class="isIE6">你目前使用的浏览器版本过低，将不能使用本站部分功能。请升级你的 <a href="http://windows.microsoft.com/zh-CN/internet-explorer/downloads/ie-8">Internet Explorer 浏览器</a>获取最佳的浏览效果。</div>';
		this.len = 0;
		this[0] = contxt.body;
		
	//ie检测	
	if(Navigator.appName === 'Microsoft Internet Explorer'){
		var e = Navigator.appVersion;
		var msIE = e.match(/MSIE \d\.\d/);
		
		if(msIE == "MSIE 6.0" || msIE == "MSIE 5.5"){
			var div = contxt.createElement("div");
				div.className = 'isIE';
			    div.innerHTML = isIEtxt;
			    this[0].appendChild(div);
				this[0].className ='pd';
		}
	}  

	
		
		
	var  myFun = function(arg){
			var that = this ;
		    return {
				_$id : function(){var el = arg[0]; if(typeof el == 'string'){return contxt.getElementById(el) } },
				
				each : function(o,myFun){
					for(var i = 0 ,len= o.length; i< len; i++){
						myFun.call(this,o[i],i);
					}
				},
				
				setStyle:function(el,prop,val){return el.style[prop] = val ;},
				
				show:function(el){return this.setStyle(el,'display','block');},
				
				hide:function(el){return this.setStyle(el,'display','none');},
				
				addEvent:function( el,e,fn ){
					
					if( window.addEventListener ){
						el.addEventListener( e,fn,false);
						
					}else if( window.attachEvent ){
						el.attachEvent('on'+e,fn);
					}
				},
				
				byClassName : function(str){
					
					var elements = [] ,j = 0;
					var tag = str == null ? '*' : str ;
					
					if(typeof tag == 'string'){
						element = contxt.getElementsByTagName(tag);
						this.each(element,function(el,i){
						
							if( el.className === arg[0] ){
								elements.push(el);
							}
						})
						return elements;
					}
					
				}
			}//end return 
		}//end myFun
	
	window['_$'] = function(){return new myFun(arguments);}
	
})(window);


_$(window).addEvent(window,"load",function(){
										   
	var txt ='搜索话题、问题或人...';
	 try{
		var el = _$("s_txt");
	 	var eletxt = el._$id();
		
		el.addEvent(eletxt,'focus',function(){elEvent(eletxt,txt,'');})
		el.addEvent(eletxt,'blur',function(){elEvent(eletxt,'',txt);})
	 }catch(exp){}
	 
	 //
	 
})

//焦点方法调用
function elEvent(el,s,e,newclass){
	var cl = el.className;
	var myclass = cl == null ? '': cl;
	
	if(el.value==s){
		el.value=e;
		
		el.className = newclass == null ? myclass : newclass;
	}
}



function onclickEvent(o,cur,on){
	var obj = 'string' === typeof o ? document.getElementById(o) : o ;
	
	obj.className = obj.className == cur ? on : cur;
}



$(document).ready(function ()
{	
	//自动完成
	$('#global_search').each(function (i)
	{
		var obj_type = $(this).attr('event_type');
		var obj_self = $(this);
		
		$(this).autocomplete(G_BASE_URL+'/search/?act=all&type=' + obj_type, {
			multiple : true,
			cacheLength : 20,
			max : 100,
			dataType : "json",
			parse : function(data) {
				return $.map(data, function(row) {
					return { data: row, value: row.name, result: row.url }
				});
			},
			event_type : obj_type,
			formatItem : function(item)
			{
				if(item.type == 1)
				{
					name_tip = '<span class="item_type">[问题]</span>';
				}
				if(item.type == 2)
				{
					name_tip = '<span class="item_type">[话题]</span>';
				}
				if(item.type == 3)
				{
					name_tip = '<span class="item_type">[会员]</span>';
				}
				return "<a href='"+ item.url +"'>" + item.name + name_tip + "</a>";
				//return "<a href='"+ item.url +"'><img class=\"atcom_item_img\" src='"+ item.pic +"' />&nbsp;" + item.name + name_tip + "</a>";
			}
			
			}).result(function(e, item)
			{
				parse_result(obj_self, obj_type, e, item);
			});
		});
});

//解析搜索结果内容
function parse_result(obj_self, obj_type, e, item)
{
	switch (obj_type)
	{
		case 'header_all':
		case 'all':
			obj_self.val(item.name);
			window.location.href = item.url;
			break;
		case 'user':
			obj_self.val(item.name);
			break;
		case 'invite_user':
			obj_self.val(item.name);
			
			//add_invite_user(item);
			break;
		case 'topic':
			obj_self.val(item.name);
			break;
		case 'question':
			obj_self.attr('event_result_id', item.sno);
			obj_self.val(item.name);
			break;
	}
}


//页面统计获取数据
function notifications()
{
	var url = G_BASE_URL+'/index/?c=ajax&act=notifications&rnd=' + Math.random();
	
	$.get(url, function (result) {
			if(Number(result.rsm.notifications_num) > 0)
			{
				$('#notifications_num').removeClass('hide');
				$('#notifications_num').html(result.rsm.notifications_num);
				
				if ($('#announce_num'))
				{
					$('#announce_num').html(result.rsm.notifications_num);
				}
			}
			else
			{
				$('#notifications_num').addClass('hide');
				
				if ($('#announce_num'))
				{
					$('#announce_num').html(0);
				}
				
				if ($('#notitile_all'))
				{
					$('#notitile_all').hide();
				}
			}
			if (Number(result.rsm.inbox_num) > 0)
			{
				$('#inbox_num').show();
				$('#inbox_num').html(result.rsm.inbox_num);
			}
			else
			{
				$('#inbox_num').hide();
			}
			
			var unread_num = Number(result.rsm.notifications_num) + Number(result.rsm.inbox_num);
			
			if (unread_num > 0)
			{
				$('#unread_num').show();
				$('#unread_num').html(unread_num);
			}
			else
			{
				$('#unread_num').hide();
			}
			
			/*if($('#invitation_available') && result.rsm.invitation_available != 0){
				$('#invitation_available').html(result.rsm.invitation_available);
				$('#invitation_available').show();
			}*/
			
	},'json');
}

function count_unread_count()
{
	var notifications_num = Number($('#notifications_num').html());
	var inbox_num = Number($('#inbox_num').html());
	var unread_num = notifications_num + inbox_num;
	
	if (unread_num > 0)
	{
		$('#unread_num').show();
		$('#unread_num').html(unread_num);
	}
	else
	{
		$('#unread_num').hide();
	}
}

//计时器
setInterval('notifications()', 100000);

$(document).ready(function () {
	notifications();

	// Date picker
	$('input.date_picker').date_input();
	
	$("a[rel=lightbox]").fancybox({
		maxWidth	: ($(window).width() * 0.8),
		maxHeight	: ($(window).height() - 0.8),
		fitToView	: false,
		width		: '80%',
		height		: '80%',
		autoSize	: true,
		closeClick	: true,
		arrows		: false,
		keys		: false
	});
});

//ajax缓存
var ajax_date = [];

//竞赛_我的方案

var strClick_show = function(){};	

strClick_show.prototype.edit_inputs = function( str ){
	
	var _this = this;
	this.editDiv  = document.getElementById( str );
	
	this.editDiv['onfocus'] = this.i_focus;
	this.editDiv['onblur'] = this.i_blur;
	this.editDiv['onkeydown' &&'onkeyup'] = function(event){
		_this.i_keyUp(this,_this,event);
	}
	this.elValues =[];
}

//竞赛 我的方案 历史记录 操作
strClick_show.prototype._histroyClick = function( obj,inpTips ){
	
	var parentNodes = obj.parentNode.getElementsByTagName('div')[0],ar=[];
	var elements = parentNodes.getElementsByTagName('p'),_this = this;
	this.gid(inpTips).className = 'user_teamLister hide';
	
	if(obj.className == 'str_click'){
		
		obj.className = 'str_click cur';
		parentNodes.className = 'i_teamLister';
		/*if(this.editDiv.value.replace(/\s/g,'').length >1 && this.editDiv.value !='限站内用户(用空格隔开)'){
						
			ar.push(this.editDiv.value);
		}*/

		for(var i =-1,len =elements.length; ++i<len;){
			
			(function(i){
			
				var el = elements[i];
				el['onclick'] = function(){
					
					
					var tagA = el.getElementsByTagName("a");
					for(var k =-1,len =tagA.length; ++k<len;){
						
						ar.push(tagA[k].rel);
						_this.editDiv.setAttribute("value",ar);
						_this.editDiv.value = ar;
					}
					
					obj.className = 'str_click';
					parentNodes.className = 'i_teamLister hide';
				}
				
			})(i)
		}
		
	}else if(obj.className == 'str_click cur'){
		
		obj.className = 'str_click';
		parentNodes.className = 'i_teamLister hide';
	}

	
	
	document.onclick = function(event){
	
		var event = event ? event : window.event;
		var trg = event.target || event.srcElement;
		
		if(trg.nodeName !="A" && trg.nodeName !="P"){
		
			obj.className = 'str_click';
			parentNodes.className = 'i_teamLister hide';
			document.onclick = null;//销毁事件
		}
		
	}
	
	
	
}

strClick_show.prototype.changes_Click = function( obj,str ){
	
	var clickshowDiv = document.getElementById(str);
	if(obj.getAttribute("value")=='personal'){
	
		clickshowDiv.className = 'i_divUserInpt hide'
		
	}else if(obj.getAttribute("value")=='teams'){
	
		clickshowDiv.className = 'i_divUserInpt'
	}
}



strClick_show.prototype.i_focus = function(){
	
	if( this.value =='限站内用户(用空格隔开)'){
		
		this.value ='';
	}
}

strClick_show.prototype.i_blur = function(){
	
	if( this.value ==''){
		
		this.value ='限站内用户(用空格隔开)';
	}
}

strClick_show.prototype.i_keyUp = function(str,obj,e){
	
	var listerTeam = str.parentNode.getElementsByTagName('div')[0];
	var elements = listerTeam.getElementsByTagName('p'),strValue = str.value.replace(/\s/g,'');
	var e = e ? e : window.event;
	
	if(strValue ==''|| strValue ==',' || e.keyCode =='188'){
		
		listerTeam.className = 'user_teamLister hide'
	}else{
		
		listerTeam.className = 'user_teamLister';

		
		var objVALUES = strValue.charAt(strValue.length-1);
		this.elValues.push(objVALUES);
		
		if(objVALUES !='' && objVALUES !=','){
			
			var url = G_BASE_URL+'/search/?act=search_user&q='+encodeURIComponent(objVALUES)+'&limit=10';
			obj.XHRAjax(url,listerTeam);
		}
	
	}

	document.onclick = function(event){
	
		var event = event ? event : window.event;
		var trg = event.target || event.srcElement;
		
		if(trg.nodeName !="A" && trg.nodeName !="P"){
		
			listerTeam.className = 'user_teamLister hide';
			document.onclick = null;//销毁事件
		}
		
	}
}

strClick_show.prototype.XHRAjax = function( url,strObj ){
					
	var xhr,docm = document,elementsArr =[];
	
	try{
		strObj.innerHTML='<img src="' + G_STATIC_URL + '/css/img/load.gif"> 正在加载，请稍后...';
		xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("microsoft.XMLHTTP");
		xhr.open("post",url,true);
		xhr.onreadystatechange = function(){
		
			if( xhr.readyState == 4 && xhr.status == 200 ){
		
				var xml = new Function('return'+xhr.responseText)();
				if(xml!=null && xml!=''){
					for(var i =-1,len =xml.length; ++i<len;){
						
						var elment,el = xml[i];
							elment = '<a href="javascript:;" id="'+el.uid+'" rel="'+el.name+'" onclick="elementClickEvent.addElementValue(this);">'+el.name+'</a>'
							elementsArr.push(elment);
							
					}
					strObj.innerHTML = elementsArr.join('');
				}else{
					
					strObj.innerHTML ='sorry！没有查询到你要的团队.';
				}
			}
		};
		xhr.send(null);
		
	}catch(e){
		
		alert(e)
	}
}


strClick_show.prototype.addElementValue = function(str){
	
	
	var elementArr=[],historyArr ='',objValues = this.editDiv.value.replace(/\s/g,''),rels;
	if(objValues.length >1){ //split(',');
		
		historyArr = objValues;
		for(var i=-1,len=historyArr.split(',').length;++i<len;){
			
			
			if(historyArr.split(',')[i] != this.elValues.join('')){
				
				elementArr.push(historyArr.split(',')[i]);
			}
			if(historyArr.split(',')[i] == str.rel){	
				
				new DialogBox_show( '250', '不能重复添加！','提示','确定');
				return false;
			}
		}
		
	}
	elementArr.push(str.rel);
	
	this.editDiv.setAttribute("value",elementArr+',');
	this.editDiv.value = elementArr+',';
	
	if(str.parentNode.nodeName=='DIV'){
		
		str.parentNode.className ='user_teamLister hide';
		this.elValues=[];
		historyArr=''
	}
	
}

//个人中心
strClick_show.prototype.EveClick = function( o,n,obj ){
	
	this.elementHide(o,'layer','detaListerDiv hide');
	var markId = this.gid('layer'+n);
	obj.className = 'cur';
	markId.className = 'detaListerDiv';
	
	if(n==1 && markId.id =='layer1' || n==2 && markId.id =='layer2'){
		
		this.elements_Clicks(markId,n)
		
	}
}


strClick_show.prototype.Project_Tabs = function( a,b,c,d,n ){

	var e = [a.rel,b,c,d],g='',dc = document,
		f = 'uLister_proJect_tags|Lister_attentionPersonal_fh|Lister_attentionPersonal|Lister_attentionPersonal_fm',
		k = f.split("|");
		this.uLister = this.gid('uLister').getElementsByTagName('a');
	
	for(var i in e){
		
		var vk = e[i];
		for(var s in e){
			this.gid(e[s]).className ='hide';
		}
		
		switch(vk){
			case k[0]:
			this.tabs_mark(a,vk,n);
			return false;
			break;
			
			case k[1]:
			this.gid(vk).className ='';
			this.eliminate('');
			return false;
			break;
			
			case k[2]:
			this.gid(vk).className ='';
			this.eliminate('');
			return false;
			break;
			
			case k[3]:
			this.gid(vk).className ='';
			this.eliminate('');
			return false;
			break;
		}
		
	}
	
	this.hideLister(this.uLister);
}

strClick_show.prototype.children_Clicks = function( o,n,t ){
	
	var element = o[n] ,_this = this;
	element['onclick'] = function(){
		
		if(t==1){
			_this.elementHide(o,'layer_','oDivClassTabs hide');
			_this.gid('layer_'+n).className = 'oDivClassTabs';
		}else if(t==2){
			_this.elementHide(o,'layer_t','oDivClassTabs hide');
			_this.gid('layer_t'+n).className = 'oDivClassTabs';
		}
		element.className ='cur';
	}
	
}

strClick_show.prototype.elementHide = function( o,str,clas ){
	
	for(var i =-1,len=o.length;++i<len;){
		
		this.gid(str+i).className = clas;
		o[i].className = '';
	}
}

strClick_show.prototype.hideLister = function(str){
	
	if(str!=null){
		
		for(var i =-1,len=str.length;++i<len;){
	
			str[i].className = '';
		}
		
	}
}

strClick_show.prototype.gid = function(o){
	var d = document,id;
	id = 'string' === typeof o ? d.getElementById(o) : o ;
	return id;
}

strClick_show.prototype.tabs_mark = function( o,str,n ){
	
	var elementDiv = this.gid(str);
	var elementH2 = this.uLister;
	
	elementDiv.className ='';
	this.EveClick(elementH2,n,o);
	if(n >= 3 && this.total_Integral!='function'&& this.total_Integral!=null ){
		this.total_Integral();
		
	}
	
}

strClick_show.prototype.eliminate = function(str){
	
	
	for(var i =-1,len=this.uLister.length;++i<len;){
		
		this.uLister[i].className = str;
	}
	
}

strClick_show.prototype.elements_Clicks = function( o,n ){
	
	var mark_ul = o.getElementsByTagName('ul')[0];
	var omg_li = mark_ul.getElementsByTagName('li');
	
	for(var i =-1,len=omg_li.length;++i<len;){
		
		this.children_Clicks(omg_li,i,n);
	}
	
}

//竞赛 展开操作
strClick_show.prototype.Click_spread = function( a,b,c,d,e,fn ){
	
	var f = a.parentNode,g=true;
	
	if(g){
		a.className = a.className != b ? b : d ;
		f.className = f.className != c ? c : e ; 
		g = false;
	}
	
	//回调
	if(fn!=null && !g){
		fn(); 
	}
}	

strClick_show.prototype.c_funTXT = function( a,b,c ){
	
	a.title = a.title != b ? b : c;
}

strClick_show.prototype.c_delSeif = function(str){
	
	str.style.display = 'none';
}


//积分 
strClick_show.prototype.total_Integral = function(){

	
	if(this.gid('totat_percentum')!=null){
		
		var totat_percentum = this.gid('totat_percentum');
		this.setAjax(totat_percentum);
	}
	
};

strClick_show.prototype.setAjax = function(id){
	
	
	var totatTum=[],parameter=0,inner='';
	var _this = this,xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("microsoft.XMLHTTP");
	
		xhr.open("post",G_BASE_URL+'/people/?c=ajax&act=user_integral_ratio_json&uid='+USER_ID,true);
		xhr.onreadystatechange = function(){
				
			if( xhr.readyState == 4 && xhr.status == 200 ){
				
				var elements = new Function('return'+xhr.responseText)();
				
				for(var i in elements){
					
					if(elements[i].ratio >0){
						
						totatTum.push('<li><span class="tl_percentum">'+elements[i].name+'：</span><p class="shell_mark"><span class="progressBar" style="width:'+elements[i].ratio+'%;"></span><em class="cQ_Percentum">'+elements[i].ratio+'%</em></p></li>');
						 
					}else{
						
						totatTum.push('<li><span class="tl_percentum">'+elements[i].name+'：</span><p class="shell_mark delTum"><span class="progressBar" style="width:'+elements[i].ratio+'%;"></span><em class="cQ_Percentum">'+elements[i].ratio+'%</em></p></li>') 
						
					}
				}
				id.innerHTML = totatTum.join('');
				totatTum=[];
			}
			
		}
		xhr.send(null);

	
};



//我的方案 赞同判断
strClick_show.prototype.schemeAgree = function(id){
	
	$(".spread").each(function(i){
		
		var element = $(".spread").eq(i);
		if(element.innerHeight() > 20 && element.parent().attr('class') =='Endorse'){
			
			element.parent().addClass('hide_end');
			if(element.parent().find('a').attr('class') == 'Click_moreErose'){
			
				element.parent().find('a').attr("onclick","elementClickEvent.Click_spread(this,'Click_moreErose','Endorse hide_end','Click_moreErose Click_spread','Endorse Endorse_Spread',elementClickEvent.c_funTXT(this,'查看更多>>','收起>>'))");	
			}
			
		}
	})
}

//返回顶部
strClick_show.prototype.return_Top = function(){
	
	var R = document.documentElement.scrollWidth || document.body.scrollWidth ;// safari firefox IE
	
	var  a = document.createElement('a');
		 a.href='javascript:;'; 
		 a.title='返回顶部';
		 a.className = 'return_Top hide';
		 a.setAttribute('onclick',' $("html,body").animate({scrollTop:0},"slow");');
		 a.style.left = (R-960)/2 +960 +'px';
		 document.getElementsByTagName('body')[0].appendChild(a);
		
	window.onscroll = function(){
	
		var Top = document.documentElement.scrollTop || document.body.scrollTop; 
		Top > 0 ? $(a).fadeIn('slow') : $(a).fadeOut('slow');
	}
}

function returnAddEvent(){
	
	var strClick_shows = new strClick_show();
	strClick_shows.return_Top();
}

window.addEventListener ? window.addEventListener('load',returnAddEvent,false) : window.attachEvent('onload',returnAddEvent);



//站内分享
strClick_show.prototype.personalLetter = function(){
	
	var _thi = this;
	var title = '<a href="javascript:;" onclick="hidePupBox(\'w_tagPupD\',\'w_mask\');" class="close_ti" title="点击关闭对话框 ">close</a>分享';
	             
				 //tab切换
	this.html = '<ul id="menu_s">'+
					'<li class="cur" onclick="elementClickEvent.tabs(this,0);" rel="weibo">微博分享</li>'+
					'<li  onclick="elementClickEvent.tabs(this,1);" rel="mail">邮件分享</li>'+
					'<li  onclick="elementClickEvent.tabs(this,2);" rel="letter">站内信分享</li>'+
					'</ul>'+
					
					//微博分享
				'<div id="weibo_share" class="class_share">'+
					'<p class="sc-wb"><a title="新浪微博" href="'+G_BASE_URL+'/account/?c=sina&amp;act=binding"><img src="' + G_STATIC_URL + '/css/img/icoSina.gif">绑定新浪微博帐号</a></p>'+
					'<p class="sc-wb"><a title="腾讯微博" href="'+G_BASE_URL+'/account/?c=qq&amp;act=binding"><img src="' + G_STATIC_URL + '/css/img/share_tx_wb.gif">绑定腾讯微博帐号</a></p>'+
					'<p class="p">绑定微博之后，可以方便把精彩问答分享到该微博，并可以邀请你的微博好友来回答该问题或者加入 '+G_SITE_NAME+' </p>'+
					'<p class="sc-wb">已经绑定新浪微博账户：<a href="#">@stank</a></p><textarea class="txt_area" style="width:430px;"></textarea>'+
					'<p class="tr"><a href="javascript:;" onclick="hidePupBox(\'w_tagPupD\',\'w_mask\');">取消</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>'+
				'</div>'+
				
					//邮件分享
				'<div class="class_share hide" id="mail_share"><ul class="txt_list">'+
					'<li><label for="inputsType_txt">收件人：</label><input type="text" name="" class="txt_input"/></li>'+
					'<li><label for="inputsType_area">内容：</label><textarea class="txt_area"></textarea></li>'+
				'</ul><p class="tr"><a href="javascript:;" class="set_msg">发送</a><a href="javascript:;" onclick="hidePupBox(\'w_tagPupD\',\'w_mask\');">取消</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p></div>'+
				
					//站内分享
				'<div class="class_share hide" id="letter_share"><ul class="txt_list">'+
					'<li><label for="inputsType_txt">发给：</label><input type="text" name="" class="txt_input" id="userShare_txt"/><div class="ajax_date hide"></div></li>'+
					'<li><label for="inputsType_area">内容：</label><textarea class="txt_area"></textarea></li>'+
				'</ul><p class="tr"><a href="javascript:;" class="set_msg">发送</a><a href="javascript:;" onclick="hidePupBox(\'w_tagPupD\',\'w_mask\');">取消</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p></div>';
	
	new DialogBox_show('500',this.html,title,'',function(){
		
		_thi.gid('w_tagPupD').style.marginTop='-130px';
	});
}


//弹出框tab切换
strClick_show.prototype.tabs = function(o){
	
	var li = o.parentNode.getElementsByTagName('li');
	var a = o.getAttribute('uid');
	
	for(var i=-1,len=li.length;++i<len;){
		
		if(i==a && typeof o.getAttribute('rel')=='string' && o.getAttribute('rel')!=null){
			
			var id = this.gid(li[i].getAttribute('rel')+'_share');
			id.className ='class_share';
			li[i].className ='cur';
			
		}else{
			
			var id = this.gid(li[i].getAttribute('rel')+'_share');
			id.className ='class_share hide';
			li[i].className ='';
		}
		
	}
	
	if(o.getAttribute('rel')!=null && o.getAttribute('rel')=='letter'){
		
		this.letter.select_letters();
	}
}

//私信查询
strClick_show.prototype.letter = {
	
	//userShare_txt
	//var url = '/search/?act=search_user&q='+encodeURIComponent(objVALUES)+'&limit=10';
	getTag : function(event){
		
		var e = event ? event : window.event,tag = e.srcElement || e.target;
		return tag;
	},
	
	G : new strClick_show(),
	
	CacheClass:[],
	
	//window tips 发起问题
	moudleTips_start:function(){
	
		//弹出框
		this.G.letter.moudle_box().moudle_show_addDOM();
		
		//添加话题
		//this.G.letter.default_Addtopic();
		
		//事件聚焦
		//this.G.letter.default_eventFocus();
	},
	
	setClassName :function(o,c){
		
		if(typeof o === 'object'){
			for(var i in c){
				o.className = c[i];
			}
		}
	},
	
	select_letters:function(){
		
		var _thi = this,ajax_date;
		if(elementClickEvent.gid('userShare_txt')!=null){
			
			var elemshare = this.elemshares = elementClickEvent.gid('userShare_txt');
			var node_list = this.node_list = elemshare.parentNode; //li
			if(node_list.getElementsByTagName('div')[0]){
				
				this.C = ajax_date = node_list.getElementsByTagName('div')[0];
				
			}
			
			node_list.style.cssText = 'position:relative;z-index:10;width:360px;';
			//node_list.appendChild(div);
			
			//输入查询用户
			elemshare['onkeydown'&&'onkeyup'] = function(){
				
				var thsVal = this.value.replace(/^\s+/,'').replace(/\s+/g,'');	
				
					if(thsVal.length !=0){
						_thi.setAjax(thsVal);
					}
			}
			
			
			document.onclick = function(event){
	
				if(_thi.getTag(event).nodeName !="INPUT" && _thi.getTag(event).nodeName !="P"){
					ajax_date.className = 'ajax_date hide';
					//document.onclick = null;//销毁事件
				}
				
			}
			
		}
	},
	
	setAjax:function(val){
		
		
		var XHR,docm = document,elementsArr =[],_ths=this;
		var url = G_BASE_URL+'/search/?act=search_user&q='+encodeURIComponent(val)+'&limit=10';
		this.C.className='ajax_date';
		 
		try{
			_ths.C.innerHTML='<img src="' + G_STATIC_URL + '/css/img/load.gif"> 正在加载，请稍后...';
			
			XHR = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("microsoft.XMLHTTP");
			XHR.open("post",url,true);
			XHR.onreadystatechange = function(){
			
				if( XHR.readyState == 4 && XHR.status == 200 ){
				
					var XML = new Function('return'+XHR.responseText)();
					
					if(XML!=null && XML!=''){
						for(var i =-1,len =XML.length; ++i<len;){
							
							var elment,el = XML[i];
								elment = '<p id="'+el.uid+'" rel="'+el.name+'" onclick="elementClickEvent.letter.elem_add(event);">'+el.name+'</p>'
								elementsArr.push(elment);
								
						}
						
						_ths.C.innerHTML = elementsArr.join('');
					}else{
						
						_ths.C.innerHTML ='<p>sorry！没有查询到你要的用户.</p>';
					}
				}
			};
			XHR.send(null);
			
		}catch(e){
			 
			alert(e)
		}
	},
	
	elem_add : function(event){
		
		if(this.getTag(event).nodeName ==='P'){
		
			
			var rel = this.getTag(event).getAttribute('rel');
			var s = document.createElement('a');
			this.elemshares.className = 'txt_input hide';
			
				s.className = 'user';
				s.setAttribute('onclick','elementClickEvent.letter.removeObj(this,event);');
				s.setAttribute('rel',rel);
				s.title ='点击修改';
				s.href='javascript:;';
				s.innerHTML = rel+'<em rel='+rel+'>修改</em>';
				this.elemshares.value = rel;
			
			this.node_list.appendChild(s);
			this.C.className = 'ajax_date hide';
		}
	},
	
	removeObj:function(x,event){

		
		if(this.getTag(event).nodeName === 'A' || this.getTag(event).nodeName === 'EM'){
			
			var val = this.getTag(event).getAttribute('rel');
			
			this.elemshares.className = 'txt_input';//text框
			this.elemshares.value = val;
			this.elemshares.setAttribute("value",val);
			if(this.getTag(event).nodeName === 'EM'){
				
				this.node_list.removeChild(this.getTag(event).parentNode);//A
			}else{
				this.node_list.removeChild(this.getTag(event));//A
			}
			this.elemshares.select();
		}
		
	},
	
	moudle_box:function(){
								
								
		var moudle_staticHTML = '<div class="default_addQuestion">'+
								'<form action="' + G_BASE_URL + '/publish/?act=question" method="post" id="quick_publish" onsubmit="return false">' + 
								//添加内容输入框
								'<div class="default_replenish"><input type="text" name="question_content"  class="default_Question_txt" value=""/></div>'+
		
								//问题补充
								'<div class="default_replenish"><p class="defa_qsadd" onclick="elementClickEvent.letter.default_AddQs(event);">问题补充<span class="defa_s">可选</span></p><div class="default_txtDiv hide"><textarea class="default_Question_textarea" name="question_detail"></textarea></div></div>'+
								
								//选择分类
								'<input type="hidden" name="category_id" value="0" id="category_id" /><div class="default_replenish"><p id="defa_qsadd" class="defa_qsadd" onclick="elementClickEvent.letter.EveDefaultHide(event);"><span class="defa_aor">箭头</span><span id="add_class_txt">选择分类</span></p><ul class="default_class_list hide"><li><img src="' + G_STATIC_URL + '/css/img/load.gif"> 正在加载，请稍后...</li></ul></div>'+
								
								//添加话题
								//'<div class="default_replenish add_topic_v1"><div class="add_topic_class hide" id="add_topic_class"></div><input type="text" name="" id="default_addTopic" class="default_addTopic" style="border:0 none;" value="添加话题"/></div><div style="position:relative;"><div class="default_ajax_deta hide" id="default_ajax_deta"></div><div id="tips" class="tips_tak hide"></div></div>'+
								
								//取消
								'<div class="default_replenish default_exit"><p class="f_default_exit"><a href="javascript:;" class="exit"  onclick="hidePupBox(\'w_tagPupD\',\'w_mask\');" title="取消添加">取消</a>&nbsp;&nbsp;<a href="javascript:;" class="default_blue_but" onclick="ajax_post($(\'form#quick_publish\'), _ajax_post_alert_processer); return false;">确定</a></p><!--<input type="checkbox" id="default_toPic_txt"/><label for="default_toPic_txt">&nbsp;匿名添加话题 </label>--><a href="#" onclick="$(\'form#quick_publish\').attr(\'action\', G_BASE_URL + \'/publish/\');document.getElementById(\'quick_publish\').submit(); return false;">高级模式</a></div>'
								'</div>' + 
								
								'</form>'; 
								
		var moudle_title = '<a title="点击关闭对话框 " class="close_ti" onclick="hidePupBox(\'w_tagPupD\',\'w_mask\');" href="javascript:;">close</a>发起问题';
		
		return{
			
			moudle_show_addDOM:function(){
				
				var x = new DialogBox_show('500',moudle_staticHTML,moudle_title,'',function(){
					
					var w_tagPupD = document.getElementById('w_tagPupD');
						w_tagPupD.style.marginTop = '-130px';
						w_tagPupD.style.position = 'absolute';
				});
			},
			e:'test'
		}
		
		
	},
	
	
	//话题首页头像编辑按钮显隐
	default_Evs :function(e){
		
		var default_id = this.G.gid('default_upLoad');
		
		if(this.getTag(e).nodeName.toLowerCase() ==='img' || this.getTag(e).nodeName.toLowerCase() ==='li'){
		
			
			var imgs = this.getTag(e);
			var imgs_li = imgs.parentNode;
			var elments = imgs_li.getElementsByTagName("a")[0];
			
				elments.className = 'editor_htFace';
				//default_id.style.top = pseInt(default_id.style.top) + 60 +'px';
			
			var Ev_defaultEvents = function(){
					
				elments.className = 'editor_htFace hide';
						
			}
			
			default_id['onmouseout'] = Ev_defaultEvents;
			
		}
		
	},
	
	//匿名身份操作
	
	default_anonymity : function(e){
		
		var _ths = this;
		if(this.getTag(e).nodeName.toLowerCase() ==='a'){
			
			var el = this.getTag(e).parentNode;
			var default_el = el.getElementsByTagName('p')[0];
			default_el.className = default_el.className == 'default_setPosition hide' ? 
			default_el.className = 'default_setPosition' : default_el.className = 'default_setPosition hide';
			
			var default_EvClick = document.onclick = function(e){
				
				if(_ths.getTag(e).nodeName.toLowerCase() !='a' && _ths.getTag(e).nodeName.toLowerCase() !='p'){
					
					default_el.className = 'default_setPosition hide';
					default_EvClick = null;//销毁事件
				}
			}
		}
		
	},
	
	//站内邀请
	default_invite : function( _v2x ){
		
		var default_id = this.G.gid('default_invite'),
			_ths = this,
			default_fun = this.default_seach;
			this.default_nums = _v2x.getElementsByTagName('span')[0];
			
			default_id.className = default_id.className == 'hide' ? 
								   (function(){
								   
										default_id.className = ''
										default_fun(_ths);
										
									})()
									 : default_id.className = 'hide';
		
			
	},
	
	//default_question
	default_question : function(obj,str,s){
		
		var el = obj.parentNode.parentNode;
		var a = el.getElementsByTagName('a');
		
		for(var i=-1,len=a.length;++i<len;){
			
			this.G.gid('layer_'+i).className = (i==str) ? s :s+' hide';
			a[i].className = (i==str) ? 'cur' :'';
		}
		
		
	},
	
	//公用显示更多操作
	default_more : function(e){
			
		var eventTag = this.getTag(e)	
		if(eventTag.nodeName.toLowerCase() === 'a' && eventTag.getAttribute('class') =='b'){
			
			eventTag.className = 'b cur';
		}else{
			
			eventTag.className = 'b';
		}
		
	},
	
	//站内用户搜索
	default_seach:function( obj ){

		if(obj.G.gid('default_seach_txt')!=null){
			
			var seach_input = obj.G.gid('default_seach_txt');
			var default_query = obj.G.gid('default_ajax_query');
			
				seach_input['onkeydown' &&'onkeyup'] = function(){
					
					var txtValue = seach_input.value.replace(/^\s+/,'').replace(/\s+/g,'');
					
					
					if(txtValue.length < 1){
						default_query.className = 'default_class_query hide';
					}else{
					
						var url = G_BASE_URL+'/search/?act=search_user&q='+encodeURIComponent(txtValue)+'&limit=10';
						default_query.className = 'default_class_query';
						default_query.innerHTML='<p class="default_dateLister"><img src="' + G_STATIC_URL + '/css/img/load.gif"> 正在加载，请稍后...</p>';
						obj.createXHR(url,default_query);
						
						//
						document.onclick = function(e){
							
							var event_tag = obj.getTag(e) , event_Case = event_tag.nodeName.toLowerCase();
							
							if(event_tag.getAttribute('id') !='default_ajax_query'&&
							   event_Case != 'p'    &&
							   event_Case != 'a'    &&
							   event_Case != 'span' &&
							   event_Case != 'img'
							){
								
								default_query.className = 'default_class_query hide';
							}
						}
						
					}
					
					
				}
		}
	},
	
	//邀请站内用户
	default_addUser:function(default_obj){
	
		var default_a = document.createElement('a'),_v2 = this;
		var obj_default = this.G.gid('default_inbites');
		var _v3num = _v2.default_nums.innerHTML.replace(/^\s+/,'').replace(/\s+/g,'');
		var _v3 = Number(_v3num);
		
		var ev_name = default_obj.getAttribute('Udate'),
			imgs_url = default_obj.getAttribute('url'),
			default_query = default_obj.parentNode;
			
			var xmlHttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("microsoft.XMLHTTP");
				psotUrl = G_BASE_URL+'/question/?c=ajax&act=add_invite&question_id='+question_id+'&uid='+default_obj.getAttribute('uid');　
						　
				xmlHttp.open("post",psotUrl,true);　　　　
				xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");　　　　
				xmlHttp.onreadystatechange=function(){
							
					 if( xmlHttp.readyState == 4 && xmlHttp.status == 200 ){
								 
						var x = new Function('return'+xmlHttp.responseText)();
						if(x.errno == -2){
							var x_v2 = new DialogBox_show('200',x.err,'提示信息','确定');
							default_query.className ='hide';
							return false;
						}
						var imgs = '<img src="'+imgs_url+'" alt="'+ev_name+'" class="default_imgs" title="'+ev_name+'" />';
						var i_vorys = '<span class="default_select" title="取消邀请" onclick="elementClickEvent.letter.default_user(event,this);" uid="'+default_obj.getAttribute('uid')+'"></span><span  class="default_atten_nums">1</span>';
						default_a.className = 'default_user';
						default_a.innerHTML = imgs + i_vorys;
						obj_default.appendChild(default_a)
						default_query.className ='hide';	
						_v2.default_nums.innerHTML = _v2._v3_default_num = ++_v3;
						
					}
							
			};　
			　
			
			xmlHttp.send(null);
		
	},
	
	//站内邀请查询
	createXHR : function(url,x){

		var elementsArr =[],WIN_XHR = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("microsoft.XMLHTTP");
			WIN_XHR.open("post",url,true);
			WIN_XHR.onreadystatechange = function(){
				
				if( WIN_XHR.readyState == 4 && WIN_XHR.status == 200 ){
				
					var reps_Txt = new Function('return'+WIN_XHR.responseText)();
					
					if(reps_Txt!=null && reps_Txt!=''){
						for(var i =-1,len =reps_Txt.length; ++i<len;){
							
							var elment,el = reps_Txt[i];
								elment = '<p class="default_dateLister" onclick="elementClickEvent.letter.default_addUser(this);" Udate="'+el.name+'" url="'+el.detail.avatar_file+'" uid="'+el.sno+'">'+
									     '<a href="javascript:;" class="default_userhead"><img src="'+el.detail.avatar_file+'" title="'+el.name+'" alt="'+el.name+'"/></a>'+el.name+
									     '<span class="default_span">'+el.detail.signature+'</span></p>'
								elementsArr.push(elment);
								
						}
						
						x.innerHTML = elementsArr.join('');
					}else{
						
						x.innerHTML ='<p class="default_dateLister">对不起！没有查询到你要的用户.</p>';
					}
				}
			}
			WIN_XHR.send(null);
	},
	
	//取消邀请
	default_user :function(e,default_obj){
		
		var _ths = this;
		var xmlHttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("microsoft.XMLHTTP");
	　
		xmlHttp.open("post",'/question/?c=ajax&act=delete_invite&question_id='+question_id+'&uid='+default_obj.getAttribute('uid'),true);　　　　
		xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");　　　　
		xmlHttp.onreadystatechange=function(){
			
			 if( xmlHttp.readyState == 4 && xmlHttp.status == 200 ){
				 
				_ths.default_removeChild(e);
				_ths.default_nums.innerHTML = --_ths._v3_default_num;
			}
			
		};　
		　
		xmlHttp.send(null);
			
	},
	
	//发起问题选择分类
	
	EveDefaultHide:function(e){
		
		
		var evels = this.getTag(e),txtDiv,_v2= this;
		var add_class_txt = this.G.gid('add_class_txt');
		
		if(evels.nodeName == 'P' || 
		   evels.nodeName == 'SPAN'){
			
			if( evels.nodeName == 'SPAN'){
				txtDiv = evels.parentNode.parentNode;
				evels = this.getTag(e).parentNode;
				
			}else if(evels.nodeName == 'P'){
				txtDiv = evels.parentNode;
			}
			var evSpan = evels.getElementsByTagName('span')[0];
			var eltags = txtDiv.getElementsByTagName('ul')[0];
				
				eltags.className = eltags.className == 'default_class_list hide' ? 'default_class_list' : 'default_class_list hide';
				evSpan.className = evSpan.className == 'defa_aor' ? 'defa_aor cur' : 'defa_aor';
					
				//缓存
				if(this.CacheClass.length <=0){
					var _xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("microsoft.XMLHTTP");
						_xhr.open("post",G_BASE_URL + '/publish/?c=ajax&act=fetch_question_category',true);
						_xhr.onreadystatechange = function(){
						
							if( _xhr.readyState == 4 && _xhr.status == 200 ){
								
								var ClassList_txt = new Function('return'+_xhr.responseText)();
									
									if(ClassList_txt!=null && ClassList_txt!=''){
										for(var i=-1,len = ClassList_txt.length;++i<len;){
											
											var _v1 = ClassList_txt[i];
											
											_v2.CacheClass.push('<li udate="'+_v1.title+'" uid="'+_v1.id+'"><a href="javascript:;">'+_v1.title+'</a></li>');

										}
										
										
									}else{
										
										eltags.innerHTML = '<p>暂无相关数据</p>';
									}
									
									eltags.innerHTML = _v2.CacheClass.join('');
									setValue_v3();
									
							}else{
							
								eltags.innerHTML = '<li><img src="' + G_STATIC_URL + '/css/img/load.gif"> 正在加载，请稍后...</li>';
								
							}
						}
					_xhr.send(null);
					
					
					
				}else{
					
					eltags.innerHTML = _v2.CacheClass.join('');
					
				}
				
				//赋值
				function setValue_v3(){
					if(eltags.getElementsByTagName('li').length >=0){
						var element_li = eltags.getElementsByTagName('li');
						for(var i=-1,len=element_li.length;++i<len;){
											
							(function(i){
								var _$ = element_li[i];
									_$['onclick'] = function(){
													
									var udate = _$.getAttribute('udate');
										add_class_txt.innerHTML = udate+'<input type="hidden" name="'+_$.getAttribute('uid')+'" value="'+udate+'" />';	
										eltags.className = 'default_class_list hide';
										evSpan.className = 'defa_aor';
										if(_v2.G.gid('category_id')!=null){
											
											_v2.G.gid('category_id').value = _$.getAttribute('uid');
											_v2.G.gid('category_id').setAttribute('value',_$.getAttribute('uid'));
										}
									}
							 })(i)
						}
					}
				}					
				setValue_v3();	
				
		}
		
	},
	
	//发起问题-问题补充
	default_AddQs:function(e){
		
		var evels = this.getTag(e),tags,_ths = this;
		if(evels.nodeName == 'P' || 
		   evels.nodeName == 'SPAN'){
			
			var txtDiv = evels.parentNode;
			var eleTags = txtDiv.getElementsByTagName('div')[0];
			var textareaTags = eleTags.getElementsByTagName('textarea')[0];
				
				if(evels.nodeName == 'SPAN'){
					tags = evels.parentNode;
				}else if(evels.nodeName == 'P'){
					tags = evels;
				}
				eleTags.className = 'default_txtDiv';
				tags.className = 'defa_qsadd hide';
				textareaTags['onblur'] = function(){
					
					if(textareaTags.value=='' ||
					   textareaTags.value==null){
							
						eleTags.className = 'default_txtDiv hide';
						tags.className = 'defa_qsadd';
					}
				}
				
				document.onclick = function(e){
					
					var _ = _ths.getTag(e);
					var _tag = _.nodeName.toLowerCase();
					
					if(_.getAttribute('id') !=null &&
  					   _.getAttribute('id')=='defa_qsadd'){
						
						if(textareaTags.value=='' ||
						   textareaTags.value==null){
							
							eleTags.className = 'default_txtDiv hide';
							tags.className = 'defa_qsadd';
							document.onclick = null;
						}
					}else if(_tag != 'p'  && 
					         _ != textareaTags && 
					         _ !=eleTags ){
						
						if(textareaTags.value=='' ||
						   textareaTags.value==null){
							
							eleTags.className = 'default_txtDiv hide';
							tags.className = 'defa_qsadd';
							document.onclick = null;
						}
						
					}
				}

		}
	},
	
	//发起问题-添加话题-查询
	default_Addtopic : function(){
		
		var _ths = this,p_v1='',p_v2=[];
		if(this.G.gid('default_addTopic')!=null && 
		   this.G.gid('default_ajax_deta')!=null
		  ){
			
			var element_topic = this.element_topic = this.G.gid('default_addTopic');
			var element_ajaxID = this.element_ajaxID = this.G.gid('default_ajax_deta');
				
				element_topic.onfocus = function(){
					if(element_topic.value == '添加话题'){
						element_topic.value ='';
					}
				}
				
				element_topic.onblur = function(){
					
					var _v3 = _ths.G.gid('add_topic_class');
					var _v2 = _v3.getElementsByTagName('span');
					if(_v2.length <=0){
					
						if(element_topic.value == ''){
							element_topic.value ='添加话题';
						}
					}
					
				}
				
				element_topic['onkeydown' && 'onkeyup']= function(){
					
					var txtValues = element_topic.value.replace(/^\s+/,'').replace(/\s+/g,'');
					
					    if(txtValues!=null && txtValues!=''){
						
						
						var _xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("microsoft.XMLHTTP");
							_xhr.open("post",G_BASE_URL+'/search/?act=all&type=topic_v2&q='+txtValues+'&limit=100',true);
							_xhr.onreadystatechange = function(){
						
								if( _xhr.readyState == 4 && _xhr.status == 200 ){
								
									var x = new Function('return'+_xhr.responseText)();
									if(x!=null && x!=''){

										for(var i=-1,len= x.length; ++i<len;){
										  
										 
											if(i!=0){
												p_v2.push('<p class="ajax_lsit" onclick="elementClickEvent.letter.default_setAjax(this);" rel="'+x[i].name+'" uid="'+x[i].sno+'"><img src="'+x[i].detail.topic_pic+'" rel="'+x[i].name+'"/>'+x[i].name+'<span rel="'+x[i].name+'">'+x[i].detail.focus_count+'人关注</span></p>');
											}else if(i==0){
												if(x[i].exist == 1){
													p_v1 = '';
													
												}else if(x[i].exist == 0){
													p_v1 = '<p class="add_Topic" onclick="elementClickEvent.letter.default_setAjax(this);" id="add_Topic" uid="'+parseInt(Math.random()*1000)+'" rel="'+txtValues+'">添加<span>'+txtValues+'</span>话题</p>';
													
												}
											}
											
										}
										element_ajaxID.className = 'default_ajax_deta';
										element_ajaxID.innerHTML = p_v1 + p_v2.join('');
										p_v2=[];
									}else{
										
										element_ajaxID.className = 'default_ajax_deta';
										element_ajaxID.innerHTML = '<p class="add_Topic">对不起！没有查询到你要的数据。</p>';
									}
									
								}else{
									
									element_ajaxID.className = 'default_ajax_deta';
									element_ajaxID.innerHTML = '<p class="add_Topic"><img src="' + G_STATIC_URL + '/css/img/load.gif"> 正在加载，请稍后...</p>';
								}
							}
							_xhr.send(null);
							
						}else{
							element_ajaxID.className = 'default_ajax_deta hide';
							//element_ajaxID.innerHTML = '<p class="add_Topic">对不起！没有查询到你要的数据。</p>';
						}
				

					//
					document.onclick = function(e){
						
						if(_ths.getTag(e) != element_ajaxID){
							
							element_ajaxID.className = 'default_ajax_deta hide';
							element_topic.value = '';
							element_topic.setAttribute('value','');
							document.onclick = null;
						}
						
					}
				}
		}
		
	},
	
	//添加话题
	default_setAjax : function(e){
		
		
		var add_topic_class = this.add_topic_class = this.G.gid('add_topic_class');
		var top_v2 = this.G.gid('tips');
		var element_UID = e.getAttribute('uid');
		var _v0 = '<input type="hidden" name="topics[]" value="'+e.getAttribute('rel')+'"/>';
		var span = document.createElement('span');
			span.className ='s';
			span.setAttribute('uid',element_UID);
			span.innerHTML = e.getAttribute('rel')+'<em title="删除话题" onclick="elementClickEvent.letter.default_topChild(event);"></em>'+_v0;
			
		if(e.getAttribute('id') == 'add_Topic'){
			
			add_topic_class.appendChild(span);
			add_topic_class.className = 'add_topic_class';
			this.element_ajaxID.className = 'default_ajax_deta hide';
			this.element_topic.value = '';
			this.element_topic.setAttribute('value','');
			return false;
			
		}else{
		
			if(add_topic_class.getElementsByTagName('span').length <= 0){
				add_topic_class.appendChild(span);
				add_topic_class.className ='add_topic_class';
				return;
			}else{
				
				var element_top_span =  add_topic_class.getElementsByTagName('span');
				for(var i=-1,len = element_top_span.length; ++i<len;){
					
					var element_ID = element_top_span[i].getAttribute('uid');
					if(element_ID == element_UID){
						
						var top_v3 = '<p>请勿重复添加，谢谢！</p><a href="javascript:;" class="a" onclick="javascript:this.parentNode.className=\'tips_tak hide\';">确定</a>';
						top_v2.innerHTML = top_v3;
						top_v2.className = 'tips_tak';
						return false;
					}
					
 				}
				
				add_topic_class.appendChild(span);
				add_topic_class.className ='add_topic_class';
			}
			
			this.element_ajaxID.className = 'default_ajax_deta hide';
			this.element_topic.value = '';
			this.element_topic.setAttribute('value','');
		}
		
	},
	
	//话题input聚焦事件
	default_eventFocus : function(){
		
		var element_Div = this.element_topic.parentNode,e = this;
			
			element_Div['onclick' || 'onmousedown' || 'onmouseover'] = function(){
				
				e.element_topic.focus();
			}
	},
	
	default_removeChild : function(e){
		
		this.getTag(e).parentNode.parentNode.removeChild(this.getTag(e).parentNode);
	},
	
	default_topChild: function(e){
		
		//删除节点
		this.default_removeChild(e);
		
		var element_span = this.add_topic_class.getElementsByTagName('span');
		if(element_span.length <=0){
			this.add_topic_class.className ='add_topic_class hide';
		}
		
	},
	
	//个人主页切换1
	
	default_v2 : function(el){
		
		var el_v2 = el.parentNode.parentNode.getElementsByTagName('div')[0];
		
		el_v2.className = el_v2.className == 'display_hide' ? 'display_hide hide' : 'display_hide';
		el.title = el.title == '展开' ? '收起' : '展开';
		el.className = el.className == 'all_answer' ? 'all_answer cuur' : 'all_answer';
		
	},
	
	//个人主页切换2
	default_layer : function(item){
	
		for(var i =-1,len=4;++i<len;){
			
			i == item ? this.G.gid('default_person_layer'+i).className ='' : this.G.gid('default_person_layer'+i).className ='hide';
			
		}
	}
	
	
}

//none_bd cur 

strClick_show.prototype.clickSort= function(e){
	
	try{var default_id = this.gid('default_liste_module');}catch(w){}
	
	if(this.letter.getTag(e).nodeName.toLowerCase()=='span' && default_id!=null){
		
		var span = this.letter.getTag(e);
		
		var node_bd = span.parentNode;
		if(span.className!='default_arr'){
			
			span.title='展开 »';
			span.className = 'default_arr';
			node_bd.className='default_tal default_class';
			default_id.className='default_liste_module hide';
		}else{
			span.title='收起 »';
			span.className = 'default_arr cur';
			node_bd.className='default_tal default_class none_bd';
			default_id.className='default_liste_module';
		}
	}
}


function default_hide(){
	
	try{
		
		if(document.getElementById('default_edite')!=null){
			
			document.getElementById('default_edite').style.display='';
		}
		
	}catch(e){}
}


