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

class system_class extends GZ_MODEL
{
	public function fetch_category_data($type, $parent_id = 0)
	{
		static $category_list_all;
		
		if (!$category_list_all[$type])
		{
			if ($type)
			{
				$category_list_all_query = $this->fetch_all('category', '`type` = \'' . $this->quote($type) . '\'', 'id ASC');
			}
			else
			{
				$category_list_all_query = $this->fetch_all('category', '', 'id ASC');
			}
			
			foreach ($category_list_all_query AS $key => $val)
			{
				$category_list_all[$type][$val['parent_id']][] = $val;
			}
		}
		
		if (!$category_all = $category_list_all[$type][$parent_id])
		{
			return array();
		}
		
		return $category_all;
	}
	
	/* 获取分类数组 */
	public function fetch_category($type, $parent_id = 0)
	{
		$category_list = array();
		
		if (!$category_all = $this->fetch_category_data($type, $parent_id))
		{
			return $category_list;
		}
		
		foreach ($category_all AS $key => $val)
		{
			if (!$val['icon'])
			{
				$val['icon'] = G_STATIC_URL . '/css/default/img/default_class_imgs.png';
			}
			else
			{
				$val['icon'] = get_setting('upload_url') . '/category/' . $val['icon'];
			}
			
			$category_list[$val['id']] = array(
				'id' => $val['id'],
				'title' => $val['title'],
				'icon' => $val['icon'],
				'description' => $val['description'],
				'parent_id' => $val['parent_id']
			);
			
			if ($child_list = $this->fetch_category($type, $val['id']))
			{
				$category_list[$val['id']]['child'] = $child_list;
			}
		}
		
		return $category_list;
	}
	
	/* 获取分类 HTML 数据 */
	public function build_category_html($type, $parent_id = 0, $selected_id = 0, $prefix = '')
	{
		$category_list = $this->fetch_category($type, $parent_id);
		
		if (!$category_list)
		{
			return false;
		}
		
		if ($prefix)
		{
			$_prefix = $prefix . ' ';
		}
		
		foreach ($category_list AS $category_id => $val)
		{
			if ($selected_id == $val['id'])
			{
				$html .= '<option value="' . $category_id . '" selected="selected">' . $_prefix . $val['title'] . '</option>';
			}
			else
			{
				$html .= '<option value="' . $category_id . '">' . $_prefix . $val['title'] . '</option>';
			}
			
			if ($val['child'])
			{
				$prefix .= '-';
				
				$html .= $this->build_category_html($type, $val['id'], $selected_id, $prefix);
			}
		}
			
		return $html;
	}
	
	/* 获取分类 JSON 数据 */
	public function build_category_json($type, $parent_id = 0, $prefix = '')
	{
		$category_list = $this->fetch_category($type, $parent_id);
		
		if(empty($category_list))
		{
			return false;
		}
		
		if ($prefix)
		{
			$_prefix = $prefix . ' ';
		}
		
		foreach ($category_list AS $category_id => $val)
		{
			$data[] = array(
				'id' => $category_id,
				'title' => $_prefix . $val['title'],
				'description' => $val['description']
			);
			
			if ($val['child'])
			{
				$prefix .= '-';
				
				$data = array_merge($data, json_decode($this->build_category_json($type, $val['id'], $prefix), true));
			}
		}
			
		return json_encode($data);
	}
	
	/* 获取数组信息 */
	public function get_category_info($category_id)
	{
		static $all_category;
		
		if (!$all_category)
		{
			$all_category_query = $this->fetch_all('category');
			
			foreach ($all_category_query AS $key => $val)
			{
				$all_category[$val['id']] = $val;
			}
		}
		
		return $all_category[$category_id];
	}
	
	public function get_category_list($type)
	{
		$category_list = array();
		
		$category_all = $this->fetch_all('category', '`type` = \'' . $this->quote($type) . '\'', 'id ASC');
		
		foreach($category_all as $key => $val)
		{
			$category_list[$val['id']] = $val;
		}
		
		return $category_list;
	}
	
	public function get_category_with_child_ids($type, $category_id)
	{
		$category_ids[] = $category_id;
		
		if ($child_ids = $this->fetch_category_data($type, $category_id))
		{
			foreach ($child_ids AS $key => $val)
			{
				$category_ids[] = $val['id'];
			}
		}
		
		return $category_ids;
	}
	
	public function clean_break_attach()
	{
		if ($answer_attachs = $this->fetch_all('answer_attach', '(answer_id IS NULL OR answer_id < 1) AND add_time < ' . (time() - 3600 * 24)))
		{
			foreach ($answer_attachs AS $key => $val)
			{
				$this->model('answer')->remove_answer_attach($val['id'], $val['access_key']);
			}
		}
		
		if ($question_attachs = $this->fetch_all('question_attach', '(question_id IS NULL OR question_id < 1) AND add_time < ' . (time() - 3600 * 24)))
		{
			foreach ($question_attachs AS $key => $val)
			{
				$this->model('publish')->remove_question_attach($val['id'], $val['access_key']);
			}
		}
	}
}