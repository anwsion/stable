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

class sina_weibo_class extends GZ_MODEL
{
	function check_sina_id($sina_id)
	{
		return $this->count('users_sina', 'id = ' . intval($sina_id));
	}

	function get_users_sina_by_id($sina_id)
	{
		return $this->fetch_row('users_sina', 'id = ' . intval($sina_id));
	}

	function get_users_sina_by_uid($uid)
	{
		return $this->fetch_row('users_sina', 'uid = ' . intval($uid));
	}

	function update_token($id, $access_token, $oauth_token_secret)
	{
		return $this->update('users_sina', array(
			'oauth_token' => $access_token, 
			'oauth_token_secret' => $oauth_token_secret
		), "id = '" . (int)$id . "'");
	}

	function del_users_by_uid($uid)
	{
		return $this->delete('users_sina', 'uid = ' . intval($uid));
	}

	function users_sina_add($id, $uid, $name, $location, $description, $url, $profile_image_url, $domain, $gender)
	{
		$id = $id * 1;
		$uid = $uid * 1;
		
		if (! $uid or ! $id)
		{
			return false;
		}
		
		$insert_arr['id'] = $id;
		$insert_arr['uid'] = $uid;
		$insert_arr['name'] = $this->quote($name);
		$insert_arr['location'] = $this->quote($location);
		$insert_arr['description'] = $this->quote($description);
		$insert_arr['url'] = $this->quote($url);
		$insert_arr['profile_image_url'] = $this->quote($profile_image_url);
		$insert_arr['domain'] = $this->quote($domain);
		$insert_arr['gender'] = $this->quote($gender);
		
		$insert_arr['add_time'] = mktime();
		
		return $this->insert('users_sina', $insert_arr);
	
	}

	function bind_account($sina_profile, $redirect, $uid, $oauth_token, $oauth_token_secret, $is_ajax = false)
	{
		$sina_id = $sina_profile["id"] * 1;
		$sina_name = $sina_profile["name"];
		$sina_location = $this->quote($sina_profile["location"]);
		
		$sina_description = $this->quote($sina_profile["sina_description"]);
		$sina_url = $this->quote($sina_profile["url"]);
		$sina_profile_image_url = $this->quote($sina_profile["sina_profile_image_url"]);
		
		$sina_domain = $this->quote($sina_profile["sina_domain"]);
		$sina_gender = $this->quote($sina_profile["sina_gender"]);
		
		$users_sina = $this->get_users_sina_by_id($sina_id);
		
		if ($openid_info = $this->get_users_sina_by_uid($uid))
		{
			if ($openid_info['id'] != $sina_id)
			{
				if ($is_ajax)
				{
					H::ajax_json_output(GZ_APP::RSM(null, "-1", "此账号已经与另外一个微博绑定"));
				}
				else
				{
					H::js_pop_msg('此账号已经与另外一个微博绑定', '/account/?c=login&act=logout');
				}
			
			}
		}
		
		if (! $users_sina)
		{
			$this->users_sina_add($sina_id, $uid, $sina_name, $sina_location, $sina_description, $sina_url, $sina_profile_image_url, $sina_domain, $sina_gender);
		
		}
		else if ($users_sina['uid'] != $uid)
		{
			if ($is_ajax)
			{
				H::ajax_json_output(GZ_APP::RSM(null, "-1", "此账号已经与另外一个微博绑定"));
			}
			else
			{
				H::js_pop_msg('微博账号已经被其他账号绑定', '/account/?c=setting&act=accountbind');
			}
		}
		
		$this->update_token($sina_id, $oauth_token, $oauth_token_secret);
		
		if ($redirect)
		{
			HTTP::redirect($redirect);
		}
	}
}
	