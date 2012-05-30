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

class credit_class extends GZ_MODEL
{
	var $today_time;

	public function setup()
	{
		$this->today_time = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
	}

	/**
	 * 添加金币
	 * @param  $action
	 * @param  $uid
	 * @param  $note
	 * @param  $today_limit
	 * @param  $custom_credit
	 */
	public function set_user_credit($action, $uid, $note = '', $today_limit = false, $custom_credit = null, $delay = 0)
	{
		$user = $this->model('account')->get_users($uid);
		
		$credit = GZ_APP::config()->get('credit')->credit_rule[$action];
		
		if ($action == 'CUSTOM' and $custom_credit) //使用自定义动作和自定义金币
		{
			$credit = $custom_credit;
		}
		else if ($credit === 0) //使用预定义动作和自定义金币
		{
			$credit = $custom_credit;
		}
		
		if (! $action || ! $credit)
		{
			return false;
		}
		
		return $this->set_user_credit_data($action, $uid, $credit, $note, $today_limit, $delay);
	}

	/**
	 * 判断用户金币是否足够
	 * @param  $uid
	 * @param  $credit
	 */
	public function check_user_credit($uid, $credit)
	{
		if (! $user = $this->model('account')->get_users($uid))
		{
			return false;
		}
		
		if (((int)$user['credit'] + (int)$credit) < 0)
		{
			return false;
		}
		
		return true;
	}

	/**
	 * 设置金币
	 * @param  $uid
	 * @param  $credit
	 * @param  $note
	 * @param  $today_limit
	 */
	public function set_user_credit_data($action, $uid, $credit, $note = '', $today_limit = false, $delay)
	{
		$credit = $credit * 1;
		
		if (! $user = $this->model('account')->get_users($uid))
		{
			return false;
		}
		
		$new_credit = (int)$user['credit'] + (int)$credit;
		
		if ($new_credit < 0)
		{
			return false;
		}
		
		if ($today_limit)
		{
			$today_credit = $this->get_today_credit($uid);
			
			if ((int)$today_credit >= GZ_APP::config()->get('credit')->credit_perday_limit) //金币小于限制就可以添加
			{
				return false;
			}
			
			$this->update_today_credit($uid, $today_credit + $credit, $this->today_time);
		}
		
		if ($delay == 0) //不延迟
		{
			$this->model('account')->update_users_fields(array(
				'credit' => $new_credit
			), $uid);
		}
		
		return $this->add_credit_log($action, $uid, $credit, $note, $new_credit, $delay);
	
	}

	/**
	 * get today credit
	 * @param  $uid
	 */
	public function get_today_credit($uid)
	{
		$uid = $uid * 1;
		
		$user = $this->model('account')->get_users($uid);
		
		if (! $user)
		{
			return false;
		}
		
		$rs = $this->fetch_row('user_credit_day', "uid = '{$uid}' AND today_time='{$this->today_time}'");
		
		//添加记录
		if (! $rs)
		{
			$this->add_today_credit($uid);
			
			return 0;
		}
		
		//取出记录
		return $rs["credit"];
	
	}

	/**
	 * 获取流水记录
	 * @param  $uid
	 * @param  $where
	 * @param  $limit
	 * @param  $orderby
	 */
	public function get_credit_log($uid, $where, $limit, $orderby = "add_time DESC")
	{
		$uid = $uid * 1;
		
		if ($uid != 0)
		{
			if (trim($where) != '')
			{
				$where = "uid = {$uid} AND " . $where;
			}
			else
			{
				$where = 'uid = ' . $uid;
			}
		}
		
		return $this->fetch_all('user_credit_log', $where, $orderby, $limit);
	}

	/**
	 * 累加SUM 金币值
	 * @param  $uid
	 * @param  $where
	 */
	public function get_credit_log_sum_credit($uid, $where)
	{
		$uid = $uid * 1;
		
		if (! $uid)
		{
			return false;
		}
		else
		{
			if (trim($where) != '')
			{
				$where = "uid = {$uid} AND " . $where;
			}
			else
			{
				$where = "uid = {$uid}";
			}
		}
		
		return $this->sum('user_credit_log', 'credit', $where);
	}

	/**
	 * add default credit
	 * @param  $uid
	 */
	public function add_today_credit($uid, $credit = 0)
	{
		
		$insert_arr["uid"] = $uid * 1;
		$insert_arr["today_time"] = $this->today_time;
		$insert_arr["credit"] = $credit * 1;
		
		return $this->insert('user_credit_day', $insert_arr);
	}

	/**
	 * update today credit
	 * @param  $uid
	 * @param  $credit
	 * @param  $today_time
	 */
	public function update_today_credit($uid, $credit = 0, $today_time = "")
	{
		if ((! $uid) || (! $credit) || (! $today_time))
		{
			return false;
		}
		
		$update_arr["credit"] = $credit * 1;
		
		return $this->update('user_credit_day', $update_arr, "uid = {$uid} AND today_time='{$today_time}'");
	}

	/**
	 * add log
	 * @param  $uid
	 * @param  $credit
	 * @param  $note
	 * @param  $new_credit
	 */
	public function add_credit_log($action, $uid, $credit = 0, $note = "", $new_credit = 0, $delay = 0)
	{
		
		$insert_arr["action"] = trim($action);
		$insert_arr["uid"] = $uid * 1;
		$insert_arr["credit"] = $credit * 1;
		$insert_arr["note"] = $note;
		$insert_arr["new_credit"] = $new_credit * 1;
		$insert_arr["add_time"] = mktime();
		$insert_arr["client_ip"] = ip2long(real_ip());
		
		if ($delay == 0)
		{
			$insert_arr["process"] = 1;
			$insert_arr["process_time"] = mktime();
		
		}
		
		return $this->insert('user_credit_log', $insert_arr);
	}

}