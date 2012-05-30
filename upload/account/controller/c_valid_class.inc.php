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

class c_valid_class extends GZ_CONTROLLER
{

	public function get_access_rule()
	{
		$rule_action["rule_type"] = "white"; // 'black'黑名单,黑名单中的检查;'white'白名单,白名单以外的检查
		$rule_action["actions"] = array();
		return $rule_action;
	}

	function setup()
	{
		HTTP::no_cache_header();
	}
	
	// 第一步AJAX处理
	function valid_email_step1_process_ajax_action()
	{
		if (! H::isemail($this->user_info['email']))
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '没有设置 E-mail'));
			exit();
		}
		
		if ($this->user_info['valid_email'] == 1)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "邮箱已经认证"));
			exit();
		}
		
		$active_code_hash = $this->model('active')->active_code_generate(); // 生成校验码
		$expre_time = time() + 60 * 60 * 24; // 24小时后过期
		
		$active_id = $this->model('active')->active_add($this->user_info['uid'], $expre_time, $active_code_hash, 21, '', 'VALID_EMAIL');
		
		$this->model('email')->valid_email($this->user_info['email'], $this->user_info['user_name'], $active_code_hash);
		
		H::ajax_json_output(GZ_APP::RSM(null, 1, "验证邮件发送成功,请去收件箱收取邮箱进行认证")); // 返回数据
	}
}