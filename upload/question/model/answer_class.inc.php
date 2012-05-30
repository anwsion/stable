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

class answer_class extends GZ_MODEL
{

	/**
	 * 得到时间段内会回答用户最多的10条记录
	 * @param int $limit_time
	 */
	public function get_answer_user_time_rang($time_limit = 172800)
	{
		$user_id_list = array(
			0
		);
		
		$sql = "SELECT uid, COUNT(*) AS total, add_time FROM " . $this->get_table('answer') . " GROUP BY uid HAVING add_time >= " . (time() - intval($time_limit)) . " ORDER BY total DESC LIMIT 50";
		
		$answer_list = $this->query_all($sql);
		
		foreach ($answer_list as $answer_info)
		{
			(! empty($answer_info['uid'])) ? $user_id_list[] = $answer_info['uid'] : '';
		}
		
		return $user_id_list;
	}

	/**
	 * 
	 * 得到时间段内会回答最多的10条记录
	 * @param int $limit_time
	 */
	public function get_answer_count_time_rang($time_limit = 172800, $limit = false)
	{
		$question_id_list = array(
			0
		);
		
		$sql = "SELECT question_id, COUNT(*) AS total, add_time FROM " . $this->get_table('answer') . " GROUP BY question_id HAVING add_time >= " . (time() - intval($time_limit)) . " ORDER BY total DESC";
		
		$answer_list = $this->query_all($sql, $limit);
		
		foreach ($answer_list as $answer_info)
		{
			$question_id_list[] = $answer_info['question_id'];
		}
		
		return $question_id_list;
	}

	/**
	 * 
	 * 根据用户ID，得到用户回答过的问题列表
	 * @param int $count
	 * @param int $uid
	 * @param int $limit
	 * 
	 * @return array
	 */
	public function get_answer_question_list_by_uid($count = false, $uid = 0, $limit = 20)
	{
		$uid = intval($uid);
		
		if ($uid == 0)
		{
			return false;
		}
		
		if ($count)
		{
			$sql = "SELECT COUNT(*) AS total FROM " . $this->get_table('answer') . " AS an , " . $this->get_table('question') . " AS q WHERE an.question_id = q.question_id AND an.uid = " . intval($uid);
			
			$retval = $this->query_row($sql);
			
			return isset($retval['total']) ? $retval['total'] : 0;
		}
		else
		{
			$sql = "SELECT q.* FROM " . $this->get_table('answer') . " as an , " . $this->get_table('question') . " as q WHERE an.question_id = q.question_id AND an.uid = " . intval($uid) . " ORDER BY an.answer_id DESC";
			
			return $this->query_all($sql, $limit);
		}
	
	}

	/**
	 * 
	 * 根据用户ID数组，得到用户回答过的问题列表
	 * @param int $count
	 * @param int $uid
	 * @param int $limit
	 * 
	 * @return array
	 */
	public function get_answer_question_list_by_uids($count = false, $uid_array, $limit = 20)
	{
		if (! is_array($uid_array))
		{
			return array();
		}
		
		$uid_array_str = implode(",", $uid_array);
		
		if ($count)
		{
			$sql = "SELECT COUNT(*) as total FROM " . $this->get_table('answer') . " AS an , " . $this->get_table('question') . " AS q WHERE an.question_id = q.question_id AND an.uid IN( " . $uid_array_str . ")";
			
			$retval = $this->get_row(false, $sql);
			
			return isset($retval['total']) ? $retval['total'] : 0;
		}
		else
		{
			$sql = "SELECT DISTINCT(question_id) FROM  " . $this->get_table('answer') . " AS an WHERE an.uid IN(" . $uid_array_str . ")";
			
			$rs = $this->query_all($sql, $limit);
			
			if (! $rs)
			{
				return false;
			}
			
			$q_array = array();
			
			foreach ($rs as $key => $val)
			{
				$q_array[] = $val["question_id"];
			}
			
			$sql = "SELECT q.* FROM  " . $this->get_table('question') . " AS q WHERE q.question_id IN(" . implode(",", $q_array) . ") ORDER BY modify_time DESC";
			
			return $this->query_all($sql);
		}
	
	}

