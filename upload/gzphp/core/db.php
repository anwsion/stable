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

class core_db
{
	private $db;
	private $current_db_object;
	
	public function __construct()
	{
		$config_class = load_class('core_config');
		
		$config = $config_class->get('database');
		
		/*foreach ($config AS $object_name => $object_set)
		{
			if (is_array($object_set))
			{
				echo $object_name;
			}
		}*/
		
		$this->db['master'] = Zend_Db::factory($config->driver, $config->master);

		try
		{
			$this->db['master']->query("SET NAMES {$config->charset}");
		}
		catch (Exception $e)
		{
			show_error('Can\'t connect master database!');
		}
		
		//$this->current_db_object = 'master';
		
		if ($config->slave)
		{
			$this->db['slave'] = Zend_Db::factory($config->driver, $config->slave);
			
			try
			{
				$this->db['slave']->query("SET NAMES {$config->charset}");
			}
			catch (Exception $e)
			{
				show_error('Can\'t connect slave database!');
			}
		}
		else
		{
			$this->db['slave'] =& $this->db['master'];
		}
		
		//Zend_Db_Table_Abstract::setDefaultAdapter($this->db['master']);
		$this->setObject();
	}
	
	public function setObject($db_object_name = 'master')
	{
		if (isset($this->db[$db_object_name]))
		{
			Zend_Registry::set('dbAdapter', $this->db[$db_object_name]);
			Zend_Db_Table_Abstract::setDefaultAdapter($this->db[$db_object_name]);
			
			$this->current_db_object = $db_object_name;
			
			return $this->db[$db_object_name];
		}
		
		show_error("Can't find this db object: " . $db_object_name);
	}
}