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

class GZ_APP
{
	private static $config;
	private static $db;
	
	private static $_controller_path;
	private static $input;
	
	public static $_debug = array();
	
	/**
	 * 程序开始执行,查找控制器和动作
	 * 
	 * @param string $app_dir 应用的绝对目录如果没有,就是当前目录
	*/
	public static function run($app_dir = './')
	{
		// 全局变量
		global $__controller, $__action, $__default_controller, $__default_action;
		
		self::init();
		
		self::$input = &$GLOBALS['_INPUT'];
		
		// 控制器操作进行选择
		if (! $__controller)
		{
			if (isset(self::$input[GZ_URL_CONTROLLER]))
			{
				$__controller = self::$input[GZ_URL_CONTROLLER]; //有传入控制器字段名
			}
			else
			{
				$__controller = $__default_controller ? $__default_controller : GZ_DEFAULT_CONTROLLER; //读取默认控制器字段名
			}
		}		

		$GLOBALS[GZ_GLOBAL_NAME]['router']['controller'] = $__controller;
		
		if (! $__action)
		{
					
			// 动作		
			if (isset(self::$input[GZ_URL_ACTION]))
			{
				$__action = self::$input[GZ_URL_ACTION]; // 有传入动作字段名
			}
			else
			{
				$__action = $__default_action ? $__default_action : GZ_DEFAULT_ACTION; // 读取默认动作字段名
			}
		}
		$GLOBALS[GZ_GLOBAL_NAME]['router']['action'] = $__action;

		// 传入应用目录,返回控制器路径
		$handle_controller = self::create_controller($__controller, $app_dir);
		
		// 判断
		if (! is_object($handle_controller))
		{
			show_error('Can\'t not find controller: ' . $__controller);
		}
		else
		{
			if (! method_exists($handle_controller, $__action))
			{
				if (! method_exists($handle_controller, $__action . '_action'))
				{
					show_error('Can\'t not find action: ' . $__action);
				}
				else
				{
					$__action = $__action . '_action';
				}
			}
		}
		
		// 判断 ACTION
		if (method_exists($handle_controller, 'get_access_rule'))
		{
			$access_rule = $handle_controller->get_access_rule();
		}
		
		if (!self::$input[GZ_URL_ACTION])
		{
			self::$input[GZ_URL_ACTION] = GZ_DEFAULT_ACTION;
		}
		
		// 判断使用白名单还是黑名单,默认使用黑名单
		if ($access_rule)
		{
			// 黑名单,黑名单中的检查  'white'白名单,白名单以外的检查(默认是黑名单检查)
			if (isset($access_rule["rule_type"]) && ($access_rule['rule_type'] == 'white')) // 白
			{
				if ((! $access_rule["actions"]) || (! in_array(str_replace('_action', '', self::$input[GZ_URL_ACTION]), $access_rule["actions"])))
				{
					self::login_check();
				}
			}
			else if (isset($access_rule["actions"]) && in_array(str_replace('_action', '', self::$input[GZ_URL_ACTION]), $access_rule['actions'])) // 非白就是黑名单
			{
				self::login_check();
			}
		
		}
		else //没有设置就全部检查
		{
			self::login_check();
		}
		
		// 执行
		$handle_controller->$__action();
	}

	private function init()
	{
		self::$config = & load_class('core_config');
		self::$db = & load_class('core_db');
		
		$cornd_db_file = GZ_PATH . '../tmp/cornd_db.php';
		
		if (file_exists($cornd_db_file))
		{
			$cornd_db = unserialize(file_get_contents($cornd_db_file));
		}
		
		if (GZ_APP::config()->get('system')->debug AND is_array($cornd_db))
		{
			foreach ($cornd_db AS $cornd_tag => $run_time)
			{
				GZ_APP::debug_log('crond', 0, 'Tag: ' . $cornd_tag . ', Last run time: ' . date('Y-m-d H:i:s', $run_time));
			}
		}
	}

	/**
	 * 建立控制器
	 * 返回控制器类对象
	 * @param string $route
	 */
	public static function create_controller($route, $app_dir)
	{
		$app_dir = trim($app_dir);
		
		if (! $app_dir)
		{
			return false; // 没有应用目录,返回错误
		}
		
		if (($route = trim($route, '/')) === '')
		{
			$route = GZ_DEFAULT_CONTROLLER;
		}
		
		self::$_controller_path = self::get_controller_path($app_dir);
		
		$class_name = 'c_' . $route . '_class';
		
		$class_file = self::$_controller_path . $class_name . '.inc.php';
		
		// 存在否
		if (is_file($class_file))
		{
			if (! class_exists($class_name, false))
			{
				require_once ($class_file);
			}
			
			//存在了就返回对象
			if (class_exists($class_name, false))
			{
				return new $class_name();
			}
			
			// 全没有变返空
			return null;
		}
	}

