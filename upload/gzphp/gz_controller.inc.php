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

class GZ_CONTROLLER
{
	public $user_id;
	public $user_info;
	public $gz_constructed = false;
	public $_INPUT;

	public function __construct()
	{
		$this->gz_constructed = true;
		
		$this->user_id = USER::get_client_uid();
		
		$this->user_info = $this->data_cache('account_get_users_by_uid_' . $this->user_id, '$this->model("account")->get_users_by_uid($this->user_id, TRUE)', get_setting('cache_level_high'), "g_uid_" . $this->user_id);
		
		$this->user_info['group_id'] = ($_SESSION['superadmin_info']['__SUPERADMIN_UID'] == "1") ? 1 : $this->user_info['group_id'];
		
		$this->_INPUT = &$GLOBALS['_INPUT'];
		
		if ($this->user_info['forbidden'] == 1)
		{
			$this->model('online')->logout();	// 在线列表退出
			$this->model("account")->setcookie_logout();	// 清除 COOKIE
			$this->model("account")->setsession_logout();	// 清除 Session
			
			return false;
		}
		else
		{
			TPL::assign('user_id', (int)$this->user_id);
			TPL::assign('user_info', $this->user_info);
		}
		
		TPL::assign('_global_css', array(
			'css/global.css', 
			'js/date_input/jquery.date_input.css', 
			'js/fancybox/jquery.fancybox.css'
		));
		
		TPL::assign('_global_js', array(
			'js/jquery.js', 
			'js/jquery.form.js', 
			'js/date_input/jquery.date_input.js', 
			'js/autocomplete/jquery.autocomplete.js', 
			'js/fancybox/jquery.fancybox.js', 
			'js/global.js', 
			'js/functions.js', 
			'js/user_info.js'
		));
		
		$img_url = get_setting('img_url');
		
		$base_url = get_setting('base_url');
		
		! empty($img_url) ? define('G_STATIC_URL', $img_url) : define('G_STATIC_URL', $base_url . '/static');
		
		$this->crumb(get_setting('site_name'), get_setting('base_url'));
		
		$this->setup();
	}

	public function setup()
	{
	}

	public function is_post()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			return TRUE;
		}
		
		return FALSE;
	}

	public function model($model)
	{
		return GZ_BASE::model($model);
	}

	/**
	* 魔术函数，实现对控制器扩展类的自动加载
	*/
	public function __call($name, $args)
	{
	}

	public function crumb($name, $url = null)
	{
		$this->_crumb($name, $url);
	}

	public function _crumb($name, $url = null)
	{
		if (is_array($name))
		{
			foreach ($name as $key => $value)
			{
				$this->crumb($key, $value);
			}
			
			return $this;
		}
		
		$crumb_template = $this->crumb;
		
		if (strlen($url) > 1 and substr($url, 0, 1) == '/')
		{
			$url = get_setting('base_url') . substr($url, 1);
		}
		
		$this->crumb[] = array(
			'name' => $name, 
			'url' => $url
		);
		
		$crumb_template['last'] = array(
			'name' => $name, 
			'url' => $url
		);
		
		TPL::assign('crumb', $crumb_template);
		
		foreach ($this->crumb as $key => $crumb)
		{
			$title = $crumb['name'] . ' - ' . $title;
		}
		
		TPL::assign('page_title', rtrim($title, ' - '));
		
		return $this;
	}

	/**
	 * 获取请求字符
	 */
	public function request()
	{
		return $_SERVER["QUERY_STRING"];
	}

	public function data_cache($key, $expression, $life_time, $group_name = null)
	{
		if (! is_string($expression))
		{
			show_error('data_cache(): ' . $key . ' expression is not string val');
		}
		
		$data = ZCACHE::get($key);
		
		if ($data === false)
		{
			$data = eval('return ' . $expression . ';');
			ZCACHE::set($key, $data, $group_name, $life_time);
		}
		
		return $data;
	}

	public function __destruct()
	{
		if (! $this->gz_constructed)
		{
			ob_end_clean();
			
			show_error('GZ_CONTROLLER not constructed or PHP error...');
		}
	}
}