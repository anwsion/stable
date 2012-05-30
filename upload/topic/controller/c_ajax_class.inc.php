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
	var $per_page = 10;

	public function get_access_rule()
	{
		$rule_action["rule_type"] = 'white'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["actions"] = array(
			'topic_json'
		);
		
		return $rule_action;
	}

	function setup()
	{
		HTTP::no_cache_header();
	}
		
	public function get_focus_users_action()
	{
		
		$focus_users = $this->model('topic')->get_focus_users_by_topic($_GET['topic_id']);
		
		if (is_array($focus_users))
		{
			foreach ($focus_users as $key => $val)
			{
				$focus_users[$key]['avatar_file'] = get_avatar_url($val['uid'], 'mid', $val['avatar_file']);
			}
		}
		
		echo json_encode($focus_users);
	}

	public function get_question_compeitions_action()
	{
		$limit = $_GET['page'] * 1 * $this->per_page;
		$num = $_GET["num"] * 1;
		
		if ($num > 0)
		{
			$limit = $limit . ", " . $num;
		}
		else
		{
			$limit = $limit . ", {$this->per_page}";
		}
		
		$questions_row_array = array(); // 问题ID内容集合,符合条件
		$questions_array = array(); // 我关注的话题的问题集合
		$cache_key = ZCACHE::format_key('topics_get_question_compeitions_by_topic_v2_' . $_GET['topic_id'] . $limit);
		$action_list = ZCACHE::get($cache_key);
		
		if ($action_list === false)
		{
			$list = $this->model('topic')->get_question_compeitions_by_topic_v2($_GET['topic_id'], $limit);
			
			foreach ($list as $key => $val)
			{
				if ($val["question_id"] > 0)
				{
					$questions_row_array[$val["question_id"]] = $val;
					$questions_array[] = $val["question_id"];
				}				
			
			}
			
			// 101 添加问题
			// 102 编辑问题标题
			// 103 编辑问题描述
			// 104 删除问题
			// 105 添加问题关注
			// 106 删除问题关注
			//
			// 201 回答问题
			// 202 编辑回答
			// 203 删除回答
			// 204 赞成回答
			// 205 反对回答
			//
			// 301 增加评论
			// 302 删除评论
			//
			// 401 创建话题
			// 402 编辑话题
			// 403 编辑话题描述
			// 404 编辑话题缩图
			// 405 删除话题
			//
			// 406 添加话题关注
			// 407 删除话题关注
			// 408 增加话题父类
			// 409 删除话题父类
			//
			// 501 添加比赛
			// 502 比赛报名
			// 503 提交作品
			// 504 编辑作品
			// 505 添加比赛关注
			// 506 删除比赛关注
			

			$action_question = '101,201';
			$action_competitions = '501,503';
			$questions_array[] = 0;
			
			if ($questions_array)
			{
				$where[] = "(associate_type='" . ACTION_LOG::CATEGORY_QUESTION . "' AND  associate_id in (" . implode($questions_array, ",") . ") AND  associate_action IN({$action_question}))";
			}
			
			// echo implode($where, ' OR ');die;
			// 限定动作
			if ($where)
			{
				$action_list = ACTION_LOG::get_actions_distint_by_where(implode($where, ' OR '), $limit);
			}
			
			foreach ($action_list as $key => $val)
			{
				$action_list[$key]["add_time"] = $val["add_time"];
				
				if (! isset($user_info_list[$val['uid']]))
				{
					$user_info_more_list[$val['uid']] = $this->model('account')->get_users_by_uid($val['uid'], true);
					$user_info_list[$val['uid']] = $user_info_more_list[$val['uid']]["user_name"];
				}
				
				$action_list[$key]["user_info"] = $user_info_more_list[$val['uid']];
				
				switch ($val["associate_type"])
				{
					case ACTION_LOG::CATEGORY_QUESTION :
						
						$index_focus_type = 1;
						$question_info = $this->model('question')->get_question_info_by_id($val["associate_id"]);
						
						
						//是否关注
						if ($this->model('question')->has_focus_question($question_info['question_id'],$this->user_id))
						{
							$question_info['has_focus'] = true;
						}
						else
						{
							$question_info['has_focus'] = false;
						}
							
						
						if (! $user_info_list[$val["uid"]])
						{
							$user_info_list[$val["uid"]] = $this->model('account')->get_users($val["uid"]);
						}
						
						$question_info['last_user_name'] = $user_info_list[$val["uid"]];
						$question_info['last_user_avatar_file'] = $user_info_more_list[$val["uid"]]["avatar_file"];
						$question_info['last_user_uid'] = $val["uid"];
						
						if (in_array($val["associate_action"], array(401, 402,403, 404,405,406,407,408,409)))
						{
							$topic_info = $this->model('topic')->get_topic($val["associate_attached"]);
						
						}
						
						if (in_array($val["associate_action"], array(101)))
						{
							$question_info['attachs'] = $this->model('question')->get_question_attach($question_info['question_id']); // 获取附件
						}
						
						$question_info['last_action_str'] = ACTION_LOG::format_action_str($val["associate_action"], $val['uid'], $user_info_list[$val['uid']], null, $topic_info, 1);
						
						if (in_array($val["associate_action"], array(201)))
						{
							$answer_list = $this->model('answer')->get_answer_info_by_id($val['associate_attached'], 0, false);
							$answer_list['attachs'] = $this->model("answer")->get_answer_attach($val['associate_attached']); //获取附件
						
							$answer_list["uname"] = $user_info_more_list[$val["uid"]]["user_name"];
							$answer_list["avatar_file"] = $user_info_more_list[$val["uid"]]["avatar_file"];
							$answer_list["signature"] = $user_info_more_list[$val["uid"]]["signature"];
					
						}
						else
						{
							$answer_list = null;
						}
						
						$user_arr = array();
						
						
						if (! empty($answer_list))
						{
							$question_info['answer_info'] = $answer_list;
						}
						
						
						if ($question_info['answer_info']['agree_count'] > 0)
						{
							$question_info['answer_info']['agree_users'] = $this->model("answer")->get_vote_user_by_answer_id($question_info['answer_info']['answer_id']);
						}
						
						$answer_vote = $this->model("answer")->get_answer_vote_status($question_info['answer_info']['answer_id'], $uid);
						$question_info['answer_info']['agree_status'] = intval($answer_vote['vote_value']);
						
						// 还原到单个数组ROW里面
						foreach ($question_info as $qkey => $qval)
						{
							if ($qkey == 'add_time')
								continue;
							$action_list[$key][$qkey] = $qval;
						}
						
						break;
					
				}
			
			}
			
			ZCACHE::set($cache_key, $action_list, null, get_setting('cache_level_high'));
		}
		
		TPL::assign('list', $action_list);

		TPL::output('topic/topic_list_all_ajax_v3');
	
	}


	public function topic_json_action()
	{
		
		$topic_id = $this->_INPUT['topic_id'];		
		$cache_key = 'json_topic_get_topic_' . $topic_id;		
		$data = ZCACHE::get($cache_key);
		
		if ($data === false)
		{
			$topic_info = $this->model('topic')->get_topic($topic_id);			
			$data['type'] = "topic";
			$data['topic_id'] = $topic_info['topic_id'];
			$data['topic_title'] = $topic_info['topic_title'];
			$data['topic_description'] = strip_tags(FORMAT::cut_str($topic_info['topic_description'], 80, "..."));
			$data['focus_count'] = $topic_info['focus_count'];
			$data['focus'] = $this->model('topic')->has_focus_topic($this->user_id, $topic_id) ? true : false;
			
			if (empty($topic_info['topic_pic']))
			{
				$data['topic_pic'] = G_STATIC_URL.'/common/topic-mid-img.jpg';
			}
			else
			{
				$data['topic_pic'] = get_setting('upload_url').'/topic/' . $this->model('topic')->get_pic_url($topic_info['topic_id'], "50", $topic_info['topic_pic']);
			}
			
			ZCACHE::set($cache_key, $data, null, get_setting('cache_level_high'));
		}
		
		H::ajax_json_output($data);
	
	}
	
	public function modfiy_topic_title_action()
	{
		$topic_id = intval($this->_INPUT['topic_id']);
		$topic_title = FORMAT::safe($this->_INPUT['content'], TRUE);
		
		$topic_info = $this->model('topic')->get_topic($topic_id);
		
		if ($topic_title == $topic_info['topic_title'])
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'type' => '2',
				'url' => '/topic/?topic_id=' . $topic_id
			), 1, '话题编辑成功'));
		}
		
		$topic_has_exist = $this->model('topic')->has_topic($topic_title);
		
		if ($topic_has_exist)
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'type' => 1
			), '-1', '话题已经存在'));
		}
		
		if ($this->model('topic')->has_lock_topic($topic_id))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'type' => '3'
			), '-1', '锁定话题不能编辑'));
		}
		
		if ($this->model('topic')->update_topic($topic_id, $topic_title))
		{
			//删除cache
			ZCACHE::cleanGroup(ZCACHE::format_key('topic_info_' . $topic_id));
			
			H::ajax_json_output(GZ_APP::RSM(array(
				'type' => '2',
				'url' => '/topic/?topic_id=' . $topic_id
			), 1, '话题编辑成功'));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'type' => '3'
			), '-1', '话题编辑失败'));
		}
	}
	
	public function modify_topic_desc_action()
	{
		$topic_id = intval($this->_INPUT['topic_id']);		
		$topic_description = FORMAT::safe($this->_INPUT['content'], TRUE);
		
		if ($topic_id == 0)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '话题编号错误'));
		}
		
		if (empty($topic_description))
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '话题描述为空'));
		}
		
		if ($this->model('topic')->has_lock_topic($topic_id))
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '锁定话题不能编辑'));
		}
		
		$topic_info = $this->model('topic')->get_topic($topic_id);
		
		if ($this->model('topic')->update_topic($topic_id, '', $topic_description, '', '', ''))
		{
			ZCACHE::cleanGroup(ZCACHE::format_key('topic_info_' . $topic_id));
			
			H::ajax_json_output(GZ_APP::RSM(array(
				'target_id' => $this->_INPUT['target_id']
			), 1, $topic_description));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '编辑话题描述失败,请稍后重试'));
		}
	}
	
	public function upload_topic_pic_action()
	{
		define('IROOT_PATH', get_setting('upload_dir').'/topic/');
		define('ALLOW_FILE_FIXS', '*');
		
		$date = date('Ymd');
		$topic_id = intval($this->_INPUT['topic_id']);
		
		if ($topic_id == 0)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '话题编号错误,请稍后重试!'));
		}
		
		if ($this->model('topic')->has_lock_topic($topic_id))
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '锁定话题不能修改'));
			exit();
		}
		
		$topic_info = $this->model('topic')->get_topic($topic_id);
		
		if ($_FILES['topic_pic']['name'])
		{
			$this->model('image')->data_dir = "";
			$this->model('image')->images_dir = "";
			$random_filename = $this->model('image')->random_filename(2);
			$file_name = $this->model('image')->upload_image($_FILES['topic_pic'], $date, $random_filename);
			
			//生成缩图
			if (!$file_name)
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', "图片格式错误"));
				exit();
			}
			$file_name = IROOT_PATH . $file_name;
			$ext = $this->model('image')->get_filetype($file_name);
			
			
			foreach( GZ_APP::config()->get('image')->topic_thumbnail as $key=>$val)
			{
				$thumb_file[$key]=$this->model('image')->make_thumb($file_name, $val['w'],  $val['h'], IROOT_PATH . $date . "/", $random_filename . "_".$val['w']."_".$val['h']. $ext, true);
						
			}
			
