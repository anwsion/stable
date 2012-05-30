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

class c_topic_class extends ADMIN_CONTROLLER
{

	public function get_permission_action()
	{
		return array(
			'list_v2'
		);
	}

	public function setup()
	{
		$this->crumb("话题管理", "?c=topic");
	}

	public function index_action()
	{
		$this->list_v2_action();
	}

	public function list_v2_action()
	{
		$per_page = 10;
		
		$page_id = $this->_INPUT['page'];
		
		$keyword = $this->_INPUT['keyword'];
		
		if (empty($page_id))
		{
			$page_id = 1;
		}
		
		$limit = ($page_id - 1) * $per_page . "," . $per_page;
		
		if (! empty($keyword))
		{
			$topic_list = $this->model('topic')->get_topic_list("topic_title LIKE '%" . mysql_escape_string($keyword) . "%'", $limit);
			$totalnum = $this->model('topic')->get_topic_list("topic_title LIKE '%" . mysql_escape_string($keyword) . "%'", '', true);
			$keyword_url = "&keyword=" . $keyword;
			TPL::assign('keyword', $keyword);
		}
		else
		{
			$topic_list = $this->model('topic')->get_topic_list("", $limit);
			$totalnum = $this->model('topic')->get_topic_list('', '', true);
		}
		
		$topic_list = $this->topic_list_process($topic_list);
		
		$this->model('pagination')->initialize(array(
			'base_url' => '?c=topic&act=list_v2' . $keyword_url, 
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
		
		$this->crumb("话题列表", "?c=topic&act=list_v2");
		
		TPL::assign('list', $topic_list);
		
		TPL::output("admin/topic_list_v2", true);
	}

	/**
	 * 格式化话题列表
	 * Enter description here ...
	 * @param unknown_type $list
	 */
	function topic_list_process($list)
	{
		if (empty($list))
		{
			return false;
		}
		
		foreach ($list as $key => $topic)
		{
			$list[$key]['add_time'] = date("Y-m-d H:i", $topic['add_time']);
			$list[$key]['topic_title'] = FORMAT::cut_str($topic['topic_title'], 12, "...");
			
			if (empty($topic['topic_pic']))
			{
				$list[$key]['topic_pic'] = G_STATIC_URL.'/common/topic-min-img.jpg';
			}
			else
			{
				$list[$key]['topic_pic'] = get_setting('upload_url').'/topic/' . str_replace("topic_max", "topic_min", $topic['topic_pic']);
			}
			
			if ($topic['parent_id'] > 0)
			{
				$list[$key]['parent'] = $this->model('topic')->get_topic($topic['parent_id']);
			}
		}
		
		return $list;
	}

	/**
	 * 锁定话题
	 * Enter description here ...
	 */
	public function topic_lock_action()
	{
		$topic_id = $this->_INPUT['topic_id'];
		
		$status = $this->_INPUT['status'];
		
		if (! in_array($status, array(
			'1', 
			'0'
		)))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "系统错误"));
		}
		
		$this->model('topic')->lock_topic_by_id($topic_id, $status);
		
