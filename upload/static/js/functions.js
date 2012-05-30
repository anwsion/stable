jQuery.fn.extend({
	highText : function (searchWords, htmlTag, tagClass) {
		return this.each(function() {
			$(this).html(function high(replaced, search, htmlTag, tagClass) {
				var pattarn = search.replace(/\b(\w+)\b/g, "($1)").replace(/\s+/g, "|");
				
				return replaced.replace(new RegExp(pattarn, "ig"), function(keyword) {
					return $("<" + htmlTag + " class=" + tagClass + ">" + keyword + "</" + htmlTag + ">").outerHTML();
				});
			}($(this).text(), searchWords, htmlTag, tagClass));
		});
	},
	outerHTML : function(s) {
		return (s) ? this.before(s).remove() : jQuery("<p>").append(this.eq(0).clone()).html();
	}
});

if ($.browser.msie && $.browser.version == "6.0" && !$.support.style)
{
	$(document).ready(function () {
  		new DialogBox_show('640', '<p class="p" style="font-size: 14px">你目前使用的浏览器版本过低，将不能使用本站部分功能。请升级你的 <a href="http://windows.microsoft.com/zh-CN/internet-explorer/downloads/ie-8">Internet Explorer 浏览器</a>获取最佳的浏览效果<br /><br /><div align="center"><a href="http://www.google.cn/chrome/eula.html?hl=zh-CN&platform=win" style="font-size: 16px">点击下载谷歌浏览器</a> | <a href="http://windows.microsoft.com/zh-CN/internet-explorer/downloads/ie-8" style="font-size: 16px">点击下载 Internet Explorer 浏览器</a></div></p>', '提示信息', '');
	});
}

// Test:
/*$(document).ready(function () {
	$('.footer a').highText('关于', 'span', 't');
});*/

/*function qDialogue(content, title) {
   $('<div />').qtip(
   {
      content: {
         text: content,
         title: title
      },
      position: {
         my: 'center',
         at: 'center', // Center it...
         target: $(window) // ... in the window
      },
      show: {
         ready: true, // Show it straight away
         modal: {
            on: true, // Make it modal (darken the rest of the page)...
            blur: false // ... but don't close the tooltip when clicked
         }
      },
      hide: false, // We'll hide it maunally so disable hide events
      style: 'ui-tooltip-light ui-tooltip-rounded ui-tooltip-dialogue', // Add a few styles
      events: {
         // Hide the tooltip when any buttons in the dialogue are clicked
         render: function(event, api) {
            $('button', api.elements.content).click(api.hide);
         },
         // Destroy the tooltip once it's hidden as we no longer need it!
         hide: function(event, api) { api.destroy(); }
      }
   });
}*/

function qAlert(message)
{
  //qDialogue($('<p />', { text: message,'class':'p' }).add($('<button />', { text: '确定', 'style': 'width:100%; text-align:center;' })), '提示信息');

  /************************
	  
	  DialogBox_show( boxWidth,html,titleTxt,butTxt )
	  
	  参数说明：
	  1、boxWidth为弹出盒子的宽度，可以设置大小，比如：255，如果不设置则为默认450的宽。
	  2、html可以任意添加代码、样式以及脚本。
	  3、titleTxt为头部的提示信息。
	  4、butTxt为按钮文字。
	  
  ************************/
  var html  = '<p class="p">'+ message +'</p>';
  
  new DialogBox_show( '255', html,'提示信息', '确定');
}

//公用弹出框
function dfAlert(){
	
}

function ajax_request(url, params)
{	
	if (params)
	{
		$.post(url, params, function (result) {
			qAlert(result.err);
		}, 'json');
	}
	else
	{
		$.get(url, function (result) {
			qAlert(result.err);
		}, 'json');
	}
	
	return false;
}

function ajax_post(formEl, processer)	// 表单对象，用 jQuery 获取，回调函数名
{	
	if (typeof(processer) != 'function')
	{
		processer = _ajax_post_processer;
	}
	
	formEl.ajaxSubmit({
		dataType:	'json',
		success:	processer
	});
}

function _ajax_post_processer(result)
{
	if (typeof(result.errno) == 'undefined')
	{
		qAlert(result);
	}
	else if (result.errno != 1)
	{
		qAlert(result.err);
	}
	else
	{
		if (typeof(result.rsm) != 'undefined')
		{
			if (result.rsm.url)
			{
				window.location = decodeURIComponent(result.rsm.url);
			}
			else
			{
				window.location.reload();
			}
		}
		else
		{
			window.location.reload();
		}
	}
}

