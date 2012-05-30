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
	//var $per_page = 10;

	public function get_access_rule()
	{
		$rule_action["rule_type"] = 'white'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		
		$rule_action['actions'] = array(
			'detail',
		);
		
		if (get_setting('guest_explorer') == 'Y')
		{
			$rule_action['actions'][] = 'index';
		}
		
		return $rule_action;
	}

	public function setup()
	{
		$this->crumb('问题', '/question/');
	}

	public function index_action()
	{
		
		
		//分类
		if (TPL::is_output( 'block/content_category.tpl.htm','question/discussion'))
		{
			$content_category = $this->model('module')->content_category();
			
			TPL::assign('content_category', $content_category);
		}
			
		//最新动态
		if (TPL::is_output('block/content_dynamic.tpl.htm','question/discussion' ))
		{
			//没有调用数据
		}
			
		//问题
		if (TPL::is_output( 'block/content_question.tpl.htm','question/discussion'))
		{
			if ($_GET['category'])
			{
				$topic_count = ZCACHE::get('topic_count_category_' . $_GET['category']);
				
				if ($topic_count === false)
				{
					$topic_count = $this->model("question")->get_topic_category_count($_GET['category']);
					
					ZCACHE::set('topic_count_category_' . $_GET['category'], $topic_count, false, get_setting('cache_level_high'));
				}
				
				TPL::assign('topic_count', $topic_count);
			}
		}
		
		//边栏分类
		if (TPL::is_output('block/sidebar_category.tpl.htm','question/discussion'))
		{
			$sidebar_category=$this->model('module')->sidebar_category();
			TPL::assign('sidebar_category', $sidebar_category);
				
		}
		
		//边栏热门问题
		if (TPL::is_output('block/sidebar_hot_questions.tpl.htm','question/discussion'))
		{
			$hot_questions=$this->model('module')->hot_questions();
			TPL::assign('sidebar_hot_questions', $hot_questions);
		}
		
		//边栏邀请
		if (TPL::is_output( 'block/sidebar_invite.tpl.htm','question/discussion'))
		{
			//没有调用数据
		}
		
		//边栏菜单
		if (TPL::is_output('block/sidebar_menu.tpl.htm','question/discussion' ))
		{
			TPL::assign('draft_count', $this->model('draft')->get_draft_count('answer', $this->user_id));
			TPL::assign('question_invite_count', $this->model('question')->get_invite_question_list($this->user_id, '', true));
		}	
		
		//边栏最新动态
		if (TPL::is_output('block/sidebar_new_dynamic.tpl.htm','question/discussion'))
		{
				
			$sidebar_new_dynamic=$this->model('module')->sidebar_new_dynamic();
			TPL::assign('sidebar_new_dynamic',$sidebar_new_dynamic);
		
		}
		
		//边栏可能感兴趣的人
		if (TPL::is_output('block/sidebar_recommend_users_topics.tpl.htm','question/discussion'))
		{
			$recommend_users_topics = $this->model('module')->recommend_users_topics($this->user_id);
			TPL::assign('sidebar_recommend_users_topics', $recommend_users_topics);
		}
		
		//边栏热门用户
		if (TPL::is_output('block/sidebar_hot_users.tpl.htm','question/discussion'))
		{
			
			$sidebar_hot_users = $this->model('module')->sidebar_hot_user2($this->user_id,$_GET['category']);
			
			TPL::assign('sidebar_hot_users', $sidebar_hot_users);
		}

		
		if ($_GET['category'] AND $category_info = $this->model('system')->get_category_info($_GET['category']))
		{			
			TPL::assign('category_info', $category_info);
			
			$this->crumb($category_info['title'], '/question/?category=' . $category_info['id']);
		}

		TPL::import_css('css/discussion.css');
		TPL::import_js('js/question_discussion.js');
		
		TPL::output("question/discussion");
	}

	/**
	 * 
	 * 根据问题ID，得到问题内容及回复
	 * 
	 * @return array
	 */
	public function detail_action()
	{
		$answer_ids = array();
		$question_lnk = array();
		$question_lnk_list = array();
		$question_id = intval($this->_INPUT['question_id']);
		
		if ($question_id == 0)
		{
			HTTP::redirect('/question/');
		}
		
		if ($_GET['page'] < 1)
		{
			$_GET['page'] = 1;
		}
		
		if (!$this->_INPUT['sort'] OR $this->_INPUT['sort'] != 'ASC')
		{
			$this->_INPUT['sort'] = 'DESC';
		}
		else
		{
			$this->_INPUT['sort'] = 'ASC';
		}
		
		if ($this->_INPUT['ntid'])
		{
			$this->model('notify')->read_notify($this->_INPUT['ntid']);
		}
		
		$question_visit_key = "question_visit_count_" . $question_id;
		
		//$question_info = ZCACHE::get("question_info_id_" . $question_id);
		
		//if ($question_info === false)
		//{
			$question_info = $this->model("question")->get_question_info_by_id($question_id);
			
			//ZCACHE::set("question_info_id_" . $question_id, $question_info, "question_detail_" . $question_id, get_setting('cache_level_high'));
			
			//ZCACHE::delete($question_visit_key);
		//}
		
		if (!$question_info)
		{
			HTTP::redirect(get_setting('base_url')."/index/");
		}
		
// 		$question_visit_count = ZCACHE::get($question_visit_key);
		
// 		if ($question_visit_count === false)
// 		{
			$question_visit_count = $question_info['view_count'];
// 		}
		
		$question_info['view_count'] = $question_visit_count;
		
		//增加浏览次数
		//ZCACHE::set($question_visit_key, ++ $question_visit_count, "question_detail_" . $question_id, get_setting('cache_level_high'));
		
		$this->model('question')->increament_question_visist_count($question_id);
		
		//判断是否发布人,可以删除
		$question_info['question_modify'] = ($question_info['published_uid'] == $this->user_id || $this->user_info['group_id'] == 1) ? true : false;
		
		$question_info['question_modify_avail'] = ($this->user_info['group_id'] == 1) ? true : (($question_info['published_uid'] == $this->user_id) ? (((time() - $question_info['add_time']) <= 30 * 60) ? true : false) : false);
		
		$question_info['question_detail'] = nl2br(strip_tags($question_info['question_detail']));
		$question_info['question_detail'] = FORMAT::parse_links($question_info['question_detail']);
		
		if (is_numeric($this->_INPUT['uid']))
		{
			$answer_list_where[] = 'an.uid = ' . intval($this->_INPUT['uid']);
			
			$answer_count_where = ' uid = ' . intval($this->_INPUT['uid']);
		}
		else if ($this->_INPUT['sort_key'] == 'add_time')
		{
			$answer_order_by = $this->_INPUT['sort_key'] . " " . $this->_INPUT['sort'];
		}
		else
		{
			$answer_order_by = "agree_count " . $this->_INPUT['sort'];
		}
		
		$answer_count = $this->model("answer")->get_answer_count_by_question_id($question_id , $answer_count_where );//
		
		if (isset($this->_INPUT['answer_id']) and ! $this->user_id)
		{			
// 			$answer_user_list_key = "answer_user_list_" . $question_id . "_" . (int)$_GET['answer_id'];
			
// 			$answer_list = ZCACHE::get($answer_user_list_key);
			
// 			if ($answer_list === false)
// 			{
				$answer_list = $this->model('answer')->get_answer_list_by_question_id($question_id, 1, 'AND an.answer_id = ' . (int)$_GET['answer_id']);
				
// 				ZCACHE::set($answer_user_list_key, $answer_list, "question_detail_" . $question_id, get_setting('cache_level_high'));
// 			}
			
		}
		else if (! $this->user_id)
		{
			$answer_list = $this->model('answer')->get_answer_list_by_question_id($question_id, 1, null, 'agree_count DESC');
		}
		else
		{
			//$limit = ((intval($_GET['page']) - 1) * $this->per_page) . ', ' . $this->per_page;
			
// 			$answer_user_list_key = ZCACHE::format_key("answer_user_list_" . $question_id . "_" . $limit . "_" . $this->_INPUT['uid'] . "_" . $this->_INPUT['sort_key'] . '_' . $this->_INPUT['sort']);
			
// 			$answer_list = ZCACHE::get($answer_user_list_key);
			
// 			if ($answer_list === false)
// 			{
				if ($answer_list_where)
				{
					$answer_list_where = ' AND ' . implode(' AND ', $answer_list_where);
				}
				
				$answer_list = $this->model('answer')->get_answer_list_by_question_id($question_id, $limit, $answer_list_where, $answer_order_by);
				
			//	ZCACHE::set($answer_user_list_key, $answer_list, "question_detail_" . $question_id, get_setting('cache_level_high'));
			//}
			
			/*$this->model('pagination')->initialize(array(
				'base_url' => '/question/?act=detail&question_id=' . $question_id . '&uid=' . $_GET['uid'], 
				'total_rows' => $answer_count, 
				'per_page' => $this->per_page
			));
			
			TPL::assign("pagination", $this->model('pagination')->create_links());*/
			
			TPL::assign('invite_users', $this->model('question')->get_invite_users($question_id, $this->user_id));
			
			TPL::assign("user_follow_check", $this->model("follow")->user_follow_check($this->user_id , $question_info['published_uid']));
		}
		
// 		$answer_list_key = ZCACHE::format_key("answer_list_q" . $question_id . "_u" . $this->user_id);
		
// 		$q_answer = ZCACHE::get($answer_list_key);
		
// 		if ($q_answer === FALSE)
// 		{
			foreach ($answer_list as $answer)
			{
				$answer_ids[] = $answer['answer_id'];
				
				$answer['attachs'] = $this->model("answer")->get_answer_attach($answer['answer_id']);
				
				$answer['user_rated_thanks'] = $this->model("answer")->user_rated('thanks', $answer['answer_id'], $this->user_id);
				$answer['user_rated_uninterested'] = $this->model("answer")->user_rated('uninterested', $answer['answer_id'], $this->user_id);
				
				if (isset($answer['answer_content']))
				{
					$answer_list_content = FORMAT::format_content($answer['answer_content']);
					
					$answer['answer_title'] = strip_tags($answer_list_content['content_title']);
					
					$answer['answer_content'] = $answer_list_content['content_content'];
					$answer['answer_content'] = nl2br(strip_tags($answer['answer_content']));
					$answer['answer_content'] = FORMAT::parse_links($answer['answer_content']);
				}
				
				if ($answer['agree_count'] > 0)
				{
					$answer['agree_users'] = $this->model("answer")->get_vote_user_by_answer_id($answer['answer_id']);
				}
				
				$answer_vote = $this->model("answer")->get_answer_vote_status($answer['answer_id'], $this->user_id);
				
				$answer['agree_status'] = intval($answer_vote['vote_value']);
				
				$q_answer[] = $answer;
			}
			
// 			ZCACHE::set($answer_list_key, $q_answer, "question_detail_" . $question_id, get_setting('cache_level_high'));
// 		}
		
		$question_info['attachs'] = $this->data_cache("question_attach_" . $question_info['question_id'], '$this->model("question")->get_question_attach(' . $question_info['question_id'] . ')', get_setting('cache_level_high'), "question_detail_" . $question_id); //获取附件
		

		$question_topics = $this->data_cache("question_topics_" . $question_id, '$this->model("question_topic")->get_question_topic_by_question_id(' . $question_id . ')', get_setting('cache_level_high'), "question_detail_" . $question_id);
		
		$question_focus = $this->data_cache("question_focus_q" . $question_id . "_u" . $this->user_id, '$this->model("question")->has_focus_question(' . $question_id . ', ' . $this->user_id . ')', get_setting('cache_level_high'), "question_detail_" . $question_id);
		
		$question_lnk_list = $this->data_cache("question_relike_list_q" . $question_id . "_u" . $this->user_id, '$this->model("question")->get_relike_question_list(' . $question_id . ')', get_setting('cache_level_high'), "question_detail_" . $question_id);
		
		$question_info['user_info'] = $this->data_cache("account_get_users_" . $question_info['published_uid'], '$this->model("account")->get_users(' . $question_info['published_uid'] . ',true)', get_setting('cache_level_high'), "question_detail_" . $question_id);
		
		TPL::assign("bind_weibo_qq", $this->data_cache("weibo_qq_" . $this->user_id, '$this->model("qq_weibo")->get_users_qq_by_uid(' . $this->user_id . ')', get_setting('cache_level_high'), "question_detail_" . $question_id));
		
		TPL::assign("bind_weibo_sina", $this->data_cache("weibo_sina_" . $this->user_id, '$this->model("sina_weibo")->get_users_sina_by_uid(' . $this->user_id . ')', get_setting('cache_level_high'), "question_detail_" . $question_id));
		
		TPL::assign("question_id", $question_id);
		TPL::assign("question_focus", $question_focus);
		TPL::assign("q_answer_count", $answer_count);
		TPL::assign("q_topic", $question_topics);
		TPL::assign("q_info", $question_info);
		TPL::assign("q_answer", $q_answer);
		TPL::assign("q_link", $question_lnk_list);
		
		$user_job = $this->data_cache("user_job_" . $question_info['user_info']['uid'], '$this->model("account")->get_user_jobs_by_uids(' . $question_info['user_info']['uid'] . ')', get_setting('cache_level_high'), "question_detail_" . $question_id);
		
		TPL::assign("user_job", $user_job[$question_info['user_info']['uid']]);
		
		$this->crumb($question_info['question_content'], '/question/?act=detail&question_id=' . $question_id);
		
		TPL::import_css(array(
			'css/discussion.css', 
			'js/fileuploader/fileuploader.css'
		));
		
		TPL::import_js(array(
			'js/fileuploader/fileuploader.js'
		));
		
		TPL::assign('attach_access_key', md5($this->user_id . time()));
		
		TPL::output("question/detail");
	}

	/**
	 * 
	 * 根据问题ID增加话题，并直接关联
	 * 
	 * @return boolean true|false
	 */
	public function save_topic_action()
	{
		$retval = false;
		$topic_title = $this->_INPUT['topic_title'];
		$question_id = intval($this->_INPUT['question_id']);
		$topic_lock = intval($this->_INPUT['topic_lock']);
		
		if ($question_id == 0)
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'type' => 'sn'
			), "-1", "问题编号不能为空!"));
			exit();
		}
		
		if (empty($topic_title))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'type' => 'content'
			), "-1", "话题内容不能为空!"));
		}
		
		if (lenmbstr_zhcn($topic_title) < 2)
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'type' => 'content'
			), "-1", "话题长度必须大于2个字!"));
		}
		
		if ($question_id > 0)
		{
			$topic_id = $this->model('topic')->save_topic($question_id, $topic_title, $this->user_id, 0, 0, '', '', $topic_lock, 1);
			$this->model('question')->update_question_state($question_id, $this->user_id, ACTION_LOG::ADD_TOPIC, $this->user_id);
			$this->model('associate_index')->update_update_time($question_id, 1);
			$this->model('associate_index')->update_update_time($question_id, 3);
		}
		
		if (! $topic_id)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "话题已锁定,不能添加话题!"));
			exit();
		}
		
		$retval = $this->model('question_topic')->save_link($topic_id, $question_id);
		
		if ($retval)
		{
			//更新话题统计
			$this->model('topic')->update_topic_count($topic_id);
			
			ZCACHE::delete("question_topics_" . $question_id);
			
			H::ajax_json_output(GZ_APP::RSM(array(
				'topic_id' => $topic_id
			), 1, "添加话题成功!"));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "添加话题失败，请稍后重试!"));
			exit();
		}
	}

	/**
	 * 
	 * 根据话题ID和问题ID，删除关联
	 * 
	 * @return boolean true|false
	 */
	public function delte_topic_action()
	{
		$topic_id = intval($this->_INPUT['topic_id']);
		$question_id = intval($this->_INPUT['question_id']);
		
		if ($topic_id == 0 || $question_id == 0)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "问题编号不能为空!"));
		}
		
		if ($this->model('topic')->delete_topic($topic_id, $question_id, 0))
		{
			ZCACHE::delete("question_topics_" . $question_id);
			
			H::ajax_json_output(GZ_APP::RSM(array(
				'topic_id' => $topic_id
			), 1, "删除话题成功!"));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "删除话题失败，请稍后重试!"));
		}
	}

	/**
	 * 
	 * 根据问题ID,添加回复
	 * 
	 * @return boolean true|false
	 */
	public function save_answer_action()
	{
		$question_id = intval($this->_INPUT['question_id']);
		$answer_content = $this->_INPUT['answer_content'];
		
		if ($question_id == 0)
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'type' => 'sn'
			), "-1", "问题编号不能为空!"));
			exit();
		}
		
		if (! $answer_content)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "请输入回答内容"));
			exit();
		}
		
		preg_match_all("/@([\S\w]+):/i", $answer_content, $preg_arr);
		
		if (is_array($preg_arr[1]))
		{
			foreach ($preg_arr[1] as $key => $val)
			{
				$username = $val;
				
				$reply_user = $this->model('account')->get_users_by_username($username);
				
				if ($reply_user)
				{
					$people_url = $this->model('account')->get_url_by_uid($reply_user['uid']);
					$reply_user['people_url'] = $people_url;
					$reply_users[] = $reply_user;
					//$answer_content = preg_replace("/@{$username}:/i", "<a href=\"{$people_url}\">@{$username}</a>:", $answer_content);
				}
			}
		}

		if ($answer_id = $this->model('answer')->save_answer($question_id, $answer_content))
		{
			$this->model('question')->update_question_state($question_id, $this->user_id, ACTION_LOG::ANSWER_QUESTION);
			
			ZCACHE::cleanGroup("question_detail_" . $question_id);
			
			$this->model('draft')->delete_draft($question_id, 'answer', $this->user_id);
			
			if (! $this->model('question')->has_focus_question($question_id, $this->user_id))
			{
				$this->model('question')->add_focus_question($question_id);
				$this->model('question')->update_question_state($question_id, $this->user_id, ACTION_LOG::ADD_REQUESTION_FOCUS);
			}
			
			$notify_uids = array();
			
			if ($reply_users)
			{
				//通知：您关注的问题有新的回复
				$data = array(
					'question_id' => $question_id, 
					'item_id' => $answer_id, 
					'from_uid' => $this->user_id
				);
				
				foreach ($reply_users as $reply_user)
				{
					$this->model('notify')->send(notify_class::TYPE_COMMENT_BE_REPLY, $reply_user['uid'], $data, notify_class::CATEGORY_QUESTION, $question_id);
					$notify_uids[] = $reply_user['uid'];
				}
			}

			if ($focus_uids = $this->model('question')->get_focus_uid_by_question_id($question_id))
			{
				foreach ($focus_uids as $focus_user)
				{
					$this->model("email")->question($this->user_id, $focus_user['uid'], $question_id, email_class::NEW_ANSWER);
					
					if (in_array($focus_user['uid'], $notify_uids))
					{
						continue;
					}
					
					$data = array(
						'question_id' => $question_id,
						'from_uid' => $this->user_id,
						'item_id' => $answer_id,
					);
					
					$this->model('notify')->send(notify_class::TYPE_COMMENT_QUESTION, $focus_user['uid'], $data, notify_class::CATEGORY_QUESTION, $question_id);
				}
			}
			
			//删除回复邀请
			$this->model('question')->answer_question_invite($question_id, $this->user_id);
			
			if ($_POST['attach_access_key'])
			{
				$this->model('answer')->update_answer_attach($answer_id, $_POST['attach_access_key']);
			}
			
			H::ajax_json_output(GZ_APP::RSM(array(
				'question_id' => $question_id, 
				'action_type' => $answer_id
			), 1, "添加回复成功!"));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "添加回复失败，请稍后重试!"));
		}
	}
}