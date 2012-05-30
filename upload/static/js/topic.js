var cur_competitions_page = 0;
var cur_competitions_sort_key = 'time';
var cur_competitions_sort = 'DESC';

var cur_question_page = 0;
var cur_question_sort_type = 'hot';
var bp_question_more_inner_o = '';

var cur_log_page = 0;

function reloadQuestionSortList(el, sort_type)
{
	cur_question_page = 0;
	cur_question_sort_type = sort_type;
	
	$('#sort_question_control a').removeClass();
	
	$(el).addClass('cur');
	
	$('#c_question_list').html('<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/css/img/loading_b.gif" alt="" /></p>');
	
	$('#bp_question_more').html(bp_question_more_inner_o).click();
}

function reloadCompetitionsSortList(el, sort_key)
{
	cur_competitions_page = 0;
	cur_competitions_sort_key = sort_key;
	
	switch (el.className)
	{
		default:
		case 'on':
			cur_competitions_sort = 'DESC';
		break;
		
		case 'cur':
			cur_competitions_sort = 'ASC';
		break;
	}
	
	$('#sort_competitions_control a').removeClass();
	
	switch (cur_competitions_sort)
	{
		case 'ASC':
			$(el).addClass('on');
		break;
		
		case 'DESC':
			$(el).addClass('cur');
		break;
	}
	
	$('#c_competitions_list').html('<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/css/img/loading_b.gif" alt="" /></p>');
	
	$('#bp_competitions_more').html(bp_competitions_more_inner_o).click();
}

$(document).ready(function()
{
	bp_more_load(G_BASE_URL+'/topic/?c=ajax&act=get_question_compeitions&topic_id=' + TOPIC_ID, $('#bp_all_more'), $('#c_all_list'));
	
	bp_more_load(G_BASE_URL+'/question/?c=ajax&act=discuss&template=&answer_count=0&page=0&category=&version=2&topic_id=' + TOPIC_ID, $('#bp_noanswer_more'), $('#c_noanswer_list'));
	

	
	bp_more_load(G_BASE_URL+'/topic/?act=log_more_ajax&topic_id=' + TOPIC_ID, $('#bp_log_more'), $('#c_log_list'));
	
	
	$.get(G_BASE_URL+'/topic/?c=ajax&act=get_focus_users&topic_id=' + TOPIC_ID, function (data) {
		$.each(data, function (i, d) {		
			$('#loc0').append('<a href="'+G_BASE_URL+'/people/?u=' + d['user_name'] + '" ><img src="' + d['avatar_file'] + '" onMouseOver="eventsMouseM(this);" rel="'+d['uid']+'" /></a>&nbsp;');
		});
	}, 'json');
	
	
	
	if (document.getElementById('edit_topic_pic'))
	{		
		init_img_uploader(G_BASE_URL+'/topic/?c=ajax&act=upload_topic_pic&topic_id=' + TOPIC_ID, 'topic_pic', $('#edit_topic_pic'), false, $('#edit_topic_pic'));
	}

		//增加话题经验
	$('#increased_experience_btn').click(function ()
	{
		if($('#experience_content_div').is(':visible'))
		{
			$('#experience_content_div').hide();
			$('#increased_experience_btn').show();
		}
		else
		{
			$('#increased_experience_btn').hide();

			$('#experience_content_div').show();
		}
	});

	//取消话题经验修改
	$('#cancle_experience_btn').click(function ()
	{
		
		$('#experience_content_div').hide();
		$('#increased_experience_btn').show();
	});
	//增加话题经验 
	$('#done_experience_btn').click(function ()
	{
		if($('#experience_content').val() == "")
		{
			alert('话题经验内容不能为空');
			return false;
		}
		if($('#experience_content').length >= 200)
		{
			alert('话题经验内容不能超过200字符!');
			return false;
		}
		if($('#experience_content').val() == $('#experience_content_html').html())
		{
			return false;
		}
		
	
		
		var url = G_BASE_URL+'/topic/?act=save_topic_experience&topic_id='+TOPIC_ID+'&&rnd=' + Math.random();
		var data =  {"experience_content" : $('#experience_content').val()};
		$.get(url, data, function (response)
			{
				if(response.errno == 1)
				{
					//show_message('修改话题经验内容成功');
					$('#increased_experience_btn').html($('#experience_content').val());
					//$('#experience_content_div').hide();
				//	$('#edit_experience_btn').show();//修改按扭可用
				//	$('#increased_experience_btn').hide();//添加按钮隐藏
					
					$('#experience_content_div').hide();
					$('#increased_experience_btn').show();

				}
				else
				{
					alert('修改话题经验内容失败');
				}
			}
		,'json');
		
		
		return false;
	});
});

