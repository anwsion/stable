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

class c_maintain_class extends ADMIN_CONTROLLER
{

	public function setup()
	{
		$this->crumb("系统维护", "?c=maintain");
	}

	/**
	 * 默认action
	 * Enter description here ...
	 */
	public function index_action()
	{
		
	}

	/**
	 * 清除系统缓存
	 * Enter description here ...
	 */
	public function cache_action()
	{
		$this->crumb("缓存管理", "?c=maintain");
		
		TPL::output('admin/maintain');
	}
	
	public function clean_cache_action()
	{
		ZCACHE::$cache_open = true;
		ZCACHE::clean();
		ZCACHE::$cache_open = false;
		
		H::ajax_json_output(GZ_APP::RSM(null, "1", "成功清除网站全部缓存"));
	}

}