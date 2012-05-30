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

class c_ajax_class extends GZ_CONTROLLER
{
	function setup()
	{
		HTTP::no_cache_header();
	}

	public function weibo_bind_action()
	{
		if (get_setting('qq_t_enabled') == 'Y')
		{
			$sina_weibo = $this->model("sina_weibo")->get_users_sina_by_uid($this->user_id);
			
			$data['qq_weibo']['name'] = $qq_weibo['name'];
		}
		
		if (get_setting('sina_weibo_enabled') == 'Y')
		{
			$qq_weibo = $this->model("qq_weibo")->get_users_qq_by_uid($this->user_id);
		
			$data['sina_weibo']['name'] = $sina_weibo['name'];
		}
		
		$data['qq_weibo']['enabled'] = get_setting('qq_t_enabled');
		$data['sina_weibo']['enabled'] = get_setting('sina_weibo_enabled');
		
		H::ajax_json_output(GZ_APP::RSM($data, 1, ''));
	}

	function welcome_message_template_action()
	{
		if ($this->user_info['birthday']) //默认的信息
		{
			TPL::assign('birthday_y_s', date('Y', $this->user_info["birthday"]));
			TPL::assign('birthday_m_s', date('m', $this->user_info["birthday"]));
			TPL::assign('birthday_d_s', date('d', $this->user_info["birthday"]));
		}
		
		/**
		 * 邮箱补充,看是否有需要
		 */
		
		if (! H::isemail($this->user_info["email"]))
		{
			TPL::assign('email_add', TRUE);
		}
		else
		{
			TPL::assign('email_add', FALSE);
		}
		
		//年符值
		$year_end = date("Y") * 1;
		$year_array[0] = "";
		
		for ($tmp_i = $year_end; $tmp_i > 1900; $tmp_i --)
		{
			$year_array[$tmp_i] = $tmp_i;
		}
		
		TPL::assign('birthday_y', $year_array);
		
		//月符值
		TPL::assign('birthday_m', array(
			"0" => "", 
			"01" => "01", 
			"02" => "02", 
			"03" => "03", 
			"04" => "04", 
			"05" => "05", 
			"06" => "06", 
			"07" => "07", 
			"08" => "08", 
			"09" => "09", 
			"10" => "10", 
			"11" => "11", 
			"12" => "12"
		));
		
		//日符值
		

		$day_array[0] = '';
		
		for ($tmp_i = 1; $tmp_i <= 31; $tmp_i ++)
		{
			$day_array[$tmp_i] = $tmp_i;
		}
		
		TPL::assign('birthday_d', $day_array);
		
		TPL::output('account/welcome_message_template');
	}

	function welcome_get_questions_action()
	{
		$questions_list = $this->model('question')->get_questions(5, 'rand()');
		
		foreach ($questions_list as $key => $question_info)
		{
			$questions_list[$key]['focus'] = $this->model('question')->has_focus_question($question_info['question_id'], $this->user_id);
		}
		
		TPL::assign('questions_list', $questions_list);
		
		if ($_GET['version'] == 3)
		{
			TPL::output('account/welcome_get_questions_ajax_v3');
		}
		else
		{
			TPL::output('account/welcome_get_questions_ajax_v2');
		}
	}

	function welcome_get_topics_action()
	{
		$topics_list = $this->model('topic')->get_topic_list_v2(12, 'rand()');
		
		foreach ($topics_list as $key => $topic)
		{
			$topics_list[$key]['focus'] = $this->model('topic')->has_focus_topic($this->user_id, $topic['topic_id']);
		}
		
		TPL::assign('topics_list', $topics_list);
		
		if ($_GET['version'] == 3)
		{
			TPL::output('account/welcome_get_topics_ajax_v3');
		}
		else
		{
			TPL::output('account/welcome_get_topics_ajax_v2');
		}
	}

	function welcome_get_users_action()
	{
		$users_list = $this->model('account')->get_activity_random_users(6);
		
		foreach ($users_list as $key => $val)
		{
			$user_job = $this->model("account")->get_user_jobs_by_uids($val['uid']);
			
			$users_list[$key]['user_job'] = $user_job[$val['uid']];
			$users_list[$key]['focus'] = $this->model('follow')->user_follow_check($this->user_id, $val['uid']);
		}
		
		TPL::assign('users_list', $users_list);
		
		TPL::output('account/welcome_get_users_ajax_v3');
	}

