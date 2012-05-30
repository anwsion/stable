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

class account_class extends GZ_MODEL
{	
	var $admin_group_ids = array('1', '2');	//管理员用户组
	
	/**
	 * 未读系统通知
	 */
	const NOTIFICATION_UNREAD = 'notification_unread';
	/**
	 * 未读短信息
	 */
	const NOTICE_UNREAD = 'notice_unread';
	/**
	 * 粉丝数
	 */
	const FANS_COUNT = 'fans_count';
	/**
	 * 观众数
	 */
	const FRIEND_COUNT = 'friend_count';
	/**
	 * 问我数量
	 */
	const INVITE_COUNT = 'invite_count';
	/**
	 * 问题总数
	 */
	const QUESTION_COUNT = 'question_count';
	/**
	 * 回答问题数量
	 */
	const ANSWER_COUNT = 'answer_count';
	/**
	 * 编辑过的数量
	 */
	const EDIT_COUNT = 'edit_count';
	/**
	 * 话题数量
	 */
	const TOPIC_COUNT = 'topic_count';
	/**
	 * 比赛数量
	 */
	//const COMPETITIONS_COUNT = 'competitions_count';
	/**
	 * 作品数量
	 */
	//const CONTRIBUTES_COUNT = 'contributes_count';

	function get_source_hash($email)
	{
		return H::encode_hash(array(
			'email' => $email
		));
	}

	/**
	 * 检查用户名是否已经存在
	 * @param $username
	 * @return rows 
	 */
	
	function check_username($username)
	{
		$user_info = $this->fetch_row('users', "user_name = '" . $this->quote(trim($username)) . "'");
		
		return $user_info['uid'];
	}

	/**
	 * 检查用户名是否已经存在
	 * @param $username
	 * @return rows 
	 */
	
	function check_uid($uid)
	{		
		if ($this->get_users_by_uid($uid))
		{
			return 1;
		}
		
		return 0;
	}

	/**
	 * 检查电子邮件地址是否已经存在
	 * @param $email
	 * @return int
	 */
	function check_email($email)
	{
		if ($user_info = $this->get_users_by_email($email))
		{
			return $user_info['uid'];
		}
		
		return 0;
	}

	/**
	 * 正式表用户登录检查,错误返回FALSE,正确返回用户数据
	 * @param $username
	 * @param $password
	 * @return
	 */
	function check_login($username, $password)
	{
		if (!$username OR !$password)
		{
			return false;
		}
		
		if (! $userinfo = $this->get_users_by_username($username))
		{
			return false;
		}
		
		if (! $this->check_password($password, $userinfo['password'], $userinfo['salt']))
		{
			return false;
		}
		else
		{
			return $userinfo;
		}
	
	}

	/**
	 * 检验密码是否和数据库里面的密码相同
	 *
	 * @param string $password		新密码
	 * @param string $db_password   数据库密码
	 * @param string $salt			混淆码
	 * @return bool
	 */
	function check_password($password, $db_password, $salt)
	{
		$password = compile_password($password, $salt);
		
		if ($password == $db_password)
		{
			return true;
		}
		
		return false;
	
	}

	/**
	 * 检查个性网址是否已经存在
	 * @param $url
	 * @return rows 
	 */
	
	function check_url($url)
	{
		if (trim($url) == '')
		{
			return false;
		}
		
		return $this->count('users', "url = '" . $this->quote(trim($url)) . "'");
	}

	/**
	 * 帐号资料完整性检查
	 * 
	 * @return bool 是否完整
	 */
	function check_user_profile_complete($uid)
	{
		$uid = $uid * 1;
		
		if (! $uid)
		{
			return false;
		}
		
		$users = $this->get_users_by_uid($uid, true);
		
		if ($users['user_name'] && $users['district'] && $users['city'] && $users['province'] && $users['signature'])
		{
			return true;
		}
		else
		{
			return false;
		}
	
	}
	
	/**
	 * 获取处理过的用户信息
	 * 
	 * @param  $uid
	 */
	function get_users($uid, $attrib = false)
	{
		$uid = $uid * 1;
		
		if (! $uid)
		{
			return false;
		}
		
		$users = $this->get_users_by_uid($uid, $attrib);
		
		if (! $users)
		{
			return false;
		}
		
		if ($users["real_name"])
		{
			$users["real_name"] = "(" . $users["real_name"] . ")";
		}
		
		if ($users["birthday"])
		{
			$users["birthday"] = date("Y-m-d", $users["birthday"]);
		}
		
		if ($users["email"] === $users["user_name"])
		{
			$users["user_name"] = '';
		}
		
		$area_tmp = $this->get_user_areas_by_uids(array(
			$uid
		));
		
		$users["area_name"] = $area_tmp[$uid];
		
		return $users;
	}

	/**
	 * 通过用户 ID 取用户附加表资料
	 *
	 * @param int $userid
	 * @return array 返回全部用户资 料
	 */
	function get_users_attrib($user_id)
	{
		static $attrib_info;
		
		if ($attrib_info[$user_id])
		{
			return $attrib_info[$user_id];
		}
		
		$attrib_info[$user_id] = $this->fetch_row('users_attrib', 'uid = ' . intval($user_id));
		
		return $attrib_info[$user_id];
	}

	/**
	 * 通过用户id获取提现信息
	 * @param  $userid
	 * @return array
	 */
	function get_users_withdraw($user_id)
	{
		if (! $rs = $this->fetch_row('users_withdraw', 'uid = ' . intval($user_id)))
		{
			$this->add_users_withdraw($user_id);
			
			return array();
		}
		
		return $rs;
	}

	/**
	 *  添加提现数据库原始记录
	 * @param  $userid
	 * @return 
	 */
	function add_users_withdraw($user_id)
	{
		//插入获取用户ID		
		return $this->insert('users_withdraw', array(
			'uid' => (int)$user_id, 
			'withdraw_salt' => fetch_salt(4)
		));
	}

	/**
	 * 更新提现字段
	 * @param  $update_arr
	 * @param  $userid
	 */
	function update_users_withdraw($update_data, $user_id)
	{
		return $this->update('users_withdraw', $update_data, 'uid = ' . (int)$user_id);
	}

	/**
	 * 更新用户总提现现金总额度
	 * @param  $userid
	 */
	function update_withdraw_credit_all($userid)
	{
		return $this->update_users_withdraw(array(
			'withdraw_credit_all' => $this->sum('user_withdraw_log', 'credit', "uid = " . intval($userid) . " AND status = 2")
		), $userid);
	}

	/**
	 * 通过用户名获取用户信息
	 * @param $username		用户名或邮箱地址
	 * @return
	 */
	function get_users_by_username($username)
	{
		static $users_info;
		
		if ($users_info[$username])
		{
			return $users_info[$username];
		}
		
		if (! $user_info = $this->fetch_row('users', "user_name = '" . $this->quote($username) . "'"))
		{
			if (! $user_info = $this->get_users_by_email($username))
			{
				return false;
			}
		}
		
		$users_info[$username] = $user_info;
		
		return $users_info[$username];
	}

	/**
	 * 通过用户邮箱获取用户信息
	 * @param $email		用邮箱地址
	 * @return row
	 */
	function get_users_by_email($email)
	{
		static $users_info;
		
		if ($users_info[$email])
		{
			return $users_info[$email];
		}
		
		$users_info[$email] = $this->fetch_row('users', "email = '" . $this->quote(trim($email)) . "'");
		
		return $users_info[$email];
	}

