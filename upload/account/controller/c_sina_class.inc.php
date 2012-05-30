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

class c_sina_class extends GZ_CONTROLLER
{

	public function get_access_rule()
	{
		$rule_action["rule_type"] = 'white'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["actions"] = array();
		
		return $rule_action;
	}
	
	public function setup()
	{
		if (get_setting('sina_weibo_enabled') != 'Y')
		{
			die;
		}
	}

	function binding_action()
	{
		$oauth = new Services_Weibo_WeiboOAuth(get_setting('sina_akey'), get_setting('sina_skey'));
		
		$_SESSION['sina_keys'] = $oauth->getRequestToken();
		
		HTTP::redirect($oauth->getAuthorizeURL($_SESSION['sina_keys']['oauth_token'], false, get_setting('base_url') . "/account/?c=sina&act=binding_callback"));
	}

	function binding_callback_action()
	{	
		$oauth = new Services_Weibo_WeiboOAuth(get_setting('sina_akey'), get_setting('sina_skey'), $_SESSION['sina_keys']['oauth_token'], $_SESSION['sina_keys']['oauth_token_secret']);
		
		$last_key = $oauth->getAccessToken($_REQUEST['oauth_verifier']);
		
		$client = new Services_Weibo_WeiboClient(get_setting('sina_akey'), get_setting('sina_skey'), $last_key['oauth_token'], $last_key['oauth_token_secret']);
		
		$sina_profile = $client->verify_credentials();
		
		if ($sina_profile["id"] <= 0)
		{
			H::js_pop_msg('与微博通信出错, 请重新登录.', "/account/?c=setting&act=accountbind");
		}
		
		//添加积分
		//$this->model("integral")->set_user_integral("BIND_WEIBO", $this->user_id, '微博绑定');
		
		$this->model('sina_weibo')->bind_account($sina_profile, '/account/?c=setting&act=accountbind', $this->user_id, $last_key['oauth_token'], $last_key['oauth_token_secret']);
	
	}

	function del_bind_action()
	{
		$this->model('sina_weibo')->del_users_by_uid($this->user_id);
		
		HTTP::redirect("/account/?c=setting&act=accountbind");
	}
}
	