(function(){

var doc = document,f = function(k,c,g,e,l,ck,cn){
	
	var o = k.getElementsByTagName(l);
	
		for(var i=-1,len= o.length; ++i < len ;){
			
			(function(i){
				var el = o[i];
				
				el[ck] = function(){
					
					for(var n=-1,len=o.length; ++n < len ;){
					
						var m = doc.getElementById(e+n);
						
						m[cn]= g;
						o[n][cn]='';
					}
					var m = doc.getElementById(e+i);
					
					m[cn]= c;
					el[cn]='current';
				}
				
			})(i)
		}
	},
	
	uLister = doc.getElementById("uLister"),
	uclassCur = 'detaListerDiv show', uclassHide = 'detaListerDiv hide';
	
	
	//f(uLister,uclassCur,uclassHide,"layer","a",'onclick','className');

})();



function hide(o,j,c,p,x){
	
	var addtxt = d.getElementById("addtxt");
	
	var x = d.getElementById(o);
	var xjx = d.getElementById(j);
		x.className = c;
		xjx.className = p;
	
	var em = addtxt.getElementsByTagName("em");
		
	for(var i =-1, len = em.length; ++i<len; ){
		
		if(j == 'editor'){
		
			em[i].className = '';
			
		}else{
		
			em[i].className = 'hide';
		}
	}
	
	
}

function addElementVALUE(){

		var inps = d.getElementById('editorInput').getElementsByTagName("input")[0],classname=null;
		var addtxt = d.getElementById('addtxt');
		var s_span = addtxt.getElementsByTagName("span");
		
		if(inps.className =='i_add'){
			
			classname = inps;
		}
		
		var inpsValue = classname.value;//.replace(/\s/g,'');
		
		if(inpsValue==''){return false;}
		
		for(var xi = 0,len = s_span.length; xi <len ; xi++){
			
			var s = s_span[xi];
			if(s.getAttribute("val") == inpsValue){
				
				return false;
			}
		}
		
		var xmlHttp,xmlValue;
						
				xmlHttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("microsoft.XMLHTTP");
				psotUrl = G_BASE_URL+'/topic/?act=save_topic&topic_title='+encodeURIComponent(inpsValue)+'&topic_child_id='+TOPIC_ID+'&rnd=' + Math.random();　
						　
				xmlHttp.open("post",psotUrl,true);　　　　
				xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");　　　　
				xmlHttp.onreadystatechange=function(){
							
					 if( xmlHttp.readyState == 4 && xmlHttp.status == 200 ){
								 
						var x = new Function('return'+xmlHttp.responseText)();
						if(x.errno ==1){
						
							var a = '<a href="'+G_BASE_URL+'/topic/?topic_id='+x.rsm.topic_id+'">'+inpsValue+'</a>';		
							var s = d.createElement("span");
								s.setAttribute("val",inpsValue);
								s.innerHTML = a+'<em onclick="removeChilds(this);" class="hide">x</em>';
								d.getElementById('addtxt').appendChild(s);
							
						}else if(x.errno ==-1){
							
							d.getElementById('editorInput').className="editorInput";
							d.getElementById('editor').className="editor hide";
							doc.getElementById("err").innerHTML = x.err;
							doc.getElementById("err").className = 'cr';
							var em = addtxt.getElementsByTagName("em");
		
							for(var i =-1, len = em.length; ++i<len; ){
								
								em[i].className = '';
							}
						}
								
				}
							
			};　　
			
			xmlHttp.send(topic_title=inpsValue,topic_child_id=TOPIC_ID);
}



var removeChilds = function(x){
	
	var el = x;
	var delectURL = G_BASE_URL+'/topic/?act=delete_topic&topic_id='+x.parentNode.getAttribute("id")+'&topic_child_id='+TOPIC_ID+'&rnd='+Math.random();
	var xmlHttp;
						
	xmlHttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("microsoft.XMLHTTP");
	　
	xmlHttp.open("post",delectURL,true);　　　　
	xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");　　　　
	xmlHttp.onreadystatechange=function(){
		
		 if( xmlHttp.readyState == 4 && xmlHttp.status == 200 ){
			 
			var t = xmlHttp.responseText;
			var x = new Function('return'+t)();
			el.parentNode.parentNode.removeChild(el.parentNode);
			
		}
		
	};　
	　
	xmlHttp.send(delete_topic=x.parentNode.getAttribute("id"),topic_child_id=TOPIC_ID);
						
						
}