	/**
	 * 通过用户 USER_ID 获取用户信息
	 * @param $username
	 * @return
	 */
	function get_users_by_uid($user_id, $attrib = false)
	{
		$user_id = $user_id * 1;
		
		if (! $user_id)
		{
			return false;
		}
		
		static $users_info;
		
		if ($users_info[$user_id . '_attrib'])
		{
			return $users_info[$user_id . '_attrib'];
		}
		else if ($users_info[$user_id])
		{
			return $users_info[$user_id];
		}
		
		if ($attrib)
		{
			$sql = "SELECT MEM.*, MEB.* FROM " . $this->get_table('users') . " AS MEM LEFT JOIN " . $this->get_table('users_attrib') . " AS MEB ON MEM.uid = MEB.uid WHERE MEM.uid = " . $user_id;
		}
		else
		{
			$sql = "SELECT * FROM " . $this->get_table('users') . " WHERE uid = " . $user_id;
		}
		
		if (! $user_info = $this->query_row($sql))
		{
			return false;
		}
		
		if (! $url)
		{
			$url = $user_info['url'];
		}

		$url = $url ? $url : $user_info['user_name'];
		
		$user_info['domain_url'] = get_setting('base_url') . '/people/?u=' . $url;
		
		//$user_info['url'] = get_setting('base_url') . '/people/?u=' . $url;
		
		$user_info['is_admin'] = $this->is_admin($user_info['group_id']);
		
		if ($attrib)
		{
			$users_info[$user_id . '_attrib'] = $user_info;
		}
		else
		{
			$users_info[$user_id] = $user_info;
		}
		
		return $user_info;
	}

	/**
	 * 通过指量用户USER_IDS返回指量用户数据
	 * 
	 * @param arrary $user_ids 用户 IDS
	 * @param bool	 $attrib   是否返回附加表数据
	 */
	function get_users_by_uids($user_ids, $attrib = false)
	{
		if (! is_array($user_ids))
		{
			return false;
		}
		
		if (sizeof($user_ids) < 1)
		{
			return false;
		}
		
		$user_ids_str = implode(',', $user_ids);
		
		if (sizeof($user_ids) == 1)
		{
			$user_id = intval($user_ids_str);
			
			return array(
				$user_id => $this->get_users_by_uid($user_id, $attrib)
			);
		}
		
		if ($attrib)
		{
			$sql = "SELECT MEM.*, MEB.introduction, MEB.signature FROM " . $this->get_table('users') . " AS MEM LEFT JOIN " . $this->get_table('users_attrib') . " AS MEB ON MEM.uid = MEB.uid WHERE MEM.uid IN({$user_ids_str})";
		}
		else
		{
			$sql = "SELECT * FROM " . $this->get_table('users') . " WHERE uid IN({$user_ids_str})";
		}
		
		$user_info = $this->query_all($sql);
				
		foreach($user_info as $key => $val)
		{
			if ($val["url"])
			{
				$url = $val['url'];
			}
			else
			{
				$url = $val['uid'];
			}
			$val['domain_url'] = get_setting('base_url') . '/people/?u=' . $url;
			//$val['url'] = get_setting('base_url') . "/people/?u=" . $url;
			
			$data[$val['uid']] = $val;
		}
		
		return $data;
	}

	/**
	 * 通过用户手机号取得用户信息
	 * 
	 * @param string  $mobile
	 */
	function get_users_by_mobile($mobile)
	{
		$mobile = trim($mobile);
		
		if (! $mobile)
		{
			return false;
		}
		
		return $this->fetch_row('users', "mobile = '" . $this->quote($mobile) . "'");
	}

	/**
	 * //注册用的获取信息,COOKIE
	 *
	 */
	function get_users_by_reg_login()
	{
		if ((isset($_COOKIE[G_COOKIE_PREFIX . "_user_reg_check"])) && ($_COOKIE[G_COOKIE_PREFIX . "_user_reg_check"] != ""))
		{
			$sso_user_check = H::decode_hash($_COOKIE[G_COOKIE_PREFIX . '_user_reg_check']); // 解 HASH
			

			return $this->get_users_by_uid($sso_user_check['uid']);
		}
		else
		{
			return false;
		}
	}

	/**
	 * 通过用户登录的COOKIE获取用户信息
	 */
	function get_users_by_login()
	{
		
		if ((isset($_COOKIE[G_COOKIE_PREFIX . '_user_login'])) && ($_COOKIE[G_COOKIE_PREFIX . '_user_login'] != ''))
		{

			$sso_user_check = H::decode_hash($_COOKIE[G_COOKIE_PREFIX . '_user_login']); // 解HASH
			
			if (! $sso_user_check['uid'])
			{
				return false;
			}
			
			return $this->get_users_by_uid($sso_user_check['uid']);
		}
		else
		{
			return false;
		}
	}

	/**
	 * 通过用户 USER_ID 获取用户信息
	 * @param $username
	 * @return
	 */
	function get_email_setting_by_uid($user_id)
	{
		if (! $user_id)
		{
			return false;
		}
		
		return $this->fetch_row('users_email_setting', 'uid = ' . intval($user_id));
	}

	/**
	 * 根据用户ID获取用户通知设置
	 * Enter description here ...
	 * @param $user_id
	 */
	function get_notification_setting_by_uid($user_id)
	{
		$setting = $this->fetch_row('users_notification_setting', 'uid = ' . intval($user_id));
		
		if (empty($setting))
		{
			return array('data' => array());
		}
		
		$setting['data'] = unserialize($setting['data']);
		
		if(empty($setting['data']))
		{
			$setting['data'] = array();
		}
		
		return $setting;
	}

	/**
	 * 通过用户名获取用户的真实性名
	 * 
	 * @param  $user_id
	 */
	function get_real_name_by_uid($user_id)
	{
		static $user_real_name_array = array();
		
		if (! $user_id)
		{
			return false;
		}
		
		if (! $user_real_name_array[$user_id])
		{
			if (! $rs = $this->query_row("SELECT user_name,real_name FROM " . $this->get_table('users') . " WHERE uid = " . intval($user_id)))
			{
				return false;
			}
			
			$user_real_name_array[$user_id] = $rs["user_name"];
		}
		
		return $user_real_name_array[$user_id];
	}

	/**
	 * 通过用户ID得到用户的个性网址
	 * 
	 * @param  $user_id
	 */
	function get_url_by_uid($user_id, $url = '')
	{
		$url = trim($url);
		
		static $user_url_array = array();
		
		if (! $user_id)
		{
			return false;
		}
		
		if (! $user_url_array[$user_id])
		{
			if (! $url)
			{
				if (! $user_info = $this->get_users_by_uid($user_id))
				{
					return false;
				}
				
				$url = $user_info['url'];
			}
			
			$url = $url ? get_setting('base_url') . '/people/?u='.$url : get_setting('base_url') . '/people/?u=' . $user_id;
			
			$user_url_array[$user_id] = $url;
		
		}
		
		return $user_url_array[$user_id];
	}

	/**
	 * 通过用户个性URL域名获取用户ID
	 * 
	 * @param $url
	 */
	function get_uid_by_url($url)
	{
		static $user_id_array = array();
		
		$url = trim($url);
		
		if (! $url)
		{
			return false;
		}
		
		if (! $user_id_array[$url])
		{
			if (! $user_info = $this->get_users_by_uid($url))
			{
				if (! $user_info = $this->query_row("SELECT uid FROM " . $this->get_table('users') . " WHERE url = '" . $this->quote($url) . "'"))
				{
					return false;
				}
			}
			
			$user_id_array[$url] = $user_info['uid'];
		}
		
		return $user_id_array[$url];
	}

	/**
	 * 通过用户名用户ID
	 *
	 * @param  $user_name
	 */
	function get_uid_by_user_name($user_name)
	{
		static $user_id_array = array();
		
		if (! $user_name)
		{
			return false;
		}
		
		if (! $user_id_array[$user_name])
		{
			if (! $user_info = $this->get_users_by_username($user_name))
			{
				return false;
			}
			
			$user_id_array[$user_name] = $user_info['uid'];
		}
		
		return $user_id_array[$user_name];
	}

