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

class ucenter_class extends GZ_MODEL
{
	var $uc_client_path;
	
	function setup()
	{
		if (get_setting('ucenter_enabled') != 'Y')
		{
			show_error('UCenter adapter not enabled.');
		}
		
		$this->uc_client_path = realpath(GZ_PATH . '../') . '/uc_client/';
		
		if (!file_exists($this->uc_client_path . '/client.php'))
		{
			show_error('UCenter client not installed.');
		}
		
		if (!file_exists($this->uc_client_path . '/config.inc.php'))
		{
			show_error('UCenter client config file not installed.');
		}
		
		require_once $this->uc_client_path . '/config.inc.php';
		require_once $this->uc_client_path . '/client.php';
	}
	
	function register($_username, $_password, $_email)
	{
		$result = uc_user_register($_username, $_password, $_email);
		
		switch ($result)
		{
			default:
				return array(
					'user_info' => $this->login($_username, $_password),
					'uc_uid' => $result,
					'username' => $_username,
					'email' => $_email
				);
			break;
			
			case -1:
				return '用户名不合法';
			break;
			
			case -2:
				return '用户名包含不允许注册的词语';
			break;
			
			case -3:
				return '用户名已经存在';
			break;
			
			case -4:
				return 'Email 格式有误';
			break;
			
			case -5:
				return 'Email 不允许注册';
			break;
			
			case -6:
				return '该 Email 已经被注册';
			break;
		}
	}
	
	function login($_username, $_password)
	{		
		if (H::isemail($_username))
		{
			// 使用 E-mail 登录
			list($uc_uid, $username, $password, $email) = uc_user_login($_username, $_password, 2);
		}
		else
		{
			list($uc_uid, $username, $password, $email) = uc_user_login($_username, $_password);
		}
		
		if ($uc_uid > 0)
		{
			if (!$use_info = $this->get_uc_user_info($uc_uid))
			{
				if ($this->model('account')->check_username($username) OR $this->model('account')->check_email($email))
				{
					return false;
				}
				
				$new_user_id = $this->model('account')->user_register($username, $_password, $email, TRUE);
				
				if ($exists_uc_id = $this->is_uc_user($email))
				{
					$this->update('users_ucenter', array(
						'username' => htmlspecialchars($username),
						'uid' => $new_user_id
					), 'uc_uid = ' . intval($exists_uc_id));
				}
				else
				{
					$this->insert('users_ucenter', array(
						'uid' => $new_user_id,
						'uc_uid' => $uc_uid,
						'username' => $username,
						'email' => $email
					));
				}
				
				$user_info = $this->model('account')->get_users_by_uid($new_user_id, true);
			}
			else
			{
				// Update password
				$this->model('account')->update_user_password_ingore_oldpassword($_password, $user_info['uid'], $user_info['salt']);
				
				// Update username
				if ($user_info['user_name'] != $username)
				{
					if (!$this->model('account')->check_username($username))
					{
						$this->model('account')->update_users_fields(array('user_name' => htmlspecialchars($username)), $user_info['uid']);
						$this->update('users_ucenter', array(
							'username' => htmlspecialchars($username),
						), 'uc_uid = ' . intval($uc_uid));
					}
				}
			}
		}
		
		return $user_info;
	}
	
	function get_uc_user_info($uc_uid)
	{
		$uc_user = $this->fetch_row('users_ucenter', 'uc_uid = ' . intval($uc_uid));
		
		return $this->model('account')->get_users_by_uid($uc_user['uid'], true);
	}
	
	function user_edit($uid, $_username, $oldpw, $newpw, $email = null)
	{
		if (!$uid)
		{
			return false;
		}
		
		$result = uc_user_edit($_username, $oldpw, $newpw, $email);
		
		switch ($result)
		{
			default:
				/*if ($new_pw)
				{
					$this->model('account')->update_user_password_ingore_oldpassword($newpw, $uid, fetch_salt(4));
				}*/
				
				return 1;
			break;
			
			case -1:
				return '旧密码不正确';
			break;
			
			case -4:
				return 'Email 格式有误';
			break;
			
			case -5:
				return 'Email 不允许注册';
			break;
			
			case -6:
				return '该 Email 已经被注册';
			break;
			
			/*case -7:
				return '没有做任何修改';
			break;*/
			
			case -8:
				return '该用户受保护无权限更改';
			break;
		}
	}
	
	function is_uc_user($email)
	{
		if (!$email)
		{
			return false;
		}
		
		static $uc_users;
		
		if ($uc_users[$email])
		{
			return $uc_users[$email];
		}
		
		$uc_user = $this->fetch_row('users_ucenter', "email = '" . $this->quote(trim($email)) . "'");
		
		$uc_users[$email] = $uc_user['uc_uid'];
		
		return $uc_users[$email];
	}
}