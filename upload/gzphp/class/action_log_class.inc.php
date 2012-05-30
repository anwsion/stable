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

class ACTION_LOG
{
	/** 
	 * 添加问题
	 */
	const ADD_QUESTION = 101;
	/** 
	 * 修改问题标题
	 */
	const MOD_QUESTON_TITLE = 102;
	/** 
	 * 修改问题描述 
	 */
	const MOD_QUESTION_DESCRI = 103;
	/**
	 * 删除问题
	 */
	const DELETE_REQUESTION = 104;
	/**
	 * 添加问题关注
	 */
	const ADD_REQUESTION_FOCUS = 105;
	/**
	 * 删除问题关注
	 */
	const DELETE_REQUESTION_FOCUS = 106;
	/**
	 * 问题重定向
	 */
	const REREWRITE_QUESTION = 107;
	/** 
	 * 回答问题
	 */
	const ANSWER_QUESTION = 201;
	/** 
	 * 修改回答
	 */
	const MOD_ANSWER = 202;
	/**
	 * 删除回答 
	 */
	const DELETE_ANSWER = 203;
	/**
	 * 增加赞同
	 */
	const ADD_AGREE = 204;
	/**
	 * 增加反对投票
	 */
	const ADD_AGANIST = 205;
	/**
	 * 增加感谢作者
	 */
	const ADD_USEFUL = 206;
	/**
	 * 问题没有帮助
	 */
	const ADD_UNUSEFUL = 207;
	/**
	 * 取消赞成
	 */
	const DEL_AGREE = 208;
	/**
	 * 取消反对投票
	 */
	const DEL_AGANIST = 209;
	/** 
	 * 增加评论
	 */
	const ADD_COMMENT = 301;
	/**
	 * 删除评论
	 */
	const DELETE_COMMENT = 302;
	/** 
	 * 创建话题
	 */
	const ADD_TOPIC = 401;
	/** 
	 * 修改话题
	 */
	const MOD_TOPIC = 402;
	/** 
	 * 修改话题描述
	 */
	const MOD_TOPIC_DESCRI = 403;
	/**
	 * 修改话题缩图
	 */
	const MOD_TOPIC_PIC = 404;
	/**
	 * 删除话题
	 */
	const DELETE_TOPIC = 405;
	/**
	 * 添加话题关注
	 */
	const ADD_TOPIC_FOCUS = 406;
	/**
	 * 删除话题关注
	 */
	const DELETE_TOPIC_FOCUS = 407;
	/**
	 * 增加话题分类
	 */
	const ADD_TOPIC_PARENT = 408;
	/**
	 * 删除话题分类
	 */
	const DELETE_TOPIC_PARENT = 409;
	/**
	 * 问题
	 */
	const CATEGORY_QUESTION = 1;
	/**
	 * 回答
	 */
	const CATEGORY_ANSWER = 2;
	/**
	 * 评论
	 */
	const CATEGORY_COMMENT = 3;
	/**
	 * 话题 
	 */
	const CATEGORY_TOPIC = 4;
	
	/**
	 * 
	 * 类型定义对应数组
	 */
	public static $ACTION_STRING_ARRAY = array(
		self::ADD_QUESTION => '添加问题', 
		self::MOD_QUESTON_TITLE => '修改问题标题', 
		self::MOD_QUESTION_DESCRI => '修改问题描述', 
		self::DELETE_REQUESTION => '删除问题', 
		self::ADD_REQUESTION_FOCUS => '添加问题关注', 
		self::DELETE_REQUESTION_FOCUS => '删除问题关注', 
		self::REREWRITE_QUESTION => '问题重定向', 
		self::ANSWER_QUESTION => '回答问题', 
		self::MOD_ANSWER => '修改回答', 
		self::DELETE_ANSWER => '删除回答', 
		self::ADD_AGREE => '增加赞成', 
		self::ADD_AGANIST => '增加反对 ', 
		self::DEL_AGREE => '取消赞成', 
		self::DEL_AGANIST => '取消反对 ', 
		self::ADD_COMMENT => '增加评论', 
		self::DELETE_COMMENT => '删除评论', 
		self::ADD_TOPIC => '添加话题', 
		self::MOD_TOPIC => '修改话题', 
		self::MOD_TOPIC_DESCRI => '修改话题描述', 
		self::MOD_TOPIC_PIC => '修改话题缩略图', 
		self::DELETE_TOPIC => '删除话题', 
		self::ADD_TOPIC_FOCUS => '关注话题', 
		self::DELETE_TOPIC_FOCUS => '取消话题关注', 
		self::ADD_TOPIC_PARENT => '增加话题分类', 
		self::DELETE_TOPIC_PARENT => '删除话题分类',
	);

