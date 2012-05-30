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

class question_class extends GZ_MODEL
{

	/**
	 * 得到具有相同回复的问题结合
	 * @param int $question_id
	 * 
	 * @return array
	 */
	public function get_question_same_answer($question_id, $answer_count, $limit = 20, $order_by = 'answer_count, question_id DESC')
	{
		$where = "question_id <> " . intval($question_id);
		
		if (! empty($answer_count))
		{
			$where .= " AND answer_count = " . intval($answer_count);
		}
		
		return $this->fetch_all('question', $where, $order_by, $limit);
	}

	public function get_topic_category_count($category_id)
	{
		return $this->count('question', 'category_id IN(' . implode(',', $this->model('system')->get_category_with_child_ids('question', $category_id)) . ')');
	}

	/**
	 * 
	 * 得到问题列表
	 * @param int $limit
	 */
	public function get_questions($limit = 10, $order_by = 'add_time DESC', $where = null)
	{
		return $this->fetch_all('question', $where, $order_by, $limit);
	}

	/**
	 * 
	 * 得到新问题列表
	 * @param int $limit
	 */
	public function get_new_questions_id($limit = 10, $order = 'add_time DESC', $category_id = null)
	{
		if ($category_id)
		{
			$where = 'category_id IN(' . implode(',', $this->model('system')->get_category_with_child_ids('question', $category_id)) . ')';
		}
		$question_list = $this->fetch_all('question', $where, $order, $limit);
		
		$question_id_list = array();
		
		foreach ($question_list as $question_info)
		{
			$question_id_list[] = $question_info['question_id'];
		}
		
		return $question_id_list;
	}

	/**
	 * 
	 * 得到热门回答列表
	 * @param int $time_limit 默认值两天
	 */
	public function get_hot_question(array $question_ids, $order = null, $limit = null)
	{
		if (empty($question_ids))
		{
			return false;
		}
		
		$question_ids = array_unique($question_ids);
		
		return $this->fetch_all('question', 'question_id IN(' . implode(',', $question_ids) . ')', $order, $limit);
	}

	public function get_hot_question_v2($add_time = 0, $category_id = 0, $limit = '0, 10')
	{
		$limit_tmp = preg_split("/,/", $limit);
		
		$offset = $limit_tmp[0] * 1;
		$length = $limit_tmp[1] * 1;
		
		$question_ids[] = 0;
		
		$hot_question_period = get_setting('hot_question_period') * 1;
		
		if ($hot_question_period < 1)
		{
			$hot_question_period = 30;
		}
		if ($add_time == 0)
		{
			$add_time = mktime() - 60 * 60 * 24 * $hot_question_period; //只处理30天内的
		}
		
		if ($category_id)
		{
			
			$question_all = $this->fetch_all('question', "add_time > " . $add_time . " AND category_id IN(" . implode(',', $this->model('system')->get_category_with_child_ids('question', $category_id)) . ')');
		
		}
		else
		{
			$question_all = $this->fetch_all('question', "add_time > " . $add_time);
		}
		
		foreach ($question_all as $key => $val)
		{
			$question_ids[] = $val['question_id'];
		
		}
		
		//计算分数
		foreach ($question_all as $key => $val)
		{
			
			$scores = ((($val['agree_count'] - $val['against_count'] + 1) / ($val['agree_count'] + $val['against_count'] + 1)) * $val['answer_count'] + $val['focus_count'] / 10 + $val['view_count'] / 100);
			
			$scores = $scores / ((mktime() - $val['add_time']) + (mktime() - $val['update_time']));
			
			$scores = $scores * ($answer_counts[$val['question_id']] * 1 + 1);
			
			$question_all[$key]['scores'] = exp($scores);
		}
		
		if (is_array($question_all))
		{
			$question_all = aasort($question_all, array(
				'-scores'
			));
			
			if ($question_all)
			{
				return array_slice($question_all, $offset, $length);
			}
			else
			{
				return array();
			}
		}
		
		return false;
	}

	/**
	 * 
	 * 根据问题ID,得到关注用户列表
	 */
	public function get_focus_uid_by_question_id($question_id)
	{
		$sql = "SELECT uid, question_content FROM " . $this->get_table('question_focus') . " AS qf, " . $this->get_table('question') . " AS q WHERE qf.question_id = " . intval($question_id) . " AND qf.question_id = q.question_id ";
		
		return $this->query_all($sql);
	}

	/**
	 * 
	 * 根据用户ID，得到用户问过的问题列表
	 * @param boolean $count
	 * @param int $uid
	 * @param int $limit
	 * 
	 * @return array
	 */
	public function get_question_list_by_uid($count = false, $uid = 0, $limit = 20)
	{
		$uid = intval($uid);
		
		if ($uid == 0)
		{
			return false;
		}
		if ($count)
		{
			return $this->count('question', "published_uid = " . $uid);
		}
		else
		{
			return $this->fetch_all('question', "published_uid = " . $uid, 'question_id DESC', $limit);
		}
	
	}

	/**
	 * 得到用户发布的列表
	 * @param  $count
	 * @param  $uids array
	 * @param  $limit
	 */
	public function get_question_list_by_uids($count = false, $uids_array, $limit = 20)
	{
		if (! is_array($uids_array) || empty($uids_array))
		{
			return false;
		}
		
		$uids = implode(',', $uids_array);
		
		if ($count)
		{
			return $this->count('question', "published_uid iN(" . $uids . ")", 'question_id DESC');
		}
		else
		{
			return $this->fetch_all('question', "published_uid IN(" . $uids . ")", 'question_id DESC', $limit);
		}
	}

