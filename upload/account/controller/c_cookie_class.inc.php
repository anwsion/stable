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

class c_cookie_class extends GZ_CONTROLLER
{

	public function get_access_rule()
	{
		$rule_action["rule_type"] = 'black'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["guest"] = array();
		$rule_action["user"] = array();
		
		return $rule_action;
	}
	
	function get_login_cookie_hash($user_name, $password, $user_id)
	{
		$hash_cookie["user_name"] = $user_name;
		$hash_cookie["password"] = $password; //存加密过的密码
		$hash_cookie["uid"] = intval($user_id);
		//$hash_cookie["UA"] = $_SERVER["HTTP_USER_AGENT"];
		

		return H::encode_hash($hash_cookie);
	}
}