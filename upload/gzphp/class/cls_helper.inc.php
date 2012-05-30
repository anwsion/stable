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

class H
{

	/**
	 * 获取文件扩展名
	 * @param string $filename 文件名
	 */
	
	public static function get_file_ext($filename)
	{
		$filename = explode('.', $filename);
		
		$ext_count = sizeof($filename) - 1;
		
		if ($filename[$ext_count] == 'jpeg' or $filename[$ext_count] == 'jpe')
		{
			$filename[$ext_count] = 'jpg';
		}
		
		if ($filename[$ext_count] == 'htm')
		{
			$filename[$ext_count] = 'html';
		}
		
		return strtolower($filename[$ext_count]);
	}

	/**
	 * 数组JSON返回
	 * 
	 * @param  $array 
	 */
	public static function ajax_json_output($array)
	{
		echo json_encode($array);
		exit();
	}

	/**
	 * 检查手机号码是否合法
	 * @param $moblie
	 * @return unknown_type
	 */
	public static function check_mobile_char($mobile)
	{
		
		$mobile = trim($mobile);
		
		if (strlen($mobile) != 11)
			return false;
		
		$exp = '/^(((13[0-9]{1})|(15[0-9]{1}))+\d{8})/isU';
		
		return preg_match($exp, $mobile);
	
	}

	/**
	 * 检查用户名的字符
	 * @param $username
	 * @return unknown_type
	 */
	public static function check_username_char($username)
	{
		
		//不允许英文特殊符号 by echo
		for ($i = 0; $i < strlen($username); $i ++)
		{
			$ascii = ord($username[$i]);
			if ($ascii < 48 || ($ascii > 57 && $ascii < 65) || ($ascii > 90 && $ascii < 97) || ($ascii > 122 && $ascii < 128))
			{
				return false;
			}
		}
		if ($username != str_replace(array(
			"　", 
			"?", 
			"", 
			"", 
			""
		), ' ', $username))
		{
			return false;
		}
		
		$username = str_replace("　", '', $username);
		$username = str_replace("?", '', $username);
		$username = str_replace("", '', $username);
		$username = str_replace("", '', $username);
		
		if (strlen($username) < 3)
		{
			return false;
		}
		if (strlen($username) > 15)
		{
			return false;
		}
		
		$guestexp = '\xA1\xA1|\xAC\xA3|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';
		
		$censorexp = '/^(' . str_replace(array(
			'\\*', 
			"\r\n", 
			' '
		), array(
			'.*', 
			'|', 
			''
		), preg_quote(($censoruser = trim($censoruser)), '/')) . ')$/i';
		if (preg_match("/^\s*$|^c:\\con\\con$|[%,\*\"\s\t\<\>\&]|$guestexp/is", $username) || ($censoruser && @preg_match($censorexp, $username)))
		{
			return false;
		
		}
		
		return true;
	
	}