	/**
	 * 根据用户ID数组，得到用户回投票过的问题列表
	 *  
	 * @param  $count
	 * @param  $uid_array
	 * @param  $vote_value
	 * @param  $limit
	 */
	public function get_answer_question_list_by_uids_vote($count = false, $uid_array, $vote_value = "1", $limit = 20)
	{
		if (! is_array($uid_array))
		{
			return array();
		}
		
		$vote_value = $vote_value * 1;
		
		$uid_array_str = implode(",", $uid_array);
		
		$rs = $this->fetch_row('answer_vote', "vote_value = '" . $vote_value . "' AND vote_uid IN(" . $uid_array_str . ")");
		
		if (! $rs)
		{
			return array();
		}
		
		foreach ($rs as $key => $val)
		{
			$answer_array[] = $val["answer_id"];
		}
		
		return $this->get_answer_question_list_answers($answer_array, false, 100);
	
	}

	/**
	 * 通过回答ID,得到问题列表
	 *  
	 * @param unknown_type $answers
	 * @param unknown_type $limit
	 */
	function get_answer_question_list_answers($answers_array, $count = false, $limit = 20)
	{
		
		if (! is_array($answers_array))
		{
			return array();
		}
		
		$answers_array_str = implode(",", $answers_array);
		
		if ($count)
		{
			$count = $this->query_row("SELECT COUNT(an.*) AS count, q.* FROM " . $this->get_table('answer') . " AS an, " . $this->get_table('question') . " AS q WHERE an.question_id = q.question_id AND an.answer_id IN (" . $answers_array_str . ")");
			
			return $count['count'];
		}
		
		$sql = "SELECT an.*, q.* FROM " . $this->get_table('answer') . " AS an, " . $this->get_table('question') . " AS q WHERE an.question_id = q.question_id AND an.answer_id IN(" . $answers_array_str . ")";
		
		return $this->query_all($sql, $limit);
	
	}

	/**
	 * 
	 * 根据用户ID,得到回复列表
	 * @param int $uid
	 * 
	 * @return array
	 */
	/*
	public function get_answer_list_by_uid($uid)
	{
		$sql = "SELECT an.*, q.* FROM " . $this->get_table('answer') . " AS an, " . $this->get_table('question') . " AS q WHERE an.question_id = q.question_id AND an.uid = " . intval($uid);
		
		return $this->query_all($sql);
	}
	*/

	/**
	 * 
	 * 根据回答ID,返回回答详细内容
	 * @param int     $answer_id
	 * @param int     $uid
	 * @param boolean true|false 是否假如回答是自己判断
	 * 
	 * @return array
	 */
	public function get_answer_info_by_id($answer_id, $uid = 0, $is_self = true)
	{
		if (intval($uid) == 0)
		{
			$uid = USER::get_client_uid();
		}
		
		if ($is_self)
		{
			$where = " AND uid = " . intval($uid);
		}
		
		return $this->fetch_row('answer', "answer_id = " . intval($answer_id) . $where);
	}

	public function get_answer_count_by_question_id($question_id, $where = "")
	{
		if ($where)
		{
			$where = ' AND ' . $where;
		}
		
		return $this->count('answer', "question_id = " . $question_id . $where);
	}

	/**
	 * 
	 * 根据问题ID得到相关回答
	 * @param array    $question_id 问题ID
	 * @param string $limit 得到相关数量
	 * 
	 * @return array
	 */
	public function get_answer_list_by_question_id($question_id, $limit = 20, $where = null, $order_by = null)
	{
		$question_ids = array();
		
		if (is_array($question_id))
		{
			foreach ($question_id as $id)
			{
				$question_ids[] = intval($id);
			}
			
			$question_id = implode(',', $question_ids);
		}
		else
		{
			$question_ids = intval($question_id);
		}
		
		$sql = "SELECT an.*, u.user_name AS uname, u.avatar_file, at.signature FROM " . $this->get_table('answer') . " AS an LEFT JOIN " . $this->get_table('users') . " AS u ON an.uid = u.uid LEFT JOIN " . $this->get_table('users_attrib') . " AS at ON an.uid = at.uid WHERE an.question_id IN( " . $question_id . ")";
		
		if ($where)
		{
			$sql = $sql . ' ' . $where;
		}
		
		if (empty($order_by))
		{
			$sql .= " ORDER BY answer_id DESC";
		}
		else
		{
			$sql .= " ORDER BY " . $order_by;
		}
		
		if ($limit == 'all')
		{
			return $this->query_all($sql);
		}
		else
		{
			return $this->query_all($sql, $limit);
		}
	}

