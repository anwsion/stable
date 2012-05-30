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

class c_captcha_class extends GZ_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action["rule_type"] = 'black'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["actions"] = array();
		
		return $rule_action;
	}
	
	function setup()
	{
		HTTP::no_cache_header();
	}
	
	public function index()
	{
		$core_captcha = load_class('core_captcha');
		
		$core_captcha->setCode(array(
			'characters' => 'A-Z,2-9', 
			'length' => 4, 
			'deflect' => true, 
			'multicolor' => false
		));
		
		$core_captcha->setMolestation(array(
			'type' => 'both', 
			'density' => 'fewness'
		));
		
		$core_captcha->setImage(array(
			'type' => 'png', 
			'width' => 120, 
			'height' => 40
		));
		
		$config_font = array(
			'space' => 5,
			'size' => 28,
			'left' => rand(5, 15), 
			'top' => rand(25, 35), 
		);
		
		$dir_handle = opendir(GZ_PATH . 'core/fonts/');
		
		while (($file = readdir($dir_handle)) !== false)
		{
		    if ($file != '.' AND $file != '..')
		    {
		    	if (strstr(strtolower($file), '.ttf'))
		    	{
		    		$config_font['file'][] = GZ_PATH . 'core/fonts/' . $file;
		    	}
		   	}
		 }
		 
		closedir($dir_handle);
		
		$core_captcha->setFont($config_font);
		
		$core_captcha->setBgColor(array(
			'r' => rand(230, 255), 
			'g' => rand(230, 255), 
			'b' => rand(230, 255)
		));
		
		$core_captcha->paint();
	}
}