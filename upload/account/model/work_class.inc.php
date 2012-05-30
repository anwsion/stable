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

class work_class extends GZ_MODEL
{
	/**
	 * 添加工作经历
	 *  
	 *  @param  $start_year
	 *  @param  $start_month	
	 *  @param  $end_year
	 *  @param  $end_month
	 *  @param  $company_name
	 *  @param  $experience_description
	 *  @param  $jobs_id
	 *  @param  $country
	 *  @param  $province
	 *  @param  $city
	 *  @param  $district
	 */
	function add_work_experience($uid, $start_year, $start_month, $end_year, $end_month, $company_name, $experience_description, $jobs_id, $country, $province, $city, $district)
	{
		$insert_arr["uid"] = $uid * 1;
		$insert_arr["start_year"] = $start_year * 1;
		$insert_arr["start_month"] = $start_month * 1;
		$insert_arr["end_year"] = $end_year * 1;
		$insert_arr["end_month"] = $end_month * 1;
		$insert_arr["company_name"] = htmlspecialchars($company_name);
		$insert_arr["jobs_id"] = $jobs_id * 1;
		$insert_arr["country"] = $country * 1;
		$insert_arr["province"] = $province * 1;
		$insert_arr["city"] = $city * 1;
		$insert_arr["district"] = $district * 1;
		$insert_arr["add_time"] = mktime();
		
		//插入获取用户ID
		return $this->insert('work_experience', $insert_arr);
	}

	/**
	  * 获取职位信息
	  */
	function get_jos_list()
	{
		return $this->fetch_all('jobs');
	}

	/**
	  * 根据职位ID获取职位信息
	  */
	function get_jobs_by_id($jobs_id)
	{
		return $this->fetch_row('jobs', 'jobs_id = ' . intval($jobs_id));
	}

	/**
	  * 通过用户ID 获取 教育经历
	  * 
	  * @param $uid
	  * 
	  * @return array
	  */
	function get_work_experience_list($uid)
	{
		return $this->fetch_all('work_experience', "uid = " . intval($uid));
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
		return $this->fetch_row('work_experience', 'education_id = ' . intval($education_id) . ' AND uid = ' . intval($uid));
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
		return $this->update('work_experience', $update_arr, 'education_id = ' . intval($education_id) . ' AND uid = ' . intval($uid));
	}

	/**
	  * 删除学校经历
	  * 
	  * @param  $education_id
	  * @param  $uid
	  */
	function del_work_experience($work_id, $uid)
	{
		return $this->delete('work_experience', 'uid = ' . intval($uid) . ' AND work_id = ' . intval($work_id));
	}

	/**
	  * 通过用户ID 获取 工作经历
	  * 
	  * @param $uid
	  * 
	  * @return array
	  */
	function get_work_experience_row($work_id, $uid)
	{
		return $this->fetch_row('work_experience', 'work_id = ' . intval($work_id) . ' AND uid =' . intval($uid));
	}

	/**
	 * 更新学校经历
	 * 
	 * @param  $update_arr
	 * @param  $education_id
	 * @param  $uid
	 */
	function update_work_experience($update_arr, $work_id, $uid)
	{
		$uid = $uid * 1;
		$work_id = $work_id * 1;
		
		if ((! $uid) || (! $work_id))
		{
			return false;
		}
		
		return $this->update('work_experience', $update_arr, "uid={$uid} AND work_id='{$work_id}'");
	}
}