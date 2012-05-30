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

class notify_class extends GZ_MODEL
{
	//=========模型类别:model_type===================================================
	

	const CATEGORY_QUESTION = 1; //问题
	const CATEGORY_PEOPLE = 4; //人物
	const CATEGORY_CONTEXT = 7; //文字
	

	//=========操作标示:action_type==================================================
	

	const TYPE_PEOPLE_FOCUS = 101; //被人关注
	const TYPE_COMMENT_QUESTION = 102; //关注的问题增加了新回复
	const TYPE_COMMENT_BE_REPLY = 103; //自己的评论被人回复
	const TYPE_INVITE_QUESTION = 104; //被人邀请问题问题
	const TYPE_CONTEXT = 115; //纯文本通知
	

	//===============================================================================
	

	public static $model_types = array(
		notify_class::CATEGORY_QUESTION, 
		notify_class::CATEGORY_PEOPLE, 
		notify_class::CATEGORY_CONTEXT,
	);
	
	public $user_id;
	public $notify_actions = array();
	public $notify_action_details;
	public $nosend_user_actions;

	public function setup()
	{
		$this->user_id = USER::get_client_uid();
		
		$this->notify_action_details = GZ_APP::config()->get('notification')->action_details;
		
		foreach ($this->notify_action_details as $key => $val)
		{
			$this->notify_actions[] = $key;
		}
		
		//以下动作不通知当前用户
		$this->nosend_user_actions = array(
			notify_class::TYPE_COMMENT_QUESTION, 
			notify_class::TYPE_COMMENT_BE_REPLY
		);
	}

	/**
	 * 发送通知
	 * Enter description here ...
	 * @param unknown_type $action_type	操作类型，使用notify_class调用TYPE
	 * @param unknown_type $uid			接收用户id
	 * @param unknown_type $data		附加数据
	 * @param unknown_type $model_type	可选，合并类别，使用notify_class调用CATEGORY
	 * @param unknown_type $source_id	可选，合并子ID
	 */
	public function send($action_type, $uid, $data, $model_type = 0, $source_id = 0)
	{
		$action_type = intval($action_type);
		
		$uid = intval($uid);
		
		if (! in_array($action_type, $this->notify_actions) && ($action_type != 0))
		{
			return false;
		}
		
		if (($uid == $this->user_id) && (in_array($action_type, $this->nosend_user_actions)))
		{
			return false;
		}
		
		if (! $this->check_notification_setting($uid, $action_type))
		{
			return false;
		}
		
		return $this->add_notify($action_type, $uid, $data, $model_type, $source_id);
	}

	/**
	 * 插入通知记录
	 * Enter description here ...
	 * @param unknown_type $action_type
	 * @param unknown_type $model_type
	 * @param unknown_type $source_id
	 * @param unknown_type $recipient_uid
	 * @param unknown_type $data
	 * @param unknown_type $sender_uid
	 */
	public function add_notify($action_type, $recipient_uid, $data, $model_type, $source_id)
	{
		$notify_data['action_type'] = $action_type;
		$notify_data['model_type'] = $model_type;
		$notify_data['source_id'] = $source_id;
		$notify_data['add_time'] = time();
		
		if (! empty($data))
		{
			$notify_data['data'] = serialize($data);
		}
		
		if (isset($data['sender_uid']))
		{
			$notify_data['sender_uid'] = $data['sender_uid'];
		}
		
		$notification_id = $this->insert('notification', $notify_data);
		
		if (! $notification_id)
		{
			return false;
		}
		
		$notify_rcv_data['notification_id'] = $notification_id;
		$notify_rcv_data['recipient_uid'] = $recipient_uid;
		$notify_rcv_data['recipient_time'] = 0;
		$notify_rcv_data['recipient_del'] = 0;
		
		$this->insert('notification_recipient', $notify_rcv_data);
		
		$this->model("account")->increase_user_statistics(account_class::NOTIFICATION_UNREAD, 0, $recipient_uid);
		
		ZCACHE::delete("notification_unread_" . $recipient_uid);
		
		ZCACHE::cleanGroup("notification_list_all");
		
		ZCACHE::cleanGroup("notification_list_unread");
		
		return $notification_id;
	}

