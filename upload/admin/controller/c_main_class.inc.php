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

class c_main_class extends ADMIN_CONTROLLER
{

	public function setup()
	{
		$this->crumb("管理首页", "?c=login&act=main");
	}

	public function index()
	{
		$this->setting_action();
	}

	public function setting_action()
	{
		if ($this->admin_info['group_id'] == "1")
		{
			$frame_url = "?c=setting&act=setting";
		}
		else
		{
			$frame_url = "?act=welcome";
		}
		
		TPL::assign("frame_url", $frame_url);
		
		TPL::output("admin/admin_index", true);
	}

	public function left_action()
	{
		TPL::assign("list", $this->model('admin_group')->get_avail_menu_by_group_id($this->admin_info['group_id']));
		
		TPL::output("admin/admin_left", true);
	}

	public function welcome_action()
	{
		TPL::output("admin/admin_welcome", true);
	}
}