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

class FORMAT
{

	/**
	 *  字符进行安全转向处理 
	 * @param string $request
	 * @param boolean $istext
	 */
	static function safe($request, $istext = false)
	{
		// If we are on PHP >= 6.0.0 we do not need some code
		if (version_compare(PHP_VERSION, '6.0.0', '<'))
		{
			$request = addslashes($request); //转意义
		}
		
		if ($istext === true)
		{
			$request = strip_tags(str_replace(array(
				'<', 
				'>'
			), array(
				'&lt;', 
				'&gt;'
			), $request)); //括号 
		}
		
		return $request;
	}

	/**
	 * 封装MYSQL安全字符处理
	 * 
	 * @param string $unescaped_string
	 */
	static function mysql_safe($unescaped_string)
	{
		return mysql_escape_string($unescaped_string);
	}

	/**
	 *
	 * 过滤跨站脚本过滤
	 */
	public static function filter_xss($string, $allowedtags = '', $disabledattributes = array(
			'onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 
			'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 
			'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 
			'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 
			'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 
			'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 
			'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 
			'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 
			'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 
			'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 
			'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 
			'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 
			'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'))
	{
		$string = preg_replace('/\s(' . implode('|', $disabledattributes) . ').*?([\s\>])/', '\\2', preg_replace('/<(.*?)>/ie', "'<'.preg_replace(array('/javascript:[^\"\']*/i', '/(" . implode('|', $disabledattributes) . ")[ \\t\\n]*=[ \\t\\n]*[\"\'][^\"\']*[\"\']/i', '/\s+/'), array('', '', ' '), stripslashes('\\1')) . '>'", $string));
		
		return $string;
	}

	/**
	 * 
	 *替换字符串
	 * @param string $string
	 * 
	 * @return string 
	 */
	public static function safe_replace($string)
	{
		$string = str_replace('%20', '', $string);
		$string = str_replace('%27', '', $string);
		$string = str_replace('*', '', $string);
		$string = str_replace('"', '&quot;', $string);
		$string = str_replace("'", '', $string);
		$string = str_replace("\"", '', $string);
		$string = str_replace('//', '', $string);
		$string = str_replace(';', '', $string);
		$string = str_replace('<', '&lt;', $string);
		$string = str_replace('>', '&gt;', $string);
		$string = str_replace('(', '', $string);
		$string = str_replace(')', '', $string);
		$string = str_replace("{", '', $string);
		$string = str_replace('}', '', $string);
		
		return $string;
	}

	/**
	 * 
	 * 截取字符串
	 * @param string $string
	 * @param int    $length
	 * @param string $dot
	 * 
	 * @return string
	 */
	public static function cut_str($string, $length, $dot = ' ...')
	{
		$charset = 'utf-8';
		
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

	/**
	 * 
	 * 格式化内容显示
	 * @param string $content
	 * @param int    $charset_num
	 * 
	 * @return array $content = array('content_title' => 截断后内容, 'content_content' => 实际内容 )
	 */
	public static function format_content($content, $charset_num = 200, $dot = '...')
	{
		$content_title = '';
		$content_conent = strip_tags($content);
		$content_len = strlen($content_conent);
		
		if ($content_len > $charset_num)
		{
			$content_title = FORMAT::cut_str($content_conent, $charset_num, $dot);
		}
		else
		{
			$content_title = '';
		}
		
		return array(
			'content_title' => $content_title, 
			'content_content' => $content
		);
	}

	public static function clear_html($content)
	{
		$content = strip_tags($content);
		$content = preg_replace("/<a[^>]*>/i", "", $content);
		$content = preg_replace("/<\/a>/i", "", $content);
		$content = preg_replace("/<div[^>]*>/i", "", $content);
		$content = preg_replace("/<\/div>/i", "", $content);
		$content = preg_replace("/<!--[^>]*-->/i", "", $content); //注释内容  
		$content = preg_replace("/style=.+?['|\"]/i", '', $content); //去除样式  
		$content = preg_replace("/class=.+?['|\"]/i", '', $content); //去除样式  
		$content = preg_replace("/id=.+?['|\"]/i", '', $content); //去除样式     
		$content = preg_replace("/lang=.+?['|\"]/i", '', $content); //去除样式      
		$content = preg_replace("/width=.+?['|\"]/i", '', $content); //去除样式   
		$content = preg_replace("/height=.+?['|\"]/i", '', $content); //去除样式   
		$content = preg_replace("/border=.+?['|\"]/i", '', $content); //去除样式   
		$content = preg_replace("/face=.+?['|\"]/i", '', $content); //去除样式   
		$content = preg_replace("/face=.+?['|\"]/", '', $content); //去除样式 只允许小写 正则匹配没有带 i 参数
		return $content;
	}

	public static function clear_body_html($content)
	{
		$content = preg_replace("/<span[^>]*>/i", "", $content);
		$content = preg_replace("/<\/span>/i", "", $content);
		$content = preg_replace("/<a[^>]*>/i", "", $content);
		$content = preg_replace("/<\/a>/i", "", $content);
		$content = preg_replace("/<div[^>]*>/i", "", $content);
		$content = preg_replace("/<\/div>/i", "", $content);
		$content = preg_replace("/<!--[^>]*-->/i", "", $content); //注释内容  
		$content = preg_replace("/style=.+?['|\"]/i", '', $content); //去除样式  
		$content = preg_replace("/class=.+?['|\"]/i", '', $content); //去除样式  
		$content = preg_replace("/id=.+?['|\"]/i", '', $content); //去除样式     
		$content = preg_replace("/lang=.+?['|\"]/i", '', $content); //去除样式      
		$content = preg_replace("/width=.+?['|\"]/i", '', $content); //去除样式   
		$content = preg_replace("/height=.+?['|\"]/i", '', $content); //去除样式   
		$content = preg_replace("/border=.+?['|\"]/i", '', $content); //去除样式   
		$content = preg_replace("/face=.+?['|\"]/i", '', $content); //去除样式   
		$content = preg_replace("/face=.+?['|\"]/", '', $content); //去除样式 只允许小写 正则匹配没有带 i 参数
		return $content;
	}

	static function parse_links($str, $popup = TRUE)
	{
		$str = @preg_replace('/(http[s]?:\/\/[-a-zA-Z0-9@:%_\+.~#?&\/\/=]+)/i', '<a href="\1" target=_blank rel=nofollow>\1</a>', $str);
		
		if (strpos($str, "http") === FALSE)
		{
			$str = @preg_replace('/(www\.[-a-zA-Z0-9@:%_\+\.~#?&\/\/=]+)/i', '<a href="http://\1" target=_blank rel=nofollow>\1</a>', $str);
		}
		
		return $str;
	}

}