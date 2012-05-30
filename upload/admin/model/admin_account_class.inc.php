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

class admin_account_class extends GZ_MODEL
{
	static $admin_group_ids = array('1', '2');
	
	public function check_login($username, $password)
	{
		$username = trim ( $username );
		
		if ($username == "" || $password == "")
		{
			return false;
		}
		
		$userinfo = self::get_users_by_username ( $username );
		
		if (! $userinfo)
		{
			return false;
		}
		
		if(!in_array($userinfo['group_id'], self::$admin_group_ids))
		{
			return false;
		}
		
		if (! self::check_password ( $password, $userinfo ["password"], $userinfo ["salt"] ))
		{
			return false;
		} else
		{
			return $userinfo;
		}
	}
	
	/**
	 * 检验密码是否和数据库里面的密码相同
	 *
	 * @param string $password		新密码(没加密)
	 * @param string $db_password   数据库密码
	 * @param string $salt			混淆码
	 * @return bool
	 */
	function check_password($password, $db_password, $salt)
	{
		$password = self::compile_password ( $password, $salt );
		
		if ($password === $db_password)
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * 通过用户名获取用户信息
	 * @param $username		用户名或邮箱地址
	 * @return unknown_type
	 */
	public function get_users_by_username($username)
	{
		$username = trim ( $username );
		
		if (! $username)
		{
			return false;
		}
		
		return $this->fetch_row ('users', "(user_name='" . $this->quote ( $username ) . "') OR (email='" . $this->quote ( $username ) . "')" );
	}
	
	/**
	 * 通过管理员id获取用户信息
	 * @param $username		用户名或邮箱地址
	 * @return unknown_type
	 */
	public function get_admin_info_by_uid($uid)
	{
		return $this->fetch_row ('users', 'uid = ' . intval ( $uid ) );
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
	public function setcookie_login($userid, $user_name, $password, $expire = 0)
	{
		if (! $userid)
		{
			return false;
		}
		
		$userid = $userid * 1;
		$user_name = trim ( $user_name );
		$password = trim ( $password );
		
		$hash_cookie ["superadmin_name"] = $user_name;
		$hash_cookie ["password"] = $password; //存加密过的密码
		$hash_cookie ["super_uid"] = $userid;
		$hash_cookie ["UA"] = $_SERVER ["HTTP_USER_AGENT"];
		
		$expire = $expire * 1;
		
		if (! $expire)
		{
			setcookie ( G_COOKIE_PREFIX . "_superadmin_login", H::encode_hash ( $hash_cookie ), 0, "/", G_COOKIE_DOMAIN );
		} else
		{
			setcookie ( G_COOKIE_PREFIX . "_superadmin_login", H::encode_hash ( $hash_cookie ), time () + $expire, "/", G_COOKIE_DOMAIN ); //加密信息去存在COOKE;				
		}
		
		return true;
	}
	
	/**
	 * 设置退出时候的COOKIE信息
	 * @param $userid
	 * @param $username
	 * @param $password
	 * @param $expire
	 * @return unknown_type
	 */
	public function setcookie_logout()
	{
		setcookie ( G_COOKIE_PREFIX . "_superadmin_login", "", time () - 3600, "/", G_COOKIE_DOMAIN ); //清除COOKE;
	}
	
	public function setsession_logout()
	{
		if (isset($_SESSION["superadmin_info"]))
		{
			unset($_SESSION["superadmin_info"]);
		}
	}
	
	/**
	 * 编译密码
	 * 
	 * @param  $password 	密码
	 * @param  $salt		混淆码
	 * 
	 * @return string		加密后的密码
	 */
	function compile_password($password, $salt)
	{
		$password = md5 ( md5 ( $password ) . $salt );
		
		return $password;
	}
}
