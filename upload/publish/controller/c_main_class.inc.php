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

class c_main_class extends GZ_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action["rule_type"] = 'white'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["actions"] = array();
		return $rule_action;
	}

	public function setup()
	{
		$this->crumb('信息发布', '/publish/');
	}

	public function index_action()
	{
		/*if ($this->is_post())
		{
			if (trim($_POST['competitions_content']) == '')
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '请输入比赛标题'));
			}
			
			if (intval($_POST['category_id']) == 0)
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '请选择分类'));
			}
			
			if (trim($_POST['specific']) == '')
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '请填写具体需求'));
			}
			
			if (trim($_POST['uninterested']) == '')
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '请填写不感兴趣'));
			}
			
			if (! $_POST['bonus'])
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '请选择奖金'));
			}
			
			if ($_POST['bonus'] == - 1 and $_POST['bonus_other'] == '')
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '请填写奖金'));
			}
			
			if (! $_POST['expire_time'])
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '请填写过期时间'));
			}
			else if ((strtotime($_POST['expire_time']) + 3600 * 24) < time())
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '过期时间不能小于明天'));
			}
			
			if ($_POST['bonus'] == - 1)
			{
				$bonus = intval($_POST['bonus_other']);
			}
			else
			{
				$bonus = $_POST['bonus'];
			}
			
			if (intval($bonus) < 0)
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '奖金金额无效'));
			}
			
			if ($bonus)
			{
				//if (!$this->model('credit')->check_user_credit($this->user_id, $bonus))
				if (! $this->model('credit')->set_user_credit("PAY", $this->user_id, "发布竞赛: " . htmlspecialchars($_POST['competitions_content']), false, (0 - $bonus)))
				{
					H::ajax_json_output(GZ_APP::RSM(null, '-1', '您的账户目前的金币 ' . $this->user_info['credit'] . ' 不够发布这次竞赛'));
				}
			}
			
			$competition_id = $this->model('publish')->publish_competition($_POST['competitions_content'], $_POST['category_id'], $_POST['specific'], $_POST['uninterested'], $bonus, $_POST['expire_time'], $_POST['contribute_pri'], $banner_id, $_POST['attach_access_key']);
			
			if ($competition_id)
			{
				$competition_obj = $this->model('competitions');
				
				$competition_obj->update_competition_state($competition_id, ACTION_LOG::ADD_COMPETITIONS);
				
				$this->model('competitions')->competitions_add_focus($competition_id, $this->user_id);
				
				ACTION_LOG::save_action($this->user_id, $competition_id, ACTION_LOG::CATEGORY_COMPETITIONS, ACTION_LOG::ADD_COMPETITIONS);
				
				//索引
				$this->model("associate_index")->add_associate_index($this->user_id, $competition_id, 4);
			
			}
			
			if ($_POST['attach_access_key'])
			{
				$this->model('publish')->update_competition_attach($competition_id, $_POST['attach_access_key']);
			}
			
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => urlencode('/contest/' . $competition_id)
			), '1', '发布成功'));
		}*/
		
		/*if ($_GET['competition_id'])
		{
			$competition_info = $this->model('competitions')->get_competitions_by_id(intval($_GET['competition_id']));
			
			if (empty($competition_info))
			{
				H::js_pop_msg("系统错误!", '/contest/' . $_GET['competition_id']);
			}
			
			if ($this->user_info['group_id'] != 1)
			{
				if ($competition_info['published_uid'] != $this->user_id)
				{
					H::js_pop_msg("权限错误!", '/contest/' . $_GET['competition_id']);
				}
				
				if ((time() - $competition_info['add_time']) > intval(get_setting('competition_edit_time')) * 60)
				{
					H::js_pop_msg("对不起，发布竞赛超过 " . get_setting('competition_edit_time') . " 分钟以后无法修改!", '/contest/' . $competition_info['competitions_id']);
				}
			}
			
			TPL::assign('competition_info', $competition_info);
		}
		else */if ($_GET['question_id'])
		{
			$question_info = $this->model('question')->get_question_info_by_id(intval($_GET['question_id']));
			
			if (empty($question_info))
			{
				H::js_pop_msg("系统错误!", '/question/?act=detail&question_id=' . $_GET['question_id']);
			}
			
			if ($this->user_info['group_id'] != 1)
			{
				if ($question_info['published_uid'] != $this->user_id)
				{
					H::js_pop_msg("权限错误!", '/question/?act=detail&question_id=' . $_GET['question_id']);
				}
				
				if ((time() - $question_info['add_time']) > intval(get_setting('question_edit_time')) * 60)
				{
					H::js_pop_msg("对不起，发布问题超过 " . get_setting('question_edit_time') . " 分钟以后无法修改!", '/question/?act=detail&question_id=' . $competition_info['competitions_id']);
				}
			}
			
			TPL::assign('question_info', $question_info);
		}
		else if ($this->is_post())
		{
			TPL::assign('question_info', array(
				'question_content' => $_POST['question_content'],
				'question_detail' => $_POST['question_detail']
			));
			
			$question_info['category_id'] = $_POST['category_id'];
		}
		
		//$category_list = $this->model('competitions')->get_competitions_category_list();
		
		TPL::assign('attach_access_key', md5($this->user_id . time()));
		
		TPL::assign('topic_tree', $this->model('topic')->get_topic_tree_list(5));
		
		//TPL::assign('category_list', $category_list);
		
		TPL::assign('question_category_list', $this->model('system')->build_category_html('question', 0, $question_info['category_id']));
		
		TPL::import_css(array(
			'css/discussion.css', 
			'js/fileuploader/fileuploader.css'
		));
		
		TPL::import_js(array(
			'js/fileuploader/fileuploader.js'
		));
		
		TPL::output('publish/publish');
	}

	public function question_action()
	{		
		$question_content = trim($this->_INPUT['question_content']);
		$question_detail = $this->_INPUT['question_detail'];
		
		$topic_id = trim($this->_INPUT['topic_id']);
		
		if (empty($question_content))
		{
			H::ajax_json_output(GZ_APP::RSM(null, - 1, '请输入问题'));
		}
		
		if (!$this->_INPUT['category_id'])
		{
			H::ajax_json_output(GZ_APP::RSM(null, - 1, '请选择分类'));
		}
		
		if ($question_id = $this->model('question')->save_question($question_content, $question_detail, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0))
		{
			$this->model('question')->update_question_category($question_id, intval($this->_INPUT['category_id']));
			
			if ($topic_id > 0)
			{
				$topic = $this->model('topic')->get_topic($topic_id);
				
				if (! empty($topic))
				{
					$this->model('question_topic')->save_link($topic_id, $question_id);
				}
			}
			
			$this->model('question')->update_question_state($question_id, $this->user_id, ACTION_LOG::ADD_REQUESTION_FOCUS);
			
			if (is_array($_POST['topics']))
			{
				foreach ($_POST['topics'] as $key => $topic_title)
				{
					$topic_id = $this->model('topic')->save_topic($question_id, $topic_title, $this->user_id);
					
					$this->model('question_topic')->save_link($topic_id, $question_id);
				}
			}
			
			if ($_POST['attach_access_key'])
			{
				$this->model('publish')->update_question_attach($question_id, $_POST['attach_access_key']);
			}
			
			//自动关注该问题
			$this->model('question')->add_focus_question($question_id, false);
			
			//索引
			$this->model("associate_index")->add_associate_index($this->user_id, $question_id, 3);
			
			//记录日志
			ACTION_LOG::save_action($this->user_id, $question_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::ADD_QUESTION, htmlspecialchars(trim($question_content)), htmlspecialchars(trim($question_detail)));
			
			$url = urlencode(get_setting('base_url') . '/question/?act=detail&question_id=' . $question_id);
			
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => $url
			), 1, '发布问题成功'));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '发布问题失败,请重新发布!'));
		}
	}
}