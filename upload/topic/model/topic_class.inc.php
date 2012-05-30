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

class topic_class extends GZ_MODEL
{

	/**
	 * 判断用户话题权限
	 * 
	 * @param  $uid 用户id
	 * @param  $users 用户组信息
	 */
	public function get_topic_permissions($uid, $users = null)
	{
		if (! is_array($users))
		{
			$users = $this->model('account')->get_users_by_uid($uid);
		}
		
		$ext_group_ids = $users['ext_group_ids'];
		$ext_group_ids_array = array();
		$permissions['edit'] = 0;
		
		if ($ext_group_ids != '')
		{
			$ext_group_ids_array = split(",", $ext_group_ids);
		}
		
		if (in_array('21', $ext_group_ids_array))
		{
			$permissions['edit'] = 1;
		}
		
		return $permissions;
	}

	/**
	 * 
	 * 根据指定条件获取话题数据
	 * @param string $where
	 * @param int    $limit
	 * 
	 * @return array
	 */
	public function get_topic_list($where = '', $limit = 10, $return_count = false)
	{
		if ($return_count)
		{
			return $this->count('topic', $where);
		}
		else
		{
			return $this->fetch_all('topic', $where, 'topic_id DESC', $limit);
		}
	}

	/**
	 * 
	 * 根据指定条件获取话题数据
	 * @param string $where
	 * @param int    $limit
	 * 
	 * @return array
	 */
	public function get_hot_topic_list($where = '', $limit = 10, $cache_time = 0)
	{
		return $this->fetch_all('topic', $where, 'topic_count DESC', $limit);
	}

	/**
	 * 
	 * 得到推荐到首页话题数量
	 * @param  $limit
	 * 
	 * @return array
	 */
	public function get_top_topic($limit = 10)
	{
		return $this->fetch_all('topic', "topic_top = 1", "topic_id DESC", $limit);
	}

	/**
	 * 
	 * 根据问题ID,批量得到话题集合
	 * @param array $question_ids
	 * 
	 * @return array
	 */
	public function get_topic_list_by_question_ids(array $question_ids)
	{
		if (empty($question_ids))
		{
			return array();
		}
		
		$topic_info_list = array();
		
		$topic_ids = array(
			0
		);
		
		$sql = "SELECT DISTINCT topic_id FROM " . $this->get_table("topic_question") . " AS t WHERE t.question_id IN (" . implode(",", $question_ids) . ")";
		
		$topic_list = $this->query_all($sql);
		
		foreach ($topic_list as $topic_info)
		{
			$topic_ids[] = $topic_info['topic_id'];
		}
		
		$topic_list = $this->get_topic_title($topic_ids);
		
		foreach ($topic_list as $topic_info)
		{
			$topic_info_list[$topic_info['topic_id']] = $topic_info; //$topic_info['topic_title'];
		}
		
		return $topic_info_list;
	}

	/**
	 * 
	 * 根据用户ID,得到用户关注话题列表
	 * @param int $uid
	 * 
	 * @return array(
	 * 		topic_title,       //话题标题
	 *	    add_time,          //添加时间
	 *		add_uid,           //添加用户ID
	 *		topic_count,       //问题总数
	 *		match_id,          //比赛ID
	 *		topic_description, //话题描述
	 *		topic_pic,         //话题图片
	 */
	public function get_focus_topic_list($count = false, $uid = 0, $limit = 20)
	{
		$uid = intval($uid);
		
		if ($uid == 0)
		{
			return array();
		}
		
		if ($count)
		{
			return $this->count('topic_focus', 'uid = ' . $uid);
		}
		else
		{
			$sql = "SELECT tp.* FROM " . $this->get_table('topic') . " AS tp RIGHT JOIN " . $this->get_table("topic_focus") . " AS t ON t.topic_id = tp.topic_id WHERE t.uid = " . $uid . " ORDER BY t.focus_id DESC ";
			return $this->query_all($sql, $limit);
		}
	}

	/**
	 * 
	 * 批量得到用户关注的话题
	 * @param array $uids
	 * 
	 * @return array
	 */
	public function get_focus_topic_by_uids(array $uids)
	{
		$topic_ids = array(
			0
		);
		
		$topic_info_list = array();
		
		if (empty($uids))
		{
			return array();
		}
		
		$sql = "SELECT T.*,TF.uid FROM " . $this->get_table("topic_focus") . " AS TF 
		LEFT JOIN " . $this->get_table('topic') . " T ON TF.topic_id=T.topic_id WHERE TF.uid IN (" . implode(",", $uids) . ") GROUP BY TF.topic_id";
		
		$topic_list = $this->query_all($sql);
		
		if ($topic_list)
		{
			foreach ($topic_list as $topic_info)
			{
				$topic_info_list[$topic_info['topic_id']] = $topic_info;
			}
			
			return $topic_info_list;
		}
		else
		{
			return array();
		}
	
	}

	/**
	 * 
	 * 根据用户ID，得到不同其它用户，做为感兴趣用户返回
	 * @param int $uid
	 * 
	 * @return array
	 */
	public function get_other_focus_uids_from_uid($uid)
	{
		$uid = intval($uid);
		
		if ($uid == 0)
		{
			return array();
		}
		
		$sql = "SELECT topic_id FROM " . $this->get_table("topic_focus") . " WHERE uid = " . $uid;
		
		$topic_ids = $this->query_all($sql);
		
		if ($topic_ids)
		{
			foreach ($topic_ids as $key => $val)
			{
				$topic_ids_array[] = $val['topic_id'];
			}
			
			$topic_ids_str = implode(',', $topic_ids_array); // 指量话题ID
			
			//得到话题id关注的用户,排除用户id
			$sql = "SELECT T.topic_id, T.topic_title, TF.uid FROM " . $this->get_table('topic') . " AS T RIGHT JOIN " . $this->get_table("topic_focus") . " AS TF ON T.topic_id = TF.topic_id WHERE TF.uid <> " . $uid . " GROUP BY uid";
			
			return $this->query_all($sql);
		
		}
		else
		{
			return false;
		}
	
	}

