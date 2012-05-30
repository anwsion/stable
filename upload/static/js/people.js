$(document).ready(function () {
	if (window.location.hash && $('#' + window.location.hash.replace('#', '')))
	{
		$('#' + window.location.hash.replace('#', '')).click();
	}
	
	$('#uLister a').click(function () {
		if ($(this).attr('id'))
		{
			window.location.hash = $(this).attr('id');
		}
		else
		{
			window.location.hash = '';
		}
	});
	
	 bp_more_load(G_BASE_URL+'/people/?c=ajax&act=user_actions&distint=0&uid=' + PEOPLE_USER_ID, $('#bp_more0'), $('#contents0'));	// 全部
	//bp_more_load('/people/?c=ajax&act=user_actions&uid=' + PEOPLE_USER_ID + '&get_type=competitions&actions=503', $('#bp_more_0'), $('#contents_0'));	// 参与的竞赛	
	//bp_more_load('/people/?c=ajax&act=user_actions&uid=' + PEOPLE_USER_ID + '&get_type=competitions&actions=501', $('#bp_more_1'), $('#contents_1'));	// 我发起的竞赛	
	 bp_more_load(G_BASE_URL+'/people/?c=ajax&act=user_actions&get_type=questions&uid=' + PEOPLE_USER_ID + '&actions=201', $('#bp_more_t0'), $('#contents_t0'));	// 参与的问题	
	 bp_more_load(G_BASE_URL+'/people/?c=ajax&act=user_actions&get_type=questions&uid=' + PEOPLE_USER_ID + '&actions=101', $('#bp_more_t1'), $('#contents_t1'));	// 发起的问题	
	//bp_more_load('/people/?c=ajax&act=user_contributes_list&uid=' + PEOPLE_USER_ID, $('#bp_more_contributes'), $('#contents_contributes'));	// 我的方案	
	bp_more_load('/people/?c=ajax&act=following_more&uid=' + PEOPLE_USER_ID, $('#bp_more_g0'), $('#contents_g0'));	// 我关注的人	
	bp_more_load('/people/?c=ajax&act=followers_more&uid=' + PEOPLE_USER_ID, $('#bp_more_g1'), $('#contents_g1'));	// 关注我的人	
	 bp_more_load('/people/?c=ajax&act=focustopics_more&uid=' + PEOPLE_USER_ID, $('#bp_more_tp1'), $('#contents_tp1'));//我关注的话题
	//bp_more_load('/people/?c=ajax&act=integral_log_more&uid=' + PEOPLE_USER_ID, $('#bp_more_i0'), $('#contents_i0'));//我关注的话题	
		
});

$('ul#i_tabs li a').click(function () {
	$('div.div_select').show();
	
	if ($(this).attr('display'))
	{
		$('div.div_select[id!=' + $(this).attr('display') + ']').hide();
	}
	else
	{
		
	}

		$('div.div_select[id="block_4"]').hide();
		$('div.div_select[id="block_6"]').hide();
		$('div.div_select[id="block_5"]').hide();
	
	$('ul#i_tabs li').removeClass('cur');
	$(this).parent().addClass('cur');
});


$('#rbtn_more_friend').click(function () {
	$('div.div_select').show();
	$('div.div_select[id!="block_4"]').hide();
});

$('#rbtn_more_fans').click(function () {
	$('div.div_select').show();
	$('div.div_select[id!="block_5"]').hide();
});
$('#rbtn_more_topic_focus').click(function () {
	$('div.div_select').show();
	$('div.div_select[id!="block_6"]').hide();
});