function _ajax_post_alert_processer(result)
{
	if (typeof(result.errno) == 'undefined')
	{
		alert(result);
	}
	else if (result.errno != 1)
	{
		alert(result.err);
	}
	else
	{
		if (typeof(result.rsm) != 'undefined')
		{
			if (result.rsm.url)
			{
				window.location = decodeURIComponent(result.rsm.url);
			}
			else
			{
				window.location.reload();
			}
		}
		else
		{
			window.location.reload();
		}
	}
}


function _ajax_post_tip_processer(result)
{
	alert(result.err);
	
	if (result.errno == 1)
	{
		if (typeof(result.rsm) != 'undefined')
		{
			if (result.rsm.url)
			{
				window.location = decodeURIComponent(result.rsm.url);
			}
		}
	}
}

//自动计算字数
function data_len(data)
{
	var int_length=0;
	for (var i = 0; i < data.length; i++)
	{
   		if ((data.charCodeAt(i) < 0) || (data.charCodeAt(i) > 255))
		{
    		int_length += 2;
		}
		else
    	{
			int_length += 1;
		}
	}
	return int_length;
}

function set_string(str,len)
{
	var strlen = 0; 
	var s = "";
	for(var i = 0;i < str.length;i++)
	{
		if(str.charCodeAt(i) > 128)
		{
			strlen += 2;
		}
		else
		{ 
			strlen++;
		}
		s += str.charAt(i);
		if(strlen >= len)
		{ 
			return s ;
		}
	}
	return s;
}

/*function focus_competition(el, text_el, competition_id)
{
	if (el.hasClass('cur'))
	{
		text_el.html('关注');
	}
	else
	{
		text_el.html('取消关注');
	}
	
	el.addClass('load');
	
	$.get('/competitions/?c=competitions&act=competitions_focus_ajax&competitions_id=' + competition_id + '&rnd=' + Math.random(), function (data)
	{
		if (data.errno == 1)
		{
			if (data.rsm.type == 'add')
			{
				el.addClass('cur');
			}
			else
			{
				el.removeClass('cur');
			}
		}
		else
		{
			if (data.err != '') alert(data.err);
			
			if (data.rsm.url != '')
			{
				window.location = decodeURIComponent(data.rsm.url);
			}
		}
		
		el.removeClass('load');
	}, 'json');
}*/

function focus_question(el, text_el, question_id)
{
	if (el.hasClass('cur'))
	{
		text_el.html('关注');
	}
	else
	{
		text_el.html('取消关注');
	}
	
	el.addClass('load');
	
	$.get(G_BASE_URL + '/question/?c=ajax&act=focus&question_id=' + question_id + '&vote_value=-1&rnd=' + Math.random(), function (data)
	{
		if (data.errno == 1)
		{
			if (data.rsm.type == 'add')
			{
				el.addClass('cur');
			}
			else
			{
				el.removeClass('cur');
			}
		}
		else
		{
			if (data.err != '')
			{
				alert(data.err);
			}
			
			if (data.rsm.url != '')
			{
				window.location = decodeURIComponent(data.rsm.url);
			}
		}
		
		el.removeClass('load');
	}, 'json');
}

function focus_topic(el, text_el, topic_id)
{
	el.addClass('load');
	
	$.get(G_BASE_URL + '/topic/?act=focus_topic&topic_id=' + topic_id + '&rnd=' + Math.random(), function (data)
	{
		if (data.errno == 1)
		{
			if (data.rsm.type == 'add')
			{
				el.addClass('cur');
				text_el.html('取消关注');
			}
			else
			{
				el.removeClass('cur');
				text_el.html('关注');
			}
		}
		else
		{
			if (data.err != '')
			{
				alert(data.err);
			}
			
			if (data.rsm.url != '')
			{
				window.location = decodeURIComponent(data.rsm.url);
			}
		}
		
		el.removeClass('load');
	}, 'json');
}

function follow_people(el, text_el, uid)
{
	el.addClass('load');
	
	$.get(G_BASE_URL + '/follow/?act=people_follow_edit_ajax_action&uid=' + uid + '&rnd=' + Math.random(), function (data)
	{
		if (data.errno == 1)
		{
			if (data.rsm.type == 'add')
			{
				el.addClass('cur');
				text_el.html('取消关注');
			}
			else
			{
				el.removeClass('cur');
				text_el.html('关注');
			}
		}
		else
		{
			if (data.err != '')
			{
				alert(data.err);
			}
			
			if (data.rsm.url != '')
			{
				window.location = decodeURIComponent(data.rsm.url);
			}
		}
		
		el.removeClass('load');
	}, 'json');
}