	/**
	 * 
	 * 得到问题列表简单信息
	 * @param int    $topic_id 话题ID 如为0则选择全部问题 
	 * @param string $limit 条数限制
	 * @param int    $uid
	 * 
	 * @return array
	 */
	public function get_question_info($topic_id = 0, $limit = "", $uid = 0, $return_count = false, $order_key = null, $category_id = null, $answer_count = null)
	{
		$where = array();
		
		$topic_id = intval($topic_id);
		
		if (! $order_key)
		{
			$order_key = 'add_time';
		}
		else
		{
			$order_key = $this->quote($order_key);
		}
		
		if ($topic_id == 0)
		{
			$uninterested_question = $this->get_question_uninterested($uid);
			
			if (! empty($uninterested_question))
			{
				$where[] = "q.question_id NOT IN(" . implode(",", $uninterested_question) . ")";
			}
			
			if (! empty($category_id))
			{
				$where[] = 'q.category_id IN(' . implode(',', $this->model('system')->get_category_with_child_ids('question', $category_id)) . ')';
			}
			
			$sql = "FROM " . $this->get_table('question') . " AS q LEFT JOIN " . $this->get_table('users') . " AS u ON q.published_uid = u.uid";
			
			if ($where)
			{
				$sql .= ' WHERE ' . implode(' AND ', $where);
			}
			
			if ($return_count)
			{
				$sql_pre = "SELECT COUNT(*) AS count ";
				
				$data = $this->query_row($sql_pre . $sql);
				
				return $data['count'];
			}
			else
			{
				$sql_pre = "SELECT *, q.answer_count as answer_count, u.user_name, u.avatar_file ";
				
				$sql = $sql_pre . $sql;
			}
			
			$sql .= " ORDER BY " . $order_key . " DESC ";
		}
		else
		{
			$sql = "SELECT q.*,tn.*, q.answer_count as answer_count FROM " . $this->get_table('question') . " AS q ";
			$sql .= "LEFT JOIN " . $this->get_table('topic_question') . " AS ql ON q.question_id = ql.question_id ";
			$sql .= "LEFT JOIN " . $this->get_table('topic') . " AS tn ON ql.topic_id = tn.topic_id ";
			$sql .= "WHERE tn.topic_id = " . intval($topic_id);
			
			if ($answer_count !== null)
			{
				$sql .= " AND  q.answer_count = " . intval($answer_count);
			}
			
			if (! empty($category_id))
			{
				$sql .= ' AND q.category_id IN(' . implode(',', $this->model('system')->get_category_with_child_ids('question', $category_id)) . ')';
			}
			
			$sql .= " ORDER BY q." . $order_key . " DESC";
			
			if ($return_count)
			{
				$sql = "SELECT COUNT(*) AS count FROM " . $this->get_table('question') . " AS q ";
				$sql .= "LEFT JOIN " . $this->get_table('topic_question') . " AS ql ON q.question_id = ql.question_id ";
				$sql .= "LEFT JOIN " . $this->get_table('topic') . " AS tn ON ql.topic_id = tn.topic_id ";
				$sql .= "WHERE tn.topic_id = " . intval($topic_id);
				if ($answer_count !== null)
				{
					$sql .= " AND q.answer_count = " . intval($answer_count);
				}
				
				if (! empty($category_id))
				{
					$sql .= ' AND q.category_id IN(' . implode(',', $this->model('system')->get_category_with_child_ids('question', $category_id)) . ')';
				}
				
				$data = $this->query_row($sql);
				
				return $data['count'];
			}
		}
		
		return $this->query_all($sql, $limit);
	}

	/**
	 * 获取问题列表
	 * Enter description here ...
	 * @param $count
	 * @param $keyword
	 * @param $limit
	 */
	public function get_question_list($count = false, $keyword = '', $limit = 10, $orderby = 'add_time DESC', $where = null)
	{
		$_where = array();
		
		if ($where)
		{
			$_where[] = $where;
		}
		
		if (! empty($keyword))
		{
			$_where[] = "(question_content LIKE '%{$keyword}%' OR question_detail LIKE '%{$keyword}%')";
		}
		
		if ($count)
		{
			return $this->count('question', implode(' AND ', $_where));
		}
		else
		{
			return $this->fetch_all('question', implode(' AND ', $_where), $orderby, $limit);
		}
	}

	/**
	 * 
	 * 得到单条问题信息
	 * 
	 * @return array
	 */
	public function get_question_info_by_id($question_id)
	{
		if (!$question_id)
		{
			return false;
		}
		
		static $questions;
		
		if ($questions[$question_id])
		{
			return $questions[$question_id];
		}
		
		$questions[$question_id] = $this->fetch_row('question', "question_id = " . intval($question_id));
		
		return $questions[$question_id];
	}

	/**
	 * 
	 * 增加问题浏览次数记录
	 * @param int $question_id
	 * 
	 * @return boolean true|false
	 */
	public function increament_question_visist_count($question_id)
	{		
		return $this->query("UPDATE " . $this->get_table('question') . " SET view_count = view_count + 1 WHERE question_id = " . intval($question_id));
	}