	function get_user_name_by_uid($uid)
	{
		$user_info = $this->get_users_by_uid($uid);
		
		return $user_info['user_name'];
	}

	/**
	 * 获取头像
	 * @param  $uid
	 * @return boolean
	 */
	function get_avatar_file($uid)
	{
		$user_info = $this->get_users_by_uid($uid);
		
		return $user_info['avatar_file'];
	}

	/**
	 * 获取用户邀请名额
	 * 
	 * @param int  $uid
	 */
	function get_invitation_available($uid)
	{
		if (! $uid)
		{
			return false;
		}
		
		$user_info = $this->get_users_by_uid($uid);
		
		return $user_info['invitation_available'];
	}

	/**
	 * 获取用户计数器
	 * 
	 * @param  $type
	 * 
	 * 	notification_unread 未读系统通知                          
	 * 	notice_unread		未读短信息                             
	 * 	inbox_recv			0-所有人可以发给我,1-我关注的人
	 * 	fans_count			粉丝数                                   
	 * 	friend_count		观众数                                   
	 * 	invite_count		问我数量                                
	 * 	question_count		问题总数                                
	 * 	answer_count		回答问题数量                          
	 * 	edit_count			编辑过的数量                          
	 * 	topic_count			话题数量                                
	 * 	competitions_count	比赛数量 
	 * 				
	 * @param   $uid
	 */
	function get_user_counts($type, $uid)
	{
		static $row_count_array = array();
		
		$type_array[] = 'notification_unread';
		$type_array[] = 'notice_unread';
		$type_array[] = 'inbox_recv';
		$type_array[] = 'fans_count';
		$type_array[] = 'friend_count';
		$type_array[] = 'invite_count';
		$type_array[] = 'question_count';
		$type_array[] = 'answer_count';
		$type_array[] = 'edit_count';
		$type_array[] = 'topic_count';
		//$type_array[] = 'competitions_count';
		$type_array[] = 'invitation_available'; // 邀请名额
		

		$type = trim($type);
		
		if (! $type || ! in_array($type, $type_array))
		{
			return false;
		}
		
		$uid = $uid * 1;
		
		if (! $uid)
		{
			return false;
		}
		
		if (! $row_count_array[$uid])
		{
			$row = $this->get_users_by_uid($uid);
			
			if (! $row)
			{
				return false;
			}

			$row_count_array[$uid] = $row;
		}
		
		return $row_count_array[$uid][$type] * 1;
	
	}

	/**
	 * 根据用户ID集合获得用户地区集合
	 * @param $user_ids
	 */
	function get_user_areas_by_uids($user_ids)
	{
		$user_list = $this->get_users_by_uids($user_ids);
		
		foreach ($user_list as $key => $user)
		{
			if (! empty($user['province']))
			{
				$area_codes[] = $user['province'];
			}
			
			if (! empty($user['city']))
			{
				$area_codes[] = $user['city'];
			}
			
			if (! empty($user['district']))
			{
				$area_codes[] = $user['district'];
			}
		}
		
		if (! $area_codes)
		{
			return false;
		}
		
		$area_codes = array_unique($area_codes);
		
		$z_city = array(
			'110200', 
			'110100', 
			'120100', 
			'120200', 
			'310100', 
			'310200', 
			'500100', 
			'500200'
		);
		
		$area_codes = array_diff($area_codes, $z_city);
		
		if (empty($area_codes))
		{
			return null;
		}
		
		$rs = $this->fetch_all('area_code', "area_code IN(" . implode(",", $area_codes) . ")");
		
		$area_data = array();
		
		foreach ($rs as $key => $val)
		{
			$val['area_name'] = $this->area_trim($val['area_name']);
			$area_data[$val['area_code']] = $val;
		}
		
		foreach ($user_list as $key => $user)
		{
			$user_area = array();
			
			if (! empty($user['province']))
			{
				$user_area[] = $user['province'];
			}
			
			if (! empty($user['city']))
			{
				$user_area[] = $user['city'];
			}
			
			if (! empty($user['district']))
			{
				$user_area[] = $user['district'];
			}
			
			$has_count = 1;
			
			$area_str = '';
			
			foreach ($user_area as $key => $val)
			{
				if (empty($area_data[$val]))
				{
					continue;
				}
				
				$area_str .= ' ' . $area_data[$val]['area_name'];
				
				if ($has_count ++ >= 2)
				{
					break;
				}
			}
			
			if (! empty($area_str))
			{
				$data[$user['uid']] = $area_str;
			}
		}
		
		return $data;
	}

	/**
	 * 根据用户ID集合获得用户职业集合
	 * Enter description here ...
	 * @param $user_ids
	 */
	function get_user_jobs_by_uids($user_ids)
	{
		if (! $user_ids)
		{
			return false;
		}
		
		if (! is_array($user_ids) and intval($user_ids))
		{
			$user_ids = array(
				$user_ids
			);
		}
		
		$user_list = $this->get_users_by_uids($user_ids);
		
		foreach ($user_list as $key => $user)
		{
			if (! empty($user['job_id']))
			{
				$jobs[] = $user['job_id'];
			}
		}
		
		if (empty($jobs))
		{
			return null;
		}
		
		$jobs = array_unique($jobs);
		
		$rs = $this->fetch_all('jobs', "jobs_id IN(" . implode(",", $jobs) . ")");
		
		$job_data = array();
		
		foreach ($rs as $key => $val)
		{
			$job_data[$val['jobs_id']] = $val['jobs_name'];
		}
		
		foreach ($user_list as $key => $user)
		{
			if (! empty($job_data[$user['job_id']]))
			{
				$data[$user['uid']] = $job_data[$user['job_id']];
			}
		}
		
		return $data;
	}
	
	// 去掉省/市
	function area_trim($string)
	{
		if (strLen($string) >= 3)
		{
			$string = preg_replace('/(.*)省$/i', '${1}', $string);
			$string = preg_replace('/(.*)市$/i', '${1}', $string);
			if (preg_match('/(.*)自治区$/i', $string))
			{
				$string = preg_replace('/(.*)壮族自治区$/i', '${1}', $string);
				$string = preg_replace('/(.*)回族自治区$/i', '${1}', $string);
				$string = preg_replace('/(.*)维吾尔自治区$/i', '${1}', $string);
				$string = preg_replace('/(.*)自治区$/i', '${1}', $string);
			}
			return $string;
		}
		else
		{
			return $string;
		}
	}

	/**
	 * 编辑邀请名额
	 * 
	 * @param int $user_id UID 
	 * @param int $value 正数为加 负数为减
	 */
	function edit_invitation_available($uid, $value)
	{
		$uid = $uid * 1;
		
		if (! $uid)
		{
			return false;
		}
		
		$value = intval($value);
		
		if ($value == 0)
		{
			return false;
		}
		
		//增加
		if ($value >= 1)
		{
			return $this->query("UPDATE " . $this->get_table('users') . " SET invitation_available = invitation_available + " . $value . " WHERE uid = " . $uid);
		}
		else if ($value < 1)
		{
			$value = $value * - 1;
			
			return $this->query("UPDATE " . $this->get_table('users') . " SET invitation_available = invitation_available - " . $value . " WHERE uid = " . $uid);
		}
		else
		{
			return false;
		}
	}

	/********************************************************************************************
 * ADD
 */
	
