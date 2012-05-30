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
 *  上传文件的处理类
 * 	需要定义以下常量
 *  define('ALLOW_FILE_FIXS',        		"doc,xls");
 *  define('ERR_DIRECTORY_READONLY',        4);
 *  define('ERR_UPLOAD_FAILURE',            5);
 *  define('ERR_INVALID_FILE_TYPE',        	7);
 *  define('IROOT_PATH',                    '网站根目录')
 *
*/

class upload_class
{
	var $error_no = 0;
	var $error_msg = '';
	var $upload_dir = 'upload';

	function __construct()
	{
		if (! defined('IROOT_PATH'))
		{
			define('IROOT_PATH', "./");
		}
		
		if (! defined('ALLOW_FILE_FIXS'))
		{
			define('ALLOW_FILE_FIXS', "jpg,png,gif,swf,rar,zip,doc,xls,ppt");
		}
	}

	function set_upload_dir($path_name)
	{
		if ($path_name)
		{
			if (substr($path_name, -1, 1) != '/')
			{
				$this->upload_dir = $path_name . '/';
			}
			else
			{
				$this->upload_dir = $path_name;
			}
		}
		else
		{
			$this->upload_dir = '';
		}
	}

	/**
     * XHR 文件上传的处理函数
     *
     * @access      public
     * @param       stream       stream       文件数据
     * @param       filename       filename       文件名
     * @param       array       dir          文件要上传在$this->upload_dir下的目录名。如果为空则文件放在$this->upload_dir下，并以当日命名的目录下
     * @param       array       file_name     上传文件名称，为空则随机生成
     * @return      mix         如果成功则返回文件名，否则返回false
     */
	function xhr_upload_file($stream, $filename, $dir = '', $file_name = '')
	{
		/* 没有指定目录默认为根目录images */
		
		if ($file_name != "")
		{
			if ($this->get_filetype($file_name) == "")
			{
				$file_name = $file_name . $this->get_filetype($filename);
			}
			
			$this->attachment = $file_name;
		}
		
		if (empty($dir))
		{
			/* 创建当月目录 */
			$dir = date('Ymd');
			$dir = IROOT_PATH . $this->upload_dir . $dir . '/';
		}
		else
		{
			/* 创建目录 */
			$dir = IROOT_PATH . $this->upload_dir . $dir . '/';
			
			if ($file_name)
			{
				$file_name = $dir . $file_name;
			}
		}
		
		/* 如果目标目录不存在，则创建它 */
		if (! is_dir($dir))
		{
			if (! make_dir($dir))
			{
				/* 创建目录失败 */
				$this->error_msg = sprintf($GLOBALS['_LANG']['directory_readonly'], $dir);
				
				$this->error_no = ERR_DIRECTORY_READONLY;
				
				return false;
			}
		}
		
		if (empty($file_name))
		{
			$file_name = $this->unique_name($dir);
			$this->attachment = $file_name . $this->get_filetype($filename);
			$file_name = $dir . $file_name . $this->get_filetype($filename);
		}
		if (! $this->check_file_type($filename))
		{
			$this->error_msg = $GLOBALS['_LANG']['invalid_upload_file_type'];
			$this->error_no = ERR_INVALID_FILE_TYPE;
			
			return false;
		}
		
		//if ($this->move_file($upload, $file_name))
		if (file_put_contents($file_name, $stream))
		{
			$this->file_name = $file_name;
			
			return str_replace(IROOT_PATH, '', $file_name);
		}
		else
		{
			$this->error_msg = sprintf($GLOBALS['_LANG']['upload_failure'], $filename);
			$this->error_no = ERR_UPLOAD_FAILURE;
			
			return false;
		}
	}

