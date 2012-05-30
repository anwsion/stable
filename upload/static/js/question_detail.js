hightlight_ids = hightlight_ids.split(',');

$(document).ready(function () {
	if ($.trim($('#addtxt').html()) == '')
	{
		$('#addtxt').hide();
	}
	
	init_fileuploader('file_uploader_answer', G_BASE_URL + '/question/?c=ajax&act=answer_attach_upload&attach_access_key=' + ATTACH_ACCESS_KEY);
	
	$.each($('ul.contxt_List'), function (i, e) {
		if ($(this).attr('uninterested_count') >= uninterested_limit)
		{
			$('#uninterested_answers_list').append('<ul class="contxt_List">' + $(e).html() + '</ul>');
			
			$(e).remove();
		}
	});
	
	if ($('#uninterested_answers_list ul.contxt_List').length > 0)
	{
		$('#load_uninterested_answers span.hide_answers_count').html($('#uninterested_answers_list ul.contxt_List').length);
		$('#load_uninterested_answers').fadeIn();
	}
	
	if ($('textarea#answer_content'))
	{
		$('textarea#answer_content').blur(function() {
			if ($(this).val() != '')
			{
				$.post(G_BASE_URL + '/account/?c=ajax&act=save_draft&item_id=' + question_id + '&type=answer', 'message=' + $(this).val(), function (result) {
					$('#answer_content_message').html(result.err + ' <a href="#" onclick="$(\'textarea#answer_content\').attr(\'value\', \'\'); delete_draft(question_id, \'answer\'); $(this).parent().html(\' \'); return false;">删除草稿</a>');
				}, 'json');
			}
		});
		
		$.get(G_BASE_URL + '/account/?c=ajax&act=get_draft&item_id=' + question_id + '&type=answer', function (result) {
			if (!result)
			{
				return false;
			}
			
			if (typeof(result.message) != 'undefined')
			{
				$('textarea#answer_content').val(result.message);
			}
		}, 'json');
	}
});

function answer_user_rate(answer_id, type, element)
{
	$.post(G_BASE_URL + '/question/?c=ajax&act=question_answer_rate', 'type=' + type + '&answer_id=' + answer_id, function (result) {
		
		switch (type)
		{
			case 'thanks':
				if (result.rsm.action == 'add')
				{
					$(element).html('撤消感谢');
				}
				else
				{
					$(element).html('感谢');
				}
			break;
			
			case 'uninterested':
				if (result.rsm.action == 'add')
				{
					$(element).html('撤消没有帮助');
				}
				else
				{
					$(element).html('没有帮助');
				}
			break;
		}
		
		if (result.errno != 1)
		{
			qAlert(result.err);
		}
	}, 'json');
}

function edit_answer(answer_id)
{	
	new DialogBox_show('740', '<iframe src="' + G_BASE_URL + '/question/?c=ajax&act=edit_answer&answer_id=' + answer_id + '" frameborder="0" height="420" allowtransparency="true" width="720" scrolling="auto"></iframe>', '<a href="javascript:;" onclick="hidePupBox(\'w_tagPupD\',\'w_mask\');" class="close_ti" title="点击关闭对话框 ">close</a>编辑回复', '', function() {
		$('#w_tagPupD').css('marginTop', '-200px');
	});
}

function agree_answer(el, answer_id, disable_class)
{
	var el = el;
	var disable_class = disable_class;
	
	$.post(G_BASE_URL + '/question/?c=ajax&act=agree_answer', 'answer_id=' + answer_id, function (result) {
		if (result.rsm.action == 'agree')
		{
			$(el).addClass(disable_class).find('big').html(parseInt($(el).find('big').html()) + 1);
			$(el).find('em').html('取消');
		}
		else
		{
			$(el).removeClass(disable_class).find('big').html(parseInt($(el).find('big').html()) - 1);
			$(el).find('em').html('赞成');
		}
		
		//if(result.rsm.action == "disagree")
		//$(el).addClass(disable_class).find('big').html(parseInt($(el).find('big').html()) + 1);
	}, 'json');
}
	
