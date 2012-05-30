var cur_page = 0;
var bp_more_inner_o = '';
var cur_uid = 0;
var cur_list_template = 'detail';
var cur_filter = '';

function reload_list(user_id, filter)
{
	if (typeof(user_id) != 'undefined')
	{
		cur_uid = user_id;
	}
	
	if (filter)
	{
		cur_filter = filter;
	}
	else
	{
		cur_filter = '';
	}
	
	$('#list_nav a').removeClass('current');
	
	/*switch (window.location.hash)
	{
		default:
			$('#list_nav a[href=#all]').addClass('current');
		break;
		
		//case '#competitions':
		case '#questions':
			$('#list_nav a[href=' + window.location.hash + ']').addClass('current');
		break;
	}*/
	
	cur_page = 0;
	
	$('#c_list').html('<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/css/img/loading_b.gif" alt="" /></p>');
	
	$('#bp_more').html(bp_more_inner_o);
	
	$('#bp_more').click();
}

$('#list_nav a').click(function () {
	$('#list_nav a').removeClass('current');
	
	$(this).addClass('current');
	
	window.location.hash = $(this).attr('href').replace(/#/g, '');
	
	cur_page = 0;
	
	$('#c_list').html('<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/css/img/loading_b.gif" alt="" /></p>');
	
	$('#bp_more').html(bp_more_inner_o);
	
	$('#bp_more').click();
	
	return false;
});

$(document).ready(function()
{
	if (Number($("#announce_num").html()) > 0)
	{
		request_url =G_BASE_URL+ '/notifications/?act=index_more_ajax&inbox=1&type=no&list_all=1&page=0&rnd=' + Math.random() + '&version=2';
		
		$.get(request_url, function (response)
		{
			if (response.length)
			{
				$("#notification_list").html(response);
				
				notification_show(5)
			}
		});
	}
	
	
/*	if (Number($("#inbox_num").html()) > 0)
	{
		request_url =G_BASE_URL+ '/inbox/?act=index_more_unread_all_ajax&type=unread_all&page=0&rnd=' + Math.random() + '&version=2';
		
		$.get(request_url, function (response)
		{
			if (response.length)
			{
				$("#notification_list").append(response);
				
				notification_show(5)
			}
		});
	}	
*/	
	
	bp_more_inner_o = $('#bp_more').html();
	
	$('#list_nav a').removeClass('current');
	
	/*switch (window.location.hash)
	{
		default:
			$('#list_nav a[href=#all]').addClass('current');
		break;
		
		case '#competitions':
		case '#questions':
			$('#list_nav a[href=' + window.location.hash + ']').addClass('current');
		break;
	}*/
	
	$('#bp_more').click(function()
	{
		var _this = this;
		
		if (parseInt(cur_uid) > 0)
		{
			cur_list_template = 'list';
		}
		else
		{
			cur_list_template = 'detail';
		}
		
		$("#delete_draft").hide();
		$("#c_list").removeClass();
		
		switch (window.location.hash)
		{
			default:
			case '#all':
				$('#list_nav a[href=#all]').addClass('current');

				var request_url = G_BASE_URL + '/index/?c=ajax&act=index_actions&page=' + cur_page  + '&rnd=' + Math.random() + '&version=2&type=all&uid=' + cur_uid + '&template=' + cur_list_template + '&filter=' + cur_filter;
			break;

			case '#draft_list&draft':
				var request_url = G_BASE_URL + '/index/?c=ajax&act=draft&page=' + cur_page  + '&rnd=' + Math.random();
				$("#c_list").addClass("default_draft");
				$("#delete_draft").show();
			break;

			case '#invite_list&invite':
				var request_url = G_BASE_URL + '/index/?c=ajax&act=invite&page=' + cur_page  + '&rnd=' + Math.random();
				$("#c_list").addClass("default_draft");
			break;
			
			/*case '#competitions':
				var request_url = '/index/?c=ajax&act=index_actions&page=' + cur_page  + '&rnd=' + Math.random() + '&version=2&type=competitions&uid=' + cur_uid + '&template=' + cur_list_template + '&filter=' + cur_filter;
			break;
			
			case '#questions':
				var request_url = '/index/?c=ajax&act=index_actions&page=' + cur_page  + '&rnd=' + Math.random() + '&version=2&type=questions&uid=' + cur_uid + '&template=' + cur_list_template + '&filter=' + cur_filter;
			break;*/
		}
		
		$(this).addClass('loading');
		$(this).find('a').html('正在载入...');
		
		$.get(request_url, function (response)
		{
			if (response.length)
			{
				if (cur_page == 0)
				{
					$('#c_list').html(response);
				}
				else
				{
					$('#c_list').append(response);
				}
					
				cur_page++;
				
				$(_this).html(bp_more_inner_o); 
			}
			else
			{
				if (cur_page == 0)
				{
					$('#c_list').html('<p style="padding: 15px 0" align="center">没有内容</p>');
				}
					
				$(_this).addClass('disabled');
				
				$(_this).find('a').html('没有更多了');
			}
			
			$(_this).removeClass('loading');
			
		});
		
		return false;
	});
	
	var query_string = window.location.hash.replace(/#/g, '').split('&');
	
	for (i = 0; i < 3; i++)
	{
		if (!query_string[i])
		{
			query_string[i] = '';
		}
	}
	
	if ($('#i_tabs a[rel=all_' + query_string[1] + '_' + query_string[2] + ']').attr('href'))
	{
		$('#i_tabs a[rel=all_' + query_string[1] + '_' + query_string[2] + ']').click();
	}
	else
	{
		$('#bp_more').click();
	}
});

function _welcome_step_1_form_processer(result)
{
	if (result.errno != 1)
	{		
		alert(result.err);
	}
	else
	{
		$('#welcome_step_1_next_link').click();
	}
}

function welcome_step_1_load()
{
	new DialogBox_show('800', $('#welcome_step1').html(), '欢迎来到 ' + G_SITE_NAME, '', function() {
		var w = document.getElementById('w_tagPupD');
		var R = document.documentElement.scrollHeight || document.body.scrollHeight; 
		w.style.position = 'absolute';
		w.style.top = '120px';
		w.style.marginTop = '0px';
		document.getElementsByTagName("body")[0].style.position ='relative';
		
		$('#welcome_step1').remove();
			
		$(".select_area").LocationSelect({
        	labels: ["请选择省份或直辖市", "请选择城市", "请选择区"],
        
        	elements: document.getElementsByTagName("select"),
        
        	detector: function () {
           		this.selectID(["", "", ""]);
       		},	// 默认显示的城市
        
			dataUrl: G_STATIC_URL + "/js/areas_1.0.json"
		});
		
		init_avatar_uploader($('#welcome_avatar_uploader'), $('#welcome_avatar_uploading_status'), $("#welcome_avatar_src"));
	});
}

function welcome_step_4_load()
{
	new DialogBox_show('800', $('#welcome_step_2').html(), '欢迎来到 ' + G_SITE_NAME, '', function() {
		var w = document.getElementById('w_tagPupD');
		var R = document.documentElement.scrollHeight || document.body.scrollHeight; w.style.position = 'absolute';
		
		w.style.top = '120px';
		w.style.marginTop = '0px';
		document.getElementsByTagName("body")[0].style.position ='relative';
		
		$('#welcome_step_2').remove();
	
		$('#welcome_reload_topics_list').click(function () {
			$('#welcome_topics_list').html('<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/css/img/loading_b.gif" alt="" /></p>');
		
			$.get(G_BASE_URL + '/account/?c=ajax&act=welcome_get_topics&version=3', function (result) {
				$('#welcome_topics_list').html(result);
			});
		
			return false;
		});
		
		$('#welcome_reload_topics_list').click();
	});
}

function welcome_step_2_load()
{
	return welcome_step_3_load();
}

function welcome_step_3_load()
{
	new DialogBox_show('800', $('#welcome_step_4').html(), '欢迎来到 ' + G_SITE_NAME, '', function() {
		var w = document.getElementById('w_tagPupD');
		var R = document.documentElement.scrollHeight || document.body.scrollHeight; w.style.position = 'absolute';
		
		w.style.top = '120px';
		w.style.marginTop = '0px';
		document.getElementsByTagName("body")[0].style.position ='relative';
		
		$('#welcome_step_4').remove();
	
		$('#welcome_reload_questions_list').click(function () {
			$('#welcome_questions_list').html('<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/css/img/loading_b.gif" alt="" /></p>');
		
			$.get(G_BASE_URL + '/account/?c=ajax&act=welcome_get_questions&version=3', function (result) {
				$('#welcome_questions_list').html(result);
			});
		
			return false;
		});
		
		$('#welcome_reload_questions_list').click();
	});
}

function welcome_step_5_load()
{
	new DialogBox_show('800', $('#welcome_step_5').html(), '欢迎来到 ' + G_SITE_NAME, '', function() {
		var w = document.getElementById('w_tagPupD');
		var R = document.documentElement.scrollHeight || document.body.scrollHeight; w.style.position = 'absolute';
		
		w.style.top = '120px';
		w.style.marginTop = '0px';
		document.getElementsByTagName("body")[0].style.position ='relative';
		
		$('#welcome_step_5').remove();
	
		$('#welcome_reload_users_list').click(function () {
			$('#welcome_users_list').html('<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/css/img/loading_b.gif" alt="" /></p>');
		
			$.get(G_BASE_URL + '/account/?c=ajax&act=welcome_get_users&version=3', function (result) {
				$('#welcome_users_list').html(result);
			});
		
			return false;
		});
		
		$('#welcome_reload_users_list').click();
		$('#welcome_reload_users_list').hide();
	});
}

function welcome_step_finish()
{
	hidePupBox('w_tagPupD', 'w_mask');
	
	$.get(G_BASE_URL + '/account/?c=ajax&act=clean_first_login', function (result)
	{
		window.location = G_BASE_URL;
	});
}

function check_actions_new(uid, time)
{
	var url = G_BASE_URL+"/index/?c=ajax&act=check_actions_new&uid=" + uid + "&time=" + time + "&rnd=" + Math.random();

	$.getJSON(url, function (result) 
	{
		if(result.errno == 1)
		{
			if(result.rsm.new_count > 0)
			{
				if($("#new_actions_tip").is(":hidden"))
				{
					$("#new_actions_tip").fadeIn();
				}
				
				$("#new_action_num").html(result.rsm.new_count);
			}
		}
	});
	
}