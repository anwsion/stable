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

class invitation_class extends GZ_MODEL
{
	/**
     * 生成新的激活码
     */
	function get_unique_invitation_code($invitation_type = '')
	{
		$salt = $this->fetch_salt();
		
		$new_code = md5(uniqid(rand(), true) . $salt);
		
		if ($this->invitation_code_exists($new_code))
		{
			return $this->get_unique_invitation_code($invitation_type);
		}
		
		return $new_code;
	}

	function add_invitation($uid, $invitation_code, $invitation_email, $add_time, $add_ip)
	{
		$c_arr['uid'] = $uid;
		$c_arr['invitation_code'] = $invitation_code;
		$c_arr['invitation_email'] = $invitation_email;
		$c_arr['add_time'] = $add_time;
		$c_arr['add_ip'] = $add_ip;
		
		return $this->insert('invitation', $c_arr);
	}

	function get_invitation_by_email($email)
	{
		$email = mysql_escape_string(trim($email));
		
		if (! $email)
		{
			return false;
		}
		
		return $this->fetch_row('invitation', "invitation_email = '" . $email . "'");
	}

	function get_invitation_list($uid, $limit = "0, 10", $registed = FALSE, $orderby = "invitation_id desc")
	{
		
		
		if (! empty($uid))
		{
			$where[]= "  uid='{$uid}'";
		}
		
		if ($registed)
		{
			$where[] = "  active_status='1'";
		}
		
		return $this->fetch_all('invitation', implode(' AND ',$where), $orderby, $limit);
	}

	/**
	 * 判断邀请码是否存在
	 * 
	 * @param  $invitation_code
	 * @return 
	 */
	function invitation_code_exists($invitation_code)
	{
		return $this->fetch_row('invitation', "invitation_code = '{$invitation_code}'");
	}

	/**
	 * 根据邀请ID获得邀请记录
	 * @param unknown_type $invitation_id
	 */
	function get_invitation_by_id($invitation_id)
	{
		return $this->fetch_row('invitation', 'invitation_id=' . intval($invitation_id));
	}

	/**
     * 
     * 或取可用激活数组(通过邀请id和用户名)
     * @param unknown_type $invitation_id
     * @param unknown_type $uid
     */
	function get_active_invitation_by_id($invitation_id, $uid)
	{
		$invitation_id = intval($invitation_id);
		
		$uid = intval($uid);
		
		if ((! $invitation_id) || (! $uid))
		{
			return false;
		}
		
		return $this->fetch_row('invitation', 'uid=' . $uid . ' AND invitation_id = ' . $invitation_id . ' AND active_status = 1');
	}

	/**
     * 更新邀请字段信息
     * 
     * @param  $update_arr		更新数组
     * @param  $invitation_id	邀请id
     * @return boolean
     */
	function update_invitation_fields($update_arr, $invitation_id)
	{
		return $this->update('invitation', $update_arr, 'invitation_id=' . intval($invitation_id));
	}

	/**
     * 根据邀请码获得邀请表信息
     * Enter description here ...
     * @param ing $invitation_code
     */
	function get_invitation_by_code($invitation_code)
	{
		if (empty($invitation_code))
		{
			return false;
		}
		
		return $this->fetch_row('invitation', "invitation_code='{$invitation_code}'");
	}

	/**
     * 生成混淆码
     *
     * @access      private
     * @param       int     length        长度
     * @return      string
     */
	function fetch_salt($length = 4)
	{
		$salt = '';
		
		for ($i = 0; $i < $length; $i ++)
		{
			$salt .= chr(rand(97, 122));
		}
		
		return $salt;
	}

	/**
     * 校验邀请码有效
     * @param string $invitation_code
     * @return bool
     */
	function check_code_available($invitation_code)
	{
		if (! $invitation_code)
		{
			return false;
		}
		
		return $this->fetch_row('invitation', "active_status=0 AND active_expire <> 1 AND invitation_code='{$invitation_code}'");
	}

	/**
     * 激活邀请码
     * @param string $invitation_code	邀请码
     * @param int $active_time	激活时间
     * @param unknown_type $active_ip	激活IP
     * @param unknown_type $active_uid	激活用户ID
     * @param unknown_type $active_uid	邀请回答问题 ID
     */
	function invitation_code_active($invitation_code, $active_time, $active_ip, $active_uid, $object_type, $object_id)
	{
		$c_arr['invitation_code'] = $invitation_code;
		$c_arr['active_time'] = $active_time;
		$c_arr['active_ip'] = $active_ip;
		$c_arr['active_uid'] = $active_uid;
		$c_arr['active_status'] = 1;
		$c_arr['object_type'] = intval($object_type);
		$c_arr['object_id'] = intval($object_id);
		
		return $this->update('invitation', $c_arr, "invitation_code='{$invitation_code}' and active_status=0");
	}

	function get_invitation_list_by_ids($invitation_ids)
	{
		if (empty($invitation_ids))
		{
			return false;
		}
		
		$invitation_ids = array_unique($invitation_ids);
		
		$rs = $this->fetch_all('invitation', "invitation_id IN (" . implode(",", $invitation_ids) . ")");
		
		if (empty($rs))
		{
			return false;
		}
		
		$data = array();
		
		foreach ($rs as $key => $val)
		{
			$data[$val['invitation_id']] = $val;
		}
		
		return $data;
	}

}