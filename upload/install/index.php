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

define('ROOT_PATH', realpath('../'));

require_once (ROOT_PATH . '/gzphp/init.php');

HTTP::no_cache_header();

if (file_exists(GZ_PATH . 'config/install.lock.php'))
{
	header('Location: ../');
	die;
}

set_time_limit(0);

TPL::assign('page_title', 'Anwsion - 安装程序');

switch ($_POST['step'])
{
	default :
		$system_require = array();
		
		if (version_compare(PHP_VERSION, '5.2.10', '>='))
		{
			$system_require['php'] = TRUE;
		}
		
		if (class_exists('PDO', false))
		{
			$system_require['db'] = 'PDO_MYSQL';
		}
		else if (function_exists('mysqli_close'))
		{
			$system_require['db'] = 'MySQLi';
		}
		
		if (function_exists('session_start'))
		{
			$system_require['session'] = TRUE;
		}
		
		if (function_exists('mb_strlen'))
		{
			$system_require['mb_strlen'] = TRUE;
		}
		
		if (function_exists('iconv'))
		{
			$system_require['iconv'] = TRUE;
		}
		
		if (isset($_COOKIE))
		{
			$system_require['cookie'] = TRUE;
		}
		
		if (function_exists('gd_info'))
		{
			$system_require['image_lib'] = 'GD';
		}
		
		if ($system_require['image_lib'] AND class_exists('Imagick', false))
		{
			$system_require['image_lib'] = 'ImageMagick';
		}
		
		if (function_exists('curl_init'))
		{
			$system_require['curl'] = TRUE;
		}
		
		// 检测 /gzphp 是否有写权限
		if (is_really_writable(GZ_PATH))
		{
			$system_require['config_writable_core'] = TRUE;
		}
		
		// 检测 /gzphp/config/ 是否有写权限
		if (is_really_writable(GZ_PATH . 'config/'))
		{
			$system_require['config_writable_config'] = TRUE;
		}
		
		$base_dir = str_replace("\\", "",dirname(dirname($_SERVER['PHP_SELF'])));
		
		TPL::assign('system_require', $system_require);
		TPL::assign('base_dir', $base_dir);
		TPL::output('install/index');
		break;
	
	case 2 :
		$data_dir = array(
			'tmp', 
			'cache', 
			'uploads'
		);
		
		foreach ($data_dir as $key => $dir_name)
		{
			if (! is_dir(ROOT_PATH . '/' . $dir_name))
			{
				if (! @mkdir(ROOT_PATH . '/' . $dir_name))
				{
					$error_messages[] = '目录: ' . ROOT_PATH . '/' . $dir_name . ' 无法创建，请将网站根目录权限设置为 777, 或者创建这个目录设置权限为 777。';
				}
			}
		}
		
		if (! is_really_writable(GZ_PATH))
		{
			$error_messages[] = '目录: ' . GZ_PATH . ' 无法写入，请将此目录权限设置为 777。';
		}
		
		if (class_exists('PDO', false))
		{
			TPL::assign('pdo_support', TRUE);
		}
		
		TPL::assign('error_messages', $error_messages);
		TPL::output('install/settings');
		break;
	
	case 3 :
		$db_config = array(
			'host' => $_POST['db_host'], 
			'username' => $_POST['db_username'], 
			'password' => $_POST['db_password'], 
			'dbname' => $_POST['db_dbname']
		);
		
		if ($_POST['db_driver'])
		{
			$db_driver = $_POST['db_driver'];
		}
		else if (class_exists('PDO', false))
		{
			$db_driver = 'PDO_MYSQL';
		}
		else
		{
			$db_driver = 'MySQLi';
		}
		
		try
		{
			$db = Zend_Db::factory($db_driver, $db_config);
		}
		catch (Exception $e)
		{
			H::js_pop_msg('数据库连接失败, 错误信息: ' . addslashes(strip_tags($e->getMessage())), './');
		}
		
		try
		{
			$db->query("SET NAMES utf8");
			//连接测试成功，将数据库设置写入配置文件
		}
		catch (Exception $e)
		{
			H::js_pop_msg('数据库连接失败, 错误信息: ' . addslashes(strip_tags($e->getMessage())), './');
		}
		
		$config = array(
			'charset' => 'utf8', 
			'prefix' => $_POST['db_prefix'], 
			'driver' => $db_driver, 
			'master' => $db_config, 
			'slave' => false
		);
		
		$config_class = load_class('core_config');
		$config_class->set('database', $config);
		
		// 创建数据表
		$db_table_querys = explode(';', str_replace('[#DB_PREFIX#]', $_POST['db_prefix'], file_get_contents(ROOT_PATH . '/install/db/mysql.sql')));
		
		foreach ($db_table_querys as $_sql)
		{
			if ($query_string = trim(str_replace(array(
				"\r", 
				"\n", 
				"\t"
			), '', $_sql)))
			{
				$db->query($query_string);
			}
		}
		
		TPL::output('install/final');
		break;
	
	case 4 :		
		$db = load_class('core_db')->setObject('master');
		$db_prefix = load_class('core_config')->get('database')->prefix;
		
		$salt = fetch_salt(4);
		
		$data = array(
			'user_name' => $_POST['user_name'], 
			'password' => compile_password($_POST['password'], $salt), 
			'email' => $_POST['email'], 
			'salt' => $salt,
			'group_id' => 1,
			'valid_email' => 1,
			'is_first_login' => 0,
		);
		
		$db->insert($db_prefix . 'users', $data);
		$db->insert($db_prefix . 'users_attrib', array('uid' => 1));
		
		//加载网站配置
		$base_dir = dirname(dirname($_SERVER['PHP_SELF']));
		$base_dir = ($base_dir == DIRECTORY_SEPARATOR) ? "" : $base_dir;
		
		$insert_query = file_get_contents(ROOT_PATH . '/install/db/system_setting.sql');
		$insert_query = str_replace('[#DB_PREFIX#]', $db_prefix, $insert_query);
		$insert_query = str_replace('[#BASE_URL#]', serialize('http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $base_dir), $insert_query);
		$insert_query = str_replace('[#UPLOAD_URL#]', serialize($base_dir . "/uploads"), $insert_query);
		$insert_query = str_replace('[#UPLOAD_DIR#]', serialize(str_replace("\\", "/", ROOT_PATH) . "/uploads"), $insert_query);
		$insert_query = str_replace('[#FROM_EMAIL#]', serialize($_POST['email']), $insert_query);
		
		$db->query($insert_query);
		
		//生成 setting 文件
		$setting_data = $db->fetchAll($db->select()->from($db_prefix . 'system_setting'));
		
		foreach ($setting_data as $key => $val)
		{
			$setting[$val['varname']] = unserialize($val['value']);
		}
		
		load_class('core_config')->set('setting', $setting);
		
		//生成 gz_config.inc.php
		$gz_config = file_get_contents(ROOT_PATH . '/gzphp/gz_config.dist.php');
		$gz_config = str_replace('{G_COOKIE_PREFIX}', fetch_salt(3) . '_', $gz_config);
		$gz_config = str_replace('{G_SECUKEY}', fetch_salt(12), $gz_config);
		$gz_config = str_replace('{G_COOKIE_HASH_KEY}', fetch_salt(15), $gz_config);
		
		file_put_contents(ROOT_PATH . '/gzphp/gz_config.inc.php', $gz_config);
		
		file_put_contents(GZ_PATH . '/config/install.lock.php', time());
		
		TPL::output('install/success');
		break;
}
