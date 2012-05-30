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

// 定义 Cookies 作用域
define('G_COOKIE_DOMAIN','');

// 定义 Cookies 前缀
define('G_COOKIE_PREFIX','{G_COOKIE_PREFIX}');

// 定义应用加密 KEY
define('G_SECUKEY','{G_SECUKEY}');
define('G_COOKIE_HASH_KEY', '{G_COOKIE_HASH_KEY}');

// 默认控制器的 URL 参数名称
define('GZ_URL_CONTROLLER', 'c');

// 默认动作的 URL 参数名称
define('GZ_URL_ACTION', 'act');

// 默认控制器
define('GZ_DEFAULT_CONTROLLER', 'main');

// 默认动作
define('GZ_DEFAULT_ACTION', 'index');

// 全局变量名
define('GZ_GLOBAL_NAME', '_GZ');	

// 全局配置变量名
define('GZ_CFG_GLOBAL_NAME', '_GZ_CFG');

// 开启 Session
define('IS_SESSION_START', TRUE); 

define('X_UA_COMPATIBLE', 'IE=edge');
define('G_STATIC_VERSION', '20120515');