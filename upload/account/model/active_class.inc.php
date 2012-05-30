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

class active_class extends GZ_MODEL
{

	function active_code_generate()
	{
		$active_code = md5(uniqid(rand()));
		
		$active_code = strtolower(md5($active_code));
		
		$active_code = substr($active_code, 0, 20);
		
		return $active_code;
	}

	/**
	 * 激活代码检查
	 * @param $active_code
	 * @param $active_type
	 * @return
	 */
	function active_code_check($active_code, $active_type)
	{
		
		$active_code = trim($active_code);
		$active_type = $active_type * 1;
		
		if ($active_code != str_replace(array(
			"　", 
			"?", 
			"", 
			"", 
			"", 
			' '
		), '', $active_code)) //字符检验
		{
			return false;
		}
		
		if (! preg_match("/^[0-9A-Za-z]+$/", $active_code))
		{
			return false;
		}
		
		if ($active_code == "")
		{
			return false;
		}

		if ($this->count('active_tbl', "active_type='{$active_type}' AND active_code='{$active_code}' AND expire_time > '" . time() . "' AND ((active_time is NULL AND active_ip is NULL)OR(active_time='' AND active_ip=''))"))
		{
			return false;
		}
		
		return true;
	
	}

	/**
	 * 激活代码激活
	 * @param $active_code
	 * @param $active_type	1-电子邮件激活	2修改邮件  -11找回密码
	 * @return
	 */
	
	function active_code_active($active_code, $active_type)
	{
		
		$active_code = trim($active_code);
		$active_type = $active_type * 1;
		
		if ($active_code != str_replace(array(
			"　", 
			"?", 
			"", 
			"", 
			"", 
			' '
		), '', $active_code)) //字符检验
		{
			return false;
		}
		
		$rs = $this->fetch_row('active_tbl', "active_type = '{$active_type}' AND active_code = '{$active_code}' AND ((active_time is NULL AND active_ip is NULL) OR (active_time = '' AND active_ip = ''))");
		
		$uid = $rs['uid']; //临时表的用户ID //找回密码的时候是正式表用户的ID
		

		if (! $rs)
		{
			return false;
		}
		
		//修改激活表状态
		$active_arr["active_time"] = time();
		$active_arr["active_ip"] = ip2long(real_ip());
		$active_arr["active_expire"] = 1;
		
		if (! $this->update('active_tbl', $active_arr, "active_id={$rs["active_id"]}"))
		{
			return false;
		}
		
		//执行激活修改的动作
		switch ($active_type)
		{
			case 0 :
				
				break;
			case 1 :
				$update_arr["user_status"] = 1;
				
				$this->model('account')->update_users_fields($update_arr, $uid);
				
				return $uid;
			
			case 2 : //修改电子邮件
				$update_arr["user_email"] = $rs["active_values"];
				
				return $this->model('account')->update_user_fields($update_arr, $uid);
				break;
			
			case 11 :
				return $uid;
			
			case 21 :
				return $uid;
		
		}
		
		return true;
	
	}

	/**
	 * 激活数据添加
	 * @param $userid
	 * @param $expre_time
	 * @param $active_code
	 * 
	 */
	function active_add($userid, $expire_time, $active_code, $active_type, $active_values = "", $active_type_code = "")
	{
		
		//传入数据处理
		$insert_arr['uid'] = $userid * 1;
		$insert_arr['expire_time'] = $expire_time * 1;
		$insert_arr['active_code'] = FORMAT::safe(trim($active_code));
		$insert_arr['active_type'] = $active_type * 1;
		$insert_arr['active_values'] = FORMAT::safe(trim($active_values)); //激活的附加数据,可为空		
		$insert_arr['active_type_code'] = FORMAT::safe(trim($active_type_code)); //激活类型,可为空
		

		//添加必要字段
		$insert_arr['add_time'] = time();
		$insert_arr['add_ip'] = ip2long(real_ip());
		
		//插入获取用户ID
		

		$active_id = $this->insert('active_tbl', $insert_arr);
		
		//旧记录进行过期处理
		

		if ($active_id)
		{
			$update_arr["active_expire"] = 1;
			
			$this->update('active_tbl', $update_arr, "uid = '{$userid}' AND active_type = '{$active_type}' AND active_id <> '{$active_id}'");
		}
		
		return $active_id;
	
	}

	/**
	 * 獲取激活码状态
	 * @param $active_code
	 * @return
	 */
	function get_active_code_row($active_code, $active_type = 0)
	{
		$active_code = trim($active_code);
		$active_type = $active_type * 1;
		
		if ($active_code != str_replace(array(
			"　", 
			"?", 
			"", 
			"", 
			"", 
			' '
		), '', $active_code)) //字符检验
		{
			return false;
		}
		
		if (! preg_match("/^[0-9A-Za-z]+$/", $active_code))
		{
			return false;
		}
		
		if ($active_code == "")
		{
			return false;
		}
		
		return $this->fetch_row('active_tbl', "active_type = '{$active_type}' AND active_code = '{$active_code}'");
	
	}
}