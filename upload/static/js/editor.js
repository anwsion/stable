var josndate = '[{"date":[{"id":"x0","txt":"动态随机存取存储器应如何创"},{"id":"x1","txt":"动态随机xxx"},{"id":"x2","txt":"动态随机xxx应如何创"},{"id":"x3","txt":"动态随机xxx应如何创"}],"exit":"true"}]';


function hide(o,j,c,p){

	var d = document;
	var x = d.getElementById(o);
	var xjx = d.getElementById(j);
	x.className = c;
	xjx.className = p;
	var addtxt = d.getElementById("addtxt");
	var em = addtxt.getElementsByTagName("em");
		
			for(var i =-1, len = em.length; ++i<len; ){
				
				if(j == 'editor'){
				
					em[i].className = '';
				}else{
				
					em[i].className = 'hide';
				}
			}
}


function removeChilds(x){x.parentNode.parentNode.removeChild(x.parentNode);}

var m = {
	
		mx : function (o){
			
			var doc = document, 
				mxId = doc.getElementById("Manytopics"),
				ajaxDate = doc.getElementById("ajaxDate"),
				take = doc.getElementById("take");
				txt = doc.getElementById("txt");
			
			var mxinput = mxId.getElementsByTagName("input")[0];
			var mxDiv = mxId.getElementsByTagName("div")[0];
			var mxSpan = mxDiv.getElementsByTagName("span");
			
			var el = this;
			
			return {
			
				add : function (val){
				
					var s = doc.createElement("span");

						s.setAttribute("val",val);
						s.innerHTML = val;
						return s ;
				
				},
				
				app : function (x){
				
					var objValue = x.getAttribute("date-list");
					
						var s = this.add(objValue);
						var em = doc.createElement("em");
						em.innerHTML = 'x';
						em['onclick'] = function(){
							removeChilds(this);
						}
						
						s.appendChild(em);
						mxDiv.appendChild(s);
						ajaxDate.className = 'hide';
						mxinput.value ='';
						mxinput.focus();
				
				},
				
				ev : function( o ){
					var elx = this;
					var Xx = doc.getElementById('x') || '';
					
					o['onkeydown'] = function(){
					
						var t = '';
						var objValue = this.value ;
						var strValue = objValue.toString();
						var stv = strValue.replace(/\s/g,'');
						ajaxDate.className ='';
						take.setAttribute("date-list",stv);
						take.innerHTML = stv;
						
						var x = new Function("return "+ josndate +";")();
						
						
							for(var i=-1,len=x[0].date.length; ++i<len;){
										
								t += '<div id="'+ x[0].date[i].id+'" onclick=\"add(this);\" date-list="'+x[0].date[i].txt+'">'+ x[0].date[i].txt +'</div>';
							}
										
							if(x[0].exit == 'false'){
									
								take.parentNode.className = 'add hide';
							}else{
								take.parentNode.className = 'add';
							}
							txt.innerHTML = t;
							
							
					}
					
					doc.onclick = function(){ajaxDate.className = 'hide';}
					
				},
				
				fc : function(e){
				
					e.ev(mxinput);
				},
				info : function(){
				
					var d = doc.createElement("div");
					d.className='s hide';
					d.id ='x';
					d.innerHTML = '请输入≤10个字符的话题！';
					if(mxId.parentNode.nodeName === "LI"){
						mxId.parentNode.appendChild(d);
					}
					var el = this;
				
					mxId['onclick'] = function(){ 
					
						mxinput.focus();
					}
					mxinput['onfocus'] = function(){el.fc(el)};
				}
				
			
			}
			
			
		},
		
		remove : function(){
		
			var o = this;
			o.parentNode.parentNode.removeChild(o.parentNode);
			
		}
	}
var t = new m.mx();t.info();
function add(x){t.app(x);}	
