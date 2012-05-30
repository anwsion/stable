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

class associate_index_class extends GZ_MODEL
{

	/**
	 * 通过用户id获取索引信息
	 * @param  $userid
	 * @return array
	 */
	function get_associate_index_list($count = false, $uid, $where = null, $limit = "10", $orderby = "")
	{
		$sql_where = "";
		
		$uid = intval($uid);
		
		if ($uid > 0)
		{
			$where .= " AND uid = " . $uid;
		}
		
		if ($count)
		{
			return $this->count('user_associate_index', $where);
		}
		else
		{
			return $this->fetch_all('user_associate_index', $where, $orderby, $limit);
		}
	}

	/**
	 *  添加提现数据库原始记录
	 * @param  $userid
	 * @return
	 */
	function add_associate_index($uid, $associate_id, $associate_type, $add_time = 0, $update_time = 0)
	{
		if ($update_time == 0)
		{
			//查找update 时间
			if (($associate_type == 1) || ($associate_type == 3))
			{
				$r = ACTION_LOG::get_actions_distint_by_where("
					associate_action IN (101,102,103,201,202,301,401,402)
					and associate_id = {$associate_id} 
					and associate_type='1'", 1);
				
				if ($r)
				{
					$update_time = $r[0]['add_time'];
				
				}
			}
			else if (($associate_type == 2) || ($associate_type == 4))
			{
				$r = ACTION_LOG::get_actions_distint_by_where("
						associate_action IN (101,102,103,201,202,301,401,402,501,502,503,504)
						and associate_id={$associate_id}
						and associate_type='4'", 1);
				if ($r)
				{
					$update_time = $r[0]['add_time'];
				
				}
			}
		
		}
		
		if (intval($add_time) == 0)
		{
			$add_time = mktime();
		}
		
		if (intval($update_time) == 0)
		{
			$update_time = mktime();
		}
		
		$insert_arr["uid"] = $uid * 1;
		$insert_arr["associate_id"] = $associate_id * 1;
		$insert_arr["associate_type"] = $associate_type * 1;
		$insert_arr["add_time"] = $add_time * 1;
		$insert_arr["update_time"] = $update_time * 1;
		
		$rs = $this->insert('user_associate_index', $insert_arr);
		
		return $rs;
	}

	/**
	 * 删除索引  
	 * @param  $uid
	 * @param  $associate_id
	 * @param  $associate_type
	 * @return  boolean
	 */
	function del_associate_index($uid, $associate_id, $associate_type)
	{
		
		$uid = $uid * 1;
		$associate_id = $associate_id * 1;
		$associate_type = $associate_type * 1;
		
		$where = " uid = {$uid} AND associate_id = {$associate_id} AND associate_type = {$associate_type} ";
		
		$rs = $this->delete('user_associate_index', $where);
		
		return $rs;
	}

	/**
	 * 更新更新时间
	 * @param  $associate_id
	 * @param  $associate_type
	 * @param  $update_time
	 * @return boolean
	 */
	function update_update_time($associate_id, $associate_type, $update_time = 0)
	{
		$associate_id = $associate_id * 1;
		$associate_type = $associate_type * 1;
		
		$update_time = intval($update_time);
		
		if ($update_time == 0)
		{
			$update_arr["update_time"] = mktime();
		}
		else
		{
			$update_arr["update_time"] = $update_time;
		}
		
		//顺带更新其它主表的时间
		//更新问题
		if (($associate_type == 1) || ($associate_type == 3))
		{
			$this->model("question")->update_question_field($associate_id, array(
				"update_time" => $update_arr["update_time"]
			));
		
		}
		
		return $this->update('user_associate_index', $update_arr, "associate_id = " . $associate_id . " AND associate_type = " . $associate_type);
	}

}