function show_prompt(data, w, h)
{
	return $.fancybox(data, {
		width: w,
		height: h,
		autoSize: false,
		closeClick: false
	});
}

function update_signature(signature)
{
	if (!signature)
	{
		qAlert('请用一句话介绍自己');
		
		return false;
	}
	
	$.post(G_BASE_URL + '/account/?c=ajax&act=update_signature', 'signature=' + signature, function (result) {			
		qAlert(result.err);
	}, "json");
}

function push_weibo(array_data)
{
	$.post(G_BASE_URL + '/account/?c=ajax&act=weibo_push_ajax', array_data, function (result) {			
		alert(result.err);
		
		if (result.errno == 1)
		{
			window.location.reload();
		}
	}, "json");
}

function send_invite_question_mail(array_data)
{
	$.post(G_BASE_URL + '/account/?c=ajax&act=send_invite_question_mail', array_data, function (result) {			
		alert(result.err);
		
		if (result.errno == 1)
		{
			window.location.reload();
		}
	}, "json");
}

/*function send_invite_competition_mail(array_data)
{
	$.post('/account/?c=ajax&act=send_invite_competition_mail', array_data, function (result) {			
		alert(result.err);
		
		if (result.errno == 1)
		{
			window.location.reload();
		}
	}, "json");
}*/

function mark_notifications(notify_id)
{
	if (notify_id)
	{
		$("#notification_" + notify_id).remove();
		$("#announce_num").html(String(Number($("#announce_num").html()) - 1));
		notification_show(5);
		var url = G_BASE_URL + '/notifications/?act=r_notify&notify_id=' + notify_id + '&read_type=1&rnd=' + Math.random();
	}
	else
	{
		$("#notitile_all").fadeOut();
		var url = G_BASE_URL + '/notifications/?act=r_notify&read_type=0&rnd=' + Math.random();
	}
	
	$.get(url, function (respose)
	{
		notifications();
	});
}

function ajax_load(url, target)
{
	$(target).html('<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/css/img/loading_b.gif" alt="" /></p>');
	
	$.get(url, function (response)
		{
			if (response.length)
			{
				$(target).html(response);
			}
			else
			{
				$(target).html('<p style="padding: 15px 0" align="center">没有内容</p>');
			}
	});
}

var _bp_more_o_inners = new Array();
var _bp_more_pages = new Array();

function bp_more_load(url, bp_more_o_inner, target_el)
{
	//if (typeof(_bp_more_pages[bp_more_o_inner.attr('id')]) == 'undefined')
	//{
		_bp_more_pages[bp_more_o_inner.attr('id')] = 0;
	//}
	
	//if (typeof(_bp_more_o_inners[bp_more_o_inner.attr('id')]) == 'undefined')
	//{
		_bp_more_o_inners[bp_more_o_inner.attr('id')] = bp_more_o_inner.html();
	//}
	
	bp_more_o_inner.unbind('click');
	
	bp_more_o_inner.bind('click', function () {
		var _this = this;
			
		$(this).addClass('loading');
		
		$(this).find('a').html('正在载入...');
			
		$.get(url + '&page=' + _bp_more_pages[bp_more_o_inner.attr('id')]  + '&version=2', function (response)
		{
			if (response.length)
			{
				if (_bp_more_pages[bp_more_o_inner.attr('id')] == 0)
				{
					target_el.html(response);
				}
				else
				{
					target_el.append(response);
				}
							
				_bp_more_pages[bp_more_o_inner.attr('id')]++; 
				
				$(_this).html(_bp_more_o_inners[bp_more_o_inner.attr('id')]);
			}
			else
			{
				if (_bp_more_pages[bp_more_o_inner.attr('id')] == 0)
				{
					target_el.html('<p style="padding: 15px 0" align="center">没有内容</p>');
				}
							
				$(_this).addClass('disabled');
						
				$(_this).find('a').html('没有更多了');
			}
				
			$(_this).removeClass('loading');
		});
			
		return false;
	});
	
	bp_more_o_inner.click();
}

function content_switcher(hide_el, show_el)
{
	hide_el.hide();
	show_el.fadeIn();
}