	/**
	 * 
	 * 增加问题内容
	 * @param string $question_content //问题内容
	 * @param string $question_detail  //问题说明
	 * @param int $add_time //添加时间
	 * @param int $modify_time //最后修改时间
	 * @param int $published_uid //发布用户UID
	 * @param int $modify_uid //最后修改用户UID
	 * @param int $answer_count //回答计数
	 * @param int $view_count //浏览次数
	 * @param int $focus_count //关注数
	 * @param int $last_action //最后操作的的类型 1回答 2关注 3赞同 4反对
	 * 
	 * @return boolean true|false
	 */
	public function save_question($question_content, $question_detail, $add_time = 0, $modify_time = 0, $published_uid = 0, $modify_uid = 0, $answer_count = 0, $view_count = 0, $focus_count = 0, $last_action = 0, $last_uid = 0, $point = 0, $action_log = true)
	{
		$published_uid = (intval($published_uid) == 0) ? USER::get_client_uid() : $published_uid;
		$modify_uid = (intval($modify_uid) == 0) ? USER::get_client_uid() : $modify_uid;
		$last_uid = (intval($last_uid) == 0) ? USER::get_client_uid() : $last_uid;
		
		$data = array(
			"question_content" => htmlspecialchars($question_content), 
			"question_detail" => htmlspecialchars($question_detail), 
			"add_time" => (intval($add_time) == 0) ? time() : $add_time, 
			"modify_time" => (intval($modify_time) == 0) ? time() : $modify_time, 
			"update_time" => (intval($modify_time) == 0) ? time() : $modify_time, 
			"published_uid" => $published_uid, 
			"modify_uid" => $modify_uid, 
			"answer_count" => intval($answer_count), 
			"view_count" => intval($view_count), 
			"focus_count" => intval($focus_count), 
			"last_action" => ACTION_LOG::ADD_QUESTION, 
			"last_uid" => $last_uid
		);
		
		$question_id = $this->insert('question', $data);
		
		//更新个人问题数量
		if ($question_id)
		{
			$this->update_user_question_count($published_uid);
		}
		
		if ($action_log)
		{
			//记录日志
			ACTION_LOG::save_action($published_uid, $question_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::ADD_QUESTION, htmlspecialchars($question_content), htmlspecialchars($question_detail));
			
			//自动关注该问题
			$this->add_focus_question($question_id, false);
		}
		
		return $question_id;
	}

	function update_question_category($question_id, $category_id)
	{
		if (! $question_id or ! $category_id)
		{
			return false;
		}
		
		return $this->update('question', array(
			'category_id' => $category_id
		), 'question_id = ' . intval($question_id));
	}

	/**
	 * 更新用户问题计数器
	 */
	function update_user_question_count($uid)
	{
		if (empty($uid))
		{
			$uid = USER::get_client_uid();
		}
		
		$user_count = $this->get_question_list_by_uid(true, $uid);
		
		return $this->update('users', array(
			'question_count' => $user_count
		), 'uid = ' . intval($uid));
	}

	/**
	 * 
	 * 更改问题最后修改状态
	 * @param int $question_id
	 * @param int $modify_uid
	 * @param int $state
	 * 
	 * @return boolean true|false
	 */
	public function update_question_state($question_id, $modify_uid, $state, $last_uid = 0)
	{
		$question_id = intval($question_id);
		$modify_uid = intval($modify_uid);
		$last_uid = (intval($last_uid) == 0) ? USER::get_client_uid() : $last_uid;
		
		if ($question_id == 0 && $modify_uid == 0)
		{
			return false;
		}
		
		$sate_array = array(
			ACTION_LOG::ADD_QUESTION, 
			ACTION_LOG::ADD_REQUESTION_FOCUS, 
			ACTION_LOG::ANSWER_QUESTION, 
			ACTION_LOG::ADD_TOPIC, 
			ACTION_LOG::DELETE_REQUESTION, 
			ACTION_LOG::DELETE_REQUESTION_FOCUS, 
			ACTION_LOG::DELETE_ANSWER, 
			ACTION_LOG::DELETE_TOPIC, 
			ACTION_LOG::MOD_QUESTION_DESCRI, 
			ACTION_LOG::MOD_QUESTON_TITLE
		);
		
		if (! in_array($state, $sate_array))
		{
			return false;
		}
		
		$data = array(
			'last_action' => $state, 
			'last_uid' => $last_uid, 
			'modify_uid' => $modify_uid
		);
		
		return $this->update('question', $data, "question_id = " . $question_id);
	}

	/**
	 * 更新禁止回答设置
	 * Enter description here ...
	 * @param $question_id
	 * @param $forbi_answer
	 */
	public function update_question_answer_status($question_id, $forbi_answer = 0)
	{
		return $this->update('question', array(
			'forbi_answer' => $forbi_answer
		), 'question_id = ' . $question_id);
	}

