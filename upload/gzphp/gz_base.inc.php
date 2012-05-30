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

class GZ_BASE
{
	private static $models = array();

	public static function model($model_class)
	{
		if (! isset(self::$models[$model_class]) and ! strstr($model_class, '_class'))
		{
			$model_class .= '_class';
		}
		
		if (! isset(self::$models[$model_class]))
		{
			$model = new $model_class();
			
			self::$models[$model_class] = $model;
		}
		
		return self::$models[$model_class];
	}
}

