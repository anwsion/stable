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

class c_setting_class extends GZ_CONTROLLER
{

	public function get_access_rule()
	{
		$rule_action["rule_type"] = 'white'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action["actions"] = array();
		
		return $rule_action;
	}

	function setup()
	{
		$this->crumb('设置', '/account/?c=setting&act=user');
	}

	function index_action()
	{
		$this->user_action();
	}

	function user_action()
	{
		if ($this->user_info["user_name"] == $this->user_info["email"])
		{
			$this->user_info["user_name"] = '';
		}
		
		if ($this->user_info["birthday"] != 0) //默认的信息
		{
			TPL::assign('birthday_y_s', date('Y', $this->user_info["birthday"]));
			TPL::assign('birthday_m_s', date('m', $this->user_info["birthday"]));
			TPL::assign('birthday_d_s', date('d', $this->user_info["birthday"]));
		}
		
		//年符值
		$year_end = date('Y') * 1;
		
		$year_array[0] = "";
		
		for ($tmp_i = $year_end; $tmp_i > 1900; $tmp_i --)
		{
			$year_array[$tmp_i] = $tmp_i;
		}
		
		TPL::assign('birthday_y', $year_array);
		
		//月符值
		TPL::assign('birthday_m', array(
			"0" => "", 
			"01" => "01", 
			"02" => "02", 
			"03" => "03", 
			"04" => "04", 
			"05" => "05", 
			"06" => "06", 
			"07" => "07", 
			"08" => "08", 
			"09" => "09", 
			"10" => "10", 
			"11" => "11", 
			"12" => "12"
		));
		
		//日符值
		$day_array[0] = '';
		
		for ($tmp_i = 1; $tmp_i <= 31; $tmp_i ++)
		{
			$day_array[$tmp_i] = $tmp_i;
		}
		
		TPL::assign('birthday_d', $day_array);
		
		$jobs_list_tmp = $this->model('work')->get_jos_list();
		$jobs_list[1] = "请选择";
		
		foreach ($jobs_list_tmp as $row)
		{
			$jobs_list[$row['jobs_id']] = $row["jobs_name"];
		}
		
		TPL::assign("jobs_list", $jobs_list);
		
		$this->crumb('基本资料', '/account/?c=setting&act=user');
		
		TPL::import_css(array(
			'css/discussion.css'
		));
		
		TPL::import_js(array(
			'js/ajaxupload.js', 
			'js/LocationSelect.js'
		));
		
		TPL::output("account/setting_user_v2", true);
	}

