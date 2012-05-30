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
 *  后台对上传文件的处理类(实现图片上传，图片缩小， 增加水印)
 * 	需要定义以下常量
 *  define('ERR_INVALID_IMAGE',             1);
 *  define('ERR_NO_GD',                     2);
 *  define('ERR_IMAGE_NOT_EXISTS',          3);
 *  define('ERR_DIRECTORY_READONLY',        4);
 *  define('ERR_UPLOAD_FAILURE',            5);
 *  define('ERR_INVALID_PARAM',             6);
 *  define('ERR_INVALID_IMAGE_TYPE',        7);
 *  define('IROOT_PATH',                     '网站根目录')
 *
*/

class image_class
{
	var $error_no = 0;
	var $error_msg = '';
	var $images_dir = 'images';
	var $data_dir = 'upload';
	
	var $type_maping = array(
		1 => 'image/gif', 
		2 => 'image/jpeg', 
		3 => 'image/png'
	);
	
	var $image_library = 'gd';
	var $quality = 90;

	public function __construct()
	{
		if (! defined(IROOT_PATH))
		{
			define('IROOT_PATH', "./");
		}
		
		if (class_exists('Imagick', false))
		{
			$this->image_library = 'imagick';
		
		}
	
	}

	/**
     * 图片上传的处理函数
     *
     * @access      public
     * @param       array       upload       包含上传的图片文件信息的数组
     * @param       array       dir          文件要上传在 $this->data_dir 下的目录名。如果为空图片放在则在 $this->images_dir 下以当月命名的目录下
     * @param       array       img_name     上传图片名称，为空则随机生成
     * @return      mix         如果成功则返回文件名，否则返回false
     */
	public function upload_image($upload, $dir = '', $img_name = '')
	{
		/* 没有指定目录默认为根目录 images */
		
		if ($img_name != "")
		{
			if ($this->get_filetype($img_name) == "")
			{
				$img_name = $img_name . $this->get_filetype($upload['name']);
			}
		}
		
		if (empty($dir))
		{
			/* 创建当月目录 */
			$dir = date('Ym');
			$dir = IROOT_PATH . $this->images_dir . '/' . $dir . '/';
		}
		else
		{
			/* 创建目录 */
			$dir = IROOT_PATH . $this->data_dir . '/' . $dir . '/';
			
			if ($img_name)
			{
				$img_name = $dir . $img_name; // 将图片定位到正确地址
			}
		}
		
		/* 如果目标目录不存在，则创建它 */
		if (! file_exists($dir))
		{
			if (! make_dir($dir))
			{
				/* 创建目录失败 */
				$this->error_msg = sprintf($GLOBALS['_LANG']['directory_readonly'], $dir);
				$this->error_no = ERR_DIRECTORY_READONLY;
				
				return false;
			}
		}
		
		if (empty($img_name))
		{
			$img_name = $this->unique_name($dir);
			$img_name = $dir . $img_name . $this->get_filetype($upload['name']);
		}
		
		if (! $this->check_img_type($upload['type']))
		{
			$this->error_msg = $GLOBALS['_LANG']['invalid_upload_image_type'];
			$this->error_no = ERR_INVALID_IMAGE_TYPE;
			
			return false;
		}
		
		if ($this->move_file($upload, $img_name))
		{
			return str_replace(IROOT_PATH, '', $img_name);
		}
		else
		{
			$this->error_msg = sprintf($GLOBALS['_LANG']['upload_failure'], $upload['name']);
			$this->error_no = ERR_UPLOAD_FAILURE;
			
			return false;
		}
	}

	public function make_thumb($img, $thumb_width = 0, $thumb_height = 0, $path = '', $filename = "", $intercept = false)
	{
		if ($this->image_library == 'imagick')
		{
			return $this->make_thumb_imagick($img, $thumb_width, $thumb_height, $path, $filename, $intercept);
		}
		
		return $this->make_thumb_gd($img, $thumb_width, $thumb_height, $path, $filename, $intercept);
	}

