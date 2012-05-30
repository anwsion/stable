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
	public $current_uid;
	public $action_key_val;

	public function get_access_rule()
	{
		$rule_action["rule_type"] = 'white'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["actions"] = array();
		
		return $rule_action;
	}

	public function setup()
	{
		if (isset($this->_INPUT['ntid']))
		{
			$this->model('notify')->read_notify($this->_INPUT['ntid']);
		}
		
		$this->action_key_val = ACTION_LOG::$ACTION_STRING_ARRAY; //注释调用ACTION_LOG动作表
		

		if (intval($this->_INPUT["uid"]))
		{
			$this->current_uid = intval($this->_INPUT["uid"]);
		}
		else
		{
			$this->current_uid = $this->model('account')->get_uid_by_url($this->_INPUT["u"]);
			
		
			
			if (! $this->current_uid)
			{
				$this->current_uid = $this->model('account')->get_uid_by_user_name($this->_INPUT["u"]);
			}
			
			if (! $this->current_uid)
			{
				$this->current_uid = $this->user_id;
			}
		}
		
		$this->current_uid = intval($this->current_uid);
		
		if (! $this->current_uid)
		{
			HTTP::redirect('/');
		}
	
	}

	/**
	 * 默认运作,显示个人首页
	 */
	function index_action()
	{
		
		if ($this->current_uid == $this->user_id)
		{
			$user = $this->user_info;
		}
		else
		{
			$user = $this->data_cache('account_get_users_' . $this->current_uid . "_true", '$this->model("account")->get_users(' . $this->current_uid . ', true)', get_setting('cache_level_high'), "g_uid_" . $this->current_uid);
		}
		
		$focus_topics = $this->data_cache('topic_get_focus_topic_list_' . $user['uid'] . '_5', '$this->model("topic")->get_focus_topic_list(false, ' . $user['uid'] . ', 5)', get_setting('cache_level_high'));
		
		$right_fans_list = $this->data_cache('follow_get_user_fans_5' . $this->current_uid, '$this->model("follow")->get_user_fans(' . $this->current_uid . ',5)', get_setting('cache_level_high'), "g_uid_" . $this->current_uid);
		
		$right_friends_list = $this->data_cache('follow_get_user_friends_5' . $this->current_uid, '$this->model("follow")->get_user_friends(' . $this->current_uid . ',5)', get_setting('cache_level_high'), "g_uid_" . $this->current_uid);
		
		//$in_integral_top = $this->model('people')->in_integral_top_list($user["uid"]);
		

		//更新计数
		$this->model('people')->update_views_count($user["uid"]);
		
		if ($user['uid'] == $this->user_id)
		{
			$user["ta"] = '我';
		}
		else
		{
			$user["ta"] = 'TA';
		}
		
		TPL::assign('user', $user);
		
		/*$job_info = $this->model('account')->get_jobs_by_id($user['job_id']);
		
		TPL::assign('jobs_name', $job_info["jobs_name"]);*/
		
		foreach ($focus_topics as $key => $val)
		{
			$focus_topics[$key]["has_focus"] = $this->model("topic")->has_focus_topic($this->user_id, $val["topic_id"]);
		}
		
		//TPL::assign('in_integral_top', $in_integral_top);
		//TPL::assign('user_job', $user_job[$user['uid']]);
		TPL::assign('user_follow_check', $this->model('follow')->user_follow_check($this->user_id, $this->current_uid));
		
		TPL::assign('right_fans_list', $right_fans_list);
		
		TPL::assign('right_friends_list', $right_friends_list);
		TPL::assign('focus_topics', $focus_topics);
		
		$this->crumb($user['user_name'] . ' 的个人主页', '/people/?u=' . urlencode($user['user_name']));
		
		TPL::import_css('css/discussion.css');
		TPL::import_css('css/bank_Cash.css');
		
		TPL::output("people/personal_index_v2");
	}

	/**
	 * 积分TOP50排行榜
	 * 
	 */
	public function top_action()
	{
		$week_sum_list = $this->data_cache('integral_get_integral_sum_by_uid_time', '$this->model("integral")->get_integral_sum_by_uid_time(false, "1 week")', get_setting('cache_level_high'));
		
		$this->crumb('积分 TOP50', '/people/?act=top');
		
		TPL::assign('update_time', $this->model('people')->get_integral_top_list("", true));
		TPL::assign('week_sum_list', $week_sum_list);
		
		TPL::import_css('css/discussion.css');
		
		TPL::output('people/top_50_v2');
	}

}