	/**
	 * 
	 * 增加用户动作跟踪
	 * @param int    $uid
	 * @param int    $associate_id   关联ID
	 * @param int    $action_type    动作大类型
	 * @param int    $action_id      动作详细类型
	 * @param string $action_content 动作内容
	 * @param string $action_attch   动作附加内容
	 * @param int    $add_time       动作发送时间
	 * 
	 * @return boolean true|false
	 */
	public static function save_action($uid, $associate_id, $action_type, $action_id, $action_content = '', $action_attch = '', $add_time = 0)
{
	if (intval($uid) == 0 || intval($associate_id) == 0)
	{
		return false;
	}
	
	$data = array(
		'uid' => intval($uid), 
		'associate_type' => $action_type, 
		'associate_action' => $action_id, 
		'associate_id' => $associate_id, 
		'associate_content' => FORMAT::safe($action_content,true), 
		'associate_attached' => $action_attch, 
		'add_time' => ($add_time == 0) ? time() : $add_time
	);
	
	//增加用户计数器
	self::update_user_nums($action_id, uid);
	
	GZ_APP::db()->insert(get_table('user_action_history'), $data);
	
	return GZ_APP::db()->lastInsertId();
}

	/**
	 * 
	 * 更新用户计数器
	 */
	public static function update_user_nums($action_id, $uid)
	{
		$account_class = new account_class();
		
		switch ($action_id)
		{
			case self::ADD_COMMENT :
				$account_class->increase_user_statistics(account_class::EDIT_COUNT);
				break;
			case self::ADD_QUESTION :
				$account_class->increase_user_statistics(account_class::QUESTION_COUNT);
				$account_class->increase_user_statistics(account_class::EDIT_COUNT);
				break;
			case self::ADD_TOPIC :
				$account_class->increase_user_statistics(account_class::TOPIC_COUNT);
				$account_class->increase_user_statistics(account_class::EDIT_COUNT);
				break;
			case self::ADD_TOPIC_FOCUS :
				$account_class->increase_user_statistics(account_class::EDIT_COUNT);
				break;
			case self::ADD_TOPIC_PARENT :
				$account_class->increase_user_statistics(account_class::TOPIC_COUNT);
				$account_class->increase_user_statistics(account_class::EDIT_COUNT);
				break;
			case self::ANSWER_QUESTION :
				$account_class->increase_user_statistics(account_class::ANSWER_COUNT);
				break;
			case self::DELETE_ANSWER :
				$account_class->increase_user_statistics(account_class::EDIT_COUNT);
				$account_class->increase_user_statistics(account_class::ANSWER_COUNT, - 1);
				break;
			case self::DELETE_COMMENT :
				$account_class->increase_user_statistics(account_class::EDIT_COUNT);
				break;
			case self::DELETE_REQUESTION :
				$account_class->increase_user_statistics(account_class::QUESTION_COUNT, - 1);
				$account_class->increase_user_statistics(account_class::EDIT_COUNT);
				break;
			case self::DELETE_REQUESTION_FOCUS :
				$account_class->increase_user_statistics(account_class::EDIT_COUNT);
				break;
			case self::DELETE_TOPIC :
				$account_class->increase_user_statistics(account_class::TOPIC_COUNT, - 1);
				$account_class->increase_user_statistics(account_class::EDIT_COUNT);
				break;
			case self::DELETE_TOPIC_FOCUS :
				$account_class->increase_user_statistics(account_class::EDIT_COUNT);
				break;
			case self::DELETE_TOPIC_PARENT :
				$account_class->increase_user_statistics(account_class::EDIT_COUNT);
				$account_class->increase_user_statistics(account_class::TOPIC_COUNT, - 1);
				break;
			case self::MOD_ANSWER :
			case self::MOD_QUESTION_DESCRI :
			case self::MOD_QUESTON_TITLE :
			case self::MOD_TOPIC :
			case self::MOD_TOPIC_DESCRI :
			case self::MOD_TOPIC_PIC :
		}
	}