	/**
     * 创建图片的缩略图
     *
     * @access  public
     * @param   string      $img    原始图片的路径
     * @param   int         $thumb_width  缩略图宽度
     * @param   int         $thumb_height 缩略图高度
     * @param   string      $path         指定生成图片的目录名
     * @param   string		$filename      生成文件名
     * @param   bool		$intercept    是否截取
     * @return  mix         如果成功返回缩略图的路径，失败则返回false
     */
	private function make_thumb_gd($img, $thumb_width = 0, $thumb_height = 0, $path = '', $filename = "", $intercept = false)
	{
		$gd = $this->gd_version(); //获取 GD 版本。0 表示没有 GD 库，1 表示 GD 1.x，2 表示 GD 2.x
		

		if ($gd == 0)
		{
			$this->error_msg = "缺少GD库";
			return false;
		}
		
		/* 检查缩略图宽度和高度是否合法 */
		if ($thumb_width == 0 && $thumb_height == 0)
		{
			return str_replace(IROOT_PATH, '', str_replace('\\', '/', realpath($img)));
		}
		
		/* 检查原始文件是否存在及获得原始文件的信息 */
		$org_info = @getimagesize($img);
		
		if (! $org_info)
		{
			$this->error_msg = "缺少原始图片";
			$this->error_no = ERR_IMAGE_NOT_EXISTS;
			
			return false;
		}
		
		if (! $this->check_img_function($org_info[2]))
		{
			$this->error_msg = sprintf("不支持图片类型 %s ", $this->type_maping[$org_info[2]]);
			$this->error_no = ERR_NO_GD;
			
			return false;
		}
		
		$img_org = $this->img_resource($img, $org_info[2]);
		
		/* 原始图片以及缩略图的尺寸比例 */
		$scale_org = $org_info[0] / $org_info[1];
		
		$scale_org_mini = $thumb_width / $thumb_height; //缩略图的比例
		

		/* 处理只有缩略图宽和高有一个为0的情况，这时背景和缩略图一样大 */
		if ($thumb_width == 0)
		{
			$thumb_width = $thumb_height * $scale_org;
		}
		if ($thumb_height == 0)
		{
			$thumb_height = $thumb_width / $scale_org;
		}
		
		if ($intercept)
		{
			//需要切割

			/* 创建缩略图的标志符 */
			
			if ($gd == 2)
			{
				$img_thumb = imagecreatetruecolor($thumb_width, $thumb_height);
			}
			else
			{
				$img_thumb = imagecreate($thumb_width, $thumb_height);
			}
			
			/* 背景颜色 */
			$clr = imagecolorallocate($img_thumb, 255, 255, 255);
			imagefilledrectangle($img_thumb, 0, 0, $thumb_width, $thumb_height, $clr);
			
			/* 按照原始图片的尺寸比例缩放后的尺寸 */
			if ($scale_org > $scale_org_mini)
			{
				/* 原始图片比较宽，这时以宽度为准 */
				$lessen_width = $thumb_width;
				$lessen_height = $thumb_width / $scale_org;
				
				$org_info_w = $org_info[1] * $scale_org_mini;
				$org_info_h = $org_info[1];
				
				$dst_xx = ($org_info[0] - $org_info_w) / 2;
				$dst_yy = 0;
			}
			else
			{
				/* 原始图片比较高，则以高度为准 */
				$lessen_width = $thumb_height * $scale_org;
				$lessen_height = $thumb_height;
				
				$org_info_w = $org_info[0];
				$org_info_h = $org_info_w / $scale_org_mini;
				$dst_xx = 0;
				$dst_yy = ($org_info[1] - $org_info_h) / 2;
			}
			
			//    $dst_x = ($thumb_width  - $lessen_width)  / 2;
			//   $dst_y = ($thumb_height - $lessen_height) / 2;
			

			$dst_x = 0;
			$dst_y = 0;
			
			$lessen_width = $thumb_width;
			$lessen_height = $thumb_height;
		} //需要切割结束
		

		else //不需要切割
		{
			
			if ($org_info[0] / $thumb_width > $org_info[1] / $thumb_height)
			{
				$lessen_width = $thumb_width;
				$lessen_height = $thumb_width / $scale_org;
			}
			else
			{
				/* 原始图片比较高，则以高度为准 */
				$lessen_width = $thumb_height * $scale_org;
				$lessen_height = $thumb_height;
			}
			
			$dst_x = 0; // = ($thumb_width  - $lessen_width)  / 2;
			$dst_y = 0; //= ($thumb_height - $lessen_height) / 2;
			

			/* 创建缩略图的标志符 */
			if ($gd == 2)
			{
				$img_thumb = imagecreatetruecolor($lessen_width, $lessen_height);
			}
			else
			{
				$img_thumb = imagecreate($lessen_width, $lessen_height);
			}
			
			$org_info_w = $org_info[0];
			$org_info_h = $org_info[1];
		
		}
		
		/* 将原始图片进行缩放处理 */
		if ($gd == 2)
		{
			// imagecopyresampled($img_thumb, $img_org, $dst_x, $dst_y, 0, 0, $lessen_width, $lessen_height, $org_info[0], $org_info[1]);
			imagecopyresampled($img_thumb, $img_org, $dst_x, $dst_y, $dst_xx, $dst_yy, $lessen_width, $lessen_height, $org_info_w, $org_info_h);
		}
		else
		{
			// imagecopyresized($img_thumb, $img_org, $dst_x, $dst_y, 0, 0, $lessen_width, $lessen_height, $org_info[0], $org_info[1]);
			imagecopyresized($img_thumb, $img_org, $dst_x, $dst_y, $dst_xx, $dst_yy, $lessen_width, $lessen_height, $org_info_w, $org_info_h);
		}
		
		/* 创建当月目录 */
		if (empty($path))
		{
			$dir = IROOT_PATH . $this->images_dir . '/' . date('Ym') . '/';
		}
		else
		{
			$dir = $path;
		}
		
		/* 如果目标目录不存在，则创建它 */
		if (! file_exists($dir))
		{
			if (! make_dir($dir))
			{
				/* 创建目录失败 */
				$this->error_msg = sprintf("目录 %s 创建失败", $dir);
				$this->error_no = ERR_DIRECTORY_READONLY;
				return false;
			}
		}
		
		/* 如果文件名为空，生成不重名随机文件名 */
		if ($filename == "")
		{
			$filename = $this->unique_name($dir);
			
			/* 生成文件 */
			if (function_exists('imagejpeg'))
			{
				$filename .= '.jpg';
				imagejpeg($img_thumb, $dir . $filename);
			}
			elseif (function_exists('imagegif'))
			{
				$filename .= '.gif';
				imagegif($img_thumb, $dir . $filename);
			}
			elseif (function_exists('imagepng'))
			{
				$filename .= '.png';
				imagepng($img_thumb, $dir . $filename);
			}
			else
			{
				$this->error_msg = "创件文件失败";
				$this->error_no = ERR_NO_GD;
				
				return false;
			}
		}
		else
		{
			$filename_type = $this->get_filetype($filename);
			
			switch ($filename_type)
			{
				case '.jpg' :
					imagejpeg($img_thumb, $dir . $filename, 100);
					break;
				
				case '.gif' :
					
					imagegif($img_thumb, $dir . $filename, 100);
					break;
				
				case '.png' :
					imagepng($img_thumb, $dir . $filename);
					break;
				
				default :
					
					return false;
			
			}
		
		}
		
		imagedestroy($img_thumb);
		imagedestroy($img_org);
		
		//确认文件是否生成
		if (file_exists($dir . $filename))
		{
			return basename($dir . $filename);
			//return str_replace(IROOT_PATH, '', $dir) . $filename;
		}
		else
		{
			$this->error_msg = "写入错误 ";
			$this->error_no = ERR_DIRECTORY_READONLY;
			
			return false;
		}
	}

