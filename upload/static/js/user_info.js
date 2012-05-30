
/****************************
	====【用户信息】====
	by:globalbreak@gmail.com
		
****************************/


var d=document,
	usersinfo,              //用户、话题
	josnDate=[],            //缓存数据
	Quick_Tips;           //私信
	

window.onload = function(){

loadAddElements();

function _$userinfor(){
	
	this.d = document;
	this._$fb = function(o){return 'string' == typeof o ? this.d.getElementById(o) : o ;};                //获取ID
	this.seif ='';
	
	this.options = {
	
				users: this._$fb('user-opacity-tips'),                                                    //名片ID
			   ukMask: this._$fb('uk-mask'),                                                              //遮罩
			    fbPgk: this._$fb('fb-pgk'),
	  _$showClassName: 'user-opacity-tips',                                                               //
	  _$hideClassName: 'user-opacity-tips hide',
				  pos: 'static',
				   px: 'px',
				   _x: '',                                                                                 //获取元素左边距
				   _y: '',                                                                                 //上边距
			_soffsetX: '',
			_soffsetY: '',
				elObj: '',
			 userOffs: '',
					x: '',
					y: '',
				  E_C: 0 ,
		    Obj_width: 330
		
	}
}

//鼠标经过
_$userinfor.prototype._s = function(s,url,booleans){

	var _this = this;
	var R = document.documentElement.scrollWidth || document.body.scrollWidth ;// safari firefox IE
	
	//
	if(R-(s.getBoundingClientRect().left+this.options.Obj_width) < 100){
	
		this.options.E_C = 230;
		
		this.setCss(this._$fb('fb-span').style,{left:'auto',right:'50px'});
	}else{
		this.options.E_C = 0;
		this.setCss(this._$fb('fb-span').style,{left:'40px',right:'auto'});
	}
	this.options._x = s.getBoundingClientRect().left+(this.d.documentElement.scrollLeft+this.d.body.scrollLeft);
	this.options._y = s.getBoundingClientRect().top+(this.d.documentElement.scrollTop+this.d.body.scrollTop);
	if(s.offsetWidth <= 36 && s.offsetWidth > 30){
		this.options._soffsetX = s.offsetWidth;
	}else if(s.offsetWidth <= 30 && s.offsetWidth > 0){
		this.options._soffsetX = s.offsetWidth+(s.offsetWidth/2);
	}else{
		this.options._soffsetX = s.offsetWidth/2;
	}
	
	this.options._soffsetY = s.offsetHeight+10;
	this.options.elObj = s.getAttribute("rel");
	
	this.options.x = this.options._x - (this.options._soffsetX+this.options.E_C)+this.options.px;
	this.options.y = this.options._y + this.options._soffsetY +this.options.px;
	this.seif = s;
	
	this.setCss( this.options.users.style,{top:this.options.y,left:this.options.x} ); 
	
	if(this.options.fbPgk.className.replace(/\s/g,'') == 'fb-pgk'){
		
		this.xhrAjax(s,url,booleans);
	}
	
	this.setCss( this.options.users,{className:this.options._$showClassName} );
	this.setCss( this.options.ukMask.style,{height:'47px'});
	this.$docHide();
	
	s.onmouseout = function(){_this.$uHide()};
}


_$userinfor.prototype.EleAjaxHTML = function(elements){
	
		var ELAJAX_html='',AreaJobs='',Attentions,_this= this;;
		//用户姓名
		ELAJAX_html = '<div style="min-height:60px;"><h3 id="uk-user-name"><a href="'+ elements.url +'" title="点击进入'+elements.user_name+'的个人主页">'+ elements.user_name +'</a></h3>';
		
		//用户区域、职业
		if(elements.area != null && elements.job != null){
				
			AreaJobs = '<p><span id="uk-user-address">'+ elements.area +'</span> <span id="uk-user-job">'+ elements.job +'</span></p>'; 
			
		}else{
		
			if(elements.area == null && elements.job != null){

				AreaJobs = '<p><span id="uk-user-job">'+ elements.job +'</span></p>';
				
			}else if(elements.area != null && elements.job == null){
					
				AreaJobs = '<p><span id="uk-user-address" >'+ elements.area +'</span></p>';	
			}else{
				
				AreaJobs = '<p>这家伙很懒，什么也没留下。</p>';
			}
		}
		
		ELAJAX_html += AreaJobs;
		
		//用户签名
		ELAJAX_html += '<p class="fnCor" id="uk-user-txt" title="'+elements.signature+'">'+ elements.signature +'</p></div>' ;
		
		//用户头像
		ELAJAX_html += '<span  class="uk-img" id="uk-user-img" ><a href="'+ elements.url +'"><img src="'+ elements.avatar_file +'" alt="'+ elements.user_name +'" title="'+ elements.user_name +'"/></a></span>';
		
		//用户积分
		ELAJAX_html+='<div class="fk-uR">';
		//ELAJAX_html += '<div class="fk-uR"><span class="c0_1">积分：<em id="uk-user-integral">'+ elements.integral+'</em></span>';						

		if (USER_ID > 0) {
			//用户关注
			if(!elements.is_me){
				
				if(elements.focus){
			
					Attentions =  '<span class="c0_3" id="fb_Attention" uid="'+_this.options.elObj+'" onclick="Attention(this,true);" title="取消关注">取消关注</span>';
				}else{
					
					Attentions =  '<span class="c0_3" id="fb_Attention" uid="'+_this.options.elObj+'" onclick="Attention(this,true);" title="关注">关注</span>';
				}
				
				ELAJAX_html += Attentions;
				
				ELAJAX_html += '<span class="default_USER_info"><a href="javascript:;" style="color:#0f59b0;" onclick="Quick_Tips.info(this,\''+elements.user_name+'\');" title="发送私信">私信</a></span></div>';
				
			}else{
				ELAJAX_html +='<span style="margin-right:20px;">我自己</span></div>';
			}
			
			
		}
	
		
	var setTime = setTimeout(function(){
		
		_this.options.fbPgk.innerHTML = ELAJAX_html;
		_this.options.userOffs = _this.options.users.offsetHeight;
		_this.setCss( _this.options.ukMask.style,{height:_this.options.userOffs+_this.options.px});
		
		clearTimeout(setTime);
		
		
	},10);
}


_$userinfor.prototype.EleAjaxHTML_HT = function(elements){
	
	var x = this.options._x +this.seif.offsetWidth -((this.seif.offsetWidth/2)+this.options.E_C)-50;
	
	this.setCss( this.options.users.style,{top:this.options.y,left:x+'px'}); 
	var ELAJAX_html='',AreaJobs='',Attentions,_this= this;;
	
		//话题标题
		ELAJAX_html = '<div style="min-height:60px;"><h3 id="uk-user-name"><a href="'+G_BASE_URL+'/topic/?topic_title='+ elements.topic_title +'" title="点击进入'+elements.topic_title+'话题主页">'+ elements.topic_title +'</a></h3>';
		
		//话题描述
		if(elements.topic_description !=''){
		
			AreaJobs = '<p class="fnCor" id="uk-user-txt" title="'+elements.topic_description+'">'+ elements.topic_description +'</p></div>' ;
		}else{
			
			AreaJobs = '<p class="fnCor">没有任何关于此话题的相关描述</p></div>' ;
		}
		
		ELAJAX_html += AreaJobs;
		
		//话题logo
		ELAJAX_html += '<span  class="uk-img" id="uk-user-img" ><img src="'+ elements.topic_pic +'" alt="'+ elements.topic_title +'" title="'+ elements.topic_title +'"/></span>';
		
		//话题关注
		if (USER_ID > 0) {
			if(elements.focus){
			
				Attentions = '<div class="fk-uR"><span class="c0_3" id="fb_Attention" uid="'+_this.options.elObj+'" onclick="Attention(this,false);" title="取消关注">取消关注</span>';
			}else{
					
				Attentions =  '<div class="fk-uR"><span class="c0_3" id="fb_Attention" uid="'+_this.options.elObj+'" onclick="Attention(this,false);" title="关注">关注</span>';
			}
	
			ELAJAX_html += Attentions;
		}
		
		//话题关注人数
		ELAJAX_html += '<span style="width:200px;">已有 <em>'+elements.focus_count+'</em> 人关注该话题</span></div>';
		
	var setTime = setTimeout(function(){
		
		_this.options.fbPgk.innerHTML = ELAJAX_html;
		_this.options.userOffs = _this.options.users.offsetHeight;
		_this.setCss( _this.options.ukMask.style,{height:_this.options.userOffs+_this.options.px});
		
		clearTimeout(setTime);
		
		
	},10);
}
_$userinfor.prototype.xhrAjax = function(s,url,e){
					
	var _this= this;

	if(josnDate.length <=0){
		
		this.setAjax(url);
			
	}else{
		
		var rel= s.getAttribute("rel").replace(/\s/g,'');
		
		for(var i in josnDate){
			
			if(josnDate[i].uid == rel && e){
				
				_this.EleAjaxHTML(josnDate[i]);
				return false;
				
			}else if(josnDate[i].topic_id == rel && !e){

				_this.EleAjaxHTML_HT(josnDate[i]);
				return false;
			}
			
		}
    	this.setAjax(url);
	 	    
	}
	
	
}

_$userinfor.prototype.setAjax = function(url){
	
	var _this = this,xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("microsoft.XMLHTTP");
	
		xhr.open("post",url+this.options.elObj,true);
		xhr.onreadystatechange = function(){
						
			if( xhr.readyState == 4 && xhr.status == 200 ){
				
				var elements = new Function('return'+xhr.responseText)();
	
					
					
					if(elements.type == 'people'){
					
						_this.EleAjaxHTML(elements);	
						josnDate.push({
						
									"uid":elements.uid,                //用户ID           
							  "user_name":elements.user_name,          //用户名
							"avatar_file":elements.avatar_file,        //用户头像
									"url":elements.url,                //用户个人首页
								   "area":elements.area,               //用户地域
									"job":elements.job,                //用户职业
							  "signature":elements.signature,          //用户签名
							   "integral":elements.integral,           //用户积分
							"award_count":elements.award_count,        //获奖次数
								  "focus":elements.focus,              //关注
								  "is_me":elements.is_me,              //本人
								   "type":elements.type                //用户名片夹
						});
						
					}else if(elements.type =='topic'){
						
						_this.EleAjaxHTML_HT(elements);
						josnDate.push({
					
							 "topic_id":elements.topic_id,               //话题ID           
						  "topic_title":elements.topic_title,            //话题标题
					"topic_description":elements.topic_description,      //话题描述
							"topic_pic":elements.topic_pic,              //话题logo
					      "focus_count":elements.focus_count,            //关注人数
							    "focus":elements.focus,                  //关注
								 "type":elements.type                    //话题
						});
						
					}
				
			}else{
				
				_this.options.fbPgk.innerHTML = '请稍后，正在加载...';
				
			}
		};
		xhr.send(null);

}

_$userinfor.prototype.getEvent = function (event) {
        return event ? event : window.event;
    }
_$userinfor.prototype.getTarget = function (event) {
        return event.target || event.srcElement;
    }
_$userinfor.prototype.addHandler = function (element, type, handler) {
        if (element.addEventListener) {
            element.addEventListener(type, handler, false);
        } else if (element.attachEvent) {
            element.attachEvent("on" + type, handler);
        } else {
            element["on" + type] = handler;
        }
    }
_$userinfor.prototype.getRelatedTarget = function (event) {
        if (event.relatedTarget) {
            return event.relatedTarget;
        } else if (event.toElement) {
            return event.toElement;
        } else if (event.fromElement) {
            return event.fromElement;
        } else {
            return null;
        }
    }

_$userinfor.prototype.isMouseLeaveOrEnter = function( o,e){  //mouseout、mouseover兼容火狐的解决方案
 
    if (e.type != 'mouseout' && e.type != 'mouseover') return false;  
    var reltg = e.relatedTarget ? e.relatedTarget : e.type == 'mouseout' ? e.toElement : e.fromElement;  
    while (reltg && reltg != o)  
        reltg = reltg.parentNode;  
    return (reltg != o);  
}  


_$userinfor.prototype.$docHide = function(){
	
	var x = this;
	
	
	x.addHandler(this.options.users,'mouseout',function(event){
		
		if(x.isMouseLeaveOrEnter( x.options.users,event )){
		
			x.options.users.className = x.options._$hideClassName;
			x.options.users.style.top='0px';
			x.options.users.style.left='0px';
		}
	})
	x.addHandler(this.options.users,'mouseover',function(event){
		
		if(x.isMouseLeaveOrEnter( x.options.users,event )){
		
			x.options.users.className = x.options._$showClassName;
		}
	})
}

_$userinfor.prototype.$uHide = function( s ){
	
	if(this.options.userOffs !=''){
		
		this.setCss( this.options.users,{className:this.options._$hideClassName} );
	}
} 

_$userinfor.prototype.setCss = function( o,Class ){

	for(var k in Class){
		
		o[k] = Class[k];
		
	}
}



var __quickPopUp = function(){
	
		var el = this;
		this.d = document;
		this.$ = function(str){return  'string' == typeof str ? el.d.getElementById(str) : str};
		this.bd = this.d.getElementsByTagName('body')[0];
		var _$= el.$ , wordsNumber = 500;
		
		this.element = '';
		this.tagDiv = _$('tagDiv');              //弹出层
		this.worDageTarea = _$('worDageTarea');  //文本输入框内容
		this.exit = _$('pm_popup_exit');		 //取消
		this.uName = _$('userName');             //私信用户名
		this.wordage = _$('wordage');            //字数
		this.submit = _$('submit');              //提交
		this.addUser = _$('addUser');
		this.mouseEvent = 'keyup';
		this.org = _$('org_alpha');
		this.offset = 'scrollHeight'||'offsetHeight';
		this.intxt = window.addEventListener ? 'textContent' : 'innerText';
		this.editor = d.createElement("span");

		return {
			
				static : function( o,x,user ){
					
					var elementScrollheight;
							
								if(el.d.documentElement[el.offset] > el.d.body.scrollHeight){
									
									elementScrollheight = el.d.documentElement[el.offset];
								}else{
									
									elementScrollheight = el.d.body.scrollHeight;
								}
								
								el.org.style['height'] = elementScrollheight +'px';
								
								el.org.className = 'obg';
								
								el.tagDiv.className = 'tagDiv';
								var bsh = el.d.body.scrollHeight/2;
								var tsh = el.tagDiv[el.offset];
								
								x.setStyle(el.tagDiv,{'top':'15%'});
								
								x.mouseEve(x);
								
								if(user != null){
									
									var user_span = _$('addUser').getElementsByTagName('span');
									//防止多用户发送私信
									if(user_span!=null && user_span.length > 0){
										
										for(var i=-1,len = user_span.length;++i<len;){
											_$('addUser').removeChild(user_span[i]);
										}
										
									}
									x.extend(el.uName,{'className':'tx hide'});
									var a = '<a title="点击修改" href="javascript:void(0);">'+user+'<em>修改</em></a>';
									
										el.editor.className = 'user';
										x.extend(el.editor,{'className':'user','onclick':'deletes(this);','val':user});
										el.editor.innerHTML = a;
									_$('addUser').appendChild(el.editor);
									//_$('userName').setAttribute('value',user);
									_$('userName').value = user;
									
								}
						
						
				},
				
				createlements : function( element,x,obj ){
				
					var elements = el.d.createElement( element );
					
						x.extend(elements,obj);
					
						return elements;
						
				},
				
				addEvent : function(el,type,fn){
					
					if(window.addEventListener){
					
						el.addEventListener(type,fn,false);
						
					}else if(window.attachEvent){
					
						el.attachEvent('on'+type,fn);
						
					}else{
					
						el['on'+type] = fn;
					}
				
				},
				
				exit : function( x ){
				
					x.addEvent(el.exit,'click',function(){
						
						el.submit['disabled'] = 'disabled';
						el.org.className = 'obg hide';
						el.tagDiv.className = 'tagDiv hide';
						el.worDageTarea.value ='';
						el.uName.value ='';
						x.setStyle(el.worDageTarea,{'height':'80px'});
						el.wordage.innerHTML = '亲，你还可以输入<big class="big">500</big>个字噢。';
					})
				},
				
				uerName : function( x ){

					var _el = el.uName,ajax = _$('ajaxDate');
						
					if(_el.value ==''){
								
						_el.value = '搜索用户';
					}
					
					x.addEvent(_el,'focus',function(){
						
						if(_el.value =='搜索用户'){
							
							_el.value ='';
							
						}
						_el.select();
					});
					
					x.addEvent(_el,'blur',function(){
													
						if(_el.value ==''){
							
							_el.value ='搜索用户';
						}else if(_el.value !='搜索用户'){
							
						}						
					});
					
					x.addEvent(_el,el.mouseEvent,function(){
						
						ajax.innerHTML ='';
						var ObjValue = _el.value.replace(/\s/g,'');
						
						if(ObjValue !='' && ObjValue.length >0){
						
							var url = G_BASE_URL+'/search/?act=search_user&q='+encodeURIComponent(ObjValue)+'&limit=10';
						
							x.XHRAjax(url,x,ajax); //ajax接口
							
							x.extend(ajax,{'className':'ajaxdate'});
						}else{
							x.extend(ajax,{'className':'ajaxdate hide'});
						}
						
						d['onclick'] = function(){  x.extend(ajax,{'className':'ajaxdate hide'}) };
						
					})
					
				},
				
				
				setStyle : function(el,obj){
				
					for(var k in obj){
					
						var tlp = obj[k];
						el.style[k] = tlp;
						
					}
					return el ;
				},
				
				mouseEve : function( o ){
					
					o.addEvent(el.worDageTarea,el.mouseEvent,function(){
						
						o.setStyle(el.worDageTarea,{'height':0+'px'});
						var twords = el.worDageTarea.value.length ;
						var len = wordsNumber - twords;
						var wsh = el.worDageTarea.scrollHeight;
						
						if (len < 0){
							
							el.submit['disabled'] = 'disabled';
							el.wordage.innerHTML = '<span style="color:red;">你输入的内容已超出<big class="big">'+(-len)+'</big>个字</span>';
							o.setStyle(el.worDageTarea,{'overflowY':'scroll','height':'320px'});
							return false;
							
							
						}else{

							el.wordage.innerHTML = '你还可以输入<big class="big">'+len+'</big>个字。';
							el.submit['disabled'] = false;
							o.setStyle(el.worDageTarea,{'height':wsh+65+'px','overflowY':'hidden'});
							
							if(wsh > 280){
								o.setStyle(el.worDageTarea,{'overflowY':'scroll','height':'320px'});
								return false;
							}
							
							if(el.worDageTarea.value ==''){el.submit['disabled'] = 'disabled';}
						}
						
						

					});
					
					o.addEvent(el.worDageTarea,'focus',function(){
					
						o.setStyle(el.worDageTarea,{'height':0+'px'});
						var wsh = el.worDageTarea.scrollHeight;
						o.setStyle(el.worDageTarea,{'height':wsh+63+'px','overflowY':'hidden'});
						if(el.worDageTarea.value ==''){el.submit['disabled'] = 'disabled';}
						
					});
					
					o.addEvent(el.worDageTarea,'blur',function(){
					
						if(el.worDageTarea.value ==''){
							o.setStyle(el.worDageTarea,{'height':'80px'});
							el.submit['disabled'] = 'disabled';
						}
					})
				},
				
				
				each : function(x,fn){
					
					for(var k=-1,len=x.length;++k<len;){
					
						fn.call(this,x[k],k);
					}
				},
				
				XHRAjax: function( url,x,ajax ){
					
					var xhr;
					try{
						
						xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("microsoft.XMLHTTP");
						xhr.open("post",url,true);
						xhr.onreadystatechange = function(){
						
							if( xhr.readyState == 4 && xhr.status == 200 ){
						
								var xml = new Function('return'+xhr.responseText)();
								
								if(xml!=null && xml!=''){
									
									x.each(xml,function(date,objx){
								
									
									var elements = document.createElement("div");
										elements.className = 'rSri';
										elements.id = date.uid;
										elements.setAttribute("val",date.name);
										elements.onclick = function(){
											createElem(this);
										}
										//elements.setAttribute("onclick",'createElem(this);');
										elements.innerHTML = '<p class="usr">'+date.name+'</p>';
										ajax.appendChild(elements);
									
									})
								}else{
									
									ajax.innerHTML = '<p style="padding:8px;color:#666;">抱歉！没有查询到你找的用户。<p>'
								}
							}
						};
						xhr.send(null);
						
					}catch(e){
						
						alert(e)
					}
				},
				
				extend : function( destination, source ) {
				
					for (var property in source) {
					
						if(property == 'id'|| property == 'className'){
						
							destination[property] = source[property]

						}else{
							destination.setAttribute(property,source[property]);
						}
					}
					
					return destination;
				},
				
				info : function( o,user ){
					
					var exl = this ;
					this.static( o,exl,user ); //点击
					this.exit( exl );   //取消
					this.uerName( exl ); //搜索用户
				}
				
		};
};






function createElem(x){

	var userName = document.getElementById('userName');
	var ajaxdate = x.parentNode;
	var elementsSPAN = document.createElement("span");
	userName.value = x.getAttribute("val");
	//userName.setAttribute('value',x.getAttribute("val"));
	
	
	elementsSPAN.className = 'user';
	//elements.setAttribute('onclick','deletes(this)');
	elementsSPAN.setAttribute("val",x.getAttribute("val"));
	elementsSPAN.onclick = function(){deletes(this);}
	elementsSPAN['innerHTML'] = '<a href="javascript:void(0);" title="点击修改">'+x.getAttribute("val")+'<em>修改</em></a>';
	ajaxdate.parentNode.appendChild(elementsSPAN);
	userName.className = 'tx hide';
	ajaxdate.className = 'ajaxdate hide';
	
	//alert(document.getElementById('userName').value);
}


//私信对话

try{
	
	var tips = d.getElementById("inputTips");
	var userAarea = d.getElementById("tUserInput");
	var USnum = 500,minHeight = 60;
	var eventMosue = 'onkeydown' && 'onkeyup';
	

		userAarea.style.height = userAarea.scrollHeight+ minHeight+'px';
		
		userAarea[eventMosue] = function(){
			
			if(this.value =='' || this.value.length == 0){
				d.getElementById('sendForm')['disabled'] = 'disabled';
			}else{
				d.getElementById('sendForm')['disabled'] = false;
			}
			
			var usLEN = userAarea.value.length;
			
			var fb_uN = USnum - usLEN;
			
			
			tips.className = 'sub';
			tips.innerHTML = '还可以输入<big class="big">'+fb_uN+'</big>个字';
			
			if(fb_uN < 0){
				
				d.getElementById('sendForm')['disabled'] = 'disabled';
				this.style.height = '300px';
				this.style.overflowY = 'auto';
				tips.className = 'sub crl';
				tips.innerHTML = '已超出<big class="big">'+ (-fb_uN) +'</big>个字';
				return false;
				
			}else{
				
				d.getElementById('sendForm')['disabled'] = false;
			}
			
			this.style.height = minHeight+'px';
			this.style.height = this.scrollHeight + minHeight +'px';

		}

}catch(e){
	
	//alert("私信对话："+e);
}


Quick_Tips = new __quickPopUp();//私信
usersinfo  = new _$userinfor();//用户、话题名片夹



} //end window onload   



