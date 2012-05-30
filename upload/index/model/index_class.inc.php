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

class index_class extends GZ_MODEL
{
	const CATEGORY_INDEX_PEOPLE = 1; //我关注的人（他关注的人，他关注的话题）
	const CATEGORY_INDEX_TOPIC = 2; //我关注的话题（谁关注了相同的话题）
	const CATEGORY_INDEX_QUESTION = 3; //我回答过的问题（包含我没关注的话题）

	
	/**
	 *
	 *得到热门问题
	 */
	public function get_hot_question($limit = 10, $sort = 'hot', $topic_id = 0, $uid = null, $category_id = null,$answer_count=null)
	{
		$topic_id = intval($topic_id);
		
		$uid = intval($uid);
		
		$user_id_list = array(
			0
		);
		
		$answer_info_list = array();
		$answer_list = array();
		$user_info_list = array();
		$user_list = array();
		$question_info_list = array();
		$question_list = array();
		
		switch ($sort)
		{
			default :
				$order_key = 'add_time';
				break;
			
			case 'new' :
				$order_key = 'update_time';
				break;
			
			case 'hot' :
				$order_key = 'update_time';
				break;
		}
		
		
		
		if ($topic_id)
		{
			
			$question_info_list = $this->model('question')->get_question_info($topic_id, $limit, 0, false, $order_key, $category_id,$answer_count);
			
			$question_ids = array();
			
			foreach ($question_info_list as $key => $data)
			{
				$question_ids[] = $data['question_id'];
			}
			
			
		}
		else
		{
			$question_ids = $this->model('question')->get_new_questions_id($limit, $order_key . ' DESC', $category_id);
			
			$question_info_list = $this->model('question')->get_hot_question($question_ids, $order_key . ' DESC');
		}
		
		if($sort=="hot")		
		{
			$question_info_list = $this->model('question')->get_hot_question_v2(0,$category_id,$limit);
			
			$question_ids = array();
			
			foreach ($question_info_list as $key => $data)
			{
				$question_ids[] = $data['question_id'];
			}		
		}
		
		//格式化回复
		if(!is_array($question_ids))
		{
			return array();
		}
		
		foreach ($question_ids as $question_id)
		{
			$retval = $this->model('answer')->get_answer_list_by_question_id($question_id, 1);
			$answer_info_list[$question_id] = $retval[0];
		}
		
		foreach ($answer_info_list as $answer_info)
		{
			if (! in_array($answer_info['uid'], $user_id_list))
			{
				$user_id_list[] = $answer_info['uid'];
			}
		}
		
		if ($question_info_list)
		{
			//格式化用户
			foreach($question_info_list as $keyx=>$question_info)
			{
				if (! in_array($question_info['published_uid'], $user_id_list))
				{
					$user_id_list[] = $question_info['published_uid'];
				}
			}
		}
		
		$categorys = $this->model('system')->get_category_list('question');
		
		if ($user_info_list = $this->model('account')->get_users_by_uids($user_id_list, true, true))
		{			
			foreach ($user_info_list as $user_info)
			{				
				$tmp = array();
				$tmp['uid'] = $user_info['uid'];
				$tmp['url'] = $user_info['url'];
				$tmp['u_url'] = $tmp['url'];
				$tmp['user_name'] = $user_info['user_name'];
				$tmp['signature'] = $user_info['signature'];
				$tmp['avatar_file'] = $user_info['avatar_file'];
				
				$user_list[$user_info['uid']] = $tmp;
			}
		}
		
		//格式话回答格式
		foreach ($answer_info_list as $answer_info)
		{
			if (! $answer_info)
			{
				continue;
			}
			
			$tmp = array();
			$tmp['uid'] = $answer_info['uid'];
			$tmp['answer_id'] = $answer_info['answer_id'];
			$tmp['question_id'] = $answer_info['question_id'];
			$tmp['user_name'] = $user_list[$answer_info['uid']]['user_name'];
			$tmp['u_url'] = $user_list[$answer_info['uid']]['url'];
			$tmp["agree_count"] = $answer_info["agree_count"] * 1;
			$tmp['modify_time'] = $answer_info['modify_time'] > 0 ? ($answer_info['modify_time'] * 1) : ($answer_info['add_time'] * 1);
			$tmp['attachs'] = $this->model("answer")->get_answer_attach($answer_info['answer_id']);
			
			if (isset($answer_info['answer_content']))
			{
				$answer_list_content = FORMAT::format_content($answer_info['answer_content']);
				$tmp['answer_content'] = $answer_list_content['content_content'];
				$tmp['answer_title'] = $answer_list_content['content_title'];
			}
			
			$answer_list[$answer_info['question_id']] = $tmp;
		}
		
		if ($question_info_list)
		{
			//格式话问题格式
			foreach ($question_info_list as $question_info)
			{
				$tmp = array();
				$top_tmp = array();
				
				$tmp['title'] = $question_info['question_content'];
				
				$tmp['question_detail'] = $question_info['question_detail'];
				
				$tmp['category_id'] = $question_info['category_id'];
				
				// 获取附件
				$tmp['attachs'] = $this->model('question')->get_question_attach($question_info['question_id']);
				
				$tmp['category_info'] = $this->model('system')->get_category_info($question_info['category_id']); 
			
				if ($uid)
				{
					$tmp['focus'] = $this->model('question')->has_focus_question($question_info['question_id'], $uid);
				}
			
				$tmp['topics'] = $this->model('question_topic')->get_question_topic_by_question_id($question_info['question_id']);
				
				$tmp['vote_count'] = $answer_info['agree_count'];
				
				$tmp['name'] = $user_list[$question_info['published_uid']]['name'];
				
				$tmp['url'] = $user_list[$question_info['published_uid']]['url'];
				
				$tmp['answer'] = $answer_list[$question_info['question_id']];
				
				$tmp['question_id'] = $question_info['question_id'];
				
				$tmp['answer_count'] = $question_info['answer_count'];
				$tmp['focus_count'] = $question_info['focus_count'];
				$tmp['view_count'] = $question_info['view_count'];
				
				$tmp['user_info'] = $user_list[$question_info['published_uid']];
				
				$tmp['add_time'] = $question_info['add_time'];
				
				$tmp['update_time'] = $question_info['update_time'];
				
				$tmp['category'] = $categorys[$question_info['category_id']];
				
				$question_list[] = $tmp;
			}
		}
		
		//p($question_list);die;

		return $question_list;
	}

