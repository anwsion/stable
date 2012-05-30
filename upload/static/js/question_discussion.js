var cur_page = 0;
var cur_sort_type = 'hot';
var bp_more_inner_o = '';
var cur_category_id = '';

function reloadSortList(el, sort_type, category_id)
{
	if (category_id)
	{
		cur_category_id = category_id;
	}
	
	if (sort_type)
	{
		cur_page = 0;
		cur_sort_type = sort_type;
		
		$('#sort_control a').removeClass();
	}
	
	if (el)
	{
		$(el).addClass('cur');
	}
	
	$('#c_list').html('<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/css/img/loading_b.gif" alt="" /></p>');
	
	$('#bp_more').html(bp_more_inner_o).click();
}

$(document).ready(function()
{
	bp_more_inner_o = $('#bp_more').html();
	
	$('#bp_more').click(function()
	{
		var _this = this;
		
		$(this).addClass('loading');
		$(this).find('a').html('正在载入...');
		
		$.get(G_BASE_URL + '/question/?c=ajax&act=discuss&sort_type=' + cur_sort_type + '&page=' + cur_page  + '&category=' + cur_category_id + '&version=2', function (response)
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
	
	//$('#bp_more').click();
	//reloadSortList();
	$('#sort_control a.cur').click();
});