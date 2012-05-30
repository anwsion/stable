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

class TPL
{
	public static $template_ext = '.tpl.htm';
	public static $view;
	public static $output_matchs;
	
	public static $template_path;
	
	function init()
	{
		if (!is_object(self::$view))
		{			
			self::$template_path = realpath(GZ_PATH . '../views/');
			
			self::$view = new Savant3(
				array(
					'template_path' => array(self::$template_path),
					'filters' => array('Savant3_Filter_trimwhitespace', 'filter')
				)
			);
		}
		
		return self::$view;
	}
	
	static function output($template_filename, $display = true)
	{
		self::init();
		
		if (!strstr($template_filename, self::$template_ext))
		{
			$template_filename .= self::$template_ext;
		}
		
		$display_template_filename = 'default/' . $template_filename;
		
		if (is_object(GZ_APP))
		{
			if (get_setting('ui_style') != 'default')
			{
				$custom_template_file = $this->template_path . get_setting('ui_style') . '/' . $template_filename;
				
				if (file_exists($custom_template_file))
				{
					$display_template_filename =  get_setting('ui_style') . '/' . $template_filename;
				}
			}
			
			self::assign('template_name', get_setting('ui_style'));
		}
		else
		{
			self::assign('template_name', 'default');	
		}
		
		if ($display)
		{
			self::$view->display($display_template_filename);
		}
		else
		{
			return self::$view->getOutput($display_template_filename);
		}
	}
	
	static function assign($name, $value)
	{
		self::init();
		
		self::$view->$name = $value;
	}
	
	static function import_css($path)
	{
		self::init();
		
		if (is_array($path))
		{
			foreach ($path AS $key => $val)
			{
				self::$view->_import_css_files[] = str_replace('css/', 'css/' . get_setting('ui_style') . '/', $val);
			}
		}
		else
		{
			self::$view->_import_css_files[] = str_replace('css/', 'css/' . get_setting('ui_style') . '/', $path);
		}
	}
	
	static function import_js($path)
	{
		self::init();
		
		if (is_array($path))
		{
			foreach ($path AS $key => $val)
			{
				self::$view->_import_js_files[] = $val;
			}
		}
		else
		{
			self::$view->_import_js_files[] = $path;
		}
	}
	
	static function result($template_filename)
	{
		self::init();
		
		return self::output($template_filename, false);
	}
	
	static function fetch($template_filename)
	{
		self::init();
		
		if (is_object(GZ_APP))
		{
			if (get_setting('ui_style') != 'default')
			{
				$custom_template_file = $this->template_path . get_setting('ui_style') . '/' . $template_filename . ".tpl.htm";
			
				if (file_exists($custom_template_file))
				{
					return file_get_contents($custom_template_file);
				}
			}
		}
		
		return file_get_contents(self::$template_path . '/default/' . $template_filename . ".tpl.htm");
	}
	
	static function is_output($output_filename, $template_filename)
	{
		if (!isset(self::$output_matchs[md5($template_filename)]))
		{			
			preg_match_all("/TPL::output\(['|\"](.+)['|\"]\)/i", self::fetch($template_filename), $matchs);
			
			self::$output_matchs[md5($template_filename)] = $matchs[1];
		}
		
		if (is_array($output_filename))
		{
			$output_filename = array_unset_null_value($output_filename);
			
			$flag = true;
			
			foreach($output_filename as $key => $val)
			{
				if (!in_array($val, self::$output_matchs[md5($template_filename)]))
				{
					$flag = false;
				}
			}
			
			return $flag;
		}
		
		if (in_array($output_filename, self::$output_matchs[md5($template_filename)]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