	/**
	 * 
	 * 修改问题内容
	 * @param array $data 修改问题内容
	 * @param int $question_id 问题ID
	 * @param int $modfiy_type 0 标题 1 描述
	 * 
	 * @return boolean true|false
	 */
	public function update_question($question_id, $question_content = "", $question_detail = "")
	{
		$question_id = intval($question_id);
		
		if ($question_id == 0)
		{
			return false;
		}
		
		$quesion_info = $this->get_question_info_by_id($question_id);
		$question_content = htmlspecialchars($question_content);
		$question_detail = htmlspecialchars($question_detail);
		
		$uid = USER::get_client_uid();
		
		$update_arr = array();
		$update_arr['modify_uid'] = $uid;
		$update_arr['modify_time'] = time();
		
		if (! empty($question_content))
		{
			$update_arr['question_content'] = $question_content;
		}
		
		if (! empty($question_detail))
		{
			$update_arr['question_detail'] = $question_detail;
		}
		
		$this->update('question', $update_arr, "question_id = " . $question_id);
		
		//自动关注该问题
		$this->add_focus_question($question_id, false);
		
		//记录日志
		if ($quesion_info['question_content'] != $question_content)
		{
			$this->update_question_state($question_id, $uid, ACTION_LOG::MOD_QUESTON_TITLE);
			ACTION_LOG::save_action($uid, $question_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::MOD_QUESTON_TITLE, htmlspecialchars($question_content), $quesion_info['question_content']);
		}
		
		if ($quesion_info['question_detail'] != $question_detail)
		{
			$this->update_question_state($question_id, $uid, ACTION_LOG::MOD_QUESTION_DESCRI);
			ACTION_LOG::save_action($uid, $question_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::MOD_QUESTION_DESCRI, htmlspecialchars($question_detail), $quesion_info['question_detail']);
		}
		
		return true;
	}

	/**
	 * 物理删除问题以及与问题相关的内容
	 * @param $question_id
	 */
	public function remove_question($question_id)
	{
		$question_id = intval($question_id);
		
		if (! $question_id)
		{
			return false;
		}
		
		$question_info = $this->get_question_info_by_id($question_id);
		
		if (empty($question_info))
		{
			return false;
		}
		
		$this->model('answer')->delete_answers_by_question_id($question_id); //删除关联的回复内容
		

		$this->model("associate_index")->del_associate_index($question_info['published_uid'], $question_id, 3); //更新首页索引表
		

		$focus_users = $this->get_focus_users_by_question($question_id);
		
		if ($focus_users)
		{
			foreach ($focus_users as $key => $user)
			{
				$this->model("associate_index")->del_associate_index($user['uid'], $question_id, 1); //更新首页索引表
			}
			
			$this->delete('question_focus', "question_id = " . $question_id);
		}
		
		$this->delete('topic_question', "question_id = " . $question_id); //删除话题关联
		

		$this->delete('question_uninterested', "question_id = " . $question_id); //删除不感兴趣的
		

		$this->delete('user_action_history', "associate_type = 1 AND associate_id = " . $question_id); //删除动作
		

		//删除附件
		$attachs = $this->get_question_attach($question_id);
		
		if ($attachs)
		{
			foreach ($attachs as $key => $val)
			{
				$this->model('publish')->remove_question_attach($val['id'], $val['access_key']);
			}
		}
		
		$this->model('notify')->delete_notify('model_type = 1 AND source_id = ' . $question_id);	//删除相关的通知
		
		return $this->delete('question', "question_id = " . $question_id); //删除问题
	}

	public function remove_question_by_ids($question_ids)
	{
		if (empty($question_ids))
		{
			return false;
		}
		
		$question_ids = array_unique($question_ids);
		
		foreach ($question_ids as $key => $val)
		{
			$this->remove_question($val);
		}
		
		return true;
	}

	/**
	 * 
	 * 增加问题关注
	 * @param int $question_id
	 * @param boolean $auto_delete 以关注问题是否删除
	 * 
	 * @return boolean true|false
	 */
	public function add_focus_question($question_id, $auto_delete = true)
	{
		$question_id = intval($question_id);
		
		$uid = intval(USER::get_client_uid());
		
		if ($question_id == 0 || $uid == 0)
		{
			return false;
		}
		
		if (! $this->has_focus_question($question_id, $uid))
		{
			$data = array(
				"question_id" => $question_id, 
				"uid" => $uid, 
				"add_time" => time()
			);
			
			$retval = $this->insert('question_focus', $data);
			
			//增加问题关注数量
			if ($retval)
			{
				$this->update_focus_count($question_id);
			}
			
			//更改最后状态
			$this->update_question_state($question_id, $uid, ACTION_LOG::ADD_REQUESTION_FOCUS);
			//记录日志
			ACTION_LOG::save_action($uid, $question_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::ADD_REQUESTION_FOCUS);
			
			//添加索引
			$this->model("associate_index")->add_associate_index($uid, $question_id, 1);
			
			return "add";
		}
		else
		{
			if ($auto_delete)
			{
				$retval = $this->delete_focus_question($question_id);
				//减少问题关注数量
				if ($retval)
				{
					$this->update_focus_count($question_id);
				}
			}
			
			//删除索引
			$this->model("associate_index")->del_associate_index($uid, $question_id, 1);
			
			return "remove";
		}
	}

	/**
	 * 
	 * 取消问题关注
	 * @param int $question_id
	 * 
	 * @return boolean true|false
	 */
	public function delete_focus_question($question_id)
	{
		$uid = USER::get_client_uid();
		
		if ($question_id == 0 || $uid == 0)
		{
			return false;
		}
		
		//更改最后状态
		$this->update_question_state($question_id, $uid, ACTION_LOG::DELETE_REQUESTION_FOCUS);
		
		//记录日志
		ACTION_LOG::save_action($uid, $question_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::DELETE_REQUESTION_FOCUS);
		
		return $this->delete('question_focus', "question_id = " . $question_id . " AND uid = " . $uid);
	}