	/**
	 * 获得通知列表
	 * @param unknown_type $read_status
	 * @param unknown_type $limit
	 * @param unknown_type $list_combine
	 * @return Ambigous <Ambigous, unknown_type, unknown, void, string>
	 */
	public function list_notify($read_status = false, $limit = "0,20", $list_combine = true, $uid = 0)
	{
		$ntlist = $this->get_notify_list($read_status, $limit, $uid);
		
		$item_arr = $this->get_items_order_by_model($ntlist);
		
		if($list_combine)
		{
			$has_arr = array();
			
			foreach ($ntlist as $key => $notify)
			{
				$combine_key = $notify['model_type'] . "_" . $notify['action_type'] . "_" . $notify['source_id'];
					
				if (in_array($combine_key, $has_arr))
				{
					unset($ntlist[$key]);
					continue;
				}
				
				$has_arr[] = $combine_key;
			}
		}
		
		foreach ($ntlist as $key => $notify)
		{
			if(!in_array($notify['action_type'], $this->notify_actions))
			{
				unset($ntlist[$key]);
				continue;
			}
			
			$combine_key = $notify['model_type'] . "_" . $notify['action_type'] . "_" . $notify['source_id'];
				
			$data = $notify['data'];
			
			if($item_arr[$combine_key])
			{
				$data["item_id"] = implode(",", array_unique($item_arr[$combine_key]));
			}
			
			$action_type = $notify['action_type'];
			
			if ($data['from_uid'])
			{
				$userinfo = $this->model('account')->get_users_by_uid($data['from_uid']);
				$data['p_url'] = $this->model('account')->get_url_by_uid($data['from_uid']);
				$data['p_username'] = $userinfo['user_name'];
			}
			
			$token = "ntid=" . $notify['notification_id'];
			
			switch ($action_type)
			{
				case notify_class::TYPE_PEOPLE_FOCUS :
					
					if(empty($userinfo))
					{
						unset($ntlist[$key]);
						continue;
					}
					
					$data['key_url'] = $data['p_url'] . "&" . $token;
			
					break;
				case notify_class::TYPE_COMMENT_QUESTION :
			
					$question_info = $this->model('question')->get_question_info_by_id($data['question_id']);
					
					if(empty($question_info))
					{
						unset($ntlist[$key]);
						continue;
					}
			
					$item_str = $data["item_id"] ? "&item_id=" . $data["item_id"] . "#answers" : "";
			
					$data['title'] = $question_info['question_content'];
					$data['key_url'] = get_setting('base_url') . '/question/?act=detail&question_id=' . $data['question_id'] . "&" . $token . $item_str;
			
					break;
				case notify_class::TYPE_COMMENT_BE_REPLY :
			
					$question_info = $this->model('question')->get_question_info_by_id($data['question_id']);
					
					if(empty($question_info))
					{
						unset($ntlist[$key]);
						continue;
					}
						
					$item_str = $data["item_id"] ? "&item_id=" . $data["item_id"] . "#answers" : "";
			
					$data['title'] = $question_info['question_content'];
					$data['key_url'] = get_setting('base_url') . '/question/?act=detail&question_id=' . $data['question_id'] . "&" . $token . $item_str;
			
					break;
				case notify_class::TYPE_INVITE_QUESTION :
			
					$question_info = $this->model('question')->get_question_info_by_id($data['question_id']);
					
					if(empty($question_info))
					{
						unset($ntlist[$key]);
						continue;
					}
					
					$data['title'] = $question_info['question_content'];
					$data['key_url'] = get_setting('base_url') . '/question/?act=detail&question_id=' . $data['question_id'] . "&" . $token;
			
					break;
				case notify_class::TYPE_CONTEXT :
			
					break;
			}
			
			if($ntlist[$key])
			{
				$ntlist[$key]['data'] = $data;
			}
		}
		
		return $ntlist;
	}