	/**
     * 创建图片的缩略图imagick
     *
     * @access  public
     * @param   string      $img    原始图片的路径
     * @param   int         $thumb_width  缩略图宽度
     * @param   int         $thumb_height 缩略图高度
     * @param   string      $path         指定生成图片的目录名
     * @param   string		 $filename      生成文件名
     * @param   bool		 $intercept    是否截取
     * @return  mix         如果成功返回缩略图的路径，失败则返回false
     */
	
	private function make_thumb_imagick($img, $thumb_width = 0, $thumb_height = 0, $path = '', $filename = "", $intercept = false)
	{
		
		/* 检查缩略图宽度和高度是否合法 */
		if ($thumb_width == 0 && $thumb_height == 0)
		{
			return str_replace(IROOT_PATH, '', str_replace('\\', '/', realpath($img)));
		}
		
		/* 检查原始文件是否存在及获得原始文件的信息 */
		$org_info = @getimagesize($img);
		
		if (! $org_info)
		{
			$this->error_msg = "缺少原始图片";
			$this->error_no = ERR_IMAGE_NOT_EXISTS;
			
			return false;
		}
		
		if (! $this->check_img_function($org_info['mime']))
		{
			$this->error_msg = sprintf("不支持图片类型 %s ", $this->type_maping[$org_info[2]]);
			$this->error_no = ERR_NO_GD;
			
			return false;
		}
		
		/* 原始图片以及缩略图的尺寸比例 */
		$scale_org = $org_info[0] / $org_info[1];
		
		$scale_org_mini = $thumb_width / $thumb_height; //缩略图的比例
		

		/* 处理只有缩略图宽和高有一个为0的情况，这时背景和缩略图一样大 */
		if ($thumb_width == 0)
		{
			$thumb_width = $thumb_height * $scale_org;
		}
		if ($thumb_height == 0)
		{
			$thumb_height = $thumb_width / $scale_org;
		}
		
		$im = new Imagick();
		
		$im->readimageblob(file_get_contents($img));
		
		$im->setCompressionQuality($this->quality);
		
		if ($intercept)
		{ //需要切割
			

			/* 按照原始图片的尺寸比例缩放后的尺寸 */
			if ($scale_org > $scale_org_mini)
			{
				/* 原始图片比较宽，这时以宽度为准 */
				$lessen_width = $thumb_width;
				$lessen_height = $thumb_width / $scale_org;
				
				$org_info_w = $org_info[1] * $scale_org_mini;
				$org_info_h = $org_info[1];
				
				$dst_xx = ($org_info[0] - $org_info_w) / 2;
				$dst_yy = 0;
			}
			else
			{
				/* 原始图片比较高，则以高度为准 */
				$lessen_width = $thumb_height * $scale_org;
				$lessen_height = $thumb_height;
				
				$org_info_w = $org_info[0];
				$org_info_h = $org_info_w / $scale_org_mini;
				$dst_xx = 0;
				$dst_yy = ($org_info[1] - $org_info_h) / 2;
			}
			

			$dst_x = 0;
			$dst_y = 0;
			
			$lessen_width = $thumb_width;
			$lessen_height = $thumb_height;
		}
		else //不需要切割
		{
			
			if ($org_info[0] / $thumb_width > $org_info[1] / $thumb_height)
			{
				$lessen_width = $thumb_width;
				$lessen_height = intval($thumb_width / $scale_org);
			}
			else
			{
				/* 原始图片比较高，则以高度为准 */
				$lessen_width = intval($thumb_height * $scale_org);
				$lessen_height = $thumb_height;
			}
			
			$dst_x = 0; // = ($thumb_width  - $lessen_width)  / 2;
			$dst_y = 0; //= ($thumb_height - $lessen_height) / 2;
			

			/* 创建缩略图的标志符 */
			
			$org_info_w = $org_info[0];
			$org_info_h = $org_info[1];
		
		}
		
		$im->cropImage($org_info_w, $org_info_h, $dst_xx, $dst_yy);
		
    	$im->thumbnailImage($lessen_width, $lessen_height, true);
		
    	/* 创建当月目录 */
    	if (empty($path))
		{
			$dir = IROOT_PATH . $this->images_dir . '/' . date('Ym') . '/';
		}
		else
		{
			$dir = $path;
		}
		
		/* 如果目标目录不存在，则创建它 */
		if (! file_exists($dir))
		{
			if (! make_dir($dir))
			{
				/* 创建目录失败 */
				$this->error_msg = sprintf("目录 %s 创建失败", $dir);
				$this->error_no = ERR_DIRECTORY_READONLY;
				return false;
			}
		}
		
		/* 如果文件名为空，生成不重名随机文件名 */
		if ($filename == "")
		{
			$filename = $this->unique_name($dir);
			
			/* 生成文件 */
			if (function_exists('imagejpeg'))
			{
				$filename .= '.jpg';
				//imagejpeg($img_thumb, $dir . $filename);
			}
			elseif (function_exists('imagegif'))
			{
				$filename .= '.gif';
				//imagegif($img_thumb, $dir . $filename);
			}
			elseif (function_exists('imagepng'))
			{
				$filename .= '.png';
				//imagepng($img_thumb, $dir . $filename);
			}
			else
			{
				$this->error_msg = "创件文件失败";
				$this->error_no = ERR_NO_GD;
				
				return false;
			}
		}
		
		$im->writeimage($dir . $filename);
		
		$im->clear();
		$im->destroy();

		//确认文件是否生成
		if (file_exists($dir . $filename))
		{
			return basename($dir . $filename);
		}
		else
		{
			$this->error_msg = "写入错误 ";
			$this->error_no = ERR_DIRECTORY_READONLY;
			
			return false;
		}
	}