	/**
	 * 
	 * 根据话题ID,得到父类话题 
	 * @param int $topic_id
	 * 
	 * @return array
	 */
	public function get_topic_parent_id($topic_id)
	{
		if (! $topic_id)
		{
			return false;
		}
		
		$sql = "SELECT tp.topic_title, tp.topic_id FROM " . $this->get_table('topic_tree') . " AS tpl, " . $this->get_table('topic') . " AS tp WHERE tpl.topic_id = tp.topic_id AND tpl.topic_child_id = " . intval($topic_id);
		
		return $this->query_all($sql);
	}

	/**
	 * 
	 * 根据话题ID,得到子类话题 
	 * @param int $topic_id
	 * 
	 * @return array
	 */
	public function get_topic_childs($topic_id, $limit = '')
	{
		$topic_id = intval($topic_id);
		
		if ($topic_id == 0)
		{
			return false;
		}
		
		$sql = "SELECT topic_title, topic_id FROM " . $this->get_table('topic') . " WHERE parent_id = " . $topic_id;
		
		return $this->query_all($sql, $limit);
	}

	/**
	 * 
	 * 根据话题ID,得到子类话题 
	 * @param int $topic_id
	 * 
	 * @return array
	 */
	public function get_topic_tree_list($max = 5)
	{
		$sql = "SELECT topic_id, topic_title FROM " . $this->get_table('topic') . " WHERE is_top = 1 ORDER BY topic_id ASC";
		
		$top_topics = $this->query_all($sql);
		
		if (empty($top_topics))
		{
			return array();
		}
		
		$top_topic_ids = array();
		
		foreach ($top_topics as $key => $val)
		{
			$top_topic_ids[] = $val['topic_id'];
		}
		
		$sql = "SELECT topic_id, topic_title, parent_id FROM " . $this->get_table('topic') . " WHERE parent_id IN (" . implode(',', $top_topic_ids) . ") ORDER BY topic_id ASC";
		
		$child_topics = $this->query_all($sql);
		
		$child_topics_list = array();
		
		foreach ($child_topics as $key => $val)
		{
			if (count($child_topics_list[$val['parent_id']]) < $max)
			{
				$child_topics_list[$val['parent_id']][$val['topic_id']] = $val;
			}
		}
		
		foreach ($top_topics as $key => $val)
		{
			$top_topics[$key]['childs'] = $child_topics_list[$val['topic_id']];
		}
		
		return $top_topics;
	}

	/**
	 * 
	 * 根据话题ID数组,指量得到子类话题 
	 * @param int $topic_id
	 * 
	 * @return array
	 */
	public function get_topics_child_id($topic_ids_array, $where = '')
	{
		if (! is_array($topic_ids_array))
		{
			return false;
		}
		
		$topic_ids = implode(",", $topic_ids_array);
		
		if ($topic_ids == '')
		{
			return false;
		}
		
		$sql = "SELECT tp.topic_title,tp.topic_id FROM " . $this->get_table('topic_tree') . " AS tpl, " . $this->get_table('topic') . " AS tp WHERE tpl.topic_child_id = tp.topic_id  AND tpl.topic_id IN( " . $topic_ids . ") ";
		
		if ($where != '')
		{
			$sql .= " AND " . $where;
		}
		return $this->query_all($sql);
	}
	
	//获取话题图片
	function get_pic_url($topic_id = 0, $size = 32, $pic_file = null)
	{
		$size = in_array($size, array(
			'32', 
			'50', 
			'100'
		)) ? $size : '50';
		
		if (! $pic_file)
		{
			return '';
		}
		else
		{
			return str_replace('32_32', $size . '_' . $size, $pic_file);
		}
	}

