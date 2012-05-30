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

define('START_TIME', microtime(TRUE));

/** 设定时间  **/
date_default_timezone_set('Etc/GMT-8');

/** 错误级别 **/
error_reporting(E_ALL ^ E_NOTICE);


if ($_GET['error_level'])
{
	error_reporting($_GET['error_level']);
}

/** 浏览器标识 **/
@ini_set('user_agent', 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727; CIBA)');

/** 核心框架路径 **/
if (! defined('GZ_PATH'))
{
	define('GZ_PATH', dirname(__FILE__) . '/');
}

/** 变量处理 */
if (get_magic_quotes_gpc()) // GPC 进行反向处理
{
	if (! function_exists('stripslashes_gpc'))
	{

		function stripslashes_gpc(&$value)
		{
			$value = stripslashes($value);
		}
		
		array_walk_recursive($_GET, 'stripslashes_gpc');
		array_walk_recursive($_POST, 'stripslashes_gpc');
		array_walk_recursive($_COOKIE, 'stripslashes_gpc');
		array_walk_recursive($_REQUEST, 'stripslashes_gpc');
	}
}

if (@ini_get('register_globals'))
{
	foreach ($_REQUEST AS $name => $value)
	{
		unset($$name);
	}
}

require_once(GZ_PATH . '../version.php');
require_once(GZ_PATH . 'gz_funs.inc.php');

load_class('core_autoload');