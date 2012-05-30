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

class c_qq_class extends GZ_CONTROLLER
{

	public function get_access_rule()
	{
		$rule_action["rule_type"] = 'white'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["actions"] = array();
		
		return $rule_action;
	}
	
	public function setup()
	{
		if (get_setting('qq_t_enabled') != 'Y')
		{
			die;
		}
	}

	function binding_action()
	{
		$this->model('qq_weibo')->init(get_setting('base_url') . '/account/?c=qq&act=callback');
	}

	function callback_action()
	{
		Services_Tencent_OpenSDK_Tencent_Weibo::init(get_setting('qq_app_key'), get_setting('qq_app_secret'));
		
		if (Services_Tencent_OpenSDK_Tencent_Weibo::getAccessToken($_GET['oauth_verifier']) and $uinfo = Services_Tencent_OpenSDK_Tencent_Weibo::call('user/info'))
		{
			$this->model('integral')->set_user_integral('BIND_WEIBO', $this->user_id, '你博绑定成功');
			
			$this->model('qq_weibo')->bind_account($uinfo, '/account/?c=setting&act=accountbind', $this->user_id);
		}
		else
		{
			H::js_pop_msg('与微博通信出错, 请重新登录.', '/account/?c=setting&act=accountbind');
		}
	}

	function del_bind_action()
	{
		$this->model('qq_weibo')->del_users_by_uid($this->user_id);
		
		HTTP::redirect('/account/?c=setting&act=accountbind');
	}
}
	