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

class core_autoload
{
	private static $_gz_class = array(
		'TPL'				=> 'class/cls_template.inc.php',	
		'ZCACHE'	   		=> 'class/cls_zcache.inc.php',
		'FORMAT'			=> 'class/cls_format.inc.php',
		'HTTP'				=> 'class/cls_http.inc.php',
		'image_class'		=> 'class/cls_image.inc.php',
	    'upload_class'		=> 'class/cls_upload.inc.php',
	    'pagination_class'	=> 'class/cls_pagination.inc.php',
		'validate_class'	=> 'class/cls_validate.inc.php',
		'H'					=> 'class/cls_helper.inc.php',
		'USER'				=> 'class/cls_user.inc.php',
		'Diff'				=> 'class/diff/diffClass.php',
		'ACTION_LOG'		=> 'class/action_log_class.inc.php',
		'ADMIN_CONTROLLER'	=> 'class/admin_controller.inc.php',
		'FineDiff'			=> 'class/diff/finediff.php',
	);
	
	public function __construct()
	{
		set_include_path(GZ_PATH);
		
		foreach (self::$_gz_class AS $key => $val)
		{
			self::$_gz_class[$key] = GZ_PATH . $val;
		}
		
		spl_autoload_register(array($this, 'loader'));
	}
    
    private static function loader($class_name)
	{
		$file = GZ_PATH . preg_replace('#_+#', '/', $class_name) . '.php';
		
		if (file_exists($file))
		{
			return require_once $file;
		}
		
		$model_lib_cache = GZ_PATH . '../tmp/models_lib.php';
		
		if (file_exists($model_lib_cache))
		{
			$_gz_class_extra = unserialize(file_get_contents($model_lib_cache));
		}
		
		if (is_array($_gz_class_extra))
		{
			self::$_gz_class = array_merge(self::$_gz_class, $_gz_class_extra);
		}
		
		// 如果内置有显示就读内置的
		if (isset(self::$_gz_class[$class_name]))
		{
			if (file_exists(GZ_PATH . self::$_gz_class[$class_name]))
			{
				require_once(GZ_PATH . self::$_gz_class[$class_name]);
			}
			else
			{
				require_once(self::$_gz_class[$class_name]);
			}
		}
		// 查找 includes
		else if (file_exists(GZ_PATH . 'class/' . $class_name . '.inc.php'))
		{
			require_once(GZ_PATH . 'class/' . $class_name . '.inc.php');
						
			return class_exists($class_name, false);
		}
		// 查找各 model 目录
		else
		{
			$model_lib = array();
			
			$root_path = realpath(GZ_PATH . '../') . '/';
			
			$dir_handle = opendir($root_path);
	    
	    	while (($file = readdir($dir_handle)) !== false)
	    	{
	        	if ($file != '.' AND $file != '..')
	        	{
	            	$location = $root_path . $file . '/model/';
	            	
	            	if (is_dir($location))
	            	{
	            		$model_dir_handle = opendir($location);
	    
	    				while (($model_file = readdir($model_dir_handle)) !== false)
	    				{
	    					if ($model_file != '.' AND $model_file != '..' AND strstr($model_file, '_class.inc.php'))
	        				{
	        					$model_lib[str_replace('.inc.php', '', $model_file)] = $root_path . $file . '/model/' . $model_file;
	        				}
	    				}
	    				
	    				closedir($model_dir_handle);
	            	}
	        	}
	    	}
	    	
	    	closedir($dir_handle);
	    	
	    	file_put_contents($model_lib_cache, serialize($model_lib));
	    	
	    	if (isset($model_lib[$class_name]))
			{
				require_once($model_lib[$class_name]);
			}
			else
			{
				die('Class ' . $class_name . ' Not found.');
			}
		}
		
		return true;
	}
}