	/**
	 * 屏蔽关键词
	 * @param $username
	 * @return unknown_type
	 */
	public static function check_username_banned($username)
	{
		$banned_lines = array_map('rtrim', file('include/username_banned.txt'));
		
		if (in_array($username, $banned_lines))
		{
			
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * 是否电子邮件格式
	 * @param $email
	 * @return bool
	 */
	public static function isemail($email)
	{
		$email = trim($email);
		
		if (!$email)
		{
			return false;
		}
		
		return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
	}

	/**
	 * 跳转JS
	 * ...
	 * @param  $msg
	 * @param  $url
	 */
	public static function js_pop_msg($message, $url = NULL)
	{
		$url = empty($url) ? 'window.history.go(-1);' : "location.href = '{$url}';";
		
		if (substr($url, 0, 1) == '/')
		{
			$url = get_setting('base_url') . $url;
		}
		
		if ($message)
		{
			echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type='text/javascript'>function sptips(){ alert(\"{$message}\");{$url} }</script></head><body onload=\"sptips()\"></body></html>";
		}
		else
		{
			echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script type='text/javascript'>function sptips(){{$url}}</script></head><body onload=\"sptips()\"></body></html>";
		}
		
		exit;
	}

	/**
	 * 加密hash，生成发送给用户的hash字符串
	 *
	 * @param array $hash_arr
	 * @return string
	 */
	public static function encode_hash($hash_arr, $hash_key = false)
	{
		if (empty($hash_arr))
		{
			return false;
		}
		
		$hash_str = "";
		
		foreach ($hash_arr as $key => $value)
		{
			$hash_str .= $key . "^]+" . $value . "!;-";
		}
		
		$hash_str = substr($hash_str, 0, - 3);
		
		// 加密干扰码，加密解密时需要用到的KEY
		if (! $hash_key)
		{
			$hash_key = G_COOKIE_HASH_KEY;
		}
		
		// 加密过程
		$tmp_str = '';
		
		for ($i = 1; $i <= strlen($hash_str); $i ++)
		{
			$char = substr($hash_str, $i - 1, 1);
			$keychar = substr($hash_key, ($i % strlen($hash_key)) - 2, 1);
			$char = chr(ord($char) + ord($keychar));
			$tmp_str .= $char;
		}
		
		$hash_str = base64_encode($tmp_str);
		$hash_str = str_replace(array(
			'+', 
			'/', 
			'='
		), array(
			'-', 
			'_', 
			'.'
		), $hash_str);
		//$hash_str	=	urlencode($hash_str);
		

		return $hash_str;
	}

	/**
	 * 解密hash，从用户回链的hash字符串解密出里面的内容
	 *
	 * @param string $hash_str
	 * @param boolean $b_urldecode	当$hash_str不是通过浏览器传递的时候就需要urldecode,否则会解密失败，反之也一样
	 * @return array
	 */
	public static function decode_hash($hash_str, $b_urldecode = false, $hash_key = false)
	{
		if (empty($hash_str))
		{
			return array();
		}
			
			// 加密干扰码，加密解密时需要用到的KEY
		if (! $hash_key)
		{
			$hash_key = G_COOKIE_HASH_KEY;
		}
		
		//解密过程
		$tmp_str = '';
		
		if (strpos($hash_str, "-") || strpos($hash_str, "_") || strpos($hash_str, "."))
		{
			$hash_str = str_replace(array(
				'-', 
				'_', 
				'.'
			), array(
				'+', 
				'/', 
				'='
			), $hash_str);
		}
		
		$hash_str = base64_decode($hash_str);
		
		for ($i = 1; $i <= strlen($hash_str); $i ++)
		{
			$char = substr($hash_str, $i - 1, 1);
			$keychar = substr($hash_key, ($i % strlen($hash_key)) - 2, 1);
			$char = chr(ord($char) - ord($keychar));
			$tmp_str .= $char;
		}
		
		$hash_arr = array();
		$arr = explode("!;-", $tmp_str);
		
		foreach ($arr as $value)
		{
			list($k, $v) = explode("^]+", $value);
			if ($k)
			{
				$hash_arr[$k] = $v;
			}
		}
		
		return $hash_arr;
	}

	/** 生成 Options **/
	function display_options($param, $default = '_DEFAULT_',$default_key='key')
	{
		$output = '';
		
		
		if (is_array($param))
		{
			$keyindex = 0;
			
			foreach ($param as $key => $value)
			{	
					
				//if ($key == $keyindex ++ && is_numeric($key))

				if($default_key=='value')
				{
					$output .= '<option value="' . $key . '"' . (($value == $default) ? '  selected' : '') . '>' . $value . '</option>';
				}
				else
				{
					$output .= '<option value="' . $key . '"' . (($key == $default) ? '  selected' : '') . '>' . $value . '</option>';
				}
			}
			
		}
		
		
		
		return $output;
	}

}