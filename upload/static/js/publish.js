$(document).ready(function () {	
	init_fileuploader('file_uploader_question', G_BASE_URL+'/publish/?c=ajax&act=question_attach_upload&attach_access_key=' + ATTACH_ACCESS_KEY);
});	

									   
//var _upLoadImg = _$("slectUploadImg");//顶部图片上传
//var _imgId = _upLoadImg._$id();
//var _interest = _$("interested"); //不感兴趣
//var _specific = _$("specific"); //竞赛具体需求
var _specificx = _$("specificx"); //问题具体需求
var _mytiTle = _$("MyTitle");   //标题
var _datelist = _$("datelist"); //tab切换

var _manytopics = _$("Manytopics"); //添加更多话题


//具体需求
//var _into = _interest._$id();
//var _spec = _specific._$id();
var _spex = _specificx._$id();
var _myti = _mytiTle._$id();
var _fouc = "onfocus",_blur = "onblur";

var changeEvent = 'onkeydown' && 'onkeyup',

     anwsionarr =[_spex],

     changesEvent = function (){
		
		
		if(this.getAttribute("id") == 'specificx'){
			if(this.scrollHeight >= 500){
				
				this.style.height = '500px';
				this.style.overflowY = 'auto';
				return false;
			}
			
			
			this.style.height = '0px';
			if(this.scrollHeight >=320){
			
				this.style.height = '0px';
				this.style.height = this.scrollHeight + 50 + 'px';
				
			}else{
				
				this.style.height = '320px';
			}
				
		}else{
			
			if(this.scrollHeight >= 500){
				
				this.style.height = '500px';
				this.style.overflowY = 'auto';
				return false;
			}
			this.style.height = '0px';
			this.style.height = this.scrollHeight + 60 + 'px';
		}
		
	 },
	 elementEventFocus = function(){
		
		if(this.value == ''){
			if(this.getAttribute("id") == 'specificx'){
			
				this.style.height = '320px';
				
			}else{
			
				this.style.height ='80px';
			}
		}
		
		
	 },
	
	elementEventblur = function(){
	
		if(this.value==''){
		
			if(this.getAttribute("id") == 'specificx'){
			
				this.style.height = '320px';
				
			}else{
				this.style.height ='80px';
			}
		
		}
	};
	

for(var idun = -1 ,len = anwsionarr.length ; ++idun < len ;){
	
	var idunNum = anwsionarr[idun];
	
	idunNum[changeEvent] = changesEvent; //绑定change事件
	
	idunNum[_fouc] = elementEventFocus;
	
	idunNum[_blur] = elementEventblur;
}

//tab切换
var uLister = _datelist._$id();
var elements = uLister.getElementsByTagName("a");
var classHide = ' hide',classCur ='discussion_element',conDiv = 'tommy_rightDiv';

for(var i=-1,len=elements.length; ++i < len ;){
	
	(function(i){
		
		var element = elements[i];
		
		element['onclick'] = function(){
			
			for(var n=-1,len=elements.length; ++n < len ;){
			
				var elementId = _$("layer"+n)._$id();
				
				elementId['className']= classCur+classHide;
				elements[n]['className']='';
				_$("con_"+n)._$id().className = conDiv+classHide;
				
			}
			var elementId = _$("layer"+i)._$id();
			
			elementId['className']= classCur;
			element['className']='cur';
			_$("con_"+i)._$id().className = conDiv;
		}
		
	})(i)
}

var fa = true;
var doc = document;
var addtags = doc.getElementById("addTags");
var inputs = addtags.getElementsByTagName("input");

// 弹出框选择话题
function addTags(x){

	fa = false;
	doc.getElementById("inValue").blur();
	var xrh = doc.getElementById("xrh");
	var elmentx = doc.getElementById("x");
	var span = doc.createElement("span");
	var em = '<em onclick="remove(this);">x</em><input type="hidden" name="topics[]" value="';
	var val = x.getAttribute("rel");
	if(inputs.length!==0){
	
		for(var i=0,len =inputs.length;i<len;i++){
		
			var elements = inputs[i];
			var elValue = elements.getAttribute("value").replace(/\s/g,'');
			var oValue = x.getAttribute("rel").replace(/\s/g,'');
			
			if(elValue == oValue){
			
				//alert("请勿重复添加!");
				doc.getElementById("inValue").value='';
				
				elmentx.innerHTML='请勿重复添加，谢谢!';
				elmentx.className ='s';
				_manytopics._$id().className ='shd clr';
				
				xrh.className = 'tips hide';
				fa = true;
				return false;
				
			}
		}
		span.setAttribute("val",val);
		span.innerHTML = '<a href="javascript:void(0);">'+val+'</a>'+em+val+'" />'
		addtags.appendChild(span);
		
		
	}else{
		span.setAttribute("val",val);
		span.innerHTML = '<a href="javascript:void(0);">'+val+'</a>'+em+val+'" />'
		addtags.appendChild(span);
		document.getElementById("inValue").value='';
		xrh.className = 'tips hide';
		fa = true;
	}
	
	
	

}
function remove(x){

		var o = x;
		o.parentNode.parentNode.removeChild(o.parentNode);
		
		
}

