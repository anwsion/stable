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

class ZCACHE
{
	public static $cache_open = false;
	private static $cache;
	private static $frontendName = 'Core';
	//private static $backendName = 'Memcached';
	private static $backendName = 'File';
	
	private static $frontendOptions = array(
		'lifeTime' => 3600, 
		'automatic_serialization' => true
	);
	
	private static $backendOptions = array(
		//'cache_dir' => './_cache_dir'
		/*'servers' => array(
			array(
				'host' => '127.0.0.1', 
				'port' => 41111, 
				'persistent' => true
			)
		)*/
	);
	
	private static $groupPrefix = 'cachegroup_';
	private static $cachePrefix = 'idunion_';
	private static $connected = false;

	/**
	 * setup
	 * @param  $config
	 */
	public function setup($config = array())
	{
		if (sizeof($config) > 0)
		{
			foreach ($config as $key => $value)
			{
				self::$$key = $value;
			}
		}
	}

	/**
	 * connect
	 */
	public static function connect()
	{
		//系统缓存开关
		if (! self::cache_open_check())
		{
			return false;
		}
		
		if (! self::$connected)
		{
			self::$cache = Zend_Cache::factory(self::$frontendName, self::$backendName, self::$frontendOptions, array(
				'cache_dir' => realpath(GZ_PATH . '../cache/')
			));
		}
		
		self::$connected = true;
		
		return true;
	}

	public static function connect_status()
	{
		if (! self::connect())
		{
			return false;
		}
		
		return self::$cache;
	}

	/**
	 * SET 
	 * @param  $key
	 * @param  $value
	 * @param  $group
	 * @param  $lifetime
	 * @return boolean
	 */
	public static function set($key, $value, $group, $lifetime)
	{
		
		if (GZ_APP::config()->get('system')->debug)
		{
			list($usec, $sec) = explode(" ", microtime());
			$start_time = (float)$usec + (float)$sec;
		}
		
		if (! self::connect())
		{
			return false;
		}
		
		if (! $key)
			return false;
		
		$result = self::$cache->save($value, self::$cachePrefix . $key, array(), $lifetime);
		
		if ($group)
		{
			if (is_array($group))
			{
				if (count($group) > 0)
					foreach ($group as $cg)
					{
						self::setGroup($cg, $key);
					}
			}
			else
			{
				self::setGroup($group, $key);
			}
		}
		
		if (GZ_APP::config()->get('system')->debug)
		{
			list($usec, $sec) = explode(" ", microtime());
			$end_time = (float)$usec + (float)$sec;
			$stime = sprintf("%06f", $end_time - $start_time);
			
			GZ_APP::debug_log('cache', $stime, 'Save Cache: ' . self::$cachePrefix . $key);
		}
		return $result;
	}

	/**
	 * GET
	 * @param  $key
	 */
	public static function get($key)
	{
		if (GZ_APP::config()->get('system')->debug)
		{
			list($usec, $sec) = explode(" ", microtime());
			$start_time = (float)$usec + (float)$sec;
		}
		
		if (! self::connect())
		{
			return false;
		}
		
		if (! $key)
		{
			return false;
		}
		
		$key = self::$cachePrefix . $key;
		
		$result = self::$cache->load($key);
		
		if (GZ_APP::config()->get('system')->debug)
		{
			list($usec, $sec) = explode(" ", microtime());
			$end_time = (float)$usec + (float)$sec;
			$stime = sprintf("%06f", $end_time - $start_time);
			
			GZ_APP::debug_log('cache', $stime, 'Get Cache: ' . self::$cachePrefix . $key . ', result type: ' .gettype($result));
		}

		return $result;
	}

	/**
	 * SET_GROUP
	 * @param  $group_name
	 * @param  $key
	 */
	public static function setGroup($group_name, $key)
	{
		$groupData = self::get(self::$groupPrefix . $group_name);
		
		if (is_array($groupData) && in_array($key, $groupData))
		{
			return false;
		}
		
		$groupData[] = $key;
		
		self::set(self::$groupPrefix . $group_name, $groupData, null, self::$frontendOptions['lifeTime']);
	}

	/**
	 * GET GROUP
	 * @param  $group_name
	 */
	public static function getGroup($group_name)
	{
		return self::get(self::$groupPrefix . $group_name);
	}

	/**
	 * CLEAN GROUP
	 * @param  $group_name
	 */
	public static function cleanGroup($group_name)
	{
		$groupData = self::get(self::$groupPrefix . $group_name);
		
		if ($groupData && is_array($groupData))
		{
			foreach ($groupData as $item)
			{
				self::delete($item);
			}
		}
		
		self::delete(self::$groupPrefix . $group_name);
	}

	/**
	 * DELETE
	 * @param  $key
	 */
	public static function delete($key)
	{
		if (! self::connect())
		{
			return false;
		}
		
		$key = self::$cachePrefix . $key;
		self::$cache->remove($key);
	}

	/**
	 * CLEAN
	 */
	public static function clean()
	{
		if (! self::connect())
		{
			return false;
		}
		
		self::$cache->clean(Zend_Cache::CLEANING_MODE_ALL);
	}

	/**
	 * START
	 * @param  $key
	 */
	public static function start($key)
	{
		if (! self::connect())
		{
			return false;
		}
		
		$key = self::$cachePrefix . $key;
		self::$cache->start($key);
	}

	/**
	 * END
	 */
	public static function end()
	{
		if (! self::connect())
		{
			return false;
		}
		
		self::$cache->end();
	}

	/**
	 * 格式化cache  的key
	 * @param unknown_type $string
	 */
	public static function format_key($string)
	{
		$regex = '/[^a-zA-Z0-9_]/u';
		$result = preg_replace($regex, "_", $string);
		
		return $result;
	}

	function cache_open_check()
	{
		if (get_setting('cache_open') == 'Y')
		{
			return true;
		}
		else
		{
			if (self::$cache_open)
			{
				return true;
			}
			
			return false;
		}
	}
}


