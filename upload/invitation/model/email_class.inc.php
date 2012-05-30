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

class email_class extends GZ_MODEL
{
	public $user_id;
	var $type = 0;
	
	const FOLLOW_ME = 11; //有人关注的了我
	const ASK_ME = 12; //有人问了我问题
	const INVIT_ME_ASK = 13; //有人邀请我回答问题
	const NEW_ANSWER = 14; //我关注的问题有新回复
	const NEW_MESSAGE = 15; //有人向我发送私信
	const EMAIL_VALIDATE = 101; //验证邮箱
	const INVITATION = 102; //邀请注册
	const FIND_PW = 103; //找回密码

	public function setup()
	{
		$this->user_id = USER::get_client_uid();
	}
	
	/**
     * 用户关注通知
     * Enter description here ...
     * @param int $from_uid	关注用户ID
     * @param int $to_uid	被关注用户ID
     */
	function follow($from_uid, $to_uid)
	{
		if ($from_uid == $to_uid)
		{
			return;
		}
		
		if (! $this->check_email_setting($to_uid, self::FOLLOW_ME))
		{
			return false;
		}
		
		$this->type = self::FOLLOW_ME;
		
		$user_info = $this->model('account')->get_users_by_uid($to_uid);
		$to_name = $this->model('account')->get_real_name_by_uid($to_uid);
		$from_name = $this->model('account')->get_real_name_by_uid($from_uid);
		
		$email = $user_info['email'];
		$title = $from_name . " 在 " . get_setting('site_name') . " 关注了你。";
		$people_url = get_setting('base_url') . "/people/?u=" . $from_uid;
		$content = "<p>" . $to_name . ", 你好</p>";
		$content .= '<p><b>' . $from_name . '</b> 在 ' . get_setting('site_name') . ' 上关注了你</p>
						<p class="answer" style="border-top: 1px solid #DDDDDD;margin: 5px 0 5px;padding: 2px;"></p>
						<p>要查看 <b>' . $from_name . '</b> 的个人主页，
						请点击：<br/><a href="' . $people_url . '">' . $people_url . '</a></p>';
		
		$this->send('', $email, $to_name, $title, $content);
	}

	/**
     * 问题通知
     * Enter description here ...
     * @param int $from_uid	来自用户ID，若为系统发出，则为0
     * @param int $to_uid	发往用户ID
     * @param int $question_id	问题ID
     * @param int $type
     */
	
	function question($from_uid, $to_uid, $question_id, $type)
	{
		if($to_uid == $this->user_id)
		{
			return false;
		}
		
		if (! $this->check_email_setting($to_uid, $type))
		{
			return false;
		}
		
		$this->type = $type;
		
		$user_info = $this->model('account')->get_users_by_uid($to_uid);
		$to_name = $this->model('account')->get_real_name_by_uid($to_uid);
		$from_name = $this->model('account')->get_real_name_by_uid($from_uid);
		
		if ($from_uid == 0)
		{
			$from_name = get_setting('site_name');
		}
		
		$question_class = $this->model('question');
		$question = $question_class->get_question_info_by_id($question_id);
		
		$question_a = "<a href=\"" . get_setting('base_url') . "/question/?act=detail&question_id=" . $question_id . "\">" . $question['question_content'] . "</a>";
		
		$email = $user_info['email'];
		
		switch ($type)
		{
			case 12 :
				$title = $from_name . " 在 " . get_setting('site_name') . " 问了你一个问题。";
				$header = '<p><b>' . $from_name . '</b> 在 ' . get_setting('site_name') . ' 问了你一个问题。</p>';
				break;
			case 13 :
				$title = $from_name . " 在 " . get_setting('site_name') . " 上邀请你回答问题。";
				$header = '<p><b>' . $from_name . '</b> 在 ' . get_setting('site_name') . ' 上邀请你回答问题。</p>';
				break;
			case 14 :
				$title = "您在 " . get_setting('site_name') . " 上关注的问题有了新回复。";
				$header = '<p>您在 ' . get_setting('site_name') . ' 上关注的问题有了新回复。</p>';
				break;
		}
		
		$content = "<p>" . $to_name . ", 你好</p>";
		$content .= $header;
		$content .= '<p class="answer" style="border-top: 1px solid #DDDDDD;margin: 5px 0 5px;padding: 2px;"></p>
						<p>要查看问题详细内容，
						请点击：<br/>“' . $question_a . '”</p>';
		
		$this->send('', $email, $to_name, $title, $content);
	}