var m = {
	
		mx : function (o){
			
			try{var doc = document, 
				mxId = doc.getElementById("Manytopics"),
				ajaxDate = doc.getElementById("ajaxDate"),
				take = doc.getElementById("take"),
				txt = doc.getElementById("txt"),
				addtxt = doc.getElementById("addtxt");
			
			var mxinput = mxId.getElementsByTagName("input")[0];
			var mxDiv = mxId.getElementsByTagName("div")[0];
			var mxSpan = mxDiv.getElementsByTagName("span");
			var eventLister = 'onkeydown' && 'onkeyup';
			
			}catch(e){return;}
			var el = this;
			
			return {
			
				add : function (a,val,id){
				
					var s = doc.createElement("span");
						
						s.setAttribute("val",val);
						s.innerHTML = a+'<em onclick="removeChilds(this);">x</em>';
						
						var str = id;
						s.id = str;
						
						return s ;
				
				},
				
				app : function (x){
					
						var objValue = x.getAttribute("date-list");
						
						var v = addtxt.getElementsByTagName("span");
								
							for(var i = 0,len = v.length; i < len ; i++ ){
								if(v[i].getAttribute("date-list") == objValue){
									mxinput.classNam ='i_add inErr';
									mxinput.value ='';
									doc.getElementById("err").innerHTML ='请勿重复添加话题！';
									doc.getElementById("err").className = 'cr';
									return false;
								}
							}
						
						var el = this;
						
						//
						var xmlHttp,xmlValue;
						
						xmlHttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("microsoft.XMLHTTP");
						psotUrl = G_BASE_URL+'/topic/?act=save_topic&topic_title='+objValue+'&topic_child_id='+TOPIC_ID+'&rnd=' + Math.random();　
						　
						xmlHttp.open("post",psotUrl,true);　　　　
						xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");　　　　
						xmlHttp.onreadystatechange=function(){
							
							 if( xmlHttp.readyState == 4 && xmlHttp.status == 200 ){
								 
								var t = xmlHttp.responseText;
								var x = new Function('return'+t)();
								
								
								if(x.errno ==1){
						
									var a = '<a href="'+G_BASE_URL+'/topic/?topic_id='+x.rsm.topic_id+'">'+objValue+'</a>';	
									var s = el.add(a,objValue,x.rsm.topic_id);
									mxDiv.appendChild(s);
									
									
									
									
								}else if(x.errno ==-1){
									
									d.getElementById('editorInput').className="editorInput";
									d.getElementById('editor').className="editor hide";
									doc.getElementById("err").innerHTML = x.err;
									doc.getElementById("err").className = 'cr';
									var em = addtxt.getElementsByTagName("em");
				
									for(var i =-1, len = em.length; ++i<len; ){
										
										em[i].className = '';
									}
								}
								
							}
							
						};　　
						xmlHttp.send(topic_title=objValue,topic_child_id=TOPIC_ID);
						
						ajaxDate.className = 'hide';
						mxinput.value ='';
						mxinput.focus();
						
						
				
				},
				
				ev : function( o ){
					var elx = this;
					
					o[eventLister] = function(){
						
						mxinput.classNam ='i_add';
						doc.getElementById("err").className = 'cr hide';
						if(this.value.replace(/\s/g,'') =='') {ajaxDate.className = 'hide';return false;}
						var xmlvalue,XHR;
						var objValue = this.value ;
						var strValue = objValue.toString();
						var stv = strValue.replace(/\s/g,'');
						ajaxDate.className ='';
						take.setAttribute("date-list",stv);
						take.innerHTML = stv;
						var url = G_BASE_URL+'/search/?act=all&type=topic_v2&q='+stv+'&limit=100';

						
							XHR = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("microsoft.XMLHTTP");
							XHR.open("GET",url,true); 
							XHR.onreadystatechange = function(){
							
							 if( XHR.readyState == 4 && XHR.status == 200 ){
									
									xmlvalue = XHR.responseText;
									var x = new Function('return'+xmlvalue)();
									
									for(var i=-1,len= x.length; ++i<len;){
										
										//判断是否相同的话题，否则出现添加话题
										if(i!==0){
											
												txt.getElementsByTagName("span")[0].className =' hide';
											var elementsAjax = doc.createElement("div");
												elementsAjax.onclick = function(){ //ie7模式
													add(this.id);
												};
												//elementsAjax.setAttribute("onclick","add('"+x[i].sno+"');");
												elementsAjax.id =  x[i].sno;
												elementsAjax.setAttribute("date-list",x[i].name);
												elementsAjax.innerHTML = x[i].name+'<p>关注：<span>'+x[i].detail.focus_count+'</span> 人</p>';
												txt.appendChild(elementsAjax);
												
												
											
										}else if(i==0){
											
											if(x[i].exist == 0){
												take.parentNode.className = 'add';
												txt.getElementsByTagName("span")[0].className =' hide';
											}else if(x[i].exist == 1){
												take.parentNode.className = 'add hide';
											}
										}
									
									}
									
									//txt.innerHTML = t;
									
							   }else{
								
									txt.innerHTML = "<span style='color:#999;'><img src='"+G_STATIC_URL+"/css/img/load.gif'/> 正在加载..</span>";
								
							   }

							};
							
							XHR.send(null);
									
							
					}
					
					doc.onclick = function(){
						ajaxDate.className = 'hide';
						//mxinput.classNam ='i_add';
						//doc.getElementById("err").className = 'cr hide';
					}
					
				},
				
				fc : function(e){
				
					e.ev(mxinput);
				},
				info : function(){
				
					if(mxId.parentNode.nodeName === "LI"){
						mxId.parentNode.appendChild(d);
					}
					var el = this;
			        try{
						mxId['onclick'] = function(){ mxinput.focus();}
						mxinput['onfocus'] = function(){el.fc(el)};
						
					}catch(w){}
					
				}
				
			
			}
			
			
		},
		
		remove : function(){
		
			var o = this;
			o.parentNode.parentNode.removeChild(o.parentNode);
			
		}
	}
	
	
var t = new m.mx();t.info();

function add(x){

	var o = 'string' == typeof x ? document.getElementById(x) : x;
	t.app(o);
}