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
	public $per_page = 10;

	public function get_access_rule()
	{
		$rule_action["rule_type"] = "white"; //'black'黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["actions"] = array();
		
		return $rule_action;
	}

	public function setup()
	{
		if (get_setting('index_per_page'))
		{
			$this->per_page = get_setting('index_per_page');
		}
		
		HTTP::no_cache_header();
	}

	public function crond_action()
	{
		$this->model('crond')->start();
		
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');             // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: no-cache, must-revalidate');           // HTTP/1.1
		header('Pragma: no-cache');                                   // HTTP/1.0
		header("Content-disposition: inline; filename=clear.gif");
		//header('Content-transfer-encoding: binary');
		//header("Content-Length: " . strlen('R0lGODlhAQABAIAAAMDAwAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='));
		//header('Content-type: image/gif');
		echo base64_decode('R0lGODlhAQABAIAAAMDAwAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
		exit;
	}

	public function notifications_action()
	{
		$inbox_num = $this->data_cache("inbox_unread_" . $this->user_id, '$this->model("account")->get_user_counts("notice_unread", $this->user_id)', get_setting('cache_level_high'));
		
		$notifications_num = $this->data_cache('notification_unread_' . $this->user_id, '$this->model("notify")->get_notifications_unread_num()', get_setting('cache_level_high'));
		
		$data = array(
			'inbox_num' => $inbox_num, 
			'notifications_num' => $notifications_num
		);
		
		H::ajax_json_output(GZ_APP::RSM($data, "1", "获取数据成功"));
	}

	public function index_actions_action()
	{
		if ($this->_INPUT['uid'] and $this->_INPUT['filter'] == 'focus')
		{
			switch ($this->_INPUT['type'])
			{
				default :
				case 'all' :
					$data = $this->model('index')->get_user_focus($this->_INPUT["uid"], ($this->_INPUT["page"] * 1 * $this->per_page) . ", {$this->per_page}");
					break;
				
				case 'competitions' :
					$data = $this->model('competitions')->get_user_focus($this->_INPUT["uid"], ($this->_INPUT["page"] * 1 * $this->per_page) . ", {$this->per_page}");
					break;
				
				case 'questions' :
					$data = $this->model('question')->get_user_focus($this->_INPUT["uid"], ($this->_INPUT["page"] * 1 * $this->per_page) . ", {$this->per_page}");
					break;
			}
			
			if ($data)
			{
				foreach ($data as $key => $val)
				{
					$data[$key]["add_time"] = $val["add_time"];
					$data[$key]["update_time"] = $val["update_time"];
					
					$data[$key]['last_user'] = $this->model('account')->get_users($val['last_uid']);
					
					if (! $user_info_list[$val["published_uid"]])
					{
						$user_info_list[$val["published_uid"]] = $this->model('account')->get_users_by_uid($val["published_uid"], true);
					}
					
					$data[$key]["user_info"] = $user_info_list[$val['published_uid']];
					
					if ($val["competitions_id"])
					{
						$data[$key]['competitions'] = $val;
						$data[$key]['associate_type'] = 5;
						$data[$key]['last_user'] = $this->model('account')->get_users($val['last_uid']);						
						$data[$key]['topics'] = $this->model('competitions')->get_competition_topic_by_id($val["competitions_id"]);
						
						//是否关注
						if ($this->model('competitions')->get_focus_status($val["competitions_id"], $this->user_id))
						{
							$data[$key]['has_focus'] = true;
						}
						else
						{
							$data[$key]['has_focus'] = false;
						}
					}
					else
					{
						$data[$key]['associate_type'] = 1;						
						$data[$key]['topics'] = $this->model('question_topic')->get_question_topic_by_question_id($val['question_id']);
						
						if ($this->model('question')->has_focus_question($val["question_id"], $this->user_id))
						{
							$data[$key]['has_focus'] = true;
						}
						else
						{
							$data[$key]['has_focus'] = false;
						}
					}
				}
			}
		}
		else if ($this->_INPUT['uid'])
		{
// 			$cache_key = ZCACHE::format_key("account_get_user_actions_" . $this->_INPUT["uid"] . "_" . $this->_INPUT["page"] * 1 * $this->per_page . "_{$this->per_page}" . "_" . $this->_INPUT["type"] . "_" . $actions);
// 			$data = ZCACHE::get($cache_key);
			
// 			if ($data === false)
// 			{
				if ($this->_INPUT['uid'] == 'public')
				{
					$data = $this->model('account')->get_user_actions(0, ($this->_INPUT["page"] * 1 * $this->per_page) . ", {$this->per_page}", $this->_INPUT['type'], false, $this->user_id);
				}
				else
				{
					switch ($this->_INPUT['type'])
					{
						default :
							$data = $this->model('index')->get_user_publish($this->_INPUT["uid"], ($this->_INPUT["page"] * 1 * $this->per_page) . ", {$this->per_page}");
							break;
						
						case 'competitions' :
							$data = $this->model('competitions')->get_user_publish($this->_INPUT["uid"], ($this->_INPUT["page"] * 1 * $this->per_page) . ", {$this->per_page}");
							break;
						
						case 'questions' :
							$data = $this->model('question')->get_user_publish($this->_INPUT["uid"], ($this->_INPUT["page"] * 1 * $this->per_page) . ", {$this->per_page}");
							break;
					}
				}
				
				//ZCACHE::set($cache_key, $data, null, 60);
// 			}
			
			if ($data and $this->_INPUT['uid'] != 'public')
			{
				foreach ($data as $key => $val)
				{
					$data[$key]["add_time"] = $val["add_time"];
					$data[$key]["update_time"] = $val["update_time"];
					$data[$key]['last_user'] = $this->model('account')->get_users($val['last_uid']);
					
					if (! $user_info_list[$val["published_uid"]])
					{
						$user_info_list[$val["published_uid"]] = $this->model('account')->get_users_by_uid($val["published_uid"], true);
					}
					
					$data[$key]["user_info"] = $user_info_list[$val['published_uid']];
					
					if ($val["competitions_id"])
					{
						$data[$key]['competitions'] = $val;
						$data[$key]['associate_type'] = 5;						
						$data[$key]['topics'] = $this->model('competitions')->get_competition_topic_by_id($val["competitions_id"]);
						
						//是否关注
						if ($this->model('competitions')->get_focus_status($val["competitions_id"], $this->user_id))
						{
							$data[$key]['has_focus'] = true;
						}
						else
						{
							$data[$key]['has_focus'] = false;
						}
					}
					else
					{
						$data[$key]['associate_type'] = 1;						
						$data[$key]['topics'] = $this->model('question_topic')->get_question_topic_by_question_id($val['question_id']);
						
						if ($this->model('question')->has_focus_question($val["question_id"], $this->user_id))
						{
							$data[$key]['has_focus'] = true;
						}
						else
						{
							$data[$key]['has_focus'] = false;
						}
					}
				}
			}
		
		}
		else
		{
// 			$cache_key = ZCACHE::format_key("index_get_index_focus_" . $this->user_id . "_" . $this->_INPUT["page"] * 1 * $this->per_page . "_{$this->per_page}" . "_" . $this->_INPUT["type"]);
// 			$data = ZCACHE::get($cache_key);
			
// 			if ($data === false)
// 			{
 				$data = $this->model('index')->get_index_focus($this->user_id, ($this->_INPUT["page"] * 1 * $this->per_page) . ", {$this->per_page}", $this->_INPUT['type']);
				
// 				ZCACHE::set($cache_key, $data, null, 60);
// 			}
		}
		
		TPL::assign("list", $data);
		
		if ($_GET['template'] == 'list')
		{
			TPL::output("index/index_index_actions_list_ajax_v2");
		}
		else
		{
			TPL::assign('page', $this->_INPUT["page"]);
			TPL::output("index/index_index_actions_ajax_v2");
		}
	}

	public function check_actions_new_action()
	{
		$time = intval($this->_INPUT['time']);		
		$data = $this->model('index')->get_index_focus($this->user_id, "0, {$this->per_page}", "all");		
		$new_count = 0;
		
		foreach ($data as $key => $val)
		{
			if ($val['add_time'] > $time)
			{
				$new_count ++;
			}
		}
		
		H::ajax_json_output(GZ_APP::RSM(array(
			'new_count' => $new_count
		), 1, ""));
	}
	
	public function draft_action()
	{
		$page = intval($this->_INPUT['page']);

		$drafts = $this->model('draft')->get_all('answer', $this->user_id, $page * $this->per_page .', '. $this->per_page);
		
		foreach ($drafts AS $key => $val)
		{
			$drafts[$key]['question_info'] = $this->model("question")->get_question_info_by_id($val['item_id']);
		}
		
		TPL::assign("drafts", $drafts);
		TPL::output("index/draft");
	}
	
	public function invite_action()
	{
		$page = intval($this->_INPUT['page']);

		$list = $this->model('question')->get_invite_question_list($this->user_id, $page * $this->per_page .', '. $this->per_page);
		
		if($list)
		{
			$uids = array();
				
			foreach($list as $key => $val)
			{
				$uids[] = $val['sender_uid'];
			}
				
			$user_info = $this->model('account')->get_users_by_uids($uids);
			
			foreach($list as $key => $val)
			{
				$list[$key]['user'] = array(
						'user_name' => $user_info[$val['sender_uid']]['user_name'],
						'url' => $user_info[$val['sender_uid']]['url'],
				);
			}
		}
		
		TPL::assign("list", $list);
		TPL::output("index/invite");
	}
}