// 			$thumb_file_org = $this->model('image')->make_thumb_imagick($file_name, 350, 350, IROOT_PATH . $date . "/", $random_filename . "_350_350" . $ext, true);
// 			$thumb_file_max = $this->model('image')->make_thumb_imagick($file_name, 150, 150, IROOT_PATH . $date . "/", $random_filename . "_150_150" . $ext, true);
// 			$thumb_file_mid = $this->model('image')->make_thumb_imagick($file_name, 50, 50, IROOT_PATH . $date . "/", $random_filename . "_50_50" . $ext, true);
// 			$thumb_file_min = $this->model('image')->make_thumb_imagick($file_name, 32, 32, IROOT_PATH . $date . "/", $random_filename . "_32_32" . $ext, true);
		
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '没选择图片'));
			exit();
		}
		
		if ($thumb_file["mid"] === false)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '图片上传错误,请稍后再试!'));
		}
		else
		{
			
			
			//删除CACHE;
			if ($topic_info)
			{
				ZCACHE::cleanGroup(ZCACHE::format_key("topic_info_" . $topic_id));
			}
			
			$topic_pic = $date . '/'.$thumb_file["min"];
			
			$this->model('topic')->modify_pic_topic_by_id($topic_id, $topic_pic);
			
			H::ajax_json_output(GZ_APP::RSM(array(
				'preview' =>get_setting('upload_url').'/topic/'.$date . '/' . $thumb_file["max"]
			), '1', ''));
		}
	}
}