	/**
	 * 获取首页我关注的模块内容
	 *
	 * @param  $uid
	 * @param  $limit
	 */
	function get_index_focus($uid, $limit = 10, $get_type = 'all')
	{
		//初始化动作数组;
		$question_action_array = array(); //问题ID动作集合,符合条件,唯一
		$user_info_list = array(); //用户ID集合,符合条件
		$user_info_more_list = array(); //用户详细信息集合,符合条件
		$questions_array = array(); //问题ID集合,符合条件
		$questions_my_last = array(); //最后动作是我的问题的集合	
		$questions_row_array = array(); //问题ID内容集合,符合条件	
		$questions_array_my_ = array(); //我关注的话题的问题集合
		$questions_array_focus = array(); //我关注的人问题集合
		$questions_uninteresteds[] = 0;

		// 当前所有问题是集合>>>>最后动作是当前用户
		

		$tmp_array = $this->model('question')->get_question_list_for_last_uid(false, $uid, "");
		
		foreach ($tmp_array as $key => $val)
		{
			$questions_my_last[] = $val["question_id"];
		}
			
			//取出当前用户关注的问题ID集合
		//$my_focus_question_all = $this->model('question')->get_focus_question_by_uid(array(), $uid);
		$my_focus_question_all = array();
		
		
		$questions_uninteresteds = $this->model('question')->get_question_uninterested($uid);
		
		//取我关注的人
		//******************************************************************************
		$my_follow_uids = $this->model('follow')->get_user_friends($uid, 200);
		
		if (is_array($my_follow_uids))
		{
			foreach ($my_follow_uids as $key => $val)
			{
				$my_follow_uids_array[] = $val["uid"];
			}
		}
		
		if ($get_type == 'questions' or $get_type == 'all')
		{
			
			//取我关注的话题>>>取出里面的问题
			//******************************************************************************
			$my_focus_topics = $this->model('topic')->get_focus_topic_list(false, $uid, 9999);
			$my_focus_topics_questions = array();
			
			if ($my_focus_topics)
			{
				foreach ($my_focus_topics as $key => $val)
				{
					$my_focus_topics_id_array[] = $val["topic_id"];
				}
				
				//得到关注的话题的问题列表(前200)
				$my_focus_topics_questions = $this->model('question_topic')->get_question_list_by_topic_id($my_focus_topics_id_array, 200);
				
				//生成动作
				foreach ($my_focus_topics_questions as $key => $val)
				{
					//去掉不感兴趣
					if (in_array($val["question_id"], $questions_uninteresteds))
					{
						continue;
					}
					
					//去掉关注过的了
					if (in_array($val["question_id"], $my_focus_question_all))
					{
						continue;
					}
					
					//跳过最后动作是我的
					//if(in_array($val["question_id"], $questions_my_last))continue;
					

					if (! isset($question_action_array[$val["question_id"]]))
					{
						$val["index_focus_type"] = 2;
						$question_action_array[$val["question_id"]] = $val["index_focus_type"]; //问题属于哪一部人的操作得到的 $val["index_focus_type"]
						$questions_row_array[$val["question_id"]] = $val; //所有的问题详细集合
						$questions_array_my[] = $val["question_id"]; //存我关注的话题的问题集合
					}
					
					if (! isset($user_info_list[$val["last_uid"]]))
					{
						$user_info_list[$val["last_uid"]] = $val["last_uid"]; //存uid
					}
				}
			
			}
			
			//取我关注的人>>>发布的
			$my_friends_questions_01 = $this->model('question')->get_question_list_by_uids(false, $my_follow_uids_array, 200);
			
			//取我关注的人>>>关注的问题
			$my_friends_questions_02 = $this->model('question')->get_focus_question_list_by_uids(false, $my_follow_uids_array, 200);
			
			//取我关注的人>>>回答的问题
			$my_friends_questions_03 = $this->model('answer')->get_answer_question_list_by_uids(false, $my_follow_uids_array, 200);
			
			//取我关注的人>>>将问题添加到了话题(动作-新建话题)
			$my_friends_questions_05 = $this->model('question')->get_question_list_for_actions(false, $my_follow_uids_array, '401', 200);
			
			//取我关注的人>>>赞同回复的问题
			$my_friends_questions_06 = $this->model('answer')->get_agree_question_list_by_users(false, $my_follow_uids_array, 200);
			
			//顺序要颠倒 31245
			

			if (! $my_friends_questions_01)
			{
				$my_friends_questions_01 = array();
			}
			
			if (! $my_friends_questions_02)
			{
				$my_friends_questions_02 = array();
			}
			
			if (! $my_friends_questions_03)
			{
				$my_friends_questions_03 = array();
			}
			
			if (! $my_friends_questions_05)
			{
				$my_friends_questions_05 = array();
			}
			
			if (! $my_friends_questions_06)
			{
				$my_friends_questions_06 = array();
			}
			
			//合并所有我朋友的问题
			$my_friends_questions_all = array_merge($my_friends_questions_03, $my_friends_questions_01, $my_friends_questions_02, $my_friends_questions_05, $my_friends_questions_06);
			
			// 生成动作
			foreach ($my_friends_questions_all as $key => $val)
			{
				// 去掉不感兴趣
				if (in_array($val["question_id"], $questions_uninteresteds))
				{
					continue;
				}
				
				//去掉关注过的了
				if (in_array($val["question_id"], $my_focus_question_all))
				{
					continue;
				}
				
				// 跳过最后动作是我的
				//if(in_array($val["question_id"],$questions_my_last))continue;
				

				if (! isset($question_action_array[$val["question_id"]]))
				{
					$val["index_focus_type"] = 3;
					$question_action_array[$val["question_id"]] = $val["index_focus_type"];
					$questions_row_array[$val["question_id"]] = $val;
					$questions_array_focus[] = $val["question_id"];
				}
				if (! isset($user_info_list[$val["last_uid"]]))
				{
					$user_info_list[$val["last_uid"]] = $val["last_uid"];
				}
			}
			
			//通过动作唯一取得所有问题id,$key就是问题ID
			foreach ($question_action_array as $key => $val)
			{
				$questions_array[] = $key; //所有问题集合
			}
			
			//批量获取问题的关注
			if (! empty($questions_array))
			{
				$focus_question = $this->model('question')->get_focus_question_by_uid($questions_array, $uid);
			}
		
		}
		
		//批量获取用户的uid用户信息
		if (! empty($user_info_list))
		{
			
			$user_list = $this->model('account')->get_users_by_uids($user_info_list, true);
			foreach ($user_list as $user)
			{
				$user_info_list[$user['uid']] = $user['user_name']; //单名称
				$user_info_more_list[$user['uid']] = $user; //多项信息
			}
		
		}
		

		
		//通过动作表进行排序和查找
		//限定作动
		//101 添加问题
		//102 修改问题标题
		//103 修改问题描述
		//104 删除问题
		//105 添加问题关注
		//106 删除问题关注
		//
		//201 回答问题
		//202 修改回答
		//203 删除回答
		//204 赞成回答
		//205 反对回答
		//
		//301 增加评论
		//302 删除评论
		//
		//401 创建话题
		//402 修改话题
		//403 修改话题描述
		//404 修改话题缩图
		//405 删除话题
		//
		//406 添加话题关注
		//407 删除话题关注
		//408 增加话题父类
		//409 删除话题父类
		//
		//501 添加比赛
		//502 比赛报名
		//503 提交作品
		//504 修改作品
		//505 添加比赛关注
		//506 删除比赛关注
		

		//
		

		$my_follow_not_my_focus_uids_array = array(0);
		$action_ids0 = "201,204,401";
		$action_ids1 = "101,105,201,204,401,406";
		$action_ids2 = "204,401,503";
		$action_ids3 = "204,401,501,503,505";
		$action_ids4 = "105"; //些类型,我关注的,别人的关注失效
		$questions_array_my[] = 0;
		$questions_array_focus[] = 0;
		$my_follow_uids_array[] = 0;
		$my_follow_uids_array[] = 0;
		$my_focus_question_all[] = 0;

		
		$where = "(associate_type='" . ACTION_LOG::CATEGORY_QUESTION . "'
				AND associate_id IN (" . implode(',', $questions_array_my) . ")
				AND uid NOT  IN(" . $uid . ")
				AND associate_action IN({$action_ids0}))
				OR
				(associate_type='" . ACTION_LOG::CATEGORY_QUESTION . "'
				AND associate_id IN (" . implode(',', $questions_array_focus) . ")
				AND uid in (" . implode(',', $my_follow_uids_array) . ")
				AND associate_action IN({$action_ids1}))";

		
		//echo $where;die;
		//限定动作
		$action_list = ACTION_LOG::get_actions_distint_by_where($where, $limit);
		
