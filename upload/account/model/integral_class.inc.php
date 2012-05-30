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

class integral_class extends GZ_MODEL
{
	
	var $today_time;
	
	//构造函数
	public function setup()
	{
		$this->today_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
	}

	/**
	 * 添加积分
	 * @param  $action	为CUSTOM时必须设置$custom_integral
	 * @param  $uid
	 * @param  $note
	 * @param  $today_limit
	 * @param  $custom_integral
	 */
	public function set_user_integral($action, $uid, $note = '', $category_id = 0, $today_limit = false, $custom_integral = null)
	{
		
		$user = $this->model('account_class')->get_users_by_uid($uid);
		
		$integral = GZ_APP::config()->get('integral')->integral_rule[$action];
		
		if ($action == 'CUSTOM' and $custom_integral)
		{
			$integral = $custom_integral;
		}
		else if (! $action || ! $integral)
		{
			return false;
		}
		
		//判断是不是可设置
		

		if ((isset(GZ_APP::config()->get('integral')->integral_rule_max[$action])) && ((GZ_APP::config()->get('integral')->integral_rule_max[$action]) > 0))
		{
			
			$integral_set_count = $this->get_integral_log_count($action, $uid);
			
			if ($integral_set_count >= GZ_APP::config()->get('integral')->integral_rule_max[$action])
			{
				return false;
			}
		
		}
		
		return $this->set_user_integral_data($action, $uid, $integral, $note, $category_id, $today_limit);
	}

	/**
	 * 设置积分
	 * 
	 * @param  $action
	 * @param  $uid
	 * @param  $integral
	 * @param  $note
	 * @param  $today_limit
	 */
	public function set_user_integral_data($action, $uid, $integral, $note = '', $category_id, $today_limit = false)
	{
		$integral = $integral * 1;
		
		$user = $this->model('account_class')->get_users_by_uid($uid);
		
		if (! $user)
		{
			return false;
		}
		
		$new_integral = (int)$user['integral'] + (int)$integral;
		
		if ($new_integral < 0)
		{
			return false;
		}
		
		$today_integral = $this->get_today_integral($uid); //今天的积分总数
		

		if ($today_limit) //使用每天积分限制
		{
			if ((int)$today_integral >= GZ_APP::config()->get('integral')->integral_perday_limit) //积分小于限制就可以添加
			{
				return false;
			}
			
			$this->update_today_integral($uid, $today_integral + $integral, $this->today_time);
		}
		else
		{
			$this->update_today_integral($uid, $today_integral + $integral, $this->today_time);
		}
		
		$this->model('account_class')->update_users_fields(array(
			'integral' => $new_integral
		), $uid);
		
		$integral_log_id = $this->add_integral_log($action, $uid, $integral, $note, $category_id, $new_integral);
		
		return $integral_log_id;
	}

	/**
	 * get today integral
	 * @param  $uid
	 */
	public function get_today_integral($uid)
	{
		$uid = $uid * 1;
		
		$user = $this->model('account_class')->get_users_by_uid($uid);
		
		if (! $user)
		{
			return false;
		}
		
		$rs = $this->fetch_row('user_integral_day', "uid = {$uid} AND today_time = '{$this->today_time}'");
		
		//添加记录
		if (! $rs)
		{
			$this->add_today_integral($uid);
			
			return 0;
		}
		
		//取出记录
		return $rs["integral"];
	
	}

	/**
	 * 获取日志记录
	 * @param  $action
	 * @param  $uid
	 * @param  $limit
	 */
	public function get_integral_log($action, $uid, $limit = 1)
	{
		$action = trim($action);
		
		$uid = $uid * 1;
		
		if (! $uid)
		{
			return false;
		}
		
		$where = "uid = {$uid}";
		
		if ($action != "")
		{
			$where .= " AND action='{$action}'";
		}
		
		return $this->fetch_all('user_integral_log', $where, 'add_time DESC', $limit);
	}

	/**
	 * 获取日志记录计数
	 * 
	 * @param  $action
	 * @param  $uid
	 */
	public function get_integral_log_count($action, $uid)
	{
		$action = trim($action);
		$uid = $uid * 1;
		
		if ((! $action) || (! $uid))
		{
			return false;
		}
		
		return $this->count('user_integral_log', "uid = '{$uid}' AND action='{$action}'");
	}

	/**
	 * add default integral
	 * @param  $uid
	 */
	public function add_today_integral($uid, $integral = 0)
	{
		
		$insert_arr["uid"] = $uid * 1;
		$insert_arr["today_time"] = $this->today_time;
		$insert_arr["integral"] = $integral * 1;
		
		return $this->insert('user_integral_day', $insert_arr);
	}

