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
		
		$rule_action["actions"] = array(
			'user_json'
		);
		
		return $rule_action;
	}

	function setup()
	{
		$this->per_page = get_setting('contents_per_page');
		
		HTTP::no_cache_header();
	}

	/**
	 * 个人动态
	 */
	function user_actions_action()
	{
		
		if((isset($_GET["distint"])&&($_GET["distint"]==0)))
		{
			$data = $this->model('account')->get_user_actions($_GET['uid'], ($_GET["page"] * 1 * $this->per_page) . ", {$this->per_page}", $_GET['get_type'], $_GET['actions'], $this->user_id,0);
		}
		else
		{
			$data = $this->model('account')->get_user_actions($_GET['uid'], ($_GET["page"] * 1 * $this->per_page) . ", {$this->per_page}", $_GET['get_type'], $_GET['actions'], $this->user_id);			
		}
		TPL::assign('list', $data);
		
		if ($_GET['uid'] == $this->user_id)
		{
			$users["ta"] = '我';
		}
		else
		{
			$users["ta"] = 'TA';
		}
		
		TPL::assign('users', $users);
		
		if (($_GET['get_type'] == "questions") && ($_GET['actions'] == '201'))
		{
			TPL::output('people/personal_user_actions_questions_201');
		
		}
		else if (($_GET['get_type'] == "questions") && ($_GET['actions'] == '101'))
		{
			TPL::output('people/personal_user_actions_questions_101');
		
		}
		else
		{
			TPL::output('people/personal_user_actions');
		}
	}

	/**
	 * JSON
	 */
	function user_json_action()
	{
		$uid = intval($this->_INPUT['uid']);
		
		if ($uid <= 0)
		{
			H::ajax_json_output(array(
				'uid' => null
			));
		}
		
		$account_obj = $this->model('account');
		$user = $account_obj->get_users_by_uid($uid);
		
		if (empty($user))
		{
			H::ajax_json_output(array(
				'uid' => null
			));
		}
		
		$user_detail = $account_obj->get_users_attrib($uid);
		$user_url = $account_obj->get_url_by_uid($uid);
		$follow_obj = $this->model('follow');
		$user_follow_check = $follow_obj->user_follow_check($this->user_id, $uid);
		$job_info = $account_obj->get_jobs_by_id($user['job_id']);
		$user_area = $account_obj->get_user_areas_by_uids(array(
			$uid
		));
		$user_json['type'] = 'people';
		$user_json['uid'] = $user['uid'];
		$user_json['user_name'] = $user['user_name'];
		$user_json['avatar_file'] = get_avatar_url($uid, 'mid', $user['avatar_file']);
		$user_json['url'] = $user_url;
		$user_json['area'] = $user_area[$uid] ? $user_area[$uid] : null;
		$user_json['job'] = $job_info['jobs_name'];
		$user_json['signature'] = $user_detail['signature'];
		$user_json['integral'] = $user['integral'];
		$user_json['award_count'] = $user['award_count'];
		$user_json['focus'] = $user_follow_check ? true : false;
		$user_json['is_me'] = ($this->user_id == $uid) ? true : false;
		
		H::ajax_json_output($user_json);
	}

	/**
	 * 积分TOP50排行榜
	 * 
	 */
	public function top_list_ajax_action()
	{
		$user_list = $this->model('people')->get_integral_top_list();
		
		TPL::import_css('css/discussion.css');
		
		TPL::assign('list', $user_list);
		
		TPL::output('people/top_50_more_ajax_v2');
	}
	
	//粉丝的人
	function followers_more_action()
	{
		
		$uid = $this->_INPUT["uid"] * 1;
		$limit = $this->_INPUT["page"] * 1 * $this->per_page;
		$limit = $limit . ",{$this->per_page}";
		$cache_key = ZCACHE::format_key('follow_get_user_fans_' . $uid . '_' . $limit);
		$followers_list = ZCACHE::get($cache_key);
		
		if ($followers_list === false)
		{
			$followers_list = $this->model('follow_class')->get_user_fans($uid, $limit);
			
			if ($followers_list)
			{
				foreach ($followers_list as $key => $val)
				{
					if ($this->user_id == $val["fans_uid"])
					{
						$followers_list[$key]["show_focus_btn"] = 0;
					}
					else
					{
						$followers_list[$key]["show_focus_btn"] = 1;
						$followers_list[$key]["show_focus"] = $this->model('follow')->user_follow_check($this->user_id, $val["fans_uid"]) ? 0 : 1;
					}
					$fans_uid_array[] = $val["fans_uid"];
				}
				
				if (isset($fans_uid_array) && is_array($fans_uid_array))
				{
					$area_tmp = $this->model('account')->get_user_areas_by_uids($fans_uid_array);
					foreach ($followers_list as $key => $val)
					{
						$followers_list[$key]['area_name'] = $area_tmp[$val["fans_uid"]];
					}
				}
			}
			else
			{
				$followers_list = array();
			}
			
			ZCACHE::set($cache_key, $followers_list, null, get_setting('cache_level_high'));
		}
		
		if (! $followers_list)
		{
			exit();
		}
		
		TPL::assign('friends_list', $followers_list);
		
		TPL::output('people/friends_list_ajax_v2');
	}

	/**
	 * 我的关注
	 */
	function following_more_action()
	{
		$uid = $this->_INPUT["uid"] * 1;
		$limit = $this->_INPUT["page"] * 1 * $this->per_page;
		$limit = $limit . ",{$this->per_page}";
		$cache_key = ZCACHE::format_key("follow_get_user_friends_" . $uid . "_" . $limit);
		$friends_list = ZCACHE::get($cache_key);
		
		if ($friends_list === false)
		{
			$friends_list = $this->model("follow")->get_user_friends($uid, $limit);
			
			if ($friends_list)
			{
				
				foreach ($friends_list as $key => $val)
				{
					
					if ($this->user_id == $val["friend_uid"])
					{
						$friends_list[$key]["show_focus_btn"] = 0;
					}
					else
					{
						$friends_list[$key]["show_focus_btn"] = 1;
						$friends_list[$key]["show_focus"] = $this->model("follow")->user_follow_check($this->user_id, $val["friend_uid"]) ? 0 : 1;
					}
					
					$friends_uid_array[] = $val["friend_uid"];
				}
				
				if (isset($friends_uid_array) && is_array($friends_uid_array))
				{
					$area_tmp = $this->model('account')->get_user_areas_by_uids($friends_uid_array);
					foreach ($friends_list as $key => $val)
					{
						$friends_list[$key]['area_name'] = $area_tmp[$val["friend_uid"]];
					}
				}
			}
			else
			{
				$friends_list = array();
			}
			
			ZCACHE::set($cache_key, $friends_list, null, get_setting('cache_level_high'));
		}
		
		if (! $friends_list)
		{
			exit();
		}
		
		TPL::assign('friends_list', $friends_list);
		
		TPL::output('people/friends_list_ajax_v2');
	}

	/**
	 * 积分历史
	 */
	function integral_log_more_action()
	{
		
		$uid = $this->_INPUT["uid"] * 1;
		$limit = $this->_INPUT["page"] * 1 * $this->per_page;
		$limit = $limit . ",{$this->per_page}";
		$integral_log_list = $this->model("integral")->get_integral_log("", $uid, $limit);
		
		if (! $integral_log_list)
		{
			exit();
		}
		
		if ($uid != $this->user_id)
		{
			exit();
		}
		
		$category_list = array(
			'0' => '其它'
		);
		
		TPL::assign('integral_log_list', $integral_log_list);
		
		TPL::output('people/Integral_log_ajax');
	
	}

	/**
	 * 关注的话题
	 */
	function focustopics_more_action()
	{
		$uid = $this->_INPUT["uid"] * 1;
		$limit = $this->_INPUT["page"] * 1 * $this->per_page;
		$limit = $limit . ",{$this->per_page}";
		$cache_key = ZCACHE::format_key('topic_get_focus_topic_list_false_' . $uid . '_' . $limit);
		$topic_list = ZCACHE::get($cache_key);
		
		if ($topic_list === false)
		{
			$topic_list = $this->model('topic')->get_focus_topic_list(false, $uid, $limit);
			
			if ($topic_list)
			{
				foreach ($topic_list as $key => $val)
				{
					if (empty($val['topic_pic']))
					{
						$topic_list[$key]['topic_pic_mid'] = G_STATIC_URL . '/common/topic-mid-img.jpg';
					}
					else
					{
						$topic_list[$key]['topic_pic_mid'] = get_setting('upload_url') . '/topic/' . str_replace("32_32", "50_50", $val["topic_pic"]);
					}
				}
			}
			else
			{
				$topic_list = array();
			}
			ZCACHE::set($cache_key, $topic_list, null, get_setting('cache_level_high'));
		}
		
		if (! $topic_list)
		{
			exit();
		}
		
		TPL::assign('topic_list', $topic_list);
		
		TPL::output('people/focustopics_list_ajax_v2');
	}
}