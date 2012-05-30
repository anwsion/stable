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

/**
 * 返回浮点型的当前 Unix 时间戳和微秒数
 */
function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

/**
 * 获取变量名
 */

/**
 * 获取一个变量值  GZ_BALSE::V 的同名函数
 * @param $vRoute	变量路由
 * @param $def		默认值
 * @return 			变量值
 */
function V($vRoute, $def = NULL, $setVar = false)
{
	return GZ_APP::V($vRoute, $def, $setVar);
}

/**
 * 格式化组件返回值，数据组件的返回值都要通过此函数格式化后返回
 * @param $rs  	结果数据
 * @param $errno	错误代码，默认为 0 无错误，其它值为相应的错误代码
 * @param $err		错误信息，默认为空，
 * @param $level	错误级别，默认为 0 ， $err 将直接显示给用户看，如果为 1 则不显示给用户看，统一为提示为  系统繁忙，请稍后再试...
 * @param $log		当数据层需要 组件管理中心 写日志时，给出值，默认为空，不写日志
 * $return 返回标准的 RST 结果集
 */
function RS($rs, $errno = 0, $err = '', $level = 0, $log = '')
{
	return array(
		'rs' => $rs, 
		'errno' => $errno * 1, 
		'err' => $err, 
		'level' => $level, 
		'log' => $log
	);
}

/**
 * 打印变量
 *
 * @param fixed $var
 */
if (! function_exists("p"))
{

	function p($var)
	{
		echo "<br><pre>";
		if (empty($var))
		{
			var_dump($var);
		}
		else
		{
			print_r($var);
		}
		
		echo "</pre><br>";
	}
}

/**
 * 打印变量 带 REQUEST["debugx"]==1
 *
 * @param fixed $var
 */
if (! function_exists("pd"))
{

	function pd($var, $exit = 0)
	{
		if ($_REQUEST["debugx"] == 1)
		{
			echo "<br><pre>";
			if (empty($var))
			{
				var_dump($var);
			}
			else
			{
				print_r($var);
			}
			
			echo "</pre><br>";
			if ($exit == 1)
				exit();
		}
	
	}
}

function aasort($array, $args)
{
	if (!$array)
	{
		return false;
	}
	
	$sort_rule = "";
	
	foreach ($args as $arg)
	{
		$order_field = substr($arg, 1, strlen($arg));
		
		foreach ($array as $array_row)
		{
			$sort_array[$order_field][] = $array_row[$order_field];
		}
		
		$sort_rule .= '$sort_array[' . $order_field . '], ' . ($arg[0] == "+" ? SORT_ASC : SORT_DESC) . ',';
	
	}
	
	eval("array_multisort($sort_rule" . ' $array);');
	
	return $array;
}

/**
 * 获取用户IP
 *
 * @return string
 */
function real_ip()
{
	if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown'))
	{
		$onlineip = getenv('REMOTE_ADDR');
	}
	elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown'))
	{
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	elseif (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown'))
	{
		$onlineip = getenv('HTTP_CLIENT_IP');
	}
	elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown'))
	{
		$onlineip = getenv('HTTP_X_FORWARDED_FOR');
	}
	
	return $onlineip;
}

if (! function_exists('gz_addslashes'))
{

	/**
	 * 转义字符串（如果传入数组，则递归转义数组组里面的所有值）
	 *
	 * @param string $string
	 * @param int $force 是否强制转换s
	 * @return string
	 */
	function gz_addslashes($string, $force = 0)
	{
		if (! $GLOBALS['magic_quotes_gpc'] || $force)
		{
			if (is_array($string))
			{
				foreach ($string as $key => $val)
				{
					$string[$key] = gz_addslashes($val, $force);
				}
			}
			else
			{
				$string = addslashes($string);
			}
		}
		return $string;
	}
}

if (! function_exists('cutstr_zhcn'))
{

	/**
	 * 中文截取字符串
	 *
	 * @param string $string
	 * @param int $length
	 * @param string $dot
	 * @param string $charset
	 * @return string
	 */
	function cutstr_zhcn($string, $length, $dot = '...', $charset = 'gbk')
	{
		
		if (strlen($string) <= $length)
		{
			return $string;
		}
		
		$string = str_replace(array(
			'&amp;', 
			'&quot;', 
			'&lt;', 
			'&gt;'
		), array(
			'&', 
			'"', 
			'<', 
			'>'
		), $string);
		
		$strcut = '';
		if (strtolower($charset) == 'utf-8')
		{
			
			$n = $tn = $noc = 0;
			while ($n < strlen($string))
			{
				
				$t = ord($string[$n]);
				if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126))
				{
					$tn = 1;
					$n ++;
					$noc ++;
				}
				elseif (194 <= $t && $t <= 223)
				{
					$tn = 2;
					$n += 2;
					$noc += 2;
				}
				elseif (224 <= $t && $t <= 239)
				{
					$tn = 3;
					$n += 3;
					$noc += 2;
				}
				elseif (240 <= $t && $t <= 247)
				{
					$tn = 4;
					$n += 4;
					$noc += 2;
				}
				elseif (248 <= $t && $t <= 251)
				{
					$tn = 5;
					$n += 5;
					$noc += 2;
				}
				elseif ($t == 252 || $t == 253)
				{
					$tn = 6;
					$n += 6;
					$noc += 2;
				}
				else
				{
					$n ++;
				}
				
				if ($noc >= $length)
				{
					break;
				}
			
			}
			if ($noc > $length)
			{
				$n -= $tn;
			}
			
			$strcut = substr($string, 0, $n);
		
		}
		else
		{
			for ($i = 0; $i < $length; $i ++)
			{
				$strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++ $i] : $string[$i];
			}
		}
		
		$strcut = str_replace(array(
			'&', 
			'"', 
			'<', 
			'>'
		), array(
			'&amp;', 
			'&quot;', 
			'&lt;', 
			'&gt;'
		), $strcut);
		return $strcut . $dot;
	}
}