//添加话题
var m = {

	mx : function (o){
		
		var mxId = _manytopics._$id() , doc = document;
		
		var mxinput = mxId.getElementsByTagName("input")[0];
		var mxDiv = mxId.getElementsByTagName("div")[0];
		var mxSpan = mxDiv.getElementsByTagName("span");
		var addtags = _$("addTags")._$id();
		var xrh = _$("xrh")._$id();
		
		
		var el = this;
		
		return {
		
			add : function (val){
			
				var s = doc.createElement("span");

					s.setAttribute("val",val);
					s.innerHTML = '<a href="javascript:void(0);">'+val+'</a><input type="hidden" name="topics[]" value="'+val+'" /><em onclick="remove(this);">x</em>';
					return s ;
			
			},
			
			ev : function( elx ){
				var o = this;
				var x = _$('x')._$id();
				
				xrh['onmousedown'] = function(){ fa= false; return false;};
				
				document.onclick = function(){
				
					if(mxinput.value ==''){
					
							mxinput.onblur = function(){
							
								var setTime = setTimeout(function(){
									fa = false;
									xrh.className = 'tips hide';
									clearTimeout(setTime);
									
								},200)
							}
					}
					
				}
				
				mxinput['onchange'] = function(){

					if(fa){
						var objValue = this.value ;
						var strValue = objValue.toString();
						var stv = strValue.replace(/\s/g,'');
						
						if(inputs.length!==0){
						
							for(var i=0,len =inputs.length;i<len;i++){
			
								var elements = inputs[i];
								var elValue = elements.getAttribute("value").replace(/\s/g,'');
								var oValue = mxinput.value.replace(/\s/g,'');
								
								if(elValue == oValue){
								
									//alert("请勿重复添加!");
									doc.getElementById("inValue").value='';
									
									x.innerHTML='请勿重复添加，谢谢!';
									x.className ='s';
									_manytopics._$id().className ='shd clr';
									
									xrh.className = 'tips hide';
									fa = true;
									
									return;	
								}
							}
							
							if(stv.length <= 10){
						
								var s = elx.add(objValue);

								mxDiv.appendChild(s);
								this.focus();
								this.value ='';
								
							}else{
								
								x.className='s';
								x.innerHTML ='请输入≤10个字符的话题！';
								mxId.className ='shd clr';
								this.value ='';
								
							}
						}else{
						
							if(stv.length <= 10){
						
								var s = elx.add(objValue);
								mxDiv.appendChild(s);
								this.focus();
								this.value ='';
								
							}else{
								
								x.className='s';
								x.innerHTML ='请输入≤10个字符的话题！';
								mxId.className ='shd clr';
								this.value ='';
								
							}
							
						}
						
						
					}
				}
				
				
				mxinput['onkeyup' && 'onkeydown'] = function(){
					
					fa = true;
					xrh.className = 'tips hide';
					x.className='s hide';
					mxId.className ='shd';
				}
			},
			
			
			info : function(){
			
				var d = doc.createElement("div"),el = this;
				d.className='s hide';
				d.id ='x';
				d.innerHTML = '请输入≤10个字符的话题！';
				mxId.parentNode.appendChild(d);

				//mxId['onclick'] = function(){ mxinput.focus();}
	
				mxinput['onfocus'] = function(){
				
					fa = true; 
					el.ev(el) ;
					xrh.className = 'tips';
					d.className='s hide';
					mxId.className ='shd';
				};
			}
			
		
		}
		
		
	},
	
	remove : function(){
		var o = this;
		o.parentNode.parentNode.removeChild(o.parentNode);
		
	}
};

$(document).ready(function () {
	switch (window.location.hash)
	{
		case '#question':
			$('#publish_' + window.location.hash.replace('#', '')).click();
		break;
	}
});