		//重组信息
	
		foreach ($action_list as $key => $val)
		{
			$action_list[$key]["add_time"] = $val["add_time"];
			
			if (! isset($user_info_list[$val['uid']]))
			{
				$user_info_more_list[$val['uid']] = $this->model('account')->get_users_by_uid($val['uid'], true);
				$user_info_list[$val['uid']] = $user_info_more_list[$val['uid']]["user_name"];
			}
			
			$action_list[$key]["user_info"] = $user_info_more_list[$val['uid']];
			
			switch ($val["associate_type"])
			{
				
				case ACTION_LOG::CATEGORY_QUESTION :
					
					$question_info = $questions_row_array[$val["associate_id"]];
					
					//是否关注
					if (in_array($question_info['question_id'], $focus_question))
					{
						$question_info['has_focus'] = true;
					}
					else
					{
						$question_info['has_focus'] = false;
					}
					
					$question_info['last_user_name'] = $user_info_list[$val["uid"]];
					
					$question_info['last_user_avatar_file'] = $user_info_more_list[$val["uid"]]["avatar_file"];
					
					$question_info['last_user_uid'] = $val["uid"];					
					$question_info['attachs'] = $this->model('question')->get_question_attach($question_info['question_id']); //获取附件
					
					//$question_info['category_info'] = $this->model('system')->get_category_info($question_info['category_id']);

					$topic_info = null;
					
					if (in_array($val["associate_action"], array(401,402,403,404,405,406,407,408,409)))
					{
						$topic_info = $this->model('topic')->get_topic($val["associate_attached"]);					
					}
					
					$index_focus_type = $question_info["index_focus_type"]; //不同的类型调用不同的东东

					switch ($index_focus_type)
					{
						case 2 :
							
							$topic_info = $this->model('topic')->get_topic($question_info["topic_id"]);							
							$question_info['last_action_str'] = ACTION_LOG::format_action_str($val["associate_action"], $val['uid'], $user_info_list[$val['uid']], null, $topic_info, $index_focus_type, $question_info[answer_count]);
							
							//对于回答问题的
							if (in_array($val["associate_action"], array(201)))
							{
								//取里面的回答的ID出来
								$answer_list[0] = $this->model('answer')->get_answer_info_by_id($val["associate_attached"], 0, false);
								//补充数据

								$answer_list[0]["uid"] = $val["uid"];
								$answer_list[0]["uname"] = $user_info_more_list[$val["uid"]]["user_name"];
								$answer_list[0]["avatar_file"] = $user_info_more_list[$val["uid"]]["avatar_file"];
								$answer_list[0]["signature"] = $user_info_more_list[$val["uid"]]["signature"];							
							}
							else
							{
								$answer_list = $this->model('answer')->get_answer_list_by_question_id($question_info['question_id'], 1);
							}
							break;
						
						case 3 :
							
							$question_info['last_action_str'] = ACTION_LOG::format_action_str($val["associate_action"], $val['uid'], $user_info_list[$val['uid']], null, $topic_info, $index_focus_type, $question_info[answer_count]);
							
							//对于回答问题的
							if (in_array($val["associate_action"], array(201,204)))
							{
								//取里面的问题ID出来
								$answer_list[0] = $this->model('answer')->get_answer_info_by_id($val["associate_attached"], 0, false);
								//补充数据
								$answer_list[0]["uid"] = $val["uid"];
								$answer_list[0]["uname"] = $user_info_more_list[$val["uid"]]["user_name"];
								$answer_list[0]["avatar_file"] = $user_info_more_list[$val["uid"]]["avatar_file"];
								$answer_list[0]["signature"] = $user_info_more_list[$val["uid"]]["signature"];
							
							}
							else
							{
								$answer_list = $this->model('answer')->get_answer_list_by_question_id($question_info['question_id'], 1);
							}
							
							break;
						
						default :							
							$question_info['last_action_str'] = ACTION_LOG::format_action_str($val["associate_action"], $val['uid'], $user_info_list[$val['uid']], null, $topic_info, $index_focus_type, $question_info[answer_count]);							
							$answer_list = $this->model('answer')->get_answer_list_by_question_id($question_info['question_id'], 1, " AND an.uid not in($uid) ");
							
							break;
					}
					
					
					$user_arr = array();
					
					if (! empty($answer_list[0]['answer_id']))
					{						
						if (isset($answer_list[0]['answer_content']))
						{
							$answer_list_content = FORMAT::format_content($answer_list[0]['answer_content']);
							$answer_list[0]['answer_content'] = $answer_list_content['content_content'];
							$answer_list[0]['answer_title'] = $answer_list_content['content_title'];
						}
						if (isset($answer_list[0]['uid']))
						{
							$answer_list[0]['url'] = $this->model('account')->get_url_by_uid($answer_list[0]['uid']);
						}
						
						$question_info['answer_info'] = (isset($answer_list[0])) ? $answer_list[0] : '';
					}
					
					if ($question_info['answer_info']['agree_count'] > 0)
					{
						$question_info['answer_info']['agree_users'] = $this->model("answer")->get_vote_user_by_answer_id($question_info['answer_info']['answer_id']);
					}
					
					$answer_vote = $this->model("answer")->get_answer_vote_status($question_info['answer_info']['answer_id'], $uid);
					$question_info['answer_info']['agree_status'] = intval($answer_vote['vote_value']);

					
					//还原到单个数组ROW里面
					foreach ($question_info as $qkey => $qval)
					{
						if ($qkey == "add_time")
						{
							continue;
						}
						
						$action_list[$key][$qkey] = $qval;
					}
					
					break;
			}
		
		}
		
	
		return $action_list;
	}

	/**
	 * 获取用户关注的内容
	 * @param  $uid
	 * @param  $limit
	 */
	public function get_user_focus($uid, $limit = 10)
	{
		$associate = $this->fetch_all("user_associate_index", " uid = " . (int)$uid . " AND (associate_type = 1 OR associate_type = 2)", " update_time DESC ", $limit);		
		$question_ids = array(0);
		
		foreach ($associate as $key => $val)
		{
			if ($val['associate_type'] == 1)
			{
				$question_ids[] = $val['associate_id'];
			}
			else
			{
				$competitions_ids[] = $val['associate_id'];
			}
		}

		$_question = $this->fetch_all('question', "question_id IN(" . implode($question_ids, ',') . ")");
		
		foreach ($_question as $key => $val)
		{
			$question[$val['question_id']] = $val;
		}
		
		foreach ($associate as $key => $val)
		{
			if ($val['associate_type'] == 1)
			{
				$result[] = $question[$val['associate_id']];
			}
			
		}
		
		return $result;
	}

	/**
	 * 获取用户发布的内容
	 * @param  $uid
	 * @param  $limit
	 */
	public function get_user_publish($uid, $limit = 10)
	{
		$associate = $this->fetch_all("user_associate_index", " uid = " . (int)$uid . " AND (associate_type = 3 OR associate_type = 4)", "update_time DESC", $limit);		
		$question_ids = array(0);
		$competitions_ids = array(0);
		
		foreach ($associate as $key => $val)
		{
			if ($val['associate_type'] == 3)
			{
				$question_ids[] = $val['associate_id'];
			}
			else
			{
				$competitions_ids[] = $val['associate_id'];
			}
		}
		
		$_question = $this->fetch_all('question', "question_id IN(" . implode($question_ids, ',') . ")");
		
		foreach ($_question as $key => $val)
		{
			$question[$val['question_id']] = $val;
		}
		
		foreach ($associate as $key => $val)
		{
			if ($val['associate_type'] == 3)
			{
				$result[] = $question[$val['associate_id']];
			}		
		}
		
		return $result;
	}
}