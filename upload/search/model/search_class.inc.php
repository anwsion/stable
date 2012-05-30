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

class search_class extends GZ_MODEL
{

	/**
	 * 
	 * 组合 SQL 语句
	 * @param string $q
	 * 
	 * @return string
	 */
	public function create_search_sql($q)
	{
		$sql = "SELECT question_id AS unique_id, question_content AS title, (SELECT 1) AS type FROM " . $this->get_table("question") . " WHERE question_content LIKE '%" . $q . "%' UNION ALL ";
		$sql .= "SELECT topic_id AS unique_id, topic_title AS title, (SELECT 2) AS type FROM " . $this->get_table("topic") . " WHERE topic_title LIKE '%" . $q . "%' UNION ALL ";
		//$sql .= "SELECT competitions_id AS unique_id, competitions_content AS title, (SELECT 4) AS type FROM " . $this->get_table("competitions_main") . " WHERE competitions_content LIKE '%" . $q . "%' UNION ALL ";
		$sql .= "SELECT uid AS uid, user_name AS title, (SELECT 3) AS type FROM " . $this->get_table("users") . " WHERE user_name LIKE '%" . $q . "%'";
		
		return $sql;
	}

	/**
	 * 
	 * 得到查询结果
	 * @param string $q
	 * @param int $limit
	 * 
	 *@return array
	 */
	public function get_result_list($q, $limit = 20)
	{
		if (empty($q))
		{
			return array();
		}
		
		$sql = $this->create_search_sql($this->quote(trim($q)));
		
		return $this->query_all($sql, $limit);
	}

	/**
	 * 
	 * 搜索用户列表
	 * @param string $q
	 * @param int $limit
	 */
	public function search_user_list($q, $limit = 20)
	{
		if (empty($q))
		{
			return array();
		}
		
		$sql = "SELECT MEM.uid AS unique_id, MEM.avatar_file, MEM.user_name AS title, (SELECT 3) AS type, MEB.signature FROM {$this->get_table("users")} MEM LEFT JOIN {$this->get_table("users_attrib")} AS MEB ON MEM.uid = MEB.uid WHERE MEM.user_name LIKE '%" . $this->quote(trim($q)) . "%'";
		
		return $this->query_all($sql, $limit);
	}

	/**
	 * 
	 * 搜索话题列表
	 * @param string $q
	 * @param int $limit
	 */
	public function search_topic_list($q, $limit = 20)
	{
		if (empty($q))
		{
			return array();
		}
		
		$sql = "SELECT focus_count, topic_count, topic_id AS unique_id, topic_title AS title, topic_pic AS pic, (SELECT 2) AS type FROM " . $this->get_table("topic") . " WHERE topic_title LIKE '%" . $this->quote(trim($q)) . "%'";
		
		return $this->query_all($sql, $limit);
	}

	/*public function search_competition_list($q, $limit = 20)
	{
		if (empty($q))
		{
			return array();
		}
		
		$sql = "SELECT contribute_count, competitions_id AS unique_id, competitions_content AS title, (SELECT 4) AS type FROM " . $this->get_table("competitions_main") . " WHERE competitions_content LIKE '%" . $this->quote(trim($q)) . "%'";
		return $this->query_all($sql, $limit);
	}*/

	/**
	 * 
	 * 搜索问题列表
	 * @param string $q
	 * @param int $limit
	 */
	public function search_question_list($q, $limit = 20)
	{
		if (empty($q))
		{
			return array();
		}
		
		$sql = "SELECT comment_count, question_id AS unique_id, question_content AS title, (SELECT 1) AS type FROM " . $this->get_table("question") . " WHERE question_content LIKE '%" . $this->quote(trim($q)) . "%'";
		
		return $this->query_all($sql, $limit);
	}

	public function search_user($q, $limit = 20)
	{
		$q = $this->quote(trim($q));
		
		if (! $limit)
		{
			$limit = 20;
		}
		else
		{
			$limit = $this->quote($limit);
		}
		
		$result_list = $this->search_user_list($q, $limit);
		$data = array();
		
		foreach ($result_list as $result_info)
		{
			$data[] = $this->prase_result_info($result_info);
		}
		
		return $data;
	}

	public function search_topic($q, $limit = 20)
	{
		$q = $this->quote(trim($q));
		
		if (! $limit)
		{
			$limit = 20;
		}
		else
		{
			$limit = $this->quote($limit);
		}
		
		$result_list = $this->search_topic_list($q, $limit);
		$data = array();
		
		foreach ($result_list as $result_info)
		{
			$data[] = $this->prase_result_info($result_info);
		}
		
		return $data;
	}

