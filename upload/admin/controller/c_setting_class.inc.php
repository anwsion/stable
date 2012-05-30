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

class c_setting_class extends ADMIN_CONTROLLER
{

	public function setup()
	{
		$this->crumb("系统设置", "?c=setting");
	}

	public function index_action()
	{
		$this->setting_action();
	}

	public function setting_action()
	{
		$vars = $this->model('setting')->get_vars($this->_INPUT['group_id'], true);
		
		if(empty($this->_INPUT['group_id']))
		{
			$vars['ui_style']['select_list'] = $this->model('setting')->get_ui_styles();
		}

		TPL::assign('group_id', $this->_INPUT['group_id']);
		TPL::assign('vars', $this->model('setting')->format_setting_by_group($vars));
		
		TPL::output("admin/setting_index", true);
	}

	/**
	 * 保存设置
	 * Enter description here ...
	 */
	public function sys_save_ajax_action()
	{
		//过滤参数
		$vars = $this->model('setting')->check_vars($this->_INPUT);

		if(preg_match('/(.*)\/$/i', $vars['upload_dir']))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "上传文件存放绝对路径 结尾不带/"));
		}
		
		if(preg_match('/(.*)\/$/i', $vars['upload_url']))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "上传目录URL地址 结尾不带/"));
		}
		
		//设置参数
		$retval = $this->model('setting')->set_vars($vars);
		
		//保存到缓存文件
		$this->model('setting')->save_setting_config();
		
		if ($retval)
		{
			ZCACHE::delete("setting_config");
			
			H::ajax_json_output(GZ_APP::RSM(null, "1", "修改成功"));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "修改失败"));
		}
	}
	
	public function test_email_setting()
	{
		$smtp_config = array(
			'smtp_server' => $this->_INPUT['smtp_server'],
			'smtp_port' => $this->_INPUT['smtp_port'], 
			'smtp_username' => $this->_INPUT['smtp_username'],
			'stmp_password' => $this->_INPUT['stmp_password'],	
		);
		
		$core_mail =  new core_mail();
		
		$connect = $core_mail->connect(false, $this->_INPUT['email_type'], $smtp_config);
		
		if(!$connect)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "测试邮件发送失败，请检查邮箱服务器设置"));
		}
		
		$retval = $core_mail->send_mail(
				$this->_INPUT['from_email'],
				get_setting('site_name'), 
				$this->_INPUT['test_email'], 
				$this->_INPUT['test_email'], 
				get_setting('site_name') . " - 邮箱服务器配置测试", 
				'这是一封测试邮件，收到邮件表明网站邮箱服务器配置成功');
		
		if($retval)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "1", "测试邮件已发送，请查收邮件以确定配置是否正确"));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, "1", "测试邮件发送失败，请检查邮箱服务器设置"));
		}
	}
}