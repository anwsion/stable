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

class HTTP
{

	/**
	 * NO CACHE 文件头
	 * 
	 * @param string  $type
	 * @param string $charset
	 */
	public static function no_cache_header($type = 'text/html', $charset = 'utf-8')
	{
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
		header('Pragma: no-cache');
		header('Content-Type: ' . $type . '; charset=' . $charset . '');
	}

	/**
	 * 获取COOKIE
	 * 
	 * @param string $name
	 */
	public static function get_cookie($name)
	{
		
		if (isset($_COOKIE[G_COOKIE_PREFIX . $name]))
		{
			return $_COOKIE[G_COOKIE_PREFIX . $name];
		}
		
		return false;
	}

	/**
	 * 设置 COOKIE
	 * Enter description here ...
	 * @param string $name
	 * @param string $value
	 * @param int $expire
	 * @param string $path
	 * @param string $domain
	 * @param string $secure
	 */
	public static function set_cookie($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false)
	{
		if (! $domain)
		{
			// Don't use a cfg value for the domain, will break mirrored sites
			//$currentDomain = preg_replace('/(^www\.|:\d+$)/', '', (!empty($_SERVER["HTTP_HOST"]) AND $_SERVER["HTTP_HOST"] != $_SERVER['SERVER_NAME']) ? $_SERVER["HTTP_HOST"] : $_SERVER['SERVER_NAME']);
			

			$domain = G_COOKIE_DOMAIN;
			
			/*if (!preg_match('/(?:\d{1,3}\.){3}\d{1,3}/', $currentDomain) AND $currentDomain != 'localhost' AND !self::valid_ip($currentDomain))
			{
				$domain = '.' . $currentDomain; // add . to allow subdomains of this domain to share this cookie
			}
			else
			{
				$domain=$currentDomain;
			}*/
		}
		
		return setcookie(G_COOKIE_PREFIX . $name, $value, $expire, $path, $domain, $secure);
	}

	/**
	 * Validate IP Address
	 *
	 * Updated version suggested by Geert De Deckere
	 * 
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public static function valid_ip($ip)
	{
		$ip_segments = explode('.', $ip);
		
		// Always 4 segments needed
		if (count($ip_segments) != 4)
		{
			return FALSE;
		}
		// IP can not start with 0
		if (substr($ip_segments[0], 0, 1) == '0')
		{
			return FALSE;
		}
		// Check each segment
		foreach ($ip_segments as $segment)
		{
			// IP segments must be digits and can not be 
			// longer than 3 digits or greater then 255
			if (preg_match("/[^0-9]/", $segment) or $segment > 255 or strlen($segment) > 3)
			{
				return FALSE;
			}
		}
		
		return TRUE;
	}

	/**
	 * 重定向 URL
	 * 
	 * @param string $url
	 */
	public static function redirect($url)
	{
		$url = trim($url);
		
		if (substr($url, 0, 1) == '/')
		{
			$url = get_setting('base_url') . $url;
		}
		
		if ($url)
		{
			header('Location: ' . $url);
			die;
		}
	}

	/**
	 * 重定向文件
	 * @param  $file_name	文件名
	 * @param  $file_url	文件URL地址,以"/"开头,例:/abc/abc.doc
	 */
	public static function redirect_download_header($file_name, $file_url)
	{
		$file_name = trim($file_name);
		$file_url = trim($file_url);
		
		if (! $file_name || ! $file_url)
		{
			return false;
		}
		
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-Disposition: attachment; " . $file_name);
		header("Content-Type: application/octet-stream;");
		header('X-Accel-Redirect:' . $file_url);
		header('HTTP/1.1 206 Partial Content');
		
		return true;
		//header("X-Accel-Buffering: yes");
	

	}

	public function downloads($file_name, $real_dir)
	{
		if (! file_exists($real_dir))
		{
			header("Content-type: text/html; charset=utf-8");
			echo "File not found!";
			exit();
		}
		else
		{
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
			header("Content-Disposition: attachment; " . $file_name);
			header("Content-Type: application/octet-stream;");
			header("Accept-Ranges: bytes");
			header("Accept-Length: " . filesize($real_dir));
			
			readfile($real_dir);
		}
	}

