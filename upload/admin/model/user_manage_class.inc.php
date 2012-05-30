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

class user_manage_class extends GZ_MODEL
{

	function get_user_list($count = false, $condition = '', $limit = '0,10')
	{
		
		if ($condition != "")
		{
			$where = $condition;
		}
		
		if ($count)
		{
			return $this->count('users', $where);
		}
		
		return $this->fetch_all('users', $where, "uid DESC", $limit);
	}

	function users_apply_list($count = false, $condition = "", $limit = 20)
	{
		
		if ($condition != "")
		{
			$where = $condition;
		}
		
		if ($count)
		{
			return $this->count('users_apply', $where);
		}
		
		return $this->fetch_all('users_apply', $where, "id DESC", $limit);
	}

	function get_user_apply($apply_id)
	{
		return $this->fetch_row('users_apply', 'id = ' . $apply_id);
	}

	function set_user_apply_field($apply_id, $fields)
	{
		return $this->update('users_apply', $fields, 'id = ' . $apply_id);
	}

	function user_field_filter($user_array)
	{
		$sql = "DESC " . get_table('users');
		$user_fields = $this->query_all($sql);
		
		$field = array();
		
		foreach ($user_fields as $key => $val)
		{
			$field[] = $val['Field'];
		}
		
		foreach ($user_array as $key => $val)
		{
			if (! in_array($key, $field))
			{
				unset($user_array[$key]);
			}
		}
		
		return $user_array;
	}
}
