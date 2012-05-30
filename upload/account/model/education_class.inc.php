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

class education_class extends GZ_MODEL
{

	/**
	 * 添加学校信息
	 * 
	 * @param int $user_id 用户ID
	 * @param int $school_type 学校类型  0：其它  1：小学  2：初中  3：中技  4：高中  5：大学
	 * @param str $school_name_tmp 学校名称(仅显示用)
	 * @param int $years		年份
	 * @param str hostel	宿舍
	 * 
	 */
	
	function add_education_experience($uid, $school_type, $school_name, $years, $departments = '')
	{
		$insert_arr["uid"] = $uid * 1;
		$insert_arr["school_type"] = $school_type * 1;
		$insert_arr["school_name"] = htmlspecialchars(trim($school_name));
		$insert_arr["education_years"] = $years * 1;
		$insert_arr['departments'] = htmlspecialchars(trim($departments));
		$insert_arr["add_time"] = mktime();
		
		//插入获取用户ID
		return $this->insert('education_experience', $insert_arr);
	}

	/**
	  * 通过用户ID 获取 教育经历
	  * 
	  * @param $uid
	  * 
	  * @return array
	  */
	function get_education_experience_list($uid)
	{
		$uid = $uid * 1;
		
		if (! $uid)
		{
			return false;
		}
		
		return $this->fetch_all('education_experience', "uid = {$uid}");
	
	}

	/**
	  * 通过用户ID 获取 教育经历
	  * 
	  * @param $uid
	  * 
	  * @return array
	  */
	function get_education_experience_row($education_id, $uid)
	{
		$uid = $uid * 1;
		$education_id = $education_id * 1;
		
		if (! $uid)
		{
			return false;
		}
		
		$rs = $this->fetch_row('education_experience', "education_id='{$education_id}' AND uid='{$uid}'");
		
		if ($rs)
		{
			return $rs;
		}
		else
		{
			return false;
		}
	}

	/**
	 * 更新学校经历
	 * 
	 * @param  $update_arr
	 * @param  $education_id
	 * @param  $uid
	 */
	function update_education_experience($update_arr, $education_id, $uid)
	{
		$uid = $uid * 1;
		$education_id = $education_id * 1;
		
		if ((! $uid) || (! $education_id))
		{
			return false;
		}
		
		return $this->update('education_experience', $update_arr, "uid={$uid} AND education_id='$education_id'");
	
	}

	/**
	  * 删除学校经历
	  * 
	  * @param  $education_id
	  * @param  $uid
	  */
	function del_education_experience($education_id, $uid)
	{
		return $this->delete('education_experience', "uid = " . intval($uid) . " AND education_id = " . intval($education_id));
	}
}