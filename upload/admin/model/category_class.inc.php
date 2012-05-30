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

class category_class extends GZ_MODEL
{
	/**
	 * 获取分类列表
	 */
	public function get_category_list($type, $parent_id = 0, $with_child = true, $level = 0, $category_rs = null)
	{		
		$category_list = array();
		
		if ($return_count)
		{
			return count($this->model('system')->fetch_category_data($type, $parent_id));
		}
		
		if (!$category_all = $this->model('system')->fetch_category_data($type, $parent_id))
		{
			return $category_list;
		}
		
		foreach ($category_all as $key => $val)
		{
			if ($level >= 1)
			{
				$title_prefix = "";
				
				for ($i = 1; $i <= $level; $i ++)
				{
					$title_prefix .= "— ";
				}
			}
			
			$val['tree_title'] = $title_prefix . $val['title'];
			
			$category_rs[] = $val;
			
			if ($with_child)
			{
				$level ++;
				
				if ($child_list = $this->get_category_list($type, $val['id'], true, $level, $category_rs))
				{
					$category_rs = $child_list;
				}
				
				$level --;
			}
		}
		
		return $category_rs;
	}


	public function get_category_by_id($category_id)
	{
		return $this->fetch_row('category', 'id = ' . intval($category_id));
	}
	
	public function update_category($category_id, $update_arr)
	{
		return $this->update('category', $update_arr, 'id = ' . $category_id);
	}
	
	public function add_category($type, $title, $description, $parent_id)
	{
		$data = array(
			'type' => $type,
			'title' => $title,
			'description' => $description,
			'parent_id' => intval($parent_id),
		);
		
		return $this->insert('category', $data);
	}

	public function delete_category($type, $category_id)
	{
		//递归删除子分类
		$childs = $this->get_category_list($type, $category_id);
		
		if($childs)
		{
			foreach($childs as $key => $val)
			{
				$this->delete_category($type, $val['id']);
			}
		}
		
		return $this->delete('category', 'id = ' . $category_id);
	}
}