	/**
     * 私信通知
     * Enter description here ...
     * @param int $from_uid	来自用户ID
     * @param int $to_uid	发往用户ID
     * @param int $message_id	消息ID
     */
	function message($from_uid, $to_uid, $message_id)
	{
		if (! $this->check_email_setting($to_uid, self::NEW_MESSAGE))
		{
			return false;
		}
		
		$this->type = self::NEW_MESSAGE;
		
		$user_info = $this->model('account')->get_users_by_uid($to_uid);
		$to_name = $this->model('account')->get_real_name_by_uid($to_uid);
		$from_name = $this->model('account')->get_real_name_by_uid($from_uid);
		
		$email = $user_info['email'];
		$title = $from_name . " 在 " . get_setting('site_name') . " 上发消息给你。";
		$url = get_setting('base_url') . "/inbox/?act=read_message&dialog_id=" . $message_id;
		$content = "<p>" . $to_name . ", 你好</p>";
		$content .= '<p><b>' . $from_name . '</b> 在 ' . get_setting('site_name') . ' 上发消息给你。</p>
						<p class="answer" style="border-top: 1px solid #DDDDDD;margin: 5px 0 5px;padding: 2px;"></p>
						<p>要查看消息详细，
						请点击：<br/><a href="' . $url . '">' . $url . '</a></p>';
		
		$this->send('', $email, $to_name, $title, $content);
	}

	/**
     * 邀请注册的
     * Enter description here ...
     * @param unknown_type $from_uid
     * @param unknown_type $email
     * @param unknown_type $invitation_code
     */
	function invitation($from_uid, $email, $invitation_code)
	{
		$from_uid = intval($from_uid) * 1;
		
		if (! $from_uid)
		{
			return false;
		}
		
		$this->type = self::INVITATION;
		
		$from_name = $this->model('account')->get_real_name_by_uid($from_uid);
		$title = $from_name . " 邀请你加入 " . get_setting('site_name') . ".";
		$url = get_setting('base_url') . "/account/?c=register&act=step1&email=" . urlencode($email) . "&icode=" . $invitation_code;
		$content .= '<p><b>' . $from_name . '</b> 邀请你加入 ' . get_setting('site_name') . '</p>
						<p>' . get_setting('site_name') . ' 是一个由大家共建的问答社区，这里也许没有标准回复，
						但有更多不同经验、观点和建议，我们希望能让最合适的人来回答最合适的问题。</p>
						<p class="answer" style="border-top: 1px solid #DDDDDD;margin: 5px 0 5px;padding: 2px;"></p>
						<p>请点击以下链接完成注册：<br  /><a href="' . $url . '">' . $url . '</a></p>';
		
		$this->send('', $email, "", $title, $content);
	}

	/**
     * 邀请注册的
     * Enter description here ...
     * @param unknown_type $from_uid
     * @param unknown_type $email
     * @param unknown_type $invitation_code
     */
	function apply_pass($email, $user_name, $invitation_code)
	{
		$this->type = self::INVITATION;
		
		$title = "您获得加入 " . get_setting('site_name') . " 资格.";
		$url = get_setting('base_url') . "/account/?c=register&act=step1&email=" . urlencode($email) . "&user_name=" . urlencode($user_name) . "&icode=" . $invitation_code;
		$content .= '<p>您好，<b>' . $user_name . '</b>， 您已获得加入 ' . get_setting('site_name') . ' 的资格</p>
						<p>' . get_setting('site_name') . ' 是一个由大家共建的问答社区，这里也许没有标准回复，
						但有更多不同经验、观点和建议，我们希望能让最合适的人来回答最合适的问题。</p>
						<p class="answer" style="border-top: 1px solid #DDDDDD;margin: 5px 0 5px;padding: 2px;"></p>
						<p>请点击以下链接完成注册：<br  /><a href="' . $url . '">' . $url . '</a></p>';
		
		$this->send('', $email, $user_name, $title, $content);
	}

