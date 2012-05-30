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

class publish_class extends GZ_MODEL
{
	/*public function publish_competition($competitions_content, $category_id, $specific, $uninterested, $bonus, $expire_time, $contribute_pri, $banner_id, $attach_access_key)
	{
		$data = array(
			'competitions_content' => htmlspecialchars($competitions_content), 
			'category_id' => (int)$category_id, 
			'specific' => htmlspecialchars($specific), 
			'uninterested' => htmlspecialchars($uninterested), 
			'bonus' => (int)$bonus, 
			'expire_time' => strtotime($expire_time), 
			'contribute_pri' => (int)$contribute_pri, 
			'banner_id' => (int)$banner_id, 
			'published_uid' => USER::get_client_uid(), 
			'add_time' => time(), 
			'update_time' => time()
		);
		
		$competition_id = $this->insert('competitions_main', $data);
		
		if ($attach_access_key)
		{
			$this->update('competitions_attach', array(
				'competition_id' => intval($competition_id)
			), "competition_id = 0 AND access_key = '" . $this->quote($attach_access_key) . "'");
		}
		
		return $competition_id;
	}*/

	public function update_question_attach($question_id, $attach_access_key)
	{
		if (! $attach_access_key)
		{
			return false;
		}
		
		return $this->update('question_attach', array(
			'question_id' => intval($question_id)
		), "question_id = 0 AND access_key = '" . $this->quote($attach_access_key) . "'");
	}

	/*public function update_competition_attach($competition_id, $attach_access_key)
	{
		if (! $attach_access_key)
		{
			return false;
		}
		
		return $this->update('competitions_attach', array(
			'competition_id' => intval($competition_id)
		), "competition_id = 0 AND access_key = '" . $this->quote($attach_access_key) . "'");
	}*/

	/*public function add_competition_attach($file_name, $attach_access_key, $add_time, $file_location, $is_image = false)
	{
		if ($is_image)
		{
			$is_image = 1;
		}
		
		$data = array(
			'file_name' => htmlspecialchars($file_name), 
			'access_key' => $attach_access_key, 
			'add_time' => $add_time, 
			'file_location' => htmlspecialchars($file_location), 
			'is_image' => $is_image
		);
		
		return $this->insert('competitions_attach', $data);
	}*/

	public function add_question_attach($file_name, $attach_access_key, $add_time, $file_location, $is_image = false)
	{
		if ($is_image)
		{
			$is_image = 1;
		}
		
		$data = array(
			'file_name' => htmlspecialchars($file_name), 
			'access_key' => $attach_access_key, 
			'add_time' => $add_time, 
			'file_location' => htmlspecialchars($file_location), 
			'is_image' => $is_image
		);
		
		return $this->insert('question_attach', $data);
	}

	/*public function remove_competition_attach($id, $access_key)
	{
		$attach = $this->fetch_row('competitions_attach', "id = " . intval($id) . " AND access_key = '" . $this->quote($access_key) . "'");
		
		if (! $attach)
		{
			return false;
		}
		
		$thumb_sizes = array(
			'165x115', 
			'90x90'
		);
		
		foreach ($thumb_sizes as $thumb_size)
		{
			@unlink('../uploads/competitions/' . date('Ymd/', $attach['add_time']) . $thumb_size . '_' . $attach[0]['file_location']);
		}
		
		@unlink('../uploads/competitions/' . date('Ymd/', $attach['add_time']) . $attach['file_location']);
		
		return $this->delete('competitions_attach', "id = " . intval($id) . " AND access_key = '" . $this->quote($access_key) . "'");
	}*/

	public function remove_question_attach($id, $access_key)
	{
		$attach = $this->fetch_row('question_attach', "id = " . intval($id) . " AND access_key = '" . $this->quote($access_key) . "'");
		
		if (! $attach)
		{
			return false;
		}
		
		foreach(GZ_APP::config()->get('image')->attachment_thumbnail AS $key => $val)
		{			
			@unlink(get_setting('upload_dir').'/questions/' . date('Ymd/', $attach['add_time']) . $val['w'] . 'x' . $val['h'] . '_' . $attach['file_location']);
				
		}	
		
		@unlink(get_setting('upload_dir').'/questions/' . date('Ymd/', $attach['add_time']) . $attach['file_location']);
		
		return $this->delete('question_attach', "id = " . intval($id) . " AND access_key = '" . $this->quote($access_key) . "'");
	}

	public function get_file_class($file_type)
	{
		switch (strtolower($file_type))
		{
			case '3ds' :
				return 'file_Type_3ds';
				break;
			
			case 'zip' :
			case 'rar' :
			case 'gz' :
			case 'tar' :
			case 'cab' :
				return 'file_Type_zip';
				break;
			
			case 'ai' :
			case 'psd' :
			case 'cdr' :
				return 'file_Type_gif';
				break;
			
			default :
				return 'file_Type_txt';
				break;
		}
	}
}