	/**
	 * 
	 * 根据事件ID,得到事件列表
	 * @param boolean $count
	 * @param int     $event_id
	 * @param int     $limit
	 * @param int     $action_type
	 * @param int     $action_id
	 * 
	 * @return array
	 */
	public static function get_action_by_event_id($count = false, $event_id = 0, $limit = 20, $action_type = "", $action_id = "")
	{
		if ($count)
		{
			$sql = "SELECT COUNT(*) AS n FROM " . get_table('user_action_history');
		}
		else
		{
			$sql = "SELECT * FROM " . get_table('user_action_history');
		}
		
		//$sql .= " WHERE 1=1";
		
		if ($event_id > 0)
		{
			$where[] = "  associate_id = " . intval($event_id) . "  ";
		}
		
		if ($action_type != "")
		{
			$where[] = "  associate_type in ( " . $action_type . " ) ";
		}
		
		if ($action_id != "")
		{
			$where[] = "  associate_action in ( " . $action_id . " ) ";
		}
		else
		{
			$where[] = "  associate_action not in ( 105,106,203,204,205,206,207,208,209) ";
		}
		
		if(is_array($where))
		{
			$sql.=" WHERE ".implode(' AND ',$where);
		}
		
		$sql .= " ORDER BY add_time DESC ";
		
		$sql .= " LIMIT " . $limit;
		
		if ($count)
		{
			$row = GZ_APP::db()->fetchRow($sql);
			
			return $row['n'];
		}
		else
		{
			return GZ_APP::db()->fetchAll($sql);
		}
	}

	/**
	 * 
	 * 根据条件,得到事件列表


	 * @param int     $limit

	 * 
	 * @return array
	 */
	public static function get_action_by_where($where = "", $limit = 20)
	{
		$where = trim($where);
		
		if ($where == "")
		{
			return false;
		}
		
		$sql = "SELECT * FROM " . get_table('user_action_history') . " WHERE " . $where . " ORDER BY add_time DESC LIMIT " . $limit;
		
		return GZ_APP::db()->fetchAll($sql);
	}

	/**
	 * 根据条件,得到不重复的动作列表(associate_type,associate_id)
	 * 
	 * @param  $where
	 * @param  $limit
	 */
	public static function get_actions_distint_by_where($where = "", $limit = 20)
	{
		$where = trim($where);
		
		if ($where == "")
		{
			return false;
		}
		
		$sql = "SELECT MAX(history_id) history_id FROM " . get_table('user_action_history') . " WHERE " . $where;
		
		$sql .= " GROUP BY associate_id, associate_type ORDER BY history_id DESC LIMIT " . $limit;
		
		$rs = GZ_APP::db()->fetchAll($sql);
		
		if (! $rs)
		{
			return array(); //返回空数组
		}
		
		foreach ($rs as $key => $val)
		{
			$history_id_array[] = $val["history_id"];
		}
		
		$sql = " SELECT associate_id,associate_type,history_id,uid,associate_action,associate_content,associate_attached,add_time  FROM " . get_table('user_action_history') . " WHERE  history_id in(" . implode(",", $history_id_array) . ") ORDER BY history_id DESC";
		
		return GZ_APP::db()->fetchAll($sql);
	}

	/**
	 * 
	 * 得到不重复的日志,以关联ID为唯一
	 * @param int $uid
	 * @param int $limit
	 * @param int $action_type
	 * @param int $action_id
	 * 
	 * @return array
	 */
	public static function get_action_distinct($uid = 0, $limit = "", $action_type = '', $action_id = '')
	{
		$sql = "SELECT DISTINCT associate_id, associate_type FROM " . get_table('user_action_history') . " WHERE uid = " . $uid . " ";
		
		if ($action_type != "")
		{
			$sql .= " AND associate_type in (" . $action_type . " ) ";
		}
		
		if ($action_id != "")
		{
			$sql .= " AND associate_action in ( " . $action_id . " ) ";
		}
		
		$sql .= " ORDER BY add_time DESC ";
		
		if (empty($limit))
		{
			$sql .= " LIMIT " . $limit;
		}
		
		return GZ_APP::db()->fetchAll($sql);
	}

