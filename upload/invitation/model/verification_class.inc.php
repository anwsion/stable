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

class verification_class extends GZ_MODEL
{

	/**
	 * 生成新的激活码
	 * Enter description here ...
	 */
	function get_unique_verification_code()
	{
		$new_code = md5(uniqid(rand(), true) . $this->fetch_salt());
		
		if ($this->verification_code_exists($new_code))
		{
			return $this->get_unique_verification_code();
		}
		
		return $new_code;
	}

	function add_verification($uid, $verification_code, $verification_email, $add_time, $add_ip)
	{
		$data['uid'] = $uid;
		$data['verification_code'] = $verification_code;
		$data['verification_email'] = $verification_email;
		$data['add_time'] = $add_time;
		$data['add_ip'] = $add_ip;
		
		$this->insert('users_email_verification', $data);
	}

	/**
	 * 发送验证邮件
	 * Enter description here ...
	 * @param unknown_type $email
	 * @param unknown_type $verification_code
	 */
	function send_verification_email($email)
	{
		$verification_code = $this->get_unique_verification_code();
		$add_ip = ip2long($_SERVER['REMOTE_ADDR']);
		$this->add_verification(USER::get_client_uid(), $verification_code, $email, time(), $add_ip);
		$url = $this->setting['base_url'] . "/account/?c=email_verification&act=verification&email=" . urlencode($email) . "&vcode=" . $verification_code;
		$account_class = $this->model('account');
		$user_name = $account_class->get_real_name_by_uid(USER::get_client_uid());
		$content = "<p>" . $user_name . ", 你好</p>";
		$content .= '<p>' . get_setting('site_name') . '帐号邮箱验证</p>
						<p class="answer" style="border-top: 1px solid #DDDDDD;margin: 15px 0 25px;padding: 15px;">
						验证您的邮箱，
						请点击：<br/><a href="' . $url . '">' . $url . '</a></p>
						<p>若您在' . get_setting('site_name') . '上没有帐号，请忽略本邮件。</p>';
		
		$this->model('email')->send('', $email, $user_name, $user_name . " 验证您的邮箱", $content);
	}

	function get_verification_by_email($email)
	{
		$email = mysql_escape_string(trim($email));
		
		if (empty($email))
		{
			return false;
		}
		
		return $this->fetch_row('users_email_verification', "verification_email = '{$this->quote($email)}'");
	}

	function verification_code_exists($verification_code)
	{
		return $this->fetch_row('users_email_verification', "verification_code='{$verification_code}'");
	}

	function get_verification_by_id($verification_id)
	{
		return $this->fetch_row('users_email_verification', "verification_id='" . intval($verification_id) . "'");
	}

	/**
	 * 根据邀请码获得邀请表信息
	 * Enter description here ...
	 * @param ing $verification_code
	 */
	function get_verification_by_code($verification_code)
	{
		return $this->verification_code_exists($verification_code);
	}

	/**
	 * 生成混淆码
	 *
	 * @access      private
	 * @param       int     length        长度
	 * @return      string
	 */
	function fetch_salt($length = 4)
	{
		$salt = '';
		for ($i = 0; $i < $length; $i ++)
		{
			$salt .= chr(rand(97, 122));
		}
		
		return $salt;
	}

	/**
	 * 校验验证码是否有效
	 * Enter description here ...
	 * @param string $verification_code
	 * @return bool
	 */
	function check_code_available($verification_code)
	{
		return $this->fetch_row('users_email_verification', "active_status='0' AND active_expire<>'1' AND verification_code='{$verification_code}'");
	}

	/**
	 * 激活验证码
	 * Enter description here ...
	 * @param string $verification_code	邀请码
	 * @param int $active_time	激活时间
	 * @param unknown_type $active_ip	激活IP
	 * @param unknown_type $active_uid	激活用户ID
	 */
	function verification_code_active($verification_code, $active_time, $active_ip)
	{
		$data['verification_code'] = $verification_code;
		$data['active_time'] = $active_time;
		$data['active_ip'] = $active_ip;
		$data['active_status'] = "1";
		$data['active_expire'] = "1";
		
		return $this->update('users_email_verification', $data, "verification_code = '{$verification_code}'");
	}

}