function _pm_form_processer(result)
{
	if (result.errno != 1)
	{		
		if (typeof(result.rsm) == 'undefined')
		{
			alert(result.err);
		}
		else
		{
			alert(result.err);
		}
	}
	else
	{
		if (typeof(result.rsm) != 'undefined' && typeof(result.rsm) != 'null')
		{	
			if (result.rsm)
			{
				if (typeof(result.rsm.url) != 'undefined')
				{
					window.location = decodeURIComponent(result.rsm.url);
				}
				else if (typeof(result.rsm.element_id) != 'undefined')
				{
					alert(result.err);
			
					$('#' + result.rsm.element_id).fadeOut();
				}
				else if (typeof(result.rsm.click_id) != 'undefined')
				{
					alert(result.err);
					
					if (window.location.href.indexOf('/inbox/') == -1)
					{
						try {
							document.getElementById(result.rsm.click_id).click();
						}
						catch (e)
						{
							window.location.reload();
						}
					}
					else
					{
						window.location.reload();
					}
				}
			}
			else
			{
				alert(result.err);
			
				window.location.reload();
			}
		}
		else
		{
			alert(result.err);
			
			window.location.reload();
		}
	}
}

function _tips_form_processer(result)
{
	if (result.errno != 1)
	{		
		if (typeof(result.rsm) == 'undefined')
		{
			qAlert(result.err);
		}
		else if (result.rsm)
		{
			if (document.getElementById('tip_' + $('input[name=' + result.rsm.input + ']').attr('id')))
			{
			
				$('#tip_' + $('input[name=' + result.rsm.input + ']').attr('id')).removeClass().addClass('err').html(result.err).show();
			}
			else
			{
				qAlert(result.err);
			}
		}
		else
		{
			qAlert(result.err);
		}
	}
	else
	{
		try {
			window.location = decodeURIComponent(result.rsm.url);
		} catch(e) {
			window.location.reload();
		}
		
	}
}

/*function set_contribute_award(contribute_id)
{
	$.post('/competitions/?c=ajax&act=set_contribute_award', 'contribute_id=' + contribute_id, function (result) {
		if (result.errno == 1)
		{
			$('.contributes_list_' + contribute_id).addClass('user_winner');
		}
		
		qAlert(result.err);
	}, 'json');
}*/

function hightlight(el, class_name)
{
	if (el.hasClass(class_name))
	{
		return true;
	}
	
	//window.scrollTo(0, (el.position()['top'] - 5));
	
	var hightlight_timer_front = setInterval(function () {
		el.addClass(class_name);
	}, 500);
	
	var hightlight_timer_background = setInterval(function () {
		el.removeClass(class_name);
	}, 600);
	
	setTimeout(function () {
		clearInterval(hightlight_timer_front);
		clearInterval(hightlight_timer_background);
		
		el.addClass(class_name);
	}, 1200);
	
	setTimeout(function () {
		el.removeClass(class_name);
	}, 4000);
}

// 回车换成<BR/>
function nl2br(str)
{
/*
	alert(str);
var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '' : '<br>';
    str= (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
	alert(str);
	return(str);

	var reg=new RegExp("\r\n","g");
	var reg2=new RegExp("\n","g");
    str = str.replace(reg,"<br>");
	str = str.replace(reg2,"<br>");
*/	
	//str= (str + '').replace(/(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
	var reg = new RegExp("\r\n|\n\r|\r|\n", "g");
	str = str.replace(reg, "<br />");
	return str;
}

// 目标 Element, 文字 Element, Ajax 处理 URL, ajax_post 处理函数, 是否 Textarea 模式
function ajax_edit(el, text_el, ajax_url, _ajax_post_processer, is_textarea, hide_el)
{
	if (hide_el)
	{
		hide_el.hide();
	}
	
	if (el.find('form[name=_ajax_edit_form]').attr('action'))
	{		
		return false;
	}

	var text_content = strip_tags(text_el.html().replace(/<br \/>/g, "\n"));
	
	if (!_ajax_post_processer)
	{
		_ajax_post_processer = _ajax_edit_default_post_processer;
	}
	
	if (is_textarea)
	{
		el.html('<form action="' + ajax_url + '" method="post" class="quick_edit" name="_ajax_edit_form"><p style="width:' + el.width() + 'px"><textarea name="content" class="default_textArea" onfocus="if(this.value.replace(\/^\\s+\/,\'\').replace(\/\\s+\/g,\'\')==\'请为话题编辑头像和详细说明...\'){this.value=\'\'}"  onblur="if(this.value.replace(\/^\\s+\/,\'\').replace(\/\\s+\/g,\'\')==\'\'){this.value=\'请为话题编辑头像和详细说明...\'}">' + text_content + '</textarea></li><p align="right" style="padding-top:5px;"><a href="javascript:;" onclick="ajax_post($(this).parent().parent(),' + _ajax_post_processer + ');default_hide();" class="default_blue_but">保存</a></p></form>');
	}
	else
	{
		el.html('<form action="' + ajax_url + '" class="quick_edit" method="post" name="_ajax_edit_form"><input name="content" value="' + text_content + '" /> <a href="javascript:;" onclick="ajax_post($(this).parent(), ' + _ajax_post_processer + '); return false;" class="default_blue_but">保存</a></form>');
	}
}
		
