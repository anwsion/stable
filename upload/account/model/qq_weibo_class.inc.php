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

class qq_weibo_class extends GZ_MODEL
{
	function update_token($name, $access_token, $oauth_token_secret)
	{
		return $this->update('users_qq', array(
			'access_token' => $access_token, 
			'oauth_token_secret' => $oauth_token_secret
		), "name = '" . $this->quote($name) . "'");
	}

	function get_users_qq_by_name($name)
	{
		return $this->fetch_row('users_qq', "name = '" . $this->quote($name) . "'");
	}

	function get_users_qq_by_uid($uid)
	{
		return $this->fetch_row('users_qq', "uid = " . intval($uid));
	}

	function del_users_by_uid($uid)
	{
		return $this->delete('users_qq', "uid = " . intval($uid));
	}

	function users_qq_add($uid, $name, $nick, $location, $gender)
	{
		$insert_arr['uid'] = intval($uid);
		$insert_arr['name'] = $this->quote($name);
		$insert_arr['nick'] = $this->quote($nick);
		$insert_arr['location'] = $this->quote($location);
		$insert_arr['gender'] = $this->quote($gender);
		$insert_arr['add_time'] = mktime();
		
		//print_r($insert_arr); die;
		

		return $this->insert('users_qq', $insert_arr);
	}

	function bind_account($uinfo, $redirect, $uid, $is_ajax = false)
	{
		if ($openid_info = $this->get_users_qq_by_uid($uid))
		{
			if ($openid_info['name'] != $uinfo["data"]["name"])
			{
				if ($is_ajax)
				{
					H::ajax_json_output(GZ_APP::RSM(null, "-1", "QQ 微博账号已经被其他账号绑定."));
				}
				else
				{
					H::js_pop_msg('QQ 微博账号已经被其他账号绑定', '/account/?c=login&act=logout');
				}
			}
		}
		
		$users_qq = $this->get_users_qq_by_name($uinfo["data"]["name"]);
		
		if (! $users_qq)
		{
			$users_qq = $this->users_qq_add($uid, $uinfo["data"]["name"], $uinfo["data"]["nick"], $uinfo["data"]["location"], $uinfo["data"]["sex"]);
		}
		else if ($users_qq['uid'] != $uid)
		{
			if ($is_ajax)
			{
				H::ajax_json_output(GZ_APP::RSM(null, "-1", "QQ 微博账号已经被其他账号绑定."));
			}
			else
			{
				H::js_pop_msg('QQ 微博账号已经被其他账号绑定', '/account/?c=setting&act=accountbind');
			}
		}
		
		$this->update_token($uinfo["data"]["name"], $_SESSION[Services_Tencent_OpenSDK_Tencent_Weibo::ACCESS_TOKEN], $_SESSION[Services_Tencent_OpenSDK_Tencent_Weibo::OAUTH_TOKEN_SECRET]);
		
		if ($redirect)
		{
			HTTP::redirect($redirect);
		}
	}

	function init($callback)
	{
		Services_Tencent_OpenSDK_Tencent_Weibo::init(get_setting('qq_app_key'), get_setting('qq_app_secret'));
		
		$request_token = Services_Tencent_OpenSDK_Tencent_Weibo::getRequestToken($callback);
		
		$url = Services_Tencent_OpenSDK_Tencent_Weibo::getAuthorizeURL($request_token);
		
		HTTP::redirect($url);
	}
}
	