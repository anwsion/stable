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

class admin_class
{

	static function &instance()
	{
		static $admin;
		
		if (empty($admin))
		{
			$admin = new session_admin();
		}
		
		return $admin;
	}
	
	/// 获取一个会话变量
	static function get($key = false)
	{
		$u = & self::instance();
		return $u->get_info($key);
	}
	
	///当前访问者的uid
	static function get_superadmin_uid()
	{
		return self::get('__SUPERADMIN_UID');
	}
	
	///当前访问者的名称
	static function get_superadmin_name()
	{
		return self::get('__SUPERADMIN_NAME');
	}
	
	//检查登录
	static function check_admin_login()
	{
		if (! admin_class::get_superadmin_uid())
		{
			$url = "./?" . GZ_URL_CONTROLLER . "=login";
			
			if (! empty($_SERVER['REQUEST_URI']))
			{
				$url .= "&url=" . urlencode(trim($_SERVER['REQUEST_URI']));
			}
			
			HTTP::redirect($url);
		}
		else
		{
			TPL::assign('superadmin_name', admin_class::get_superadmin_name());
		}
	}
}

/**
 * 一个用户SESSION的类
 *
 * @author sank
 *
 */
class session_admin
{
	// 保存客户端用户的信息
	public static $server_superadmin_info = array();

	function session_admin()
	{
		//是否存在SESSION,不存在
		if (! $_SESSION["superadmin_info"] && $_COOKIE[G_COOKIE_PREFIX . "_superadmin_login"])
		{
			$admin_account_obj = new admin_account_class();
			
			//解掉COOKIE,然后进行验证
			$sso_superadmin_login = H::decode_hash($_COOKIE[G_COOKIE_PREFIX . "_superadmin_login"]);
			
			if ($sso_superadmin_login["superadmin_name"] && $sso_superadmin_login["password"] && $sso_superadmin_login["super_uid"] && ($sso_superadmin_login['UA'] == $_SERVER["HTTP_USER_AGENT"]))
			{
				$rs = $admin_account_obj->check_login($sso_superadmin_login["superadmin_name"], $sso_superadmin_login["password"]);
				
				if ($rs)
				{
					$_SESSION["superadmin_info"]["__SUPERADMIN_UID"] = $sso_superadmin_login["super_uid"];
					$_SESSION["superadmin_info"]["__SUPERADMIN_NAME"] = $sso_superadmin_login["superadmin_name"];
					$_SESSION["superadmin_info"]["__SUPERADMIN_PASSWORD"] = $sso_superadmin_login["password"];
					return true;
				}
			}
			
			return false;
		}
	
	}

	/**
	 * 返回信息
	 *
	 * @param $key 字段名
	 */
	function get_info($key)
	{
		return $_SESSION["superadmin_info"][$key];
	}

}