	/**
     * 文件上传的处理函数
     *
     * @access      public
     * @param       array       upload       包含上传的文件信息的数组
     * @param       array       dir          文件要上传在$this->upload_dir下的目录名。如果为空则文件放在$this->upload_dir下，并以当日命名的目录下
     * @param       array       file_name     上传文件名称，为空则随机生成
     * @return      mix         如果成功则返回文件名，否则返回false
     */
	function upload_file($upload, $dir = '', $file_name = '')
	{
		/* 没有指定目录默认为根目录images */
		
		if ($file_name != "")
		{
			if ($this->get_filetype($file_name) == "")
			{
				$file_name = $file_name . $this->get_filetype($upload['name']);
			}
			$this->attachment = $file_name;
		}
		
		if (empty($dir))
		{
			/* 创建当月目录 */
			$dir = date('Ymd');
			$dir = IROOT_PATH . $this->upload_dir . $dir . '/';
		}
		else
		{
			/* 创建目录 */
			$dir = IROOT_PATH . $this->upload_dir . $dir . '/';
			
			if ($file_name)
			{
				$file_name = $dir . $file_name;
			}
		}
		
		/* 如果目标目录不存在，则创建它 */
		if (! is_dir($dir))
		{
			if (! make_dir($dir))
			{
				/* 创建目录失败 */
				$this->error_msg = sprintf($GLOBALS['_LANG']['directory_readonly'], $dir);
				$this->error_no = ERR_DIRECTORY_READONLY;
				
				return false;
			}
		}
		
		if (empty($file_name))
		{
			$file_name = $this->unique_name($dir);
			$this->attachment = $file_name . $this->get_filetype($upload['name']);
			$file_name = $dir . $file_name . $this->get_filetype($upload['name']);
		}
		if (! $this->check_file_type($upload['name']))
		{
			$this->error_msg = $GLOBALS['_LANG']['invalid_upload_file_type'];
			$this->error_no = ERR_INVALID_FILE_TYPE;
			return false;
		}
		
		if ($this->move_file($upload, $file_name))
		{
			$this->file_name = $file_name;
			return str_replace(IROOT_PATH, '', $file_name);
		}
		else
		{
			$this->error_msg = sprintf($GLOBALS['_LANG']['upload_failure'], $upload['name']);
			$this->error_no = ERR_UPLOAD_FAILURE;
			
			return false;
		}
	}

	/**
     * 返回错误信息
     *
     * @return  string   错误信息
     */
	function error_msg()
	{
		return $this->error_msg;
	}

	/**
     * 检查上传的文件后缀
     * @param   string  $upload_name   文件名称
     * @return  bool
     */
	function check_file_type($upload_name)
	{
		$file_fix = $this->get_filetype($upload_name);
		
		$file_fix = trim($file_fix, ".");
		
		if (ALLOW_FILE_FIXS == '*')
		{
			if (in_array($file_fix, array(
				'php', 
				'phtml', 
				'asp', 
				'cgi', 
				'aspx', 
				'exe'
			)))
			{
				return false;
			}
			
			return true;
		}
		
		$file_fixs = explode(',', ALLOW_FILE_FIXS);
		
		if (in_array($file_fix, $file_fixs))
		{
			return true;
		}
		
		return false;
	}

	function random_filename()
	{
		return md5(microtime() . rand(100000, 999999));
	}

	/**
     *  生成指定目录不重名的文件名
     *
     * @access  public
     * @param   string      $dir        要检查是否有同名文件的目录
     *
     * @return  string      文件名
     */
	function unique_name($dir)
	{
		$filename = '';
		
		while (empty($filename))
		{
			$filename = self::random_filename();
			$file_fixs = explode(",", ALLOW_FILE_FIXS);
			
			foreach ($file_fixs as $key => $value)
			{
				if (file_exists($dir . $filename . '.' . $value))
				{
					$filename = '';
				}
			}
		}
		
		return $filename;
	}

	/**
     *  返回文件后缀名，如‘.php’
     *
     * @access  public
     * @param
     *
     * @return  string      文件后缀名
     */
	function get_filetype($path)
	{
		$pos = strrpos($path, '.');
		if ($pos !== false)
		{
			return strtolower(substr($path, $pos));
		}
		else
		{
			return '';
		}
	}

	/**
     *  返回文件大小
     *
     * @access  public
     * @param
     *
     * @return  string      文件后缀名
     */
	function get_filesize()
	{
		return filesize($this->file_name);
	}

	/**
     *  返回文件名
     *
     * @access  public
     * @param
     *
     * @return  string      文件名
     */
	function get_attachment()
	{
		return $this->attachment;
	}

	/**
     *
     *
     * @access  public
     * @param
     *
     * @return void
     */
	function move_file($upload, $target)
	{
		if (isset($upload['error']) && $upload['error'] > 0)
		{
			return false;
		}
		
		if (! move_uploaded_file($upload['tmp_name'], $target))
		{
			return false;
		}
		
		return true;
	}
}
