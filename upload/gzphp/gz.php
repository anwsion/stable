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

if (version_compare(PHP_VERSION, '5.2.10', '<'))
{
	die('ERROR: GZPHP require php 5.2.10 or newer');
}

if (! defined('GZ_PATH'))
{
	define('GZ_PATH', dirname(__FILE__) . '/');
}

require_once (GZ_PATH . 'init.php');

/** 载入框架配置 **/
require_once (GZ_PATH . 'gz_config.inc.php');

/** 载入核心类和核心函数. **/
require_once (GZ_PATH . 'gz_base.inc.php');
require_once (GZ_PATH . 'gz_app.inc.php');
require_once (GZ_PATH . 'gz_controller.inc.php');
require_once (GZ_PATH . 'gz_model.inc.php');

/** 载入rewrite类. **/
//require_once (GZ_PATH . 'urlrewrite.inc.php');


/// 初始化全局数据
$GLOBALS['_INPUT'] = array_merge($_GET, $_POST);
$GLOBALS[GZ_GLOBAL_NAME] = array();
$GLOBALS[GZ_GLOBAL_NAME]['TPL'] = array(); //模板
$GLOBALS[GZ_GLOBAL_NAME]['router'] = array(); //路由
$GLOBALS[GZ_GLOBAL_NAME]['obj'] = array(); //对象
$GLOBALS[GZ_GLOBAL_NAME]['import_file'] = array(); //包含文件

// 初始化配置数据
$GLOBALS[GZ_CFG_GLOBAL_NAME] = array();

//判断是否启用SESSION
if (defined('IS_SESSION_START') && IS_SESSION_START)
{
	session_start();
}