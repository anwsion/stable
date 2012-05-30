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

class c_main_class extends GZ_CONTROLLER
{
	function index_action()
	{
		HTTP::redirect('/account/?' . GZ_URL_CONTROLLER . '=setting');
	}
	
	function test_action()
	{
		echo $this->user_id . '<br />';
		
		//print_r($this->model('account')->get_users_by_uids(array($this->user_id)));
		print_r($this->model('account')->get_users_by_uid($this->user_id));
	}
}