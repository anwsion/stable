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

class question_topic_class extends GZ_MODEL
{

	/**
	 * 根据话题ID组合,得到相关问题
	 * @param array $topic_ids
	 * @param string $limit
	 * 
	 * @return array
	 */
	public function get_question_list_by_topic_id(array $topic_ids, $limit = "10", $orderby = 'q.question_id DESC')
	{
		$topic_ids = array_unset_null_value($topic_ids);
		
		if (empty($topic_ids))
		{
			return false;
		}
		
		$sql = "SELECT q.*,tn.topic_id,tn.topic_title FROM " . $this->get_table('question') . " AS q ";
		$sql .= "LEFT JOIN " . $this->get_table('topic_question') . " AS ql ON q.question_id = ql.question_id ";
		$sql .= "LEFT JOIN " . $this->get_table('topic') . " AS tn ON ql.topic_id = tn.topic_id ";
		$sql .= "WHERE tn.topic_id IN (" . implode(",", $topic_ids) . ") ";
		$sql .= "ORDER BY " . $orderby;
		
		return $this->query_all($sql, $limit);
	}

	/**
	 * 
	 * 获取问题列表
	 * @param array $topic_ids
	 * @param string $limit
	 * 
	 * @return array
	 */
	public function get_question_list($limit = "10", $orderby = 'question_id DESC')
	{
		return $this->fetch_all('question', '', $orderby, $limit);
	}

	/**
	 * 
	 * 根据话题ID，得到相关联的问题列表信息
	 * @param int $topic_id
	 * @param string $limit
	 * 
	 * @return array
	 */
	public function get_question_topic_by_topic_id($topic_id, $limit = "10")
	{
		$topic_id = intval($topic_id);
		
		if ($topic_id <= 0)
		{
			return false;
		}
		
		$sql = "SELECT q.* FROM " . $this->get_table('question') . " AS q ";
		$sql .= "LEFT JOIN " . $this->get_table('topic_question') . " AS ql ON q.question_id = ql.question_id ";
		$sql .= "LEFT JOIN " . $this->get_table('topic') . " AS tn ON ql.topic_id = tn.topic_id ";
		$sql .= "WHERE tn.topic_id = " . $topic_id . " ";
		$sql .= "ORDER BY q.question_id DESC";
		
		return $this->query_all($sql, $limit);
	}

	/**
	 * 
	 * 根据话题ID，得到相关联的问题没有回答列表信息
	 * @param int $topic_id
	 * @param string $limit
	 * 
	 * @return array
	 */
	public function get_no_answer_question_topic_by_topic_id($topic_id, $limit = "10")
	{
		$topic_id = intval($topic_id);
		
		if ($topic_id <= 0)
		{
			return false;
		}
		
		$sql = "SELECT q.* FROM " . $this->get_table('question') . " AS q ";
		$sql .= "LEFT JOIN " . $this->get_table('topic_question') . " AS ql ON q.question_id = ql.question_id ";
		$sql .= "LEFT JOIN " . $this->get_table('topic') . " AS tn ON ql.topic_id = tn.topic_id ";
		$sql .= "WHERE tn.topic_id = " . $topic_id . " AND q.answer_count=0 ";
		$sql .= "ORDER BY q.question_id DESC";
		
		return $this->query_all($sql, $limit);
	}

	/**
	 * 
	 * 根据问题ID,得到相关联的话题标题信息
	 * @param int $question_id
	 * @param string $limit
	 * 
	 * @return array
	 */
	public function get_question_topic_by_question_id($question_id, $limit = '')
	{
		$question_id = intval($question_id);
		
		if ($question_id <= 0)
		{
			return false;
		}
		
		$sql = "SELECT tn.* FROM " . $this->get_table('topic') . " AS tn ";
		$sql .= "LEFT JOIN " . $this->get_table('topic_question') . " AS tq ON tq.topic_id = tn.topic_id ";
		$sql .= "WHERE tq.question_id = " . $question_id . " ";
		$sql .= "ORDER BY tq.question_id DESC";
		
		return $this->query_all($sql, $limit);
	}

	/**
	 * 
	 * 生成问题与话题相关联
	 * @param int $data $topic_id //话题id
	 * @param int $question_id    //问题ID
	 * @param int $add_time       //添加时间
	 * 
	 * @return boolean true|false
	 */
	public function save_link($topic_id, $question_id, $add_time = '')
	{
		$topic_id = intval($topic_id);
		$question_id = intval($question_id);
		
		if ($topic_id == 0 || $question_id == 0)
		{
			return false;
		}
		
		$flag = $this->has_link_by_question_topic($topic_id, $question_id);
		
		if ($flag)
		{
			return $flag;
		}
		
		$data = array(
			'topic_id' => $topic_id, 
			'question_id' => $question_id, 
			'add_time' => (intval($add_time) == 0) ? time() : $add_time, 
			'uid' => USER::get_client_uid()
		);
		
		return $this->insert('topic_question', $data);
	}

	/**
	 * 
	 * 删除话题与问题想关联
	 * @param int $topic_question_id
	 * 
	 * @return boolean true|false
	 */
	public function delete_link($topic_question_id)
	{
		return $this->delete('topic_question', 'topic_question_id = ' . intval($topic_question_id));
	}

	/**
	 * 
	 * 判断是否话题与问题已经相关联
	 * @param int $topic_id
	 * @param int $question_id
	 * 
	 * @return int topic_question_id
	 */
	public function has_link_by_question_topic($topic_id, $question_id)
	{
		$where = "topic_id = " . $topic_id;
		
		if ($question_id > 0)
		{
			$where .= " AND question_id = " . $question_id;
		}
		
		$retval = $this->fetch_row('topic_question', $where);
		
		if ((isset($retval['topic_question_id'])) && ($retval['topic_question_id'] > 0))
		{
			return $retval['topic_question_id'];
		}
		else
		{
			return 0;
		}
	}
}