	/**
	 * update today integral
	 * @param  $uid
	 * @param  $integral
	 * @param  $today_time
	 */
	public function update_today_integral($uid, $integral = 0, $today_time = "")
	{
		
		$update_arr["integral"] = $integral * 1;
		
		if ((! $uid) || (! $integral) || (! $today_time))
		{
			return false;
		}
		
		return $this->update('user_integral_day', $update_arr, "uid={$uid} AND today_time='{$today_time}'");
	}

	/**
	 * add log
	 * @param  $uid
	 * @param  $integral
	 * @param  $note
	 * @param  $new_integral
	 */
	public function add_integral_log($action, $uid, $integral = 0, $note = "", $category_id = 0, $new_integral = 0)
	{
		
		$insert_arr["action"] = trim($action);
		$insert_arr["uid"] = $uid * 1;
		$insert_arr["integral"] = $integral * 1;
		$insert_arr["note"] = $note;
		$insert_arr["category_id"] = $category_id;
		$insert_arr["new_integral"] = $new_integral * 1;
		$insert_arr["add_time"] = mktime();
		$insert_arr["client_ip"] = ip2long(real_ip());
		
		return $this->insert('user_integral_log', $insert_arr);
	}

	/**
	 * 根据积分日志id获得一条日志记录
	 * @param $integral_log_id
	 */
	public function get_integral_log_by_id($integral_log_id)
	{
		$integral_log_id = intval($integral_log_id);
		
		if ($integral_log_id <= 0)
		{
			return false;
		}
		
		return $this->fetch_row('user_integral_log', "integral_log_id = {$integral_log_id}");
	}

	/**
	 * 根据时间列出最近获得最多积分的用户列表
	 * @param  $count
	 * @param  $time
	 * @param  $limit
	 */
	public function get_integral_sum_by_uid_time($count = false, $_time = "", $limit = "0,10")
	{
		if (is_numeric($_time))
		{
			$time = $_time;
		}
		else
		{
			if (! empty($_time))
			{
				$time_arr = explode(" ", $_time);
				
				if (empty($time_arr[1]))
				{
					$time_arr[0] = 1;
					$time_arr[1] = $_time;
				}
				
				$t_num = intval($time_arr[0]) - 1;
				
				switch ($time_arr[1])
				{
					case "year" :
						$time = strtotime(date("Y", strtotime("-{$t_num} year")) . "-01-01");
						break;
					case "month" :
						$time = strtotime(date("Y-m", strtotime("-{$t_num} month")) . "-01");
						break;
					case "week" :
						$time = strtotime("last Sunday", strtotime(date("Y-m-d", strtotime("-{$t_num} week"))));
						break;
					case "day" :
						$time = strtotime(date("Y-m-d", strtotime("-{$t_num} day")));
						break;
					case "hour" :
						$time = strtotime(date("Y-m-d H:00:00", strtotime("-{$t_num} hour")));
						break;
					default :
						$time = strtotime("last Sunday", strtotime(date("Y-m-d", strtotime("-{$t_num} week"))));
						break;
				}
			}
			else
			{
				//为空则默认为本周
				$time = strtotime("last Sunday", strtotime(date("Y-m-d", strtotime("-0 week"))));
			}
		}
		
		//echo date("Y-m-d H:i:s", $time);die;
		

		$sql = "SELECT inlog.uid, SUM(inlog.integral) AS week_integral, mem.user_name, mem.integral FROM " . $this->get_table('user_integral_day') . " AS inlog LEFT JOIN " . $this->get_table('users') . " AS mem ON inlog.uid = mem.uid WHERE inlog.today_time >= {$time} GROUP BY inlog.uid ORDER BY week_integral DESC";
		
		$user_list = $this->query_all($sql, $limit);
		
		$uids = array();
		
		foreach ($user_list as $key => $val)
		{
			$user_list[$key]['url'] = $this->model('account')->get_url_by_uid($val['uid']);
		}
		
		return $user_list;
	}

	/**
	 * 获取单个用户的积分分类统计， by category id
	 * @param  $uid
	 */
	public function get_integral_cout_by_category_id($uid)
	{
		$uid = intval($uid);
		
		if (! $uid)
		{
			return false;
		}
		
		return $this->query_all("SELECT sum(integral) AS integral, category_id FROM " . $this->get_table('user_integral_log') . " WHERE uid = {$uid} GROUP BY category_id");
	}
}