	/**
	 * 
	 * 判断话题是否已经关联话题
	 * @param int $topic_id 
	 * @param int $topic_child_id
	 * 
	 * @return boolean true|false
	 */
	public function has_topic_lnk_topic($topic_id, $topic_child_id)
	{
		//得到所有相关联的ID
		$child_ids = $this->display_topic_tree($topic_child_id);
		$child_ids = substr($child_ids, 1, strlen($child_ids));
		$child_ids_array = explode(',', $child_ids);
		$child_ids_array = array_unique($child_ids_array);
		
		if (in_array($topic_id, $child_ids_array))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * 
	 * 判断从父类开始递归是否完结
	 * @param int $topic_id
	 * @param int $end_topic_id
	 * 
	 * @return boolean true|false
	 */
	public function has_finish_tree($topic_id, $end_topic_id)
	{
		$sql = "SELECT topic_id FROM " . $this->get_table('topic_tree') . " WHERE topic_id = " . intval($topic_id) . " AND topic_child_id = " . intval($end_topic_id);
		
		$retval = $this->query_row($sql);
		
		if (isset($retval['topic_id']) && $retval['topic_id'] > 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * 
	 * 显示话题UL树形
	 * @param int $topic_id
	 * @param int $end_topic_id
	 * 
	 * @return array
	 */
	public function display_topic_ul_tree($topic_id, $end_topic_id)
	{
		$topic_id = intval($topic_id);
		$topic_ids = ',' . $topic_id;
		$sql = "SELECT topic_id, topic_child_id FROM  " . $this->get_table('topic_tree') . " WHERE topic_id = " . $topic_id;
		
		$topinfo_recordset = $this->query_row($sql);
		
		if (isset($topinfo_recordset) && $topinfo_recordset['topic_child_id'] > 0)
		{
			$recordset = $topinfo_recordset;
			
			if ($recordset['topic_child_id'] != $end_topic_id)
			{
				
				$topic_ids .= $this->display_topic_ul_tree($recordset['topic_child_id'], $end_topic_id);
			}
			else
			{
				$topic_ids .= ',' . $recordset['topic_child_id'];
			}
		
		}
		return $topic_ids;
	}

	/**
	 * 
	 * 判断是否存在子类,显示话题树类
	 * @param int $topic_id
	 * 
	 * @return boolean true|false
	 */
	public function has_child($topic_id)
	{
		$sql = 'SELECT topic_id FROM ' . $this->get_table('topic_tree') . ' WHERE topic_child_id = ' . intval($topic_id);
		
		$retval = $this->query_row($sql);
		
		if (isset($retval['topic_id']) && $retval['topic_id'] > 0)
		{
			return $retval['topic_id'];
		}
		else
		{
			return 0;
		}
	}

	/**
	 * 
	 * 批量判断是否存在
	 * @param array $topic_ids
	 * 
	 * @return array
	 */
	public function has_child_by_ids($topic_ids)
	{
		$sql = 'SELECT topic_id, topic_child_id FROM ' . $this->get_table('topic_tree') . ' WHERE topic_child_id IN(' . implode(',', $topic_ids) . ')';
		
		return $this->query_all($sql);
	}

	/**
	 * 
	 * 显示话题树形
	 * @param int $topic_id
	 * 
	 * @return array
	 */
	public function display_topic_tree($topic_id)
	{
		$topic_id = intval($topic_id);
		$topic_ids = ',' . $topic_id;
		
		$sql = 'SELECT topic_id, topic_child_id FROM  ' . $this->get_table('topic_tree') . ' WHERE topic_child_id = ' . $topic_id;
		
		$topinfo_recordset = $this->query_all($sql);
		
		foreach ($topinfo_recordset as $recordset)
		{
			if ($this->has_child($recordset['topic_id']))
			{
				$topic_ids .= $this->display_topic_tree($recordset['topic_id']);
			}
			else
			{
				$topic_ids .= "," . $recordset['topic_id'] . "," . $recordset['topic_child_id'];
			}
		}
		
		return $topic_ids;
	}

	/**
	 * 批量获取话题标题
	 * @param array $topic_ids
	 * 
	 * @return array
	 */
	public function get_topic_title(array $topic_ids)
	{
		$sql = 'SELECT topic_id, topic_title,topic_pic FROM ' . $this->get_table('topic') . " WHERE topic_id IN (" . implode(",", $topic_ids) . ")";
		
		return $this->query_all($sql);
	}

	/**
	 * 
	 * 得到单条话题内容
	 * @param int $topic_id 话题ID
	 * 
	 * @return array
	 */
	public function get_topic($topic_id)
	{
		static $topics;
		
		if (! $topics[$topic_id])
		{
			$topics[$topic_id] = $this->fetch_row('topic', 'topic_id = ' . intval($topic_id));
		}
		
		return $topics[$topic_id];
	}

	/**
	 * 获取单条话题-通过话题名称
	 * @param  $topic_title 
	 */
	public function get_topic_by_title($topic_title)
	{
		if (! $topic_title)
		{
			return false;
		}
		
		static $topics;
		
		if (! $topics[$topic_title])
		{
			$topics[$topic_title] = $this->fetch_row('topic', "topic_title = '" . $this->quote($topic_title) . "'");
		}
		
		return $topics[$topic_title];
	}

	/**
	 * 获取单条话题id-通过话题名称
	 * @param  $topic_title
	 */
	public function get_topic_id_by_title($topic_title)
	{
		if (! $topic_title)
		{
			return false;
		}
		
		$topic = $this->get_topic_by_title($topic_title);
		
		return $topic['topic_id'];
	}

	/**
	 * 
	 * 得到话题关注数量
	 * @param array $topic_ids
	 * 
	 * @return int 
	 */
	public function get_topic_focus_count(array $topic_ids)
	{
		if (empty($topic_ids))
		{
			return 0;
		}
		
		return $this->count('topic_focus', 'topic_id IN(' . implode(',', $topic_ids) . ')');
	}

	/**
	 * 
	 * 增加话题内容
	 * @param int    $question_id       //操作附加ID,如话题就为话题ID,比赛就为比赛ID
	 * @param string $topic_title       //话题标题
	 * @param int    $add_uid           //添加用户ID
	 * @param int    $topic_count       //问题总数
	 * @param int    $competitions_id   //比赛ID
	 * @param string $topic_description //话题描述
	 * @param string $topic_pic         //话题图片
	 * @param int    $topic_lock        //话题是否锁定
	 * @param in     $topic_type        //1添加问题话题  2添加话题话题 3比赛话题
	 * 
	 * @return boolean true|false
	 */
	public function save_topic($question_id = 0, $topic_title, $add_uid = 0, $topic_count = 0, $competition_id = 0, $topic_description = '', $topic_pic = '', $topic_lock = 0, $topic_type = 1)
	{
		if (! $add_uid)
		{
			return false;
		}
		
		//判断是否存在话题
		$topic_id = $this->has_topic($topic_title);
		$question_id = intval($question_id);
		$competition_id = intval($competition_id);
		$retval = false;
		
		if ($topic_id)
		{
			//添加问题添加到话题的动作
			if ($question_id > 0)
			{
				ACTION_LOG::save_action($add_uid, $question_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::ADD_TOPIC, $topic_title, $topic_id);
			}
			
			return $topic_id;
		}
		
		//判断话题是否锁定
		if ($this->has_lock_topic($topic_id))
		{
			return false;
		}
		
		$data = array(
			'topic_title' => htmlspecialchars(trim($topic_title)), 
			'add_time' => time(), 
			'add_uid' => intval($add_uid), 
			'topic_count' => intval($topic_count), 
			'topic_description' => htmlspecialchars(trim($topic_description)), 
			'topic_pic' => htmlspecialchars(trim($topic_pic)), 
			'topic_lock' => intval($topic_lock)
		);
		
		//更新用户话题数量
		$topic_id = $this->insert('topic', $data);
		
		//记录日志
		if ($topic_type == 1)
		{
			//添加问题添加到话题的动作
			ACTION_LOG::save_action($add_uid, $question_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::ADD_TOPIC, $topic_title, $topic_id);
			ACTION_LOG::save_action($add_uid, $topic_id, ACTION_LOG::CATEGORY_TOPIC, ACTION_LOG::ADD_TOPIC, $topic_title, $question_id);
		}
		else if ($topic_type == 2)
		{
			//ACTION_LOG::save_action(USER::get_client_uid(), $topic_id, ACTION_LOG::CATEGORY_TOPIC, ACTION_LOG::ADD_TOPIC_PARENT, $topic_title, '');
		}
		else if ($topic_type == 3)
		{
			ACTION_LOG::save_action($add_uid, $topic_id, ACTION_LOG::CATEGORY_TOPIC, ACTION_LOG::ADD_TOPIC, $topic_title, $competition_id);
		}
		
		return $topic_id;
	}

	/**
	 * 
	 * 删除话题内容
	 * @param int $topic_id 话题ID
	 * @param int $question_id 话题关联类型ID
	 * 
	 * @return boolean true|false
	 */
	public function delete_topic($topic_id, $question_id, $competition_id, $action_log = true, $delete_record = false)
	{
		if (intval($topic_id) == 0)
		{
			return false;
		}
		
		$topic_info = $this->get_topic($topic_id);
		
		if ($question_id > 0)
		{
			$this->delete('topic_question', ' topic_id = ' . intval($topic_id) . ' AND question_id = ' . intval($question_id));
			
			if ($action_log)
			{
				ACTION_LOG::save_action(USER::get_client_uid(), $topic_id, ACTION_LOG::CATEGORY_TOPIC, ACTION_LOG::DELETE_TOPIC, $topic_info['topic_title'], $question_id);
			}
		}
		
		if ($competition_id > 0)
		{
			$this->delete('topic_question', ' topic_id = ' . intval($topic_id) . ' AND competition_id = ' . intval($competition_id));
			
			if ($action_log)
			{
				ACTION_LOG::save_action(USER::get_client_uid(), $topic_id, ACTION_LOG::CATEGORY_TOPIC, ACTION_LOG::DELETE_TOPIC, $topic_info['topic_title'], $competition_id);
			}
		}
		
		if (($question_id == 0) && ($competition_id == 0))
		{
			$this->delete("topic_question", 'topic_id = ' . intval($topic_id));
			
			if ($action_log)
			{
				ACTION_LOG::save_action(USER::get_client_uid(), $topic_id, ACTION_LOG::CATEGORY_TOPIC, ACTION_LOG::DELETE_TOPIC, $topic_info['topic_title']);
			}
		}
		
		if ($delete_record)
		{
			$this->delete('topic', 'topic_id = ' . intval($topic_id));
		}
		
		return true;
	}

	/**
	 * 
	 * 增加话题回答数量
	 * @param int $topic_id 话题ID
	 * 
	 * @return boolean true|false
	 */
	public function increase_topic_count($topic_id)
	{
		return $this->query('UPDATE ' . $this->get_table('topic') . " SET topic_count = topic_count + 1 WHERE  topic_id = " . intval($topic_id));
	}

	/**
	 * 更新话题内容
	 * @param int    $topic_id             //话题ID
	 * @param string $topic_title       //话题标题
	 * @param string $topic_description //话题描述
	 * @param string $topic_pic         //话题图片
	 * @param int    $match_id             //比赛ID
	 * @param int    $topic_count          //问题总数
	 *  
	 * @return boolean true|false
	 */
	public function update_topic($topic_id, $topic_title, $topic_description = '', $topic_pic = '', $topic_count = 0, $competitions_id = 0, $is_top = '', $topic_lock = '', $parent_id = '')
	{
		$topic_title = FORMAT::safe(trim($topic_title), true);
		$topic_description = FORMAT::safe(trim($topic_description), true);
		$topic_pic = FORMAT::safe(trim($topic_pic), true);
		$topic_count = intval($topic_count);
		$competitions_id = intval($competitions_id);
		$topic_info = $this->get_topic($topic_id); //得到话题信息
		$retval = '';
		$UPDATE_str = '';
		
		$sql = 'UPDATE ' . $this->get_table('topic') . ' SET topic_id = ' . intval($topic_id);
		
		if (! empty($topic_title))
		{
			$UPDATE_str .= ", topic_title = '" . $topic_title . "'";
		}
		if (! empty($topic_description))
		{
			$UPDATE_str .= ", topic_description = '" . $topic_description . "'";
		}
		if (! empty($topic_pic))
		{
			$UPDATE_str .= ", topic_pic = '" . $topic_pic . "'";
		}
		if ($topic_count > 0)
		{
			$UPDATE_str .= ", topic_count = '" . $topic_count . "'";
		}
		/*if ($competitions_id > 0)
		{
			$UPDATE_str .= ", competitions_id = '" . $competitions_id . "'";
		}*/
		if ($is_top >= 0)
		{
			$UPDATE_str .= ", is_top = '" . $is_top . "'";
		}
		if ($topic_lock >= 0)
		{
			$UPDATE_str .= ", topic_lock = '" . $topic_lock . "'";
		}
		if ($parent_id >= 0)
		{
			$UPDATE_str .= ", parent_id = '" . $parent_id . "'";
		}
		if (strlen($UPDATE_str) > 0)
		{
			$sql .= $UPDATE_str . " WHERE topic_id = " . intval($topic_id);
			
			$retval = $this->query($sql);
		}
		
		//记录日志
		if (! empty($topic_title))
		{
			ACTION_LOG::save_action(USER::get_client_uid(), $topic_id, ACTION_LOG::CATEGORY_TOPIC, ACTION_LOG::MOD_TOPIC, $topic_title, $topic_info['topic_title']);
		}
		if (! empty($topic_description))
		{
			ACTION_LOG::save_action(USER::get_client_uid(), $topic_id, ACTION_LOG::CATEGORY_TOPIC, ACTION_LOG::MOD_TOPIC_DESCRI, $topic_description, $topic_info['topic_description']);
		}
		if (! empty($topic_pic))
		{
			ACTION_LOG::save_action(USER::get_client_uid(), $topic_id, ACTION_LOG::CATEGORY_TOPIC, ACTION_LOG::MOD_TOPIC_PIC, $topic_pic, $topic_info['topic_pic']);
		}
		
		return $retval;
	}

	/**
	 * 
	 * 锁定/解锁话题
	 * @param int $topic_id
	 * @param int $topic_lock
	 * 
	 * @return boolean true|false
	 */
	public function lock_topic_by_id($topic_id, $topic_lock = 0)
	{
		$topic_id = intval($topic_id);
		
		if ($topic_id == 0)
		{
			return false;
		}
		
		return $this->UPDATE('topic', array(
			'topic_lock' => $topic_lock
		), 'topic_id = ' . $topic_id);
	
	}

	/**
	 * 
	 * 删除话题
	 * @param int $topic_id
	 * @param int $uid
	 * 
	 * @return boolean true|false
	 */
	public function delete_topic_by_topic_id($topic_id, $uid)
	{
		ACTION_LOG::save_action(USER::get_client_uid(), $topic_id, ACTION_LOG::CATEGORY_TOPIC, ACTION_LOG::DELETE_TOPIC, '', $uid);
		
		return $this->delete('topic', ' topic_id = ' . intval($topic_id) . ' AND add_uid = ' . intval($uid));
	}

	/**
	 * 
	 * 增加话题关联数据表
	 * @param int $topic_id
	 * @param int $topic_child_id
	 * 
	 * @return boolean true|false
	 */
	public function add_lnk_2_topic($topic_id, $topic_child_id)
	{
		$topic_id = intval($topic_id);
		$topic_child_id = intval($topic_child_id);
		
		if ($topic_id == 0 || $topic_child_id == 0)
		{
			return false;
		}
		if ($this->has_topic_lnk_topic($topic_id, $topic_child_id))
		{
			return false;
		}
		else
		{
			$data = array(
				"topic_id" => $topic_id, 
				"topic_child_id" => $topic_child_id
			);
			
			$this->insert('topic_tree', $data);
			//记录添加父类日志
			ACTION_LOG::save_action(USER::get_client_uid(), $topic_child_id, ACTION_LOG::CATEGORY_TOPIC, ACTION_LOG::ADD_TOPIC_PARENT, $topic_id, $topic_child_id);
			
			return true;
		}
	}

	/**
	 * 删除话题关联
	 * @param int $topic_id
	 * @param int $topic_child_id
	 * 
	 * @return boolean true|false
	 */
	public function delete_lnk_2_topic($topic_id, $topic_child_id)
	{
		$topic_id = intval($topic_id);
		$topic_child_id = intval($topic_child_id);
		
		if ($topic_id == 0 || $topic_child_id == 0)
		{
			return false;
		}
		//记录删除父类日志
		ACTION_LOG::save_action(USER::get_client_uid(), $topic_id, ACTION_LOG::CATEGORY_TOPIC, ACTION_LOG::DELETE_TOPIC_PARENT, $topic_id, $topic_child_id);
		
		return $this->delete('topic_tree', 'topic_id = ' . $topic_id . '  AND topic_child_id = ' . $topic_child_id);
	}

	/**
	 * 
	 * 修改话题分类,修改其父类即可
	 * @param int $topic_id
	 * @param int $topic_parent_id
	 * 
	 * @return boolean true|false
	 */
	public function change_topic_parent_id($topic_id, $topic_parent_id)
	{
		$topic_id = intval($topic_id);
		$topic_parent_id = intval($topic_parent_id);
		
		if ($topic_id == 0)
		{
			return false;
		}
		
		return $this->UPDATE('topic', array(
			'topic_parent_id' => $topic_parent_id
		), 'topic_id = ' . $topic_id);
	}

	/**
	 * 
	 * 判断话题是否锁定
	 * @param int $topic_id
	 * 
	 * @return boolean true|false
	 */
	public function has_lock_topic($topic_id)
	{
		$topic = $this->get_topic($topic_id);
		
		if ((isset($topic['topic_lock'])) && ($topic['topic_lock'] == 1))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * 
	 * 判断是否存在相同话题
	 * @param string $topic_title
	 * 
	 * @return int topic_id
	 */
	public function has_topic($topic_title)
	{
		$topic = $this->get_topic_by_title($topic_title);
		
		if ((isset($topic['topic_id'])) && ($topic['topic_id'] > 0))
		{
			return $topic['topic_id'];
		}
		else
		{
			return 0;
		}
	}

	/**
	 * 
	 * 增加话题关注
	 * 
	 * @return boolean true|false
	 */
	public function add_focus_topic($uid, $topic_id)
	{
		if (! $this->has_focus_topic($uid, $topic_id))
		{
			$data = array(
				"topic_id" => intval($topic_id), 
				"uid" => intval($uid), 
				"add_time" => time()
			);
			
			if ($this->insert("topic_focus", $data))
			{
				$this->query('UPDATE ' . $this->get_table('topic') . " SET focus_count = focus_count + 1 WHERE topic_id = " . intval($topic_id));
			}
			
			$retval = 'add';
			
			//记录日志
			ACTION_LOG::save_action($uid, $topic_id, ACTION_LOG::CATEGORY_TOPIC, ACTION_LOG::ADD_TOPIC_FOCUS);
		}
		else
		{
			if ($this->delete_focus_topic($topic_id, $uid))
			{
				$this->query('UPDATE ' . $this->get_table('topic') . " SET focus_count = focus_count - 1 WHERE topic_id = " . intval($topic_id));
			}
			
			$retval = 'remove';
			
			//记录日志
			ACTION_LOG::save_action($uid, $topic_id, ACTION_LOG::CATEGORY_TOPIC, ACTION_LOG::DELETE_TOPIC_FOCUS);
		}
		
		//更新个人计数

		$focus_count = $this->count("topic_focus", 'uid = ' . intval($uid));
		
		$this->model("account")->update_users_fields(array(
			"topic_focus_count" => $focus_count
		), $uid);
		
		return $retval;
	}

	/**
	 * 
	 * 删除话题关注
	 * @param int/string $topic_id [1, 12]
	 * @param int $uid
	 * 
	 * @return boolean true|false
	 */
	public function delete_focus_topic($topic_id, $uid)
	{
		if (intval($topic_id) == 0)
		{
			return false;
		}
		
		return $this->delete('topic_focus', 'uid = ' . intval($uid) . ' AND topic_id = ' . intval($topic_id));
	
	}

	/**
	 * 
	 * 判断是否已经关注该话题
	 * @param int $uid
	 * @param int $topic_id
	 * 
	 * @return boolean true|false
	 */
	public function has_focus_topic($uid, $topic_id)
	{
		$sql = 'SELECT focus_id FROM ' . $this->get_table('topic_focus') . " WHERE uid = " . intval($uid) . " AND topic_id = " . intval($topic_id);
		
		$retval = $this->query_row($sql);
		
		return $retval['focus_id'];
	}

	/**
	 * 
	 * 增加/更新话题经验
	 * @param int    $topic_id
	 * @param int    $uid                //用户UID
	 * @param string $experience_content //话题经验内容
	 * 
	 * @return boolean true|false
	 */
	public function save_topic_experience($topic_id, $uid, $experience_content)
	{
		$topic_id = intval($topic_id);
		$uid = intval($uid);
		$experience_content = FORMAT::safe(trim($experience_content),true);
		
		if ($topic_id == 0 || $uid == 0 || $experience_content == '')
		{
			return false;
		}
		
		if ($this->has_topic_experience($uid, $topic_id))
		{
			
			$this->UPDATE("topic_experience", array(
				'experience_content' => $experience_content
			), 'uid = ' . intval($uid) . ' AND topic_id = ' . intval($topic_id));
			
			return 'UPDATE';
		}
		else
		{
			$data = array(
				'topic_id' => $topic_id, 
				'uid' => $uid, 
				'experience_content' => $experience_content, 
				'add_time' => time()
			);
			
			$this->insert('topic_experience', $data);
			
			return 'add';
		}
	}

	/**
	 * 
	 * 根据用户ID/话题ID,得到话题经验
	 * @param int $uid
	 * @param int $topic_id
	 * 
	 * @return boolean true|false
	 */
	public function get_topic_experience($uid, $topic_id)
	{
		$sql = 'SELECT experience_content FROM ' . $this->get_table('topic_experience') . " WHERE uid = " . intval($uid) . " AND topic_id = " . intval($topic_id);
		
		$retval = $this->query_row($sql);
		
		if (isset($retval['experience_content']))
		{
			return $retval['experience_content'];
		}
		else
		{
			return '';
		}
	}

	/**
	 * 
	 * 判断用户是否已经存在话题经验
	 * @param int $uid
	 * @param int $topic_id
	 * 
	 * @return boolean true|false
	 */
	public function has_topic_experience($uid, $topic_id)
	{
		$sql = 'SELECT experience_id FROM ' . $this->get_table('topic_experience') . " WHERE uid = " . intval($uid) . " AND topic_id = " . intval($topic_id);
		
		$retval = $this->query_row($sql);
		
		if (isset($retval['experience_id']) && $retval['experience_id'] > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * 
	 * 得到用户不感兴趣话题列表
	 * @param int $uid
	 * 
	 * @return array
	 */
	public function get_uninterested_list_by_uid($uid)
	{		
		return $this->query_all('SELECT topic_id FROM ' . $this->get_table('topic_uninterested') . " WHERE uid = " . intval($uid));
	}

	/**
	 * 
	 * 不感兴趣话题
	 * @param int $uid
	 * @param int $topic_id
	 * 
	 * @return boolean
	 */
	public function save_topic_uninterested($uid, $topic_id)
	{
		if (! $this->has_topic_uninterested($uid, $topic_id))
		{
			
			$this->insert("topic_uninterested", array(
				'uid' => $uid, 
				'topic_id' => $topic_id, 
				'add_time' => time()
			));
			
			return true;
		}
		
		return false;
	}

	/**
	 * 
	 * 判断用户是否已经不感兴趣话题
	 * @param int $uid
	 * @param int $topic_id
	 * 
	 * @return boolean
	 */
	public function has_topic_uninterested($uid, $topic_id)
	{
		$sql = 'SELECT uid FROM ' . $this->get_table('topic_uninterested') . " WHERE uid = " . intval($uid) . " AND topic_id = " . intval($topic_id);
		
		$retval = $this->query_row($sql);
		
		if (isset($retval['uid']) && $retval['uid'] > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function get_topic_by_parent_id($count, $parent_id)
	{
		$parent_id = intval($parent_id);
		
		if ($parent_id < 0)
		{
			return false;
		}
		
		if ($parent_id >= 0)
		{
			$where[] = "parent_id = " . $parent_id;
		}
		if ($parent_id == 0)
		{
			$where[] = "is_top = 1";
		}
		
		if ($count)
		{
			return $this->count('topic', implode(' AND ', $where));
		}
		
		return $this->fetch_all('topic', implode(' AND ', $where), 'topic_id ASC');
	}

	function get_position_by_id($topic_id, $position = array())
	{
		$topic_id = intval($topic_id);
		$rs = $this->get_topic($topic_id);
		
		if (is_array($rs))
		{
			$data['topic_id'] = $rs['topic_id'];
			$data['topic_title'] = $rs['topic_title'];
			$data['parent_id'] = $rs['parent_id'];
			$position[] = $data;
		}
		
		if ($data['parent_id'] == 0 || COUNT($position) > 3)
		{
			return array_reverse($position);
		}
		else if ($data['parent_id'] > 0)
		{
			return $this->get_position_by_id($data['parent_id'], $position);
		}
	}

	/**
	 * 更新问题统计
	 * @param  $topic_id
	 */
	function update_topic_count($topic_id)
	{
		$topic_id = $topic_id * 1;
		
		if (! $topic_id)
		{
			return false;
		}
		
		$sql = "UPDATE " . $this->get_table('topic') . " SET topic_count = (SELECT COUNT(*) FROM " . $this->get_table('topic_question') . " WHERE topic_id = " . $topic_id . ") WHERE topic_id = " . $topic_id;
		
		return $this->query($sql);
	
	}

	/**
	 * 物理删除话题及其关联的图片等
	 * 
	 * @param  $topic_id
	 */
	public function remove_topic($topic_id)
	{
		$topic_id = intval($topic_id);
		
		if (! $topic_id)
		{
			return false;
		}
		
		$topic_info = $this->get_topic($topic_id);
		
		//删除与问题、比赛的关联及其动作
		$this->delete('topic_question', 'topic_id = ' . $topic_id);
		
		//删除话题图片
		// 		$thumb_sizes = array(
		// 			'350_350', 
		// 			'150_150', 
		// 			'50_50'
		// 		);
		
		if ($topic_info['topic_pic'])
		{
			foreach (GZ_APP::config()->get('image')->topic_thumbnail as $size)
			{
				
				unlink(get_setting('upload_dir') . '/topic/' . str_replace('32_32', $size['w'] . '_' . $size['h'], $topic_info['topic_pic']));
			
			}
			
			@unlink(get_setting('upload_dir') . '/topic/' . $topic_info['topic_pic']);
		}
		
		//删除关注		
		$this->delete('topic_focus', 'topic_id=' . $topic_id);
		
		//删除不感兴趣的
		$this->delete('topic_uninterested', 'topic_id=' . $topic_id);
		
		//删除动作	
		$this->delete('user_action_history', "associate_type=4 AND associate_id=" . $topic_id);
		$this->delete('user_action_history', "associate_type IN (1,5) AND associate_action=401 AND associate_attached=" . $topic_id);
		
		//删除话题		
		return $this->delete('topic', 'topic_id=' . $topic_id);
	
	}

	public function get_topic_list_v2($limit, $order = 'topic_id DESC', $where = false)
	{
		return $this->fetch_all('topic', $where, $order, $limit);
	}
	
	// 我关注的人关注的话题
	public function get_user_recommend_v2($uid, $limit = 10)
	{
		$get_my_focus = $this->query_all("SELECT topic_id FROM " . $this->get_table("topic_focus") . " WHERE uid = " . (int)$uid);
		
		$my_focus = array(
			0
		);
		
		foreach ($get_my_focus as $key => $val)
		{
			$my_focus[] = $val['topic_id'];
		}
		
		$friends = $this->model('follow')->get_user_friends($uid, false);
		
		foreach ($friends as $key => $val)
		{
			$follow_uids[] = $val['friend_uid'];
			$follow_users_array[$val['friend_uid']] = $val;
		}
		
		if (! $follow_uids)
		{
			return $this->get_topic_list_v2($limit, 'topic_id DESC', "topic_id NOT IN(" . implode($my_focus, ',') . ")");
		}
		
		$topic_focus = $this->query_all("SELECT DISTINCT topic_id, uid FROM " . $this->get_table("topic_focus") . " WHERE uid IN(" . implode($follow_uids, ',') . ") AND topic_id NOT IN (" . implode($my_focus, ',') . ") ORDER BY focus_id DESC LIMIT " . $limit);
		
		foreach ($topic_focus as $key => $val)
		{
			$topic_ids[] = $val['topic_id'];
			$topic_id_focus_uid[$val['topic_id']] = $val[uid];
		}
		
		if (! $topic_ids)
		{
			return $this->get_topic_list_v2($limit, 'topic_id DESC', "topic_id NOT IN (" . implode($my_focus, ',') . ")");
		}
		
		$topics = $this->query_all("SELECT * FROM " . $this->get_table('topic') . " WHERE topic_id IN(" . implode($topic_ids, ',') . ") AND topic_id NOT IN (" . implode($my_focus, ',') . ") ORDER BY topic_id DESC LIMIT " . $limit);
		
		if (sizeof($topics) < $limit)
		{
			$topics_new = $this->get_topic_list_v2(($limit - sizeof($topics)), 'topic_id DESC', "topic_id NOT IN(" . implode($topic_ids, ',') . ") AND topic_id NOT IN (" . implode($my_focus, ',') . ")");
			
			if (is_array($topics_new))
			{
				foreach ($topics_new as $key => $val)
				{
					$topics[] = $val;
				}
			}
		}
		
		foreach ($topics as $key => $val)
		{
			$topics[$key]["focus_users"] = $follow_users_array[$topic_id_focus_uid[$val["topic_id"]]];
		}
		
		return $topics;
	}

	function get_focus_users_by_topic($topic_id, $limit = 19)
	{
		$uids = $this->query_all("SELECT DISTINCT uid FROM {$this->get_table("topic_focus")} WHERE topic_id = " . intval($topic_id) . " LIMIT " . $limit);
		
		$users_list = array();
		
		foreach ($uids as $key => $user)
		{
			$users_list[$user_info['uid']] = $this->model('account')->get_users_by_uid($user['uid']);
		}
		
		return $users_list;
	}

	function get_question_compeitions_by_topic($topic_id, $limit = 10)
	{
		$query = "SELECT t.*, q.published_uid AS q_published_uid, q.add_time AS q_add_time, q.* FROM {$this->get_table("topic_question")} AS t LEFT JOIN {$this->get_table("question")} AS q ON t.question_id = q.question_id  WHERE t.question_id>0 AND  t.topic_id = " . intval($topic_id) . " ORDER BY q.modify_time DESC";
		
		$result = $this->query_all($query, $limit);
		
		foreach ($result as $key => $data)
		{
			if ($data['question_id'])
			{
				$data['published_uid'] = $data['q_published_uid'];
				$result[$key]['published_uid'] = $data['q_published_uid'];
				
				$answer_list = $this->model('answer')->get_answer_list_by_question_id($data['question_id'], 1);
				
				if (! isset($user_list[$data['published_uid']]))
				{
					$user_list[$data['published_uid']] = $this->model('account')->get_users($data['published_uid']);
				}
				
				$result[$key]['title'] = $data['question_content'];
				$result[$key]['focus'] = $this->model('question')->has_focus_question($data['question_id'], $this->user_id);
				$result[$key]['topics'] = $this->model('question_topic')->get_question_topic_by_question_id($data['question_id']);
				$result[$key]['vote_count'] = $answer_list[0]['agree_count'];
				$result[$key]['pic'] = $user_list[$data['published_uid']]['avatar_file'];
				$result[$key]['name'] = $user_list[$data['published_uid']]['name'];
				$result[$key]['url'] = $user_list[$data['published_uid']]['url'];
				$result[$key]['answer'] = $answer_list[0];
				$result[$key]['question_id'] = $data['question_id'];
				$result[$key]['add_time'] = $data['q_add_time'];
				$result[$key]['answer_count'] = $data['answer_count'];
				$result[$key]['user_info'] = $user_list[$data['published_uid']];
			}
		
		}
		
		return $result;
	}

	/**
	 * 新版获取
	 * @param  $topic_id
	 * @param  $limit
	 */
	function get_question_compeitions_by_topic_v2($topic_id, $limit = 10)
	{
		$query = "SELECT t.*, q.published_uid AS q_published_uid, q.add_time AS q_add_time, q.* FROM {$this->get_table("topic_question")} AS t LEFT JOIN {$this->get_table("question")} AS q ON t.question_id = q.question_id  WHERE t.question_id>0 AND t.topic_id = " . intval($topic_id);
		
		return $this->query_all($query, $limit);
	}

	public function modify_pic_topic_by_id($topic_id, $topic_pic)
	{
		$topic_id = intval($topic_id);
		$topic_pic = FORMAT::safe($topic_pic, true);
		
		if ($topic_id == 0)
		{
			return false;
		}
		
		if (empty($topic_pic))
		{
			return false;
		}
		
		return $this->update_topic($topic_id, '', '', $topic_pic, '', '');
	}
}