function deletes(o){
	
	var userName = document.getElementById('userName');
	userName.value = o.getAttribute("val");
	userName.className ='tx';
	o.parentNode.removeChild(o);
}

//添加取消关注
function Attention(o,Booleans){

	var url='';
	
	if(Booleans){
		
		url = G_BASE_URL+'/follow/?act=people_follow_edit_ajax_action&uid=';
	}else{
		
		url = G_BASE_URL+'/topic/?act=focus_topic&topic_id=';
	}
	
	 
	var uid = o.getAttribute("uid");
	
	var XHRhTTp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("microsoft.XMLHTTP");　
						　
	XHRhTTp.open("post",url+uid+'&rnd'+Math.random(),true);　　　　
	XHRhTTp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");　　　　
	XHRhTTp.onreadystatechange=function(){
		if( XHRhTTp.readyState == 4 && XHRhTTp.status == 200 ){
						
			var XHRREPOSETEXT = new Function('return'+XHRhTTp.responseText)();
			
				
				//更新关注缓存
				if(josnDate.length!=0){
					
					for(var i=0,len=josnDate.length;i<len;i++){
						
						if(Booleans){
							if(josnDate[i].uid ==uid && XHRREPOSETEXT.rsm.type =='remove'){
							
								josnDate[i].focus = false;
								o.innerHTML = '关注';
								o.setAttribute("title",'关注');
								
							}else if(josnDate[i].uid ==uid && XHRREPOSETEXT.rsm.type =='add'){
							
								josnDate[i].focus = true;
								o.innerHTML = '取消关注';
								o.setAttribute("title",'取消关注');
							}
							
						}else{
						
							if(josnDate[i].topic_id ==uid && XHRREPOSETEXT.rsm.type =='remove'){
							
								josnDate[i].focus = false;
								o.innerHTML = '关注';
								o.setAttribute("title",'关注');
								
							}else if(josnDate[i].topic_id ==uid && XHRREPOSETEXT.rsm.type =='add'){
							
								josnDate[i].focus = true;
								o.innerHTML = '取消关注';
								o.setAttribute("title",'取消关注');
							}
						}
						
					}
				}
		}
	}
				
	XHRhTTp.send();
}