	/**
     * 为图片增加水印
     *
     * @access      public
     * @param       string      filename            原始图片文件名，包含完整路径
     * @param       string      target_file         需要加水印的图片文件名，包含完整路径。如果为空则覆盖源文件
     * @param       string      $watermark          水印完整路径
     * @param       int         $watermark_place    水印位置代码
     * @return      mix         如果成功则返回文件路径，否则返回false
     */
	public function add_watermark($filename, $target_file = '', $watermark = '', $watermark_place = '', $watermark_alpha = 0.65)
	{
		// 是否安装了GD
		$gd = $this->gd_version();
		if ($gd == 0)
		{
			$this->error_msg = $GLOBALS['_LANG']['missing_gd'];
			$this->error_no = ERR_NO_GD;
			
			return false;
		}
		
		/* 如果水印的位置为0，则返回原图 */
		if ($watermark_place == 0)
		{
			return str_replace(IROOT_PATH, '', str_replace('\\', '/', realpath($filename)));
		}
		
		if (! $this->validate_image($watermark))
		{
			/* 已经记录了错误信息 */
			return false;
		}
		
		// 文件是否存在
		if ((! file_exists($filename)) || (! is_file($filename)))
		{
			$this->error_msg = sprintf($GLOBALS['_LANG']['missing_orgin_image'], $filename);
			$this->error_no = ERR_IMAGE_NOT_EXISTS;
			
			return false;
		}
		
		// 获得水印文件以及源文件的信息
		$watermark_info = @getimagesize($watermark);
		$watermark_handle = $this->img_resource($watermark, $watermark_info[2]);
		
		if (! $watermark_handle)
		{
			$this->error_msg = sprintf($GLOBALS['_LANG']['create_watermark_res'], $this->type_maping[$watermark_info[2]]);
			$this->error_no = ERR_INVALID_IMAGE;
			
			return false;
		}
		
		// 根据文件类型获得原始图片的操作句柄
		$source_info = @getimagesize($filename);
		$source_handle = $this->img_resource($filename, $source_info[2]);
		if (! $source_handle)
		{
			$this->error_msg = sprintf($GLOBALS['_LANG']['create_origin_image_res'], $this->type_maping[$source_info[2]]);
			$this->error_no = ERR_INVALID_IMAGE;
			
			return false;
		}
		
		// 根据系统设置获得水印的位置
		switch ($watermark_place)
		{
			case '1' :
				$x = 0;
				$y = 0;
				break;
			case '2' :
				$x = $source_info[0] - $watermark_info[0];
				$y = 0;
				break;
			case '4' :
				$x = 0;
				$y = $source_info[1] - $watermark_info[1];
				break;
			case '5' :
				$x = $source_info[0] - $watermark_info[0];
				$y = $source_info[1] - $watermark_info[1];
				break;
			default :
				$x = $source_info[0] / 2 - $watermark_info[0] / 2;
				$y = $source_info[1] / 2 - $watermark_info[1] / 2;
		}
		
		imagecopymerge($source_handle, $watermark_handle, $x, $y, 0, 0, $watermark_info[0], $watermark_info[1], $watermark_alpha);
		
		$target = empty($target_file) ? $filename : $target_file;
		
		switch ($source_info[2])
		{
			case 'image/gif' :
			case 1 :
				imagegif($source_handle, $target);
				break;
			
			case 'image/pjpeg' :
			case 'image/jpeg' :
			case 2 :
				imagejpeg($source_handle, $target);
				break;
			
			case 'image/x-png' :
			case 'image/png' :
			case 3 :
				imagepng($source_handle, $target);
				break;
			
			default :
				$this->error_msg = $GLOBALS['_LANG']['creating_failure'];
				$this->error_no = ERR_NO_GD;
				
				return false;
		}
		
		imagedestroy($source_handle);
		
		$path = realpath($target);
		if ($path)
		{
			return str_replace(IROOT_PATH, '', str_replace('\\', '/', $path));
		}
		else
		{
			$this->error_msg = $GLOBALS['_LANG']['writting_failure'];
			$this->error_no = ERR_DIRECTORY_READONLY;
			
			return false;
		}
	}