function strip_tags(input, allowed) {
    allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join('');
    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
        commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
    return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
    });
} 


function _ajax_edit_default_post_processer(result)
{
	if (result.errno != 1)
	{		
		qAlert(result.err);
	}
	else
	{
		try {
			document.getElementById(result.rsm.target_id).innerHTML = result.err;
		} catch(e) {
			try {
				window.location = decodeURIComponent(result.rsm.url);
			} catch(e) {
				window.location.reload();
			}
		}
		
		try {
			$(document.getElementById(result.rsm.display_id)).show();
		} catch(e) {
			
		}
	}
}

var doc = document,_$$$ = function(o){return 'string' == typeof o ? doc.getElementById(o) : o ;};
function DialogBox_show( boxWidth,html,titleTxt,butTxt,fn ){
	
	this[0] = document;
	this.elementsMaskBox = elementsMaskBox;
	this.elScroll = this[0].documentElement.scrollHeight;
	this.bd = this[0].getElementsByTagName("body")[0];

	this.eWidth = 450; //默认弹出框宽度
	this.info( boxWidth,html,titleTxt,butTxt );
	
	if(fn!=null){fn();}
};

var elementsMaskBox = document.createElement("div"),boxPup = document.createElement("div");

DialogBox_show.prototype.add_mask = function(){
	
	this.setAttr(this.elementsMaskBox,{className:'w_mask',id:'w_mask'});
	this.bd.appendChild(this.elementsMaskBox);
	this.setAttr(this.elementsMaskBox.style,{height:this.elScroll+'px'});
	
};

DialogBox_show.prototype.elementsTag = function( boxWidth,HTML,s,txt ){
	
	var boxW = boxWidth == null ?  this.eWidth : boxWidth;

	this.setAttr(boxPup,{className:'w_tagPupD',id:'w_tagPupD'});
	
	this.a = this[0].createElement("a");
	this.a.href = 'javascript:void(0)';
	this.a.className = 'w_sub';
	this.a.onclick = function(){
		
		hidePupBox('w_tagPupD','w_mask');
	};
	this.a.innerHTML = txt;
	
	this.setAttr(boxPup.style,{width:boxW+'px'});
	
	var INNERHTML = '<div class="b"><h3 class="w_title">'+s+'</h3>'+
					'<div class="w_Contxt">'+HTML+'</div>'+
					'<div class="w_submte" id="w_submit"></div></div>';
					
	
	boxPup.innerHTML = INNERHTML;
	
	
	this.bd.appendChild(boxPup);
	
	var submitP = this[0].getElementById('w_submit');
		if(txt!=null && txt !=''){
			submitP.appendChild(this.a);
		}else{
			submitP.className = 'w_submte hide';
		}
	
	var scllW = boxPup.scrollWidth/2,scllH = boxPup.scrollHeight/2;
	this.setAttr(boxPup.style,{marginLeft:-scllW+'px',marginTop:-scllH+'px'});
	
}


DialogBox_show.prototype.info = function( boxWidth,h,s,txt ){
	
	this.elementsTag( boxWidth,h,s,txt );
	this.add_mask();
	
}

DialogBox_show.prototype.setAttr = function( o,Class ){
	
	for(var k in Class){
		
		o[k] = Class[k];
		
	}
};

function hidePupBox( o,k ){
	
	var PupBox = document.getElementById( o ),ar=[];
	var elementsMaskBox = document.getElementById(k);
	var bd = document.getElementsByTagName("body")[0];
	ar.push(PupBox,elementsMaskBox);

	for(var i in ar){
		
		
		bd.removeChild(ar[i]);
		
	}
	
}

function init_img_uploader(upload_url, upload_name, upload_element, upload_status_elememt, perview_element)
{
    return new AjaxUpload(upload_element, {
        action: upload_url,
        name: upload_name,
        responseType: 'json',
        
        onSubmit: function (file, ext) {
            var re = new RegExp('(png|jpg|jpeg|gif|bmp)$', 'i');

            if (!re.test(ext))
            {
                qAlert("请选择正确的图片文件");
                
                return false;
            }
			
            this.disable();
            
            if (upload_status_elememt)
            {
            	upload_status_elememt.show();
            }
        },
        
        onComplete: function (file, response) {
            this.enable();
            
			if (upload_status_elememt)
            {
            	upload_status_elememt.hide();
            }
            
            if (response.errno == "-1")
			{
            	qAlert(response.err);
        	}
        	else
        	{
            	perview_element.attr("src", response.rsm.preview + "?" + Math.random());
            }		
        }
    });
}