		H::ajax_json_output(GZ_APP::RSM(null, "1", ""));
	}

	/**
	 * 添加话题
	 * Enter description here ...
	 */
	public function topic_add_action()
	{
		$this->crumb("添加话题");
		
		TPL::assign('act', 'save_ajax');
		TPL::output("admin/topic_add_v2", true);
	}

	/**
	 * 话题修改
	 * Enter description here ...
	 */
	public function edit_v2_action()
	{
		$this->crumb("话题修改");
		
		$topic_id = $this->_INPUT['topic_id'];
		
		$topic = $this->model('topic')->get_topic($topic_id);
		
		if (empty($topic['parent_id']))
		{
			$parent_id = 0;
		}
		else
		{
			$parent_id = $topic['parent_id'];
			
			$parent_topic = $this->model('topic')->get_topic($parent_id);
		}
		
		if (empty($topic['topic_pic']))
		{
			$topic['topic_pic_max'] = G_STATIC_URL.'/common/topic-max-img.jpg';
		}
		else
		{
			$topic['topic_pic_max'] = get_setting('upload_url').'/topic/' . str_replace('32_32', '150_150', $topic['topic_pic']);
		}
		
		$topic['parent_id'] = empty($topic['parent_id']) ? 0 : $topic['parent_id'];
		
		TPL::assign('refer', $_SERVER['HTTP_REFERER']);
		TPL::assign('parent_id', $parent_id);
		TPL::assign('topic', $topic);
		TPL::assign('parent_topic', $parent_topic);
		TPL::assign('act', 'save_ajax');
		TPL::output("admin/topic_edit_v2", true);
	}

	/**
	 * 树形话题列表
	 * Enter description here ...
	 */
	public function topic_select_list_v2_ajax_action()
	{
		$parent_id = $this->_INPUT['parent_id'];
		$topic_id = $this->_INPUT['topic_id'];
		$target_id = $this->_INPUT['target_id'];
		
		$topic = $this->model('topic')->get_topic($parent_id);
		
		if (! $topic['parent_id'])
		{
			$topic['parent_id'] = 0;
		}
		
		$topic_parent = $this->model('topic')->get_topic($parent_id);
		
		$topic_list = $this->model('topic')->get_topic_by_parent_id(false, $parent_id);
		
		$pos = $this->model('topic')->get_position_by_id($parent_id);
		
		array_unshift($pos, array(
			'topic_title' => "顶级话题", 
			"topic_id" => 0
		));
		
		TPL::assign('parent_id', $parent_id);
		TPL::assign('topic_id', $topic_id);
		TPL::assign('target_id', $target_id);
		TPL::assign('topic', $topic);
		TPL::assign('topic_parent', $topic_parent);
		TPL::assign('topic_list', $topic_list);
		TPL::assign('pos', $pos);
		TPL::assign('ajax_link', "?c=topic&act=topic_select_list_v2_ajax&topic_id={$topic_id}&target_id={$target_id}");
		TPL::output("admin/topic_select_list_v2_ajax", true);
	}

	/**
	 * 修改话题父话题
	 * Enter description here ...
	 */
	public function edit_parent_ajax_action()
	{
		$topic_id = $this->_INPUT['topic_id'];
		
		$parent_id = $this->_INPUT['parent_id'];
		
		if ((intval($topic_id) <= 0) || (intval($parent_id) <= 0))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "系统错误"));
		}
		
		$this->model('topic')->update_topic($topic_id, '', '', '', '', '', '', '', $parent_id);
		
		H::ajax_json_output(GZ_APP::RSM(null, "1", ''));
	}

	/**
	 * 保存修改话题
	 * Enter description here ...
	 */
	public function save_ajax_action()
	{
		$topic_id = $this->_INPUT['topic_id'];
		$topic_title = FORMAT::safe($this->_INPUT['topic_title'],true);
		$topic_description = FORMAT::safe($this->_INPUT['topic_description'],true);
		$refer_url = $this->_INPUT['refer'];
		$is_top = $this->_INPUT['is_top'];
		$topic_lock = $this->_INPUT['topic_lock'];
		$parent_id = $this->_INPUT['parent_id'];
		
		if (empty($topic_id))
		{
			$topic_id = $this->model('topic')->save_topic(0, '', 1, 0, 0, '', '', 0, 3);
		}
		else
		{
			$topic_info = $this->model('topic')->get_topic($topic_id);
			
			if (($topic_info['topic_title'] != $topic_title) && ($this->model('topic')->get_topic_by_title($topic_title)))
			{
				H::ajax_json_output(GZ_APP::RSM(null, "-1", "话题名称已经存在！"));
			}
		}
		
		if($_FILES['topic_pic']['name'] && $topic_id)
		{
			define('IROOT_PATH', get_setting('upload_dir').'/topic/');
			define('ALLOW_FILE_FIXS', 'jpg,png,jpeg,gif');
			
			$date = date('Ymd');
				
			$this->model('image')->data_dir = "";
			$this->model('image')->images_dir = "";
			$random_filename = $this->model('image')->random_filename(2);
			$file_name = $this->model('image')->upload_image($_FILES['topic_pic'], $date, $random_filename);
		
			//生成缩图
			if (!$file_name)
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', "图片格式错误"));
				exit();
			}
				
			$file_name = IROOT_PATH . $file_name;
			$ext = $this->model('image')->get_filetype($file_name);
		
			foreach( GZ_APP::config()->get('image')->topic_thumbnail as $key=>$val)
			{
				$thumb_file[$key]=$this->model('image')->make_thumb($file_name, $val['w'],  $val['h'], IROOT_PATH . $date . "/", $random_filename . "_".$val['w']."_".$val['h']. $ext, true);
			}
				
			/*
			if($thumb_file["mid"])
			{
				$this->model('topic')->modify_pic_topic_by_id($topic_id, $date . '/'.$thumb_file["min"]);
			}
			*/
			
			//删除CACHE;
			$topic_info = $this->model('topic')->get_topic($topic_id);
			
			if ($topic_info)
			{
				ZCACHE::cleanGroup(ZCACHE::format_key("topic_info_" . $topic_id));
			}
		}
		
		if ($topic_id)
		{
			$this->model('topic')->update_topic($topic_id, $topic_title, $topic_description, $date . '/'.$thumb_file["min"], '', '', $is_top, $topic_lock, $parent_id);
			$this->model('topic')->lock_topic_by_id($topic_id, $topic_lock);
			
			$refer_url = empty($refer_url) ? "?c=topic&act=list_v2" : $refer_url;
			
			H::ajax_json_output(GZ_APP::RSM(array(
				'url' => urlencode($refer_url)
			), "1", ""));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "保存失败，请联系管理员。"));
		}
	}

	/**
	 * 删除话题
	 * Enter description here ...
	 */
	public function topic_remove_ajax_action()
	{
		$topic_id = intval($this->_INPUT['topic_id']);
		
		if (! topic_id)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "系统错误"));
		}
		
		if ($this->model('topic')->remove_topic($topic_id))
		{
			H::ajax_json_output(GZ_APP::RSM(null, "1", "删除成功"));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "删除失败"));
		}
	
	}

}