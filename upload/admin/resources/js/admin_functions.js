function _form_process(result)
{
	if(result.errno == "-1")
	{
		$("#notf_cont").html(result.err);
		$("#notf_tip").css("opacity", "").addClass("error").show();
	}
	else if(result.errno == "1")
	{
		if(result.rsm)
		{
			if(result.rsm.url != null)
			{
				window.location.href = decodeURIComponent(result.rsm.url);
				return false;
			}
			else
			{
				window.location.reload();
			}
		}
		window.location.reload();
	}
}

function topic_remove(topic_id)
{
	if(!confirm("确定删除话题？"))
	{
		return false;
	}
	
	var url = '?c=topic&act=topic_remove_ajax&topic_id=' + topic_id;
	
	$.getJSON(url, function (response)
	{
		if(response)
		{
			if(response.err)
			{
				alert(response.err);
			}
			
			if(response.errno == 1)
			{
				if(response.rsm)
				{
					if(response.rsm.url != undefined)
					{
						window.location.href = response.rsm.url;
					}
					else
					{
						window.location.reload();
					}
				}
				window.location.reload();
			}
		}
		else
		{
			alert("系统错误");
		}
	},'json');
}


function topic_lock(topic_id, status)
{
	var url = '?c=topic&act=topic_lock&topic_id=' + topic_id + '&status=' + status;
	
	$.getJSON(url, function (response)
	{
		if(response)
		{
			if(response.err)
			{
				alert(response.err);
			}
			
			if(response.errno == 1)
			{
				if(response.rsm)
				{
					if(response.rsm.url != undefined)
					{
						window.location.href = response.rsm.url;
					}
					else
					{
						window.location.reload();
					}
				}
				window.location.reload();
			}
		}
		else
		{
			alert("系统错误");
		}
	},'json');
}

function apply_pass(apply_id)
{
	var url = '?c=user&act=review_apply_ajax&apply_id=' + apply_id;
	
	$.getJSON(url, function (response){
		if(response)
		{
			if(response.err)
			{
				alert(response.err);
			}
			
			if(response.errno == 1)
			{
				window.location.reload();
			}
		}
		else
		{
			alert("系统错误");
		}
	},'json');
}

function apply_ignore(apply_id)
{
	if(!confirm("确定忽略？\n[忽略的记录将不会在本页显示。]"))
	{
		return false;
	}
		
	var url = '?c=user&act=ignore_apply_ajax&apply_id=' + apply_id;
	
	$.getJSON(url, function (response){
		if(response)
		{
			if(response.err)
			{
				alert(response.err);
			}
			
			if(response.errno == 1)
			{
				window.location.reload();
			}
		}
		else
		{
			alert("系统错误");
		}
	},'json');
}
	
function edit_topic_parent(topic_id, parent_id, target_id)
{
	$.facebox('<iframe src="?c=topic&act=topic_select_list_v2_ajax&topic_id=' + topic_id + '&parent_id=' + parent_id + '&target_id=' + target_id + '" frameborder="0" id="edit_topic_parent_frame" height="100%" width="100%" scrolling="auto"></iframe>');
}

function question_remove(question_id)
{
	if(!confirm("确定删除问题与问题关联的回复等内容？"))
	{
		return false;
	}
		
	var url = '?c=question&act=question_remove&question_id=' + question_id;
	
	$.getJSON(url, function (response){
		if(response)
		{
			if(response.err)
			{
				alert(response.err);
			}
			
			if(response.errno == 1)
			{
				window.location.reload();
			}
		}
		else
		{
			alert("系统错误");
		}
	},'json');
}


function category_remove(category_id)
{
	if(!confirm("确定删除分类及其子分类？"))
	{
		return false;
	}
	
	var url = '?c=category&act=category_remove&category_id=' + category_id;
	
	$.getJSON(url, function (response){
		if(response)
		{
			if(response.err)
			{
				alert(response.err);
			}
			
			if(response.errno == 1)
			{
				window.location.reload();
			}
		}
		else
		{
			alert("系统错误");
		}
	},'json');
}

function change_send_email(el)
{
	if(el.val() == '2')
	{
		el.parent().parent().find("[groupid=6]").hide();
	}
	else if(el.val() == '1')
	{
		el.parent().parent().find("[groupid=6]").show();
	}
	return false;
}

function test_email_setting(fm, el)
{

	var fm_array = fm.formToArray();
	
	el.val("  正在发送...  ");
	
	$.post('?c=setting&act=test_email_setting', fm_array, function (result)
	{
		el.val("  发送测试邮件  ");
		alert(result.err);
	}, 'json');
}

function batch_action(b_form, ajax_url)
{
	var action = b_form.find("[name=batch_action]").val();

	if(action == '')
	{
		alert("请选择操作");
		return;
	}

	if($(":checkbox:checked").length == 0)
	{
		alert("请选择要操作的条目");
		return;
	}

	var array_data = b_form.formToArray();

	$.post(ajax_url, array_data, function (result)
	{
		if (result.errno == 1)
		{
			window.location.reload();
		}
		else
		{
			alert(result.err);
		}
	}, "json");
}