	function clean_first_login_action()
	{
		$this->model('account')->clean_first_login($this->user_id);
		
		die('success');
	}

	function update_signature_action()
	{
		if (! $this->user_id)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '请先登录'));
		}
		
		$this->model('account')->update_signature($this->user_id, $_POST['signature']);
		
		H::ajax_json_output(GZ_APP::RSM(null, 1, '签名更新成功'));
	}

	/**
	 * 微博分享
	 */
	function weibo_push_ajax_action()
	{
		if (! $this->user_id)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '请先登录'));
		}
		
		if (! $_POST['push_message'])
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '请输入分享内容'));
		}
		
		if (! $_POST['push_qq'] and ! $_POST['push_sina'])
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '请选择要分享的微博'));
		}
		
		if ($_POST['push_qq'] AND get_setting('qq_t_enabled') == 'Y')
		{
			if ($openid_info = $this->model('qq_weibo')->get_users_qq_by_uid($this->user_id))
			{
				Services_Tencent_OpenSDK_Tencent_Weibo::init(get_setting('qq_app_key'), get_setting('qq_app_secret'));
				
				$result = Services_Tencent_OpenSDK_Tencent_Weibo::call('t/add', array(
					'content' => $_POST['push_message']
				), 'POST');
				
				if ($result['errcode'] > 0)
				{
					H::ajax_json_output(GZ_APP::RSM(null, '-1', "QQ 微博发送失败，错误：{$result['msg']}"));
				}
				else
				{
					//分享成功,加积分
					//自己
				//	$this->model("integral")->set_user_integral("SHARE", $this->user_id, "腾讯微博分享成功");
				}
			}
		}
		
		if ($_POST['push_sina'] AND get_setting('sina_weibo_enabled') == 'Y')
		{
			if ($openid_info = $this->model('sina_weibo')->get_users_sina_by_uid($this->user_id))
			{
				$client = new Services_Weibo_WeiboClient(get_setting('sina_akey'), get_setting('sina_skey'), $openid_info['oauth_token'], $openid_info['oauth_token_secret']);
				
				$result = $client->update($_POST['push_message']); //发送微博
				

				if ($result['error_code'] > 0)
				{
					H::ajax_json_output(GZ_APP::RSM(null, '-1', "新浪微博发送失败，错误：{$result['error_code']}: {$result['error']}"));
				}
				else
				
				{
					//分享成功,加积分
					//自己
					//$this->model("integral")->set_user_integral("SHARE", $this->user_id, "新浪微博分享成功");
				}
			}
		}
		
		H::ajax_json_output(GZ_APP::RSM(null, 1, '分享发送成功'));
	}

	public function send_invite_question_mail_action()
	{
		if (! $this->user_id)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '请先登录'));
		}
		
		if (! $_POST['email_message'])
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '请输入邮件内容'));
		}
		
		if (! H::isemail($_POST['email_address']))
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '邮件地址格式错误'));
		}
		
		if($_POST['email_address'] == $this->user_info['email'])
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '不能发送给自己'));
		}
		
		$invite_url = get_setting('base_url') . '/account/?c=register&act=step1&invite_question_id=' . (int)$_POST['question_id'] . '&source=' . urlencode($this->model('account')->get_source_hash($this->user_info['email']));
		
		$this->model('email')->send("", $_POST['email_address'], '', $this->user_info['user_name'] . ' 邀请你来 ' . get_setting('site_name') . ' 回答问题', nl2br($_POST['email_message']));
		
		H::ajax_json_output(GZ_APP::RSM(null, 1, '邀请发送成功'));
	}
	
	public function get_draft_action()
	{
		echo json_encode($this->model('draft')->get_data($_GET['item_id'], $_GET['type'], $this->user_id));
	}
	
	public function delete_draft_action()
	{
		if (!$_POST['item_id'] OR !$_POST['type'])
		{
			die;
		}
		
		$this->model('draft')->delete_draft($_POST['item_id'], $_POST['type'], $this->user_id);
		
		H::ajax_json_output(GZ_APP::RSM(null, 1, '草稿删除成功'));
	}
	
	public function save_draft_action()
	{
		if (!$_GET['item_id'] OR !$_GET['type'] OR !$_POST)
		{
			die;
		}
		
		$this->model('draft')->save_draft($_GET['item_id'], $_GET['type'], $this->user_id, $_POST);
		
		H::ajax_json_output(GZ_APP::RSM(null, 1, '已保存草稿，' . date('Y-m-d H:i:s', time())));
	}
}