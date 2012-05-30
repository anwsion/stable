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

class c_login_class extends ADMIN_CONTROLLER
{

	public function index_action()
	{
		$this->login_action();
	}

	public function login_action()
	{
		if ($this->admin_uid)
		{
			HTTP::redirect("./?act=index");
		}
		
		$user_id = USER::get_client_uid();
		
		$user_info = $this->model('account')->get_users_by_uid($user_id);
		
		if ($user_id && $user_info['is_admin'])
		{
			TPL::assign('user_info', $user_info);
		}
		
		TPL::assign("url", urlencode($this->_INPUT['url']));
		
		TPL::output("admin/login");
	}

	/**
	 * 登录处理
	 */
	public function login_process_ajax_action()
	{
		if (!core_captcha::validate($this->_INPUT['seccode_verify'], false))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => 'seccode_verify'
			), "-1", "请填写正确的验证码"));
		}
		
		$url = urldecode(trim($this->_INPUT["fromurl"]));
		
		$user_name = FORMAT::safe(trim($this->_INPUT["username"]));
		
		$password = FORMAT::safe($this->_INPUT["password"]);
		
		$admin_info = $this->model('admin_account')->check_login($user_name, $password);
		
		if (! $admin_info)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "帐号密码错误或无权限。"));
		}
		else
		{
			//强制清除域名其它cookie
			$this->model('admin_account')->setcookie_logout();
			
			$this->model('admin_account')->setcookie_login($admin_info["uid"], $user_name, $password, 0);
			
			if (empty($url) || $url == 'undefined')
			{
				$url = "?act=index";
			}
			
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => $url
			), "1", "登录成功"));
		}
	
	}

	/**
	 * 退出
	 */
	function logout_action($return_url = "")
	{
		$this->model('admin_account')->setcookie_logout();
		
		$this->model('admin_account')->setsession_logout();
		
		$_SESSION["error"] = 0; //重置登录出错计数
		

		if ($return_url == "")
		{
			HTTP::redirect("./?c=login");
		}
		else
		{
			HTTP::redirect($return_url);
		}
	}
	
	


}