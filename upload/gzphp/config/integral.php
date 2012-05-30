<?php

	

//积分配置文件



$config["integral_perday_limit"] = 50;	//每人每天获取 最多的积分
	
$config["integral_rule"] = array (
			'INVITE_USER'		=>1,	// 邀请用户
			'FILL_AVATAR'		=>1,	//上传头像
			'VALID_MOBILE'		=>1,	//验证邮箱			
			'FILL_CONTACT'		=>1,	//补充联系方式
			'FILL_EDU'			=>1,	//补充教育经历
			'FILL_CAREER'		=>1,	//补充工作经历
			'BIND_WEIBO'		=>1,	//绑定微博
			'INVITE_QUESTION'	=>0.1,	//邀请用户并解决问题
			'SHARE'				=> 1,	//分享

	);
	
//每用户每个动作可或得积分的次数限制,不设置或0为不限制
$config["integral_rule_max"]=array (

			'FILL_AVATAR'		=>1,	//上传头像
			'VALID_MOBILE'		=>1,	//验证邮箱
			'FILL_CONTACT'		=>1,	//补充联系方式
			'FILL_EDU'			=>1,	//补充教育经历
			'FILL_CAREER'		=>1,	//补充工作经历
			'BIND_WEIBO'		=>1,	//绑定微博
	);
	
	

