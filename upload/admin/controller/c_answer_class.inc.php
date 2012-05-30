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

class c_answer_class extends ADMIN_CONTROLLER
{

	public function setup()
	{
		$this->crumb("答案管理", "?c=question");
	}

	public function list_action()
	{
		$question_id = $this->_INPUT['question_id'];
	
		if(empty($question_id))
		{
			H::js_pop_msg('问题编号错误，请返回');
		}
	
		$per_page = 20;
	
		$page_id = $this->_INPUT['page'];
	
		if (empty($page_id))
		{
			$page_id = 1;
		}
	
		$limit = ($page_id - 1) * $per_page . "," . $per_page;
	
		$keyword = $this->_INPUT['keyword'];
	
		$page_url = "?c=answer&act=list&question_id=" . $question_id;
	
		$question_info = $this->model("question")->get_question_info_by_id($question_id);
	
		if(empty($question_info))
		{
			H::js_pop_msg('问题编号错误，请返回');
		}
	
		$answer_list = $this->model('answer')->get_answer_list_by_question_id($question_id, $limit);

		$this->model('pagination')->initialize(array(
				'base_url' => $page_url,
				'total_rows' => $question_info['answer_count'],
				'per_page' => $per_page,
				'last_link' => "末页",
				'first_link' => "首页",
				'next_link' => "下一页 »",
				'prev_link' => "« 上一页",
				'anchor_class' => ' class="number"',
				'cur_tag_open' => '<a class="number current">',
				'cur_tag_close' => '</a>'
		));
		
		$this->crumb("问题 : {$question_info['question_content']}  回复列表", "?c=question&act=answer_list");
		
		TPL::assign("pagination", $this->model('pagination')->create_links());
	
		TPL::assign('list', $answer_list);
	
		TPL::output('admin/answer_list', true);
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

	public function batch_action()
	{
		$batch_action = $this->_INPUT['batch_action'];
		
		$answer_ids = $this->_INPUT['answer_ids'];
		
		if(empty($batch_action))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "请先选择操作"));
		}

		if(empty($answer_ids))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "请先选择问题"));
		}
		
		$a_ids = array();
		
		foreach($answer_ids as $key => $val)
		{
			$a_ids[] = $val;
		}
		
		switch($batch_action)
		{
			case 'delete' :
				$this->model('answer')->delete_answers_by_ids($a_ids);
				break;
			default:
		}
		
		H::ajax_json_output(GZ_APP::RSM(null, "1", "操作成功"));
	}
	


}