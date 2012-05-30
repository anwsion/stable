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
	var $per_page = 10;
	
	var $email_interval = 10; //重发邮件时间间隔，单位:分钟

	public function get_access_rule()
	{
		$rule_action["rule_type"] = "white"; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["actions"] = array();
		return $rule_action;
	}

	function setup()
	{
	
	}

	/**
	 * 默认动作重定向
	 * 
	 */
	function index_action()
	{
		$this->invitation_register_action();
	}

	/**
	 * 邀请首页
	 * 
	 */
	function invitation_register_action()
	{
		$this->crumb('邀请好友', '/invitation');
		
		TPL::import_css(array(
			'css/discussion.css'
		));
		
		TPL::assign('recommend_questions', $this->model('question')->get_user_recommend_v2($this->user_id, 10));
		TPL::assign('recommend_users', $this->model('account')->get_user_recommend_v2($this->user_id, 5));
		TPL::assign('recommend_topics', $this->model('topic')->get_user_recommend_v2($this->user_id, 10));
		
		//边栏菜单
		if (TPL::is_output('global/__account_slidebar_v2.tpl.htm','invitation/invitation_register' ))
		{
			TPL::assign('draft_count', $this->model('draft')->get_draft_count('answer', $this->user_id));
			TPL::assign('question_invite_count', $this->model('question')->get_invite_question_list($this->user_id, '', true));
		}
		
		TPL::output("invitation/invitation_register");
	}

	/**
	 * 列出受邀请的列表
	 */
	function invitation_register_more_ajax_action()
	{
		$page = $this->_INPUT['page'];
		
		$limit = $this->_INPUT["page"] * 1 * $this->per_page;
		
		$limit = $limit . ", {$this->per_page}";
		
		$invitation = $this->model('invitation');
		
		$invitation_list = $invitation->get_invitation_list($this->user_id, $limit);
		
		$invitation_list = $this->format_invitation_list($invitation_list);
		
		TPL::assign("invitation_list", $invitation_list);
		
		TPL::output("invitation/invitation_register_more_ajax");
	}

	/**
	 * 格式化输出列表
	 * Enter description here ...
	 * @param unknown_type $invitation_list
	 */
	function format_invitation_list($invitation_list)
	{
		if (empty($invitation_list))
		{
			return false;
		}
		
		$user_ids = array();
		
		foreach ($invitation_list as $key => $val)
		{
			if ($val['active_status'] == '1')
			{
				$user_ids[] = $val['active_uid'];
			}
		}
		
		$user_ids = array_unique($user_ids);
		
		if (empty($user_ids))
		{
			return $invitation_list;
		}
		
		$account_class = $this->model('account');
		
		$user_info_tmp = $account_class->get_users_by_uids($user_ids);
		
		if (empty($user_info_tmp))
		{
			return $invitation_list;
		}
		
		$users_infos = array();
		
		foreach ($user_info_tmp as $key => $val)
		{
			$user_data['user_name'] = $val['user_name'];
			$user_data['avatar_file'] = get_avatar_url($val['uid'], "mid");
			$user_data['url'] = $account_class->get_url_by_uid($val['uid']);
			$users_infos[$val['uid']] = $user_data;
		}
		
		foreach ($invitation_list as $key => $val)
		{
			if ($val['active_status'] == '1')
			{
				$invitation_list[$key]['userinfo'] = $users_infos[$val['active_uid']];
			}
		}
		
		return $invitation_list;
	}

	/**
	* 邀请首页
	*
	*/
	function invitation_weibo_action()
	{
		$show_row = $this->model('sina_weibo')->get_users_sina_by_uid($this->user_id);
		
		if (! $show_row)
		{
			TPL::assign("show_sina_weibo_btn", 1);
		}
		
		TPL::assign("users", $this->model('account')->get_users($this->user_id));
		
		TPL::output("invitation/invitation_weibo", true);
	}
	
	//执行邀请动作
	function invite_friend_ajax_action()
	{
		$email = trim($this->_INPUT['email']);
		
		if (empty($email))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => ''
			), "-1", "请填写邮箱。"));
		}
		
		if (! H::isemail($email))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => ''
			), "-1", "请填写正确的邮箱。"));
		}
		
		//判断当前用户是否还有邀请额
		if (! $this->model('account')->get_invitation_available($this->user_id))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => '/invitation/?act=invitation_register'
			), "-1", "已经没有可使用的邀请名额。"));
		}
		
		//搜索邮箱是否已为本站用户，存在则提示用户已存在，返回显示。
		

		if ($this->model('account')->check_email($email))
		{
			$user_info = $this->model('account')->get_users_by_email($email);
			
			$userinfo['uid'] = $user_info['uid'];
			
			$userinfo['real_name'] = $this->model('account')->get_real_name_by_uid($user_info['uid']);
			
			if ($user_info['uid'] == $this->user_id)
			{
				H::ajax_json_output(GZ_APP::RSM(array(
					'url' => ''
				), "-3", "不能邀请您自己."));
			}
			
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => '', 
				'userinfo' => $userinfo
			), "-2", "此邮箱已在本站注册帐号。"));
		}
		
		//若再次填入已邀请过的邮箱，则再发送一次邀请邮件
		$invitation_info = $this->model('invitation')->get_invitation_by_email($email);
		
		if ($invitation_info)
		{
			if ($invitation_info['uid'] == $this->user_id)
			{
				$this->resend_invitation($invitation_info['invitation_id']);
			}
			else
			{
				H::ajax_json_output(GZ_APP::RSM(array(
					'url' => '', 
					'userinfo' => $userinfo
				), "-2", "此邮箱已接收过本站发出的邀请。"));
			}
		}
		
		//生成邀请码
		$invitation_code = $this->model('invitation')->get_unique_invitation_code($this->active_type);
		
		$invitation_id = $this->model('invitation')->add_invitation($this->user_id, $invitation_code, $email, time(), ip2long($_SERVER['REMOTE_ADDR']));
		
		//发送邮件
		if ($invitation_id)
		{
			$this->model('account')->edit_invitation_available($this->user_id, - 1);
			
			$this->model('email')->invitation($this->user_id, $email, $invitation_code);
			
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => ''
			), "1", "邀请发送成功。"));
		}
	}

	/**
	 * 重发邀请
	 */
	function invite_resend_ajax_action()
	{
		$invitation_id = $this->_INPUT['invitation_id'];
		
		$this->resend_invitation($invitation_id);
	}

	function resend_invitation($invitation_id)
	{
		$invitation = $this->model('invitation');
		
		$email_class = $this->model('email');
		
		$invitation_row = $invitation->get_invitation_by_id($invitation_id);
		
		if ($invitation_row['active_status'] == '1')
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-2", "用户已注册激活，无需重发邀请。"));
		}
		
		//已取消的记录重置状态
		if ($invitation_row['active_status'] == '-1')
		{
			$invitation->update_invitation_fields(array(
				'active_status' => '0'
			), $invitation_id);
		}
		
		if ((time() - $email_class->get_last_resend_time($invitation_row['invitation_email'])) <= ($this->email_interval * 60))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => ''
			), "-2", "请超过{$this->email_interval}分钟后再重发邀请。"));
		}
		
		$email_class->invitation($this->user_id, $invitation_row['invitation_email'], $invitation_row['invitation_code']);
		
		H::ajax_json_output(GZ_APP::RSM(array(
			'url' => ''
		), "-2", "重发邀请成功。"));
	}

	/**
	 * 取消邀请
	 * Enter description here ...
	 */
	function invite_del_ajax_action()
	{
		$invitation_id = $this->_INPUT['invitation_id'] * 1;
		
		if (! $invitation_id)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "操作失败!"));
		}
		
		$invitation = $this->model('invitation');
		
		$account_class = $this->model('account');
		
		$invitation_row = $invitation->get_invitation_by_id($invitation_id);
		
		if (! $invitation_row)
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => ''
			), "-1", "操作失败!"));
		}
		
		//更新状态
		$invitation->update_invitation_fields(array(
			"active_status" => "-1"
		), $invitation_id);
		
		if ($account_class->edit_invitation_available($this->user_id, 1))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => ''
			), "1", "操作成功!"));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => ''
			), "1", "操作失败!"));
			exit();
		}
	
	}

}