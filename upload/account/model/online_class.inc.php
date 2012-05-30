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

class online_class extends GZ_MODEL
{
	
	var $db_update_intval = 30; //数据表更新时间间隔

	
	/**
	 * 根据条件获得在线用户列表
	 * @param $count
	 * @param $where
	 */
	public function get_db_online_users($count = false, $where = "", $limit = "0, 10", $order_by = "last_active DESC")
	{
		$this->check_online_group();
		
		if ($count)
		{
			return $this->count('users_online', $where);
		}
		else
		{
			return $this->fetch_all('users_online', $where, $order_by, $limit);
		}
	}

	/**
	 * 间隔一分钟清理一次在线用户列表，并更新数据表记录
	 */
	private function check_online_group()
	{
		ZCACHE::$cache_open = true;

		$online_users = ZCACHE::getGroup('online_users');
		
		if (! empty($online_users))
		{
			$users = array();
			
			foreach ($online_users as $key => $val)
			{
				$online_user = ZCACHE::get($val);
				
				if ($online_user === FALSE)
				{
					unset($online_users[$key]);
				}
				else
				{
					$users[] = $online_user;
				}
			}
			
			ZCACHE::set('cachegroup_online_users', $online_users, null, 3600);
		}
		
		//更新数据库记录
		
		if ($users)
		{
			$this->update_online_table($users);
			
			foreach($users as $key => $val)
			{
				$this->model('account')->add_user_online_time($val['uid'], $this->db_update_intval);
			}
		}
		
		ZCACHE::$cache_open = false;
	}

	/**
	 * 当前用户激活在线状态
	 */
	public function online_active()
	{
		if (get_setting('online_count_open') == "N")
		{
			return false;
		}
		
		$uid = USER::get_client_uid();
		
		if ($uid == 0)
		{
			return false;
		}
		
		$online_user['uid'] = $uid;
		$online_user['last_active'] = time();
		$online_user['ip'] = ip2long(real_ip());
		$online_user['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		$online_user['active_url'] = $_SERVER['HTTP_REFERER'];
		
		$this->check_online_group();
		
		ZCACHE::$cache_open = true;
		
		ZCACHE::delete('online_user_' . $uid);
		
		ZCACHE::set('online_user_' . $uid, $online_user, 'online_users', intval(get_setting('online_interval')) * 60);
		
		ZCACHE::$cache_open = false;
		
		return true;
	}
	
	//以下是使用数据库处理在线用户列表
	

	/**
	 * 批量导入用户在线列表
	 * @param $users
	 */
	public function update_online_table($users)
	{
		$this->query("TRUNCATE TABLE " . $this->get_table('users_online'));
		
		foreach ($users as $key => $val)
		{
			$svalue[] = "('" . implode("','", $val) . "')";
		}
		
		$sql = "INSERT INTO " . $this->get_table('users_online') . " (`uid`, `last_active`, `ip`, `user_agent`, `active_url`) VALUES ";
		
		$sql .= implode(", ", $svalue);
		
		$this->query($sql);
	}

	/**
	 * 清除超过时间不在线的会员
	 */
	public function delete_expire_users()
	{
		$expire = time() - get_setting('online_interval') * 60;
		
		return $this->delete('users_online', "last_active < {$expire}");
	}

	/**
	 * 获得用户在线数据
	 * @param $uid
	 */
	public function get_user_online_by_uid($uid = '')
	{
		if (empty($uid))
		{
			$uid = USER::get_client_uid();
		}
		
		return $this->fetch_row('users_online', 'uid = ' . intval($uid));
	}

	/**
	 * 更新用户在线数据
	 * @param $online_id
	 * @param $update_arr
	 */
	public function update_user_online($online_id, $update_arr)
	{
		return $this->update('users_online', $update_arr, "online_id = " . intval($online_id));
	}

	public function logout()
	{
		$uid = USER::get_client_uid();
		
		if ($uid == 0)
		{
			return false;
		}
		
		ZCACHE::$cache_open = true;
		
		ZCACHE::delete('online_user_' . $uid);
		
		ZCACHE::$cache_open = false;
		
		return true;
	}

}
