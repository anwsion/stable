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

class c_login_class extends GZ_CONTROLLER
{
	//var $show_seccode_max_count = 3; //最大出错不显示安全信息次数
	
	public function get_access_rule()
	{
		$rule_action["rule_type"] = 'black'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		
		$rule_action["actions"] = array();
		
		return $rule_action;
	}

	public function setup()
	{
		HTTP::no_cache_header();
	}

	function index_action()
	{
		$this->login_action();
	}

	function logout_action($return_url = '')
	{
		$this->model('online')->logout();	// 在线列表退出
		$this->model("account")->setcookie_logout();	// 清除 COOKIE
		$this->model("account")->setsession_logout();	// 清除 Session
		
		if($_SESSION["superadmin_info"])
		{
			$this->model('admin_account')->setcookie_logout();
			$this->model('admin_account')->setsession_logout();
		}

		//$_SESSION["error"] = 0; // 重置登录出错计数

		if (! $return_url)
		{
			HTTP::redirect(get_setting('base_url')."/index/"); // 转到根目录 
		}
		else
		{
			HTTP::redirect($return_url);
		}
	}

	function login_action()
	{
		$url = urlencode(trim($this->_INPUT["url"]));
		
		if ($this->user_id)
		{
			if (urldecode(trim($this->_INPUT["url"])))
			{
				header('Location: ' . urldecode(trim($this->_INPUT["url"]))); 
			}
			else
			{
				HTTP::redirect(get_setting('base_url') . '/index/');
			}
		}
		
		TPL::assign('r_uname', HTTP::get_cookie('r_uname'));
		
		TPL::import_js('js/login.js');
		
		$this->crumb('用户登录', '/account/?c=login');
		
		TPL::output("account/login");
	}

	function login_process_ajax_action()
	{
		$url = urldecode($this->_INPUT['url']);
		
		$user_name = trim($this->_INPUT['user_name']);
		$password = $this->_INPUT['password'];
		$net_auto_login = intval($this->_INPUT['net_auto_login']);
		
		if (get_setting('ucenter_enabled') == 'Y')
		{
			if (!$users = $this->model('account')->check_login($user_name, $password))
			{
				if ($user_info = $this->model('ucenter')->login($user_name, $password))
				{
					$users = $this->model('account')->check_login($user_info['email'], $user_info['password']);
				}				
			}
		}
		else
		{
			$users = $this->model('account')->check_login($user_name, $password);
		}
		
		if (! $users)
		{
			H::ajax_json_output(GZ_APP::RSM(null, -1, '请输入正确的帐号或密码'));
		}
		else
		{
			if ($users['forbidden'] == 1)
			{
				H::ajax_json_output(GZ_APP::RSM(null, -1, '抱歉, 你的账号已经被禁止登录'));
			}

			if (!$users['valid_email'])
			{
				$_SESSION['valid_email'] = $users['email'];
				
				H::ajax_json_output(GZ_APP::RSM(array(
					'url' => get_setting('base_url') . '/account/?c=register&act=valid_email'
				), 1, '请验证您的帐户邮箱'));
			}
			
			if ($net_auto_login)
			{
				$expire = 60 * 60 * 24 * 360;
			}

			$this->model('account')->update_user_last_login($users['uid']);
			
			$this->model('account')->setcookie_logout();
			
			// 默认记住用户名
			HTTP::set_cookie('r_uname', $user_name, time() + 60 * 60 * 24 * 30, "/");
			
			$this->model('account')->setcookie_login($users["uid"], $user_name, $password, $expire);
			
			if ($users['is_first_login'])
			{
				$url = get_setting('base_url') . '/index/?first_login=TRUE';
			}
			
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => $url
			), 1, "登录成功"));
		}
	}
}