	/**
	 * 添加新用户
	 *  
	 *  @param  $username		 用户名
	 *  @param  $password		密码
	 *  @param  $email			邮箱
	 *  @param  $sex			性别
	 *  @param  $user_status	用户状态(0-未激活1激活)
	 *  @param  $user_type  	用户类别(1-普通用户)
	 *  @param  $mobile			手机
	 *  @param  $referer_url	注册来源地址
	 *  @param  $referer_type	注册来源别
	 */
	
	function user_add($username, $password, $email, $sex = 0, $user_status = 0, $user_type = 1, $mobile = "", $referer_url = "", $referer_type = 0)
	{
		$insert_arr["salt"] = fetch_salt(4);
		
		if (trim($username) == '')
		{
			$username = $email;
		}
		
		//传入数据处理
		$insert_arr['user_name'] = htmlspecialchars(trim($username));
		$insert_arr['password'] = compile_password($password, $insert_arr['salt']);
		$insert_arr['email'] = htmlspecialchars(trim($email));
		$insert_arr['sex'] = $sex * 1;
		
		$insert_arr["mobile"] = $mobile;
		
		$insert_arr['reg_time'] = mktime();
		
		$insert_arr['reg_ip'] = ip2long(real_ip());
		
		$insert_arr['user_status'] = $user_status * 1;
		
		$insert_arr['user_type'] = $user_type * 1;
		
		$insert_arr['referer_url'] = htmlspecialchars(trim($referer_url));
		$insert_arr['referer_type'] = $referer_type * 1;
		$insert_arr['avatar_type'] = 1;
		
		if ($rs = $this->insert('users', $insert_arr))
		{
			$this->user_attrib_add($rs); //插附加表
			$this->user_email_setting_add($rs);
			$this->update_notification_setting_fields(null, $rs);
		
		}
		
		return $rs;
	}
	
	function user_register($username, $password, $email, $email_valid = false)
	{
		if ($uid = $this->user_add($username, $password, $email))
		{			
			$this->update_users_fields(array(
				"group_id" => 11
			), $uid);
			

			$this->update_users_fields(array(
				"invitation_available" => get_setting('newer_invitation_num')
			), $uid);
			

			$this->update_email_setting_fields(array(
				'sender_14' => 1, 
				'sender_15' => 1
			), $uid);
			
			//获取默认的关注用户并关注
			$default_user_focus = $this->get_user_list("ext_group_ids LIKE '%31%'", 100);
			
			$def_focus_uids_str = get_setting('def_focus_uids');
			$def_focus_uids = explode(',', $def_focus_uids_str);
			
			$default_user_focus_array = array();
			
			if ($default_user_focus)
			{
				foreach ($default_user_focus as $key => $val)
				{
					$default_user_focus_array[] = $val["uid"];
				}
			}
			
			$default_user_focus_array = array_merge($default_user_focus_array, $def_focus_uids);
			
			foreach ($default_user_focus_array as $key => $val)
			{
				if ($this->model('follow')->user_follow_add($uid, $val))
				{
					$this->model('follow')->user_fans_count_edit(1, $val); // 粉丝加1
					$this->model('follow')->user_friend_count_edit($uid, $val); // 我的关注会加1
				}
			}
			
			if ($email_valid)
			{
				$this->update_users_fields(array(
					"valid_email" => 1
				), $uid);
			}
			else
			{
				$active_code_hash = $this->model('active')->active_code_generate();
				
				$expre_time = time() + 60 * 60 * 24;
				
				$active_id = $this->model('active')->active_add($uid, $expre_time, $active_code_hash, 21, '', 'VALID_EMAIL');
				
				$this->model('email')->valid_email($email, $username, $active_code_hash);
			}
		}
		
		return $uid;
	}

	/**
	 * 插入附加表
	 *  
	 *  @param $uid
	 *  @param $signature
	 *  @param $introduction
	 */
	function user_attrib_add($uid, $signature = "", $introduction = "")
	{
		$uid = $uid * 1;
		
		if (! $uid)
		{
			return false;
		}
		
		$insert_arr['uid'] = $uid;
		$insert_arr['signature'] = htmlspecialchars($signature);
		$insert_arr['introduction'] = htmlspecialchars($introduction);
		
		return $this->insert('users_attrib', $insert_arr);
	}

	/**
	 * 插入附加表
	 *  
	 *  @param $uid
	 *  @param $signature
	 *  @param $introduction
	 */
	function user_email_setting_add($uid)
	{
		$uid = $uid * 1;
		
		if (! $uid)
		{
			return false;
		}
		
		$insert_arr['uid'] = $uid;
		$insert_arr['sender_15'] = 1;
		
		return $this->insert('users_email_setting', $insert_arr);
	
	}

	/*********************************************************************************************
 * UPDATE
 */
	
	/**
	 * 更新用户状态或字段
	 * @param $update_arr 字段
	 * @param $userid 用户id
	 * @return  
	 */
	function update_users_fields($update_arr, $uid)
	{
		return $this->update('users', $update_arr, 'uid = ' . intval($uid));
	}

	/**
	 * 更新用户附加表状态或字段
	 * @param $update_arr 字段
	 * @param $userid	用户id
	 * @return 
	 */
	function update_users_attrib_fields($update_arr, $user_id)
	{
		return $this->update('users_attrib', $update_arr, 'uid = ' . intval($user_id));
	}

	/**
	 * 更改用户密码
	 *
	 * @param  $oldpassword 旧密码
	 * @param  $password 新密码
	 * @param  $userid 用户id
	 * @param  $salt 混淆码
	 */
	function update_user_password($oldpassword, $password, $userid, $salt)
	{
		if ($salt == '')
		{
			return false;
		}
		
		$userid = $userid * 1;
		
		if (! $userid)
		{
			return false;
		}
		
		$oldpassword = compile_password($oldpassword, $salt);
		
		if ($this->count('users', "uid = " . $userid . " AND password = '{$oldpassword}'") != 1)
		{
			return false;
		}
		
		return $this->update_user_password_ingore_oldpassword($password, $userid, $salt);
	
	}

	/**
	 * 更改用户不用旧密码密码
	 *
	 * @param $password
	 * @param $userid
	 */
	function update_user_password_ingore_oldpassword($password, $user_id, $salt)
	{
		if (!$salt)
		{
			return false;
		}
		
		if (!$password)
		{
			return false;
		}
		
		$user_id = $user_id * 1;
		
		if (! $user_id)
		{
			return false;
		}
		
		$update_arr['password'] = compile_password($password, $salt);
		$update_arr['salt'] = $salt;
		
		$this->update('users', $update_arr, 'uid = ' . (int)$user_id);
		
		return true;
	}

	/**
	 * 更新登录信息;
	 *
	 * @param $userinfo
	 * @return unknown
	 */
	function update_login_state($userinfo)
	{
		$login_times = $userinfo["login_times"] * 1 + 1;
		
		$update_arr["user_login_time"] = time();
		$update_arr["user_login_ip"] = ip2long($this->real_ip());
		
		if (! $this->update('users', $update_arr, 'uid = ' . intval($userinfo['user_id'])))
		{
			return false;
		}
		else
		{
			return true;
		}
	
	}