if (! function_exists('cutmbstr_zhcn'))
{

	/**
	 * 中文截取字符串
	 *
	 * @param string $string
	 * @param int $length
	 * @param string $dot
	 * @param string $charset
	 * @return string
	 */
	function cutmbstr_zhcn($string, $length, $dot = '...', $charset = 'UTF-8')
	{
		
		if (mb_strlen($string, $charset) <= $length)
		{
			return $string;
		}
		
		return mb_substr($string, 0, $length, $charset) . $dot;
	}
}

if (! function_exists('lenmbstr_zhcn'))
{

	/**
	 * 中文截取字符串
	 *
	 * @param string $string
	 * @param int $length
	 * @param string $dot
	 * @param string $charset
	 * @return string
	 */
	function lenmbstr_zhcn($string, $charset = 'UTF-8')
	{
		
		return mb_strlen($string, $charset);
	
	}
}

if (! function_exists('json_encode'))
{

	/**
	   * 模拟PHP5的json_encode函数
	   *
	   * @param unknown_type $a
	   * @return unknown
	   */
	function json_encode($a = false)
	{
		
		// if (is_null($a)) return 'null';
		if (is_null($a))
			return '""'; //为了js不再做判断，改成这样了 
		if ($a === false)
			return 'false';
		if ($a === true)
			return 'true';
		if (is_scalar($a))
		{
			if (is_float($a))
			{
				// Always use "." for floats.   
				return floatval(str_replace(",", ".", strval($a)));
			}
			
			if (is_string($a))
			{
				static $jsonReplaces = array(
					array(
						"\\", 
						"/", 
						"\n", 
						"\t", 
						"\r", 
						"\b", 
						"\f", 
						'"'
					), 
					array(
						'\\\\', 
						'\\/', 
						'\\n', 
						'\\t', 
						'\\r', 
						'\\b', 
						'\\f', 
						'\"'
					)
				);
				
				return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
			}
			else
				return $a;
		}
		$isList = true;
		for ($i = 0, reset($a); $i < count($a); $i ++, next($a))
		{
			if (key($a) !== $i)
			{
				$isList = false;
				break;
			}
		}
		$result = array();
		if ($isList)
		{
			foreach ($a as $v)
			{
				$result[] = gz_json_encode($v);
			}
			return '[' . join(',', $result) . ']';
		}
		else
		{
			foreach ($a as $k => $v)
			{
				$result[] = gz_json_encode($k) . ':' . gz_json_encode($v);
			}
			return '{' . join(',', $result) . '}';
		}
	}
}

if (! function_exists('make_dir'))
{

	function make_dir($dir, $mode = 0777)
	{
		if (is_dir($dir) || @mkdir($dir, $mode))
		{
			return TRUE;
		}
		
		if (! make_dir(dirname($dir), $mode))
		{
			return FALSE;
		}
		
		return @mkdir($dir, $mode);
	}
}

