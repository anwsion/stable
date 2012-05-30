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

class admin_group_class extends GZ_MODEL
{
    
	/**
	 * 获得组用户权限
	 * Enter description here ...
	 * @param unknown_type $group_id
	 */
	function get_permission_by_group_id($group_id)
	{
		$group_id = intval($group_id);
		
		$group = $this->fetch_row('admin_group', 'group_id=' . $group_id );
		
		if(empty($group))
		{
			return false;
		}
		
		$data = array();
		
		foreach(explode(",", $group['permission']) as $key => $val)
		{
			$pri_arr = explode("->", $val);

			$data[$pri_arr[0]][] = $pri_arr[1];
		}
		
		return $data;
	}
	
	
	/**
	 * 获得组可见栏目
	 * Enter description here ...
	 * @param unknown_type $group_id
	 */
	function get_avail_menu_by_group_id($group_id)
	{
		$group_id = intval($group_id);

		$group = $this->fetch_row('admin_group', 'group_id=' . $group_id );
		
		if(empty($group))
		{
			return false;
		}
		
		if($group['menu'] == "all")
		{
			return $this->get_menu_by_ids("all", $group['no_menu']);
		}
		
		$menu_ids = array();
		
		foreach(explode(",", $group['menu']) as $menu_id)
		{
			$menu_ids[] = $menu_id;
		}
		
		return $this->get_menu_by_ids($menu_ids);
	}
	
	/**
	 * 根据栏目id集获得栏目列表
	 * Enter description here ...
	 * @param unknown_type $ids
	 */
	public function get_menu_by_ids($ids, $no_menus = '')
	{
		if($ids == "all")
		{
			$where = "status = 0";
			
			if(!empty($no_menus))
			{
				$where .= " AND id NOT IN(" . $no_menus . ")";
			}
		}
		else
		{
			$ids = array_unset_null_value($ids);
			
			if(is_array($ids))
			{
				$where = "status = 0 AND id IN (" . implode(',', $ids) . ")";
			}
		}
		
		$data = $this->fetch_all('admin_menu', $where, 'parent_id ASC, sort ASC, id ASC', '999');
		
		return $this->menu_list_process($data);
	}
	
	/**
	 * 列表格式化
	 * Enter description here ...
	 * @param unknown_type $list
	 */
	public function menu_list_process($list)
	{
		$data = array();
		$new_list = array();
		
		foreach($list as $key => $val)
		{
			$data[$val['id']] = $val;
		}
		
		foreach($data as $key => $val)
		{
			if($val['parent_id'] == 0)
			{
				$new_list[$key] = $val;
			}
			else if($val['parent_id'] > 0)
			{
				$new_list[$val['parent_id']]['children'][] = $val;
			}
		}
		
		unset($new_list[1]);	//暂不显示
		
		return $new_list;
	}

}