	/**
	 * 
	 * 得到问题回复的批量用户名
	 * @param array $uids
	 * 
	 * @return array
	 */
	public function get_answer_user_by_uids(array $uids)
	{
		$data = array();
		
		$user_list = $this->model('account')->get_users_by_uids($uids);
		
		foreach ($user_list as $user)
		{
			$data[$user['uid']] = $user;
		}
		
		return $data;
	}

	/**
	 * 
	 * 根据回答问题ID，得到投票的用户
	 * @param int $answer_id
	 * 
	 * @return array
	 */
	public function get_vote_user_by_answer_id($answer_id)
	{
		if (empty($answer_id))
		{
			return array();
		}
		
		$sql = "SELECT DISTINCT uid, user_name FROM " . $this->get_table('users') . " AS u, " . $this->get_table('answer_vote') . " as an WHERE u.uid = an.vote_uid AND an.answer_id IN(" . $answer_id . ") AND an.vote_value = 1";
		
		$users = $this->query_all($sql);
		
		foreach ($users as $key => $val)
		{
			$data[$val['uid']] = $val['user_name'];
		}
		
		return $data;
	}

	/**
	 * 
	 * 保存问题回复内容
	 * @param int    $question_id       //问题id
	 * @param string $answer_content    //回答内容
	 * @param int    $add_time          //添加时间
	 * @param int    $modify_time       //修改时间
	 * @param int    $against_count     //支持人数
	 * @param int    $agree_count       //支持人数
	 * @param int    $rating            //权重评分
	 * @param int    $uid        //发布回复用户ID
	 * 
	 * @return boolean true|false
	 */
	public function save_answer($question_id, $answer_content, $add_time = 0, $modify_time = 0, $against_count = 0, $agree_count = 0, $rating = 0, $uid = 0)
	{
		$question_id = intval($question_id);
		
		$question_info = $this->model('question')->get_question_info_by_id($question_id);
		
		if ($question_id == 0)
		{
			return false;
		}
		
		$uid = ($uid == 0) ? USER::get_client_uid() : intval($uid);
		
		$data = array(
			"question_id" => $question_id, 
			"answer_content" => htmlspecialchars($answer_content), 
			"add_time" => ($add_time == 0) ? time() : $add_time, 
			"modify_time" => intval($modify_time), 
			"against_count" => intval($against_count), 
			"agree_count" => intval($agree_count), 
			"rating" => intval($rating), 
			"uid" => $uid,
			'category_id' => $question_info['category_id'],
		);
		
		$answer_id = $this->insert('answer', $data);
		
		if ($answer_id <= 0)
		{
			return false;
		}
		
		
		
		//更新问题最后时间
		$this->update('question', array(
			'modify_uid' => intval($uid), 
			'modify_time' => time(),
			'update_time' => time(),
		), "question_id = " . intval($question_id));
		
		//自动关注该问题
		$this->model('question')->add_focus_question($question_id, false);
		
		//更新问题回复数量,及时间
		if ($answer_id)
		{
			//更新问题回复计数
			$this->model('question')->update_answer_count($question_id);
			$this->model('question')->update_answer_users_count($question_id);
			
			//更新用户回复计数
			$this->model('account')->increase_user_statistics(account_class::ANSWER_COUNT, - 1);
		}
		
		//记录日志
		ACTION_LOG::save_action($uid, $answer_id, ACTION_LOG::CATEGORY_ANSWER, ACTION_LOG::ANSWER_QUESTION, htmlspecialchars($answer_content), $question_id);
		
		//记录日志
		ACTION_LOG::save_action($uid, $question_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::ANSWER_QUESTION, htmlspecialchars($answer_content), $answer_id);
		
		return $answer_id;
	}

	/**
	 * 
	 * 更新问题回复内容
	 * @param array $data
	 * @param int   $answer_id
	 * @param int $question_id
	 * @param string $answer_content
	 * @param int $modify_time
	 * 
	 * @return boolean true|false
	 */
	public function update_answer($answer_id, $question_id, $answer_content, $modify_time, $attach_access_key)
	{
		$answer_id = intval($answer_id);
		$quetion_id = intval($quetion_id);
		
		if ($answer_id == 0)
		{
			return false;
		}
		
		$answer_info = $this->get_answer_info_by_id($answer_id);
		
		$data = array(
			"answer_content" => htmlspecialchars($answer_content),
			"modify_time" => intval($modify_time == 0) ? time() : $modify_time
		);
		
		//更新问题最后时间		
		$this->update('question', array(
			'modify_uid' => USER::get_client_uid(), 
			'modify_time' => time(),
			'update_time' => time()
		), "question_id = " . intval($question_id));
		
		if ($attach_access_key)
		{
			$this->update_answer_attach($answer_id, $attach_access_key);
		}
		
		//记录日志
		ACTION_LOG::save_action(USER::get_client_uid(), $question_id, ACTION_LOG::CATEGORY_ANSWER, ACTION_LOG::MOD_ANSWER, htmlspecialchars($answer_content), $answer_info['answer_content']);
		
		return $this->update('answer', $data, " answer_id = " . intval($answer_id));
	}

