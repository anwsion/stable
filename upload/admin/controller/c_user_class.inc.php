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

class c_user_class extends ADMIN_CONTROLLER
{
	public function setup()
	{
		$this->crumb("会员管理", "?c=user");
	}

	public function index_action()
	{
		$this->list_v2_action();
	}

	public function list_v2_action()
	{
		$per_page = 15;
		
		$page_id = $this->_INPUT['page'];
		
		if (empty($page_id))
		{
			$page_id = 1;
		}
		
		$limit = ($page_id - 1) * $per_page . "," . $per_page;
		
		$keyword = $this->_INPUT['keyword'];
		
		$m_keyword = mysql_escape_string($keyword);
		
		if (! empty($keyword))
		{
			$user_list = $this->model('user_manage')->get_user_list(false, "user_name LIKE '%" . $m_keyword . "%' OR email LIKE '%" . $m_keyword . "%'", $limit);
			$totalnum = $this->model('user_manage')->get_user_list(true, "user_name LIKE '%" . $m_keyword . "%' OR email LIKE '%" . $m_keyword . "%'");
			$keyword_url .= "&keyword=" . $keyword;
		}
		else
		{
			$user_list = $this->model('user_manage')->get_user_list(false, "", $limit);
			$totalnum = $this->model('user_manage')->get_user_list(true);
		}
		
		$this->model('pagination')->initialize(array(
			'base_url' => '?c=user&act=list_v2' . $keyword_url, 
			'total_rows' => $totalnum, 
			'per_page' => $per_page, 
			'last_link' => '末页', 
			'first_link' => '首页', 
			'next_link' => '下一页 »', 
			'prev_link' => '« 上一页', 
			'anchor_class' => ' class="number"', 
			'cur_tag_open' => '<a class="number current">', 
			'cur_tag_close' => '</a>'
		));
		
		TPL::assign('pagination', $this->model('pagination')->create_links());
		TPL::assign('user_groups', $this->model('setting')->get_select_config('user_group'));
		TPL::assign('keyword', $keyword);
		TPL::assign('list', $user_list);
		TPL::output('admin/user_list_v2', true);
	}

	/**
	 * 修改用户资料
	 * Enter description here ...
	 */
	public function edit_action()
	{
		$user_id = $this->_INPUT['uid'];
		
		$user_info = $this->model('account')->get_users_by_uid($user_id);
		
		$user_info['reg_time'] = date("Y-m-d H:i:s", $user_info['reg_time']);
		
		if($user_info['birthday'])
		{
			$user_info['birthday'] = date("Y-m-d H:i:s", $user_info['birthday']);
		}
		
		$user_info['reg_ip'] = long2ip($user_info['reg_ip']);
		
		if ($user_info['last_login'])
		{
			$user_info['last_login'] = date("Y-m-d H:i:s", $user_info['last_login']);
		}
		
		if ($user_info['last_ip'])
		{
			$user_info['last_ip'] = long2ip($user_info['last_ip']);
		}
		
		$this->crumb("修改用户", "?c=user&act=edit&uid=" . $user_id);
		
		TPL::assign('user_groups', $this->model('setting')->get_select_config('user_group'));
		TPL::assign('user', $user_info);
		TPL::output("admin/user_edit_v2", true);
	}