function init_avatar_uploader(upload_element, upload_status_elememt, avatar_element)
{
	return init_img_uploader(G_BASE_URL + '/account/?c=setting&act=myface_ajax_upload_action', 'user_avatar', upload_element, upload_status_elememt, avatar_element);
}

function init_fileuploader(element_id, action_url)
{
	if (!document.getElementById(element_id))
	{
		return false;
	}
	
	return new _ajax_uploader.FileUploader({
		element: document.getElementById(element_id),
		action: action_url,
		debug: false
	});
}

function share_content(share_type, target_id, focus_tab, weibo_enabled)
{
	if(typeof focus_tab == 'undefined')
	{
		focus_tab = "weibo";
	}

	switch(share_type)
	{
		//请求分享内容
		case 'question' : 
			url = G_BASE_URL+"/question/?c=ajax&act=question_share_txt&question_id=" + target_id;
			share_title = "分享问题";
			break;
		case 'answer' : 
			url = G_BASE_URL+"/question/?c=ajax&act=answer_share_txt&answer_id=" + target_id;
			share_title = "分享回复";
			break;
	}
	
	if(typeof url == 'undefined')
	{
		return false;
	}
	
	show_share_box(share_title, weibo_enabled);
	
	$("#menu_s").find("li[rel=" + focus_tab + "]").click();
	
	$.getJSON(url, function(result)
	{
		if(result.errno == '1')
		{
			var share_txt = result.rsm.share_txt;
			
			$(".txt_area[rel=weibo]").html(share_txt.weibo);
			$(".txt_area[rel=mail]").html(share_txt.mail);
			$(".txt_area[rel=message]").html(share_txt.message);
		}
	});
}
	
	
function show_share_box(share_title, weibo_enabled)
{
	var title = '<a href="javascript:;" onclick="hidePupBox(\'w_tagPupD\',\'w_mask\');" class="close_ti" title="点击关闭对话框 ">close</a>' + share_title;
	
	var html = '<ul id="menu_s">';
	
	if (weibo_enabled == 'Y')
	{
		html += '<li class="cur" onclick="elementClickEvent.tabs(this);" rel="weibo" uid="0">微博分享</li>'+
				'<li onclick="elementClickEvent.tabs(this);" rel="mail" uid="1">邮件分享</li>'+
				'<li onclick="elementClickEvent.tabs(this);" rel="letter" uid="2">站内信分享</li>'+
				'</ul>';
	}else{
		html += '<li onclick="elementClickEvent.tabs(this);" rel="mail" uid="0">邮件分享</li>'+
				'<li onclick="elementClickEvent.tabs(this);" rel="letter" uid="1">站内信分享</li>'+
				'</ul>';
	}		//微博分享
	
	if (weibo_enabled == 'Y')
	{
		html += '<div id="weibo_share" class="class_share"></div>';
	}

					//邮件分享
		html += '<div class="class_share hide" id="mail_share"><form id="mail_share_form" method="post" action=""><ul class="txt_list">'+
					'<li><label for="inputsType_txt">收件人：</label><input type="text" name="email_address" class="txt_input"/></li>'+
					'<li><label for="inputsType_area">内容：</label><textarea rel="mail" class="txt_area" name="email_message"></textarea></li>'+
				'</ul><p class="tr"><a href="javascript:;" onclick="send_invite_question_mail($(\'#mail_share_form\').formToArray());" class="set_msg">发送</a><a href="javascript:;" onclick="hidePupBox(\'w_tagPupD\',\'w_mask\');">取消</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p></form></div>'+
				
					//站内分享
				'<div class="class_share hide" id="letter_share"><form id="message_share_form" method="post" action="'+G_BASE_URL + '/inbox/?c=main&act=write_message&click_id=share_box_close"><ul class="txt_list">'+
					'<li><label for="userShare_txt">发给：</label><input type="text" name="recipient" class="txt_input" id="userShare_txt"/><div class="ajax_date hide"></div></li>'+
					'<li><label for="inputsType_area">内容：</label><textarea name="message" rel="message" class="txt_area"></textarea></li>'+
				'</ul><p class="tr"><a href="javascript:;" onclick="ajax_post($(\'#message_share_form\'), _pm_form_processer); return false;" class="set_msg">发送</a><a href="javascript:;" id="share_box_close" onclick="hidePupBox(\'w_tagPupD\',\'w_mask\');">取消</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p></form></div>';

	
	new DialogBox_show('500', html, title, '', function()
	{
		$('#w_tagPupD').css('marginTop', '-130px');
	});
	
	//判断微博绑定
	$.getJSON(G_BASE_URL + '/account/?c=ajax&act=weibo_bind', function(result)
	{
		if (result.errno == 1)
		{
			var qq_weibo = result.rsm.qq_weibo.name;
			var sina_weibo = result.rsm.sina_weibo.name;

			if(result.rsm.qq_weibo.enabled != 'Y' && result.rsm.sina_weibo.enabled != 'Y')
			{
				return;
			}
			
			var weibo_html = '';
			
			if (qq_weibo == null && result.rsm.qq_weibo.enabled == 'Y')
			{
				weibo_html += '<p class="sc-wb"><a title="腾讯微博" target=_blank href="'+G_BASE_URL + '/account/?c=qq&amp;act=binding"><img src="' + G_STATIC_URL + '/css/img/share_tx_wb.gif">绑定腾讯微博帐号</a></p>';
			}
			
			if (sina_weibo == null && result.rsm.sina_weibo.enabled == 'Y')
			{
				weibo_html += '<p class="sc-wb"><a title="新浪微博" target=_blank href="'+G_BASE_URL + '/account/?c=sina&amp;act=binding"><img src="' + G_STATIC_URL + '/css/img/icoSina.gif">绑定新浪微博帐号</a></p>';
			}
			
			if (qq_weibo == null && sina_weibo == null)
			{
				weibo_html += '<p class="p">绑定微博之后，可以方便把精彩问答分享到该微博，并可以邀请你的微博好友来回答该问题或者加入 ' + G_SITE_NAME + '</p>';
			}

			if (qq_weibo != null || sina_weibo != null)
			{
				weibo_account = '';
				wb_select = '';
				
				if (sina_weibo != null)
				{
					weibo_account += '新浪微博帐号：<a href="javascript:;">' + sina_weibo + '</a>';
					wb_select += '<input type="checkbox" value="3" checked="checked" name="push_sina"> 新浪微博</label>';
				}
				
				if (qq_weibo != null)
				{
					weibo_account += '，腾讯微博帐号：<a href="javascript:;">' + qq_weibo + '</a>';
					wb_select += '&nbsp;&nbsp;<input type="checkbox" value="3" checked="checked" name="push_qq"> 腾讯微博</label>';
				}
				
				weibo_html += '<p class="sc-wb">已经绑定' + weibo_account + '</p><form id="share_weibo_from" method="post" action=""><textarea rel="weibo" name="push_message" class="txt_area" style="width:430px;"></textarea>' + wb_select + '<p class="tr"><a href="javascript:;" onclick="push_weibo($(\'#share_weibo_from\').formToArray());" class="set_msg">发布</a><a href="javascript:;" onclick="hidePupBox(\'w_tagPupD\',\'w_mask\');">取消</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p></form>';
			}
			
			$('#weibo_share').html(weibo_html);
		}
	});
}