	/**
	 * 通知列表格式化
	 * @param unknown_type $ntlist
	 * @return Ambigous <unknown, void, string>
	 */
	/*
	function ntlist_format($ntlist, $item_arr)
	{
		foreach ($ntlist as $key => $notify)
		{
			if(!in_array($notify['action_type'], $this->notify_actions))
			{
				unset($ntlist[$key]);
				continue;
			}
			
			if ($notify['handled'])
			{
				continue;
			}
			
			$ntlist[$key] = $notify;
			
			$data = $this->get_notify_data_detail($notify);
			
			if ($data)
			{
				$ntlist[$key]['data'] = $data;
			}
			else
			{
				unset($ntlist[$key]);
			}
		}
		
		return $ntlist;
	}
	*/

	/**
	 * 得到单条通知的详细信息
	 * @param unknown_type $notify
	 * @return void|Ambigous <string, unknown>
	 */
	/*
	function get_notify_data_detail($notify)
	{
		$data = $notify['data'];
		
		$action_type = $notify['action_type'];
		
		if ($data['from_uid'])
		{
			$userinfo = $this->model('account')->get_users_by_uid($data['from_uid']);
			$data['p_url'] = $this->model('account')->get_url_by_uid($data['from_uid']);
			$data['p_username'] = $userinfo['user_name'];
		}
		
		$token = "ntid=" . $notify['notification_id'];
		
		switch ($action_type)
		{
			case notify_class::TYPE_PEOPLE_FOCUS :
				
				$data['key_url'] = $data['p_url'] . "&" . $token;
				
				break;
			case notify_class::TYPE_COMMENT_QUESTION :
				
				$question_info = $this->model('question')->get_question_info_by_id($data['question_id']);
				
				$item_str = $data["item_id"] ? "&item_id=" . $data["item_id"] . "#answers" : "";
				
				$data['title'] = $question_info['question_content'];
				$data['key_url'] = get_setting('base_url') . '/question/?act=detail&question_id=' . $data['question_id'] . "&" . $token . $item_str;
				
				break;
			case notify_class::TYPE_COMMENT_BE_REPLY :

				$question_info = $this->model('question')->get_question_info_by_id($data['question_id']);
				
				$item_str = $data["item_id"] ? "&item_id=" . $data["item_id"] . "#answers" : "";
				
				$data['title'] = $question_info['question_content'];
				$data['key_url'] = get_setting('base_url') . '/question/?act=detail&question_id=' . $data['question_id'] . "&" . $token . $item_str;
				
				break;
			case notify_class::TYPE_INVITE_QUESTION :
				
				$question_info = $this->model('question')->get_question_info_by_id($data['question_id']);
				
				$data['title'] = $question_info['question_content'];
				$data['key_url'] = get_setting('base_url') . '/question/?act=detail&question_id=' . $data['question_id'] . "&" . $token;
				
				break;
			case notify_class::TYPE_CONTEXT :
				
				break;
		}
		
		return $data;
	}
	*/

	/**
	 * 统计通知组合
	 * @param unknown_type $ntlist
	 */
	/*
	function get_model_num_count($ntlist)
	{
		//组合数据
		$model_num_count = array();
		
		foreach ($ntlist as $key => $val)
		{
			if ($val['model_type'] == 0 || $val['source_id'] == 0)
			{
				continue;
			}
			
			$model_num_count[] = $val['model_type'] . "_" . $val['source_id'];
		}
		
		$num_count_value = array_count_values($model_num_count);
		
		foreach ($num_count_value as $key => $val)
		{
			if ($val <= 1)
			{
				unset($num_count_value[$key]);
			}
		}
		
		return $num_count_value;
	}
	*/