	/**
	 * 
	 * 根据问题日志获取一个条日志相信信息
	 * @param int $associate_id
	 * @param int $action_type
	 * @param int $limit
	 * 
	 * @return array
	 */
	public static function get_action_detail_by_action_type($associate_id = '', $action_type = '', $limit = '1', $action_id = '', $uid = 0)
	{
		$uid = intval($uid);
		
		$sql = "SELECT * FROM " . get_table('user_action_history') . " WHERE associate_id = " . $associate_id . " ";
		
		if ($action_type != "")
		{
			$sql .= " AND associate_type in (" . $action_type . " ) ";
		}
		
		if ($action_id != "")
		{
			$sql .= " AND associate_action in ( " . $action_id . " ) ";
		}
		
		if ($uid != 0)
		{
			$sql .= " AND  uid='{$uid}'";
		}
		
		$sql .= ' ORDER BY add_time desc ,history_id DESC LIMIT ' . $limit;
		
		return GZ_APP::db()->fetchAll($sql);
	}

	/**
	 * 
	 * 根据用户ID，得到限定的[动作类型]动作数据
	 * @param boolean $content 是否返回内容总数
	 * @param int $uid
	 * @param string $limit
	 * @param int $action_type 动作类型
	 * @param int $action_id  动作详细类型
	 * 
	 * @return array
	 */
	/* 无调用，暂时注销
	public static function get_action_by_uid($count = false, $uid = 0, $limit = 20, $action_type = "", $action_id = "")
	{
		if (intval($uid) == 0)
		{
			return false;
		}
		
		if ($count)
		{
			$sql = "SELECT COUNT(*) as total FROM " . get_table('user_action_history') . " WHERE uid = " . $uid . " ";
			
			if ($action_type != "")
			{
				$sql .= " AND associate_type in ( " . $action_type . " ) ";
			}
			
			if ($action_id != "")
			{
				$sql .= " AND associate_action in (" . $action_id . " ) ";
			}
			
			$retval = GZ_APP::db()->fetchRow($sql);
			
			return isset($retval[0]['total']) ? $retval[0]['total'] : 0;
		}
		else
		{
			$sql = "SELECT * FROM " . get_table('user_action_history') . " WHERE uid = " . $uid . " ";
			if ($action_type != "")
			{
				$sql .= " AND associate_type in (" . $action_type . " ) ";
			}
			if ($action_id != "")
			{
				$sql .= " AND associate_action in ( " . $action_id . " ) ";
			}
		}
		
		$sql .= " ORDER BY add_time DESC LIMIT " . $limit;
		
		return GZ_APP::db()->fetchAll($sql);
	}
	*/

	/**
	 * 
	 * 删除指定用户[动作类型]动作
	 * @param int $uid
	 * @param int $action_type
	 * @param int $action_id
	 * 
	 * @return boolean true|false
	 */
	/* 无调用，暂时注销
	public static function delete_action_by_uid($uid, $action_type = "", $action_id = "")
	{
		if (intval($uid) == 0)
		{
			return false;
		}
		
		$sql = "DELETE FROM " . get_table('user_action_history') . " WHERE uid = " . $uid . " ";
		
		return GZ_APP::db()->query($sql);
	}
	*/

	/**
	 * 
	 * 删除指定时间断内用户[动作类型]动作
	 * @param int $start_time
	 * @param int $end_time
	 * @param int $action_type
	 * @param int $action_id
	 * 
	 * @return array
	 */
	/* 无调用，暂时注销
	public static function delete_action_by_time($start_time, $end_time, $action_type = "", $action_id = "")
	{
		$start_time = intval($start_time);
		$end_time = intval($end_time);
		$sql = "DELETE FROM " . get_table('user_action_history') . " WHERE add_time BETWEEN " . $start_time . " AND " . $end_time . " ";
		
		return GZ_APP::db()->query($sql);
	}
	*/

