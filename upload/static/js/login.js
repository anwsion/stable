


window.onload = function(){

try{
var doc = document, _$ = function(o){return 'string' == typeof o ? doc.getElementById(o) : o ;},
    userValue = '请输入您的邮箱..|请输入您的密码...|验证码..',
	UserName = _$("UserName"),                      //用户名
	PassWord = _$("PassWord"),                      //密码
	userLogin = _$("userLogin"),
	elceate = doc.createElement("input");                 //创建元素
	elceate.type='text';		
	elceate.className='usertxt';
	elceate.tabIndex='2';
	
	elceate.value = userValue.split("|")[1];
var userPassword = userLogin.getElementsByTagName("li")[1],
	eluserName = userLogin.getElementsByTagName("li")[0];
	userPassword.appendChild(elceate);
	
	
	if(UserName.value ==''){
		UserName.value = userValue.split("|")[0];
	}
}catch(e){return}	
	var EvenetBind = {
		
		//userName
		un: function( o, fn ){
			
			var el = this;
			o[el.event.focus] = function(){
				
				//var colors = this.style.color;
				this.style.color = el.event.focColor;
				this.className = el.event.classCur;
				
				el.setmouse(this,0,0);
				
				this[el.event.keydown] = function(){
					
					if(this.value == userValue.split("|")[0]){
					
						this.value ='';
					}
					this.style.color = el.event.keyColor;
					
				}
				
			} // end focus 
			
			o[el.event.blur] = function(){
				
				if(this.value == ''){
					this.value = userValue.split("|")[0]
				}
				
				this.style.color = el.event.bluColor;
				this.className = el.event.classOn;
				
				
			}  // end blur 
			
			el.els(eluserName,{x:UserName});
			
			if(fn!=null){
				return fn();
			}
		},
		
		// password
		pw: function( o ,fn ){
			
			var el = this;
			elceate[el.event.focus] = function(){
				
				userPassword.removeChild(this);
				o.className = el.event.classOn;
				o.style.color = el.event.keyColor;
				o.focus();
				
			} //end focus
			
			elceate[el.event.blur] = function(){
				if(this.value ==''){
					this.value = userValue.split("|")[1]
				}
				
				this.style.color = el.event.bluColor;
				this.className = el.event.classOn;
			} // end this create password blur 
			
			o[el.event.blur] = function(){
				
				if( this.value !=''){
				
					this.style.color = el.event.bluColor;
					this.className = el.event.classOn;
					
				}else{
					userPassword.appendChild(elceate);
					this.className = el.event.classHide;
					if(elceate.value ==''){
						elceate.value = userValue.split("|")[1]
					}
					
				}
				
				
			}  // end blur 
			
			o[el.event.focus] = function(){
				
				this.style.color = el.event.keyColor;
				this.className = el.event.classCur;
				if(this.parentNode.getElementsByTagName('input')[1] !=null){
				
					userPassword.removeChild(this.parentNode.getElementsByTagName('input')[1]);
				}
			}
			
			if(fn!=null){
				return fn();
			}
		},
		
		event:{
		
			focus:'onfocus',
			blur:'onblur',
			click:'onclick',
			keydown:'onkeydown',
			focColor:'#999',
			bluColor:'#777',
			keyColor:'#000',
			classCur:'usertxt cur',
			classOn:'usertxt',
			classHide:'usertxt hide'
		},	
		
		cf : function(o,el){	
		
			o[this.event.click] = function(){
				
				if(typeof el === 'object'){
				
					for(var i in el){
				
						var els = el[i];
						if(els.value !=''){
			
							els.value ='';
							els.focus();
							
						}
					}
				}
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
		
		els : function(o,obj,fn){
		
			var a = doc.createElement("a");
				a.href = 'javascript:void(0);';
				a.title='点击清除';
				a.className = 'clear';
				o.appendChild(a);
				this.cf(a,obj);
				
				if(fn !=null){
				
					return fn();
				}
		}
		
	};

	EvenetBind.un(UserName); //用户名
	EvenetBind.pw(PassWord);//用户密码
	
	try{
		
		var __default__ID = _$("default_account"),d = document;
		var __defalt__height = Math.max(Math.max(d.documentElement.scrollHeight,d.documentElement.offsetHeight),d.body.scrollHeight);
		var _ = __defalt__height - 354 ;
		
		__default__ID.style.height = _+'px';
		
	}catch(e){}
};