if (! function_exists('mime_content_type'))
{

	function mime_content_type($filename)
	{
		
		$mime_types = array(
			
			'txt' => 'text/plain', 
			'htm' => 'text/html', 
			'html' => 'text/html', 
			'php' => 'text/html', 
			'css' => 'text/css', 
			'js' => 'application/javascript', 
			'json' => 'application/json', 
			'xml' => 'application/xml', 
			'swf' => 'application/x-shockwave-flash', 
			'flv' => 'video/x-flv', 
			
			// images
			'png' => 'image/png', 
			'jpe' => 'image/jpeg', 
			'jpeg' => 'image/jpeg', 
			'jpg' => 'image/jpeg', 
			'gif' => 'image/gif', 
			'bmp' => 'image/bmp', 
			'ico' => 'image/vnd.microsoft.icon', 
			'tiff' => 'image/tiff', 
			'tif' => 'image/tiff', 
			'svg' => 'image/svg+xml', 
			'svgz' => 'image/svg+xml', 
			
			// archives
			'zip' => 'application/zip', 
			'rar' => 'application/x-rar-compressed', 
			'exe' => 'application/x-msdownload', 
			'msi' => 'application/x-msdownload', 
			'cab' => 'application/vnd.ms-cab-compressed', 
			
			// audio/video
			'mp3' => 'audio/mpeg', 
			'qt' => 'video/quicktime', 
			'mov' => 'video/quicktime', 
			
			// adobe
			'pdf' => 'application/pdf', 
			'psd' => 'image/vnd.adobe.photoshop', 
			'ai' => 'application/postscript', 
			'eps' => 'application/postscript', 
			'ps' => 'application/postscript', 
			
			// ms office
			'doc' => 'application/msword', 
			'rtf' => 'application/rtf', 
			'xls' => 'application/vnd.ms-excel', 
			'ppt' => 'application/vnd.ms-powerpoint', 
			
			// open office
			'odt' => 'application/vnd.oasis.opendocument.text', 
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
		);
		
		$ext = strtolower(array_pop(explode('.', $filename)));
		if (array_key_exists($ext, $mime_types))
		{
			return $mime_types[$ext];
		}
		elseif (function_exists('finfo_open'))
		{
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
			return $mimetype;
		}
		else
		{
			return 'application/octet-stream';
		}
	}
}

function get_last_days($timestamp)
{
	if ($timestamp - time() <= 0)
	{
		return false;
	}
	
	$last_time = $timestamp - time();
	
	$last_day = $last_time / (3600 * 24);
	
	if (intval($last_day) < $last_day)
	{
		$last_day = intval($last_day) + 1;
	}
	
	return intval($last_day);
}

/**
 * 获取头像目录文件地址
 * @param  $uid
 * @param  $size
 * @param  $return_type 0=返回全部 1=返回目录(a/b/c/) 2=返回文件名
 * @return string
 */
function get_avatar_url($uid, $size = 'min', $avatar_file = null)
{
	$uid = $uid * 1;
	
	if ($uid < 1)
	{
		return "";
	}
	
	$size = in_array($size, array(
		'max', 
		'mid', 
		"50", 
		'min', 
		"150"
	)) ? $size : 'real';
	
	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	
	if ($avatar_file === null)
	{
		$avatar_file = GZ_BASE::model("account")->get_avatar_file($uid);
	}
	
	if ($avatar_file === "")
	{
		return G_STATIC_URL . '/common/avatar-' . $size . '-img.jpg';
	}
	
	if (file_exists(get_setting('upload_dir') . '/avatar/' . $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid, - 2) . "_avatar_$size.jpg"))
	{
		return get_setting('upload_url') . '/avatar/' . $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid, - 2) . "_avatar_$size.jpg";
	}
	else
	{
		return G_STATIC_URL . '/common/avatar-' . $size . '-img.jpg';
	}
}

/**
 * 计算字符串UTF-8长度
 */
if (! function_exists('ustrLen'))
{

	function ustrLen($str, $charset = 'UTF-8')
	{
		if (function_exists('mb_get_info'))
		{
			return mb_strlen($str, $charset);
		}
		else
		{
			preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $str, $info);
			
			return sizeof($info[0]);
		}
	}
}

function jsonp_encode($json = array(), $callback = 'jsoncallback')
{
	if ($_GET[$callback])
	{
		return $_GET[$callback] . '(' . json_encode($json) . ')';
	}
	
	return json_encode($json);
}

