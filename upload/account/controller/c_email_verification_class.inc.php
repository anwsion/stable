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

class c_email_verification_class extends GZ_CONTROLLER
{

	/**
	* 控制器登录检查
	*/
	public function get_access_rule()
	{
		$rule_action["rule_type"] = 'black'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["guest"] = array();
		$rule_action["user"] = array();
		
		return $rule_action;
	}
	
	function setup()
	{
		HTTP::no_cache_header();
	}
	
	function index_action()
	{
		$this->verification();
	}

	function verification()
	{
		include "../invitation/model/verification_class.inc.php";
		
		$email = $this->_INPUT['email'];
		$verification_code = $this->_INPUT['vcode'];
		
		if (!$this->model('verification')->check_code_available($verification_code))
		{
			H::js_pop_msg('错误：验证链接已失效。', get_setting('base_url'));
		}
		
		$active_ip = ip2long($_SERVER['REMOTE_ADDR']);
		
		if ($this->model('verification')->verification_code_active($verification_code, time(), $active_ip))
		{
			H::js_pop_msg('邮箱验证成功', get_setting('base_url'));
		}
	}

}


