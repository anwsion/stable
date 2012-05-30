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

class c_register_class extends GZ_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action["rule_type"] = 'black'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["actions"] = array();
		
		return $rule_action;
	}
	
	function index_action()
	{
		$this->register_action();
	}

	function register_action()
	{
		$this->step1_action();
	}
	
	function step1_action()
	{
		$uid = $this->user_id;
		
		if ($uid && ($this->_INPUT['invite_question_id'] || $this->_INPUT['invite_competition_id']))
		{
			$invite_question_id = intval($this->_INPUT['invite_question_id']);
			$invite_competition_id = intval($this->_INPUT['invite_competition_id']);
			
			if ($invite_question_id)
			{
				$url = get_setting('base_url') . "/question/?act=detail&question_id=" . $invite_question_id;
			}
			
			HTTP::redirect($url);
			exit();
		}
		
		$__source = $this->_INPUT["source"];
		
		if (HTTP::get_cookie('source'))
		{
			$__source = HTTP::get_cookie('source');
		}
		
		$source = H::decode_hash($__source);
		
		if ($__source and $this->model('account')->check_email($source['email']))
		{
			// 有来源的用户无需邀请码
			TPL::assign("source", $__source);
		}
		else
		{
			// 邀请码验证
			$icode = trim($this->_INPUT["icode"]);
			
			if ($icode)
			{
				$invitation_class = $this->model('invitation');
				
				//检验失败
				if (!$this->model('invitation')->check_code_available($icode))
				{
					H::js_pop_msg('邀请码无效或已经使用，请使用新的邀请码', get_setting('base_url'));
				}
				else
				{
					TPL::assign('icode', $icode);
				}
			}
		}
		
		TPL::assign("email", trim($this->_INPUT["email"]));
		TPL::assign("user_name", trim($this->_INPUT["user_name"]));
		TPL::assign("invite_question_id", (int)$this->_INPUT['invite_question_id']);
		TPL::assign("invite_competition_id", (int)$this->_INPUT['invite_competition_id']);
		
		$this->crumb('注册', '/account/?c=register');
		
		TPL::output("account/register_step1_v2");
	}

	/**
	 * 注册第一步
	 * 
	 */
	function step1_process_ajax()
	{
		$email = $this->_INPUT["email"];
		
		$email_valid = false;
		
		if (!$email)
		{
			$email = $this->_INPUT["email_ref"];
			$email_valid = true;
		}
		
		if ($this->_INPUT["invite_question_id"])
		{
			$objetc_type = 1;
			$objetc_id = $this->_INPUT["invite_question_id"];
		}
		
		if ($this->_INPUT["invite_competition_id"])
		{
			$objetc_type = 2;
			$objetc_id = $this->_INPUT["invite_competition_id"];
		}
		
		//检查验证码
		if (!($this->_INPUT["source"] || $this->_INPUT["icode"]) && (!core_captcha::validate($this->_INPUT["seccode_verify"], false)))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => 'seccode_verify'
			), -1, '请填写正确的验证码'));
		}
		
		$__source = $this->_INPUT["source"];
		
		if (HTTP::get_cookie('source'))
		{
			$__source = HTTP::get_cookie('source');
		}
		
		$source = H::decode_hash($__source);
		
		if ($__source and $this->model('account')->check_email($source['email']))
		{
			// 有来源的用户无需邀请码
		}
		else
		{
			//邀请码验证
			$icode = trim($this->_INPUT["icode"]);
			
			if ($icode)
			{
				//检验失败
				if (!$this->model('invitation')->check_code_available($icode))
				{
					
					H::ajax_json_output(GZ_APP::RSM(array(
						'input' => 'icode'
					), -1, "邀请码出错"));
					
					exit();
				}
			}
		}
		
		//检查用户名
		if (trim($this->_INPUT["user_name"]) == '')
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => 'user_name'
			), -1, "请输入真实姓名"));
		}
		else if ($this->model('account')->check_username($this->_INPUT["user_name"]))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => 'user_name'
			), -1, "真实姓名已经存在"));
		}
		
		if ($this->model('account')->check_email($email))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => 'userEmail'
			), -1, "邮箱已经存在, 请使用新的邮箱"));
		}
		
		if (strlen($this->_INPUT["password"]) < 6 OR strlen($this->_INPUT["password"]) > 16)
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => 'userPassword'
			), -1, "密码长度不符合规则"));
		}
		
		if (! $this->_INPUT["agreement_chk"])
		{
			H::ajax_json_output(GZ_APP::RSM(null, -1, "请选择同意用户协议中的条款"));
		}

		if ($__source and $this->model('account')->check_email($source['email']))
		{
			// 有来源的用户无邀请码
			$follow_usrs = $this->model('account')->get_users_by_email($source['email']);
		}
		else
		{
			$follow_usrs = $this->model('invitation')->get_invitation_by_code($icode);
		}
		
		if ($follow_usrs)
		{
			$follow_uid = $follow_usrs["uid"];
		}
		
		if (get_setting('ucenter_enabled') == 'Y')
		{
			$result = $this->model('ucenter')->register($this->_INPUT['user_name'], $this->_INPUT['password'], $email);
			if (is_array($result))
			{
				$uid = $result['user_info']['uid'];
			}
			else
			{
				H::ajax_json_output(GZ_APP::RSM(null, -1, $result));
			}
		}
		else
		{
			$uid = $this->model('account')->user_register($this->_INPUT['user_name'], $this->_INPUT['password'], $email, $email_valid);
		}
		
		if ($uid)
		{
			$this->model("account")->setcookie_logout(); // 清除COOKIE
			$this->model("account")->setsession_logout(); // 清除session;
			
			$_SESSION["error"] = 0; // 重置登录出错计数

			core_captcha::clear(); // 清除验证码
			
			if ($__source and $this->model('account')->check_email($source['email']) and $follow_uid)
			{
				// 有来源的用户自动产生邀请码
				$invitation_code = $this->model('invitation')->get_unique_invitation_code();
				
				$invitation_id = $this->model('invitation')->add_invitation($follow_uid, $invitation_code, $email, time(), ip2long('127.0.0.1'));
				
				$icode = $invitation_code;
			}
			
			// 发送邀请问答站内信
			if ($objetc_id and $follow_usrs)
			{
				if ($objetc_type == 1)
				{
					$url = get_setting('base_url') . '/question/?act=detail&question_id=' . $this->_INPUT['invite_question_id'];
					$title = $follow_usrs['user_name'] . ' 邀请你来回答问题';
					$content = $follow_usrs['user_name'] . "  邀请你来回答问题: " . $url . " \r\n\r\n 邀请你来回答问题期待您的回答";
				}
				
				$this->model('message')->send_message($follow_uid, $uid, $title, $content, 0, 0);
			}
			
			//互为关注
			if ($follow_uid)
			{
				if ($this->model('follow')->user_follow_add($uid, $follow_uid))
				{
					$this->model('follow')->user_fans_count_edit($follow_uid, 1); //粉丝加1			
					$this->model('follow')->user_friend_count_edit($uid, 1); //我的关注会加1
					
					//互加
					$this->model('follow')->user_follow_add($follow_uid, $uid);
					$this->model('follow')->user_fans_count_edit($uid, 1);
					$this->model('follow')->user_friend_count_edit($follow_uid, 1);
				}
			}

			if ($icode)
			{
				$this->model('account')->setcookie_login($uid, $this->_INPUT["user_name"], $this->_INPUT["password"]); //登录COOKIE
				$this->model('invitation')->invitation_code_active($icode, time(), real_ip(), $uid, $objetc_type, $objetc_id);

				H::ajax_json_output(GZ_APP::RSM(array(
				'url' => urlencode(get_setting('base_url') . '/index/?first_login=TRUE')
				), 1, '用户基本信息注册成功')); // 返回数据
			}
			else
			{
				$_SESSION['valid_email'] = $email;
				H::ajax_json_output(GZ_APP::RSM(array(
					'url' => urlencode(get_setting('base_url') . '/account/?c=register&act=valid_email')
				), 1, '用户基本信息注册成功')); // 返回数据
			}
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, -1, '用户基本信息注册失败')); //返回数据			
		}
	
	}

	function valid_email_action()
	{
		$email = $_SESSION['valid_email'];
		
		if(!$email)
		{
			HTTP::redirect('./?c=login');
		}
		
		//判断邮箱是否已验证
		$users = $this->model('account')->get_users_by_email($email);
		
		if(!$users)
		{
			HTTP::redirect('./?c=login');
		}
		
		if($users['valid_email'])
		{
			H::js_pop_msg('邮箱已通过验证，请登录', './?c=login');
		}

		$email_domain = substr(stristr($email, '@'), 1);
		
		$common_email = (array)GZ_APP::config()->get('common_email');
		
		if($common_email[$email_domain])
		{
			$email_site = $common_email[$email_domain];
		}
		else
		{
			$email_site = array(
				'name' => $email_domain,
				'url' => $email_domain,
			);
		}
		
		TPL::assign('email', $email);
		
		TPL::assign('common_email', $common_email[$email_domain]);
		
		$this->crumb('验证您的帐号邮箱', '/account/?c=register&act=valid_email');
		
		TPL::output("account/valid_email");
	}
	
	public function valid_email_process_action()
	{
		$email = $_SESSION['valid_email'];
		
		if(!$email)
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => urlencode(get_setting('base_url') . '/account/?c=login')
			), -1, '系统错误，请返回登录'));
		}
		
		$users = $this->model('account')->get_users_by_email($email);
		
		if(!$users)
		{
			unset($_SESSION['valid_email']);
			H::ajax_json_output(GZ_APP::RSM(array(
			'url' => urlencode(get_setting('base_url') . '/account/?c=register')
			), -1, '用户不存在，请重新注册'));
		}

		if($users['valid_email'])
		{
			unset($_SESSION['valid_email']);
			H::ajax_json_output(GZ_APP::RSM(array(
			'url' => urlencode(get_setting('base_url') . '/account/?c=login')
			), -1, '邮箱已通过验证，请返回登录'));
		}
		
		$active_code_hash = $this->model('active')->active_code_generate();
		
		$expre_time = time() + 60 * 60 * 24; // 24小时后过期
		
		$active_id = $this->model('active')->active_add($users['uid'], $expre_time, $active_code_hash, 21, '', 'VALID_EMAIL');
		
		$retval = $this->model('email')->valid_email($email, $users["user_name"], $active_code_hash);
		
		if($retval)
		{
			H::ajax_json_output(GZ_APP::RSM(null, 1, '确认邮件发送成功，请及时查收邮件'));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, 1, '发送确认邮件失败，请联系管理员，谢谢。'));
		}
	}

	public function apply_account_action()
	{
		TPL::assign('page_title', '获得加入资格');
		
		TPL::output("account/apply_account_v2");
	}

	public function apply_success_action()
	{
		TPL::assign('page_title', '获得加入资格');
		
		TPL::output("account/apply_success_v2");
	}

	public function apply_account_ajax_action()
	{
		
		$email = $this->_INPUT['email'];
		$user_name = $this->_INPUT['user_name'];
		$reason = $this->_INPUT['reason'];
		
		if (! H::isemail($email))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => 'email'
			), -1, '请填写正确的邮箱地址。'));
		}
		
		// 检测邮箱是否已经注册帐号
		if ($this->model('account')->check_email($email))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => 'email'
			), -1, '此邮箱已注册了帐号。'));
		}
		
		if (!$user_name)
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => "user_name"
			), -1, "真实姓名不能为空。"));
		}
		
		if (!$this->model('account')->check_username_char($user_name))
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => 'user_name'
			), -1, '真实姓名请输入 2-7 个汉字，或 4-14 个英文。'));
		}
		
		if (strlen($reason) < 10)
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'input' => 'reason'
			), -1, '申请理由不能少于 10 个字符。'));
		}
		
		$retval = $this->model('account')->add_user_apply($email, $user_name, $reason);
		
		if ($retval)
		{
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => urlencode("/account/?c=register&act=apply_success")
			), 1, '提交申请成功，请留意您的邮箱。'));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, -1, '提交失败，请稍候重试。'));
		}
	}

}