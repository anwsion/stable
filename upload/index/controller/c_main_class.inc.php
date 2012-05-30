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
	const CATEGORY_INDEX_PEOPLE = 1; //我关注的人（他关注的人，他关注的话题）
	const CATEGORY_INDEX_TOPIC = 2; //我关注的话题（谁关注了相同的话题）
	const CATEGORY_INDEX_QUESTION = 3; //我回答过的问题（包含我没关注的话题）

	
	public function get_access_rule()
	{
		$rule_action["rule_type"] = "white"; //'black'黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["actions"] = array();
		
		if (get_setting('guest_explorer') == 'Y')
		{
			$rule_action['actions'][] = 'index';
		}
		
		return $rule_action;
	}

	/**
	 * 
	 * 列出首页
	 */
	public function index_action()
	{
		if (! $this->user_id)
		{
			HTTP::redirect('/question/?guest');
		}

		
		//分类
		if (TPL::is_output( 'block/content_category.tpl.htm','index/index_index_v2'))			
		{
			//等
		}
			
		//最新动态
		if (TPL::is_output('block/content_dynamic.tpl.htm','index/index_index_v2' ))
		{
			//没有调用数据
		}			
		
		//问题
		if (TPL::is_output( 'block/content_question.tpl.htm','index/index_index_v2'))
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
		if (TPL::is_output('block/sidebar_category.tpl.htm','index/index_index_v2'))
		{
			$sidebar_category=$this->model('module')->sidebar_category();
			TPL::assign('sidebar_category', $sidebar_category);
			
		}

		//边栏热门问题
		if (TPL::is_output('block/sidebar_hot_questions.tpl.htm','index/index_index_v2'))
		{
			$hot_questions=$this->model('module')->hot_questions();
			TPL::assign('sidebar_hot_questions', $hot_questions);
		}

		//边栏邀请
		if (TPL::is_output( 'block/sidebar_invite.tpl.htm','index/index_index_v2'))
		{
			//没有调用数据
		}

		//边栏菜单
		if (TPL::is_output('block/sidebar_menu.tpl.htm','index/index_index_v2' ))
		{
			TPL::assign('draft_count', $this->model('draft')->get_draft_count('answer', $this->user_id));
			TPL::assign('question_invite_count', $this->model('question')->get_invite_question_list($this->user_id, '', true));
		}

		//边栏最新动态
		if (TPL::is_output('block/sidebar_new_dynamic.tpl.htm','index/index_index_v2'))
		{
			
			$sidebar_new_dynamic=$this->model('module')->sidebar_new_dynamic();
			TPL::assign('sidebar_new_dynamic',$sidebar_new_dynamic);

		
		}

		//边栏可能感兴趣的人
		if (TPL::is_output('block/sidebar_recommend_users_topics.tpl.htm','index/index_index_v2'))
		{	
			$recommend_users_topics = $this->model('module')->recommend_users_topics($this->user_id);
			
			TPL::assign('sidebar_recommend_users_topics', $recommend_users_topics);
		}		
		
		//边栏热门用户
		if (TPL::is_output('block/sidebar_hot_users.tpl.htm','index/index_index_v2'))
		{
			$sidebar_hot_users = $this->model('module')->sidebar_hot_user($this->user_id);
			TPL::assign('sidebar_hot_users', $sidebar_hot_users);
		}

		

		$this->crumb('首页', '/index/');
		
		TPL::import_css('css/discussion.css');
		
		TPL::import_js(array(
			'js/LocationSelect.js',
			'js/ajaxupload.js',
			'js/index.js'
		));
		
		TPL::output("index/index_index_v2");
	}

	/**
	 *
	 * 不感兴趣的话题
	 */
	public function uninterested_topic()
	{
		
		$uid = $this->user_id;
		$topic_id = intval($this->_INPUT['topic_id']);
		
		if ($topic_id == 0)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "操作失败!"));
			exit();
		}
		
		$topic_obj = $this->model('topic');
		$topic_info = $topic_obj->get_topic($topic_id);
		
		if (empty($topic_info))
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "话题不存在!"));
			exit();
		}
		
		if ($topic_obj->save_topic_uninterested($uid, $topic_id))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "1", "操作成功!"));
			exit();
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "操作失败!"));
			exit();
		}
	}

	/**
	 *
	 * 不感兴趣的人
	 */
	public function uninterested_user()
	{
		
		$uid = $this->user_id;
		$user_id = intval($this->_INPUT['user_id']);
		
		if ($user_id == 0)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "操作失败!"));
			exit();
		}
		
		
		$account_obj = $this->model('account');		
		$user_info = $account_obj->get_users_by_uid($user_id);
		
		if (empty($user_info))
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "用户不存在!"));
			exit();
		}
		
		if ($account_obj->save_user_uninterested($uid, $user_id))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "1", "操作成功!"));
			exit();
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "操作失败!"));
			exit();
		}
	}
}