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
	/**
	 * 个人关注动作
	 * 
	 */
	public function people_follow_add_ajax_action()
	{
		
		$follow_uid = $this->_INPUT["uid"] * 1;
		
		if (! $follow_uid)
		{
			return false;
		}
		
		$follow = $this->model('follow');
		
		if ($follow->user_follow_add($this->user_id, $follow_uid))
		{
			$follow->user_fans_count_edit($follow_uid, 1); //粉丝加1			
			$follow->user_friend_count_edit($this->user_id, 1); //我的关注会加1			

			H::ajax_json_output(GZ_APP::RSM(null, "1", "添加关注成功"));
			exit();
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "添加关注失败"));
			exit();
		}
	}

	/**
	 * 删除我的关注
	 * 
	 */
	public function people_follow_del_ajax_action()
	{
		
		$friend_uid = $this->_INPUT["uid"] * 1;
		
		if (! $friend_uid)
		{
			return false;
		}
		$follow = $this->model('follow');
		
		if ($follow->user_follow_del($this->user_id, $friend_uid))
		{
			$follow->user_fans_count_edit($friend_uid, - 1); //粉丝减1			
			$follow->user_friend_count_edit($this->user_id, - 1); //我的关注会减1			
			H::ajax_json_output(GZ_APP::RSM(null, "1", "删除关注成功"));
			exit();
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, "-1", "删除关注失败"));
			exit();
		}
	}

	/**
	 * 
	 * 执行编辑AJAX操作
	 * 
	 */
	public function people_follow_edit_ajax_action()
	{
		$action = null;
		$friend_uid = $this->_INPUT["uid"] * 1;
		
		if (! $friend_uid)
			return false;
		
		$follow = $this->model('follow');
		
		//首先判断是否存在关注
		if ($follow->user_follow_check($this->user_id, $friend_uid))
		{
			$action = "remove";
			
			if ($follow->user_follow_del($this->user_id, $friend_uid))
			{
				$follow->user_fans_count_edit($friend_uid, - 1); //粉丝减1			
				$follow->user_friend_count_edit($this->user_id, - 1); //我的关注会减1			
				H::ajax_json_output(GZ_APP::RSM(array(
					"type" => "remove"
				), "1", "删除关注成功"));
				exit();
			}
			else
			{
				H::ajax_json_output(GZ_APP::RSM(array(
					"type" => "remove"
				), "-1", "删除关注失败"));
				exit();
			}
		}
		else
		{
			$action = "add";
			
			if ($follow->user_follow_add($this->user_id, $friend_uid))
			{
				$follow->user_fans_count_edit($friend_uid, 1); //粉丝加1			
				$follow->user_friend_count_edit($this->user_id, 1); //我的关注会加1
				
				$this->model('notify')->send(notify_class::TYPE_PEOPLE_FOCUS, $friend_uid, array(
					'from_uid' => $this->user_id
				), notify_class::CATEGORY_PEOPLE, $this->user_id);
				
				H::ajax_json_output(GZ_APP::RSM(array(
					"type" => "add"
				), "1", "添加关注成功"));
				exit();
			}
			else
			{
				H::ajax_json_output(GZ_APP::RSM(array(
					"type" => "add"
				), "-1", "添加关注失败"));
				exit();
			}
		}
	
	}

}