	/**
	 * 获取控制器路径
	 * 
	 */
	public static function get_controller_path($app_dir = '')
	{
		$app_dir = trim($app_dir);
		
		if ($app_dir == '')
		{
			return self::$_controller_path; // 没有参数或为空,返回错误APP控制器的目录;
		}
		
		if (self::$_controller_path != '')
		{
			return self::$_controller_path;
		}
		else
		{
			self::$_controller_path = $app_dir . 'controller/';
			
			return self::$_controller_path;
		}
	
	}

	/**
	 * V($vRoute,$def_v=NULL);
	 * APP:V($vRoute,$def_v=NULL);
	 * 获取还原后的  $_GET ，$_POST , $_FILES $_COOKIE $_REQUEST $_SERVER $_ENV
	 * 同名全局函数： V($vRoute,$def_v=NULL);
	 * @param $vRoute	变量路由，规则为：“<第一个字母>[：变量索引/[变量索引]]
	 * 					例:	V('G:TEST/BB'); 表示获取 $_GET['TEST']['BB']
	 * 						V('p'); 		表示获取 $_POST
	 * 						V('c:var_name');表示获取 $_COOKIE['var_name']
	 * @param $def_v
	 * @return unknown_type
	 */
	public static function V($vRoute, $def_v = NULL, $setVar = false)
	{
		static $v;
		
		$vRoute = strtolower($vRoute); //变成小写
		
		if (empty($v))
		{
			$v = array();
		}
		
		$vRoute = trim($vRoute); //接收的
		
		//强制初始化值
		if ($setVar)
		{
			$v[$vRoute] = $def_v;
			
			return true;
		}
		
		if (! isset($v[$vRoute]))
		{
			$vKey = array(
				'C' => $_COOKIE, 
				'G' => $_GET, 
				'P' => $_POST, 
				'R' => $_REQUEST, 
				'F' => $_FILES, 
				'S' => $_SERVER, 
				'E' => $_ENV, 
				'-' => $GLOBALS[GZ_GLOBAL_NAME]
			);
			
			if (empty($vKey['R']))
			{
				$vKey['R'] = array_merge($_COOKIE, $_GET, $_POST);
			}
			
			if (! preg_match("#^([cgprfse-])(?::(.+))?\$#sim", $vRoute, $m) || ! isset($vKey[strtoupper($m[1])]))
			{
				trigger_error("Can't parse var from vRoute: $vRoute ", E_USER_ERROR);
				
				return NULL;
			}
			
			//----------------------------------------------------------
			$m[1] = strtoupper($m[1]);
			$tv = $vKey[$m[1]];
			
			//----------------------------------------------------------
			if (empty($m[2]))
			{
				$v[$vRoute] = ($m[1] == '-' || $m[1] == 'F' || $m[1] == 'S' || $m[1] == 'E') ? $tv : GZ_APP::_magic_var($tv);
			}
			else if (empty($tv))
			{
				return $def_v;
			}
			else
			{
				$vr = explode('/', $m[2]);
				
				while (count($vr) > 0)
				{
					$vk = array_shift($vr);
					
					if (! isset($tv[$vk]))
					{
						return $def_v;
						break;
					}
					
					$tv = $tv[$vk];
				}
			}
			
			$v[$vRoute] = ($m[1] == '-' || $m[1] == 'F' || $m[1] == 'S' || $m[1] == 'E') ? $tv : GZ_APP::_magic_var($tv);
		}
		
		return $v[$vRoute];
	}

	/**
	 * 根据用户服务器环境配置，递归还原变量
	 * @param  $mixed 			变量
	 * @param  $check_quotes_gpc 是否检查转义
	 * @return 还原后的值
	 */
	public static function _magic_var($mixed, $check_magic_quotes_gpc = FALSE)
	{
		if ($check_magic_quotes_gpc) // 默认不进行替换,以免跟以前的变量选成2次替换冲突
		{
			if ((function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) || @ini_get('magic_quotes_sybase'))
			{
				if (is_array($mixed))
				{
					return array_map(array(
						'GZ_APP', 
						'_magic_var'
					), $mixed, true);
				}
				
				return stripslashes($mixed);
			}
			else
			{
				return $mixed;
			}
		}
		else
		{
			return $mixed;
		}
	}
	