	/**
	 * 
	 * 根据用户，批量判断是否关注话题
	 * @param int $question_id
	 * @param int $uid
	 * 
	 * @return boolean true|false
	 */
	public function get_focus_question_by_uid(array $question_ids, $uid)
	{
		$data = array();
		
		$uid = intval($uid);
		
		if ($uid == 0)
		{
			return false;
		}
		
		$where[] = "uid = " . $uid;
		
		if (! empty($question_ids))
		{
			$ids = implode(",", $question_ids);
			
			$where[] = "question_id IN(" . $ids . ")";
		}
		
		$recordset_list = $this->fetch_all('question_focus', implode(' AND ', $where));
		
		foreach ($recordset_list as $record)
		{
			$data[] = $record['question_id'];
		}
		
		return $data;
	}

	/**
	 * 根据用户,获取批量用户关注的问题.
	 * 
	 * @param bool $count;
	 * @param int  $uid
	 * @param string $limit
	 * 
	 * @return array $row
	 */
	public function get_focus_question_list_by_uid($count = false, $uid, $limit = '10')
	{
		$uid = intval($uid);
		
		if ($uid == 0)
		{
			return false;
		}
		
		if ($count) //查询数量的话返回count
		{
			return $this->count('question_focus', 'uid = ' . $uid);
		}
		
		//取出关注ID,不要用关联表查询,提高查询效率
		$rs_ids = $this->fetch_all('question_focus', "uid = " . $uid, "question_id DESC", $limit);
		
		if (empty($rs_ids))
		{
			return false;
		}
		
		foreach ($rs_ids as $key => $val)
		{
			$rs_ids_array[] = $val["question_id"];
		}
		
		$ids = implode(",", $rs_ids_array);
		
		//获取问题列表
		return $this->fetch_all('question', "question_id IN(" . $ids . ")", 'question_id DESC');
	}

	/**
	 * 根据用户ID,得到所有最后操作是该用户的所有问题
	 *  
	 * @param  $count
	 * @param  $uid
	 * @param  $limit
	 */
	public function get_question_list_for_last_uid($count = false, $uid, $limit = '10')
	{
		$uid = intval($uid);
		
		if ($uid == 0)
		{
			return false;
		}
		
		return $this->fetch_all('question', "last_uid = " . $uid, "question_id DESC");
	}

	/**
	 * 获取特定动作操作的问题列表ID集合(用户数组集)
	 *  
	 * @param  $count
	 * @param  $uids
	 * @param  $actions
	 * @param  $limit
	 */
	public function get_question_list_for_actions($count = false, $uids, $actions, $limit = '10')
	{
		if (! is_array($uids))
		{
			return false;
		}
		
		if (empty($uids))
		{
			return false;
		}
		
		$actions = trim($actions);
		
		if (empty($actions))
		{
			return false;
		}
		
		$where = " uid IN(" . implode(",", $uids) . ") AND associate_type = 1 AND associate_action IN(" . $actions . ") ";
		
		$question_list = ACTION_LOG::get_actions_distint_by_where($where, $limit);
		
		if (! $question_list)
		{
			return array();
		}
		
		foreach ($question_list as $key => $val)
		{
			$questions_array[] = $val["associate_id"];
		}
		
		$questions_array_str = implode(",", $questions_array);
		
		if ($count)
		{
			return $this->count('question', "question_id IN(" . $questions_array_str . ")");
		}
		else
		{
			return $this->fetch_all('question', "question_id IN(" . $questions_array_str . ")", "modify_time DESC", $limit);
		}
	}

	/**
	 * 根据用户,获取批量用户关注的问题.
	 * 
	 * @param bool $count;
	 * @param int  $uid
	 * @param string $limit
	 * 
	 * @return array $row
	 */
	public function get_focus_question_list_by_uids($count = false, $uid_array, $limit = '10')
	{
		//判断
		if (! is_array($uid_array))
		{
			return false;
		}
		
		if ($count) //查询数量的话返回count
		{
			return $this->count('question_focus', "uid IN( " . implode(",", $uid_array) . ")");
		}
		
		//取出关注ID,不要用关联表查询,提高查询效率
		$rs_ids = $this->fetch_all('question_focus', "uid IN( " . implode(",", $uid_array) . ")", '', $limit);
		
		if (empty($rs_ids))
		{
			return false;
		}
		
		$rs_ids_array = array();
		
		foreach ($rs_ids as $key => $val)
		{
			$rs_ids_array[] = $val["question_id"];
		}
		
		//获取问题列表
		return $this->fetch_all('question', "question_id IN(" . implode(",", $rs_ids_array) . ")", "question_id DESC");
	}