	/**
     *  检查水印图片是否合法
     *
     * @access  public
     * @param   string      $path       图片路径
     *
     * @return boolen
     */
	public function validate_image($path)
	{
		if (empty($path))
		{
			$this->error_msg = $GLOBALS['_LANG']['empty_watermark'];
			$this->error_no = ERR_INVALID_PARAM;
			
			return false;
		}
		
		/* 文件是否存在 */
		if (! file_exists($path))
		{
			$this->error_msg = sprintf($GLOBALS['_LANG']['missing_watermark'], $path);
			$this->error_no = ERR_IMAGE_NOT_EXISTS;
			return false;
		}
		
		// 获得文件以及源文件的信息
		$image_info = @getimagesize($path);
		
		if (! $image_info)
		{
			$this->error_msg = sprintf($GLOBALS['_LANG']['invalid_image_type'], $path);
			$this->error_no = ERR_INVALID_IMAGE;
			return false;
		}
		
		/* 检查处理函数是否存在 */
		if (! $this->check_img_function($image_info[2]))
		{
			$this->error_msg = sprintf($GLOBALS['_LANG']['nonsupport_type'], $this->type_maping[$image_info[2]]);
			$this->error_no = ERR_NO_GD;
			return false;
		}
		
		return true;
	}

	/**
     * 返回错误信息
     *
     * @return  string   错误信息
     */
	public function error_msg()
	{
		return $this->error_msg;
	}
	
