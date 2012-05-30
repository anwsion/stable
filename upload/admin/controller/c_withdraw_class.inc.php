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

class c_withdraw_class extends ADMIN_CONTROLLER
{

	public function setup()
	{
		$this->crumb("提现管理", "?c=withdraw");
	}

	/**
	 * 默认action
	 */
	public function index_action()
	{
		$this->verify_list_action();
	}

	/**
	 * 审核列表
	 */
	public function verify_list_action()
	{
		$per_page = 10;
		
		$page_id = $this->_INPUT['page'];
		
		$keyword = $this->_INPUT['keyword'];
		
		if (empty($page_id))
		{
			$page_id = 1;
		}
		
		$limit = ($page_id - 1) * $per_page . ", {$per_page}";
		
		if (! empty($keyword))
		{
			$list = $this->model('withdraw')->get_withdraw_log_list(false, 0, "withdraw_log_id LIKE '%{$keyword}%'", $limit, " add_time DESC");
			$totalnum = $this->model('withdraw')->get_withdraw_log_list(true, 0, "withdraw_log_id LIKE '%{$keyword}%'");
		}
		else
		{
			$list = $this->model('withdraw')->get_withdraw_log_list(false, 0, "", $limit, " add_time DESC");
			$totalnum = $this->model('withdraw')->get_withdraw_log_list(true, 0, "");
		}
		
		foreach ($list as $key => $val)
		{
			$list[$key]['user'] = $this->model('account')->get_users_by_uid($val['uid']);
		}
		
		$this->model('pagination')->initialize(array(
			'base_url' => '?c=withdraw&act=verify_list', 
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
		
		TPL::assign("list", $list);
		
		TPL::assign("keyword", $keyword);
		
		TPL::assign("pagination", $this->model('pagination')->create_links());
		
		TPL::output("admin/withdraw_verify_list", TRUE);
	}

	/**
	 * 审核处理
	 */
	public function verify_process_ajax_action()
	{
		$withdraw_id = intval($this->_INPUT['withdraw_id']);
		
		$status = $this->_INPUT['status'];
		
		if ($withdraw_id <= 0 || ! in_array($status, array(
			"1", 
			"-1"
		)))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "系统错误，请联系管理员。"));
		}
		
		$retval = $this->model('withdraw')->update_withdraw_log(array(
			'status' => $status
		), $withdraw_id);
		
		if ($retval)
		{
			if ($status == "1")
			{
				H::ajax_json_output(GZ_APP::RSM(null, "1", "审核成功。"));
			}
			else if ($status == "-1")
			{
				H::ajax_json_output(GZ_APP::RSM(null, "1", "取消申请成功。"));
			}
		}
		
		H::ajax_json_output(GZ_APP::RSM(null, "-1", "系统错误，请联系管理员。"));
	
	}

	/**
	 * 处理页面
	 */
	public function handle_list_action()
	{
		$per_page = 10;
		
		$page_id = $this->_INPUT['page'];
		
		$keyword = $this->_INPUT['keyword'];
		
		if (empty($page_id))
		{
			$page_id = 1;
		}
		
		$limit = ($page_id - 1) * $per_page . ", {$per_page}";
		
		if (! empty($keyword))
		{
			$list = $this->model('withdraw')->get_withdraw_log_list(false, 0, "status IN (1, 2, -2) AND withdraw_log_id LIKE '%{$keyword}%'", $limit, " add_time DESC");
			
			$totalnum = $this->model('withdraw')->get_withdraw_log_list(true, 0, "status IN (1, 2, -2) AND withdraw_log_id LIKE '%{$keyword}%'");
		}
		else
		{
			$list = $this->model('withdraw')->get_withdraw_log_list(false, 0, "status IN (1, 2, -2)", $limit, " add_time DESC");
			
			$totalnum = $this->model('withdraw')->get_withdraw_log_list(true, 0, "status IN (1, 2, -2)");
		}
		
		foreach ($list as $key => $val)
		{
			$list[$key]['user'] = $this->model('account')->get_users_by_uid($val['uid']);
		}
		
		$this->model('pagination')->initialize(array(
			'base_url' => '?c=withdraw&act=verify_list', 
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
		
		TPL::assign("keyword", $keyword);
		
		TPL::assign("list", $list);
		
		TPL::assign("pagination", $this->model('pagination')->create_links());
		
		TPL::output("admin/withdraw_handle_list", TRUE);
	}

	/**
	 * 提现处理
	 */
	public function handle_process_ajax_action()
	{
		$withdraw_id = intval($this->_INPUT['withdraw_id']);
		
		$status = $this->_INPUT['status'];
		
		if ($withdraw_id <= 0 || ! in_array($status, array(
			"2", 
			"-2"
		)))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "系统错误，请联系管理员。"));
		}
		
		$withdraw_info = $this->model('withdraw')->get_withdraw_log_list(false, 0, "withdraw_log_id={$withdraw_id}", 1);
		
		$retval = $this->model('withdraw')->update_withdraw_log(array(
			'status' => $status
		), $withdraw_id);
		
		if ($retval)
		{
			$this->model('account')->update_withdraw_credit_all($withdraw_info[0]['uid']);
			
			if ($status == "2")
			{
				H::ajax_json_output(GZ_APP::RSM(null, "1", "提现处理成功。"));
			}
			else if ($status == "-2")
			{
				H::ajax_json_output(GZ_APP::RSM(null, "1", "取消申请成功。"));
			}
		}
		
		H::ajax_json_output(GZ_APP::RSM(null, "-1", "系统错误，请联系管理员。"));
	
	}
}