	/**
	 * 
	 * 判断是否已经关注问题
	 * @param int $question_id
	 * @param int $uid
	 * 
	 * @return boolean true|false
	 */
	public function has_focus_question($question_id, $uid)
	{
		if (intval($uid) <= 0)
		{
			return false;
		}
		
		if (intval($question_id) <= 0)
		{
			return false;
		}
		
		$retval = $this->fetch_row('question_focus', "question_id = " . intval($question_id) . " AND uid = " . intval($uid));
		
		if ($retval)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function get_focus_users_by_question($question_id)
	{
		$uids = $this->query_all("SELECT DISTINCT uid FROM " . $this->get_table('question_focus') . " WHERE question_id = " . intval($question_id));
		
		$users_list = array();
		
		foreach ($uids as $key => $user)
		{
			$user_info = $this->model('account')->get_users_by_uid($user['uid']);
			
			$user_info['avatar_file'] = get_avatar_url($user_info['uid'], 'mid', $user_info['avatar_file']);
			
			$users_list[$user_info['uid']] = $user_info;
		}
		
		return $users_list;
	}

	public function get_user_focus($uid, $limit = 10)
	{
		$get_my_focus = $this->query_all('question_focus', "uid = " . (int)$uid . " AND question_id > 0");
		
		$my_focus = array();
		
		foreach ($get_my_focus as $key => $val)
		{
			$my_focus[] = $val['question_id'];
		}
		
		return $this->fetch_all('question', "question_id IN(" . implode(',', $my_focus) . ")", "add_time DESC", $limit);
	}

	public function get_user_publish($uid, $limit = 10)
	{
		return $this->fetch_all('question', "published_uid = " . intval($uid), "add_time DESC", $limit);
	}
	
	// 我关注的人关注的问题的话题下的问题
	public function get_user_recommend_v2($uid, $limit = 10)
	{
		$get_my_focus = $this->fetch_all('question_focus', "uid = " . (int)$uid . " AND question_id > 0");
		
		$my_focus = array(
			0
		);
		
		foreach ($get_my_focus as $key => $val)
		{
			$my_focus[] = $val['question_id'];
		}
		
		$friends = $this->model('follow')->get_user_friends($uid, false);
		
		foreach ($friends as $key => $val)
		{
			$follow_uids[] = $val['friend_uid'];
		}
		
		if (! $follow_uids)
		{
			return $this->get_questions($limit, "add_time DESC", "question_id NOT IN (" . implode(',', $my_focus) . ")");
		}
		
		$question_focus = $this->query_all("SELECT DISTINCT question_id FROM " . $this->get_table('question_focus') . " WHERE uid IN(" . implode($follow_uids, ',') . ") AND question_id NOT IN (" . implode($my_focus, ',') . ") ORDER BY add_time DESC", $limit);
		
		foreach ($question_focus as $key => $val)
		{
			$questions_ids[] = $val['question_id'];
		}
		
		if (! $questions_ids)
		{
			return $this->get_questions($limit, "add_time DESC", "question_id NOT IN (" . implode($my_focus, ',') . ")");
		}
		
		$topics = $this->query_all("SELECT DISTINCT topic_id FROM " . $this->get_table('topic_question') . " WHERE question_id IN(" . implode($questions_ids, ',') . ") AND question_id NOT IN (" . implode($my_focus, ',') . ") AND uid != " . (int)$uid . " ORDER BY topic_question_id DESC", $limit);
		
		foreach ($topics as $key => $val)
		{
			$topics_ids[] = $val['topic_id'];
		}
		
		if (! $topics_ids)
		{
			return $this->get_questions($limit, "add_time DESC", "question_id NOT IN (" . implode(',', $my_focus) . ")");
		}
		
		unset($questions_ids);
		
		$questions_ids_result = $this->query_all("SELECT DISTINCT question_id FROM " . $this->get_table('topic_question') . " WHERE topic_id IN(" . implode($topics_ids, ',') . ") AND question_id > 0 AND question_id NOT IN (" . implode($my_focus, ',') . ") AND uid != " . (int)$uid . " ORDER BY topic_question_id DESC", $limit);
		
		foreach ($questions_ids_result as $key => $val)
		{
			$questions_ids[] = $val['question_id'];
		}
		
		if (! $questions_ids)
		{
			return $this->get_questions($limit, "add_time DESC", "question_id NOT IN (" . implode(',', $my_focus) . ")");
		}
		
		$questions = $this->fetch_all('question', "question_id IN(" . implode(',', $questions_ids) . ") AND published_uid != " . (int)$uid . " AND question_id NOT IN (" . implode(',', $my_focus) . ")", 'add_time DESC', $limit);
		
		if (sizeof($questions) < $limit)
		{
			$questions_new = $this->get_questions(($limit - sizeof($questions)), "question_id DESC", "question_id NOT IN(" . implode($questions_ids, ',') . ") AND question_id NOT IN (" . implode(',', $my_focus) . ")");
			
			foreach ($questions_new as $key => $val)
			{
				$questions[] = $val;
			}
		}
		
		return $questions;
	}

	public function get_question_attach($question_id)
	{
		$attach = $this->fetch_all('question_attach', "question_id = " . intval($question_id), "is_image DESC");
		
		$attach_list = array();
		
		foreach ($attach as $key => $data)
		{
			$attach_list[$data['id']] = array(
				'id' => $data['id'], 
				'is_image' => $data['is_image'], 
				'file_name' => $data['file_name'], 
				'access_key' => $data['access_key'], 
				'attachment' => get_setting('upload_url') . '/questions/' . date('Ymd', $data['add_time']) . '/' . $data['file_location'], 
				'thumb' => get_setting('upload_url') . '/questions/' . date('Ymd', $data['add_time']) . '/' . GZ_APP::config()->get('image')->attachment_thumbnail['min']['w'] . 'x' . GZ_APP::config()->get('image')->attachment_thumbnail['min']['h'] . '_' . $data['file_location']
			);
		}
		
		return $attach_list;
	}

	/**
	 * 更新问题表字段

	 * @param $question_id
	 * @param $fields
	 */
	public function update_question_field($question_id, $fields)
	{
		$question_id = intval($question_id);
		
		if ($question_id <= 0)
		{
			return false;
		}
		
		return $this->update('question', $fields, "question_id = " . $question_id);
	}

	/**
	 * 更新问题回复计数
	 */
	public function update_answer_count($question_id)
	{
		$question_id = intval($question_id);
		
		if ($question_id <= 0)
		{
			return false;
		}
		
		$count = $this->count('answer', "question_id = " . $question_id);
		
		return $this->update_question_field($question_id, array(
			'answer_count' => $count
		));
	}

	/**
	 * 更新问题回复计数
	 */
	public function update_answer_users_count($question_id)
	{
		$question_id = intval($question_id);
		
		if ($question_id <= 0)
		{
			return false;
		}
		
		$count = $this->count('answer', "question_id = " . $question_id);
		
		return $this->update_question_field($question_id, array(
			'answer_users' => $count
		));
	}

	/**
	 * 更新问题关注计数
	 */
	public function update_focus_count($question_id)
	{
		$question_id = intval($question_id);
		
		if ($question_id <= 0)
		{
			return false;
		}
		
		$count = $this->count('question_focus', "question_id = " . $question_id);
		
		return $this->update_question_field($question_id, array(
			'focus_count' => $count
		));
	}

	/**
	 * 问题详细页面-相关问题
	 * @param $question_id
	 * @param $uid
	 */
	function get_relike_question_list($question_id)
	{
		$question_topics = $this->model('question_topic')->get_question_topic_by_question_id($question_id);
		
		//根据话题ID,得到相关问题
		foreach ($question_topics as $topic_info)
		{
			$topic_infos[] = $topic_info['topic_id'];
		}
		
		if (! empty($topic_infos))
		{
			if ($q_links = $this->model('question_topic')->get_question_list_by_topic_id($topic_infos, 100, "RAND()"))
			{
				foreach ($q_links as $key => $link)
				{
					if ($link['question_id'] == $question_id)
					{
						unset($q_links[$key]);
					}
					else
					{
						$question_lnk[$link['question_id']] = $link['question_content'];
					}
				}
			}
		}
		
		$q_links_num = count($question_lnk);
		
		if ($q_links_num < 100)
		{
			$same_answer_q = $this->get_question_same_answer($question_id, "", "0,100", "RAND()");
			
			foreach ($same_answer_q as $same_question)
			{
				if (! isset($question_lnk[$same_question['question_id']]))
				{
					$question_lnk[$same_question['question_id']] = $same_question['question_content'];
				}
			}
		}
		
		$questions_uninteresteds = $this->get_question_uninterested(USER::get_client_uid());
		
		$questions_uninteresteds[] = 0;
		
		if (! $question_lnk)
		{
			return false;
		}
		
		foreach ($question_lnk as $key => $question_content)
		{
			if (in_array($key, $questions_uninteresteds))
				continue;
			
			$question_lnk_list[] = array(
				'question_id' => $key, 
				'question_content' => $question_content
			);
		}
		
		$question_lnk_list = array_slice($question_lnk_list, 0, 10);
		
		return $question_lnk_list;
	}

	/**
     * 格式化问题
     * 
     * @param  $question_list
     */
	function question_list_process($question_list)
	{
		foreach ($question_list as $qkey => $qval)
		{
			$user_ids[] = $qval["modify_uid"];
			$user_ids[] = $qval["published_uid"];
		}
		
		if (is_array($user_ids))
		{
			$user_ids = array_unique($user_ids);
		}
		
		$user_info_arr = $this->model('account')->get_users_by_uids($user_ids);
		
		foreach ($user_info_arr as $user)
		{
			$user_infos[$user['uid']] = $user;
		}
		
		if (! is_array($question_list))
		{
			return false;
		}
		
		foreach ($question_list as $qkey => $qval)
		{
			//最后动作
			$question_list[$qkey]["last_action_str"] = $this->action_key_val[$qval["last_action"]];
			
			//最后动作真实用户名
			if ($question_list[$qkey]["last_action_str"])
			{
				$question_list[$qkey]["modify_real_name"] = $user_infos[$qval["modify_uid"]]['user_name'];
			}
			
			//是否已经关注
			if ($this->model('question')->has_focus_question($qval["question_id"], $this->user_id))
			{
				$question_list[$qkey]["has_focus_question"] = 1;
			}
			else
			{
				$question_list[$qkey]["has_focus_question"] = 0;
			}
			//得到回答
			$answer = $this->model('answer')->get_answer_list_by_question_id($qval["question_id"], 1);
			
			if ($answer)
			{
				$tmp = array();
				$tmp = FORMAT::format_content($answer[0]["answer_content"]);
				$question_list[$qkey]["answer_id"] = $answer[0]["answer_id"];
				$question_list[$qkey]["answer_title"] = $tmp['content_title'];
				$question_list[$qkey]["answer_content"] = $tmp['content_content'];
				//$question_list[$qkey]["answer"]=$answer[0]["answer_content"];
			}
			
			$question_list[$qkey]["avatar_file"] = $qval['avatar_file'];
			$question_list[$qkey]["published_username"] = $user_infos[$qval["published_uid"]]['user_name'];
		}
		
		return $question_list;
	}

	/**
	 *
	 * 得到用户感兴趣问题列表
	 * @param int $uid
	 * @return array
	 */
	public function get_question_uninterested($uid)
	{
		$data = array();
		
		$questions = $this->fetch_all('question_uninterested', 'uid = ' . intval($uid));
		
		foreach ($questions as $info)
		{
			$data[] = $info['question_id'];
		}
		
		return $data;
	}

	/**
	 *
	 * 保存用户不感兴趣问题列表
	 * @param int $uid
	 * @param int $question_id
	 *
	 * @return boolean true|false
	 */
	public function add_question_uninterested($uid, $question_id)
	{
		$uid = intval($uid);
		$question_id = intval($question_id);
		
		if ($uid == 0 || $question_id == 0)
		{
			return false;
		}
		
		if (! $this->has_question_uninterested($uid, $question_id))
		{
			$data = array(
				"question_id" => $question_id, 
				"uid" => $uid, 
				"add_time" => time() //添加时间
			);
			return $this->insert('question_uninterested', $data);
		}
		else
		{
			return false;
		}
	
	}

	/**
	 *
	 * 删除用户不感兴趣问题列表
	 * @param int $uid
	 * @param int $question_id
	 *
	 * @return boolean true|false
	 */
	public function delete_question_uninterested($uid, $question_id)
	{
		return $this->delete('question_uninterested', "question_id = " . intval($question_id) . " AND uid = " . intval($uid));
	}

	public function has_question_uninterested($uid, $question_id)
	{
		$retval = $this->fetch_row('question_uninterested', "question_id = " . intval($question_id) . " AND uid = " . intval($uid));
		
		if ($retval)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function add_invite($question_id, $sender_uid, $recipients_uid)
	{
		$question_id = intval($question_id);
		$sender_uid = intval($sender_uid);
		$recipients_uid = intval($recipients_uid);
		
		if ($question_id == 0 || $sender_uid == 0 || $recipients_uid == 0)
		{
			return false;
		}
		
		$data = array(
			'question_id' => $question_id, 
			'sender_uid' => $sender_uid, 
			'recipients_uid' => $recipients_uid, 
			'add_time' => time()
		);
		
		return $this->insert('question_invite', $data);
	}

	/**
	 * 发起者取消邀请
	 * @param unknown_type $question_id
	 * @param unknown_type $sender_uid
	 * @param unknown_type $recipients_uid
	 */
	public function cancel_question_invite($question_id, $sender_uid, $recipients_uid)
	{
		return $this->delete('question_invite', 'question_id = ' . intval($question_id) . ' AND sender_uid = ' . intval($sender_uid) . ' AND recipients_uid = ' . intval($recipients_uid));
	}

	/**
	 * 接收者删除邀请
	 * @param unknown_type $question_invite_id
	 * @param unknown_type $recipients_uid
	 */
	public function delete_question_invite($question_invite_id, $recipients_uid)
	{
		return $this->delete('question_invite', 'question_invite_id = ' . intval($question_invite_id) . ' AND recipients_uid = ' . intval($recipients_uid));
	}

	/**
	 * 接收者回复邀请的问题
	 * @param unknown_type $question_invite_id
	 * @param unknown_type $recipients_uid
	 */
	public function answer_question_invite($question_id, $recipients_uid)
	{
		return $this->delete('question_invite', 'question_id = ' . intval($question_id) . ' AND recipients_uid = ' . intval($recipients_uid));
	}

	public function check_question_invite($question_id, $sender_uid, $recipients_uid)
	{
		return $this->fetch_row('question_invite', 'question_id = ' . intval($question_id) . ' AND sender_uid = ' . intval($sender_uid) . ' AND recipients_uid = ' . intval($recipients_uid));
	}

	public function get_invite_users($question_id, $sender_uid)
	{
		$invites = $this->fetch_all('question_invite', 'question_id = ' . intval($question_id) . ' AND sender_uid = ' . intval($sender_uid), 'question_invite_id DESC');
		
		if (empty($invites))
		{
			return array();
		}
		
		$invite_users = array();
		
		foreach ($invites as $key => $val)
		{
			$invite_users[] = $val['recipients_uid'];
		}
		
		$invite_users = array_unique($invite_users);
		
		$user_info = $this->model('account')->get_users_by_uids($invite_users, true);
		
		if (empty($user_info))
		{
			return array();
		}
		
		foreach ($invites as $key => $val)
		{
			$user = $user_info[$val['recipients_uid']];
			$data[] = array(
				'uid' => $user['uid'], 
				'user_name' => $user['user_name'], 
				'signature' => $user['signature'], 
				'avatar_file' => $user['avatar_file'] ? get_setting('upload_url') . '/avatar/' . $user['avatar_file'] : get_setting('base_url') . '/static/common/avatar-mid-img.jpg', 
				'url' => $user['url']
			);
		}
		
		return $data;
	}

	public function get_invite_question_list($uid, $limit = '10', $count = false)
	{
		$uid = intval($uid);
		
		if ($uid == 0)
		{
			return false;
		}

		$sql = "SELECT a.*, b.* FROM " . get_table('question') . " AS a LEFT JOIN " . get_table('question_invite') . " AS b ON a.question_id = b.question_id WHERE b.recipients_uid = " . $uid;
			
		$rs = $this->query_all($sql, $limit);
		
		if($count)
		{
			return count($rs);
		}
		else 
		{
			return $rs;
		}
	}
}