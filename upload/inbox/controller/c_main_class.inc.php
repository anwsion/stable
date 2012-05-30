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
	public $per_page = 10;

	public function get_access_rule()
	{
		$rule_action["rule_type"] = "white"; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["guest"] = array();
		$rule_action["user"] = array();
		
		return $rule_action;
	}

	public function setup()
	{
		$this->crumb('私信', '/inbox');
		
		TPL::assign('recommend_questions', $this->model('question')->get_user_recommend_v2($this->user_id, 10));
		
		TPL::import_css('css/discussion.css');
	}

	/**
	 * 
	 * 列出用户短信息
	 */
	public function index_action()
	{
		$data = array();
		$tmp = array();
		$users = array();
		$titles = array();
		
		$uid = $this->user_id;
		
		if ($this->_INPUT["page"] < 1)
		{
			$this->_INPUT["page"] = 1;
		}
		
		$limit = ($this->_INPUT["page"] - 1) * $this->per_page;
		
		$limit = intval($limit) . ", {$this->per_page}";
		
		$list = $this->model('message')->list_message($limit);
		
		//边栏全局菜单
		if (TPL::is_output('global/__account_slidebar_v2.tpl.htm','inbox/inbox_index' ))
		{
			TPL::assign('draft_count', $this->model('draft')->get_draft_count('answer', $this->user_id));
			TPL::assign('question_invite_count', $this->model('question')->get_invite_question_list($this->user_id, '', true));
		}
		
		//边栏可能感兴趣的人
		if(TPL::is_output('block/sidebar_recommend_users_topics.tpl.htm','inbox/inbox_index'))
		{
			$recommend_users_topics = $this->model('module')->recommend_users_topics($this->user_id);
			TPL::assign('sidebar_recommend_users_topics', $recommend_users_topics);
		}		
		if (! empty($list['user_list']))
		{
			
			$users_info = $this->model('account')->get_users_by_uids($list['user_list']);
			
			//组合用户名
			foreach ($users_info as $user)
			{
				$users[$user['uid']] = $user['user_name'];
				$users_avatar_file[$user['uid']] = $user["avatar_file"];
			}
		}
		
		//组合消息标题
		if (! empty($list['title_list']))
		{
			foreach ($list['title_list'] as $title)
			{
				$titles[$title['dialog_id']] = array(
					"title" => $title['notice_content'], 
					"type" => $title["notice_type"]
				);
			}
		}
		
		foreach ($list['content_list'] as $key => $value)
		{
			if (($value['sender_uid'] == $uid) && ($value["sender_count"] > 0)) //当前处于发送用户
			{
				//显示标题前内容
				$tmp['per_tile'] = "<a href='/people/?u=" . $value['recipient_uid'] . "'>" . $users[$value['recipient_uid']] . "</a>";
				
				$tmp["unread"] = false;
				
				//新消息条数
				if ($value["sender_unread"] > 0)
				{
					$tmp["unread"] = $value["sender_unread"];
					$tmp['btn_title'] = "有 " . $value["sender_unread"] . " 条新回复";
				}
				else
				{
					$tmp['btn_title'] = "共 " . $value["sender_count"] . " 条对话";
				}
				
				$tmp["avatar_file"] = $users_avatar_file[$value['recipient_uid']];
				
				$tmp["recipients_name"] = $users[$value['recipient_uid']];
				
				$tmp['uid'] = $value['recipient_uid'];
			}
			else if (($value['recipient_uid'] == $uid) && ($value["recipient_count"] > 0)) ////当前处于接收用户
			{
				//显示标题前内容
				$tmp['per_tile'] = "<a href='/people/?u=" . $value['sender_uid'] . "'>" . $users[$value['sender_uid']] . "</a>";
				
				$tmp["unread"] = false;
				
				//新消息条数
				if ($value["recipient_unread"] > 0)
				{
					$tmp["unread"] = $value["recipient_unread"];
					$tmp['btn_title'] = "有 " . $value["recipient_unread"] . " 条新回复";
				}
				else
				{
					$tmp['btn_title'] = "共 " . $value["recipient_count"] . " 条对话";
				}
				
				$tmp["avatar_file"] = $users_avatar_file[$value['sender_uid']];
				
				$tmp["recipients_name"] = $users[$value['sender_uid']];
				
				$tmp['uid'] = $value['sender_uid'];
			}
			
			//显示标题
			$tmp['title'] = $titles[$value['dialog_id']]["title"];
			
			$tmp["last_time"] = $value["last_time"];
			
			$tmp["dialog_id"] = $value['dialog_id'];
			
			$data[] = $tmp;
		}
		
		$this->model('pagination')->initialize(array(
			'base_url' => '/inbox/?act=index', 
			'total_rows' => $this->model('message')->count_user_message($this->user_id), 
			'per_page' => $this->per_page
		));
		
		TPL::assign("pagination", $this->model('pagination')->create_links());
		
		TPL::assign("list", $data);

		
		TPL::output("inbox/inbox_index");
	}
	
	
	
	
	/**
	 *  获取未读的私信
	 */
	function index_more_unread_all_ajax_action()
	{
		$uid = $this->user_id;
		
		
		$list= $this->model('message')->list_not_read_message($this->user_id);
		
		
		if (! empty($list['user_list']))
		{
				
			$users_info = $this->model('account')->get_users_by_uids($list['user_list']);
				
			//组合用户名
			foreach ($users_info as $user)
			{
				$users[$user['uid']] = $user['user_name'];
				$users_avatar_file[$user['uid']] = $user["avatar_file"];
			}
		}
		
		
		//组合消息标题
		if (! empty($list['title_list']))
		{
			foreach ($list['title_list'] as $title)
			{
				$titles[$title['dialog_id']] = array(
						"title" => $title['notice_content'],
						"type" => $title["notice_type"]
				);
			}
		}
		
		foreach ($list['content_list'] as $key => $value)
		{
			if (($value['sender_uid'] == $uid) && ($value["sender_count"] > 0)) //当前处于发送用户
			{
				//显示标题前内容
				$tmp['per_tile'] = "<a href='/people/?u=" . $value['recipient_uid'] . "'>" . $users[$value['recipient_uid']] . "</a>";
		
				$tmp["unread"] = false;
		
				//新消息条数
				if ($value["sender_unread"] > 0)
				{
					$tmp["unread"] = $value["sender_unread"];
					$tmp['btn_title'] = "有 " . $value["sender_unread"] . " 条新回复";
				}
				else
				{
					$tmp['btn_title'] = "共 " . $value["sender_count"] . " 条对话";
				}
		
				$tmp["avatar_file"] = $users_avatar_file[$value['recipient_uid']];
		
				$tmp["recipients_name"] = $users[$value['recipient_uid']];
		
				$tmp['uid'] = $value['recipient_uid'];
			}
			else if (($value['recipient_uid'] == $uid) && ($value["recipient_count"] > 0)) ////当前处于接收用户
			{
				//显示标题前内容
				$tmp['per_tile'] = "<a href='/people/?u=" . $value['sender_uid'] . "'>" . $users[$value['sender_uid']] . "</a>";
		
				$tmp["unread"] = false;
		
				//新消息条数
				if ($value["recipient_unread"] > 0)
				{
					$tmp["unread"] = $value["recipient_unread"];
					$tmp['btn_title'] = "有 " . $value["recipient_unread"] . " 条新回复";
				}
				else
				{
					$tmp['btn_title'] = "共 " . $value["recipient_count"] . " 条对话";
				}
		
				$tmp["avatar_file"] = $users_avatar_file[$value['sender_uid']];
		
				$tmp["recipients_name"] = $users[$value['sender_uid']];
		
				$tmp['uid'] = $value['sender_uid'];
			}
				
			//显示标题
			$tmp['title'] = $titles[$value['dialog_id']]["title"];
				
			$tmp["last_time"] = $value["last_time"];
				
			$tmp["dialog_id"] = $value['dialog_id'];
				
			$data[] = $tmp;
		}		
		
		
		TPL::assign("list", $data);
		
		TPL::output("inbox/inbox_index_more_ajax");
		//p($data);
		
		
	}

	public function delete_dialog_action()
	{
		$dialog_id = intval($this->_INPUT["dialog_id"]);
		
		if ($dialog_id == 0)
		{
			exit('参数传递错误');
		}
		
		$list = $this->model('message')->delete_dialog($dialog_id);
		
		if ($_SERVER['HTTP_REFERER'])
		{
			HTTP::redirect($_SERVER['HTTP_REFERER']);
		}
		else
		{
			HTTP::redirect('/inbox/');
		}
	}

	/**
	 * 
	 * 删除用户短信息
	 */
	public function delete_message_action()
	{
		$msg_id = intval($this->_INPUT["recipient_id"]);
		
		if ($msg_id == 0)
		{
			exit('参数传递错误');
		}
		
		$list = $this->model('message')->delete_message($msg_id);
		
		if ($_SERVER['HTTP_REFERER'])
		{
			HTTP::redirect($_SERVER['HTTP_REFERER']);
		}
		else
		{
			HTTP::redirect('/inbox/');
		}
	}

	/**
	 * 
	 * 阅读用户短信息
	 */
	public function read_message_action()
	{
		$dialog_id = intval($this->_INPUT["dialog_id"]);
		$u_id = $this->user_id;
		$data = array();
		$tmp = array();
		
		if ($dialog_id == 0)
		{
			exit('参数传递错误');
		}
		
		if ($this->_INPUT["page"] < 1)
		{
			$this->_INPUT["page"] = 1;
		}
		
		$limit = ($this->_INPUT["page"] - 1) * $this->per_page;
		$limit = intval($limit) . ", {$this->per_page}";
		
		$this->model('message')->read_message($dialog_id);
		
		$list = $this->model('message')->get_message_by_dialog_id($dialog_id, $limit);
		
		if (empty($list['list_one']))
		{
			HTTP::redirect("/inbox/");
		}
		
		if (! empty($list))
		{
			$account_class = $this->model('account'); //新建OBJ;
			

			if ($list['list_one'][0]['sender_uid'] != $u_id)
			{
				$rev_info = $account_class->get_users_by_uid($list['list_one'][0]['sender_uid']);
			}
			else
			{
				$rev_info = $account_class->get_users_by_uid($list['list_one'][0]['recipient_uid']);
			}
			
			$send_name = $rev_info['user_name'];
			
			foreach ($list['list'] as $key => $value)
			{
				$value["notice_content"] = FORMAT::parse_links($value["notice_content"]);
				
				if (($value["sender_uid"] == $u_id) && ($value["sender_del"] == 0)) //接收用户为本人，则删除不显示
				{
					$tmp = $value;
					
					if ($value['sender_uid'] != $u_id)
					{
						$tmp["user_name"] = $send_name;
						$tmp["avatar_file"] = $rev_info["avatar_file"];
					}
					else
					{
						$tmp["user_name"] = '我';
						$tmp["reply"] = '';
					}
				}
				else if (($value["recipient_uid"] == $u_id) && ($value["recipient_del"] == 0))
				{
					$tmp = $value;
					
					if ($value['sender_uid'] != $u_id)
					{
						$tmp["user_name"] = $send_name;
						
						$tmp["avatar_file"] = $rev_info["avatar_file"];
					}
					else
					{
						$tmp["user_name"] = '我';
						$tmp["reply"] = '';
					}
				}
				
				if (! $tmp["avatar_file"])
				{
					$tmp["avatar_file"] = G_STATIC_URL."/common/avatar-img.png";
				}
				
				$tmp["add_time"] = $value["add_time"];
				$tmp["dialog_id"] = $value['dialog_id'];
				
				$data[] = $tmp;
			}
		}
		
		$this->crumb('私信对话: ' . $send_name, '/inbox/?act=read_message&dialog_id=' . $dialog_id);
		
		$this->model('pagination')->initialize(array(
			'base_url' => '/inbox/?act=read_message&dialog_id=' . $dialog_id, 
			'total_rows' => $this->model('message')->count_message($dialog_id), 
			'per_page' => $this->per_page
		));
		
		TPL::assign("pagination", $this->model('pagination')->create_links());
		
		TPL::assign("list", $data);
		TPL::assign("send_name", $send_name);
		TPL::output("inbox/inbox_read_list", true);
	}

	/**
	 * 
	 * 书写短信息
	 */
	public function write_message_action()
	{
		if ($this->is_post())
		{
			$sender_uid = $this->user_id;
			$message = htmlspecialchars($this->_INPUT["message"]);
			$notice_type = (! isset($this->_INPUT["is_system"])) ? 0 : 11;
			$recipient = $this->_INPUT["recipient"];
			
			if (trim($message) == '')
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', "请输入私信内容"));
				exit();
			}
			
			$rev_info = $this->model('account')->get_users_by_username($recipient);
			
			if (empty($rev_info))
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', "接收信息的用户不存在"));
				exit();
			}
			else
			{
				$recipient_uid = $rev_info['uid'];
			}
			
			if ($recipient_uid == $sender_uid)
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', "不能给自己写信息哦"));
			}
			
			//判断是否设置为关注人发送信息
			if (! $this->model('message')->check_recv($rev_info['uid'], $sender_uid))
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', "对方设置了只有关注的人才能发送信息"));
			}
			
			$retval = $this->model('message')->send_message($sender_uid, $recipient_uid, null, $message, 0, 0);
			
			if ($retval)
			{
				ZCACHE::delete("inbox_unread_" . $recipient_uid);
				
				if ($this->_INPUT['click_id'])
				{
					$rsm = array(
						'click_id' => $this->_INPUT['click_id']
					);
				}
				
				H::ajax_json_output(GZ_APP::RSM($rsm, 1, "私信发送成功"));
			}
			else
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', "私信发送失败, 请重新发送"));
			}
		}
	}

}
