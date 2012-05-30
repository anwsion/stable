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

class core_config
{
	
	private $config = array();
	private $base_url;

	function __construct()
	{
	
	}
	
	/*function base_url()
	{
		if (!$this->base_url)
		{
			if (isset($_SERVER['HTTP_HOST']))
			{
				$this->base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
				
				$this->base_url .= '://'. $_SERVER['HTTP_HOST'];
				
				if (strstr($_SERVER['REQUEST_URI'], '/index.php?/'))
				{
					$this->base_url .= '/index.php?/';
				}
				else
				{
					$this->base_url .= str_replace(Kernel::uri()->path_info(), '/', $_SERVER['REQUEST_URI']);
				}
			}
		}
		
		return $this->base_url;
	}*/
	
	function get($config_id)
	{
		if (isset($this->config[$config_id]))
		{
			return $this->config[$config_id];
		}
		else
		{
			return $this->load_config($config_id);
		}
	}

	function load_config($config_id)
	{
		if (! file_exists(GZ_PATH . 'config/' . $config_id . '.php'))
		{
			show_error('The configuration file config/' . $config_id . '.php does not exist.');
		}
		else
		{
			include (GZ_PATH . 'config/' . $config_id . '.php');
			
			if (! is_array($config))
			{
				show_error('Your config/' . $config_id . '.php file does not appear to contain a valid configuration array.');
			}
			
			$this->config[$config_id] = (object)$config;
			
			return $this->config[$config_id];
		}
	}

	/**	 * 写入配置文件
	 * @param unknown_type $config_id
	 * @param unknown_type $data
	 * @return unknown
	 */
	public function set($config_id, $data)
	{
		if (empty($data) || (! is_array($data)))
		{
			show_error('is not array.');
		}
		
		$content = "<?php\n\n";
		
		foreach($data as $key => $val)
		{
			if(is_array($val))
			{
				$content .= "\$config['{$key}'] = " . var_export($val, true) . ";";;
			}
			else if(is_bool($val))
			{
				$content .= "\$config['{$key}'] = " . ($val ? 'true' : 'false') . ";";
			}
			else 
			{
				$content .= "\$config['{$key}'] = '" . $val . "';";
			}
			
			$content .= "\r\n";
		}
		
		$content .= "\n?>";
		
		$config_path = GZ_PATH . 'config/' . $config_id . '.php';
		
		$fp = @fopen($config_path, "w");
		
		@chmod($fpath, 0777);
		
		$fwlen = @fwrite($fp, $content);
		
		@fclose($fp);
		
		return $fwlen;
	}
}