	/*------------------------------------------------------ */
	//-- 工具函数
	/*------------------------------------------------------ */
	
	/**
     * 检查图片类型
     * @param   string  $img_type   图片类型
     * @return  bool
     */
	public function check_img_type($img_type)
	{
		return $img_type == 'image/pjpeg' || $img_type == 'image/x-png' || $img_type == 'image/png' || $img_type == 'image/gif' || $img_type == 'image/jpeg' || $img_type == 'image/bmp' || $img_type == 'application/octet-stream';
	
	}

	/**
     * 检查图片处理能力
     *
     * @access  public
     * @param   string  $img_type   图片类型
     * @return  void
     */
	public function check_img_function($img_type)
	{
		switch ($img_type)
		{
			case 'image/gif' :
			case 1 :
				
				if (PHP_VERSION >= '4.3')
				{
					return function_exists('imagecreatefromgif');
				}
				else
				{
					return (imagetypes() & IMG_GIF) > 0;
				}
				break;
			
			case 'image/pjpeg' :
			case 'image/jpeg' :
			case 2 :
				if (PHP_VERSION >= '4.3')
				{
					return function_exists('imagecreatefromjpeg');
				}
				else
				{
					return (imagetypes() & IMG_JPG) > 0;
				}
				break;
			
			case 'image/x-png' :
			case 'image/png' :
			case 3 :
				if (PHP_VERSION >= '4.3')
				{
					return function_exists('imagecreatefrompng');
				}
				else
				{
					return (imagetypes() & IMG_PNG) > 0;
				}
				break;
			case 'image/bmp' :
			case 6 :
				if (PHP_VERSION >= '4.3')
				{
					return function_exists('imagecreatefromwbmp');
				}
				else
				{
					return (imagetypes() & IMG_WBMP) > 0;
				}
				break;
			
			default :
				return false;
		}
	}

