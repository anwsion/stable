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

class c_main_class extends GZ_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action["rule_type"] = "white"; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["guest"] = array();
		$rule_action["user"] = array();
		return $rule_action;
	}

	public function setup()
	{
		$this->per_page = get_setting('contents_per_page');
	}

	public function index_v2_action()
	{
		
		$topic_list = $this->data_cache('topic_get_topic_list_10', '$this->model("topic")->get_topic_list("",10)', 60);
		
		foreach ($topic_list as $k => $v)
		{
			$topic_list[$k]['topic_title'] = FORMAT::cut_str($v['topic_title'], 12, "...");
		}
		
		//CACHE 
		$hot_topic_list = $this->data_cache('topic_get_hot_topic_list_10', '$this->model("topic")->get_hot_topic_list("", 10)', 60);
		
		foreach ($hot_topic_list as $k => $v)
		{
			$hot_topic_list[$k]['topic_title'] = FORMAT::cut_str($v['topic_title'], 12, "...");
		}
		
		TPL::assign('page_title', "话题库");
		TPL::assign('hot_topic', $hot_topic_list);
		TPL::assign('new_topic', $topic_list);
		
		TPL::import_css(array(
				'css/discussion.css'
		));
		
		TPL::output('topic/index_v2');
	}

	public function index_action()
	{	
		if ($_GET['topic_id'])
		{
			$this->topic_action();
		}
		else if ($_GET['topic_title'])
		{
			$this->topic_action();
		}
		else
		{
			$this->index_v2_action();
		}
	}

	/**
	 * 
	 * 列出话题信息
	 */
	public function topic_action()
	{
		$uid = $this->user_id;
		$topic_id = intval($this->_INPUT['topic_id']);
		$topic_title = trim($this->_INPUT["topic_title"]);
		$data = array();
		$tmp = array();
		$quesion_id_list = array();
		$focus_question = array();
		$answer_id_list = array();		
		$account_obj = $this->model('account');
		$answer_obj = $this->model('answer');
		$question_obj = $this->model('question');
		$topic_obj = $this->model('topic');
		$index_obj = $this->model('index');
		
		if ($topic_title)
		{
			$topic_id = $this->model('topic')->get_topic_id_by_title($topic_title, get_setting('cache_level_high')); //60秒缓存
			
			if ($topic_id > 0)
			{
				$topic_info = $this->data_cache(ZCACHE::format_key('topic_get_topic_by_title_' . base64_encode($topic_title)), '$this->model("topic")->get_topic_by_title("' . $topic_title . '")', get_setting('cache_level_high'), ZCACHE::format_key("topic_info_" . $topic_id));
			}
		}
		else if ($topic_id)
		{
			$topic_info = $topic_obj->get_topic_by_title($topic_id);
			
			if (! $topic_info) //避免有时 topic title 是全数字
			{
				$topic_info = $topic_obj->get_topic($topic_id);
			}
		}
		
		if (empty($topic_info))
		{
			H::js_pop_msg("话题不存在！");
		}
		
		$no_answer_question_count = $question_obj->get_question_info($topic_info['topic_id'], "", 0, true,null, null,0);
			
		if (empty($topic_info['topic_pic']))
		{
			$topic_info['topic_pic_max'] = G_STATIC_URL.'/common/topic-max-img.jpg';
		}
		else
		{
			$topic_info['topic_pic_max'] = get_setting('upload_url').'/topic/' . str_replace('32_32', '150_150', $topic_info['topic_pic']);
		}
		
		$topic_info['has_focus'] = $topic_obj->has_focus_topic($this->user_id, $topic_info['topic_id']);
		$topic_info['topic_description'] = $topic_info['topic_description'];
		$topic_info['topic_experience'] = $topic_obj->get_topic_experience($uid, $topic_id);
		$topic_parent_list = $topic_obj->get_topic_parent_id($topic_id);
		
		$topic_info['topic_modify'] = $this->user_info['group_id'] == 1 ? true : false;
		
		if($topic_parent_list)
		{
			foreach ($topic_parent_list as $key=>$val)
			{
				$parent[]=$val["topic_id"];
			}
		}
		
		TPL::assign("topic_parent_list", $topic_parent_list);		
		TPL::assign("topic_info", $topic_info);
		TPL::assign("no_answer_question_count", $no_answer_question_count);
		
		
		TPL::assign("page_title", '话题: ' . $topic_info['topic_title']);
		//相关话题	
		//$topic_othter_child_list = $topic_obj->get_topic_childs($topic_info["parent_id"], "0,10");
		$topic_othter_child_list = $topic_obj->get_topics_child_id($parent," tp.topic_id<>'{$topic_id}' ");
		
		
		if (! is_array($topic_othter_child_list))
		{
			$topic_othter_child_list = array();
		}
		
		TPL::assign("topic_othter_child_list", $topic_othter_child_list);
		
		TPL::import_css(array(
			'css/discussion.css'
		));
		
		TPL::import_js(array(
			'js/ajaxupload.js'
		));
		
		TPL::output('topic/topic_v2');
	
	}

	/**
	 * 
	 * 话题分类
	 */
	public function organize_action()
	{
		$uid = $this->user_id;
		$topic_id = intval($this->_INPUT['topic_id']);
		$data = array();
		$tmp = array();
		$quesion_id_list = array();
		$focus_question = array();
		$answer_id_list = array();
		
		if ($topic_id == 0)
		{
			exit('参数传递错误!');
		}
		
		$user_obj = $this->model('account');
		$user_info = $user_obj->get_users_by_uid($uid);
		$topic_obj = $this->model('topic');
		$topic_info = $topic_obj->get_topic($topic_id);		
		$topic_info['topic_description'] = FORMAT::clear_body_html($topic_info['topic_description']);
		
		if (empty($topic_info['topic_pic']))
		{
			$topic_info['topic_pic_max'] = G_STATIC_URL.'/common/topic-max-img.jpg';
		}
		else
		{
			$topic_info['topic_pic_max'] = get_setting('upload_url').'/topic/' . str_replace('topic_min', 'topic_max', $topic_info['topic_pic']);
		}
		
		$topic_parent_list = $topic_obj->get_topic_parent_id($topic_id);
		$topic_child_list = $topic_obj->get_topic_child_id($topic_id);
		$topic_focus_info = $topic_obj->has_focus_topic($uid, $topic_id);
		
		if ($topic_focus_info)
		{
			$topic_info['topic_foucus'] = true;
		}
		else
		{
			$topic_info['topic_foucus'] = false;
		}
		
		//得到用户关注问题列表
		$topic_quest_obj = $this->model('question_topic');
		$quesion_list = $topic_quest_obj->get_question_topic_by_topic_id($topic_id, '10');
		
		foreach ($quesion_list as $quesion)
		{
			$quesion_id_list[] = $quesion['question_id'];
		}
		if (! empty($quesion_id_list))
		{
			$quesion_obj = $this->model('question');
			$focus_question = $quesion_obj->get_focus_question_by_uid($quesion_id_list, $uid);
		}
		//得到用户关注问题回复列表
		if (! empty($quesion_id_list))
		{
			$answer_obj = $this->model('answer');
			$answer_list = $answer_obj->get_answer_list_by_question_id($quesion_id_list, 1);
		}
		if (! empty($answer_list))
		{
			foreach ($answer_list as $answer)
			{
				$answer_id_list[$answer['question_id']] = $answer;
			}
		}
		//判断是否已经关注
		foreach ($quesion_list as $question)
		{
			$tmp = $question;
			if (in_array($question['question_id'], $focus_question))
			{
				$tmp['has_focus'] = true;
			}
			else
			{
				$tmp['has_focus'] = false;
			}
			
			if (isset($answer_id_list[$question['question_id']]))
			{
				$tmp['answer_count'] = $answer_id_list[$question['question_id']]['against_count'];
				$tmp['answer_content'] = $answer_id_list[$question['question_id']]['answer_content'];
			}
			else
			{
				$tmp['answer_count'] = '';
				$tmp['answer_content'] = '';
			}
			
			$data[] = $tmp;
		}
		//得到话题经验
		$topic_info['topic_experience'] = $topic_obj->get_topic_experience($uid, $topic_id);
		
		//得到树形话题结构
		$tree_html = $this->display_ul_action($topic_id);
		
		if ($topic_parent_list)
		{
			foreach ($topic_parent_list as $key => $val)
			{
				$parent[] = $val["topic_id"];
			}
		}
		
		$topic_othter_child_list = $topic_obj->get_topics_child_id($parent, " tp.topic_id<>'{$topic_id}' ");
		
		TPL::assign('topic_othter_child_list', $topic_othter_child_list);		
		TPL::assign('users', $user_info);
		TPL::assign('topic_info', $topic_info);
		TPL::assign('topic_parent_info', $topic_parent_list);
		TPL::assign('topic_child_info', $topic_child_list);
		TPL::assign('"quest_list', $data);
		TPL::assign('tree_html', $tree_html);
		TPL::output('topic/topic_organize', true);
	}

	/**
	 * 
	 * 话题增加话题子类
	 */
	public function save_topic_action()
	{		
		$retval = false;
		$topic_title = FORMAT::safe($this->_INPUT['topic_title'], true);
		$topic_lock = intval($this->_INPUT['topic_lock']);
		$topic_child_id = intval($this->_INPUT['topic_child_id']);
		
		if (empty($topic_title))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-2", "话题内容不能为空!"));
			exit();
		}
		
		$topic_obj = $this->model('topic'); //增加话题
		$topic_id = $topic_obj->save_topic(0, $topic_title, $this->user_id, 0, 0, '', '', $topic_lock, 2);
		
		if (! $topic_id)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-3", "话题已锁定,不能添加话题!"));
			exit();
		}
		else
		{
			//增加话题关联
			$retval = $topic_obj->add_lnk_2_topic($topic_id, $topic_child_id);
			if ($retval)
			{
				H::ajax_json_output(GZ_APP::RSM(array(
													'topic_id' => $topic_id
													), '1', '添加话题成功!'));
				exit();
			}
			else
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '添加话题失败!'));
				exit();
			}
		}
	}

	/**
	 * 
	 * 删除话题
	 */
	public function delete_topic_action()
	{
		
		$topic_id = intval($this->_INPUT['topic_id']);
		$topic_child_id = intval($this->_INPUT['topic_child_id']);
		
		if ($topic_id == 0)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '删除话题失败!'));
			exit();
		}
		$topic_obj = $this->model('topic');
		$topic_obj->delete_lnk_2_topic($topic_id, $topic_child_id);

		H::ajax_json_output(GZ_APP::RSM(null, '1', '删除话题成功!'));
		exit();
	}

	/**
	 * 
	 * 保存话题经验
	 */
	public function save_topic_experience_action()
	{
		
		$topic_id = intval($this->_INPUT['topic_id']);
		$uid = $this->user_id;
		$experience_content = FORMAT::safe(trim($this->_INPUT['experience_content']),true);
		
		if ($topic_id == 0 || $experience_content == "")
		{
			H::ajax_json_output(GZ_APP::RSM(array(
												'type' => $experience_content
												), '-1', "话题经验内容不能为空!"));
			exit();
		}
		
		$topic_obj = $this->model('topic');
		$retval = $topic_obj->save_topic_experience($topic_id, $uid, $experience_content);
		
		if ($retval)
		{
			H::ajax_json_output(GZ_APP::RSM(array(
												'type' => $retval
												), '1', '话题经验修改成功!'));
			exit();
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(array(
											'type' => $retval
											), '-1', '话题经验修改失败!'));
			exit();
		}
	}

	/**
	 * 
	 * 关注话题
	 * 
	 * @return boolean true|false
	 */
	public function focus_topic_action()
	{
		
		$topic_id = intval($this->_INPUT['topic_id']);
		
		if ($topic_id == 0)
		{
			return false;
		}
		
		$topic_obj = $this->model('topic');
		$retval = $topic_obj->add_focus_topic($this->user_id, $topic_id);
		
	//	ZCACHE::cleanGroup(ZCACHE::format_key("topic_info_" . $topic_id));
		
		if ($retval)
		{
			
			H::ajax_json_output(GZ_APP::RSM(array(
											'type' => $retval
										), '1', '关注话题描成功!'));
			exit();
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(array(
												'type' => $retval
											), '-1', '关注话题描述失败,请稍后重试!'));
			exit();
		}
	}

	/**
	 * 
	 * 显示UL树形
	 * @param int $end_topic_id
	 * 
	 * @return string
	 */
	public function display_ul_action($end_topic_id)
	{
		$end_topic_id = intval($end_topic_id);
		$html =	'';
		$topic_info_list = array();
		$child_id_info = array();
		$topic_obj = $this->model('topic');		
		$topic_lnk_id = $topic_obj->display_topic_tree($end_topic_id);		
		$topic_id_list = explode(',', '0' . $topic_lnk_id); //追加一个0
		$topic_id_list = array_unique($topic_id_list);		
		$child_list = $topic_obj->has_child_by_ids($topic_id_list); //得到所有存在子类的数组
		$topic_info_arra = $topic_obj->get_topic_title($topic_id_list); //得到所有ID信息类型
 		//格式话话题数据,用话题ID作为数组下标
		foreach ($topic_info_arra as $topic_info)
		{
			$topic_info_list[$topic_info['topic_id']] = $topic_info['topic_title'];
		}
		
		foreach ($child_list as $child_info)
		{
			if (! in_array($child_info['topic_child_id'], $child_id_info))
			{
				$child_id_info[] = $child_info['topic_child_id'];
			}
		}
		//得到父类分支
		$parent_ids = array_diff($topic_id_list, $child_id_info);
		
		if (empty($parent_ids))
		{
			return '';
		}
		
		foreach ($parent_ids as $topic_parent_id)
		{
			$topic_id_list = "";
			if ($topic_parent_id == 0)
			{
				continue;
			}
			if ($topic_parent_id == $end_topic_id)
			{
				$topic_id_list = "," . $end_topic_id; //组合上自己信息
			}
			else
			{
				$topic_id_list = $topic_obj->display_topic_ul_tree($topic_parent_id, $end_topic_id);
			}
			
			$html .= $this->display_ul_tree_html($topic_id_list, $topic_info_list);
			
		}
		
		return $html;
	}

	/**
	 * 
	 * 组合输出 ul html tree
	 * @param string $topic_id_list
	 * @param string $topic_info_list
	 * 
	 * @return string
	 */
	public function display_ul_tree_html($topic_id_list, $topic_info_list)
	{
		$return_str = '';
		$str_start = '<ul>';
		$str_end = '</ul>';
		$tree_info_list = explode(',', $topic_id_list);
		unset($tree_info_list[0]); //由于第一个数为0直接unset掉
		$count = count($tree_info_list);
		foreach ($tree_info_list as $key => $tree_id)
		{
			$str_start .= '<ul>';
			if ($count == $key)
			{
				$info_str = '<li><strong><a href="/topic/?topic_id=' . $tree_id . '&topic_title=' . $topic_info_list[$tree_id] . '"> ' . $topic_info_list[$tree_id] . ' </a> </strong></li>';
			}
			else
			{
				$info_str = '<li> <a href="/topic/?topic_id=' . $tree_id . '&topic_title=' . $topic_info_list[$tree_id] . '"> ' . $topic_info_list[$tree_id] . ' </a> </li>';
			}
			$str_end .= '</ul>';
			$return_str .= $str_start . $info_str . $str_end;
		}
		
		return $return_str;
	}

	/**
	 * 话题操作日志 
	 */
	public function log_action()
	{
		
	$uid = $this->user_id;
		$topic_id = intval($this->_INPUT['topic_id']);
		$topic_title = trim($this->_INPUT["topic_title"]);
		$data = array();
		$tmp = array();
		$quesion_id_list = array();
		$focus_question = array();
		$answer_id_list = array();		
		$account_obj = $this->model('account');
		$answer_obj = $this->model('answer');
		$question_obj = $this->model('question');
		$topic_obj = $this->model('topic');
		$index_obj = $this->model('index');
		
		if ($topic_title)
		{
			$topic_id = $this->model('topic')->get_topic_id_by_title($topic_title, get_setting('cache_level_high')); //60秒缓存
			
			if ($topic_id > 0)
			{
				$topic_info = $this->data_cache(ZCACHE::format_key('topic_get_topic_by_title_' . base64_encode($topic_title)), '$this->model("topic")->get_topic_by_title("' . $topic_title . '")', get_setting('cache_level_high'), ZCACHE::format_key("topic_info_" . $topic_id));
			}
		}
		else if ($topic_id)
		{
			$topic_info = $topic_obj->get_topic_by_title($topic_id);
			
			if (! $topic_info) //避免有时 topic title 是全数字
			{
				$topic_info = $topic_obj->get_topic($topic_id);
			}
		}
		
		if (empty($topic_info))
		{
			H::js_pop_msg("话题不存在！");
		}
		
		$no_answer_question_count = $question_obj->get_question_info($topic_info['topic_id'], "", 0, true,null, null,0);
			
		if (empty($topic_info['topic_pic']))
		{
			$topic_info['topic_pic_max'] = G_STATIC_URL.'/common/topic-max-img.jpg';
		}
		else
		{
			$topic_info['topic_pic_max'] = get_setting('upload_url').'/topic/' . str_replace('32_32', '150_150', $topic_info['topic_pic']);
		}
		
		$topic_info['has_focus'] = $topic_obj->has_focus_topic($this->user_id, $topic_info['topic_id']);
		$topic_info['topic_description'] = $topic_info['topic_description'];
		$topic_info['topic_experience'] = $topic_obj->get_topic_experience($uid, $topic_id);
		$topic_parent_list = $topic_obj->get_topic_parent_id($topic_id);
		
		$topic_info['topic_modify'] = $this->user_info['group_id'] == 1 ? true : false;
		
		if($topic_parent_list)
		{
			foreach ($topic_parent_list as $key=>$val)
			{
				$parent[]=$val["topic_id"];
			}
		}
		
		TPL::assign("topic_parent_list", $topic_parent_list);		
		TPL::assign("topic_info", $topic_info);
		TPL::assign("no_answer_question_count", $no_answer_question_count);
		
		
		TPL::assign("page_title", '话题: ' . $topic_info['topic_title']);
		//相关话题	
		//$topic_othter_child_list = $topic_obj->get_topic_childs($topic_info["parent_id"], "0,10");
		$topic_othter_child_list = $topic_obj->get_topics_child_id($parent," tp.topic_id<>'{$topic_id}' ");
		
		
		if (! is_array($topic_othter_child_list))
		{
			$topic_othter_child_list = array();
		}
		
		TPL::assign("topic_othter_child_list", $topic_othter_child_list);
		
		TPL::import_css(array(
			'css/discussion.css'
		));
		
		TPL::import_js(array(
			'js/ajaxupload.js'
		));
		
		TPL::output('topic/topic_log_v2');
	}

	/**
	 * 话题操作日志 
	 */
	public function log_more_ajax_action()
	{
		
		$uid = $this->user_id;
		$topic_id = intval($this->_INPUT['topic_id']);
		$data = array();
		$tmp = array();
		$quesion_id_list = array();
		$focus_question = array();
		$answer_id_list = array();
		
		if ($topic_id == 0)
		{
			exit('参数传递错误!');
		}
		
		$limit = $this->_INPUT["page"] * 1 * $this->per_page;
		$limit = $limit . ",{$this->per_page}";		
		$user_obj = $this->model('account');
		$user_info = $user_obj->get_users_by_uid($uid);
		$topic_obj = $this->model('topic');
		$topic_info = $topic_obj->get_topic($topic_id);
		
		if (empty($topic_info['topic_pic']))
		{
			$topic_info['topic_pic'] = G_STATIC_URL.'/common/avatar-mid-img.jpg';
		}
		
		$topic_parent_list = $topic_obj->get_topic_parent_id($topic_id);
		$topic_focus_info = $topic_obj->has_focus_topic($uid, $topic_id);
		if ($topic_focus_info)
		{
			$topic_info['topic_foucus'] = true;
		}
		else
		{
			$topic_info['topic_foucus'] = false;
		}
		
		//401 创建话题
		//402 修改话题
		//403 修改话题描述
		//404 修改话题缩图
		//405 删除话题
		//
		//406 添加话题关注
		//407 删除话题关注
		//408 增加话题父类
		//409 删除话题父类		
		

		$action_ids = " 401,402,403,404,405,408,409 ";
		$log_list = ACTION_LOG::get_action_by_event_id(false, $topic_id, $limit, ACTION_LOG::CATEGORY_TOPIC, $action_ids);
		
		//处理日志记录
		$log_list = $this->analysis_log($log_list);
		

		
		TPL::assign('user_info', $user_info);
		TPL::assign('topic_info', $topic_info);
		TPL::assign('topic_parent_info', $topic_parent_list);
		TPL::assign('log_list', $log_list);
		
		
		 TPL::output('topic/topic_log_more_ajax_v2');
		
	}

	/**
	 * 处理话题日志
	 * @param array $log_list
	 * 
	 * @return array
	 */
	public function analysis_log($log_list)
	{
		$data_list = array();
		$uid_list = array(0);
		$topic_list = array(0);
		
		if (empty($log_list))
		{
			return;
		}
		/**
		 * 找到唯一数据值
		 */
		foreach ($log_list as $key => $log)
		{
			
			if (! in_array($log['uid'], $uid_list))
			{
				$uid_list[] = $log['uid'];
			}
			if (! empty($log['associate_attached']) && is_numeric($log['associate_attached']) && ! in_array($log['associate_attached'], $topic_list))
			{
				$topic_list[] = $log['associate_attached'];
			}
			if (! empty($log['associate_content']) && is_numeric($log['associate_content']) && ! in_array($log['associate_content'], $topic_list))
			{
				$topic_list[] = $log['associate_content'];
			}
		}
		
		/**
		 * 格式话简单数据类型
		 */
		
		$topic_obj = $this->model('topic');
		$topic_title_array = $topic_obj->get_topic_title($topic_list);
		
		foreach ($topic_title_array as $topic_title_info)
		{
			$topic_title_list[$topic_title_info['topic_id']] = $topic_title_info['topic_title'];
		}
		//格式化用户名
		$user_obj = $this->model('account');
		$user_name_array = $user_obj->get_users_by_uids($uid_list);
		
	
		
		foreach ($user_name_array as $user_info)
		{
			
			
			$user_url = $user_obj->get_url_by_uid($user_info['uid']);
			
			
			$user_info_list[$user_info['uid']] = array(
													$user_info['user_name'], 
													$user_url
													 );
		}
		
		mb_internal_encoding("UTF-8");
		
		/**
		 * 格式话数组
		 */
		
		foreach ($log_list as $key => $log)
		{
			$title_list = "";
			$user_name = $user_info_list[$log['uid']][0];
			$user_url = $user_info_list[$log['uid']][1];
			
			switch ($log['associate_action'])
			{
				case ACTION_LOG::ADD_TOPIC : //增加话题
					$title_list = '<a href="' . $user_url . '">' . $user_name . '</a>创建了该话题</p><p><a href="/topic/?topic_id=' . $log['associate_id'] . '&topic_title=' . $log['associate_content'] . '"></a>被创建';
					break;
				case ACTION_LOG::ADD_TOPIC_FOCUS : //关注话题
					

					break;
				case ACTION_LOG::ADD_TOPIC_PARENT : //增加话题父类
					$title_list = '<a href="' . $user_url . '">' . $user_name . '</a>给<a href="/topic/?topic_id=' . $log['associate_id'] . '&topic_title=' . $topic_title_list[$log['associate_id']] . '">' . $topic_title_list[$log['associate_id']] . '</a>添加了父话题';
					$title_list .= '&nbsp;<a href="/topic/?topic_id=' . $log['associate_id'] . '&topic_title=' . $topic_title_list[$log['associate_content']] . '">' . $topic_title_list[$log['associate_content']] . '</a>';
					break;
				case ACTION_LOG::DELETE_TOPIC : //删除话题
					

					break;
				case ACTION_LOG::DELETE_TOPIC_FOCUS : //删除话题关注
					

					break;
				case ACTION_LOG::DELETE_TOPIC_PARENT : //删除话题父类
					$title_list = '<a href="' . $user_url . '">' . $user_name . '</a>修改<a href="/topic/?topic_id=' . $log['associate_id'] . '&topic_title=' . $log['associate_content'] . '"></a>删除了父话题';
					break;
				case ACTION_LOG::MOD_TOPIC : //修改话题标题
					

					$Diff = new FineDiff($log['associate_attached'], $log['associate_content']);
					
					$rendered_diff = $Diff->renderDiffToHTML();
					
					$title_list = '<a href="' . $user_url . '">' . $user_name . '</a>修改了话题<a href="/topic/?topic_id=' . $log['associate_id'] . '&topic_title=' . $log['associate_content'] . '"></a>标题"<p>' . $rendered_diff . "</p>";
					break;
				case ACTION_LOG::MOD_TOPIC_DESCRI : //修改话题描述

					$Diff = new FineDiff($log['associate_attached'], $log['associate_content']);
					
					$rendered_diff = $Diff->renderDiffToHTML();
					
					$title_list = '<a href="' . $user_url . '">' . $user_name . '</a>修改了话题<!--<a href="/topic/?topic_id=' . $log['associate_id'] . '&topic_title=' . $log['associate_content'] . '"></a>-->描述
								<p>' . 

					$rendered_diff . '</p>';
					
					break;
				case ACTION_LOG::MOD_TOPIC_PIC : //修改话题图片
					$title_list = '<a href="' . $user_url . '">' . $user_name . '</a>修改了该话题<a href="/topic/?topic_id=' . $log['associate_id'] . '&topic_title=' . $log['associate_content'] . '"></a>图片';
					break;
			}
			
			(! empty($title_list)) ? $data_list[] = array(
				'title' => $title_list, 
				'add_time' => date('Y-m-d', $log['add_time']), 
				'log_id' => sprintf('%06s', $log['history_id'])
			) : '';
		}
		
		return $data_list;
	}

}