	/**
   	* 格式化弹出信息组件返回值
   	* 
   	* @param $rsm 		结果数据,可以为空
   	* @param $errno		错误代码，默认为 0 无错误，其它值为相应的错误代码
   	* @param $err		错误信息
   	* @param $level 	错误级别，默认为 0 ， $err 将直接显示给用户看，如果为 1 则不显示给用户看，统一为提示为  系统繁忙，请稍后再试...
   	* @param $log		当数据层需要 组件管理中心 写日志时，给出值，默认为空，不写日志
   	* 
   	* @return 返回标准的RSM数据
   	*/
	public static function RSM($rsm, $errno = 0, $err = "", $level = 0, $log = "")
	{
		return array(
			'rsm' => $rsm, 
			'errno' => (int)$errno, 
			'err' => $err, 
			'level' => $level, 
			'log' => $log
		);
	}

	/**
	 * 格式化数据组件返回值
	 * 
	 * @param $rsd		结果数据,不可为空
	 * @param $errno	错误代码，默认为 0 无错误，其它值为相应的错误代码
	 * @param $err		错误信息
	 * @param $level	错误级别，默认为 0 ， $err 将直接显示给用户看，如果为 1 则不显示给用户看，统一为提示为  系统繁忙，请稍后再试...
	 * @param $log		当数据层需要 组件管理中心 写日志时，给出值，默认为空，不写日志
	 * 
	 * @return 返回标准的RSD数据
	 */
	public static function RSD($rsd, $errno = 0, $err = "", $level = 0, $log = "")
	{
		if (! $rsd)
		{
			return false;
		}
		
		return array(
			'rsd' => $rsd, 
			'errno' => (int)$errno, 
			'err' => $err, 
			'level' => $level, 
			'log' => $log
		);
	}

	/**
	 * get_class  类实例化函数实例化并返回对象句柄(不自动载入类定义文件,因为自动载入已经在程序定义好)
	 * 
	 * @param class_name    类名称
	 * @param args   类初始化时使用的参数，数组形式
	 * @param sdir 载入类定义文件的路径，可以是目录+文件名的方式，也可以单独是目录。sdir的值将传入import()进行载入
	 * @param force_inst 是否强制重新实例化对象
	 */
	public static function get_class_obj($class_name, $args = NULL, $sdir = NULL, $force_inst = FALSE)
	{
		// 检查类名称是否正确，以保证类定义文件载入的安全性
		if (preg_match('/[^a-z0-9\-_.]/i', $class_name))
		{
			show_error('Class name: ' . $class_name . ' not valid.');
		}
		
		//处理附带的参数
		$argString = '';
		$comma = '';
		
		if (null != $args)
		{
			for ($i = 0; $i < count($args); $i ++)
			{
				$argString .= $comma . "\$args[$i]";
				$comma = ', ';
			}
		}
			
		// 检查是否该类已经实例化，直接返回已实例对象，避免再次实例化
		if (! $force_inst)
		{
			if (isset($GLOBALS[GZ_GLOBAL_NAME]['obj'][$class_name]))
			{
				return $GLOBALS[GZ_GLOBAL_NAME]['obj'][$class_name];
			}
			else
			{
				$GLOBALS[GZ_GLOBAL_NAME]['obj'][$class_name] = new $class_name($argString);
				return $GLOBALS[GZ_GLOBAL_NAME]['obj'][$class_name];
			}
		}
		else
		{
			$GLOBALS[GZ_GLOBAL_NAME]['obj'][$class_name] = new $class_name($argString);
			return $GLOBALS[GZ_GLOBAL_NAME]['obj'][$class_name];
		}
	}
	
	public static function login_check()
	{
		if (! USER::is_user_login())
		{
			HTTP::redirect(get_setting('base_url') . '/account/?c=login&url=' . urlencode($_SERVER['REQUEST_URI']));
		}
	}

	public static function config()
	{
		return self::$config;
	}

	public static function db($db_object_name = 'master')
	{
		return self::$db->setObject($db_object_name);
	}
	
	public static function debug_log($type, $expend_time, $message)
	{
		self::$_debug[$type][] = array(
			'expend_time' => $expend_time,
			'message' => $message
		);
	}
}