	/**
     * 生成随机的数字串
     *
     * @author: weber liu
     * @return string
     */
	public function random_filename($add_max = 9)
	{
		$add_max = intval($add_max);
		
		$str = '';
		for ($i = 0; $i < $add_max; $i ++)
		{
			$str .= mt_rand(0, 9);
		}
		
		return time() . $str;
	}

	/**
     *  生成指定目录不重名的文件名
     *
     * @access  public
     * @param   string      $dir        要检查是否有同名文件的目录
     *
     * @return  string      文件名
     */
	public function unique_name($dir)
	{
		$filename = '';
		while (empty($filename))
		{
			$filename = $this->random_filename();
			if (file_exists($dir . $filename . '.jpg') || file_exists($dir . $filename . '.gif') || file_exists($dir . $filename . '.png'))
			{
				$filename = '';
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
	public function get_filetype($path)
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
     * 根据来源文件的文件类型创建一个图像操作的标识符
     *
     * @access  public
     * @param   string      $img_file   图片文件的路径
     * @param   string      $mime_type  图片文件的文件类型
     * @return  resource    如果成功则返回图像操作标志符，反之则返回错误代码
     */
	public function img_resource($img_file, $mime_type)
	{
		switch ($mime_type)
		{
			case 1 :
			case 'image/gif' :
				$res = imagecreatefromgif($img_file);
				break;
			
			case 2 :
			case 'image/pjpeg' :
			case 'image/jpeg' :
				$res = imagecreatefromjpeg($img_file);
				break;
			
			case 3 :
			case 'image/x-png' :
			case 'image/png' :
				$res = imagecreatefrompng($img_file);
				break;
			
			default :
				return false;
		}
		
		return $res;
	}

	/**
     * 获得服务器上的 GD 版本
     *
     * @access      public
     * @return      int         可能的值为0，1，2
     */
	public function gd_version()
	{
		static $version = - 1;
		
		if ($version >= 0)
		{
			return $version;
		}
		
		if (! extension_loaded('gd'))
		{
			$version = 0;
		}
		else
		{
			// 尝试使用gd_info函数
			if (PHP_VERSION >= '4.3')
			{
				if (function_exists('gd_info'))
				{
					$ver_info = gd_info();
					preg_match('/\d/', $ver_info['GD Version'], $match);
					$version = $match[0];
				}
				else
				{
					if (function_exists('imagecreatetruecolor'))
					{
						$version = 2;
					}
					elseif (function_exists('imagecreate'))
					{
						$version = 1;
					}
				}
			}
			else
			{
				if (preg_match('/phpinfo/', ini_get('disable_functions')))
				{
					/* 如果phpinfo被禁用，无法确定gd版本 */
					$version = 1;
				}
				else
				{
					// 使用phpinfo函数
					ob_start();
					phpinfo(8);
					$info = ob_get_contents();
					ob_end_clean();
					$info = stristr($info, 'gd version');
					preg_match('/\d/', $info, $match);
					$version = $match[0];
				}
			}
		}
		
		return $version;
	}

	/**
     *
     *
     * @access  public
     * @param
     *
     * @return void
     */
	public function move_file($upload, $target)
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