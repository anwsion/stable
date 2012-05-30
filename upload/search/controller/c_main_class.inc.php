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
		$rule_action["rule_type"]="white"; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["actions"]=array();
		return $rule_action;
	}
	
	public function setup()
	{
		HTTP::no_cache_header();
		
		$this->crumb('搜索', '/search/');
	}
	
	public function index_action()
	{
		$keyword = urldecode($_GET['q']);
		
		$this->crumb($keyword, '/search/?q=' . $_GET['q']);
		
		if (trim($keyword) == '')
		{
			HTTP::redirect('/index/');	
		}
		
		TPL::assign('keyword', htmlspecialchars($keyword));
		
		TPL::import_css(array(
			'css/search.css',
		));
		
		TPL::output('search/search_result');
	}
	
	public function all_action()
	{
		echo json_encode($this->model('search')->search_all($this->_INPUT['q'], $this->_INPUT['type'], intval($this->_INPUT['limit'])));
	}
	
	/**
	 * 
	 * 列出用户搜索信息
	 */
	public function search_question_action()
	{
		echo json_encode($this->model('search')->search_question($this->_INPUT['q'], intval($this->_INPUT['limit'])));
	}
	
	/**
	 * 
	 * 列出话题搜索信息
	 */
	public function search_topic_action()
	{
		echo json_encode($this->model('search')->search_topic($this->_INPUT['q'], intval($this->_INPUT['limit'])));
	}
	
	public function search_user_action()
	{
		echo json_encode($this->model('search')->search_user($this->_INPUT['q'], intval($this->_INPUT['limit'])));
	}
}