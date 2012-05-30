<?php
/*
+--------------------------------------------------------------------------
|   Anwsion [#RELEASE_VERSION#]
|   ========================================
|   by Tatfook Network Team
|   (c) 2011 - 2012 Anwsion Software
|   http://www.anwsion.com
|   ========================================
|   Support: zhengqiang@gmail.com
|   
+---------------------------------------------------------------------------
*/

class c_ajax_class extends GZ_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action["rule_type"] = 'white'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["guest"] = array();
		$rule_action["user"] = array();
		$rule_action["actions"] = array();
		
		return $rule_action;
	}

	public function fetch_question_category_action()
	{
		echo $this->model('system')->build_category_json('question', 0, $question_info['category_id']);
		exit;
	}

	public function question_attach_upload_action()
	{
		define('IROOT_PATH', get_setting('upload_dir').'/questions/');
		
		define('ALLOW_FILE_FIXS', '*');
		
		$this->model('upload')->set_upload_dir('');
		
		$date = date('Ymd');
		
		if (isset($_GET['qqfile']))
		{
			$imagepath = $this->model('upload')->xhr_upload_file(file_get_contents('php://input'), $_GET['qqfile'], $date);
			
			$file_name = $_GET['qqfile'];
		}
		else if (isset($_FILES['qqfile']))
		{
			$imagepath = $this->model('upload')->upload_file($_FILES['qqfile'], $date);
			
			$file_name = $_FILES['qqfile']['name'];
		}
		else
		{
			return false;
		}
		
		if (! $imagepath)
		{
			return false;
		}
		
		$fileinfo = pathinfo($imagepath);
		
		$file_type = $this->model('upload')->get_filetype($imagepath);
		$file_size = $this->model('upload')->get_filesize();
		$sfile_type = ltrim($file_type, ".");
		$img_type_arr = explode(",", 'jpg,png,gif,jpeg');
		
		//如果是图片，则生成缩略图
		if (in_array($sfile_type, $img_type_arr))
		{			
			foreach(GZ_APP::config()->get('image')->attachment_thumbnail AS $key => $val)
			{
				$thumb_file[$key] = $this->model('image')->make_thumb(IROOT_PATH . $imagepath, $val['w'], $val['h'], IROOT_PATH . $fileinfo['dirname'] . '/', $val['w'].'x'.$val['h'].'_' . $fileinfo['basename'], true);
					
			}
			
			$min_thumb = $thumb_file['square'];
			
			if ($min_thumb)
			{
				$thumb = get_setting('upload_url') . '/questions/'.$date.'/' . $min_thumb;
			}
		}
		
		$attach_id = $this->model('publish')->add_question_attach($file_name, $_GET['attach_access_key'], time(), $fileinfo['basename'], $thumb);
		
		$output = array(
			'success' => true
		);
		
		if ($thumb)
		{
			$output['thumb'] = $thumb;
		}
		else
		{
			$output['class_name'] = $this->model('publish')->get_file_class($sfile_type);
		}
		
		$output['delete_url'] = get_setting('base_url') . '/publish/?c=ajax&act=remove_question_attach&attach_id=' . H::encode_hash(array(
			'attach_id' => $attach_id, 
			'access_key' => $_GET['attach_access_key']
		));
		
		echo htmlspecialchars(json_encode($output), ENT_NOQUOTES);
	}

	public function remove_question_attach_action()
	{
		$attach_info = H::decode_hash($_GET['attach_id']);
		
		if ($this->model('publish')->remove_question_attach($attach_info['attach_id'], $attach_info['access_key']))
		{
			H::ajax_json_output(GZ_APP::RSM(null, 1, '附件删除成功'));
		}
		
		H::ajax_json_output(GZ_APP::RSM(null, '-1', '附件删除失败'));
	}

	public function remove_competition_attach_action()
	{
		$attach_info = H::decode_hash($_GET['attach_id']);
		
		if ($this->model('publish')->remove_competition_attach($attach_info['attach_id'], $attach_info['access_key']))
		{
			H::ajax_json_output(GZ_APP::RSM(null, 1, '附件删除成功'));
		}
		
		H::ajax_json_output(GZ_APP::RSM(null, '-1', '附件删除失败'));
	}

	/*public function competitions_modify_action()
	{
		$competitions_id = intval($this->_INPUT['competitions_id']);
		
		$competition_obj = $this->model('competitions');
		
		$competition_info = $competition_obj->get_competitions_by_id($competitions_id);
		
		if (empty($competition_info))
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '系统错误!'));
		}
		
		if ($this->user_info['group_id'] != 1)
		{
			if ($competition_info['published_uid'] != $this->user_id)
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '权限错误!'));
			}
			
			if ((time() - $competition_info['add_time']) > intval(get_setting('competition_edit_time')) * 60)
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '对不起，发布竞赛超过 ' . get_setting('competition_edit_time') . ' 分钟以后无法修改!'));
			}
		}
		
		//$expire_time = strtotime($expire_time);
		

		if (trim($_POST['competitions_content']) == '')
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '请输入比赛标题'));
		}
		
		if (trim($_POST['specific']) == '')
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '请填写具体需求'));
		}
		
		if (trim($_POST['uninterested']) == '')
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '请填写不感兴趣'));
		}
		
		$retval = $competition_obj->update_competitions($competitions_id, $_POST['competitions_content'], $_POST['specific'], $_POST['uninterested'], $competition_info['bonus'], date('Y-m-d', $competition_info['expire_time']), $_POST['contribute_pri'], $_POST['attach_access_key']);
		
		if ($retval)
		{
			$competition_obj = $this->model('competitions');
			
			$competition_obj->update_competition_state($competitions_id, ACTION_LOG::MOD_COMPETITIONS);
			
			$url = "/competitions/?c=competitions&act=show&mid=" . $competitions_id;
			
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => $url
			), "1", "修改成功"));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => $url
			), "1", "修改失败，请联系管理员。"));
		}
	}*/

	function question_modify_action()
	{
		$question_id = intval($this->_INPUT['question_id']);
		
		$question_info = $this->model('question')->get_question_info_by_id($question_id);
		
		if (empty($question_info))
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '系统错误!'));
		}
		
		if ($this->user_info['group_id'] != 1)
		{
			if ($question_info['published_uid'] != $this->user_id)
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '权限错误!'));
			}
			
			if ((time() - $question_info['add_time']) > intval(get_setting('question_edit_time')) * 60)
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '对不起，发布问题超过 ' . get_setting('question_edit_time') . ' 分钟以后无法修改!'));
			}
		}
		
		$question_content = $this->_INPUT['question_content'];
		
		$question_detail = $this->_INPUT['question_detail'];
		
		if (!$this->_INPUT['category_id'])
		{
			H::ajax_json_output(GZ_APP::RSM(null, - 1, '请选择分类'));
		}
		
		$this->model('question')->update_question($question_id, $question_content, $question_detail);
		
		$this->model('question')->update_question_category($question_id, intval($this->_INPUT['category_id']));
		
		ZCACHE::cleanGroup("question_detail_" . $question_id);
		
		if ($_POST['attach_access_key'])
		{
			$this->model('publish')->update_question_attach($question_id, $_POST['attach_access_key']);
		}
		
		$url = urlencode(get_setting('base_url') . "/question/?act=detail&question_id=" . $question_id);
		
		$this->model('associate_index')->update_update_time($question_id, 1);
		$this->model('associate_index')->update_update_time($question_id, 3);
		
		H::ajax_json_output(GZ_APP::RSM(array(
			'url' => $url
		), 1, "修改问题成功!"));
	
	}
}