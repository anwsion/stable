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

class c_main_class extends GZ_CONTROLLER
{

	public function re_download_action()
	{
		$file_name = trim($this->_INPUT["file_name"]);
		
		$url = trim($this->_INPUT["url"]);
		
		if (! $file_name || ! $url)
		{
			show_error("file re_download error");
		}
		
		if (preg_match('~&#([0-9]+);~', $file_name))
		{
			if (function_exists('iconv'))
			{
				$file_name_conv = @iconv('utf-8', 'UTF-8//IGNORE', $file_name);
				
				if ($file_name_conv !== false)
				{
					$file_name = $file_name_conv;
				}
			}
			
			$file_name = preg_replace('~&#([0-9]+);~e', "convert_int_to_utf8('\\1')", $file_name);
		}
		
		$file_name_charset = 'utf-8';
		
		$file_name = preg_replace('#[\r\n]#', '', $file_name);
		
		// Opera and IE have not a clue about this, mozilla puts on incorrect extensions.
		if (HTTP::IsBrowser('mozilla'))
		{
			$file_name = "filename*=" . $file_name_charset . "''" . rawurlencode($file_name);
			//$file_name = "filename==?'utf-8'?B?" . base64_encode($file_name) . "?=";
		}
		else
		{
			// other browsers seem to want names in UTF-8
			if ($file_name_charset != 'utf-8' and function_exists('iconv'))
			{
				$file_name_conv = iconv($file_name_charset, 'UTF-8//IGNORE', $file_name);
				
				if ($file_name_conv !== false)
				{
					$file_name = $file_name_conv;
				}
			}
			
			if (HTTP::IsBrowser('opera') or HTTP::IsBrowser('konqueror') or HTTP::IsBrowser('safari'))
			{
				// Opera / Konqueror does not support encoded file names
				$file_name = 'filename="' . str_replace('"', '', $file_name) . '"';
			}
			else if (HTTP::IsBrowser('ie'))
			{
				$file_name = 'filename=' . str_replace('+', ' ', urlencode($file_name)) . '';
			}
			else
			{
				// encode the filename to stay within spec
				$file_name = 'filename="' . rawurlencode($file_name) . '"';
			}
		}
		
		$this->_INPUT['url'] = str_replace('..', '', base64_decode($this->_INPUT['url']));
		$this->_INPUT['url'] = str_replace(get_setting("upload_url"), "", $this->_INPUT['url']);
		$path = get_setting("upload_dir") . "/" . $this->_INPUT['url'];
		
		HTTP::downloads($file_name, $path);
	}

}