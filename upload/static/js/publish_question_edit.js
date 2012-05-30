$(document).ready(function () {
	init_fileuploader('file_uploader_question', G_BASE_URL+'/publish/?c=ajax&act=question_attach_upload&attach_access_key=' + ATTACH_ACCESS_KEY);
	
	if ($("#file_uploader_question ._ajax_upload-list"))
	{
		$.post(G_BASE_URL+'/question/?c=ajax&act=question_attach_edit_list', "question_id=" + question_id, function (data) {
			if (data['err'])
			{
				alert(data['err']);
				
				return false;
			}
			else
			{
				$.each(data['rsm']['attachs'], function(i, v)
				{
					_ajax_uploader_append_file("#file_uploader_question ._ajax_upload-list", v);
				});
			}
		}, 'json');
	}
});	