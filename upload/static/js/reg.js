$(document).ready(function () {
	$('#seccode').click();
});

window.onload = function(){

	var doc = document, _$ = function(o){return 'string' == typeof o ? doc.getElementById(o) : o ;},

   
	
	reg = function(){
	
		this['txt'] = '\u771f\u5b9e\u59d3\u540d|\u8d26\u53f7\uff08\u7535\u5b50\u90ae\u7bb1\uff09|\u5bc6\u7801|\u9a8c\u8bc1\u7801|\u518d\u6b21\u8f93\u5165\u5bc6\u7801';	
		this['regId'] = _$("reg_moster");                    //注册Id		
		this['userName'] = _$("userName");                   //用户名		
		this['userEmail'] = _$("userEmail");                 //用户邮箱		
		this['userPasswordx'] = _$("userPasswordx");            //密码	
		this['userPassword'] = _$("userPassword");    
		this['reqs_ansx'] = _$("re_userPasswordx");            //再次密码	
		this['reqs_ans'] = _$("re_userPassword"); 
		this['userCode'] = _$("userCode");                     //验证码		
		this['en'] = /^[A-Za-z0-9\u4e00-\u9fa5]{2,13}$/;                            		
		this['ch'] = /^[\u4e00-\u9fa5]{1,6}$/;                     		
		this['em'] = /^[A-Za-z\d]+([-_\.\+]*[A-Za-z\d])*@([A-Za-z\d][A-Za-z\d-]{0,61}[A-Za-z\d]\.)+[A-Za-z\d]{2,6}$/; 
		this['cd'] = /^[\w\d]{4,6}$/;
		this['eltxt'] = this['txt'].split("|");
		this['_sp'] = doc.createElement("span");
		this['_em'] = doc.createElement("em");
		
		var xarr=[],el = this;
		
		
		return {
			
			a : function(e,_s,i,fn){
				
				e.className = _s.event.focusClass;
				
				
				switch(e.getAttribute("id")){
				
					case 'userName' :
					_s.app(_$("tip_userName"),_s.event.tipsClassName,0);
					_s.setmouse(e,0,0);
					break;
					
					case 'userEmail':
					_s.app(_$("tip_userEamil"),_s.event.tipsClassName,1);
					_s.setmouse(e,0,0);
					break;
					
					case 'userPassword' :
					_s.app(_$("tip_userPassword"),_s.event.tipsClassName,2);
					el['userPasswordx'].className = _s.event.hide;
					_s.setmouse(e,0,0);
					break;
					
					case 'userPasswordx' :
					_s.app(_$("tip_userPassword"),_s.event.tipsClassName,2);
					_s.setmouse(e,0,0);
					break;
					
					case 're_userPassword' :
					_s.app(_$("re_tip_userPassword"),_s.event.tipsClassName,9);
					el['reqs_ansx'].className = _s.event.hide;
					_s.setmouse(e,0,0);
					break;
					
					case 're_userPasswordx' :
					_s.app(_$("re_tip_userPassword"),_s.event.tipsClassName,9);
					_s.setmouse(e,0,0);
					break;
					
					case 'userCode' :
					_s.app(_$("tip_userCode"),_s.event.tipsClassName,3);
					_s.setmouse(e,0,0);
					break;
					
				}
				
				if(fn != null){
					return fn();
				}
			},
			
			b : function(e,_sx,i,fn){
				
				e.className = _sx.event.blurClass;
				
				if(e.value == ''){
				
					e.value = el['eltxt'][i];
					if(e.getAttribute("id") ==='userCode'){
					
						e.value = el['eltxt'][i-1];
					}
					if(e.getAttribute("id") === 'userPassword'){
						
						el['userPasswordx'].className = _sx.event.blurClass;
						el['userPassword'].className = _sx.event.hide;
						el['userPassword'].value='';
					}
					if(e.getAttribute("id") === 're_userPassword'){
						
						el['reqs_ansx'].className = _sx.event.blurClass;
						el['reqs_ans'].className = _sx.event.hide;
						el['reqs_ans'].value='';
					}
				}

				if(fn != null){
					return fn();
				}
			},
			
			c : function(e,o,i,fn){
				
				if(e.value == el['eltxt'][i]){
				
					e.value ='';
					e.focus();
					
				}
				if(e.getAttribute("id") =='userCode'){
						if(e.value == el['eltxt'][i-1]){
						
						e.value ='';
						e.focus();
					}
				}
				
				e.className = o.event.keydownClass;
				
				if(e.getAttribute("id") === 'userPasswordx'){
					
					e.className = o.event.hide;
					el['userPassword'].className = o.event.blurClass;
					el['userPassword'].focus();
				}
				
				if(e.getAttribute("id") === 're_userPasswordx'){
					
					e.className = o.event.hide;
					el['reqs_ans'].className = o.event.blurClass;
					el['reqs_ans'].focus();
				}
				
				if(fn != null){
					return fn();
				}
			},
			
			each : function(o,fn){
			
				for(var i =-1,len= o.length; ++i<len;){
					fn.call(this,o[i],i);
				}
				
			},
			
			setmouse : function(o,s,e){
			
				if(o.setSelectionRange){

					o.focus();
					o.setSelectionRange(s,e);
					
					
				}else if(o.createTextRange){
					
					var trg = o.createTextRange(); 
					trg.collapse(true); 
					trg.moveEnd('character', e); 
					trg.moveStart('character', s); 
					trg.select(); 
					
				}
				
			},
			
			setobjValue: function(elements,t){
				
				if(t == 0 && el['userName'].value =='' ){
					
					el['userName'].value = elements;
					
				}else if(t == 1){
					
					if(el['userEmail'].value ==''){
					
						el['userEmail'].value = elements;
						
					}
					
				}else if(t == 2 || el['userPasswordx'].value ==''){
				
					el['userPasswordx'].value = elements;
					
				}else if(t == 3 || el['userCode'].value ==''){
				
					el['userCode'].value = elements;
					
				}
			},
			
			d : function(arr,i){
			
				var x  = this;
				
				arr[this.event.focus] = function(){x.a(this,x,i,x.fc(this,x));};
				
				arr[this.event.blur] = function(){x.b(this,x,i,x.bur(this,x));};
				
				arr[this.event.keydown] = function(){x.c(this,x,i);};
				
			},
			
			app : function(obj,elClass,n){
				
				var txt = '请输入2-7个汉字或 4-14个英文字母|请输入你常用的电子邮箱作为你的账号|请输入6-16个字符， 区分大小写|请输入图上的字母或数字，不区分大小写|真实姓名输入错误|电子邮箱输入错误||验证码输入有误|密码输入有误|请输入跟上面一样的字符';
				
					obj.className = elClass;
					obj.innerHTML = txt.split("|")[n];
				
			},
			
			e : function(){
				
				if(el['reqs_ans']!=null){
					xarr.push(el['userName'],el['userEmail'],el['userPassword'],el['userPasswordx'],el['userCode'],el['reqs_ans'],el['reqs_ansx']);
				}else{
					xarr.push(el['userName'],el['userEmail'],el['userPassword'],el['userPasswordx'],el['userCode']);
				}
				
				this.each(el['eltxt'],this.setobjValue);
				
				this.each(xarr,this.d);
			},
			
			fc : function(o,x){
				
				if(o.getAttribute("id") === 'userCode'){
				
					o.style.width = '150px';
				}
			
			},
			
			bur : function(o,_sx){
			
				var objVALUE = o.value.replace(/\s/g,'');
				
				if(o.getAttribute("id")==='userName'){
					
					if(el['en'].test(objVALUE) ){
					
						if(objVALUE !== el['eltxt'][0]){
						
							_sx.app(_$("tip_userName"),_sx.event.rightClassName,6);
						}
						else{
							_sx.app(_$("tip_userName"),_sx.event.errorClassName,4);
						}
					}else{
						
						_sx.app(_$("tip_userName"),_sx.event.errorClassName,4);
					}
				}
				
				if(o.getAttribute("id") ==='userEmail'){
					
					if(el['em'].test(objVALUE)){
						
						_sx.app(_$("tip_userEamil"),_sx.event.rightClassName,6);
					}else{
					
						_sx.app(_$("tip_userEamil"),_sx.event.errorClassName,5);
					}
				}
				
				if(o.getAttribute("id") ==='userCode'){
					
					if(el['cd'].test(objVALUE)){
						
						_sx.app(_$("tip_userCode"),_sx.event.rightClassName,6);
					}else{
					
						_sx.app(_$("tip_userCode"),_sx.event.errorClassName,7);
					}
				}
				if(o.getAttribute("id") ==='userPassword'){
					
					if(objVALUE.length >=6 && objVALUE.length <= 16){
						
						_sx.app(_$("tip_userPassword"),_sx.event.rightClassName,6);
					}else{
					
						_sx.app(_$("tip_userPassword"),_sx.event.errorClassName,8);
					}
				}
				if(o.getAttribute("id") ==='userPasswordx'){
					if(objVALUE == el['eltxt'][2]){
						_sx.app(_$("tip_userPassword"),_sx.event.errorClassName,8);
					}
				
				}
				if(o.getAttribute("id") ==='re_userPassword'){
					
					if(objVALUE.length >=6 && objVALUE.length <= 16){
						
						_sx.app(_$("re_tip_userPassword"),_sx.event.rightClassName,8);
					}else{
					
						_sx.app(_$("re_tip_userPassword"),_sx.event.errorClassName,8);
					}
				}
				if(o.id =='re_userPasswordx'){
						
					if(objVALUE == '再次输入密码'){
						_sx.app(_$("re_tip_userPassword"),_sx.event.errorClassName,8);
					}
				}
				
			},
			
			event :{
				
				focus:'onfocus',
				blur : 'onblur',
				keydown :'onkeydown',
				hide :'userTxt hide',
				focusClass : 'userTxt cur',
				blurClass :'userTxt',
				keydownClass :'userTxt on',
				yCodeClass:'userTxt yzm',
				yCodefocusClass:'userTxt yzm cur',
				tipsClassName :'explain',
				errorClassName : 'err',
				rightClassName :'right'	
			}
			
			
		}
	
	};
	
	var elementsReg = new reg();
	elementsReg.e();
	
};