function down_url($file_name, $url)
{
	$file_name = trim($file_name);
	$url = trim($url);
	
	if (! $file_name || ! $url)
		return false;
	
	return GZ_APP::config()->get("setting")->base_url . "/file/?act=re_download&file_name=" . urlencode($file_name) . "&url=" . base64_encode($url);

}

function date_friendly($timestamp, $time_limit = 604800, $out_format = 'Y-m-d H:i', $formats = null, $time_now = null)
{
	if ($formats == null)
	{
		$formats = array(
			'YEAR' => '%s 年前', 
			'MONTH' => '%s 月前', 
			'DAY' => '%s 天前', 
			'HOUR' => '%s 小时前', 
			'MINUTE' => '%s 分钟前', 
			'SECOND' => '%s 秒前'
		);
	}
	
	$time_now = $time_now == null ? time() : $time_now;
	$seconds = $time_now - $timestamp;
	
	if ($time_limit != null && $seconds > $time_limit)
	{
		return date($out_format, $timestamp);
	}
	
	$minutes = floor($seconds / 60);
	$hours = floor($minutes / 60);
	$days = floor($hours / 24);
	$months = floor($days / 30);
	$years = floor($months / 12);
	
	if ($years > 0)
	{
		$diffFormat = 'YEAR';
	}
	else
	{
		if ($months > 0)
		{
			$diffFormat = 'MONTH';
		}
		else
		{
			if ($days > 0)
			{
				$diffFormat = 'DAY';
			}
			else
			{
				if ($hours > 0)
				{
					$diffFormat = 'HOUR';
				}
				else
				{
					$diffFormat = ($minutes > 0) ? 'MINUTE' : 'SECOND';
				}
			}
		}
	}
	
	$dateDiff = null;
	
	switch ($diffFormat)
	{
		case 'YEAR' :
			$dateDiff = sprintf($formats[$diffFormat], $years);
			break;
		case 'MONTH' :
			$dateDiff = sprintf($formats[$diffFormat], $months);
			break;
		case 'DAY' :
			$dateDiff = sprintf($formats[$diffFormat], $days);
			break;
		case 'HOUR' :
			$dateDiff = sprintf($formats[$diffFormat], $hours);
			break;
		case 'MINUTE' :
			$dateDiff = sprintf($formats[$diffFormat], $minutes);
			break;
		case 'SECOND' :
			$dateDiff = sprintf($formats[$diffFormat], $seconds);
			break;
	}
	
	return $dateDiff;
}

function &load_class($class)
{
	static $_classes = array();
	
	// Does the class exist?  If so, we're done...
	if (isset($_classes[$class]))
	{
		return $_classes[$class];
	}
	
	if (class_exists($class) === FALSE)
	{
		$file = GZ_PATH . preg_replace('#_+#', '/', $class) . '.php';
		
		if (! file_exists($file))
		{
			show_error('Unable to locate the specified class: ' . $class . ' ' . preg_replace('#_+#', '/', $class) . '.php');
		}
		
		require_once $file;
	}
	
	$_classes[$class] = new $class();
	
	return $_classes[$class];
}

