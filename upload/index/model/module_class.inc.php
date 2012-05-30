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

class module_class extends GZ_MODEL
{

	/**
	 * 热门问题
	 */
	function hot_questions()
	{
		$hot_questions = $this->data_cache('get_question_get_hot_v2_' . $this->user_id . '_10', '$this->model("question")->get_question_list(false,"", 5,"answer_count desc"," add_time<' . (mktime() - 60 * 60 * 24 * 30) . ' ")', get_setting('cache_level_high'));
		
		//判断是不中关注
		foreach ($hot_questions as $key => $val)
		{
			if ($this->model('question')->has_focus_question($val["question_id"], $this->user_id))
			{
				$hot_questions[$key]['has_focus'] = true;
			}
			else
			{
				$hot_questions[$key]['has_focus'] = false;
			}
		}
		
		return $hot_questions;
	
	}

	/**
	 * 可能感兴趣的人和话题
	 * @return multitype:
	 */
	function recommend_users_topics($uid)
	{
		$recommend_users = $this->model("account")->get_user_recommend_v2($uid , 20);
		
		$recommend_topics = $this->model("topic")->get_user_recommend_v2( $uid  , 20);

		if (! is_array($recommend_topics))
		{
			return array_slice($recommend_users, 0, get_setting('recommend_users_number'));
		}
		
		
		shuffle($recommend_topics);
		$recommend_topics=array_slice($recommend_topics, 0, intval(get_setting('recommend_users_number')/2));
		
		shuffle($recommend_users);
		$recommend_users=array_slice($recommend_users, 0, (get_setting('recommend_users_number')-count($recommend_topics)));
				
		$recommend_users_topics = array_merge($recommend_users, $recommend_topics);
		
		//shuffle($recommend_users_topics);
		
		//return array_slice($recommend_users_topics, 0, 6);
		return $recommend_users_topics;
	}

	/**
	 * 边栏最新动态
	 */
	function sidebar_new_dynamic()
	{
		
// 		$cache_key = "topic_get_topic_list__10";
// 		$new_topic = ZCACHE::get($cache_key);
// 		if ($new_topic === false)
// 		{
			$new_topic = $this->model('topic')->get_topic_list('', 10);
			
			foreach ($new_topic as $k => $v)
			{
				$new_topic[$k]['topic_title'] = FORMAT::cut_str($v['topic_title'], 12, "...");
				$new_topic[$k]['topic_title_full'] = $v['topic_title'];
			}
			
// 			ZCACHE::set($cache_key, $new_topic, null, get_setting('cache_level_high'));
// 		}
		
	//	$new_list = $this->data_cache("question_topic_get_question_list_10", '$this->model("question_topic")->get_question_list(10)', get_setting('cache_level_high'));
		
			$new_list = $this->model("question_topic")->get_question_list(10);
		return array(
			"new_topic" => $new_topic, 
			"new_list" => $new_list
		);
	}

	/**
	 * 边栏分类
	 * @return 
	 */
	function sidebar_category()
	{
		$sidebar_category = $this->model('system')->fetch_category("question");
		
		return $sidebar_category;
	}

	/**
	 * 内容分类
	 * @return 
	 */
	function content_category()
	{
		$content_category = $this->model('system')->fetch_category("question");
		
		/*foreach ($content_category AS $key => $val)
		{
			$question_count = ZCACHE::get('question_count_category_' . $val['id']);
			
			if ($question_count === false)
			{
				$question_count = $this->model("question")->get_question_category_count($val['id']);
				
				ZCACHE::set('question_count_category_' . $val['id'], $question_count, false, get_setting('cache_level_high'));
			}
			
			$content_category[$key]['question_count'] = $question_count;
		}*/
		
		return $content_category;
	}

	/**
	 * 边栏热门用户
	 */
	function sidebar_hot_user2($uid = 0, $category = 0)
	{
		//	$hot_user_list = $this->data_cache(ZCACHE::format_key("account_get_answer_hot_user_list add_time > "), '$this->model("account")->get_answer_hot_user_list("")', get_setting('cache_level_high'));
		

		$category = $category * 1;
		$cache_key = "sidebar_hot_user" . $category;
		$data = ZCACHE::get($cache_key);
		
		if ($data === false)
		{
			$min_time = mktime() - 60 * 60 * 24 * get_setting('hot_user_period');
			if ($category > 0)
			{
			$sql = " SELECT  uid,count(*) as count FROM  " . $this->get_table("answer") . " WHERE category_id='" . $category . "' AND add_time>" . $min_time . " GROUP BY uid";
			}
			else
			{
				$sql = " SELECT  uid,count(*) as count FROM  " . $this->get_table("answer") . " WHERE add_time>" . $min_time . " GROUP BY uid";
			}
			$answer_rs = $this->query_all($sql);

			
			foreach ($answer_rs as $key => $val)
			{
				$user_answer_count[$val["uid"]] = $val["count"];
			}
			
			if ($category > 0)
			{
				$user_answer_all_count = $this->count("answer", " category_id='" . $category . "' AND add_time>" . $min_time);
				$sql = " SELECT  answer_uid,count(*) as count FROM  " . $this->get_table("answer_vote") . " WHERE  answer_id IN (SELECT answer_id FROM " . $this->get_table("answer") . " WHERE category_id='" . $category . "' AND add_time>" . $min_time . " ) AND add_time>" . $min_time . " AND vote_value=1 GROUP BY answer_uid";
			}
			else
			{
				$user_answer_all_count = $this->count("answer", "add_time>" . $min_time);
				$sql = " SELECT  answer_uid,count(*) as count FROM  " . $this->get_table("answer_vote") . " where add_time>" . $min_time . " AND vote_value=1 GROUP BY answer_uid";
			
			}

			
			
			$vote_rs = $this->query_all($sql);
			
			foreach ($vote_rs as $key => $val)
			{
				$user_vote_count[$val["answer_uid"]] = $val["count"];
			}
			
			//取出所有用户
			

			$users_list = $this->model("account")->get_users_list("", null, true,false);
			
		
			//计算数组得分
			foreach ($users_list as $key => $val)
			{
				
				$user_answer_count[$val["uid"]] = $user_answer_count[$val["uid"]] * 1;
				
				$user_vote_count[$val["uid"]] = $user_vote_count[$val["uid"]] * 1;
				$scores = 0;
				if ($user_answer_count[$val["uid"]])
				{
					
					$scores = (($user_answer_count[$val["uid"]] / $user_answer_all_count) * ($user_vote_count[$val["uid"]] + 1));
				}
				$scores = $scores + $val["online_time"] / 60 * 60 * 24 * get_setting('hot_user_period');
				$a_user_list[$val["uid"]] = array(
					"users" => $val, 
					"scores" => $scores
				);
			
			}
			
			if(is_array($a_user_list))
			{
				$a_user_list = aasort($a_user_list, array(
					"-scores"
				));
			}
			//数组排序
		}
		
		if ($a_user_list)
		{
			$i = 0;
			
			foreach ($a_user_list as $key => $val)
			{
				$i ++;
				
				if ($i > 10)
				{
					break;
				}
				
				$hot_user_list[$key] = $val["users"];
				$hot_user_list[$key]['focus'] = $this->model('follow')->user_follow_check($uid, $val['users']['uid']);
			
			}
		}
		
	   ZCACHE::set($cache_key, $hot_user_list, null, get_setting('cache_level_high'));
		return $hot_user_list;
	}

}