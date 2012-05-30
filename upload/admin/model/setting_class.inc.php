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

class setting_class extends GZ_MODEL
{
	
	var $_config_setting_file = "../gzphp/config/setting.php";

	/**
     * 构造函数
     */
	
	function get_vars($groupid = 0, $with_select = false)
	{
		$groupid = intval($groupid);
		
		$where = "status=0";
		
		if ($groupid > 0)
		{
			$where .= " AND groupid = " . $groupid;
		}
		
		$vars = $this->fetch_all('system_setting', $where, 'groupid ASC,sort ASC,id ASC');
		
		$r_vars = array();
		
		foreach ($vars as $key => $val)
		{
			$r_vars[$val['varname']] = $val;
			
			$r_vars[$val['varname']]['value'] = unserialize($val['value']);
			
			if ($with_select && ($val['type'] == 'select'))
			{
				$r_vars[$val['varname']]['select_list'] = $this->get_select_config($val['varname']);
			}
		}
		
		return $r_vars;
	}

	public function get_select_config($varname)
	{
		$config_path = './config/select_' . $varname . '.php';
		
		if (! file_exists($config_path))
		{
			return false;
		}
		
		@include $config_path;
		
		return $config;
	}

	/**
	 * 根据变量名获得设置记录
	 * Enter description here ...
	 * @param unknown_type $key
	 */
	function get_vars_by_name($key)
	{
		if (empty($key))
		{
			return false;
		}
		
		$vars = $this->fetch_row('system_setting', "varname='" . $key . "'", 'id ASC');
		
		if (! is_array($vars))
		{
			return false;
		}
		
		return $vars['value'];
	}

	/**
	 * 检查过滤系统识别的参数
	 * Enter description here ...
	 * @param unknown_type $input
	 */
	function check_vars($input)
	{
		if (empty($input))
		{
			return false;
		}
		
		$data = $this->fetch_all('system_setting', "varname IN ('" . implode("','", array_keys($input)) . "')");
		
		if (empty($data))
		{
			return false;
		}
		
		$validted_vars = array();
		
		foreach ($data as $val)
		{
			$validted_vars[] = $val['varname'];
		}
		
		foreach ($input as $key => $val)
		{
			if (in_array($key, $validted_vars))
			{
				$r_vars[$key] = $val;
			}
		}
		
		return $r_vars;
	}

	/**
	 * 保存设置参数
	 * Enter description here ...
	 * @param unknown_type $vars
	 */
	function set_vars($vars)
	{
		if (empty($vars))
		{
			return false;
		}
		
		foreach ($vars as $key => $val)
		{
			$val = FORMAT::safe($val);
			
			$update_arr['value'] = serialize($val);
			
			$this->update('system_setting', $update_arr, "varname='" . $key . "'");
		}
		
		return true;
	}

	/**
	 * 获得所有设置
	 * Enter description here ...
	 */
	public function get_setting()
	{
		$vars = $this->get_vars();
		
		if (empty($vars) && ! is_array($vars))
		{
			return false;
		}
		
		foreach ($vars as $key => $val)
		{
			$var_arr[$val['varname']] = $val['value'];
		}
		
		return $var_arr;
	}

	/**
	 * 将系统设置保存到缓存文件
	 * Enter description here ...
	 */
	function save_setting_config()
	{
		$vars = $this->get_setting();
		
		if (empty($vars) && ! is_array($vars))
		{
			return false;
		}
		
		$export_str = "<?php\r\n\r\n";
		
		foreach ($vars as $key => $val)
		{
			$export_str .= "\$config['" . $key . "'] = '" . addslashes($val) . "';\r\n";
		}
		
		$export_str .= "\r\n?>";
		
		return @file_put_contents($this->_config_setting_file, $export_str);
	}

	public function get_ui_styles()
	{
		$exclude_dir = array('.', '..', '.svn');
		
		$dirs = array();
		
		if ($handle = opendir('../views'))
		{
			while (false !== ($file = readdir($handle)))
			{
				if(!in_array($file, $exclude_dir) && is_dir('../views/' . $file))
				{
					$dirs[] = $file;
				}
			}
			
			closedir($handle);
		}
		
		$ui_style = array();
			
		foreach($dirs as $key => $val)
		{
			$ui_style[] = array(
					'id' => $val,
					'title' => $val,
			);
		}
		
		return $ui_style;
	}
	
	public function format_setting_by_group($vars)
	{
		$group_h = array(1);
		
		foreach($vars as $key => $s_var)
		{
			if(!in_array($s_var['groupid'], $group_h))
			{
				$group_h[] = $s_var['groupid'];
				$vars[$key]['group_line'] = true;
			}
		}
		
		return $vars;
	}

}
