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

class follow_class extends GZ_MODEL
{

	/**
	 * 添加关注
	 * 
	 * @param  $fans_uid			用户UID
	 * @param  $friend_uid  所关注的用户UID
	 * 
	 * @return bool
	 */
	function user_follow_add($fans_uid, $friend_uid)
	{
		
		$fans_uid = $fans_uid * 1;
		$friend_uid = $friend_uid * 1;
		$account_obj = $this->model('account');
		
		if (! $fans_uid || ! $friend_uid)
		{
			return false;
		}
		
		if ($fans_uid == $friend_uid)
		{
			return false;
		}

		if (! $account_obj->check_uid($fans_uid) || ! $account_obj->check_uid($friend_uid))
		{
			return false;
		}
		
		//存在了就返回错误
		if ($this->user_follow_check($fans_uid, $friend_uid))
		{
			return false;
		}
		else
		{
			
			$insert_arr['fans_uid'] = $fans_uid;
			$insert_arr['friend_uid'] = $friend_uid;
			$insert_arr['add_time'] = mktime();
			
			
			//插入关注
			//$rs=DB::_insert_tbl_filed( $insert_arr, $this->_user_follow_tbl);//先插入表
			$rs = $this->insert("user_follow", $insert_arr);
			
			//发送系统邮件通知
			$email_class = $this->model('email');
			$email_class->follow($fans_uid, $friend_uid);
			
			return $rs;
		}
	
	}

	/**
	 * 检查关注是否存在
	 * 
	 * @param  $fans_uid			用户UID
	 * @param  $friend_uid		  所关注的用户UID
	 * 
	 * @return bool
	 */
	function user_follow_check($fans_uid, $friend_uid)
	{
		$fans_uid = $fans_uid * 1;
		$friend_uid = $friend_uid * 1;
		
		if (! $fans_uid || ! $friend_uid)
		{
			return false;
		}
		
		if ($fans_uid == $friend_uid)
		{
			return false;
		}
		
		$rs = $this->count('user_follow', "fans_uid='{$fans_uid}' AND friend_uid='{$friend_uid}'");
		
		return $rs;
	
	}

	/**
	 * 删除关注
	 * 
	 * @param  $fans_uid	用户UID
	 * @param  $friend_uid	被关注的UID
	 */
	function user_follow_del($fans_uid, $friend_uid)
	{
		
		$fans_uid = $fans_uid * 1;
		$friend_uid = $friend_uid * 1;
		
		if (! $fans_uid || ! $friend_uid)
		{
			return false;
		}	
			//不存在了就返回错误
		if (! $this->user_follow_check($fans_uid, $friend_uid))
		{
			return false;
		}
		else
		{
			
			$where_str = " fans_uid='{$fans_uid}' AND friend_uid='{$friend_uid}'";
			
			return $this->delete("user_follow", $where_str);
		
		}
	
	}

	/**
	 * 
	 * @param  $uids
	 */
	function get_people_focus_by_uids($uids)
	{
		if (empty($uids))
		{
			return false;
		}
		
		$uids = array_unique($uids);

		$rs = $this->fetch_all('user_follow', "friend_uid IN (" . implode(",", $uids) . ") AND fans_uid='" . USER::get_client_uid() . "'");
		
		if (empty($rs))
		{
			return false;
		}
		
		$follow_array = array();
		
		foreach ($rs as $key => $val)
		{
			$follow_array[] = $val['friend_uid'];
		}
		
		$data = array();
		
		foreach ($uids as $key => $val)
		{
			if (in_array($val, $follow_array))
			{
				$data[$val] = true;
			}
		}
		
		return $data;
	}