	function user_process_ajax_action()
	{
		$rsm_data = null;
		$msg = "保存成功";
		
		$update_arr["province"] = $this->_INPUT["province"] * 1;
		$update_arr["city"] = $this->_INPUT["city"] * 1;
		$update_arr["district"] = $this->_INPUT["district"] * 1;
		$update_arr["job_id"] = $this->_INPUT["job_id"] * 1;
		
		$update_arr["user_name"] = htmlspecialchars(trim($this->_INPUT["user_name"]));
		
		if (! $this->model('account')->check_username_char($update_arr["user_name"]))
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "真实姓名请输入 2-7 个汉字，或 4-14 个英文。"));
		}
		
		$user_id = $this->user_id;
		//$user_info = $this->model('account')->get_users_by_uid ( $user_id );
		$user_info = $this->user_info;
		//end modfiy
		

		$this->_INPUT["domain_url"] = htmlspecialchars(trim($this->_INPUT["domain_url"]));
		
		if ($this->_INPUT["domain_url"] && ($this->_INPUT["domain_url"] != $user_info["url"]))
		{
			if (preg_match("/^[\d]+$/i", $this->_INPUT["domain_url"]))
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '个性域名不能全部为数字, 请重新填写'));
			}
			
			$url_match = preg_match('/([1-9A-Za-z]+)/i', $this->_INPUT["domain_url"]);
			
			if ((! $url_match) || (lenmbstr_zhcn($this->_INPUT["domain_url"]) < 4) || (lenmbstr_zhcn($this->_INPUT["domain_url"]) > 20))
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '个性域名不符合要求, 请重新填写'));
			}
			
			if ($this->model('account')->check_url($this->_INPUT["domain_url"]))
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '个性域名已经存在, 请重新填写'));
			}
			
			$update_arr["url"] = $this->_INPUT["domain_url"];
		}
		
		if ($this->model('account')->check_username($update_arr["user_name"]) and $user_info['user_name'] != $update_arr["user_name"])
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '已经存在相同的姓名, 请重新填写'));
		}
		
		if (! H::isemail($user_info["email"]))
		{
			if (! H::isemail($this->_INPUT["email"]))
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '请输入正确的 E-Mail 地址'));
			}
			
			if ($this->model('account')->check_email($this->_INPUT["email"]))
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', '邮箱已经存在, 请使用新的邮箱'));
			}
			
			$update_arr["email"] = $this->_INPUT["email"];
			
			//发送验证邮件
			$email = $this->_INPUT["email"];
			
			$msg = "保存成功，请登录您的邮箱检查验证邮件。";
			$rsm_data["text"] = "请登录您的邮箱查收验证邮件";
			
			$rsm_data["email_info"] = $this->model('verification')->get_email_info($email);
			$this->model('verification')->send_verification_email($email);
		}
		
		$update_arr["sex"] = (trim($this->_INPUT["sex"])) * 1;
		
		if ($this->_INPUT['birthday_y'])
		{
			$update_arr['birthday'] = mktime(0, 0, 0, ($this->_INPUT['birthday_m'] * 1), ($this->_INPUT['birthday_d'] * 1), ($this->_INPUT['birthday_y'] * 1));
		}
		
		$update_attrib_arr['signature'] = htmlspecialchars(trim($this->_INPUT['signature']));		

		//更新主表
		$this->model('account')->update_users_fields($update_arr, $this->user_id);
		
		//更新从表
		$this->model('account')->update_users_attrib_fields($update_attrib_arr, $this->user_id);
		
		ZCACHE::cleanGroup('g_uid_' . $this->user_id);
		
		H::ajax_json_output(GZ_APP::RSM($rsm_data, 1, $msg));
	
	}

	function myface_ajax_upload_action()
	{
		define('IROOT_PATH', get_setting('upload_dir') . '/avatar/');
		define('ALLOW_FILE_FIXS', '*');
		
		$account_obj = $this->model('account');
		
		if ($_FILES["user_avatar"]["name"])
		{
			$class_image = $this->model('image');
			
			$class_image->data_dir = "";
			
			$file_name = $class_image->upload_image($_FILES["user_avatar"], $this->model('account')->get_avatar($this->user_id, '', 1), $this->model('account')->get_avatar($this->user_id, '', 2));
			
			//生成缩图
			if (! $file_name)
			{
				H::ajax_json_output(GZ_APP::RSM(null, '-1', "图片格式错误"));
				exit();
			}
			
			//	$thumb_file_max	=$class_image->make_thumb($file_name,100,300,"upload/avatar_max/",$this->user_id.".gif");
			
			
			foreach( GZ_APP::config()->get('image')->avatar_thumbnail as $key=>$val)
			{
				$thumb_file[$key]=$class_image->make_thumb(IROOT_PATH . $file_name, $val["w"], $val["h"], IROOT_PATH . $this->model('account')->get_avatar($this->user_id, "", 1), $this->model('account')->get_avatar($this->user_id, $key, 2), true);
				
				
			}

			//$thumb_file_max = $class_image->make_thumb(IROOT_PATH . $file_name, 100, 100, IROOT_PATH . $this->model('account')->get_avatar($this->user_id, "", 1), $this->model('account')->get_avatar($this->user_id, "max", 2), true);
			//$thumb_file_50 = $class_image->make_thumb(IROOT_PATH . $file_name, 50, 50, IROOT_PATH . $this->model('account')->get_avatar($this->user_id, "", 1), $this->model('account')->get_avatar($this->user_id, "50", 2), true);
			//$thumb_file_150 = $class_image->make_thumb(IROOT_PATH . $file_name, 150, 150, IROOT_PATH . $this->model('account')->get_avatar($this->user_id, "", 1), $this->model('account')->get_avatar($this->user_id, "150", 2), true);
			//$thumb_file_mid = $class_image->make_thumb(IROOT_PATH . $file_name, 32, 32, IROOT_PATH . $this->model('account')->get_avatar($this->user_id, "", 1), $this->model('account')->get_avatar($this->user_id, "mid", 2), true);
		 	//thumb_file = $class_image->make_thumb(IROOT_PATH . $file_name, 20, 20, IROOT_PATH . $this->model('account')->get_avatar($this->user_id, "", 1), $this->model('account')->get_avatar($this->user_id, "min", 2), true);
		
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "没选择图片"));
			exit();
		}
		
		$update_arr["avatar_type"] = 1;
		$update_arr["avatar_file"] = $this->model('account')->get_avatar($this->user_id, "", 1) .$thumb_file["min"];
		$account_class = $this->model('account');
		
		//更新主表
		$this->model('account')->update_users_fields($update_arr, $this->user_id);
		
		$rsm_data = array(
			'preview' => get_setting('upload_url') . '/avatar/' . $this->model('account')->get_avatar($this->user_id, "", 1) . $thumb_file["max"]
		);
		
		//头像添加积分
		//$this->model("integral")->set_user_integral('FILL_AVATAR', $this->user_id, "你的个人设置补充了头像");
		
		H::ajax_json_output(GZ_APP::RSM($rsm_data, 1, "上传头像成功"));
	}

	/**
	 * 修改联系方式
	 */
	function contact_action()
	{
		$this->crumb('联系方式', '/account/?c=setting&act=contact');
		
		TPL::import_css(array(
			'css/discussion.css'
		));
		
		TPL::output("account/setting_contact_v2", true);
	}

	function contact_process_ajax_action()
	{
		
		$rsm_data = null;
		$msg = "保存成功";
		
		$update_attrib_arr["qq"] = htmlspecialchars(trim($this->_INPUT["qq"]));
		$update_attrib_arr["msn"] = htmlspecialchars(trim($this->_INPUT["msn"]));
		$update_attrib_arr["popular_email"] = htmlspecialchars(trim($this->_INPUT["popular_email"]));		
		$update_attrib_arr["homepage"] = htmlspecialchars(trim($this->_INPUT["homepage"]));		
		$update_arr["mobile"] = htmlspecialchars(trim($this->_INPUT["mobile"]));
		
		//联系方式添加积分
		if ($update_attrib_arr["qq"] || $update_attrib_arr["msn"] || $update_attrib_arr["popular_email"] || $update_attrib_arr["homepage"] || $update_arr["mobile"])
		{
			//$this->model("integral")->set_user_integral('FILL_CONTACT', $this->user_id, "你的个人设置补充了联系方式");
		}
		
		//更新主表
		$this->model('account')->update_users_fields($update_arr, $this->user_id);
		
		//更新从表
		$this->model('account')->update_users_attrib_fields($update_attrib_arr, $this->user_id);
		
		H::ajax_json_output(GZ_APP::RSM($rsm_data, 1, $msg));
	
	}

	/**
	 * 私信设置
	 */
	function inbox_action()
	{
		$email_setting = $this->model('account')->get_email_setting_by_uid($this->user_id);
		
		$nt_setting = $this->model('account')->get_notification_setting_by_uid($this->user_id);
		TPL::assign('email_setting', $email_setting);
		TPL::assign('nt_setting', $nt_setting);
		TPL::assign('notify_actions', $this->model('notify')->notify_action_details);
		
		TPL::assign('inbox_recv', $this->user_info["inbox_recv"] * 1);
		
		$this->crumb('私信及邮件通知', '/account/?c=setting&act=inbox');
		
		TPL::import_css(array(
			'css/discussion.css'
		));
		
		TPL::output("account/setting_inbox_v2", true);
	}

	function inbox_process_ajax()
	{
		$update_arr["inbox_recv"] = $this->_INPUT["inbox_recv"] * 1;
		
		//更新主表
		$this->model('account')->update_users_fields($update_arr, $this->user_id);
		
		$update_array["sender_11"] = $this->_INPUT["sender_11"] * 1;
		$update_array["sender_12"] = $this->_INPUT["sender_12"] * 1;
		$update_array["sender_13"] = $this->_INPUT["sender_13"] * 1;
		$update_array["sender_14"] = $this->_INPUT["sender_14"] * 1;
		$update_array["sender_15"] = $this->_INPUT["sender_15"] * 1;
		
		$nt_setting = $this->_INPUT['nt_setting'];
		
		$notify_actions = $this->model('notify')->notify_action_details;
		
		foreach ($notify_actions as $key => $val)
		{
			if (! isset($nt_setting[$key]) && $val['user_setting'])
			{
				$nt_data[] = $key;
			}
		}
		
		$update_nt_array = array(
			'data' => mysql_escape_string(serialize($nt_data))
		);
		
		if (($this->model('account')->update_notification_setting_fields($update_nt_array, $this->user_id)) && ($this->model('account')->update_email_setting_fields($update_array, $this->user_id)))
		{
			H::ajax_json_output(GZ_APP::RSM(null, 1, "保存成功"));
			exit();
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "保存失败"));
			exit();
		}
	}

	/**
	 * 修改个人密码
	 */
	function editpassword_action()
	{
		$this->crumb('修改密码', '/account/?c=setting&act=editpassword');
		
		TPL::import_css(array(
			'css/discussion.css'
		));
		
		if ($this->_INPUT['version'] == 1)
		{
			TPL::output("account/setting_editpassword", true);
		}
		else
		{
			TPL::output('account/setting_editpassword_v2', true);
		}
	}

	function editpassword_process_ajax_action()
	{		
		$old_password = $this->_INPUT["old_password"];
		$password = $this->_INPUT["password"];
		$re_password = $this->_INPUT["re_password"];
		
		if ($password != $re_password)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '请输入相同的确认密码'));
		}
		
		if (strlen($password) < 6 OR strlen($password) > 16)
		{
			H::ajax_json_output(GZ_APP::RSM(null, -1, "密码长度不符合规则"));
		}
		
		if (get_setting('ucenter_enabled') == 'Y')
		{
			if ($this->model('ucenter')->is_uc_user($this->user_info['email']))
			{
				$result = $this->model('ucenter')->user_edit($this->user_id, $this->user_info['user_name'], $old_password, $password);
				
				if ($result !== 1)
				{
					H::ajax_json_output(GZ_APP::RSM(null, -1, $result));
				}
			}
		}
		
		if ($this->model('account')->update_user_password($old_password, $password, $this->user_id, $this->user_info['salt']))
		{
			H::ajax_json_output(GZ_APP::RSM(null, 1, '密码修改成功'));
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', '请输入正确的当前密码'));
		}
	}

	/*******教育************************************************************************************************/
	
	/**
	 * 教育信息
	 */
	function educationinfo_action()
	{
		$education_experience_list = $this->model('education')->get_education_experience_list($this->user_id);
		
		TPL::assign('education_experience_list', $education_experience_list);
		
		$year_end = date('Y') * 1;
		
		$year_array[0] = '请选择';
		
		for ($tmp_i = $year_end; $tmp_i > 1900; $tmp_i --)
		{
			$year_array[$tmp_i] = $tmp_i;
		}
		
		TPL::assign('education_years_list', $year_array);
		
		$this->crumb('教育经历', '/account/?c=setting&act=educationinfo');
		
		TPL::import_css(array(
			'css/discussion.css'
		));
		
		TPL::output('account/setting_educationinfo_v2', true);
	}

	function educationinfo_edit_action()
	{
		$education_id = $this->_INPUT["eid"] * 1;
		
		$education_experience_row = $this->model('education')->get_education_experience_row($education_id, $this->user_id);
		
		if (! $education_experience_row)
		{
			H::js_pop_msg("连接出错");
		}
		
		TPL::assign('education_experience_row', $education_experience_row);
		
		$year_end = date('Y') * 1;
		
		$year_array[0] = '请选择';
		
		for ($tmp_i = $year_end; $tmp_i > 1900; $tmp_i --)
		{
			$year_array[$tmp_i] = $tmp_i;
		}
		
		TPL::assign('school_type_list', array(
			5 => '大学', 
			4 => '高中', 
			3 => '中技', 
			2 => '初中', 
			1 => '小学'
		));
		TPL::assign('school_type', $education_experience_row['school_type']);
		TPL::assign('education_years', $education_experience_row['education_years']);
		TPL::assign('education_years_list', $year_array);
		
		$this->crumb('教育经历', '/account/?c=setting&act=educationinfo');
		
		TPL::import_css(array(
			'css/discussion.css'
		));
		
		TPL::output("account/setting_educationinfo_edit_v2", true);
	}
	
	//添加教育经历 
	function add_educationinfo_process_ajax_action()
	{
		$school_type = $this->_INPUT["school_type"] * 1;
		$school_name = htmlspecialchars(trim($this->_INPUT["school_name"]));
		$education_years = $this->_INPUT["education_years"] * 1;
		$departments = htmlspecialchars(trim($this->_INPUT["departments"]));
		
		if (empty($this->_INPUT['school_name']))
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "请输入学校名称"));
		}
		
		if (empty($this->_INPUT['departments']))
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "请输入院系"));
		}
		
		if ($this->_INPUT['education_years'] == "请选择")
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "请选择入学年份"));
		}
		
		if ($this->model('education')->add_education_experience($this->user_id, $school_type, $school_name, $education_years, $departments))
		{
			//添加积分
			$this->model('integral')->set_user_integral('FILL_EDU', $this->user_id, '你的个人设置补充了教育经历');
			
			H::ajax_json_output(GZ_APP::RSM(null, 1, "添加成功")); //格式化输出,并转为JSON进行处理
			exit();
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "添加失败"));
			exit();
		}
	
	}
	
	//修改教育经历 
	function edit_educationinfo_process_ajax_action()
	{
		$update_arr["school_type"] = $this->_INPUT["school_type"] * 1;
		$update_arr["school_name"] = htmlspecialchars(trim($this->_INPUT["school_name"]));
		$update_arr["education_years"] = $this->_INPUT["education_years"] * 1;
		$update_arr["departments"] = htmlspecialchars(trim($this->_INPUT["departments"]));
		
		$education_id = $this->_INPUT["eid"] * 1;
		
		$this->model('education')->update_education_experience($update_arr, $education_id, $this->user_id);
		
		H::ajax_json_output(GZ_APP::RSM(array(
			'url' => urlencode("?c=setting&act=educationinfo")
		), 1, '修改成功'));
		
	}
	
	// 删除教育经历
	function educationinfo_del_ajax_action()
	{
		$education_id = $this->_INPUT["eid"] * 1;
		
		if ($this->model('education')->del_education_experience($education_id, $this->user_id))
		{
			H::ajax_json_output(GZ_APP::RSM(null, 1, "删除成功")); //格式化输出,并转为JSON进行处理
			exit();
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "删除失败"));
			exit();
		}
	
	}

	/*************工作经历********************************************************************************************************/
	
	/**
	 * 工作经历
	 */
	
	function workinfo_action()
	{
		$work_experience_list = $this->model('work')->get_work_experience_list($this->user_id);
		
		$jobs_list_tmp = $this->model('work')->get_jos_list();
		
	
		$jobs_list[0] = "";
		
		foreach ($jobs_list_tmp as $row)
		{
			$jobs_list[$row['jobs_id']] = $row["jobs_name"];
		}
		
		
		foreach ($work_experience_list as $key => $val)
		{
		
			$work_experience_list[$key]['jobs_name'] = $jobs_list[$val['jobs_id']];
		}
		
		TPL::assign("jobs_list", $jobs_list);
		
		TPL::assign('work_experience_list', $work_experience_list);
		
		$year_end = date('Y') * 1;
		
		$year_array[0] = '';
		
		for ($tmp_i = $year_end; $tmp_i > 1900; $tmp_i --)
		{
			$year_array[$tmp_i] = $tmp_i;
		}
		
		TPL::assign('month_list', array(
			0 => '', 
			1 => 1, 
			2 => 2, 
			3 => 3, 
			4 => 4, 
			5 => 5, 
			6 => 6, 
			7 => 7, 
			8 => 8, 
			9 => 9, 
			10 => 10, 
			11 => 11, 
			12 => 12
		));
		
		TPL::assign('years_list', $year_array);
		
		$this->crumb('工作经历', '/account/?c=setting&act=workinfo');
		
		TPL::import_css(array(
			'css/discussion.css'
		));
		
		TPL::import_js(array(
			'js/ajaxupload.js', 
			'js/LocationSelect.js'
		));
		
		TPL::output("account/setting_workinfo_v2", true);
	}
	
	//添加工作经历 
	function add_workinfo_process_ajax_action()
	{
		$jobs_id = $this->_INPUT["jobs_id"] * 1;
		$company_name = htmlspecialchars($this->_INPUT["company_name"]);
		$province = $this->_INPUT["province"] * 1;
		$city = $this->_INPUT["city"] * 1;
		$district = $this->_INPUT["district"] * 1;
		
		$start_year = $this->_INPUT["start_year"] * 1;
		$start_month = $this->_INPUT["start_month"] * 1;
		$end_year = $this->_INPUT["end_year"] * 1;
		$end_month = $this->_INPUT["end_month"] * 1;
		
		if (!$company_name)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "请输入公司名称"));
		}
		
		if (!$province OR !$city OR !$district)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "请选择工作地点"));
		}
		
		if (!$start_year OR !$start_month OR !$end_year OR !$end_month)
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "请选择工作时间"));
		}
		
		if ($this->model('work')->add_work_experience($this->user_id, $start_year, $start_month, $end_year, $end_month, $company_name, '', $jobs_id, 0, $province, $city, $district))
		{
			//添加积分
			//$this->model("integral")->set_user_integral("FILL_CAREER", $this->user_id, "补充工作经历");
			
			H::ajax_json_output(GZ_APP::RSM(null, 1, "添加成功")); //格式化输出,并转为JSON进行处理
			exit();
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "添加失败"));
			exit();
		}
	
	}
	
	// 删除工作经历
	function workinfo_del_ajax_action()
	{
		$work_id = $this->_INPUT["wid"] * 1;
		
		if ($this->model('work')->del_work_experience($work_id, $this->user_id))
		{
			H::ajax_json_output(GZ_APP::RSM(null, 1, "删除成功")); //格式化输出,并转为JSON进行处理
			exit();
		}
		else
		{
			H::ajax_json_output(GZ_APP::RSM(null, '-1', "删除失败"));
			exit();
		}
	
	}

	function workinfo_edit_action()
	{
		$work_id = $this->_INPUT["wid"] * 1;
		
		$work_experience_row = $this->model('work')->get_work_experience_row($work_id, $this->user_id);
		
		if (! $work_experience_row)
		{
			H::js_pop_msg("连接出错");
		}
		
		TPL::assign('work_experience_row', $work_experience_row);
		
		$jobs_list_tmp = $this->model('work')->get_jos_list();
		
		$jobs_list[0] = '';
		
		foreach ($jobs_list_tmp as $row)
		{
			$jobs_list[$row['jobs_id']] = $row["jobs_name"];
		}
		
		TPL::assign("jobs_list", $jobs_list);
		TPL::assign("jobs_list_select", $work_experience_row["jobs_id"]);
		
		TPL::assign("start_year", $work_experience_row["start_year"]);
		TPL::assign("end_year", $work_experience_row["end_year"]);
		TPL::assign("start_month", $work_experience_row["start_month"]);
		TPL::assign("end_month", $work_experience_row["end_month"]);
		
		$year_end = date('Y') * 1;
		
		$year_array[0] = '';
		
		for ($tmp_i = $year_end; $tmp_i > 1900; $tmp_i --)
		{
			$year_array[$tmp_i] = $tmp_i;
		}
		
		TPL::assign('month_list', array(
			0 => '', 
			1 => 1, 
			2 => 2, 
			3 => 3, 
			4 => 4, 
			5 => 5, 
			6 => 6, 
			7 => 7, 
			8 => 8, 
			9 => 9, 
			10 => 10, 
			11 => 11, 
			12 => 12
		));
		
		TPL::assign('years_list', $year_array);
		
		$this->crumb('工作经历', '/account/?c=setting&act=workinfo');
		
		TPL::import_css(array(
			'css/discussion.css'
		));
		
		TPL::import_js(array(
			'js/ajaxupload.js', 
			'js/LocationSelect.js'
		));
		
		TPL::output("account/setting_workinfo_edit_v2", true);
	}
	
	//修改工作经历 
	function edit_workinfo_process_ajax_action()
	{
		$update_arr["jobs_id"] = $this->_INPUT["jobs_id"] * 1;
		$update_arr["company_name"] = htmlspecialchars(trim($this->_INPUT["company_name"]));
		$update_arr["province"] = $this->_INPUT["province"] * 1;
		$update_arr["city"] = $this->_INPUT["city"] * 1;
		$update_arr["district"] = $this->_INPUT["district"] * 1;
		
		$update_arr["start_year"] = $this->_INPUT["start_year"] * 1;
		$update_arr["start_month"] = $this->_INPUT["start_month"] * 1;
		$update_arr["end_year"] = $this->_INPUT["end_year"] * 1;
		$update_arr["end_month"] = $this->_INPUT["end_month"] * 1;
		
		$work_id = $this->_INPUT["wid"] * 1;
		
		$this->model('work')->update_work_experience($update_arr, $work_id, $this->user_id);
		
		H::ajax_json_output(GZ_APP::RSM(array('url' => '?c=setting&act=workinfo'), 1, "修改成功")); //格式化输出,并转为JSON进行处理
	}

	/**
	 * 帐号绑定
	 */
	function accountbind_action()
	{
		if (! $sina_row = $this->model('sina_weibo')->get_users_sina_by_uid($this->user_id))
		{
			$sina_weibo_bind = false;
		}
		else
		{
			$sina_weibo_bind = true;
			$sina_show_row[] = $sina_row;
			
			TPL::assign("sina_show_row", $sina_show_row);
		}
		
		if (! $qq_row = $this->model('qq_weibo')->get_users_qq_by_uid($this->user_id))
		{
			$qq_weibo_bind = false;
		}
		else
		{
			$qq_weibo_bind = true;
			$qq_show_row[] = $qq_row;
			
			TPL::assign("qq_show_row", $qq_show_row);
		}
		
		TPL::assign("sina_weibo_bind", $sina_weibo_bind);
		TPL::assign("qq_weibo_bind", $qq_weibo_bind);
		
		$this->crumb('微博绑定', '/account/?c=setting&act=accountbind');
		
		TPL::import_css(array(
			'css/discussion.css'
		));
		
		TPL::output("account/setting_accountbind_v2");
	}
}

