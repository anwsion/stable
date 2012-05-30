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

class c_ajax_class extends GZ_CONTROLLER
{
	var $per_page = 20;
	
	public function get_access_rule()
	{
		$rule_action["rule_type"]="white"; //'black'黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["actions"] = array();
		
		return $rule_action;
	}
	
	public function setup()
	{
		HTTP::no_cache_header();
	}
	
	public function search_result_action()
	{
		$limit = intval($_GET["page"]) * $this->per_page . ', ' . $this->per_page;
		
		switch ($_GET['search_type'])
		{
			default:
			/*case 'competitions':
				$search_result = $this->model('search')->search_competition($_GET['q'], $limit);
			break;*/
		
			case 'questions':
				$search_result = $this->model('search')->search_question($_GET['q'], $limit);
			break;
			
			case 'topics':
				$search_result = $this->model('search')->search_topic($_GET['q'], $limit);
			break;
			
			case 'users':
				$search_result = $this->model('search')->search_user($_GET['q'], $limit);
			break;
		}
		
		TPL::assign('search_result', $search_result);
		
		TPL::output('search/search_result_ajax');
	}
}