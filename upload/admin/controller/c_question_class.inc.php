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

class c_question_class extends ADMIN_CONTROLLER
{

	public function setup()
	{
		$this->crumb("问题管理", "?c=question");
	}

	public function index_action()
	{
		$this->list_v2_action();
	}

	public function list_v2_action()
	{
		$per_page = 10;
		
		$page_id = $this->_INPUT['page'];
		
		if (empty($page_id))
		{
			$page_id = 1;
		}
		
		$limit = ($page_id - 1) * $per_page . "," . $per_page;
		
		$keyword = $this->_INPUT['keyword'];
		
		$page_url = "?c=question&act=list";
		
		if (! empty($keyword))
		{
			$question_list = $this->model('question')->get_question_list(false, mysql_escape_string($keyword), $limit);
			$keyword_url .= "&keyword=" . $keyword;
			$totalnum = $this->model('question')->get_question_list(true, mysql_escape_string($keyword));
		}
		else
		{
			$question_list = $this->model('question')->get_question_list(false, "", $limit);
			$totalnum = $this->model('question')->get_question_list(true, "");
		}
		
		$question_list = $this->question_list_process($question_list);
		
		$this->model('pagination')->initialize(array(
			'base_url' => '?c=question&act=list_v2' . $keyword_url, 
			'total_rows' => $totalnum, 
			'per_page' => $per_page, 
			'last_link' => "末页", 
			'first_link' => "首页", 
			'next_link' => "下一页 »", 
			'prev_link' => "« 上一页", 
			'anchor_class' => ' class="number"', 
			'cur_tag_open' => '<a class="number current">', 
			'cur_tag_close' => '</a>'
		));
		
		TPL::assign("pagination", $this->model('pagination')->create_links());
		
		TPL::assign('keyword', $keyword);
		TPL::assign('list', $question_list);
		TPL::output("admin/question_list_v2", true);
	}

	/**
	 * 问题删除
	 */
	function question_remove_action()
	{
		$question_id = intval($this->_INPUT['question_id']);
		
		if (! $question_id)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "系统错误"));
		}
		
		$retval = $this->model('question')->remove_question($question_id);
		
		if ($retval)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "1", "删除成功"));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "删除失败"));
		}
	}

	/**
	 * 问题列表格式化
	 * Enter description here ...
	 * @param unknown_type $question_list
	 */
	function question_list_process($question_list)
	{
		if (empty($question_list))
		{
			return $question_list;
		}
		
		foreach ($question_list as $key => $val)
		{
			$detail_tmp = FORMAT::format_content($val['question_detail']);
			
			$question_list[$key]['published_user_name'] = $this->model('account')->get_real_name_by_uid($val['published_uid']);
			
			if (empty($detail_tmp['content_title']))
			{
				$question_list[$key]['question_detail'] = $detail_tmp['content_content'];
			}
			else
			{
				$question_list[$key]['question_detail'] = $detail_tmp['content_title'];
			}
		}
		return $question_list;
	}
	
	public function batch_action()
	{
		$batch_action = $this->_INPUT['batch_action'];
		
		$question_ids = $this->_INPUT['question_ids'];
		
		if(empty($batch_action))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "请先选择操作"));
		}

		if(empty($question_ids))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "请先选择问题"));
		}
		
		$q_ids = array();
		
		foreach($question_ids as $key => $val)
		{
			$q_ids[] = $val;
		}
		
		$q_ids = array_unique($q_ids);
		
		switch($batch_action)
		{
			case 'delete' :
				$this->model('question')->remove_question_by_ids($q_ids);
				break;
			default:
		}
		
		H::ajax_json_output(GZ_APP::RSM(null, "1", "操作成功"));
	}
	
}