	/**
	 * 
	 * 判断是否已经回答过问题
	 * @param int $uid
	 * @param int $question_id
	 * 
	 * @return boolean true|false
	 */
	public function has_answer_question($uid, $question_id)
	{
		if ($this->count('answer', "question_id = " . intval($question_id) . " AND uid = " . intval($uid)) > 0)
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
	 * 回复投票
	 * @param int $answer_id   //回复id
	 * @param int $question_id //问题ID
	 * @param int $vote_value  //-1反对 1 赞同
	 * @param int $uid         //用户ID
	 * 
	 * @return boolean true|false
	 */
	public function change_answer_vote($answer_id, $vote_value = 1, $uid = 0)
	{
		$answer_id = intval($answer_id);
		$uid = (intval($uid) == 0) ? USER::get_client_uid() : $uid;
		
		if ($answer_id == 0)
		{
			return false;
		}
		
		if (! in_array($vote_value, array(
			- 1, 
			0, 
			1
		)))
		{
			return false;
		}
		
		$answer_info = $this->get_answer_info_by_id($answer_id, 0 , false);
		
		$question_id = $answer_info['question_id'];
		$answer_uid = $answer_info['uid'];
		
		$vote_info = $this->get_answer_vote_status($answer_id, $uid);
		
		if (empty($vote_info)) //添加记录
		{
			$data = array(
				"answer_id" => $answer_id, 
				"answer_uid" => $answer_uid, 
				"vote_uid" => $uid, 
				"add_time" => time(), 
				"vote_value" => $vote_value
			);
			
			$this->insert('answer_vote', $data);
			
			if ($vote_value == 1)
			{
				ACTION_LOG::save_action($uid, $answer_id, ACTION_LOG::CATEGORY_ANSWER, ACTION_LOG::ADD_AGREE, '', $question_id);
			}
			else if ($vote_value == -1)
			{
				ACTION_LOG::save_action($uid, $answer_id, ACTION_LOG::CATEGORY_ANSWER, ACTION_LOG::ADD_AGANIST, '', $question_id);
			}
			
		}
		else if($vote_info['vote_value'] == $vote_value)	//再次点击，删除投票
		{
			$this->delete_answer_vote($vote_info['voter_id']);
				
			if ($vote_value == 1)
			{
				ACTION_LOG::save_action($uid, $answer_id, ACTION_LOG::CATEGORY_ANSWER, ACTION_LOG::DEL_AGREE, '', $question_id);
			}
			else if ($vote_value == -1)
			{
				ACTION_LOG::save_action($uid, $answer_id, ACTION_LOG::CATEGORY_ANSWER, ACTION_LOG::DEL_AGANIST, '', $question_id);
			}
		}
		else
		{
			$this->set_answer_vote_status($vote_info['voter_id'], $vote_value);
			
			if ($vote_value == 1)
			{
				ACTION_LOG::save_action($uid, $answer_id, ACTION_LOG::CATEGORY_ANSWER, ACTION_LOG::ADD_AGREE, '', $question_id);
				ACTION_LOG::save_action($uid, $answer_id, ACTION_LOG::CATEGORY_ANSWER, ACTION_LOG::DEL_AGANIST, '', $question_id);
			}
			else if ($vote_value == - 1)
			{
				ACTION_LOG::save_action($uid, $answer_id, ACTION_LOG::CATEGORY_ANSWER, ACTION_LOG::ADD_AGANIST, '', $question_id);
				ACTION_LOG::save_action($uid, $answer_id, ACTION_LOG::CATEGORY_ANSWER, ACTION_LOG::DEL_AGREE, '', $question_id);
			}
		}
		
		$this->update_vote_count($answer_id, 'against');
		$this->update_vote_count($answer_id, 'agree');
		
		$this->update_question_vote_count($question_id);
		
		//更新回复作者的被赞同数
		$this->model('account')->update_users_fields(array('agree_count' => $this->user_agree_count($answer_uid)), $answer_uid);
		
		return true;
	}
	
	
	public function user_agree_count($uid)
	{
		$uid = intval($uid);
		
		if($uid == 0)
		{
			return false;
		}
		
		return $this->count('answer_vote', 'vote_value = 1 AND answer_uid = ' . $uid);
	}

	/**
	 * 删除回复投票
	 * Enter description here ...
	 * @param unknown_type $voter_id
	 */
	public function delete_answer_vote($voter_id)
	{
		return $this->delete('answer_vote', "voter_id = " . intval($voter_id));
	}

	public function update_vote_count($answer_id, $type)
	{
		if (! in_array($type, array(
			'against', 
			'agree'
		)))
		{
			return false;
		}
		
		$vote_value = ($type == 'agree') ? '1' : '-1';
		
		$count = $this->count('answer_vote', 'answer_id = ' . $answer_id . ' AND vote_value = ' . $vote_value);
		
		return $this->query("UPDATE " . $this->get_table('answer') . " SET {$type}_count = {$count}, modify_time = '" . time() . "' WHERE answer_id = " . $answer_id);
	}
	
	public function update_question_vote_count($question_id)
	{
		$answers = $this->get_answer_list_by_question_id($question_id, 'all');
		
		if(empty($answers))
		{
			return false;
		}
		
		$answer_ids = array();
		
		foreach($answers as $key => $val)
		{
			$answer_ids[] = $val['answer_id'];
		}
		
		$agree_count = $this->count('answer_vote', 'answer_id IN (' . implode(',', $answer_ids) . ') AND vote_value = 1');
		
		$against_count = $this->count('answer_vote', 'answer_id IN (' . implode(',', $answer_ids) . ') AND vote_value = -1');
		
		return $this->query("UPDATE " . $this->get_table('question') . " SET agree_count = {$agree_count}, against_count = {$against_count} WHERE question_id = " . $question_id);
	}

	/**
	 * 设置回复投票状态
	 * Enter description here ...
	 */
	function set_answer_vote_status($voter_id, $vote_value)
	{
		$update_arr = array(
			"add_time" => time(), 
			"vote_value" => $vote_value
		);
		
		return $this->update('answer_vote', $update_arr, "voter_id = " . intval($voter_id));
	}

	function get_answer_vote_status($answer_id, $uid)
	{
		if (empty($uid))
		{
			$uid = USER::get_client_uid();
		}
		return $this->fetch_row('answer_vote', "answer_id = " . intval($answer_id) . " AND vote_uid = " . intval($uid), 'voter_id DESC');
	}

	/**
	 * 
	 * 删除问题回复内容
	 * @param int $answer_id
	 * @param int $question_id
	 * 
	 * @return boolean true|false
	 */
	public function delete_answer($answer_id, $question_id)
	{
		$uid = USER::get_client_uid();
		
		$answer_info = $this->get_answer_info_by_id($answer_id, $uid);
		
		$retval = $this->delete('answer', " answer_id = " . intval($answer_id) . " AND uid = " . $uid);
		
		if ($retval)
		{
			//更新问题
			$this->query("UPDATE " . $this->get_table('question') . " SET answer_count = answer_count - 1 WHERE question_id = " . intval($question_id));
			
			//删除评论
			$this->delete('question_comment', " source_id = " . intval($answer_id) . " AND comment_type=2");
			
			//删除投票记录
			$this->delete('answer_vote', "answer_id = " . intval($answer_id));
		}
		
		//更新问题最后时间		
		$this->update('question', array(
			'modify_uid' => USER::get_client_uid(), 
			'modify_time' => time()
		), "question_id = " . intval($question_id));
		
		//增加用户编辑数量
		//USER::increase_user_statistics(USER::EDIT_COUNT, -1);
		//USER::increase_user_statistics(USER::TOPIC_COUNT, -1);
		

		//记录日志
		ACTION_LOG::save_action($uid, $question_id, ACTION_LOG::CATEGORY_ANSWER, ACTION_LOG::DELETE_ANSWER, $answer_info['answer_content']);
		
		return $retval;
	}

	/**
	 * 删除问题关联的所有回复及相关的内容
	 * Enter description here ...
	 */
	public function delete_answers_by_question_id($question_id)
	{
		$answers = $this->get_answer_list_by_question_id($question_id);
		
		if (empty($answers))
		{
			return false;
		}
		
		foreach ($answers as $key => $val)
		{
			$answer_id = intval($val['answer_id']);
			
			$this->delete('answer_vote', "answer_id=" . intval($answer_id)); //删除赞同

			$this->delete('user_action_history', "associate_type=2 AND associate_id=" . intval($answer_id)); //删除动作
			
			if ($attachs = $this->get_answer_attach($answer_id))
			{
				foreach ($attachs as $key => $val)
				{
					$this->remove_answer_attach($val['id'], $val['access_key']);
				}
			}
		}
		
		return $this->delete('answer', "question_id = " . intval($question_id));
	}
	
	/**
	 * 根据答案集合批量删除答案
	 * @param unknown_type $answer_id
	 */
	public function delete_answers_by_ids($answer_id)
	{
		if(empty($answer_id))
		{
			return false;
		}
		
		$answers = array();
		
		if(is_array($answer_id))
		{
			$answers = $answer_id;
		}
		else
		{
			$answers[] = intval($answer_id);
		}
		
		foreach ($answers as $answer_id)
		{
			$this->delete('answer_vote', "answer_id=" . intval($answer_id)); //删除赞同
		
			$this->delete('user_action_history', "associate_type=2 AND associate_id=" . intval($answer_id)); //删除动作
				
			if ($attachs = $this->get_answer_attach($answer_id))
			{
				foreach ($attachs as $key => $val)
				{
					$this->remove_answer_attach($val['id'], $val['access_key']);
				}
			}
			
			$this->delete('answer', "answer_id = " . intval($answer_id));
		}
		
		return true;
	}
	
	public function get_answers($count = false, $where = '', $limit = '')
	{
		if($count)
		{
			return $this->count('answer', $where);
		}
		else
		{
			return $this->fetch_all('answer', $where, $limit);
		}
	}

	/*
	public function delete_answer_agree($answer_id)
	{
		return $this->delete('notice', "answer_id = " . intval($answer_id));
	}
	*/

	/**
	 * 
	 * 根据问题ID,增加回复投票数
	 * @param int $answer_id
	 * @param int $question_id
	 * 
	 * @return boolean true|false
	 */
	/*
	public function increase_vote_by_answer_id($answer_id, $question_id)
	{
		$sql = "UPDATE " . $this->get_table('answer') . " SET agree_count = agree_count + 1, modify_time = " . time() . " WHERE answer_id = " . $answer_id . " ";
		
		return $this->query($sql);
	}
	*/


	/**
	 * 
	 * 根据问题ID,删除赞成投票记录
	 * @param int $answer_id
	 * @param int $question_id
	 * 
	 * @return boolean true|false
	 */
	/*
	public function delete_agree_vote_by_answer_id($answer_id)
	{
		$uid = USER::get_client_uid();
		
		$retval = $this->delete('answer_vote', " answer_id = " . intval($answer_id) . " AND vote_uid = " . intval($uid) . " AND vote_value = 1");
		
		return $retval;
	}
	*/

	/**
	 * 判断用户是否回答了问题
	 */
	function has_answer_by_uid($question_id, $uid)
	{
		return $this->count('answer', "question_id = " . intval($question_id) . " AND uid = " . intval($uid));
	}

	function get_answer_users_by_question($question_id)
	{
		$uids = $this->query_all("SELECT DISTINCT uid FROM " . $this->get_table('answer') . " WHERE question_id = " . intval($question_id));
		
		$users_list = array();
		
		foreach ($uids as $key => $user)
		{
			$user_info = $this->fetch_row('users', "uid = " . $user['uid']);
			
			$users_list[$user_info['uid']] = $user_info;
		}
		
		return $users_list;
	}

	public function add_answer_attach($file_name, $attach_access_key, $add_time, $file_location, $is_image = false)
	{
		if ($is_image)
		{
			$is_image = 1;
		}
		
		$data = array(
			'file_name' => htmlspecialchars($file_name), 
			'access_key' => $attach_access_key, 
			'add_time' => $add_time, 
			'file_location' => htmlspecialchars($file_location), 
			'is_image' => $is_image
		);
		
		return $this->insert('answer_attach', $data);
	}

	public function get_answer_attach($answer_id)
	{
		$attach = $this->fetch_all('answer_attach', "answer_id = " . intval($answer_id), "is_image DESC");
		
		$attach_list = array();
		
		foreach ($attach as $key => $data)
		{
			$attach_list[$data['id']] = array(
				'id' => $data['id'], 
				'is_image' => $data['is_image'], 
				'file_name' => $data['file_name'], 
				'access_key' => $data['access_key'], 
				'attachment' => get_setting('upload_url').'/answer/' . date('Ymd', $data['add_time']) . '/' . $data['file_location'],
				'thumb' => get_setting('upload_url').'/answer/' . date('Ymd', $data['add_time']) . '/' . GZ_APP::config()->get('image')->attachment_thumbnail['min']['w'] . 'x' . GZ_APP::config()->get('image')->attachment_thumbnail['min']['h'] . '_' . $data['file_location'],
			);
		}
		
		return $attach_list;
	}
	
	public function get_last_answer($question_id)
	{
		return $this->fetch_row('answer', 'question_id = ' . intval($question_id), 'answer_id DESC');
	}

	public function update_answer_attach($answer_id, $attach_access_key)
	{
		if (! $attach_access_key)
		{
			return false;
		}
		
		return $this->update('answer_attach', array(
			'answer_id' => $answer_id
		), "answer_id = 0 AND access_key = '" . $this->quote($attach_access_key) . "'");
	}

	public function remove_answer_attach($id, $access_key)
	{
		$attach = $this->fetch_row('answer_attach', "id = " . intval($id) . " AND access_key = '" . $this->quote($access_key) . "'");
		
		if (! $attach)
		{
			return false;
		}
		
		foreach(GZ_APP::config()->get('image')->attachment_thumbnail AS $key => $val)
		{
			@unlink(get_setting('upload_dir').'/answer/' . date('Ymd/', $attach['add_time']) . $val['w'] . 'x' . $val['h'] . '_' . $attach['file_location']);
		}
		
		@unlink(get_setting('upload_dir').'/answer/' . date('Ymd/', $attach['add_time']) . $attach['file_location']);
		
		return $this->delete('answer_attach', "id = " . intval($id) . " AND access_key = '" . $this->quote($access_key) . "'");
	}

	/*
	public function agree_answer($uid, $answer_id)
	{
		if (intval($answer_id) == 0)
		{
			return false;
		}
		
		$agree = $this->get_answer_agree($uid, $answer_id);
		
		if (! $agree)
		{
			$this->insert('answer_agree', array(
				'uid' => (int)$uid, 
				'answer_id' => (int)$answer_id, 
				'user_name' => $this->model('account')->get_user_name_by_uid($uid), 
				'time' => time()
			));
			
			$this->query("UPDATE " . $this->get_table('answer') . " SET agree_count = agree_count + 1 WHERE answer_id = " . intval($answer_id));
			
			$ans_info = $this->get_answer_info_by_id($answer_id, 0, false);
			
			ACTION_LOG::save_action(USER::get_client_uid(), $ans_info['question_id'], ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::ADD_AGREE, 0, intval($answer_id));
			
			return true;
		}
		else
		{
			$this->delete('answer_agree', "uid = " . intval($uid) . " AND answer_id = " . intval($answer_id));
			
			$this->query("UPDATE " . $this->get_table('answer') . " SET agree_count = agree_count - 1 WHERE answer_id = " . intval($answer_id));
			
			return false;
		}
		
		return false;
	}
	*/

	/*
	public function get_answer_agree($uid, $answer_id)
	{
		return $this->fetch_row('answer_agree', "uid = " . intval($uid) . " AND answer_id = " . intval($answer_id));
	}
	*/

	public function get_answer_agree_users($answer_id)
	{
		$agrees = $this->fetch_all('answer_vote', "answer_id = " . intval($answer_id) . " AND vote_value = 1");
		
		foreach ($agrees as $key => $val)
		{
			$agree_uids[] = $val['vote_uid'];
		}
		
		$users = $this->model('account')->get_users_by_uids($agree_uids);
		
		foreach ($users as $key => $val)
		{
			$user_infos[$val['uid']] = $val;
		}
		
		foreach ($agree_uids as $key => $val)
		{
			$agree_users[$val] = $user_infos[$val]['user_name'];
		}
		
		return $agree_users;
	}

	/**
	 * 根据用户 id 集获得赞同过的回复关联的问题列表
	 */
	public function get_agree_question_list_by_users($count = false, $uids = array(), $limit = '0,10')
	{
		if (empty($uids))
		{
			return false;
		}
		
		$uid_array_str = implode(",", $uids);
		
		if ($count)
		{
			$count = $this->query_row("SELECT COUNT(*) AS total FROM " . $this->get_table('answer_vote') . " AS ag LEFT JOIN " . $this->get_table('answer') . " AS an ON ag.answer_id = an.answer_id WHERE ag.vote_uid in (" . $uid_array_str . ")");
			
			return $count['total'];
		}
		else
		{
			$sql = "SELECT DISTINCT(an.question_id) FROM " . $this->get_table('answer_vote') . " AS ag LEFT JOIN " . $this->get_table('answer') . " AS an ON ag.answer_id=an.answer_id WHERE ag.vote_uid in (" . $uid_array_str . ")";
			
			if (! $rs = $this->query_all($sql, $limit))
			{
				return false;
			}
			
			foreach ($rs as $key => $val)
			{
				$q_array[] = $val['question_id'];
			}
			
			return $this->fetch_all('question', "question_id IN(" . implode(",", $q_array) . ")", "modify_time DESC");
		}
	}

	public function update_answer_comments_count($answer_id)
	{
		$count = $this->count('answer_comments', "answer_id = " . intval($answer_id));
		
		$this->update('answer', array(
			'comment_count' => $count
		), "answer_id = " . intval($answer_id));
	}

	public function insert_answer_comment($answer_id, $uid, $message)
	{
		$data = array(
			'uid' => intval($uid), 
			'answer_id' => intval($answer_id), 
			'message' => htmlspecialchars($message), 
			'time' => time()
		);
		
		$comment_id = $this->insert('answer_comments', $data);
		
		$this->update_answer_comments_count($answer_id);
		
		return $comment_id;
	}

	public function get_answer_comments($answer_id)
	{
		return $this->fetch_all('answer_comments', "answer_id = " . intval($answer_id), "time ASC");
	}
	
	public function user_rated($type, $answer_id, $uid)
	{
		switch ($type)
		{
			default:
				return false;
			break;
			
			case 'thanks':
			case 'uninterested':
				
			break;
		}
		
		static $user_rated;
		
		if ($user_rated[$type . '_' . $answer_id . '_' . $uid])
		{
			return $user_rated[$type . '_' . $answer_id . '_' . $uid];
		}
		
		$user_rated[$type . '_' . $answer_id . '_' . $uid] = $this->fetch_row('answer_' . $type, 'uid = ' . intval($uid) . ' AND answer_id = ' . intval($answer_id));
		
		return $user_rated[$type . '_' . $answer_id . '_' . $uid];
	}
	
	public function user_rate($type, $answer_id, $uid, $user_name)
	{		
		switch ($type)
		{
			default:
				return false;
			break;
			
			case 'thanks':
			case 'uninterested':
				
			break;
		}
		
		if ($user_rated = $this->user_rated($type, $answer_id, $uid))
		{
			$this->delete('answer_' . $type, 'uid = ' . intval($uid) . ' AND answer_id = ' . intval($answer_id));
		}
		else
		{
			$this->insert('answer_' . $type, array(
				'uid' => $uid,
				'answer_id' => $answer_id,
				'user_name' => $user_name,
				'time' => time()
			));
		}
		
		$this->update_user_rate_count($type, $answer_id);
		
		$answer_info = $this->get_answer_by_id($answer_id);
		
		$this->model('account')->update_thanks_count($answer_info['uid']);
		
		return !$user_rated;
	}
	
	public function update_user_rate_count($type, $answer_id)
	{
		switch ($type)
		{
			default:
				return false;
			break;
			
			case 'thanks':
			case 'uninterested':
				
			break;
		}
		
		$this->update('answer', array(
			$type . '_count' => $this->count('answer_' . $type, 'answer_id = ' . intval($answer_id))
		), 'answer_id = ' . intval($answer_id));
	}
	
	public function get_answer_by_id($answer_id)
	{
		static $answers;
		
		if ($answers[$answer_id])
		{
			return $answers[$answer_id];
		}
		
		$answers[$answer_id] = $this->fetch_row('answer', 'answer_id = ' . intval($answer_id));
		
		return $answers[$answer_id];
	}
	
	public function get_answer_counts($question_ids, $where = '',$limit = '')
	{
		if ((!is_array($question_ids)) || (!$question_ids))
		{
			return array();
		}
		
		$sql = "SELECT question_id, COUNT(*) AS count FROM " . $this->get_table('answer')." WHERE question_id IN(" . implode(",", $question_ids) . ")";
		
		if ($where)
		{
			$sql .= ' AND ' . $where;
		}
			
		$sql .= ' GROUP BY question_id';

		return $this->query_all($sql, $limit);
	}
}