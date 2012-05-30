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

class c_find_password_class extends GZ_CONTROLLER
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
		$this->crumb('找回密码', '/account/?c=find_password');
	}

	function index_action()
	{
		$this->step1_action();
	}
	
	//第一步
	function step1_action()
	{		
		TPL::output('account/find_password_step1_v2');
	}
	
	//第一步AJAX处理
	function step1_process_ajax_action()
	{
		$email = $this->_INPUT['email'];
		
		//检查验证码
		if (!core_captcha::validate($this->_INPUT["seccode_verify"], false))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => "seccode_verify"
			), "-1", "请填写正确的验证码"));
		}
			
		//$this->_INPUT["user_name"]=trim($this->_INPUT["user_name"]);
		$this->_INPUT["email"] = trim($this->_INPUT["email"]);
		
		if (!H::isemail($email))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => "email"
			), "-1", "请填写正确的邮箱地址"));
			exit();
		}		

		if (!$rs = $this->model('account')->get_users_by_email($email))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => "email"
			), "-1", "邮箱地址错误或帐号不存在。"));
			
			exit();
		}
		
		$active_code_hash = $this->model('active')->active_code_generate(); //生成校验码
		
		$expre_time = time() + 60 * 60 * 24; //24小时后过期
		
		$active_id = $this->model('active')->active_add($rs["uid"], $expre_time, $active_code_hash, 11, "", "FIND_PASSWORD");
		
		$this->model('email')->find_password($this->_INPUT["email"], $rs["user_name"], $active_code_hash); // 发送
		
		$_SESSION['find_password'] = $this->_INPUT['email'];
		
		//TPL::assign ("email", $this->_INPUT["email"]);
		//TPL::output ("account/find_password_step1_success_v2");
		

		H::ajax_json_output(GZ_APP::RSM(array(
			'url' => "/account/?c=find_password&act=step1_process_success"
		), "1", "找回密码成功。")); //返回数据
	

	}
	
	//第一步处理成功
	function step1_process_success_action()
	{
		$email = $_SESSION['find_password'];
		
		$email_domain = substr(stristr($email, '@'), 1);
		
		$common_email = (array)GZ_APP::config()->get('common_email');
		
		TPL::assign('email', $email);
		
		TPL::assign('common_email', $common_email[$email_domain]);
		
		TPL::output("account/find_password_step1_success_v2");
	}
	
	//第一步
	function step2_action()
	{
		TPL::assign('key', $this->_INPUT['key']);
		TPL::output("account/find_password_step2_v2");
	}

	function step2_process_ajax()
	{
		//检查验证码
		if (!core_captcha::validate($this->_INPUT["seccode_verify"], false))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => "seccode_verify"
			), "-1", "请填写正确的验证码"));
		}

		//检查验证码
		if (empty($this->_INPUT['password']))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
			'input' => "password"
			), "-1", "密码不能为空"));
		}

		//检查验证码
		if ($this->_INPUT['password'] != $this->_INPUT['re_password'])
		{
			H::ajax_json_output(GZ_APP::RSM(array(
			'input' => "password"
			), "-1", "两次输入的密码不一致"));
		}
		
		$password = $this->_INPUT["password"];
		
		$active_code = trim($this->_INPUT["active_code"]);
		
		$active_code_row = $this->model('active')->get_active_code_row($active_code, 11);
		
		if ($active_code_row)
		{
			if ($active_code_row["active_time"] || $active_code_row["active_ip"] || $active_code_row["active_expire"])
			{
				H::ajax_json_output(GZ_APP::RSM(array(
				'url' => get_setting('base_url') . '/account/?c=login',
			), "1", "重置密码链接已失效")); //返回数据
				
				exit();
			}
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => get_setting('base_url') . '/account/?c=login',
			), "1", "重置密码链接已失效")); //返回数据
			
			exit();
		}
		
		/**
		 * 重新验证一次结束
		 * 
		 */
		
		//激活代码检查成功
		

		$uid = $this->model('active')->active_code_active($active_code, 11);
		
		if (! $uid)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "找回密码KEY使用失败")); //返回数据
			exit();
		}
		
		$users = $this->model('account')->get_users_by_uid($uid);
		
		if ($this->model('account')->update_user_password_ingore_oldpassword($password, $uid, $users["salt"]))
		{
			$this->model("account")->setcookie_logout(); //清除COOKIE
			
			$this->model("account")->setsession_logout(); //清除session;
			
			$_SESSION["error"] = 0; //重置登录出错计数
			
			unset($_SESSION['find_password']);

			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => get_setting('base_url') . "/account/?c=login",
			), "1", "密码修改成功,请返回登录")); //格式化输出,并转为JSON进行处理
			
			exit();
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "修改失败")); //格式化输出,并转为JSON进行处理
			exit();
		}
	
	}
	
	//第二步处理完成
	function step2_success_action()
	{
		TPL::output("account/find_password_step2_success_v2");
	}
}