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

class ADMIN_CONTROLLER extends GZ_CONTROLLER
{
	public $admin_uid;
	public $admin_info;
	public $gz_constructed = false;
	public $_INPUT;

	public function __construct()
	{
		$this->gz_constructed = true;
		
		global $__controller;
		
		$skip_controller = array(
			"login"
		);
		
		if (! in_array($__controller, $skip_controller))
		{
			admin_class::check_admin_login();
		}
		
		$this->admin_uid = admin_class::get_superadmin_uid();
		
		$this->admin_info = $this->model('admin_account')->get_admin_info_by_uid($this->admin_uid);
		
		$this->_INPUT = &$GLOBALS['_INPUT'];
		
		$this->crumb(get_setting('site_name') . " 管理后台", get_setting('base_url') . DF_ADMIN_DIR);
		
		//组权限检测
		if ($this->admin_info['group_id'] != "1")
		{
			if (! $this->permission_check())
			{
				H::js_pop_msg("无访问权限！");
			}
		}
		
		$img_url = get_setting('img_url');
		
		$base_url = get_setting('base_url');
		
		! empty($img_url) ? define('G_STATIC_URL', $img_url) : define('G_STATIC_URL', $base_url . '/static');

		TPL::assign('admin_uid', $this->admin_uid);
		TPL::assign('admin_info', $this->admin_info);
		
		$this->setup();
	}

	public function permission_check()
	{
		global $__controller, $__action;
		
		$actions = $this->get_permission_action();
		
		if (! is_array(is_array($actions)))
		{
			return true;
		}
		
		if (! in_array($__action, $actions))
		{
			return true;
		}
		
		$permission = $this->model('admin_group')->get_permission_by_group_id($this->admin_info['group_id']);
		
		if (in_array($__action, $permission[$__controller]))
		{
			return true;
		}
		
		return false;
	}

}