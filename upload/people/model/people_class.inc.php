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

class people_class extends GZ_MODEL
{
	var $top_num = 50;	// TOP 总数

	/**
	 * 格式化TOP50排名列表
	 * 
	 * @param  $list
	 */
	public function people_format_list($list)
	{
		$account_obj = $this->model('account');		

		$follow_obj = $this->model('follow');		
		$uids = array();
		
		foreach ($list as $key => $val)
		{
			$uids[] = $val['uid'];
		}
		
		$uids = array_unique($uids);
		
		if (empty($uids))
		{
			return $list;
		}
		
		$user_areas = $account_obj->get_user_areas_by_uids($uids);		
		$user_jobs = $account_obj->get_user_jobs_by_uids($uids);		
		$user_follows = $follow_obj->get_people_focus_by_uids($uids);
		
		foreach ($list as $key => $val)
		{
			$list[$key]["url"] = $val["url"] ? $val["url"] : "?u=" . $val["uid"];
			$list[$key]["url"] = get_setting('base_url') . "/people/" . $list[$key]["url"];
			$list[$key]['area_name'] = $user_areas[$val['uid']];
			$list[$key]['job_name'] = $user_jobs[$val['uid']];
			$list[$key]['follow'] = $user_follows[$val['uid']];
		}
		
		return $list;
	}

	/**
	 * 判断用户是否在积分top列表里
	 * 
	 * @param int $uid 用户id 
	 */
	public function in_integral_top_list($uid)
	{
		$uid = intval($uid);
		
		if (! $uid)
		{
			return false;
		}
		
		$cache_key_top_uids = "integral_top_{$this->top_num}_uids";		
		$top_uids = ZCACHE::get($cache_key_top_uids);
		
		if ($top_uids === false)
		{
			$user_list = $this->get_integral_top_list('0, ' . $this->top_num);			
			$top_uids = array();
			
			foreach ($user_list as $key => $val)
			{
				$top_uids[] = $val['uid'];
			}
			
			ZCACHE::set($cache_key_top_uids, $top_uids, null, 60);
		}
		
		if (in_array($uid, $top_uids))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * 积分top列表
	 *
	 * @param string $limit limit范围
	 * @param bool $update_time 为true时返回列表的更新时间
	 */
	public function get_integral_top_list($limit = '', $update_time = false)
	{
		if (empty($limit))
		{
			$limit = "0, {$this->top_num}";
		}
		
		$cache_key = ZCACHE::format_key("integral_top_list_" . $limit);		
		$user_list = ZCACHE::get($cache_key);
		
		if ($user_list === false)
		{
			$user_list = $this->model('account')->get_users_list('', $limit, false, false, 'integral DESC');			
			$user_list = $this->people_format_list($user_list);			
			$user_list['update_time'] = time();
			
			ZCACHE::set($cache_key, $user_list, null, 60);
		}
		
		if ($update_time)
		{
			return $user_list['update_time'];
		}
		
		unset($user_list['update_time']);
				
		return $user_list;
	}
	
	//更新个人首页计数
	public function update_views_count($uid)
	{
		return $this->query('UPDATE ' . $this->get_table('users') . ' SET views_count = views_count + 1 WHERE uid = ' . intval($uid));
	}
}