	/**
	 * Browser detection system - returns whether or not the visiting browser is the one specified
	 *
	 * @param	string	Browser name (opera, ie, mozilla, firebord, firefox... etc. - see $is array)
	 * @param	float	Minimum acceptable version for true result (optional)
	 *
	 * @return	boolean
	 */
	static function IsBrowser($browser, $version = 0)
	{
		static $is;
		
		if (! is_array($is))
		{
			$useragent = strtolower($_SERVER["HTTP_USER_AGENT"]);
			
			$is = array(
				'opera' => 0, 
				'ie' => 0, 
				'mozilla' => 0, 
				'firebird' => 0, 
				'firefox' => 0, 
				'camino' => 0, 
				'konqueror' => 0, 
				'safari' => 0, 
				'webkit' => 0, 
				'webtv' => 0, 
				'netscape' => 0, 
				'mac' => 0
			);
			
			// detect opera
			# Opera/7.11 (Windows NT 5.1; U) [en]
			# Mozilla/4.0 (compatible; MSIE 6.0; MSIE 5.5; Windows NT 5.0) Opera 7.02 Bork-edition [en]
			# Mozilla/4.0 (compatible; MSIE 6.0; MSIE 5.5; Windows NT 4.0) Opera 7.0 [en]
			# Mozilla/4.0 (compatible; MSIE 5.0; Windows 2000) Opera 6.0 [en]
			# Mozilla/4.0 (compatible; MSIE 5.0; Mac_PowerPC) Opera 5.0 [en]
			if (strpos($useragent, 'opera') !== false)
			{
				preg_match('#opera(/| )([0-9\.]+)#', $useragent, $regs);
				$is['opera'] = $regs[2];
			}
			
			// detect internet explorer
			# Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Q312461)
			# Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.0.3705)
			# Mozilla/4.0 (compatible; MSIE 5.22; Mac_PowerPC)
			# Mozilla/4.0 (compatible; MSIE 5.0; Mac_PowerPC; e504460WanadooNL)
			if (strpos($useragent, 'msie ') !== false and ! $is['opera'])
			{
				preg_match('#msie ([0-9\.]+)#', $useragent, $regs);
				$is['ie'] = $regs[1];
			}
			
			// detect macintosh
			if (strpos($useragent, 'mac') !== false)
			{
				$is['mac'] = 1;
			}
			
			// detect safari
			# Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en-us) AppleWebKit/74 (KHTML, like Gecko) Safari/74
			# Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/51 (like Gecko) Safari/51
			if (strpos($useragent, 'applewebkit') !== false and $is['mac'])
			{
				preg_match('#applewebkit/(\d+)#', $useragent, $regs);
				$is['webkit'] = $regs[1];
				
				if (strpos($useragent, 'safari') !== false)
				{
					preg_match('#safari/([0-9\.]+)#', $useragent, $regs);
					$is['safari'] = $regs[1];
				}
			}
			
			// detect konqueror
			# Mozilla/5.0 (compatible; Konqueror/3.1; Linux; X11; i686)
			# Mozilla/5.0 (compatible; Konqueror/3.1; Linux 2.4.19-32mdkenterprise; X11; i686; ar, en_US)
			# Mozilla/5.0 (compatible; Konqueror/2.1.1; X11)
			if (strpos($useragent, 'konqueror') !== false)
			{
				preg_match('#konqueror/([0-9\.-]+)#', $useragent, $regs);
				$is['konqueror'] = $regs[1];
			}
			
			// detect mozilla
			# Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.4b) Gecko/20030504 Mozilla
			# Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.2a) Gecko/20020910
			# Mozilla/5.0 (X11; U; Linux 2.4.3-20mdk i586; en-US; rv:0.9.1) Gecko/20010611
			if (strpos($useragent, 'gecko') !== false and ! $is['safari'] and ! $is['konqueror'])
			{
				preg_match('#gecko/(\d+)#', $useragent, $regs);
				$is['mozilla'] = $regs[1];
				
				// detect firebird / firefox
			# Mozilla/5.0 (Windows; U; WinNT4.0; en-US; rv:1.3a) Gecko/20021207 Phoenix/0.5
			# Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.4b) Gecko/20030516 Mozilla Firebird/0.6
			# Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.4a) Gecko/20030423 Firebird Browser/0.6
			# Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.6) Gecko/20040206 Firefox/0.8
				if (strpos($useragent, 'firefox') !== false or strpos($useragent, 'firebird') !== false or strpos($useragent, 'phoenix') !== false)
				{
					preg_match('#(phoenix|firebird|firefox)( browser)?/([0-9\.]+)#', $useragent, $regs);
					$is['firebird'] = $regs[3];
					
					if ($regs[1] == 'firefox')
					{
						$is['firefox'] = $regs[3];
					}
				}
				
				// detect camino
			# Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en-US; rv:1.0.1) Gecko/20021104 Chimera/0.6
				if (strpos($useragent, 'chimera') !== false or strpos($useragent, 'camino') !== false)
				{
					preg_match('#(chimera|camino)/([0-9\.]+)#', $useragent, $regs);
					$is['camino'] = $regs[2];
				}
			}
			
			// detect web tv
			if (strpos($useragent, 'webtv') !== false)
			{
				preg_match('#webtv/([0-9\.]+)#', $useragent, $regs);
				$is['webtv'] = $regs[1];
			}
			
			// detect pre-gecko netscape
			if (preg_match('#mozilla/([1-4]{1})\.([0-9]{2}|[1-8]{1})#', $useragent, $regs))
			{
				$is['netscape'] = "$regs[1].$regs[2]";
			}
		}
		
		// sanitize the incoming browser name
		$browser = strtolower($browser);
		if (substr($browser, 0, 3) == 'is_')
		{
			$browser = substr($browser, 3);
		}
		
		// return the version number of the detected browser if it is the same as $browser
		if ($is["$browser"])
		{
			// $version was specified - only return version number if detected version is >= to specified $version
			if ($version)
			{
				if ($is["$browser"] >= $version)
				{
					return $is["$browser"];
				}
			}
			else
			{
				return $is["$browser"];
			}
		}
		
		// if we got this far, we are not the specified browser, or the version number is too low
		return 0;
	}
}