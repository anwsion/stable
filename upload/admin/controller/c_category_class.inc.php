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

class c_category_class extends ADMIN_CONTROLLER
{

	public function setup()
	{
		$this->crumb("分类管理", "?c=question");
	}

	public function index_action()
	{
		$this->list_v2_action();
	}

	public function list_action()
	{
		$category_list = $this->model('category')->get_category_list('question', 0, true, 0);
		
		$this->crumb('分类管理');
		
		TPL::assign('list', $category_list);
		TPL::assign('category_option', $this->model('system')->build_category_html('question', 0));
		TPL::output("admin/category_list", true);
	}

	public function edit_action()
	{
		$category_id = intval($this->_INPUT['category_id']);
		
		$category = $this->model('category')->get_category_by_id($category_id);
		
		$this->crumb('分类修改');
		
		TPL::assign('category', $category);
		TPL::assign('category_option', $this->model('system')->build_category_html('question', 0, $category['parent_id']));
		TPL::output('admin/category_edit', true);
	}

	/**
	 * 保存分类
	 */
	public function save_ajax_action()
	{
		$category_id = intval($this->_INPUT['category_id']);
		$title = $this->_INPUT['title'];
		$description = $this->_INPUT['description'];
		$parent_id = intval($this->_INPUT['parent_id']);
		
		//上传图片
		if ($_FILES['category_icon'])
		{
			define('IROOT_PATH', get_setting('upload_dir') . '/category');
			define('ALLOW_FILE_FIXS', 'jpg,jpeg,gif,png'); //限制上传图片格式
			
			$class_image = $this->model('image');
			$class_image->data_dir = "";
			$class_image->images_dir = "";
			$file_name = $class_image->upload_image($_FILES["category_icon"]);
			
			//生成缩图
			if (! $file_name)
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', "上传图片格式错误"));
			}
			
			$file_name = IROOT_PATH . $file_name;
			
			$ext = $class_image->get_filetype($file_name);
		}
		
		//增加新分类
		if (! $category_id)
		{
			if (empty($title))
			{
				H::ajax_json_output(GZ_APP::RSM(null, "-1", "分类标题不能为空"));
			}
			
			$category_id = $this->model('category')->add_category('question', $title, htmlspecialchars($description), $parent_id);
			
			if ($_FILES['category_icon'])
			{
				$icon = $class_image->make_thumb($file_name, 50, 50, IROOT_PATH . "/", $category_id . $ext, true);
			}
			
			if ($category_id)
			{
				$update_arr = array(
					'icon' => $icon
				);
				
				$this->model('category')->update_category($category_id, $update_arr);
				
				H::ajax_json_output(GZ_APP::RSM(array(
					'url' => '?c=category&act=list'
				), "1", "添加成功"));
			}
			else
			{
				H::ajax_json_output(GZ_APP::RSM(null, "-1", "添加失败"));
			}
		}
		
		//修改分类
		$category = $this->model('category')->get_category_by_id($category_id);
		
		if ($category['id'] == $parent_id)
		{
			H::ajax_json_output(GZ_APP::RSM(null, "1", "不能设置当前分类为父级分类"));
		}
		
		$update_arr = array(
			'title' => $title,
			'description' => htmlspecialchars($description),
			'parent_id' => $parent_id
		);
		
		if ($_FILES['category_icon'])
		{
			$icon = $class_image->make_thumb($file_name, 50, 50, IROOT_PATH . "/", $category_id . $ext, true);
			$update_arr['icon'] = $icon;
		}
		
		$this->model('category')->update_category($category_id, $update_arr);
		
		H::ajax_json_output(GZ_APP::RSM(array(
			'url' => '?c=category&act=list'
		), "1", "修改成功"));
	
	}

	/**
	 * 上传分类图标
	 */
	public function upload_icon_action()
	{
		define('IROOT_PATH', get_setting('upload_dir') . '/category');
		define('ALLOW_FILE_FIXS', 'jpg,jpeg,gif,png');
		
		$category_id = intval($this->_INPUT['category_id']);
		
		if ($category_id == 0)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '编号错误,请稍后重试!'));
		}
		
		if ($_FILES["category_icon"]["name"])
		{
			$class_image = $this->model('image');
			$class_image->data_dir = "";
			$class_image->images_dir = "";
			$file_name = $class_image->upload_image($_FILES["category_icon"]);
			
			//生成缩图
			if (! $file_name)
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', "图片格式错误"));
			}
			$file_name = IROOT_PATH . $file_name;
			
			$ext = $class_image->get_filetype($file_name);
			$thumb_file_mid = $class_image->make_thumb_imagick($file_name, 50, 50, IROOT_PATH . "/", $category_id . $ext, true);
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '没选择图片'));
		}
		
		if ($thumb_file_mid === false)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '图片上传错误,请稍后再试!'));
		}
		else
		{
			$this->model('category')->update_category($category_id, array(
				'icon' => $category_id . $ext
			));
			
			H::ajax_json_output(GZ_APP::RSM(array(
				'avatar_file' => get_setting('upload_url') . '/category' . $thumb_file_mid
			), '1', ''));
		}
	}

	public function category_remove_action()
	{
		$category_id = intval($this->_INPUT['category_id']);
		
		if (! $category_id)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '系统错误'));
		}
		
		if ($this->model('category')->delete_category('question', $category_id))
		{
			H::ajax_json_output(GZ_APP::RSM(null, '1', ''));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '删除失败'));
		}
	}

}