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
		$rule_action["rule_type"] = "white"; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action['actions'] = array(
			'get_answer_comments'
		);
		
		if (get_setting('guest_explorer') == 'Y')
		{
			$rule_action['actions'][] = 'discuss';
		}
		
		return $rule_action;
	}

	/**
	 * 
	 * 用户增加不感兴趣问题
	 * 
	 * @return boolean true|false
	 */
	public function uninterested_action()
	{
		$question_id = intval($this->_INPUT['question_id']);
		
		if ($question_id == 0)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "问题编号不能为空!"));
			exit();
		}
		
		$this->model('question')->add_question_uninterested($this->user_id, $question_id);
		
		H::ajax_json_output(GZ_APP::RSM(null, 1, '操作成功'));
	}

	public function get_focus_users_action()
	{
		echo json_encode($this->model('question')->get_focus_users_by_question($_GET['question_id']));
	}

	public function get_answer_users_action()
	{
		echo json_encode($this->model('answer')->get_answer_users_by_question($_GET['question_id']));
	}

	public function answer_attach_upload_action()
	{
		define('IROOT_PATH', get_setting('upload_dir') . '/answer/');
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
		$img_type_arr = explode(",", 'jpg,png,gif,jpeg,bmp');
		
		//如果是图片，则生成缩略图
		if (in_array($sfile_type, $img_type_arr))
		{
			foreach(GZ_APP::config()->get('image')->attachment_thumbnail AS $key => $val)
			{
				$thumb_file[$key] = $this->model('image')->make_thumb(IROOT_PATH . $imagepath, $val['w'], $val['h'], IROOT_PATH . $fileinfo['dirname'] . '/', $val['w'].'x'.$val['h'].'_' . $fileinfo['basename'], true);
			}		
			
			$min_thumb=$thumb_file['square'];
			
			if ($min_thumb)
			{
				$thumb = get_setting('upload_url') . '/answer/'.$date.'/' . $min_thumb;
			}
		
		}
		
		$attach_id = $this->model('answer')->add_answer_attach($file_name, $_GET['attach_access_key'], time(), $fileinfo['basename'], $thumb);
		
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
		
		$output['delete_url'] = '/question/?c=ajax&act=remove_answer_attach&attach_id=' . H::encode_hash(array(
			'attach_id' => $attach_id, 
			'access_key' => $_GET['attach_access_key']
		));
		
		echo htmlspecialchars(json_encode($output), ENT_NOQUOTES);
	}

	public function remove_answer_attach_action()
	{
		$attach_info = H::decode_hash($_GET['attach_id']);
		
		if ($this->model('answer')->remove_answer_attach($attach_info['attach_id'], $attach_info['access_key']))
		{
			H::ajax_json_output(GZ_APP::RSM(null, 1, '附件删除成功'));
		}
		
		H::ajax_json_output(GZ_APP::RSM(null, - 1, '附件删除失败'));
	}

	public function add_invite_action()
	{
		$question_id = intval($this->_INPUT['question_id']);
		$recipients_uid = intval($this->_INPUT['uid']);
		
		if($question_id == 0 || $recipients_uid == 0)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "系统错误"));
		}
		
		if($recipients_uid == $this->user_id)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-2", "不能邀请自己回答问题。"));
		}
		
		if ($this->model('question')->check_question_invite($question_id, $this->user_id, $recipients_uid))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-2", "已邀请过该用户"));
		}

		if ($this->model('question')->add_invite($question_id, $this->user_id, $recipients_uid))
		{
			$data = array(
				'from_uid' => $this->user_id,
				'question_id' => $question_id,
			);
			$this->model('notify')->send(notify_class::TYPE_INVITE_QUESTION, $recipients_uid, $data, notify_class::CATEGORY_QUESTION, $question_id);
			
			$this->model('email')->question($this->user_id, $recipients_uid, $question_id, email_class::INVIT_ME_ASK);
			
			H::ajax_json_output(GZ_APP::RSM(null, "1", "邀请成功"));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(l, "-1", "邀请失败"));
		}
	}

	public function delete_invite_action()
	{
		$question_id = $this->_INPUT['question_id'];
		$uid = $this->_INPUT['uid'];
		
		if ($this->model('question')->cancel_question_invite($question_id, $this->user_id, $uid))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "1", "删除成功!"));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(l, "-1", "删除失败!"));
		}
	}

	/**
	 * 修改回复内容
	 */
	public function edit_question_answer_action()
	{
		$answer_id = intval($this->_INPUT['answer_id']);
		$question_id = intval($this->_INPUT['question_id']);
		$answer_content = $this->_INPUT['content'];
		
		if (empty($answer_content))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => 'answer_content'
			), "-2", "请输入回复内容。"));
		}
		
		$answer_info = $this->model('answer')->get_answer_info_by_id($answer_id);
		
		if (empty($answer_info))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => 'answer_content'
			), "-2", "回答不存在"));
		}
		
		$this->model('answer')->update_answer($answer_id, $question_id, $answer_content, 0, $this->_INPUT['attach_access_key']);
		
		if ($answer_info['uid'] != $this->user_id)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-2", "权限错误。"));
		}
		
		$this->model('question')->update_question_state($question_id, $this->user_id, ACTION_LOG::MOD_ANSWER);
		
		ZCACHE::cleanGroup("question_detail_" . $answer_info['question_id']);
		
		$answer_content = FORMAT::parse_links($answer_content);
		
		$this->model('associate_index')->update_update_time($question_id, 1);
		$this->model('associate_index')->update_update_time($question_id, 3);
		
		H::ajax_json_output(GZ_APP::RSM(array(
			'target_id' => $this->_INPUT['target_id'], 
			'display_id' => $this->_INPUT['display_id']
		), "1", nl2br($answer_content)));
	}

	public function edit_question_content_action()
	{
		$question_id = intval($this->_INPUT['question_id']);
		
		$content = $this->_INPUT['content'];
		
		if (empty($content))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => $this->_INPUT['target_id']
			), "-2", "标题内容不能为空。"));
		}
		
		$question_info = $this->model("question")->get_question_info_by_id($question_id);
		
		if (empty($question_info))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-2", "系统错误。"));
		}
		
		if ($question_info['published_uid'] != $this->user_id)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-2", "权限错误。"));
		}
		
		$this->model("question")->update_question($question_id, $content);
		
		H::ajax_json_output(GZ_APP::RSM(array(
			'target_id' => $this->_INPUT['target_id'], 
			'display_id' => $this->_INPUT['display_id']
		), "1", nl2br($content)));
	
	}

	public function edit_question_detail_action()
	{
		$question_id = intval($this->_INPUT['question_id']);
		
		$content = $this->_INPUT['content'];
		
		if (empty($content))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => $this->_INPUT['target_id']
			), "-2", "问题描述内容不能为空。"));
		}
		
		$question_info = $this->model("question")->get_question_info_by_id($question_id);
		
		ZCACHE::cleanGroup("question_detail_" . $question_id);
		
		if ($question_info['published_uid'] != $this->user_id)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-2", "权限错误。"));
		}
		
		$this->model("question")->update_question($question_id, "", $content);
		
		H::ajax_json_output(GZ_APP::RSM(array(
			'target_id' => $this->_INPUT['target_id'], 
			'display_id' => $this->_INPUT['display_id']
		), "1", nl2br($content)));
	
	}

	public function question_attach_edit_list_action()
	{
		$question_id = $this->_INPUT['question_id'];
		
		$question_obj = $this->model('question');
		
		$question_info = $question_obj->get_question_info_by_id($question_id);
		
		if (empty($question_info))
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '无法获取附件列表'));
		}
		
		if (($question_info['published_uid'] != $this->user_id) && $this->user_info['group_id'] != 1)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '权限错误!'));
		}
		
		$question_attach = $question_obj->get_question_attach($question_id);
		
		foreach ($question_attach as $attach_id => $val)
		{
			$question_attach[$attach_id]['file_type_class'] = $this->model('publish')->get_file_class(ltrim($val['file_locatin'], '.'));
			$question_attach[$attach_id]['delete_link'] = '/publish/?c=ajax&act=remove_question_attach&attach_id=' . H::encode_hash(array(
				'attach_id' => $attach_id, 
				'access_key' => $val['access_key']
			));
		}
		
		H::ajax_json_output(GZ_APP::RSM(array(
			'attachs' => $question_attach
		), "1", ""));
	}

	public function answer_attach_edit_list_action()
	{
		$answer_id = intval($this->_INPUT['answer_id']);
		
		$answer_info = $this->model('answer')->get_answer_info_by_id($answer_id);
		
		if (empty($answer_info))
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '无法获取附件列表'));
		}
		
		if (($answer_info['uid'] != $this->user_id))
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '权限错误!'));
		}
		
		$answer_attach = $this->model('answer')->get_answer_attach($answer_id);
		
		foreach ($answer_attach as $attach_id => $val)
		{
			$answer_attach[$attach_id]['file_type_class'] = $this->model('publish')->get_file_class(ltrim($val['file_locatin'], '.'));
			$answer_attach[$attach_id]['delete_link'] = '/question/?c=ajax&act=remove_answer_attach&attach_id=' . H::encode_hash(array(
				'attach_id' => $attach_id, 
				'access_key' => $val['access_key']
			));
		}
		
		H::ajax_json_output(GZ_APP::RSM(array(
			'attachs' => $answer_attach
		), "1", ""));
	}

	public function edit_answer_action()
	{
		$answer_id = intval($this->_INPUT['answer_id']);
		
		$answer_info = $this->model('answer')->get_answer_info_by_id($answer_id);
		
		if (empty($answer_info))
		{
			H::js_pop_msg("方案不存在。");
		}
		
		if (($answer_info['uid'] != $this->user_id))
		{
			H::js_pop_msg("权限错误!");
		}
		
		TPL::assign('answer_info', $answer_info);
		TPL::assign('attach_access_key', md5($this->user_id . time()));
		
		TPL::import_css(array(
			'css/discussion.css', 
			'js/fileuploader/fileuploader.css'
		));
		
		TPL::import_js('js/fileuploader/fileuploader.js');
		
		TPL::output('question/question_answer_edit_v2');
	}

	function agree_answer_action()
	{
		$retval = $this->model('answer')->agree_answer($this->user_id, $_POST['answer_id']);
		
		$ans_info = $this->model('answer')->get_answer_info_by_id($_POST['answer_id'], 0, false);
		
		ZCACHE::cleanGroup('question_detail_' . $ans_info['question_id']);
		
		if ($retval)
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'action' => 'agree'
			)), "1", "赞同发送成功");
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'action' => 'disagree'
			)), "1", "赞同发送成功");
		}
	}

	public function question_share_txt_action()
	{
		$question_id = intval($this->_INPUT['question_id']);
		
		$q_info = $this->model('question')->get_question_info_by_id($question_id);
		
		$q_info['question_content'] = cutmbstr_zhcn($q_info['question_content'], 100);
		
		$url = get_setting('base_url') . "/question/?act=detail&question_id=" . $question_id . '&source=' . urlencode($this->model('account')->get_source_hash($this->user_info['email']));
		
		$data = array(
			'weibo' => "#" . get_setting('site_name') . "# " . $q_info['question_content'] . " " . $url . "&source=" . urlencode($this->model('account')->get_source_hash($this->user_info['email'])) . " (分享自@" . get_setting('site_name') . ")", 
			'mail' => $this->user_info['user_name'] . " 在" . get_setting('site_name') . "分享了一个问题给你： “" . $q_info['question_content'] . "” " . $url . "&source=" . urlencode($this->model('account')->get_source_hash($this->user_info['email'])), 
			'message' => "我看到一个不错的问题，想和你分享： “" . $q_info['question_content'] . "” " . $url
		);
		
		H::ajax_json_output(GZ_APP::RSM(array(
			'share_txt' => $data
		), "1"));
	}

	public function answer_share_txt_action()
	{
		$answer_id = intval($this->_INPUT['answer_id']);
		
		$a_info = $this->model('answer')->get_answer_info_by_id($answer_id, 0, false);
		
		$u_info = $this->model('account')->get_users_by_uid($a_info['uid']);
		
		$q_info = $this->model('question')->get_question_info_by_id($a_info['question_id']);
		
		$a_info['answer_content'] = cutmbstr_zhcn($a_info['answer_content'], 100);
		
		$url = get_setting('base_url') . "/question/?act=detail&question_id=" . $a_info['question_id'] . "&answer_id=" . $answer_id;
		
		$data = array(
			'weibo' => cutmbstr_zhcn("#" . get_setting('site_name') . "# " . $q_info['question_content'] . " " . '@' . $u_info['user_name'] . "： " . $a_info['answer_content'], 100) . ' ' .$url . "&source=" . urlencode($this->model('account')->get_source_hash($this->user_info['email'])) . " (分享自@" . get_setting('site_name') . ")",
			'mail' => $this->user_info['user_name'] . " 在" . get_setting('site_name') . "分享了一个问题给你： “" . $q_info['question_content'] . "” " . $u_info['user_name'] . "： " . cutmbstr_zhcn($a_info['answer_content'], 300) . ' ' .$url . "&source=" . urlencode($this->model('account')->get_source_hash($this->user_info['email'])),
			'message' => "我看到一个不错的问题，想和你分享： “" . $q_info['question_content'] . "” " . $u_info['user_name'] . "： " . cutmbstr_zhcn($a_info['answer_content'], 300) . ' ' .$url
		);
		
		H::ajax_json_output(GZ_APP::RSM(array(
			'share_txt' => $data
		), "1"));
	}

	function discuss_action()
	{		
		$num = intval($this->_INPUT['num']);
		$limit = $this->_INPUT["page"] * 1 * get_setting('contents_per_page');
		
		if ($num > 0)
		{
			$limit = $limit . ', ' . $num;
		}
		else
		{
			$limit = $limit . ', ' . get_setting('contents_per_page');
		}
		
		$cache_key = ZCACHE::format_key("index_get_hot_question_" . $limit . "_" . $this->_INPUT['sort_type'] . "_" . $this->_INPUT['topic_id']);
		$hot_list = ZCACHE::get($cache_key);
		
		if ($hot_list === false)
		{
			
			$hot_list = $this->model('index')->get_hot_question($limit, $this->_INPUT['sort_type'], $this->_INPUT['topic_id'], $this->user_id, $_GET['category'],$_GET["answer_count"]);
			
			ZCACHE::set($cache_key, $hot_list, null, get_setting('cache_level_high'));
		}
		
		TPL::assign('hot_list', $hot_list);
		
		if ($_GET['template'] == 'topic')
		{
			TPL::output("question/discussion_topic_ajax_v2");
		}
		else
		{
			TPL::output("question/discussion_more_ajax_v2");
		}
	}

	public function save_answer_comment_action()
	{
		if (! $_GET['answer_id'])
		{
			H::ajax_json_output(GZ_APP::RSM(null, - 1, "未知错误"));
		}
		
		if (trim($_POST['message']) == '')
		{
			H::ajax_json_output(GZ_APP::RSM(null, - 1, "请输入评论内容"));
		}
		
		$this->model('answer')->insert_answer_comment($_GET['answer_id'], $this->user_id, $_POST['message']);
		
		H::ajax_json_output(GZ_APP::RSM(array(
			'answer_id' => $_GET['answer_id']
		), 1, "评论成功"));
	}

	public function get_answer_comments_action()
	{
		$comments = $this->model('answer')->get_answer_comments($_GET['answer_id']);
		
		foreach ($comments as $key => $val)
		{
			$comments[$key]['user_info'] = $this->model('account')->get_users($val['uid']);
		}
		
		TPL::assign('comments', $comments);
		
		TPL::output("question/answer_comments_ajax_v2");
	}

	public function change_vote_action()
	{
		$answer_id = intval($this->_INPUT['answer_id']);
		$value = intval($this->_INPUT['value']);
		
		if (! in_array($value, array(
			- 1, 
			1
		)))
		{
			H::ajax_json_output(GZ_APP::RSM(null, - 1, "系统错误"));
		}
		
		$retval = $this->model('answer')->change_answer_vote($answer_id, $value, $this->user_id);
		
		H::ajax_json_output(GZ_APP::RSM(null, 1, "success"));
	}
	
	public function cancel_question_invite_action()
	{
		$question_id = intval($this->_INPUT['question_id']);
		$recipients_uid = intval($this->_INPUT['recipients_uid']);
		
		if($this->model('question')->cancel_question_invite($question_id, $this->user_id, $recipients_uid))
		{
			H::ajax_json_output(GZ_APP::RSM(null, 1, "删除成功"));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, 1, "删除失败"));
		}
	}
	
	public function question_invite_delete_action()
	{
		$question_invite_id = intval($this->_INPUT['question_invite_id']);
		
		if($this->model('question')->delete_question_invite($question_invite_id, $this->user_id))
		{
			H::ajax_json_output(GZ_APP::RSM(null, 1, "删除成功"));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, 1, "删除失败"));
		}
	}
	
	public function question_answer_rate_action()
	{
		if ($this->model('answer')->user_rate($_POST['type'], $_POST['answer_id'], $this->user_id, $this->user_info['user_name']))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'action' => 'add'
			), 1, '回答评价成功'));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'action' => 'remove'
			), 1, '撤消评价成功'));
		}
	}
	
	public function focus_action()
	{
		$question_id = intval($this->_INPUT['question_id']);
		
		if ($question_id == 0)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "问题不存在"));
		}
		
		$retval = $this->model('question')->add_focus_question($question_id);
		
		$this->model('question')->update_question_state($question_id, $this->user_id, ACTION_LOG::ADD_REQUESTION_FOCUS);
		
		if ($retval)
		{
			ZCACHE::delete("question_focus_q" . $question_id . "_u" . $this->user_id);
			
			ZCACHE::delete("question_get_user_recommend_v2_" . $this->user_id . "_10");
			
			H::ajax_json_output(GZ_APP::RSM(array(
				"type" => $retval
			), "1", "关注成功!"));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				"type" => $retval
			), "-1", "关注失败,请稍后重试!"));
		}
	}
}