	/**
	 * 组合通知中的参数
	 * @param unknown_type $ntlist
	 * @return multitype:multitype:
	 */
	function get_items_order_by_model($ntlist)
	{
		$item_arr = array();
		
		foreach ($ntlist as $key => $val)
		{
			$combine_key = $val['model_type'] . "_" . $val['action_type'] . "_" . $val['source_id'];
			
			if (empty($val['data']['item_id']))
			{
				continue;
			}
			
			$item_arr[$combine_key][] = $val['data']['item_id'];
			
			$item_arr[$combine_key] = array_unique($item_arr[$combine_key]);
		}
		
		return $item_arr;
	}

	/**
	 * 相同通知模块整合
	 * @param unknown_type $ntlist
	 * @param unknown_type $list_combine
	 * @return unknown|boolean
	 */
	/*
	function ntlist_combine($ntlist, $list_combine)
	{
		$num_count = $this->get_model_num_count($ntlist);
		
		if (empty($num_count))
		{
			return $ntlist;
		}
		
		$has_arr = array();
		
		$item_arr = $this->get_items_order_by_model($ntlist);
		
		foreach ($ntlist as $key => $notify)
		{
			if (($notify['model_type'] == 0) || ($notify['source_id'] == 0))
			{
				continue;
			}
			
			$combine_key = $notify['model_type'] . "_" . $notify['source_id'];
			
			if (in_array($combine_key, $has_arr))
			{
				unset($ntlist[$key]);
				continue;
			}
			
			if (! isset($num_count[$combine_key]))
			{
				continue;
			}
			
			//关于同个问题的多项通知
			if (($notify['model_type'] == notify_class::CATEGORY_QUESTION) && ($list_combine || empty($notify['data'])))
			{
				
				$q_info = $this->model('question')->get_question_info_by_id($notify['source_id']);
				
				if (empty($q_info))
				{
					unset($ntlist[$key]);
					continue;
				}
				
				if (is_array($item_arr[$combine_key]))
				{
					$item_ids = $item_arr[$combine_key];
					$item_str = "&item_id=" . implode(",", array_unique($item_ids)) . "#answers";
				}
				
				$ntlist[$key]['data']['num'] = $num_count[$combine_key];
				$ntlist[$key]['data']['key_url'] = get_setting('base_url') . "/question/?act=detail&question_id=" . $q_info['question_id'] . "&ntid=" . $notify['notification_id'] . $item_str;
				$ntlist[$key]['data']['title'] = $q_info['question_content'];
				
				
				$has_arr[] = $notify['model_type'] . "_" . $notify['source_id'];
				//$ntlist[$key]['handled'] = true;
			}
			
			//人物关注
			if ($notify['model_type'] == notify_class::CATEGORY_PEOPLE)
			{
				$has_arr[] = $notify['model_type'] . "_" . $notify['source_id'];
			}
		}
		
		return $ntlist;
	}
	
	*/

	/**
	 * 获得通知列表
	 * @param unknown_type $read_status
	 * @param unknown_type $limit
	 * @return mixed
	 */
	function get_notify_list($read_status, $limit, $uid)
	{
		$uid = intval($uid);
		
		if($uid == 0)
		{
			$uid = $this->user_id;
		}
		
		$sql = "SELECT * FROM " . $this->get_table('notification') . " as a LEFT JOIN " . $this->get_table('notification_recipient') . " as b
			ON a.notification_id = b.notification_id WHERE b.recipient_uid = '" . $uid. "'";
		
		if (! $read_status)
		{
			$sql .= " AND b.recipient_time = 0";
		}
		
		$sql .= " ORDER BY a.notification_id DESC ";
		
		$sql .= " LIMIT " . $limit;
		
		$rs = $this->query_all($sql);
		
		foreach ($rs as $key => $val)
		{
			$rs[$key]['data'] = unserialize($val['data']);
		}
		
		return $rs;
	}