	/**
	 * 用户修改处理
	 * Enter description here ...
	 */
	public function user_edit_ajax_action()
	{
		$user_id = intval($this->_INPUT['uid']);

		if($user_id == 0)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "系统错误"));
		}
		
		$update_user = $this->model('user_manage')->user_field_filter($this->_INPUT);
		
		if (! empty($update_user['password']))
		{
			$this->model('account')->update_user_password_ingore_oldpassword($update_user['password'], $user_id, fetch_salt(4));
		}
		
		if($this->admin_info['group_id'] != '1')
		{
			unset($update_user['group_id']);
		}
		
		if($this->_INPUT['delete_avatar'])
		{
			$this->model('account')->delete_avatar($user_id);
		}
		
		unset($update_user['uid']);
		unset($update_user['password']);
		
		if($update_user['reg_time'])
		{
			$update_user['reg_time'] = strtotime($update_user['reg_time']);
		}
		
		if($update_user['last_login'])
		{
			$update_user['last_login'] = strtotime($update_user['last_login']);
		}
		
		if($update_user['birthday'])
		{
			$update_user['birthday'] = strtotime($update_user['birthday']);
		}
		
		if($update_user['reg_ip'])
		{
			$update_user['reg_ip'] = ip2long($update_user['reg_ip']);
		}
		
		if($update_user['last_ip'])
		{
			$update_user['last_ip'] = ip2long($update_user['last_ip']);
		}
		
		$this->model('account')->update_users_fields($update_user, $user_id);
		
		H::ajax_json_output(GZ_APP::RSM(null, "1", "修改成功"));
	}

	/*
	public function add_action()
	{
		TPL::assign('act', "add_save_ajax");
		TPL::output("admin/user_add", true);
	}

	public function add_save_ajax_action()
	{
		$username = $this->_INPUT['username'];
		$password = $this->_INPUT['password'];
		$email = $this->_INPUT['email'];
		
		if ($this->model('account')->check_username($username))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => ""
			), "-1", "用户名已存在"));
		}
		
		if (! H::isemail($email))
		{
			$email = $username . "#SNS";
		}
		
		$re = $this->model('account')->user_add($username, $password, $email);
		
		if ($re)
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => '?c=user&act=list'
			), "1", ""));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(array(), "-1", "添加失败，请联系管理员"));
		}
	}
	*/

	/**
	 * 设置会员状态
	 * Enter description here ...
	 */
	public function forbidden_status_ajax_action()
	{
		$user_id = $this->_INPUT['user_id'];
		
		$status = $this->_INPUT['status'];
		
		if (! in_array($status, array(
			'0', 
			'1'
		)))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "系统错误"));
		}
		
		$retval = $this->model('account')->update_users_fields(array(
			'forbidden' => $status
		), $user_id);
		
		if ($retval)
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => '?c=user&act=list'
			), "1", ""));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(array(), "-1", "添加失败，请联系管理员"));
		}
	}

	/**
	 * 审核新会员
	 * Enter description here ...
	 */
	public function review_apply_v2_action()
	{
		$per_page = 50;
		
		$page_id = $this->_INPUT['page'];
		
		if (empty($page_id))
		{
			$page_id = 1;
		}
		
		$limit = ($page_id - 1) * $per_page . "," . $per_page;
		
		$page_url = "?c=user&act=review_apply";
		
		$keyword = $this->_INPUT['keyword'];
		
		if ($keyword != "")
		{
			$list = $this->model('user_manage')->users_apply_list(false, "status='0' AND (user_name LIKE '%" . $keyword . "%' OR email LIKE '%" . $keyword . "%')", $limit);
			$totalnum = $this->model('user_manage')->users_apply_list(true, "status='0' AND (user_name LIKE '%" . $keyword . "%' OR email LIKE '%" . $keyword . "%')");
			$keyword_url .= "&keyword=" . $keyword;
		}
		else
		{
			$list = $this->model('user_manage')->users_apply_list(false, "status='0'", $limit);
			$totalnum = $this->model('user_manage')->users_apply_list(true, "status='0'");
		}
		
		$list = $this->review_apply_list_process($list);
		
		$this->model('pagination')->initialize(array(
			'base_url' => '?c=user&act=review_apply_v2' . $keyword_url, 
			'total_rows' => $totalnum, 
			'per_page' => $per_page, 
			'last_link' => '末页', 
			'first_link' => '首页', 
			'next_link' => '下一页 »', 
			'prev_link' => '« 上一页', 
			'anchor_class' => ' class="number"', 
			'cur_tag_open' => '<a class="number current">', 
			'cur_tag_close' => '</a>'
		));
		
		TPL::assign('pagination', $this->model('pagination')->create_links());
		
		TPL::assign('list', $list);
		TPL::assign('keyword', $keyword);
		TPL::output('admin/user_apply_v2', true);
	}

	/**
	 * 获取邀请注册链接
	 * Enter description here ...
	 */
	public function load_email_content_ajax_action()
	{
		$invitation = $this->model('invitation')->get_invitation_by_id($this->_INPUT['invitation_id']);
		
		$url = get_setting('base_url') . "/account/?c=register&act=step1&email=" . urlencode($invitation['invitation_email']) . "&icode=" . $invitation['invitation_code'];
		
		TPL::assign('url', $url);
		
		TPL::output('admin/user_apply_email_load', TRUE);
	}

	/**
	 * 新会员审核列表格式化
	 * Enter description here ...
	 * @param unknown_type $list
	 */
	function review_apply_list_process($list)
	{
		$invitation_ids = array();
		
		foreach ($list as $key => $val)
		{
			if ($val['invitation_id'] > 0)
				$invitation_ids[] = $val['invitation_id'];
		}
		
		if (empty($invitation_ids))
		{
			return $list;
		}
		
		$invitation_list = $this->model('invitation')->get_invitation_list_by_ids($invitation_ids);
		
		if (empty($invitation_list))
		{
			return $list;
		}
		
		foreach ($list as $key => $val)
		{
			if (intval($val['invitation_id']) > 0)
			{
				$action_uid = $invitation_list[$val['invitation_id']]['active_uid'];
				
				$list[$key]['active_uid'] = $action_uid;
			}
		}
		
		return $list;
	}

	/**
	 * 通过审核处理
	 * Enter description here ...
	 */
	public function review_apply_ajax_action()
	{
		$apply_id = $this->_INPUT['apply_id'];
		
		$apply = $this->model('user_manage')->get_user_apply($apply_id);
		
		$email = $apply['email'];
		
		$user_name = $apply['user_name'];
		
		//重发邀请
		if ($apply['invitation_id'])
		{
			$invitation_row = $this->model('invitation')->get_invitation_by_id($apply['invitation_id']);
			
			if ($invitation_row['active_status'] == '1')
			{
				H::ajax_json_output(GZ_APP::RSM(null, "-2", "用户已注册激活，无需重发邀请。"));
			}
			
			$this->model('email')->apply_pass($email, $user_name, $invitation_row['invitation_code']);
			
			H::ajax_json_output(GZ_APP::RSM(null, "1", "重发邀请成功。"));
		}
		
		$invitation = $this->model('invitation');
		
		$invitation_code = $invitation->get_unique_invitation_code("system");
		
		$invitation_id = $invitation->add_invitation(0, $invitation_code, $email, time(), ip2long($_SERVER['REMOTE_ADDR']));
		
		if ($invitation_id)
		{
			$this->model('email')->apply_pass($email, $user_name, $invitation_code);
			
			$this->model('user_manage')->set_user_apply_field($apply_id, array(
				'passed' => "1", 
				'invitation_id' => $invitation_id
			));
			
			H::ajax_json_output(GZ_APP::RSM(null, "1", "发送成功。"));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "发送失败。"));
		}
	
	}

	/**
	 * 忽略帐号申请
	 * Enter description here ...
	 */
	public function ignore_apply_ajax_action()
	{
		$apply_id = $this->_INPUT['apply_id'];
		
		$retval = $this->model('user_manage')->set_user_apply_field($apply_id, array(
			'status' => 1
		));
		
		if ($retval)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "1", ""));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "操作失败。"));
		}
	}

	/**
	 * 列出在线列表
	 * Enter description here ...
	 */
	public function online_list_action()
	{
		$per_page = 20;
		
		$page = $this->_INPUT['page'];
		
		if (intval($page) <= 0)
		{
			$page = 1;
		}
		
		$limit = ($page - 1) * $per_page . "," . $per_page;
		
		$r_time = time() - (get_setting('online_interval') * 60);
		
		$online_users = $this->model('online')->get_db_online_users(false, "last_active >= " . $r_time, $limit);
		
		$total_count = $this->model('online')->get_db_online_users(true, "last_active >= " . $r_time);
		
		$online_users = $this->online_users_format($online_users);
		
		$this->model('pagination')->initialize(array(
			'base_url' => '?c=user&act=online_list', 
			'total_rows' => $total_count, 
			'per_page' => $per_page, 
			'last_link' => '末页', 
			'first_link' => '首页', 
			'next_link' => '下一页 »', 
			'prev_link' => '« 上一页', 
			'anchor_class' => ' class="number"', 
			'cur_tag_open' => '<a class="number current">', 
			'cur_tag_close' => '</a>'
		));
		
		TPL::assign('pagination', $this->model('pagination')->create_links());
		
		TPL::assign('list', $online_users);
		
		TPL::assign('total_count', $total_count);
		
		TPL::output('admin/user_online', true);
	}

	/**
	 * 在线会员列表格式化
	 * Enter description here ...
	 * @param unknown_type $online_users
	 */
	public function online_users_format($online_users)
	{
		foreach ($online_users as $user)
		{
			$uids[] = $user['uid'];
		}
		
		if (empty($uids))
		{
			return $online_users;
		}
		
		$uids = array_unique($uids);
		
		$_user_infos = $this->model('account')->get_users_by_uids($uids);
		
		$user_infos = array();
		
		foreach ($_user_infos as $user)
		{
			$user_infos[$user['uid']] = $user;
		}
		
		foreach ($online_users as $key => $val)
		{
			$online_users[$key]['userinfo'] = $user_infos[$val['uid']];
		}
		
		return $online_users;
	}

}