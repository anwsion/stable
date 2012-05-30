<?php

$config['action_details'] = array
	(
		notify_class::TYPE_PEOPLE_FOCUS => array(
			'user_setting' => 1,
			'desc' => "有人关注了我",
		),
		
		notify_class::TYPE_COMMENT_QUESTION => array(
			'user_setting' => 1,
			'desc' => "我关注的问题有了新的回复",
		),
		
		notify_class::TYPE_COMMENT_BE_REPLY => array(
			'user_setting' => 1,
			'desc' => "我的回复被评论",
		),
		
		notify_class::TYPE_INVITE_QUESTION => array(
			'user_setting' => 1,
			'desc' => "有人邀请我回答问题",
		),
		
		notify_class::TYPE_CONTEXT => array(
			'user_setting' => 0,
			'desc' => "文字通知",
		),
	);

?>