	/**
	 * 
	 * 格式化问题日志
	 * @param int $log_info_list
	 * 
	 * @return array
	 */
	/*
	public static function format_question_log($log_info_list)
	{
		$uid_list = array(
			0
		);
		$user_info_list = array();
		$data_list = array();
		
		if (! is_array($log_info_list))
		{
			return array();
		}
		foreach ($log_info_list as $log_info)
		{
			if (! in_array($log_info['uid'], $uid_list))
			{
				$uid_list[] = $log_info['uid'];
			}
		}
		
		$user_obj = $this->model('account');
		$users_info = $user_obj->get_users_by_uids($uid_list);
		foreach ($users_info as $user_info)
		{
			$user_url = $user_obj->get_url_by_uid($user_info['uid']);
			$user_info_list[$user_info['uid']] = array(
				$user_info['user_name'], 
				$user_url
			);
		}
		
		foreach ($log_info_list as $log_info)
		{
			$str_title = "";
			$add_time = date('m-d H:i:s', $log_info['add_time']);
			$log_id = sprintf('%06s', $log_info['history_id']);
			
			switch ($log_info['associate_action'])
			{
				case ACTION_LOG::ADD_TOPIC :
					if ($log_info[associate_type] == 1)
					{
						$question_class = $this->model('question');
						$question_info = $question_class->get_question_info_by_id($log_info['associate_id']);
						$str_content = '<a target="_blank" href="/question/?act=detail&question_id=' . $question_info[question_id] . '">' . $question_info[question_content] . '</a>';
						$str_title = '<a href="' . $user_info_list[$log_info['uid']][1] . '">' . $user_info_list[$log_info['uid']][0] . '</a> 给该问题添加了一个话题<br />';
						$str_title .= '<a href="/topic/?topic_id=' . $log_info['associate_attached'] . '">' . $log_info['associate_content'] . '</a>';
					}
					
					//else if($log_info[associate_type] == 4){
					//	$str_content = '<a target="_blank" href="/topic/?topic_id='.$log_info['associate_id'].'">' . $log_info['associate_content'] . '</a>';
					//	$str_title = '<a href="'.$user_info_list[$log_info['uid']][1].'">'.$user_info_list[$log_info['uid']][0].'</a> 创建了该话题<br />';
					//	$str_title .= '<a href="/topic/?topic_id='.$log_info['associate_id'].'">'.$log_info['associate_content'].'</a> 被创建';
					//}
					
					break;
				case ACTION_LOG::ADD_QUESTION :
					#					$str_title .= '<div class="item"><div class="item_log" id="logitem-'.sprintf('%06s', $log_info['history_id']).'"><!-- 显示log的标题 --><div class="item_title">';
					#					$str_title .= '<h2><a href="/question/?act=detail&question_id='.$log_info['associate_id'].'">'.$log_info['associate_content'].'</a></h2></div> ';
					#					$str_title .= '<div class="log_detail">';
					$str_title .= '<p><!-- log主体 --><a href="' . $user_info_list[$log_info['uid']][1] . '">' . $user_info_list[$log_info['uid']][0] . ' <a/>添加了该问题</p>';
					$str_title .= '<p><ins>' . $log_info['associate_attached'] . '</ins></p>';
					#					$str_title .= '</div> <!--.log_detail--><div class="item_meta"><p>#'.sprintf('%06s', $log_info['history_id']).' •发布';
					#					$str_title .= '<time datetime="'.$log_info['add_time'].'">'.date('Y-m-d H:i', $log_info['add_time']).'</time></p></div></div><!--.item--></div> <!--.item--> ';
					break;
				case ACTION_LOG::ANSWER_QUESTION :
					if ($log_info[associate_type] == 2)
						continue;
					$question_class = $this->model('question');
					$question_info = $question_class->get_question_info_by_id($log_info['associate_id']);
					$str_content = '<a target="_blank" href="/question/?act=detail&question_id=' . $question_info[question_id] . '">' . $question_info[question_content] . '</a>';
					$str_title = '<a href="' . $user_info_list[$log_info['uid']][1] . '">' . $user_info_list[$log_info['uid']][0] . '</a> 回答了该问题<br />';
					$str_title .= $log_info['associate_content'];
					break;
				case ACTION_LOG::DELETE_ANSWER :
					$str_title = '<a href="' . $user_info_list[$log_info['uid']][1] . '">' . $user_info_list[$log_info['uid']][0] . '</a> 删除了回复<br />';
					$str_title .= '<del>' . $log_info['associate_content'] . '</del>';
					break;
				case ACTION_LOG::DELETE_REQUESTION :
					$str_title = '<a href="' . $user_info_list[$log_info['uid']][1] . '">' . $user_info_list[$log_info['uid']][0] . '</a> 删除了问题<br />';
					$str_title .= '<del>' . $log_info['associate_content'] . '</del>';
					break;
				case ACTION_LOG::DELETE_TOPIC :
					$str_title = '<a href="' . $user_info_list[$log_info['uid']][1] . '">' . $user_info_list[$log_info['uid']][0] . '</a> 删除了话题<br />';
					$str_title .= '<a href="' . get_setting('base_url') . '/topic/?topic_id=' . $log_info['associate_attached'] . '"><del>' . $log_info['associate_content'] . '</del></a>';
					break;
				case ACTION_LOG::MOD_ANSWER :
					$question_class = $this->model('question');
					$question_info = $question_class->get_question_info_by_id($log_info['associate_id']);
					$str_content = '<a target="_blank" href="/question/?act=detail&question_id=' . $question_info[question_id] . '">' . $question_info[question_content] . '</a>';
					$str_title = '<a href="' . $user_info_list[$log_info['uid']][1] . '">' . $user_info_list[$log_info['uid']][0] . '</a> 修改了回复<br />';
					$Diff = new FineDiff($log_info['associate_attached'], $log_info['associate_content']);
					$rendered_diff = $Diff->renderDiffToHTML();
					$str_title .= $rendered_diff;
					break;
				case ACTION_LOG::MOD_QUESTION_DESCRI :
					$question_class = $this->model('question');
					$question_info = $question_class->get_question_info_by_id($log_info['associate_id']);
					$str_content = '<a target="_blank" href="/question/?act=detail&question_id=' . $question_info[question_id] . '">' . $question_info[question_content] . '</a>';
					$str_title = '<a href="' . $user_info_list[$log_info['uid']][1] . '">' . $user_info_list[$log_info['uid']][0] . '</a> 修改了问题描述<br />';
					
					$Diff = new FineDiff($log_info['associate_attached'], $log_info['associate_content']);
					
					$rendered_diff = $Diff->renderDiffToHTML();
					$str_title .= $rendered_diff;
					break;
				case ACTION_LOG::MOD_QUESTON_TITLE :
					$question_class = $this->model('question');
					$question_info = $question_class->get_question_info_by_id($log_info['associate_id']);
					$str_content = '<a target="_blank" href="/question/?act=detail&question_id=' . $question_info[question_id] . '">' . $question_info[question_content] . '</a>';
					$Diff = new FineDiff($log_info['associate_attached'], $log_info['associate_content']);
					$rendered_diff = $Diff->renderDiffToHTML();
					$str_title = '<a href="' . $user_info_list[$log_info['uid']][1] . '">' . $user_info_list[$log_info['uid']][0] . '</a> 修改了问题标题<br />';
					$str_title .= $rendered_diff;
					break;
				case ACTION_LOG::MOD_TOPIC_DESCRI :
					$topic_class = $this->model('topic');
					$topic = $topic_class->get_topic($log_info['associate_id']);
					$str_content = '<a target="_blank" href="/topic/?topic_id=' . $topic[topic_id] . '">' . $topic[topic_title] . '</a>';
					$str_title = '<a href="' . $user_info_list[$log_info['uid']][1] . '">' . $user_info_list[$log_info['uid']][0] . '</a> 修改了该话题的描述<br />';
					$Diff = new FineDiff($log_info['associate_attached'], $log_info['associate_content']);
					$rendered_diff = $Diff->renderDiffToHTML();
					$str_title .= $rendered_diff;
					break;
			}
			
			if ($str_title != "")
			{
				$data_list[] = array(
					'title' => $str_title, 
					'content' => $str_content, 
					'add_time' => $add_time, 
					'log_id' => $log_id
				);
			}
			else
			{
				//p($log_info);
			}
		}
		
		return $data_list;
	}
	*/