	/**
     * 验证邮箱
     * Enter description here ...
     */
	function valid_email($email, $username, $active_code_hash)
	{
		$this->type = self::EMAIL_VALIDATE;
		
		$content = '<p>' . $username . ', 您好:</p>';
		$content .= '<p>这是您在 ' . get_setting('site_name') . ' 的重要邮件, 功能是进行 ' . get_setting('site_name') . ' 邮箱验证。</p>';
		$content .= '<p style="border-top: 1px solid #DDDDDD; margin: 15px 0 25px; padding: 15px;">请点击完成操作：<br/>';
		$content .= '<a href="' . get_setting('base_url') . '/account/?c=active&act=valid_email_active&key=' . $active_code_hash . '">' . get_setting('base_url') . '/account/?c=active&act=valid_email_active&key=' . $active_code_hash . '</a></p>';
		
		$this->send('', $email, $username, get_setting('site_name') . ' 邮箱验证', $content);
	}

	/**
	 * 找回密码
	 * @param $email
	 * @param $username
	 * @param $active_code_hash
	 * @return bool
	 */
	function find_password($email, $username, $active_code_hash)
	{
		$url = get_setting('base_url') . '/account/?c=active&act=find_password_active&key=' . $active_code_hash;
		$content = "<p>" . $username . ", 你好</p>";
		$content .= '<p>您提交了找回密码申请，请点击以下链接完成找回密码。</p>
				<p>如果您没有提交修改密码的申请，请忽略本邮件。</p>
				<p class="answer"  style="border-top: 1px solid #DDDDDD;margin: 15px 0 25px;padding: 15px;">
				点击链接重置帐号密码：<br/><a href="' . $url . '" target=_blank>' . $url . '</a>';

		return $this->send('', $email, $username, get_setting('site_name') . " 找回密码", $content);
	}

	/**
     * 发送邮件
     * Enter description here ...
     * @param string $from_name	署名
     * @param string $to_email	发往邮箱
     * @param string $to_name	对方称呼
     * @param string $title		标题
     * @param string $content	内容
     */
	function send($from_name = '', $to_email, $to_name, $title, $content)
	{
		if(empty($from_name))
		{
			$from_name = get_setting('site_name');
		}
		
		//载入邮件正文模板
		
		TPL::assign('content', $content);
		
		$body = TPL::output('invitation/email_template', false);
		
		$mail_status = load_class('core_mail')->send_mail('', $from_name, $to_email, $to_name, $title, $body);
		
		$this->save_email_history('', $to_email, $this->type, $title, $body, $from_name, '', $_SERVER['REMOTE_ADDR'], $to_name, time(), $mail_status);
		
		return $mail_status;
	}

	/**
     * 插入邮件发送记录表
     * Enter description here ...
     * @param unknown_type $from_email
     * @param unknown_type $to_email
     * @param unknown_type $type
     * @param unknown_type $title
     * @param unknown_type $body
     * @param unknown_type $from_name
     * @param unknown_type $note
     * @param unknown_type $add_ip
     * @param unknown_type $user_name
     * @param unknown_type $add_time
     * @param unknown_type $status
     */
	function save_email_history($from_email, $to_email, $type, $title, $body, $from_name, $note, $add_ip, $user_name, $add_time, $status)
	{
		$data['from_email'] = $from_email;
		$data['to_email'] = $to_email;
		$data['type'] = $type;
		$data['title'] = $title;
		$data['body'] = mysql_escape_string(trim($body));
		$data['from_name'] = $from_name;
		$data['note'] = $note;
		$data['add_ip'] = $add_ip;
		$data['user_name'] = $user_name;
		$data['add_time'] = $add_time;
		$data['state'] = $status;
		
		return $this->insert('users_email_history', $data);
	}

	/**
     * 获得用户email设置记录
     * Enter description here ...
     * @param unknown_type $uid
     */
	function get_email_setting($uid)
	{
		if (intval($uid) <= 0)
		{
			return false;
		}
		
		return $this->fetch_row('users_email_setting', 'uid = ' . intval($uid));
	}

	/**
     * 判断用户email设置
     * Enter description here ...
     * @param unknown_type $uid
     * @param unknown_type $type
     */
	function check_email_setting($uid, $type)
	{
		if ($type > 100)
		{
			return true;
		}
		
		$user_setting = $this->get_email_setting($uid);
		
		if ($user_setting['sender_' . $type] == "1")
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
     * 获得email上一次发送时间
     * Enter description here ...
     * @param unknown_type $email
     */
	function get_last_resend_time($email)
	{
		if (empty($email))
		{
			return 0;
		}
		
		$rs = $this->fetch_row('users_email_history', "to_email = '" . $email . "'", "id DESC");
		
		if ($rs)
		{
			return intval($rs['add_time']);
		}
		else
		{
			return 0;
		}
	}

}