function htmlspecialchars(text)  
{  
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function delete_draft(item_id, type)
{
	$.post(G_BASE_URL + '/account/?c=ajax&act=delete_draft', 'item_id=' + item_id + '&type=' + type, function (result) {
		if (result.errno != 1)
		{
			qAlert(result.err);
		}
	}, 'json');
}

function agree_show(el)
{
	el.hide();
	el.parent().find(".defa_agree").show();
	el.parent().find(".defa_oppose").show();
	if(Number(el.html()) >= 1) el.parent().find("[name=agree_user_list]").show();
}


function change_agree(el, st)
{
	if(el.attr('ajax_doing') == '1')
	{
		return;
	}
	
	el.attr('ajax_doing', '1');
	
	var alc = el.parent().find('a').first();
	var agree_num = el.parent().find('[name=agree_num]');
	var status = String(alc.attr('status'));
	var answer_id = alc.attr('name');
	var new_ag_users = new Array();

	el.parent().find('.defa_agree').removeClass("up_cur").attr("title", "投一票");
	el.parent().find('.defa_oppose').removeClass("down_cur").attr("title", "反对，不会显示你的姓名");
	
	if(status == st)
	{
		alc.attr('status', 0);
		if(st == '1')
		{
			agree_num.html(Number(agree_num.html()) - 1);
			el.parent().find('a[rel=' + USER_ID + ']').remove();
		}
	}
	else
	{
		alc.attr('status', st);

		if(st == '1')
		{
			agree_num.html(Number(agree_num.html()) + 1);
			el.parent().find('a[rel=' + USER_ID + ']').remove();
			var cur_user = new Array();
			cur_user[0] = USER_ID;
			cur_user[1] = USER_NAME;
			new_ag_users.push(cur_user);
			el.addClass("up_cur").parent().find('.defa_agree').attr("title", "取消投票");
		}
		else if(st == '-1')
		{
			if(status == '1')
			{
				agree_num.html(Number(agree_num.html()) - 1);
				el.parent().find('a[rel=' + USER_ID + ']').remove();
			}
			el.addClass("down_cur").parent().find('.defa_oppose').attr("title", "取消反对");
		}
	}

	el.parent().find('[name=agee_people]').each(function (){
		var user = new Array();
		user[0] = $(this).attr('rel');
		user[1] = $(this).html();
		new_ag_users.push(user);
	});
	
	var count = 0;
	var new_aus_str = "";
	$(new_ag_users).each(function (i){
		new_aus_str += '<a href="'+G_BASE_URL + '/people/?u=' + new_ag_users[i][0] + '" rel="' + new_ag_users[i][0] + '" onmouseover="eventsMouseM(this);" name="agee_people" class="a">' + new_ag_users[i][1] + '</a>';
		if(new_ag_users.length > ++count) new_aus_str += "、";
	});
	
	el.parent().find(".agee_people").html(new_aus_str);
	
	if(new_ag_users.length == 0)
	{
		el.parent().find("[name=agree_user_list]").hide();
	}
	else
	{
		el.parent().find("[name=agree_user_list]").show();
	}
	
	$.post(G_BASE_URL + '/question/?c=ajax&act=change_vote', "answer_id=" + answer_id + "&value=" + st, function (result) {
		if (result.errno == '1')
		{
			el.removeAttr('ajax_doing');
		}
	
	}, 'json');

	return true;
}


//问题-不感兴趣
function question_uninterested(el, question_id)
{
	el.fadeOut();
	
	$.post(G_BASE_URL + '/question/?c=ajax&act=uninterested', 'question_id=' + question_id, function (result) {
		if (result.errno != '1')
		{
			alert(result.err);
		}
	}, 'json');
}

function cancel_question_invite(el, question_id, recipients_uid)
{
	$.post(G_BASE_URL + '/question/?c=ajax&act=cancel_question_invite', 'question_id=' + question_id + '&recipients_uid=' + recipients_uid, function (result) {
		if (result.errno == '1')
		{
			el.parent().remove();
		}
		else
		{
			alert("错误:" + result.rsm.err);
		}

	}, 'json');
}

function question_invite_delete(el, question_invite_id)
{
	$.post(G_BASE_URL + '/question/?c=ajax&act=question_invite_delete', 'question_invite_id=' + question_invite_id, function (result) {
		if (result.errno == '1')
		{
			el.parent().parent().remove();
		}
		else
		{
			alert("错误:" + result.rsm.err);
		}

	}, 'json');
}

function notification_show(max)
{
	var n_count = 0;
	$("#notification_list").find("li").each(function()
	{
		if(n_count < 5)
		{
			$(this).show();
		}
		else
		{
			$(this).hide();
		}
		n_count ++;
	});
	
	if($("#notification_list").find("li").size() == 0)
	{
		if ($('#notitile_all'))
		{
			$('#notitile_all').fadeOut();
		}
	}
}

function toggle_answer_comments(answer_id)
{
	if ($('#answer_comments_' + answer_id).css('display') == 'none')
	{
		if ($('#answer_comments_list_' + answer_id).html() == '')
		{
			reload_answer_comments_list(answer_id, 'answer_comments_list_' + answer_id);
		}
		
		$('#answer_comments_' + answer_id).fadeIn();
	}
	else
	{
		$('#answer_comments_' + answer_id).fadeOut();
	}
}

function reload_answer_comments_list(answer_id, element_id)
{
	var _element_id = element_id;
	
	$('#' + _element_id).html('<p style="padding: 10px 0" align="center"><img src="' + G_STATIC_URL + '/css/img/load.gif" alt="" /></p>');
	
	$.get(G_BASE_URL + '/question/?c=ajax&act=get_answer_comments&answer_id=' + answer_id, function (data) {
		$('#' + _element_id).html(data);
	});
}

function _answer_comments_form_processer(result)
{
	if (result.errno != 1)
	{		
		qAlert(result.err);
	}
	else
	{
		$('#answer_comments_form_' + result.rsm.answer_id).fadeOut();
		
		reload_answer_comments_list(result.rsm.answer_id, 'answer_comments_list_' + result.rsm.answer_id);
	}
}