	/**
	 * 格式化动作字符通用
	 *
	 * @param  $action
	 * @param  $uid
	 * @param  $question_info
	 * @param  $topic_info
	 * @param  $index_focus_type 首页提取类型
	 * @param  $answer_count 回答计数
	 * @param  $associate_type 动作大类型
	 */
	public static function format_action_str($action, $uid = 0, $user_name = "", $question_info = array(), $topic_info = array(), $index_focus_type = 0, $answer_count = 0, $associate_type = 0)
	{
		$action = $action * 1;
		$action_str = "";
		$index_focus_type = $index_focus_type * 1;
		$user_tip = "onMouseOver='eventsMouseM(this);'  rel='{$uid}'";
		$topic_tip = "onmouseover='eventstalk(this);' rel='{$topic_info['topic_id']}'";
		
		switch ($action)
		{
			case ACTION_LOG::ADD_QUESTION : //'添加问题',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 发起了该问题";
				break;
			
			case ACTION_LOG::MOD_QUESTON_TITLE : //'修改问题标题',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 修改了问题标题";
				break;
			
			case ACTION_LOG::MOD_QUESTION_DESCRI : // '修改问题描述',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 修改了问题";
				
				break;
			case ACTION_LOG::DELETE_REQUESTION : // '删除问题',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 删除问题";
				
				break;
			case ACTION_LOG::ADD_REQUESTION_FOCUS : // '添加问题关注',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 关注了该问题";
				break;
			
			case ACTION_LOG::DELETE_REQUESTION_FOCUS : // '删除问题关注',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 取消关注了该问题";
				break;
			
			case ACTION_LOG::ANSWER_QUESTION : // '回答问题',
				

				if (in_array($index_focus_type, array(
					1, 
					3
				)))
				{
					$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 回复了该问题";
				}
				elseif ($index_focus_type == 2)
				{
					$action_str = " <a href='/topic/?topic_title={$topic_info['topic_title']}' {$topic_tip}>{$topic_info[topic_title]}</a> 话题添加了一个问题回复";
				}
				else
				{
					$action_str = "该问题增加了一个回答";
				}
				
				break;
			
			case ACTION_LOG::MOD_ANSWER : // '修改回答',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 修改了回复";
				break;
			
			case ACTION_LOG::DELETE_ANSWER : // '删除回答',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 删除了回复";
				break;
			
			case ACTION_LOG::ADD_AGREE : //'增加赞同',
				

				if (in_array($index_focus_type, array(
					3
				)))
				{
					$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 赞同了一个回复";
				}
			
			case ACTION_LOG::ADD_AGANIST : // '增加反对 ',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 对该问题投票";
				break;
			
			case ACTION_LOG::DEL_AGREE : // '增加赞成 ',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 对该问题投票";
				break;
			
			case ACTION_LOG::DEL_AGANIST : // '取消反对 ',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 对该问题投票";
				break;
			
			case ACTION_LOG::ADD_COMMENT : // '增加评论',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 添加了评论";
				break;
			
			case ACTION_LOG::DELETE_COMMENT : //'删除评论',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 删除了评论";
				break;
			
			case ACTION_LOG::ADD_TOPIC : //'添加话题',
				

				// 						if($answer_count==0)
				// 						{
				

				// 							$action_str= "该问题被添加到 <a href='/topic/{$topic_info[topic_title]}'>$topic_info[topic_title]</a> 话题";
				// 						}
				

				if (in_array($index_focus_type, array(
					2
				)))
				{
					
					
					$action_str = "该问题被添加到 <a href='/topic/?topic_title={$topic_info[topic_title]}' $topic_tip>$topic_info[topic_title]</a>  话题";
				
				}
				else if ($topic_info)
				{
					$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 将该问题添加到 <a href='/topic/?topic_title={$topic_info[topic_title]}' $topic_tip>$topic_info[topic_title]</a>话题";
				}
				else
				{
					$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a> 添加了一个话题";
				}
				
				break;
			
			case ACTION_LOG::MOD_TOPIC : // '修改话题',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a>  修改话题";
				break;
			
			case ACTION_LOG::MOD_TOPIC_DESCRI : // '修改话题描述',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a>  修改话题描述";
				break;
			
			case ACTION_LOG::MOD_TOPIC_PIC : // '修改话题缩略图',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a>  修改话题缩略图";
				break;
			
			case ACTION_LOG::DELETE_TOPIC : // '删除话题',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a>  删除话题";
				break;
			
			case ACTION_LOG::ADD_TOPIC_FOCUS : // '关注话题',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a>  关注话题";
				break;
			
			case ACTION_LOG::DELETE_TOPIC_FOCUS : // '取消话题关注',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a>  取消话题关注";
				break;
			
			case ACTION_LOG::ADD_TOPIC_PARENT : //'增加话题分类',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a>  增加话题分类";
				break;
			
			case ACTION_LOG::DELETE_TOPIC_PARENT : //'删除话题分类',
				

				$action_str = "<a href='/people/?u={$user_name}' {$user_tip}>{$user_name}</a>  删除话题分类";
				break;
		}
		
		return $action_str;
	}
}