	public function search_question($q, $limit = 20)
	{
		$q = $this->quote(trim($q));
		
		if (! $limit)
		{
			$limit = 20;
		}
		else
		{
			$limit = $this->quote($limit);
		}
		
		$result_list = $this->search_question_list($q, $limit);
		$data = array();
		
		foreach ($result_list as $result_info)
		{
			$data[] = $this->prase_result_info($result_info);
		}
		
		return $data;
	}

	/*public function search_competition($q, $limit = 20)
	{
		$q = $this->quote(trim($q));
		
		if (! $limit)
		{
			$limit = 20;
		}
		else
		{
			$limit = $this->quote($limit);
		}
		
		$result_list = $this->search_competition_list($q, $limit);
		$data = array();
		
		foreach ($result_list as $result_info)
		{
			$data[] = $this->prase_result_info($result_info);
		}
		
		return $data;
	}*/

	public function search_all($q, $type, $limit = 20)
	{
		$search_type = trim($type);
		
		if (! in_array($search_type, array(
			'all', 
			'user', 
			'invite_user', 
			'topic', 
			'topic_v2', 
			'question'
		)))
		{
			$search_type = 'all';
		}
		
		$q = $this->quote(trim($q));
		
		if (! $limit)
		{
			$limit = 20;
		}
		else
		{
			$limit = $this->quote($limit);
		}
		
		$data = array();
		
		switch ($search_type)
		{
			case 'all' :
				$result_list = $this->get_result_list($q, $limit);
				
				//print_r($result_list); die;
				break;
			
			case 'user' :
				$result_list = $this->search_user_list($q, $limit);
				break;
			
			case 'invite_user' :
				$result_list = $this->search_user_list($q, $limit);
				break;
			
			case 'topic' :
				$result_list = $this->search_topic_list($q, $limit);
				break;
			
			case 'topic_v2' :
				$result_list = $this->search_topic_list($q, $limit);
				
				if ($this->model('topic')->has_topic($q))
				{
					$data[] = array(
						'exist' => 1
					);
				}
				else
				{
					$data[] = array(
						'exist' => 0
					);
				}
				break;
			
			/*case 'competition' :
				$result_list = $this->search_competition_list($q, $limit);
				break;*/
			
			case 'question' :
				$result_list = $this->search_question_list($q, $limit);
				break;
		}
		
		foreach ($result_list as $result_info)
		{
			$data[] = $this->prase_result_info($result_info);
		}
		
		return $data;
	}

	public function prase_result_info($result_info)
	{
		switch ($result_info['type'])
		{
			case 1 :
				$sno = $result_info['unique_id'];
				$url = get_setting('base_url') . '/question/?act=detail&question_id=' . $result_info['unique_id'];
				$name = $result_info['title'];
				$detail = array(
					'comment_count' => $result_info['comment_count']
				);
				break;
			
			case 2 :
				$sno = $result_info['unique_id'];
				$url = get_setting('base_url') . '/topic/?topic_id=' . $result_info['unique_id'];
				$name = $result_info['title'];
				$detail = array(
					'focus_count' => $result_info['focus_count'],
					'topic_pic' => $result_info['pic'] ? get_setting('upload_url') . '/topic/' . $result_info['pic'] : get_setting('base_url') . '/static/common/topic-min-img.jpg', 
					'topic_count' => $result_info['topic_count']
				);
				break;
			
			case 3 :
				$sno = $result_info['unique_id'];
				$url = $this->model('account')->get_url_by_uid($result_info['unique_id']);
				$name = $result_info['title'];
				$detail = array(
					'avatar_file' => $result_info['avatar_file'] ? get_setting('upload_url') . '/avatar/' . $result_info['avatar_file'] : get_setting('base_url') . '/static/common/avatar-min-img.jpg', 
					'signature' => $result_info['signature']
				);
				break;
			
			/*case 4 :
				$sno = $result_info['unique_id'];
				$url = get_setting('base_url') . '/contest/' . $result_info['unique_id'];
				$name = $result_info['title'];
				$detail = array(
					'contribute_count' => $result_info['contribute_count']
				);
				break;*/
		}
		
		if ($name)
		{
			return array(
				'uid' => $result_info['unique_id'], 
				'type' => $result_info['type'], 
				'url' => $url, 
				'sno' => $sno, 
				'name' => $name, 
				'detail' => $detail
			);
		}
	}
}