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
	public $per_page = 20; //全部通知列表，每页面显示个数

	public function get_access_rule()
	{
		$rule_action["rule_type"] = "white"; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["actions"] = array();
		return $rule_action;
	}

	/**
	 * 
	 * 列出用户短信息
	 */
	public function index_action()
	{
		
		//边栏可能感兴趣的人
		if(TPL::is_output('block/sidebar_recommend_users_topics.tpl.htm','notifications/notify_index'))
		{
			$recommend_users_topics = $this->model('module')->recommend_users_topics($this->user_id);
			TPL::assign('sidebar_recommend_users_topics', $recommend_users_topics);
		}
		
		//边栏全局菜单
		if (TPL::is_output('global/__account_slidebar_v2.tpl.htm','notifications/notify_index' ))
		{
			TPL::assign('draft_count', $this->model('draft')->get_draft_count('answer', $this->user_id));
			TPL::assign('question_invite_count', $this->model('question')->get_invite_question_list($this->user_id, '', true));
		}
		
		
	
		
		TPL::assign('recommend_questions', $this->model('question')->get_user_recommend_v2($this->user_id, 10));
		
		$this->crumb('通知', '/notifications');
		
		TPL::import_css('css/discussion.css');
		
		TPL::output('notifications/notify_index');
	}

	/**
	 * 
	 * 列出用户短信息
	 */
	public function index_more_ajax_action()
	{
		if(get_setting('notifications_per_page'))
		{
			$this->per_page = get_setting('notifications_per_page');
		}
		
		$page = intval($this->_INPUT['page']);
		$offset = $page * $this->per_page;
		$limit = $offset . ", {$this->per_page}";
		
		$data = array();
		$tmp = array();
		$uids = array();
		$users = array();
		
		$type = ($this->_INPUT['type'] == 'all') ? true : false;
		
		$is_combine = $type ? false : true;
		
		if($this->_INPUT['list_all'])
		{
			$limit = '0, 100';
		}
		
		$notification_list_key = ZCACHE::format_key('notification_list_' . ($type ? "1" : "0") . '_' . $this->user_id . '_' . str_replace(", ", "_", $limit) . '_' . ($is_combine ? "1" : "0"));
		
		$nt_list = $this->data_cache($notification_list_key, '$this->model("notify")->list_notify("' . $type . '", "' . $limit . '", "' . $is_combine . '")', get_setting('cache_level_high'), ($type ? "notification_list_all" : "notification_list_unread"));
		
		if (intval($this->_INPUT["num"]) > 0)
		{
			$nt_list = array_slice($nt_list, 0, intval($this->_INPUT["num"]));
		}
		
		TPL::assign("type", $type);
		TPL::assign("nt_list", $nt_list);
		TPL::output("notifications/notify_index_more_ajax");
	}

	/**
	 * 
	 * 系统首页阅读通知
	 * 
	 * @return boolean true
	 */
	public function r_notify_action()
	{
		$notify_id = intval($this->_INPUT["notify_id"]);
		
		$read_type = intval($this->_INPUT['read_type']);
		
		//单条阅读
		if ($read_type == 1)
		{
			$this->model('notify')->read_notify($notify_id);
		}
		//全部阅读
		else if ($read_type == 0)
		{
			$this->model('notify')->read_all();
		}
		
		H::ajax_json_output(GZ_APP::RSM(null, "1", "阅读成功"));
	}

}