$(document).ready(function () {
	$.each(hightlight_ids, function (i, answer_id) {
		hightlight($('#answer_list_' + answer_id), 'wrap_BcakG');
	});
	
	$.get(G_BASE_URL + '/question/?c=ajax&act=get_focus_users&question_id=' + question_id, function (data) {
		$.each(data, function (i, d) {

			if (d['avatar_file'])
			{
				$('#focus_users').append('<a href="' + G_BASE_URL + '/people/?u=' + d['user_name'] + '" onMouseOver="eventsMouseM(this);" rel="' + d['uid'] + '"><img src="' + d['avatar_file'] + '" /></a>');
			}
			else
			{
				$('#focus_users').append('<a href="' + G_BASE_URL + '/people/?u=' + d['user_name'] + '" onMouseOver="eventsMouseM(this);" rel="' + d['uid'] + '"><img src="' + G_STATIC_URL + '/common/avatar-min-img.jpg"/></a>');
			}

		});
	}, 'json');
		
	/*$.get('/question/?c=ajax&act=get_answer_users&question_id=' + question_id, function (data) {
		$.each(data, function (i, d) {
			if (d['avatar_file']!="")
			{
				$('#loc1').append('<a href="/people/?u=' + d['user_name'] + '" onMouseOver="eventsMouseM(this);"  rel="' + d['uid'] + '"><img src="' + G_UPLOAD_URL + '/avatar/' + d['avatar_file'] + '" /></a>');
			}
			else
			{
				$('#loc1').append('<a href="/people/?u=' + d['user_name'] + '" onMouseOver="eventsMouseM(this);"  rel="' + d['uid'] + '"><img src="'+G_STATIC_URL+'/common/avatar-min-img.jpg"/></a>');
			}
		});
	}, 'json');*/
});

//文字内容溢出
var d = document,e0 = d.getElementById("i_txt0"),
	s = function(o){
		var el = o.parentNode;
		el.style.maxHeight='none';
		el.className = 'congtxt';
		o.className ='hide';
		
	};
	


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
				psotUrl = G_BASE_URL + '/question/?act=save_topic&topic_title='+encodeURIComponent(inpsValue)+'&question_id='+question_id+'&rnd=' + Math.random();　
						　
				xmlHttp.open("post",psotUrl,true);　　　　
				xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");　　　　
				xmlHttp.onreadystatechange=function(){
							
					 if( xmlHttp.readyState == 4 && xmlHttp.status == 200 ){
								 
						var x = new Function('return'+xmlHttp.responseText)();
						if(x.errno ==1){
						
							var a = '<a href="' + G_BASE_URL + '/topic/?topic_id='+x.rsm.topic_id+'">'+inpsValue+'</a>';		
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
			
			xmlHttp.send(topic_title=inpsValue,question_id=question_id);
}



var removeChilds = function(x){
	
	var el = x;
	var delectURL = G_BASE_URL + '/question/?act=delte_topic&topic_id='+x.parentNode.getAttribute("id")+'&question_id='+question_id+'&rnd='+Math.random();
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
	　
	xmlHttp.send(delte_topic=x.parentNode.getAttribute("id"),question_id=question_id);
						
						
}



var m = {
	
		mx : function (o){
			
			var doc = document, 
				mxId = doc.getElementById("Manytopics"),
				ajaxDate = doc.getElementById("ajaxDate"),
				take = doc.getElementById("take"),
				txt = doc.getElementById("txt"),
				addtxt = doc.getElementById("addtxt");
			
			var mxinput = mxId.getElementsByTagName("input")[0];
			var mxDiv = mxId.getElementsByTagName("div")[0];
			var mxSpan = mxDiv.getElementsByTagName("span");
			var eventLister = 'onkeydown' && 'onkeyup';
			
			
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
						psotUrl = G_BASE_URL + '/question/?act=save_topic&topic_title='+objValue+'&question_id='+question_id+'&rnd=' + Math.random();　
						　
						xmlHttp.open("post",psotUrl,true);　　　　
						xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");　　　　
						xmlHttp.onreadystatechange=function(){
							
							 if( xmlHttp.readyState == 4 && xmlHttp.status == 200 ){
								 
								var t = xmlHttp.responseText;
								var x = new Function('return'+t)();
								
								
								if(x.errno ==1){
						
									var a = '<a href="' + G_BASE_URL + '/topic/?topic_id='+x.rsm.topic_id+'">'+objValue+'</a>';	
									var s = el.add(a,objValue,x.rsm.topic_id);
									mxDiv.appendChild(s);	
									mxDiv.style.display = 'inline';
									
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
						xmlHttp.send(topic_title=objValue,question_id=question_id);
						
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
						var url = G_BASE_URL + '/search/?act=all&type=topic_v2&q='+stv+'&limit=100';

						
							XHR = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("microsoft.XMLHTTP");
							XHR.open("GET",url,true); 
							XHR.onreadystatechange = function(){
							
							 if( XHR.readyState == 4 && XHR.status == 200 ){
									
									xmlvalue = XHR.responseText;
									var x = new Function('return'+xmlvalue)();
									
									for(var i=-1,len= x.length; ++i<len;){
						
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
			
					mxId['onclick'] = function(){ mxinput.focus();}
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

function add(x){

	var o = 'string' == typeof x ? document.getElementById(x) : x;
	t.app(o);
}