	/**
	 * 更新签名
	 * 
	 * @param  $userid		用户名
	 * @param  $signature	签名
	 */
	function update_signature($userid, $signature)
	{
		if (! $userid)
		{
			return false;
		}
		
		$update_arr['signature'] = trim($signature);
		
		if (! $this->update('users_attrib', $update_arr, 'uid = ' . intval($userid)))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	function clean_first_login($uid)
	{
		if (! $this->update('users', array(
			'is_first_login' => 0
		), 'uid = ' . intval($uid)))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * 更新用户接收EMAIL设置
	 * 
	 * @param  $update_arr	更新数组
	 * @param  $userid	UID
	 * 
	 * @return bool
	 */
	function update_email_setting_fields($update_arr, $userid)
	{
		return $this->update('users_email_setting', $update_arr, 'uid = ' . intval($userid));
	}

	/**
	 * 更新用户最后登录时间
	 * @param  $userid 用户id
	 * @param  $login_time 登录时间戳(默认为当前时间,可为空)
	 */
	function update_user_last_login($userid, $login_time = 0)
	{
		$login_time = $login_time * 1;
		$userid = $userid * 1;
		
		if (! $userid)
		{
			return false;
		}
		
		if ($login_time == 0)
		{
			$login_time = mktime();
		}
		
		$update_arr["last_login"] = $login_time;
		
		$update_arr["last_ip"] = ip2long(real_ip());
		
		return $this->update('users', $update_arr, 'uid = ' . intval($userid));
	}

	/**
	 * 更新用户通知设置
	 * 
	 * @param  $update_arr	更新数组
	 * @param  $userid	UID
	 * 
	 * @return bool
	 */
	function update_notification_setting_fields($update_arr, $userid)
	{
		$userid = $userid * 1;
		
		$user_setting = $this->fetch_row('users_notification_setting', 'uid = ' . $userid);
		
		if (empty($user_setting))
		{
			$update_arr['uid'] = $userid;
			
			$this->insert('users_notification_setting', $update_arr);
		}
		else
		{
			$this->update('users_notification_setting', $update_arr, 'uid = ' . $userid);
		
		}
		
		return true;
	}
	
	public function add_user_online_time($uid, $online_time)
	{
		return $this->query("UPDATE " . get_table('users') . ' SET online_time = online_time + ' . intval($online_time) . ' WHERE uid = ' . intval($uid));
	}

	/**
	 * 修改用户所属的统计数值  [未读系统通知,未读短信息,粉丝数,观众数,问我数量,问题总数,回答问题数量,
	 * 编辑过的数量,话题数量,比赛数量
	 * @param int $state_type
	 * @param int $state_num
	 * 
	 * @return boolean
	 */
	public function increase_user_statistics($state_type, $state_num = 1, $uid = 0)
	{
		$state_array = array(
			self::ANSWER_COUNT, 
			//self::COMPETITIONS_COUNT, 
			//self::CONTRIBUTES_COUNT, 
			self::EDIT_COUNT, 
			self::FANS_COUNT, 
			self::FRIEND_COUNT, 
			self::INVITE_COUNT, 
			self::NOTICE_UNREAD, 
			self::NOTIFICATION_UNREAD, 
			self::TOPIC_COUNT
		);
		
		if (! in_array($state_type, $state_array))
		{
			return false;
		}
		
		if (intval($uid == 0))
		{
			$uid = USER::get_client_uid();
		}
		
		//未读通知
		if ($state_type == self::NOTIFICATION_UNREAD)
		{
			return $this->update('users', array(
				$state_type => $this->model('notify')->get_notifications_unread_num($uid)
			), 'uid = ' . intval($uid));
		}
		
		//未读私信
		if ($state_type == self::NOTICE_UNREAD)
		{
			return $this->update('users', array(
				$state_type => $this->model('message')->get_message_unread_num(intval($uid))
			), 'uid = ' . intval($uid));
		}
		
		//回复计数
		if ($state_type == self::ANSWER_COUNT)
		{
			$question_answer_count = $this->count('answer', 'uid = ' . intval($uid));
			
			//$competitions_comments_count = $this->count('competitions_comments', 'uid = ' . intval($uid));
			
			//$count = $question_answer_count + $competitions_comments_count;
			
			return $this->update('users', array(
				$state_type => $question_answer_count
			), 'uid = ' . intval($uid));
		}
		
		//比赛计数
		/*if ($state_type == self::COMPETITIONS_COUNT)
		{
			$count = $this->count('competitions_main', 'published_uid = ' . intval($uid));
			
			return $this->update('users', array(
				$state_type => $count
			), 'uid = ' . intval($uid));
		}*/
		
		//作品计数
		/*if ($state_type == self::CONTRIBUTES_COUNT)
		{
			$count = $this->count('contribute', 'uid = ' . intval($uid));
			
			return $this->update('users', array(
				$state_type => $count
			), 'uid = ' . intval($uid));
		}*/
		
		return $this->update('users', array(
			$state_type => ($state_type + $state_num)
		), 'uid = ' . intval($uid));
	}

	function get_login_cookie_hash($user_name, $password, $user_id)
	{
		$hash_cookie["user_name"] = $user_name;
		$hash_cookie["password"] = $password; //存加密过的密码
		$hash_cookie["uid"] = intval($user_id);
		$hash_cookie["UA"] = $_SERVER["HTTP_USER_AGENT"];
		
		return H::encode_hash($hash_cookie);
	}

	/**
	 * 设置登录时候的COOKIE信息
	 *
	 * @param  $userid
	 * @param  $username
	 * @param  $password
	 * 
	 * @return true
	 */
	function setcookie_login($user_id, $user_name, $password, $expire = null)
	{
		if (! $user_id)
		{
			return false;
		}
		
		if (! $expire)
		{
			HTTP::set_cookie("_user_login", $this->get_login_cookie_hash($user_name, $password, $user_id), null, "/"); //加密信息去存在COOKE;
		}
		else
		{
			$expire = time() + $expire;
			
			HTTP::set_cookie("_user_login", $this->get_login_cookie_hash($user_name, $password, $user_id), $expire, "/"); //加密信息去存在COOKE;				
		}
		
		return true;
	}

	/**
	 * 设置退出时候的COOKIE信息
	 * @param $userid
	 * @param $username
	 * @param $password
	 * @param $expire
	 * @return
	 */
	function setcookie_logout()
	{
		HTTP::set_cookie("_user_login", "", time() - 3600, "/"); //清除COOKE;
		
		//清除用户在之前空作用域名及解密方式情况下登录的cookie
		//setcookie(G_COOKIE_PREFIX . "_user_login", "", time() - 3600, "/", "", false);
	}

	function setsession_logout()
	{
		if (isset($_SESSION['client_info']))
		{
			unset($_SESSION['client_info']);
		}
	}
	
	//	/**
	//	 * 获取登录状态信息成功返回数据组,不成功返回FALSE
	//	 *
	//	 */
	//	function get_login_status_info(){
	//		
	//		
	//		
	//		if(isset($_COOKIE[G_COOKIE_PREFIX."_user_login"])&&($_COOKIE[G_COOKIE_PREFIX."_user_login"]!=""))
	//		{			
	//			$sso_user_login=H::decode_hash($_COOKIE["sso_user_login"]);
	//			
	//			
	//			if(($sso_user_login["user_name"]!="")&&($sso_user_login["password"]!="")&&($sso_user_login["uid"]!=""))
	//			{
	//				
	//				$sso_user_login["uid"]=$sso_user_login["uid"]*1;
	//				return $sso_user_login;
	//			}else{
	//				return false;
	//			}
	//		}			
	//		else{
	//			return false;
	//		}
	//		
	//	}
	/**
	 * 用户是否已经登录,是返回ID,否跳到登录界面
	 */
	function logined()
	{
		
		if (isset($_COOKIE[G_COOKIE_PREFIX . "_user_login"]) && ($_COOKIE[G_COOKIE_PREFIX . "_user_login"] != ""))
		{
			$sso_user_login = H::decode_hash($_COOKIE["sso_user_login"]);
			
			if (($sso_user_login["user_name"] != "") && ($sso_user_login["password"] != "") && ($sso_user_login["uid"] != ""))
			{
				
				$sso_user_login["uid"] = $sso_user_login["uid"] * 1;
				return $sso_user_login;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/***************************************************************************************************
 * 邮件处理
 */
	
	/**
	 * 发送激活电子邮件
	 * @param $email
	 * @param $username
	 * @param $active_code_hash
	 * @return bool
	 */
	/*
	function active_send_mail($email, $username, $active_code_hash)
	{
		$from_email = G_SMTP_ACCOUNT;
		$from_name = get_setting('site_name');
		
		$to_email = $email;
		$to_name = $username;
		$title = "用户 E-mail 验证 -- " . $from_name;
		$url = get_setting('base_url') . '/account/' . '?' . GZ_URL_CONTROLLER . "=active&" . GZ_URL_ACTION . "=account_active&key=" . $active_code_hash;
		$content = '<p>亲爱的用户,您好！</p>
		<p>感谢您注册成为' . $from_name . '在线会员！</p>
		<p>您的登录名为：' . $username . '</p>
		
						<p>请点击下面的链接地址激活您的会员帐户：  </p>
						<div style="width:750px; white-space:normal; word-break:break-all;">
						<a href="' . $url . '" target="_blank">' . $url . '</a></div>
		
						<p>(如果无法点击该URL链接地址，请将它复制并粘帖到浏览器的地址输入框，然后单击回车即可)</p>
						<p>&nbsp;</p>
						<p><strong>' . $from_name . '<br/>
												
						</strong></p><p>提示: 此信是系统自动发出, 请不要直接"回复"本电子邮件, 系统看不懂您的回信：</p>';
		
		$from_name = iconv("UTF-8", "GB2312//IGNORE", $from_name);
		$to_name = iconv("UTF-8", "GB2312//IGNORE", $to_name);
		$title = iconv("UTF-8", "GB2312//IGNORE", $title);
		$content = iconv("UTF-8", "GB2312//IGNORE", $content);
		
		return MAIL::send_mail($from_email, $from_name, $to_email, $to_name, $title, $content);
	
	}
	*/

	/****************************************************************************************************
  * OTHER
  */

	/**
	 * 检查用户名的字符
	 * @param $username
	 * @return
	 */
	function check_username_char($username)
	{
		//不允许英文特殊符号
		for ($i = 0; $i < strlen($username); $i ++)
		{
			$ascii = ord($username[$i]);
			
			if ($ascii == 32)
			{
				continue;
			}
			
			if ($ascii < 48 || ($ascii > 57 && $ascii < 65) || ($ascii > 90 && $ascii < 97) || ($ascii > 122 && $ascii < 128))
			{
				return false;
			}
		}
		
		if ($username != str_replace(array(
			"　", 
			"?", 
			"", 
			"", 
			""
		), ' ', $username))
		{
			return false;
		}
		
		$username = str_replace("　", '', $username);
		$username = str_replace("?", '', $username);
		$username = str_replace("", '', $username);
		$username = str_replace("", '', $username);
		
		if (strlen(iconv("UTF-8", "gb2312", $username)) < 4)
		{
			return false;
		}
		
		if (strlen(iconv("UTF-8", "gb2312", $username)) > 14)
		{
			return false;
		}
		
		$guestexp = '\xA1\xA1|\xAC\xA3|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';
		
		$censorexp = '/^(' . str_replace(array(
			'\\*', 
			"\r\n", 
			' '
		), array(
			'.*', 
			'|', 
			''
		), preg_quote(($censoruser = trim($censoruser)), '/')) . ')$/i';
		
		if (preg_match("/^\s*$|^c:\\con\\con$|[%,\*\"\t\s\<\>\&]|$guestexp/is", $username) || ($censoruser && @preg_match($censorexp, $username)))
		{
			return false;
		}
		
		return true;
	}

	/**
   * Returns true if $string is valid UTF-8 and false otherwise.
   *
   * @param mixed $str String to be tested
   * @return boolean
   */
	function is_utf8($str)
	{
		$c = 0;
		$b = 0;
		$bits = 0;
		$len = strlen($str);
		
		for ($i = 0; $i < $len; $i ++)
		{
			$c = ord($str[$i]);
			if ($c > 128)
			{
				if (($c >= 254))
					return false;
				elseif ($c >= 252)
					$bits = 6;
				elseif ($c >= 248)
					$bits = 5;
				elseif ($c >= 240)
					$bits = 4;
				elseif ($c >= 224)
					$bits = 3;
				elseif ($c >= 192)
					$bits = 2;
				else
					return false;
				if (($i + $bits) > $len)
					return false;
				while ($bits > 1)
				{
					$i ++;
					$b = ord($str[$i]);
					if ($b < 128 || $b > 191)
						return false;
					$bits --;
				}
			}
		}
		return true;
	}

	/**
	 * 
	 * 得到用户不感兴趣用户列表
	 * @param int $uid
	 * 
	 * @return array
	 */
	public function get_uninterested_list_by_uid($uid)
	{
		return $this->query_all("SELECT user_id FROM " . $this->get_table('user_uninterested') . " WHERE uid = " . intval($uid));
	}

	/**
	 * 
	 * 不感兴趣用户
	 * @param int $uid
	 * @param int $topic_id
	 * 
	 * @return boolean
	 */
	public function save_user_uninterested($uid, $user_id)
	{
		if (! $this->has_user_uninterested($uid, $user_id))
		{
			$this->insert('user_uninterested', array(
				'uid' => $uid, 
				'user_id' => $user_id, 
				'add_time' => time()
			));
			
			return true;
		}
		
		return false;
	}

	/**
	 * 
	 * 判断用户是否已经不感兴趣用户
	 * @param int $uid
	 * @param int $topic_id
	 * 
	 * @return boolean
	 */
	public function has_user_uninterested($uid, $user_id)
	{
		$retval = $this->query_row("SELECT uid FROM " . $this->get_table('user_uninterested') . " WHERE uid = " . intval($uid) . " AND user_id = " . intval($user_id));
		
		if (isset($retval['uid']) && $retval['uid'] > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * 
	 * 根据where条件批量获取用户
	 * @param string $where
	 * @param int    $limit
	 * 
	 * @return array
	 */
	public function get_user_list($where = '', $limit = 10, $orderby = "uid DESC")
	{
		if ($where)
		{
			$where = " WHERE (valid_email = 1 AND forbidden = 0) AND  " . $where;
		}
		
		return $this->query_all("SELECT uid FROM " . $this->get_table('users') . $where . " ORDER BY {$orderby} LIMIT " . $limit);
	}

	/**
	 * 
	 * @param string $where
	 * @param int    $limit
	 * 
	 * @return array
	 */
	public function get_users_list($where, $limit = 10, $attrib = false, $exclude_self = true, $orderby = "uid DESC")
	{
		if ($attrib)
		{
			if ($where)
			{
				$where = ' WHERE (MEM.valid_email=1 AND MEM.forbidden=0) AND ' . $where;
			}
			else 
			{
				$where = ' WHERE (MEM.valid_email=1 AND MEM.forbidden=0) ';
			}
			
			if ($exclude_self)
			{
				if ($where)
				{
					$where .= " AND MEM.uid <> " . USER::get_client_uid();
				}
				else
				{
					$where = " WHERE MEM.uid <> " . USER::get_client_uid();
				}
			}
			
			$sql = "SELECT MEM.*, MEB.introduction, MEB.signature FROM " . $this->get_table('users') . " MEM LEFT JOIN " . $this->get_table('users_attrib') . " AS MEB ON MEM.uid = MEB.uid " . $where . " ORDER BY MEM.{$orderby}";
			
			return $this->query_all($sql);
		}
		else
		{
			if ($exclude_self)
			{
				if ($where)
				{
					$where .= " AND (valid_email=1 AND forbidden=0)  AND uid <> " . USER::get_client_uid();
				}
				else
				{
					$where = " (valid_email=1 AND forbidden=0)  AND uid <> " . USER::get_client_uid();
				}
			}
			
			return $this->fetch_all('users', $where, $orderby, $limit);
		}
	}

	/**
	 * 随机获取用户
	 * 
	 * @param  $where
	 * @param  $limit
	 */
	public function get_rand_user_list($where, $limit = 0)
	{
		if ($where)
		{
			$where = " AND " . $where;
		}
		
		$sql = "SELECT * FROM  " . $this->get_table('users') . " AS arc JOIN (SELECT (RAND() * (SELECT MAX(uid) FROM " . $this->get_table('users') . ")) AS id) arc2" . " WHERE arc.uid >= arc2.id " . $where;
		
		return $this->query_all($sql, $limit);
	}

	/**
	 * 批量获取多个话题关注的用户列表
	 * @param  $topics_array
	 */
	public function get_users_list_by_topic_focus($topics_array, $only_uids = true)
	{
		$topics_array = array_unset_null_value($topics_array);
		
		if ( empty($topics_array))
		{
			return false;
		}
		
		$sql = "SELECT DISTINCT uid, topic_id FROM " . $this->get_table('topic_focus') . " WHERE topic_id IN(" . implode(",", $topics_array) . ")";
		
		$uid_list = $this->query_all($sql);
		
		if (! $uid_list)
		{
			return false;
		}
		
		if ($only_uids == true)
		{
			return $uid_list;
		}
		
		return $uid_list;
	}

	/**
	 * 获取回答一段时间内最多的人
	 * @param  $where
	 * @param  $limit
	 */
	public function get_answer_hot_user_list($where = '', $limit = 10)
	{
		if ($where)
		{
			$where = " WHERE  " . $where;
		}
		
		$sql = "SELECT uid FROM " . $this->get_table('users') . $where . " ORDER BY answer_count DESC";
		
		if (! $rs = $this->query_all($sql, $limit))
		{
			return false;
		}
		
		foreach ($rs as $key => $val)
		{
			$user_id_array[] = $val["uid"];
		}
		
		if ($user_id_array)
		{
			return $this->get_users_by_uids($user_id_array, true);
		}
		
		return false;
	}

	/**
	 * 获取个人动态
	 * 
	 * @param  $uid 为0表示查全部
	 * @param  $limit
	 * @param  $this_uid  当前登录的用户
	 * @param  $$distint 是否过滤重同一问题
	 */
	function get_user_actions($uid, $limit = 10, $get_type = 'all', $actions = false, $this_uid = 0,$distint=1)
	{
		if (! $get_type)
		{
			$get_type = 'all';
		}
		
		$this_uid = $this_uid * 1;
		
		//通过动作表进行排序和查找
		

		//限定作动
		

		//101 添加问题   
		//102 修改问题标题
		//103 修改问题描述
		//104 删除问题 
		//105 添加问题关注
		//106 删除问题关注
		//
		//201 回答问题
		//202 修改回答
		//203 删除回答
		//204 赞成回答
		//205 反对回答
		//
		//301 增加评论
		//302 删除评论
		//
		//401 创建话题
		//402 修改话题
		//403 修改话题描述
		//404 修改话题缩图
		//405 删除话题
		//
		//406 添加话题关注
		//407 删除话题关注
		//408 增加话题父类
		//409 删除话题父类
		//
		//501 添加比赛  
		//502 比赛报名 
		//503 提交作品 
		//504 修改作品
		//505 添加比赛关注
		//506 删除比赛关注
		

		$action_question = "101,201";
		
		//$action_competitions = "501,503";
		
		if ($get_type == 'questions' or $get_type == 'all')
		{
			if ($actions)
			{
				$action_question = $actions;
			}
			
			if (intval($uid) == 0)
			{
				$where[] = "(associate_type='" . ACTION_LOG::CATEGORY_QUESTION . "'  AND associate_action IN(" . FORMAT::Safe($action_question) . "))";
			}
			else
			{
				$where[] = "(associate_type='" . ACTION_LOG::CATEGORY_QUESTION . "' AND uid = " . (int)$uid . " AND associate_action IN(" . FORMAT::Safe($action_question) . "))";
			}
		}
		
		if($distint==1)
		{
			$action_list = ACTION_LOG::get_actions_distint_by_where(implode($where, ' OR '), $limit);
		}
		else
		{
			$action_list = ACTION_LOG::get_action_by_where(implode($where, ' OR '), $limit);
		}
		
		//重组信息
		// p($action_list);
		foreach ($action_list as $key => $val)
		{
			$action_list[$key]["add_time"] = $val["add_time"];
			
			if (! isset($user_info_list[$val['uid']]))
			{
				$user_info_more_list[$val['uid']] = $this->get_users_by_uid($val['uid'], true);
				$user_info_list[$val['uid']] = $user_info_more_list[$val['uid']]["user_name"];
			}
			
			$action_list[$key]["user_info"] = $user_info_more_list[$val['uid']];
			
			switch ($val["associate_type"])
			{
				case ACTION_LOG::CATEGORY_QUESTION :
					
					$index_focus_type = 1;
					
					$question_info = $this->model('question')->get_question_info_by_id($val["associate_id"]);
					
					if (! $user_info_list[$val["uid"]])
					{
						$user_info_list[$val["uid"]] = $this->get_users($val["uid"]);
					}
					
					if (in_array($val["associate_action"], array(
						401, 
						402, 
						403, 
						404, 
						405, 
						406, 
						407, 
						408, 
						409
					)))
					{
						$topic_info = $this->model('topic')->get_topic($val["associate_attached"]);
					}
					
					if (in_array($val["associate_action"], array(101)))
					{
						$question_info[attachs] = $this->model('question')->get_question_attach($question_info['question_id']); //获取附件
					}
					
					$question_info['last_action_str'] = ACTION_LOG::format_action_str($val["associate_action"], $val['uid'], $user_info_list[$val['uid']], null, $topic_info, 1);
					
					if (in_array($val["associate_action"], array(
						201
					)))
					{
						
						$answer_list = $this->model('answer')->get_answer_info_by_id($val['associate_attached'], 0, false);
						$answer_list['attachs'] = $this->model("answer")->get_answer_attach($val['associate_attached']); //获取附件
					}
					else
					{
						$answer_list = null;
					}
					
					$user_arr = array();
					
					if (! empty($answer_list))
					{
						$user_info = $this->get_users_by_uid($answer_list['uid'], true);
						$answer_list['url'] = $this->get_url_by_uid($user_info['domain_url']);
						$answer_list['uname'] = $user_info['user_name'];
						$answer_list['signature'] = $user_info['signature'];
						$question_info['answer_info'] = $answer_list;
					}
					
					//是否关注
					if ($this_uid > 0)
					{
						if ($this->model('question')->has_focus_question($question_info["question_id"], $this_uid))
						{
							$action_list[$key]['has_focus'] = true;
						}
						else
						{
							$action_list[$key]['has_focus'] = false;
						}
					}
					else
					{
						if ($this->model('question')->has_focus_question($question_info["question_id"], $uid))
						{
							$action_list[$key]['has_focus'] = true;
						}
						else
						{
							$action_list[$key]['has_focus'] = false;
						}
					}
					
					//还原到单个数组ROW里面
					foreach ($question_info as $qkey => $qval)
					{
						if ($qkey == 'add_time')
						{
							continue;
						}
						
						$action_list[$key][$qkey] = $qval;
					}
					
					$action_list[$key]['topics'] = $this->model('question_topic')->get_question_topic_by_question_id($question_info['question_id']);
					
					break;
			}
		}
		
		return $action_list;
	}

	public function get_user_recommend_v2($uid, $limit = 10)
	{
		$friends = $this->model('follow')->get_user_friends($uid, false);
		
		foreach ($friends as $key => $val)
		{
			$follow_uids[] = $val['friend_uid'];
			
			$follow_users_array[$val['friend_uid']] = $val;
		}
		
		//echo implode($follow_uids, ','); die;
		

		if (! $follow_uids)
		{
			return $this->get_users_list(false, $limit, true);
		}
		
		$users_focus = $this->query_all("SELECT DISTINCT friend_uid, fans_uid FROM " . $this->get_table('user_follow') . " WHERE fans_uid IN(" . implode($follow_uids, ',') . ") ORDER BY follow_id DESC LIMIT " . $limit);
		
		foreach ($users_focus as $key => $val)
		{
			$users_ids[] = $val['friend_uid'];
			
			if (! isset($users_ids_rtype[$val['friend_uid']]))
			{
				$users_ids_rtype[$val['friend_uid']] = array(
					'type' => 'friend', 
					'fans_uid' => $val['fans_uid']
				); //推荐类型
			}
		}
		
		//取跟我关注同样话题人出来
		

		//取我关注的话题
		$my_focus_topics_ids[] = 0;
		$my_focus_topics = $this->model('topic')->get_focus_topic_list(false, $uid, null);
		
		foreach ($my_focus_topics as $key => $val)
		{
			$my_focus_topics_ids[] = $val['topic_id'];
			$my_focus_topics_array[$val['topic_id']] = $val;
		}
		
		$uids = $this->get_users_list_by_topic_focus($my_focus_topics_ids);
		
		if(!empty($uids))
		{
			foreach ($uids as $key => $val)
			{
				if (in_array($val['uid'], $users_ids))
				{
					continue;
				}
				$users_ids[$val['uid']] = $val['uid'];
				
				if (! isset($users_ids_rtype[$val['friend_uid']]))
				{
					$users_ids_rtype[$val['uid']] = array(
						"type" => "topic", 
						"topic_id" => $val['topic_id']
					);
				}
			}
		}
		

		
		if (! $users_ids)
		{
			return $this->get_users_list("MEM.uid NOT IN (" . implode($follow_uids, ',') . ")", $limit, true);
		}
		

		$users = $this->query_all("SELECT MEM.*, MEB.introduction, MEB.signature 
			FROM " . $this->get_table('users') . " MEM						
			LEFT JOIN " . $this->get_table('users_attrib') . " AS MEB 						
			ON MEM.uid = MEB.uid
			WHERE  (MEM.valid_email=1 AND MEM.forbidden=0) AND MEM.uid IN(" . implode($users_ids, ',') . ") AND MEM.uid NOT IN (" . implode($follow_uids, ',') . ") AND MEM.uid <> " . $uid . " ORDER BY MEM.uid DESC LIMIT " . $limit);
		
		
		if (sizeof($users) < $limit)
		{
			$users_new = $this->get_users_list('MEM.uid NOT IN(' . implode($users_ids, ',') . ') AND  MEM.uid <> ' . $uid . ' AND  MEM.uid NOT IN (' . implode($follow_uids, ',') . ')', ($limit - sizeof($users)), true);
			$users_new = $this->get_activity_random_users(($limit - sizeof($users)), false, array_merge($users_ids, $follow_uids,array($uid)));
			
			foreach ($users_new as $key => $val)
			{
				$users[] = $val;
			}
		}
		
		foreach ($users as $key => $val)
		{
			$users[$key]["rtype"] = $users_ids_rtype[$val["uid"]];
			
			if ($users_ids_rtype[$val["uid"]]["type"] == "friend")
			{
				$users[$key]["friend_users"] = $follow_users_array[$users[$key]["rtype"]['fans_uid']];
			}
			else if ($users_ids_rtype[$val["uid"]]["type"] == "topic")
			{
				$users[$key]["topic_info"] = $my_focus_topics_array[$users[$key]["rtype"]["topic_id"]];
			}
		
		}
		
		return $users;
	}

	public function add_user_apply($email, $user_name, $reason)
	{
		$insert_arr = array(
			'email' => $this->quote($email), 
			'user_name' => htmlspecialchars($user_name), 
			'add_time' => time(), 
			'client_ip' => ip2long(real_ip()), 
			'reason' => htmlspecialchars($reason), 
			'passed' => 0
		);
		
		return $this->insert('users_apply', $insert_arr);
	}

	/**
	 * 根据职位ID获取职位信息
	 */
	function get_jobs_by_id($jobs_id)
	{
		static $jobs_info;
		
		if (!$jobs_info[$jobs_id])
		{
			$jobs_info[$jobs_id] = $this->fetch_row('jobs', 'jobs_id = ' . intval($jobs_id));
		}
		
		return $jobs_info[$jobs_id];
	}

	/**
	 * 获取头像目录文件地址
	 * @param  $uid
	 * @param  $size
	 * @param  $return_type 0=返回全部 1=返回目录(a/b/c/) 2=返回文件名
	 * @return string
	 */
	function get_avatar($uid, $size = 'min', $return_type = 0)
	{
		$size = in_array($size, array(
			'max', 
			'mid', 
			'min', 
			'50', 
			'150'
		)) ? $size : 'real';
		
		$uid = abs(intval($uid));
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		
		if ($return_type == 1)
		{
			return $dir1 . '/' . $dir2 . '/' . $dir3 . '/';
		}
		
		if ($return_type == 2)
		{
			return substr($uid, - 2) . '_avatar_' . $size . '.jpg';
		}
		
		return $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid, - 2) . '_avatar_' . $size . '.jpg';
	}
	
	/**
	 * 删除用户头像
	 * @param unknown_type $uid
	 * @return boolean
	 */
	function delete_avatar($uid)
	{
		$uid = intval($uid);
		
		if($uid == 0)
		{
			return false;
		}
		
		$avatar = $this->get_avatar($uid);
		
		
		foreach( GZ_APP::config()->get('image')->avatar_thumbnail as $key => $val)
		{
			@unlink(get_setting('upload_dir').'/avatar/' . $this->get_avatar($uid, $key, 1) . $this->get_avatar($uid, $key, 2));
		}
		
		return $this->update_users_fields(array('avatar_file' => ''), $uid);
	}
	
	function update_thanks_count($uid)
	{
		$counter = $this->sum('answer', 'thanks_count', 'uid = ' . intval($uid));
		
		return $this->update('users', array(
			'thanks_count' => $counter
		), "uid = " . intval($uid));
	}
	
	// 获取活跃用户 (非垃圾用户)
	function get_activity_random_users($limit = 10, $extra_info = false, $uid_not_in = array())
	{
		// 好友 & 粉丝 > 5, 回答 > 5, 根据登陆时间, 倒序
		if (sizeof($uid_not_in) > 0)
		{
			$not_in_query = ' AND uid NOT IN(' . implode($uid_not_in, ',') . ')';
		}
		
		if ($extra_info)
		{
			$sql = "SELECT uid FROM " . $this->get_table('users') . " WHERE fans_count > 5 AND friend_count > 5 AND answer_count > 1 " . $not_in_query . " ORDER BY last_login DESC LIMIT " . $limit;
			
			if (! $rs = $this->query_all($sql))
			{
				return false;
			}
			
			foreach ($rs as $key => $val)
			{
				$user_id_array[] = $val["uid"];
			}
			
			if ($user_id_array)
			{
				return $this->get_users_by_uids($user_id_array, true);
			}
			
			return false;
		}
		
		return $this->fetch_all('users', "fans_count > 5 AND friend_count > 5 AND answer_count > 1 " . $not_in_query, 'last_login DESC', $limit);
	}
	

	private function is_admin($group_id)
	{
		if(in_array(intval($group_id), $this->admin_group_ids))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