	/**
	 * 获取单个用户的粉丝列表
	 * 
	 * @param  $friend_uid		
	 * @param  $limit
	 */
	function get_user_fans($friend_uid, $limit = 20)
	{
		$friend_uid = $friend_uid * 1;
		
		if (! $friend_uid)
		{
			return false;
		}
		
		if ($limit)
		{
			$limit = "limit " . $limit;
		}
		$sql = "SELECT UF.*,MEM.uid,MEM.user_name,MEM.real_name,MEM.avatar_file,MEB.signature
		  FROM   " . $this->get_table("user_follow") . " AS UF 
		  LEFT JOIN " . $this->get_table("users") . " AS MEM ON UF.fans_uid=MEM.uid 
		  LEFT JOIN " . $this->get_table("users_attrib") . " AS MEB ON UF.fans_uid=MEB.uid
		  WHERE friend_uid='{$friend_uid}' AND MEM.UID>0 AND  (MEM.valid_email=1 AND MEM.forbidden=0)  {$limit}";

		
		return $this->query_all($sql);
	
	}

	/**
	 * 获取单个用户的关注列表(我关注的人)
	 * 
	 * @param  $friend_uid
	 * @param  $limit
	 */
	function get_user_friends($fans_uid, $limit = 20)
	{
		$fans_uid = $fans_uid * 1;
		
		if (! $fans_uid)
		{
			return false;
		}
		
		if ($limit)
		{
			$limit = "limit " . $limit;
		}
		
		$sql = "SELECT UF.*,
				MEM.uid,MEM.user_name,MEM.real_name,MEM.avatar_file,
				MEB.introduction,MEB.signature
				FROM " . $this->get_table("user_follow") . " AS UF 
				LEFT JOIN " . $this->get_table("users") . " AS MEM 
				ON UF.friend_uid=MEM.uid
				LEFT JOIN " . $this->get_table("users_attrib") . " AS MEB				
				ON UF.friend_uid=MEB.uid
				WHERE fans_uid='{$fans_uid}' AND MEM.uid>0  AND  (MEM.valid_email=1 AND MEM.forbidden=0) {$limit}";
		
		
		return $this->query_all($sql);
		
	}

	/**
	 * 获取多个用户的关注列表
	 * 
	 * @param  $fans_uid_array
	 * @param  $limit
	 */
	function get_users_friends($fans_uid_array, $limit = 20)
	{		
		if ((! $fans_uid_array) || (! is_array($fans_uid_array)))
		{
			
			return false;
		}
		
		if ($limit)
		{
			$limit = "limit " . $limit;
		}
		$fans_uids = implode(",", $fans_uid_array);
		
		if (! $fans_uids)
		{
			return false;
		}
		
		$sql = "SELECT UF.*,
				MEM.uid,MEM.user_name,MEM.real_name,MEM.avatar_file,
				MEB.introduction,MEB.signature,UF.fans_uid  
				FROM " . $this->get_table("user_follow") . " AS UF 
				LEFT JOIN " . $this->get_table("users") . " AS MEM 
				ON UF.friend_uid=MEM.uid
				LEFT JOIN " . $this->get_table("users_attrib") . " AS MEB				
				ON UF.friend_uid=MEB.uid
				WHERE fans_uid IN  ({$fans_uids}) AND  (MEM.valid_email=1 AND MEM.forbidden=0) {$limit}";
		
		return $this->query_all($sql);
	
	}

	/**
	 * 获得用户粉丝数量
	 */
	function get_fans_count($friend_uid)
	{
		return $this->count('user_follow', ' friend_uid=' . intval($friend_uid));
	}

	/**
	 * 获得用户关注的人的数量
	 */
	function get_friends_count($fans_uid)
	{
		return $this->count('user_follow',  ' fans_uid= ' . intval($fans_uid));
	}

	/**
	 * 粉丝数修改
	 * 
	 * @param  $uid
	 */
	function user_fans_count_edit($uid, $value = 1)
	{
		$fans_count = $this->get_fans_count($uid);
	
		$this->update("users", array(
								'fans_count' => $fans_count
								)
								, '  uid = ' . intval($uid));

		ZCACHE::cleanGroup("g_uid_" . $uid);
		
		return true;
	}

	/**
	 * 关注数修改
	 * 
	 * @param  $uid
	 */
	function user_friend_count_edit($uid, $value = 1)
	{
		$friend_count = $this->get_friends_count($uid);

		
		$this->update("users", array(
								"friend_count" => $friend_count
								), "  uid = " . intval($uid));
		
		//删除缓存
		

		ZCACHE::cleanGroup("g_uid_" . $uid);
		
		return true;
	}
}