	/**
	 * 检查指定用户的通知设置
	 * Enter description here ...
	 */
	public function check_notification_setting($recipient_uid, $action_type)
	{
		if (! in_array($action_type, $this->notify_actions))
		{
			return false;
		}
		
		$notification_setting = $this->model('account')->get_notification_setting_by_uid($recipient_uid);
		
		//默认不认置则全部都发送
		if (empty($notification_setting['data']))
		{
			return true;
		}
		
		if (in_array($action_type, $notification_setting['data']))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * 
	 * 阅读段信息
	 * @param int $notification_id 信息id
	 * 
	 * @return array信息内容数组
	 */
	public function read_notify($notification_id)
	{
		$notify_info = $this->get_notfiy_info_by_id($notification_id);
		
		if (empty($notify_info))
		{
			return false;
		}
		
		if (($notify_info['model_type'] > 0) && ($notify_info['action_type'] > 0) && ($notify_info['source_id'] > 0))
		{
			$notic_ids = $this->get_unread_ntid_by_model($notify_info['model_type'], $notify_info['action_type'], $notify_info['source_id']);
		}
		
		if (empty($notic_ids))
		{
			$notic_ids[] = $notification_id;
		}
		
		//更新阅读时间
		$this->update('notification_recipient', array(
			'recipient_time' => time()
		), "notification_id IN (" . implode(",", array_unique($notic_ids)) . ")");
		
		//更新汇总信息
		$this->model('account')->increase_user_statistics(account_class::NOTIFICATION_UNREAD, 0, $this->user_id);
		
		ZCACHE::delete("notification_unread_" . $this->user_id);
		
		ZCACHE::cleanGroup("notification_list_unread");
		
		return $notify_info['notification_id'];
	}

	/**
	 * 全部已读
	 */
	public function read_all()
	{
		$this->update('notification_recipient', array(
			'recipient_time' => time()
		), 'recipient_uid = ' . $this->user_id);
		
		$this->model("account")->increase_user_statistics(account_class::NOTIFICATION_UNREAD, 0, $this->user_id);
		
		ZCACHE::delete("notification_unread_" . $this->user_id);
		
		ZCACHE::cleanGroup("notification_list_unread");
	}

	/**
	 * 根据通知ID获得通知记录
	 * @param unknown_type $notification_id
	 */
	function get_notfiy_info_by_id($notification_id)
	{
		$sql = "SELECT a.* FROM " . $this->get_table('notification') . " as a LEFT JOIN " . $this->get_table('notification_recipient') . " as b ON a.notification_id = b.notification_id";
		
		$sql .= " WHERE  a.notification_id = " . $notification_id . " AND b.recipient_uid = '" . $this->user_id . "'";
		
		return $this->query_row($sql);
	}

	/**
	 * 获得同类模块的未读通知
	 * @param unknown_type $model_type
	 * @param unknown_type $source_id
	 */
	function get_unread_ntid_by_model($model_type, $action_type, $source_id)
	{
		$sql = "SELECT a.notification_id FROM " . $this->get_table('notification') . " as a LEFT JOIN " . $this->get_table('notification_recipient') . " as b ON a.notification_id = b.notification_id";
		
		$sql .= " AND a.model_type = " . $model_type . " AND a.source_id = " . $source_id . " AND a.action_type = " . $action_type;
		
		$sql .= " WHERE b.recipient_uid = " . $this->user_id . " AND b.recipient_time = 0";
		
		$notic_ids_arr = $this->query_all($sql);
		
		$ids = array();
		
		foreach ($notic_ids_arr as $key => $val)
		{
			$ids[] = $val['notification_id'];
		}
		
		return $ids;
	}
	
	public function delete_notify($where)
	{
		return $this->delete('notification', $where);
	}

	/**
	 * 获取当前用户未读通知个数
	 * @param unknown_type $uid
	 * @return number
	 */
	function get_notifications_unread_num($uid = 0)
	{
		$ntlist = $this->list_notify(false, "0,100", true, $uid);
		
		return count($ntlist);
	}

}
