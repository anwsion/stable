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

class c_admin_class extends ADMIN_CONTROLLER
{

	public function setup()
	{
		$this->crumb("管理员设置", "?c=login");
	}

	public function index_action()
	{
		//$this->setting_action();
	}

	public function account_action()
	{
		$vars = $this->model('setting')->get_vars($this->_INPUT['group']);
		
		TPL::assign('vars', $vars);
		
		TPL::assign('act', "sys_save_ajax");
		
		TPL::output("admin/setting_index", true);
	}

	public function setting_v2_action()
	{
		$vars = $this->model('setting')->get_vars($this->_INPUT['group']);
		
		TPL::assign('vars', $vars);
		
		TPL::assign('act', "sys_save_ajax");
		
		TPL::output("admin/setting_index_v2", true);
	}

	/**
	 * 保存设置
	 * Enter description here ...
	 */
	public function sys_save_ajax_action()
	{
		//过滤参数
		$vars = $this->model('setting')->check_vars($this->_INPUT);
		
		//设置参数
		$retval = $this->model('setting')->set_vars($vars);
		
		if ($retval)
		{
			ZCACHE::delete("setting_config");
			
			H::ajax_json_output(GZ_APP::RSM(null, "1", "修改成功"));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "修改失败"));
		}
	
	}

}