//mouse event
function eventsMouseM(s){
	
	usersinfo._s(s,G_BASE_URL+'/people/?c=ajax&act=user_json&uid=',true); //个人
}
function eventstalk(s){
	usersinfo._s(s,G_BASE_URL+'/topic/?c=ajax&act=topic_json&topic_id=',false); //话题
}


function loadAddElements(){

	var element = document,el = element.createElement("div"),PopHTML,//私信
		bd = element.getElementsByTagName('body')[0],
		
		elementsHTML = '<div class="user-opacity-tips hide" id="user-opacity-tips"><div class="m" id="uk-mask"></div>';
		elementsHTML+= '<div class="fb-pgk" id="fb-pgk">请稍后，正在加载...</div>';
		elementsHTML+= '<span id="fb-span"></span></div>';
		PopHTML ='<div class="tagDiv hide" id="tagDiv"><div class="tcon">';
		PopHTML +='<h3 id="drag">发私信</h3>';
		PopHTML +='<form id="pm_popup_form" action="'+G_BASE_URL+'/inbox/index.php?c=main&act=write_message&click_id=pm_popup_exit" method="post"><ul class="u">';
		PopHTML +='<li id="addUser" class="addUser"><div id="ajaxDate" class="ajaxdate hide"></div><label>发给：</label><input class="tx" type="text" name="recipient" id="userName" value="" onkeydown="if (event.keyCode == 13) { return false; }" /></li>';
		PopHTML +='<li><label>内容：</label><textarea class="tarea" id="worDageTarea" name="message" maxlength="500"></textarea></li>';
		PopHTML +='<li class="subm"><a href="javascript:void(0);" class="sbumi" disabled="disabled" id="submit" onclick="ajax_post($(\'#pm_popup_form\'), _pm_form_processer); return false;" >提交</a><a href="javascript:void(0);" class="exit" id="pm_popup_exit">取消</a><span id="wordage">你还可以输入<big class="big">500</big>个字。</span></li>';
		PopHTML +='</ul></form>';
		PopHTML +='</div></div><div class="obg hide" id="org_alpha"></div>';
		
		el.innerHTML = elementsHTML + PopHTML;
		element.getElementsByTagName('body')[0].appendChild(el);
		
		
}