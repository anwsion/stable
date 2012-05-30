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

class c_active_class extends GZ_CONTROLLER
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

	/**
	 * 注册邮箱激活
	 */
	function account_active_action()
	{
		$active_code = trim($this->_INPUT["key"]);
		
		$active_code_row = $this->model('active')->get_active_code_row($active_code, 1);
		
		if ($active_code_row)
		{
			
			if ($active_code_row["active_time"] || $active_code_row["active_ip"] || $active_code_row["active_expire"])
			{
				
				H::js_pop_msg("已经激活成功,请不要重复激活", "?" . GZ_URL_CONTROLLER . "=login");
			}
			
			//激活代码检查成功
			$user_id = $this->model('active')->active_code_active($active_code, 1);
			
			if (! $user_id)
			{
				
				H::js_pop_msg("激活失败, 用户不存在", "?" . GZ_URL_CONTROLLER . "=login");
			}
			else
			{
				
				H::js_pop_msg("激活成功,请重新登录", "?" . GZ_URL_CONTROLLER . "=login");

			}
		
		}
		else
		{
			H::js_pop_msg("激活失败", "?" . GZ_URL_CONTROLLER . "=login");
		}
	
	}

	/**
	 * 找回密码
	 */
	function find_password_active_action()
	{
		$active_code = trim($this->_INPUT["key"]);
		
		$active_code_row = $this->model('active')->get_active_code_row($active_code, 11);
		
		if ($active_code_row)
		{
			if ($active_code_row["active_time"] || $active_code_row["active_ip"] || $active_code_row["active_expire"])
			{
				H::js_pop_msg('激活失败, 激活代码无效', '/');
			}
			else
			{
				HTTP::redirect('/account/?' . GZ_URL_CONTROLLER . "=find_password&" . GZ_URL_ACTION . "=step2&key=" . $active_code);
			}
		}
		else
		{
			H::js_pop_msg("激活失败, 激活代码无效", '/');
		}
	}

	/**
	 * 验证邮箱激活
	 */
	function valid_email_active_action()
	{
		$active_code = trim($this->_INPUT["key"]);
		
		$active_code_row = $this->model('active')->get_active_code_row($active_code, 21);
		
		if (!$active_code_row)
		{
			H::js_pop_msg('激活失败, 链接已无效', get_setting('base_url'));
		}
		
		if ($active_code_row["active_time"] || $active_code_row["active_ip"] || $active_code_row["active_expire"])
		{
			H::js_pop_msg('邮箱已通过验证，请返回登录', get_setting('base_url') . '/account/?c=login');
		}
		
		$users = $this->model('account')->get_users_by_uid($active_code_row['uid']);
		
		if($users['valid_email'])
		{
			H::js_pop_msg('帐户已激活, 请返回登录', get_setting('base_url') . '/account/?c=login');
		}
		
		$this->crumb('帐户激活');
		
		TPL::assign('active_code', $active_code);
		
		TPL::assign('email', $users['email']);
		
		TPL::output('account/valid_email_active');
	}

	/**
	 * 验证邮箱激活_处理
	 */
	function valid_email_active_process_action()
	{
		$active_code = trim($this->_INPUT["active_code"]);

		if (!core_captcha::validate($this->_INPUT["seccode_verify"], false))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
			'input' => "seccode_verify"
			), "-1", "请填写正确的验证码"));
		}
				
		$active_code_row = $this->model('active')->get_active_code_row($active_code, 21);
		
		if (!$active_code_row)
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => get_setting('base_url') . '/account/?c=login',
			), 1, '激活失败, 链接已无效'));
		}
		
		if ($active_code_row["active_time"] || $active_code_row["active_ip"] || $active_code_row["active_expire"])
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => get_setting('base_url') . '/account/?c=login',
			), 1, '帐户已激活, 请返回登录'));
		}
		
		$email_ref = trim($this->_INPUT["email_ref"]);
		$password = ($this->_INPUT["password"]);
		
		$users = $this->model('account')->check_login($email_ref, $password);

		if (!$users)
		{
			H::ajax_json_output(GZ_APP::RSM(null, -1, '请输入正确的帐号或密码'));
		}
		
		if($users['uid'] != $active_code_row['uid'])
		{
			H::ajax_json_output(GZ_APP::RSM(array(
			'url' => get_setting('base_url') . '/account/?c=login',
			), 1, '请使用注册的邮箱及密码激活帐户'));
		}
		
		if ($users['valid_email'])
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => get_setting('base_url') . '/account/?c=login',
			), 1, '帐户已通过邮箱验证，请返回登录'));
		}
		
		$user_id = $this->model('active')->active_code_active($active_code, 21);

		if($user_id)
		{
			if($_SESSION['valid_email'])
			{
				unset($_SESSION['valid_email']);
			}
			
			$this->model('account')->update_users_fields(array(
					'valid_email' => 1
			), $active_code_row['uid']);
			
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => get_setting('base_url') . '/account/?c=login',
			), 1, '帐户激活成功，请返回首页登录'));
		}
	}
}