function _show_error($errorMessage = '')
{
	$errorBlock = '';
	$name = strtoupper($_SERVER['HTTP_HOST']);
	
	if ($errorMessage)
	{
		$errorMessage = htmlspecialchars($errorMessage);
		$errorBlock = <<<EOF
		<div class='database-error'>
	    	<form name='mysql'>
	    		<textarea rows="15" cols="60">{$errorMessage}</textarea>
	    	</form>
    	</div>
EOF;
	}
	
	return <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="Pragma" content="no-cache" />
		<meta http-equiv="Cache-Control" content="no-cache" />
		<meta http-equiv="Expires" content="Fri, 01 January 1999 01:00:00 GMT" />
		<title>{$name} System Error</title>
		<style type='text/css'>
			body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,form,fieldset,input,textarea,p,blockquote,th,td { margin:0; padding:0; } 
			table {	border-collapse:collapse; border-spacing:0; }
			fieldset,img { border:0; }
			address,caption,cite,code,dfn,em,strong,th,var { font-style:normal; font-weight:normal; }
			ol,ul { list-style:none; }
			caption,th { text-align:left; }
			h1,h2,h3,h4,h5,h6 { font-size:100%;	font-weight:normal; }
			q:before,q:after { content:''; }
			abbr,acronym { border:0; }
			hr { display: none; }
			address{ display: inline; }
			body {
				font-family: arial, tahoma, sans-serif;
				font-size: 0.8em;
				width: 100%;
			}
			
			h1 {
				font-family: arial, tahoma, "times new roman", serif;
				font-size: 1.9em;
				color: #fff;
			}
			h2 {
				font-size: 1.6em;
				font-weight: normal;
				margin: 0 0 8px 0;
				clear: both;
			}
			a {
				color: #3e70a8;
			}
			
				a:hover {
					color: #3d8ce4;
				}
				
				a.cancel {
					color: #ad2930;
				}
			#branding {
				background: #484848;
				padding: 8px;
			}
			
			#content {
				clear: both;
				overflow: hidden;
				padding: 20px 15px 0px 15px;
			}
			
			* #content {
				height: 1%;
			}
			
			.message {
				border-width: 1px;
				border-style: solid;
				border-color: #d7d7d7;
				background-color: #f5f5f5;
				padding: 7px 7px 7px 30px;
				margin: 0 0 10px 0;
				clear: both;
			}
			
				.message.error {
					background-color: #f3dddd;
					border-color: #deb7b7;
					color: #281b1b;
					font-size: 1.3em;
					font-weight: bold;
				}
				
				.message.unspecific {
					background-color: #f3f3f3;
					border-color: #d4d4d4;
					color: #515151;
				}
			.footer {
				text-align: center;
				font-size: 1.5em;
			}
			
			.database-error {
				padding: 4px 0px 10px 80px;
				margin: 10px 0px 10px 0px;
			}
			
			textarea {
				width: 700px;
				height: 250px;
			}
		</style>
	</head>
	<body id='ipboard_body'>
		<div id='header'>
			<div id='branding'>
				<h1>{$name} System Error</h1>
			</div>
		</div>
		<div id='content'>
			<div class='message error'>
				There appears to be an error:
				{$errorBlock}
			</div>
			
			<p class='message unspecific'>
				If you are seeing this page, it means there was a problem communicating with our database.  Sometimes this error is temporary and will go away when you refresh the page.  Sometimes the error will need to be fixed by an administrator before the site will become accessible again.
				<br /><br />
				You can try to refresh the page by clicking <a href="#" onclick="window.location=window.location; return false;">here</a>
			</p>
		</div>
	</body>
</html>
EOF;
}

function show_error($errorMessage = '')
{
	echo _show_error($errorMessage);
	exit();
}

function get_table($name)
{
	return GZ_APP::config()->get('database')->prefix . $name;
}

function array_unset_null_value($array)
{
	foreach ($array as $key => $val)
	{
		if (! $val)
		{
			unset($array[$key]);
		}
	}
	
	return $array;
}

function get_setting($varname = '')
{
	$setting = load_class('core_config')->get('setting');
	
	if (empty($varname))
	{
		return (array)load_class('core_config')->get('setting');
	}
	
	return $setting->$varname;
}

// ------------------------------------------------------------------------


/**
 * Tests for file writability
 *
 * is_writable() returns TRUE on Windows servers when you really can't write to 
 * the file, based on the read-only attribute.  is_writable() is also unreliable
 * on Unix servers if safe_mode is on. 
 *
 * @return	void
 */
function is_really_writable($file)
{
	// If we're on a Unix server with safe_mode off we call is_writable
	if (DIRECTORY_SEPARATOR == '/' and @ini_get("safe_mode") == FALSE)
	{
		return is_writable($file);
	}
	
	// For windows servers and safe_mode "on" installations we'll actually
	// write a file then read it.  Bah...
	if (is_dir($file))
	{
		$file = rtrim($file, '/') . '/' . md5(rand(1, 100));
		
		if (! file_put_contents($file, 'is_really_writable() test.'))
		{
			return FALSE;
		}
		else
		{
			@unlink($file);
		}
		
		return TRUE;
	}
	else if (($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE)
	{
		return FALSE;
	}
	
	return TRUE;
}

/**
 * 生成密码种子的函数
 *
 * @access      private
 * @param       int     length        长度
 * @return      string
 */
function fetch_salt($length = 4)
{
	$salt = '';
	for ($i = 0; $i < $length; $i ++)
	{
		$salt .= chr(rand(97, 122));
	}
	
	return $salt;
}

/**
 * 编译密码
 *
 *  @param  $password 	密码
 *  @param  $salt		混淆码
 
 * 	@return string		加密后的密码
 */
function compile_password($password, $salt)
{
	if (strlen($password) == 32)
	{
		return $password;
	}
	
	$password = md5(md5($password) . $salt);
		
	return $password;
}