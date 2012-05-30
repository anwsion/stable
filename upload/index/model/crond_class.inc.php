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

class crond_class extends GZ_MODEL
{
	public function start()
	{
		$cornd_db_file = GZ_PATH . '../tmp/cornd_db.php';
	
		if (file_exists($cornd_db_file))
		{
			$cornd_db = unserialize(file_get_contents($cornd_db_file));
		}
		
		$this->every();
		
		if ($cornd_db['half_minute'] < time() - 30)
		{
			$this->half_minute();
			
			$cornd_db['half_minute'] = time();
			
			$cornd_is_run = TRUE;
		}
		
		if ($cornd_db['minute'] < time() - 60)
		{
			$this->minute();
			
			$cornd_db['minute'] = time();
			
			$cornd_is_run = TRUE;
		}
		
		if ($cornd_db['hour'] < time() - 3600)
		{
			$this->hour();
			
			$cornd_db['hour'] = time();
			
			$cornd_is_run = TRUE;
		}
		
		if (date('Y-m-d', $cornd_db['day']) != date('Y-m-d', time()))
		{
			$this->day();
			
			$cornd_db['day'] = time();
			
			$cornd_is_run = TRUE;
		}
		
		if (date('YW', $cornd_db['week']) != date('YW', time()))
		{
			$this->week();
			
			$cornd_db['week'] = time();
			
			$cornd_is_run = TRUE;
		}
		
		if ($cornd_is_run)
		{
			file_put_contents($cornd_db_file, serialize($cornd_db));
		}
	}
	
	// 每次执行
	public function every()
	{
		
	}
	
	// 每半分钟执行
	public function half_minute()
	{
		$this->model('online')->online_active();
	}
	
	// 每分钟执行
	public function minute()
	{
		
	}
	
	// 每小时执行
	public function hour()
	{
		
	}
	
	// 每日时执行
	public function day()
	{
		$this->model('system')->clean_break_attach();
	}
	
	// 每周执行
	public function week()
	{
		
	}
}