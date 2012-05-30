CREATE TABLE `[#DB_PREFIX#]active_tbl` (
  `active_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) DEFAULT '0',
  `expire_time` int(10) DEFAULT NULL,
  `active_code` varchar(30) DEFAULT NULL,
  `active_type` tinyint(1) DEFAULT NULL COMMENT '1.邮件激活 11找回密码 1121邮箱验证 22手机验证',
  `active_type_code` varchar(20) DEFAULT NULL,
  `active_values` varbinary(200) DEFAULT NULL,
  `add_time` int(12) DEFAULT NULL,
  `add_ip` bigint(12) DEFAULT NULL,
  `active_expire` tinyint(1) DEFAULT NULL,
  `active_time` int(12) DEFAULT NULL,
  `active_ip` bigint(12) DEFAULT NULL,
  PRIMARY KEY (`active_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]admin_group` (
  `group_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(50) NOT NULL COMMENT '组名',
  `menu` text NOT NULL COMMENT '可见栏目',
  `no_menu` text,
  `permission` text NOT NULL COMMENT '权限',
  `no_permission` text,
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]admin_menu` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL COMMENT '栏目父id',
  `sort` mediumint(6) NOT NULL COMMENT '排序',
  `title` varchar(100) NOT NULL COMMENT '栏目标题',
  `url` varchar(255) NOT NULL COMMENT '链接',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '开启关闭状态',
  `cname` varchar(20) DEFAULT NULL COMMENT '别名',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]answer` (
  `answer_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '回答id',
  `question_id` int(11) NOT NULL COMMENT '问题id',
  `answer_content` text COMMENT '回答内容',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `modify_time` int(10) DEFAULT '0',
  `against_count` int(11) NOT NULL DEFAULT '0' COMMENT '反对人数',
  `agree_count` int(11) NOT NULL DEFAULT '0' COMMENT '支持人数',
  `rating` float NOT NULL DEFAULT '0' COMMENT '权重评分',
  `uid` int(11) DEFAULT '0' COMMENT '发布问题用户ID',
  `comment_count` int(11) DEFAULT '0' COMMENT '评论总数',
  `uninterested_count` int(11) DEFAULT '0' COMMENT '不感兴趣',
  `thanks_count` int(11) DEFAULT '0' COMMENT '感谢数量',
  `category_id` int(11) DEFAULT '0' COMMENT '分类id',
  PRIMARY KEY (`answer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='回答';

CREATE TABLE `[#DB_PREFIX#]answer_attach` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) DEFAULT NULL COMMENT '附件名称',
  `access_key` varchar(32) DEFAULT NULL COMMENT '批次 Key',
  `add_time` int(10) DEFAULT '0' COMMENT '上传时间',
  `answer_id` int(11) DEFAULT '0' COMMENT '问题 ID',
  `file_location` varchar(255) DEFAULT NULL COMMENT '文件位置',
  `is_image` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]answer_comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `answer_id` int(11) DEFAULT '0',
  `uid` int(11) DEFAULT '0',
  `message` varchar(255) DEFAULT NULL,
  `time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]answer_thanks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `answer_id` int(11) DEFAULT '0',
  `user_name` varchar(255) DEFAULT NULL,
  `time` int(10) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]answer_uninterested` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `answer_id` int(11) DEFAULT '0',
  `user_name` varchar(255) DEFAULT NULL,
  `time` int(10) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]answer_useful` (
  `voter_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自动ID',
  `answer_id` int(11) DEFAULT NULL COMMENT '问题id',
  `vote_uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `vote_value` tinyint(4) NOT NULL COMMENT '-1无用 1 感谢',
  PRIMARY KEY (`voter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户感谢/没有用记录表';

CREATE TABLE `[#DB_PREFIX#]answer_vote` (
  `voter_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自动ID',
  `answer_id` int(11) DEFAULT NULL COMMENT '回复id',
  `answer_uid` int(11) DEFAULT NULL COMMENT '回复作者id',
  `vote_uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `vote_value` tinyint(4) NOT NULL COMMENT '-1反对 1 支持',
  PRIMARY KEY (`voter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]area_code` (
  `area_autoid` int(32) NOT NULL AUTO_INCREMENT COMMENT '地区代码自增ID',
  `area_code` int(11) NOT NULL COMMENT '地区代码',
  `area_parentcode` int(11) DEFAULT '0' COMMENT '父地区代码',
  `area_name` varchar(32) DEFAULT NULL COMMENT '省市名称',
  `area_zip_code` varchar(12) DEFAULT NULL COMMENT '邮政编码',
  `area_tel_code` int(11) DEFAULT '0' COMMENT '电话区号',
  `area_eng_code` varchar(6) DEFAULT NULL COMMENT '英文代码',
  `area_class_level` int(11) DEFAULT NULL COMMENT '等级类别',
  PRIMARY KEY (`area_code`),
  KEY `AK_areacode_autoid` (`area_autoid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='地区代码';

CREATE TABLE `[#DB_PREFIX#]category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type` varchar(16) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]draft` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `type` varchar(16) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `data` text,
  `time` int(10) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]education_experience` (
  `education_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户UID',
  `education_years` int(11) DEFAULT NULL COMMENT '入学年份',
  `school_code` int(11) DEFAULT NULL COMMENT '学校代码',
  `departments_code` int(11) DEFAULT NULL COMMENT '院系代码',
  `educational` tinyint(4) DEFAULT NULL COMMENT '学历',
  `school_name` varchar(50) DEFAULT NULL COMMENT '学校名',
  `school_type` tinyint(4) DEFAULT NULL COMMENT '学校类别',
  `departments` varchar(100) DEFAULT NULL COMMENT '院系',
  `add_time` int(11) DEFAULT NULL COMMENT '记录添加时间',
  PRIMARY KEY (`education_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='教育经历';

CREATE TABLE `[#DB_PREFIX#]invitation` (
  `invitation_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '激活ID',
  `uid` int(11) DEFAULT '0' COMMENT '用户ID',
  `invitation_code` varchar(50) DEFAULT NULL COMMENT '激活码',
  `invitation_email` varchar(100) DEFAULT NULL COMMENT '激活email',
  `add_time` int(12) DEFAULT NULL COMMENT '添加时间',
  `add_ip` bigint(12) DEFAULT NULL COMMENT '添加IP',
  `active_expire` tinyint(1) DEFAULT '0' COMMENT '激活过期',
  `active_time` int(12) DEFAULT NULL COMMENT '激活时间',
  `active_ip` bigint(12) DEFAULT NULL COMMENT '激活IP',
  `active_status` tinyint(4) DEFAULT '0' COMMENT '1已使用0未使用-1已删除',
  `active_uid` int(11) DEFAULT NULL,
  `object_type` tinyint(3) NOT NULL COMMENT '类型 1-问题 2-竞赛',
  `object_id` int(10) DEFAULT '0' COMMENT '对象ID',
  PRIMARY KEY (`invitation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jobs_id` int(11) NOT NULL COMMENT '职位ID',
  `jobs_name` varchar(50) DEFAULT NULL COMMENT '职位名',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]login` (
  `id` int(11) NOT NULL COMMENT '自增ID',
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `login_time` int(11) DEFAULT NULL COMMENT '登录时间',
  `login_type` tinyint(4) DEFAULT NULL COMMENT '登录类型',
  `login_ip` bigint(11) DEFAULT NULL COMMENT '登录IP',
  `login_rs` tinyint(4) DEFAULT NULL COMMENT '结果 1成功,-1失败,0,未知',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='登录历史表';

CREATE TABLE `[#DB_PREFIX#]mail_queue` (
  `queue_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from_email` varchar(100) NOT NULL,
  `to_email` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `from_name` varchar(100) DEFAULT NULL,
  `to_name` varchar(100) DEFAULT NULL,
  `add_time` int(10) DEFAULT NULL,
  `send_time` int(10) DEFAULT NULL,
  `state` tinyint(4) DEFAULT '0' COMMENT '0未发送 1已经发送',
  `result` varchar(255) DEFAULT NULL COMMENT '结果',
  `receive` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`queue_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='邮件队列表';

CREATE TABLE `[#DB_PREFIX#]notice` (
  `notice_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `sender_uid` int(11) DEFAULT NULL COMMENT '发送者ID',
  `dialog_id` int(11) DEFAULT NULL COMMENT '对话id',
  `notice_title` varchar(50) DEFAULT NULL COMMENT '标题',
  `notice_content` text COMMENT '内容',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `notice_type` tinyint(4) DEFAULT NULL COMMENT '0-普通消息10-系统发的消息，不能回复11-系统通知',
  PRIMARY KEY (`notice_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]notice_dialog` (
  `dialog_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '对话ID',
  `sender_uid` int(11) DEFAULT NULL COMMENT '发送者UID',
  `sender_unread` int(11) DEFAULT NULL COMMENT '发送者未读',
  `recipient_uid` int(11) DEFAULT NULL COMMENT '接收者UID',
  `recipient_unread` int(11) DEFAULT NULL COMMENT '接收者未读',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `last_time` int(11) DEFAULT NULL COMMENT '最后更新时间',
  `last_notice_id` int(11) DEFAULT NULL COMMENT '最后短消息ID',
  `sender_count` int(11) DEFAULT NULL COMMENT '发送者显示对话条数',
  `recipient_count` int(11) DEFAULT NULL COMMENT '接收者显示对话条数',
  `all_count` int(11) DEFAULT NULL COMMENT '总对话条数',
  PRIMARY KEY (`dialog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]notice_recipient` (
  `recipient_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `notice_id` int(11) DEFAULT NULL COMMENT '短信息ID',
  `dialog_id` int(11) DEFAULT NULL COMMENT '对话ID，由时间戳生成',
  `sender_uid` int(11) DEFAULT NULL COMMENT '发送者UID',
  `sender_time` int(11) DEFAULT NULL COMMENT '发送时间',
  `sender_del` tinyint(4) DEFAULT NULL COMMENT '发送者删除',
  `recipient_uid` int(11) DEFAULT NULL COMMENT '接收者UID',
  `recipient_time` int(11) DEFAULT NULL COMMENT '接收时间',
  `recipient_del` tinyint(4) DEFAULT NULL COMMENT '接收者删除',
  PRIMARY KEY (`recipient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]notification` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `sender_uid` int(11) DEFAULT NULL COMMENT '发送者ID',
  `action_type` int(4) DEFAULT NULL COMMENT '通知类别',
  `rel_id` int(11) DEFAULT NULL COMMENT '关联id',
  `model_type` smallint(11) NOT NULL DEFAULT '0' COMMENT '模块类型 1-问题 2-比赛 3-作品 4-人物 5-积分 6-金币',
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '问题或比赛ID',
  `data` text COMMENT '附加数据',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `notification_type` tinyint(4) DEFAULT NULL COMMENT '0-普通系统通知 1-用户之间产生的通知',
  PRIMARY KEY (`notification_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='系统通知';

CREATE TABLE `[#DB_PREFIX#]notification_recipient` (
  `recipient_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `notification_id` int(11) DEFAULT NULL COMMENT '通知ID',
  `recipient_uid` int(11) DEFAULT NULL COMMENT '接收者UID',
  `recipient_time` int(11) DEFAULT NULL COMMENT '接收时间',
  `recipient_del` tinyint(4) DEFAULT NULL COMMENT '接收者删除',
  PRIMARY KEY (`recipient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='通知接收者';

CREATE TABLE `[#DB_PREFIX#]question` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `question_content` varchar(200) NOT NULL COMMENT '问题内容',
  `question_detail` text COMMENT '问题说明',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `modify_time` int(11) DEFAULT NULL COMMENT '最后修改时间',
  `update_time` int(11) DEFAULT NULL,
  `published_uid` int(11) DEFAULT NULL COMMENT '发布用户UID',
  `modify_uid` int(11) DEFAULT NULL COMMENT '修改用户ID',
  `answer_count` int(11) DEFAULT NULL COMMENT '回答计数',
  `answer_users` int(11) DEFAULT '0' COMMENT '回答人数',
  `view_count` int(11) DEFAULT '0' COMMENT '浏览次数',
  `focus_count` int(11) DEFAULT '0' COMMENT '关注数',
  `comment_count` int(11) DEFAULT '0' COMMENT '评论数',
  `last_action` int(4) DEFAULT NULL COMMENT '最后操作类型',
  `last_uid` int(11) DEFAULT '0' COMMENT '上一动作用户ID',
  `action_history_id` int(11) DEFAULT '0' COMMENT '动作的记录表的关连id',
  `rewrite_url` varchar(200) DEFAULT NULL COMMENT '重定向地址',
  `point` int(11) NOT NULL DEFAULT '0' COMMENT '奖励积分',
  `prize_status` tinyint(3) NOT NULL COMMENT '问题积分奖励状态 0:未结帖 1:已结帖',
  `forbi_answer` tinyint(3) NOT NULL DEFAULT '0' COMMENT '禁止回答',
  `category_id` int(11) DEFAULT '0' COMMENT '分类 ID',
  `agree_count` int(11) DEFAULT '0' COMMENT '回复赞同数总和',
  `against_count` int(11) DEFAULT '0' COMMENT '回复反对数总和',
  PRIMARY KEY (`question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='问题列表';

CREATE TABLE `[#DB_PREFIX#]question_attach` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) DEFAULT NULL COMMENT '附件名称',
  `access_key` varchar(32) DEFAULT NULL COMMENT '批次 Key',
  `add_time` int(10) DEFAULT '0' COMMENT '上传时间',
  `question_id` int(11) DEFAULT '0' COMMENT '问题 ID',
  `file_location` varchar(255) DEFAULT NULL COMMENT '文件位置',
  `is_image` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]question_comment` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `source_id` int(11) DEFAULT '0',
  `comment_type` tinyint(2) NOT NULL COMMENT '评论类型: 1 - 问题, 2 - 回答, 11 - 话题',
  `comment_content` text NOT NULL COMMENT '评论内容',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `uid` int(11) DEFAULT '0' COMMENT '用户 ID',
  PRIMARY KEY (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='问题评论';

CREATE TABLE `[#DB_PREFIX#]question_focus` (
  `focus_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `question_id` int(11) DEFAULT NULL COMMENT '话题ID',
  `uid` int(11) DEFAULT NULL COMMENT '用户UID',
  `add_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`focus_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='问题关注表';

CREATE TABLE `[#DB_PREFIX#]question_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `modify_uid` int(11) NOT NULL COMMENT '用户id',
  `question_id` int(11) DEFAULT NULL COMMENT '操作ID',
  `question_content` varchar(255) DEFAULT NULL COMMENT '问题内容',
  `question_detial` text,
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `last_action` tinyint(4) NOT NULL DEFAULT '0' COMMENT '最后操作类型',
  PRIMARY KEY (`history_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='问题修改记录';

CREATE TABLE `[#DB_PREFIX#]question_invite` (
  `question_invite_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `question_id` int(11) NOT NULL COMMENT '问题ID',
  `sender_uid` int(11) NOT NULL,
  `recipients_uid` int(11) NOT NULL,
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `available_time` int(11) DEFAULT NULL COMMENT '生效时间',
  PRIMARY KEY (`question_invite_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='邀请问答';

CREATE TABLE `[#DB_PREFIX#]question_report` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '回答id',
  `question_id` int(11) NOT NULL COMMENT '问题id',
  `report_content` text COMMENT '回答内容',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `reply_time` int(11) DEFAULT NULL COMMENT '回复时间',
  `reply_conent` text COMMENT '回复内容',
  `uid` int(11) DEFAULT NULL COMMENT '举报问题用户ID',
  PRIMARY KEY (`report_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='问题举报';

CREATE TABLE `[#DB_PREFIX#]question_uninterested` (
  `interested_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `question_id` int(11) DEFAULT NULL COMMENT '话题ID',
  `uid` int(11) DEFAULT NULL COMMENT '用户UID',
  `add_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`interested_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='问题不感兴趣表';

CREATE TABLE `[#DB_PREFIX#]school` (
  `school_id` int(11) NOT NULL COMMENT '自增ID',
  `school_type` tinyint(4) DEFAULT NULL COMMENT '学校类型ID',
  `school_code` int(11) DEFAULT NULL COMMENT '学校编码',
  `school_name` varchar(50) DEFAULT NULL COMMENT '学校名称',
  `area_code` int(11) DEFAULT NULL COMMENT '地区代码',
  PRIMARY KEY (`school_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='学校';

CREATE TABLE `[#DB_PREFIX#]system_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `varname` varchar(30) NOT NULL COMMENT '字段类型',
  `desc` varchar(200) NOT NULL COMMENT '变量描述',
  `groupid` tinyint(3) NOT NULL DEFAULT '0' COMMENT '组别 1-综合 2-问题 3-竞赛 4-积分 5-金币',
  `sort` smallint(6) NOT NULL DEFAULT '0' COMMENT '组内排序',
  `type` varchar(10) DEFAULT NULL COMMENT '字段类型',
  `value` text COMMENT '变量值',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `varname` (`varname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='系统设置';

CREATE TABLE `[#DB_PREFIX#]topic` (
  `topic_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '话题id',
  `topic_title` varchar(100) DEFAULT NULL COMMENT '话题标题',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `add_uid` int(11) DEFAULT NULL COMMENT '添加的用户ID',
  `topic_count` int(11) DEFAULT NULL COMMENT '问题计数',
  `topic_description` text COMMENT '话题描述',
  `topic_pic` varchar(100) DEFAULT NULL COMMENT '话题图片',
  `topic_lock` tinyint(2) NOT NULL DEFAULT '0' COMMENT '话题是否锁定 1 锁定 0 未锁定',
  `topic_top` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否推荐到首页',
  `focus_count` int(11) DEFAULT '0' COMMENT '关注计数',
  `is_top` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否为顶级话题',
  `parent_id` int(10) DEFAULT NULL COMMENT '父话题ID',
  PRIMARY KEY (`topic_id`),
  UNIQUE KEY `topic_title` (`topic_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='话题';

CREATE TABLE `[#DB_PREFIX#]topic_experience` (
  `experience_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '话题经验ID',
  `topic_id` int(11) NOT NULL COMMENT '话题ID',
  `uid` int(11) NOT NULL COMMENT '所属用户',
  `experience_content` varchar(210) NOT NULL COMMENT '经验内容',
  `add_time` int(11) NOT NULL COMMENT '增加时间',
  PRIMARY KEY (`experience_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户话题经验表';

CREATE TABLE `[#DB_PREFIX#]topic_focus` (
  `focus_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `topic_id` int(11) DEFAULT NULL COMMENT '话题ID',
  `uid` int(11) DEFAULT NULL COMMENT '用户UID',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`focus_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='话题关注表';

CREATE TABLE `[#DB_PREFIX#]topic_question` (
  `topic_question_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `topic_id` int(11) DEFAULT '0' COMMENT '话题id',
  `question_id` int(11) DEFAULT '0' COMMENT '问题ID',
  `add_time` int(11) DEFAULT '0' COMMENT '添加时间',
  `uid` int(11) DEFAULT '0' COMMENT '用户ID',
  PRIMARY KEY (`topic_question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]topic_tree` (
  `topic_id` int(11) NOT NULL COMMENT '话题ID',
  `topic_child_id` int(11) NOT NULL COMMENT '父类话题ID',
  UNIQUE KEY `lnk_name` (`topic_id`,`topic_child_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='话题关联表';

CREATE TABLE `[#DB_PREFIX#]topic_uninterested` (
  `interested_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `topic_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `uid` int(11) DEFAULT NULL COMMENT '用户UID',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`interested_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='话题不感兴趣表';

CREATE TABLE `[#DB_PREFIX#]user_action_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int(11) NOT NULL COMMENT '用户id',
  `associate_type` int(4) DEFAULT NULL COMMENT '关联类型 1 问题 2回答 3评论 4话题 5比赛 6 作品',
  `associate_action` int(4) DEFAULT NULL COMMENT '操作类型',
  `associate_id` int(11) DEFAULT NULL COMMENT '关联ID',
  `associate_content` text COMMENT '附加内容',
  `associate_attached` text COMMENT '附加数据',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`history_id`),
  KEY `NewIndex1` (`associate_type`,`associate_action`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户操作记录';

CREATE TABLE `[#DB_PREFIX#]user_associate_index` (
  `associate_index_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增 ID',
  `uid` int(10) DEFAULT '0' COMMENT '用户ID',
  `associate_id` int(10) DEFAULT '0' COMMENT '关联ID',
  `associate_type` tinyint(2) DEFAULT '0' COMMENT '1 关注问题  3 发起的问题 ',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) DEFAULT '0' COMMENT '关联表最后动作更新时间',
  PRIMARY KEY (`associate_index_id`),
  UNIQUE KEY `NewIndex5` (`uid`,`associate_id`,`associate_type`),
  KEY `NewIndex1` (`uid`),
  KEY `NewIndex2` (`associate_type`),
  KEY `NewIndex3` (`add_time`),
  KEY `NewIndex4` (`update_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]user_follow` (
  `follow_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `fans_uid` int(11) DEFAULT NULL COMMENT '关注人的UID',
  `friend_uid` int(11) DEFAULT NULL COMMENT '被关注人的uid',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`follow_id`),
  UNIQUE KEY `NewIndex1` (`fans_uid`,`friend_uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户关注表';

CREATE TABLE `[#DB_PREFIX#]user_integral_day` (
  `integral_day_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `today_time` int(10) NOT NULL DEFAULT '0',
  `integral` int(10) DEFAULT '0',
  `uid` int(10) NOT NULL,
  PRIMARY KEY (`integral_day_id`),
  UNIQUE KEY `NewIndex1` (`today_time`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]user_integral_log` (
  `integral_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `action` varchar(20) DEFAULT NULL COMMENT '动作',
  `uid` int(10) DEFAULT '0',
  `category_id` int(10) DEFAULT '0' COMMENT '分类',
  `integral` int(10) DEFAULT '0' COMMENT '所得积分',
  `note` varchar(100) DEFAULT NULL COMMENT '备注',
  `new_integral` int(10) DEFAULT '0' COMMENT '新积分',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `client_ip` bigint(12) DEFAULT NULL COMMENT '客户IP',
  PRIMARY KEY (`integral_log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]user_uninterested` (
  `interested_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `uid` int(11) DEFAULT NULL COMMENT '用户UID',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`interested_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户不感兴趣表';

CREATE TABLE `[#DB_PREFIX#]users` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户的 UID',
  `user_name` varchar(50) DEFAULT NULL COMMENT '用户名',
  `email` varchar(50) DEFAULT NULL COMMENT 'EMAIL',
  `mobile` varchar(50) DEFAULT NULL COMMENT '用户手机',
  `telephone` varchar(50) DEFAULT NULL COMMENT '市话',
  `real_name` varchar(50) DEFAULT NULL COMMENT '真实姓名',
  `password` varchar(50) DEFAULT NULL COMMENT '用户密码',
  `salt` varchar(50) DEFAULT NULL COMMENT '用户附加混淆码',
  `user_status` tinyint(4) DEFAULT NULL COMMENT '用户状态 0未激活 1激活',
  `avatar_type` tinyint(4) DEFAULT NULL COMMENT '头像类型',
  `avatar_file` varchar(50) DEFAULT NULL COMMENT '头像文件',
  `sex` tinyint(4) DEFAULT NULL COMMENT '性别',
  `birthday` int(11) DEFAULT NULL COMMENT '用户生日',
  `country` int(11) DEFAULT NULL COMMENT '国家ID',
  `province` int(11) DEFAULT NULL COMMENT '省ID',
  `city` int(11) DEFAULT NULL COMMENT '市ID',
  `district` int(11) DEFAULT NULL COMMENT '区ID',
  `job_id` int(11) DEFAULT '0' COMMENT '职业ID',
  `reg_time` int(11) DEFAULT NULL COMMENT '注册时间',
  `reg_ip` bigint(12) DEFAULT NULL COMMENT '注册IP',
  `user_type` int(4) DEFAULT NULL COMMENT '用户类型 201 新浪',
  `referer_type` tinyint(4) DEFAULT NULL COMMENT '注册来源类别',
  `referer_url` varchar(100) DEFAULT NULL COMMENT '来源 URL',
  `last_login` int(10) DEFAULT '0' COMMENT '最后登录时间',
  `last_ip` bigint(12) DEFAULT NULL COMMENT '最后登录 IP',
  `online_time` int(11) DEFAULT '0' COMMENT '在线时间 (分钟)',
  `last_update` int(10) DEFAULT '0' COMMENT '资料最后修改时间',
  `url` varchar(100) DEFAULT NULL COMMENT '个性域名',
  `notification_unread` int(11) NOT NULL DEFAULT '0' COMMENT '未读系统通知',
  `notice_unread` int(11) NOT NULL DEFAULT '0' COMMENT '未读短信息',
  `inbox_recv` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-所有人可以发给我,1-我关注的人',
  `fans_count` int(10) NOT NULL DEFAULT '0' COMMENT '粉丝数',
  `friend_count` int(10) NOT NULL DEFAULT '0' COMMENT '观众数',
  `invite_count` int(11) NOT NULL DEFAULT '0' COMMENT '问我数量',
  `question_count` int(11) NOT NULL DEFAULT '0' COMMENT '问题总数',
  `answer_count` int(11) NOT NULL DEFAULT '0' COMMENT '回答问题数量',
  `edit_count` int(11) NOT NULL DEFAULT '0' COMMENT '编辑过的数量',
  `topic_count` int(11) NOT NULL DEFAULT '0' COMMENT '话题数量',
  `topic_focus_count` int(11) NOT NULL DEFAULT '0' COMMENT '话题关注数量',
  `invitation_available` int(11) NOT NULL DEFAULT '0' COMMENT '邀请名额',
  `group_id` smallint(5) DEFAULT '0' COMMENT '用户组 (普通用户 11,超级管理员 1,管理员 2)',
  `admin_id` smallint(5) DEFAULT '0' COMMENT '管理员 ID',
  `ext_group_ids` varchar(20) DEFAULT NULL COMMENT '附加用户组,多个用逗号分隔(,注册用户添加好友31)',
  `forbidden` tinyint(3) DEFAULT '0' COMMENT '是否禁止用户',
  `credit` int(11) DEFAULT '0' COMMENT '金币',
  `integral` int(11) DEFAULT '0' COMMENT '积分',
  `valid_email` tinyint(2) DEFAULT '0' COMMENT '邮箱验证',
  `valid_mobile` tinyint(2) DEFAULT '0' COMMENT '手机验证',
  `is_first_login` tinyint(1) DEFAULT '1' COMMENT '首次登录标记',
  `agree_count` int(11) DEFAULT '0' COMMENT '赞同数量',
  `thanks_count` int(11) DEFAULT '0' COMMENT '感谢数量',
  `views_count` int(11) DEFAULT '0' COMMENT '个人主页查看数量',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `NewIndex1` (`email`),
  UNIQUE KEY `NewIndex2` (`user_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]users_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `email` varchar(255) NOT NULL COMMENT '邮箱',
  `user_name` varchar(255) NOT NULL COMMENT '姓名',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `client_ip` bigint(12) NOT NULL COMMENT '客户端ip',
  `reason` text NOT NULL COMMENT '申请理由',
  `passed` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否通过审核',
  `invitation_id` int(11) DEFAULT NULL COMMENT '关联邀请id',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态 0:正常 1:不显示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='邀请码申请表';

CREATE TABLE `[#DB_PREFIX#]users_attrib` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(11) DEFAULT NULL COMMENT '用户UID',
  `introduction` varchar(255) DEFAULT NULL COMMENT '个人简介',
  `signature` varchar(255) DEFAULT NULL COMMENT '个人签名',
  `qq` varchar(50) DEFAULT NULL,
  `msn` varchar(50) DEFAULT NULL,
  `popular_email` varchar(50) DEFAULT NULL,
  `homepage` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户附加属性表';

CREATE TABLE `[#DB_PREFIX#]users_email_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '历史ID',
  `from_email` varchar(50) DEFAULT NULL COMMENT '发送邮箱',
  `to_email` varchar(50) DEFAULT NULL COMMENT '发往邮箱',
  `type` tinyint(3) NOT NULL COMMENT '操作类型',
  `title` varchar(100) DEFAULT NULL COMMENT '邮件标题',
  `body` text COMMENT '邮件正文',
  `from_name` varchar(50) DEFAULT NULL COMMENT '署名',
  `note` varchar(50) DEFAULT NULL COMMENT '注释',
  `add_ip` bigint(12) DEFAULT NULL COMMENT '添加IP',
  `user_name` varchar(50) DEFAULT NULL COMMENT '用户名',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `state` tinyint(1) DEFAULT NULL COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]users_email_setting` (
  `email_setting_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(11) NOT NULL,
  `sender_11` tinyint(4) DEFAULT NULL COMMENT '有人关注了我',
  `sender_12` tinyint(4) DEFAULT NULL COMMENT '有人问了我一个问题',
  `sender_13` tinyint(4) DEFAULT NULL COMMENT '有人邀请我回答一个问题',
  `sender_14` tinyint(4) DEFAULT NULL COMMENT '我关注的问题有了新回复',
  `sender_15` tinyint(4) DEFAULT NULL COMMENT '有人向我发送私信',
  PRIMARY KEY (`email_setting_id`),
  UNIQUE KEY `NewIndex1` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='邮件通知设定';

CREATE TABLE `[#DB_PREFIX#]users_email_verification` (
  `verification_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '激活ID',
  `uid` int(11) DEFAULT '0' COMMENT '用户ID',
  `verification_code` varchar(50) DEFAULT NULL COMMENT '激活码',
  `verification_email` varchar(100) DEFAULT NULL,
  `add_time` int(12) DEFAULT NULL COMMENT '添加时间',
  `add_ip` bigint(12) DEFAULT NULL COMMENT '添加IP',
  `active_expire` tinyint(1) DEFAULT '0' COMMENT '激活过期',
  `active_time` int(12) DEFAULT NULL COMMENT '激活时间',
  `active_ip` int(12) DEFAULT NULL COMMENT '激活IP',
  `active_status` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`verification_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]users_notification_setting` (
  `notice_setting_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(11) NOT NULL,
  `data` text COMMENT '设置数据',
  PRIMARY KEY (`notice_setting_id`),
  UNIQUE KEY `NewIndex1` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='通知设定';

CREATE TABLE `[#DB_PREFIX#]users_online` (
  `online_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(11) NOT NULL COMMENT '用户id',
  `last_active` int(11) NOT NULL COMMENT '上次活动时间',
  `ip` bigint(12) NOT NULL COMMENT '客户端ip',
  `active_url` varchar(255) NOT NULL COMMENT '停留页面',
  `user_agent` varchar(255) NOT NULL COMMENT '用户客户端信息',
  PRIMARY KEY (`online_id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='在线用户列表';

CREATE TABLE `[#DB_PREFIX#]users_qq` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户在本地的UID',
  `name` varchar(64) DEFAULT NULL COMMENT '微博尼称',
  `location` varchar(255) DEFAULT NULL COMMENT '地址',
  `gender` varchar(8) DEFAULT NULL COMMENT '性别,m--男，f--女,n--未知 ',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `access_token` varchar(64) DEFAULT NULL,
  `oauth_token_secret` varchar(64) DEFAULT NULL,
  `nick` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `NewIndex2` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]users_sina` (
  `id` int(11) NOT NULL COMMENT '新浪用户ID',
  `uid` int(11) NOT NULL COMMENT '用户在本地的UID',
  `name` varchar(64) DEFAULT NULL COMMENT '微博尼称',
  `location` varchar(255) DEFAULT NULL COMMENT '地址',
  `description` text COMMENT '个人描述',
  `url` varchar(255) DEFAULT NULL COMMENT '用户博客地址',
  `profile_image_url` varchar(255) DEFAULT NULL COMMENT 'SINA自定义头像地址',
  `domain` varchar(255) DEFAULT NULL COMMENT '用户个性化URL',
  `gender` varchar(8) DEFAULT NULL COMMENT '性别,m--男，f--女,n--未知 ',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `oauth_token` varchar(64) DEFAULT NULL,
  `oauth_token_secret` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]users_ucenter` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `uc_uid` int(11) DEFAULT '0',
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]work_experience` (
  `work_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `start_year` int(11) DEFAULT NULL COMMENT '开始年份',
  `start_month` int(11) DEFAULT NULL COMMENT '开始月份',
  `end_year` int(11) DEFAULT NULL COMMENT '结束年月',
  `end_month` int(11) DEFAULT NULL COMMENT '结束月份',
  `company_name` varchar(50) DEFAULT NULL COMMENT '公司名',
  `experience_description` varchar(255) DEFAULT NULL COMMENT '工作描述',
  `jobs_id` int(11) DEFAULT NULL COMMENT '职位ID',
  `country` int(11) DEFAULT NULL COMMENT '国家',
  `province` int(11) DEFAULT NULL COMMENT '省',
  `city` int(11) DEFAULT NULL COMMENT '市',
  `district` int(11) DEFAULT NULL COMMENT '区',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`work_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='工作经历';

INSERT INTO `[#DB_PREFIX#]admin_group`(`group_id`,`group_name`,`menu`,`no_menu`,`permission`,`no_permission`) VALUES
(1,'超级管理员','all',NULL,'all',NULL),
(2,'管理员','all',NULL,'all',NULL),
(3,'版主','1,2,3,4,5,6',NULL,'',NULL);

INSERT INTO `[#DB_PREFIX#]admin_menu` (`id`, `parent_id`, `sort`, `title`, `url`, `status`, `cname`) VALUES
(1, 0, 0, '面板首页', '', 0, NULL),
(2, 0, 0, '系统设置', '', 0, 'system'),
(3, 2, 0, '所有设置', '?c=setting&act=setting', 0, NULL),
(4, 0, 0, '问题管理', '', 0, 'question'),
(5, 0, 4, '话题管理', '', 0, 'topic'),
(25, 2, 0, '邮件服务器', '?c=setting&act=setting&group_id=6', 0, NULL),
(10, 4, 0, '问题列表', '?c=question&act=list_v2', 0, NULL),
(11, 5, 0, '话题列表', '?c=topic&act=list_v2', 0, NULL),
(12, 5, 0, '添加话题', '?c=topic&act=topic_add', 0, NULL),
(13, 0, 5, '会员管理', '', 0, 'user'),
(14, 13, 0, '会员列表', '?c=user&act=list_v2', 0, NULL),
(27, 26, 0, '清除缓存', '?c=maintain&act=cache', 0, NULL),
(19, 2, 0, '缓存设置', '?c=setting&act=setting&group_id=10', 0, NULL),
(23, 0, 0, '分类管理', '', 0, 'category'),
(24, 23, 0, '分类管理', '?c=category&act=list', 0, NULL),
(22, 13, 0, '在线会员', '?c=user&act=online_list', 0, NULL),
(26, 0, 6, '系统维护', '', 0, 'maintain');

INSERT INTO `[#DB_PREFIX#]category`(`title`,`type`) VALUES
('默认分类', 'question');

INSERT INTO `[#DB_PREFIX#]jobs` (`id`, `jobs_id`, `jobs_name`) VALUES
(1, 1, '销售'),
(2, 2, '市场/市场拓展/公关'),
(3, 3, '商务/采购/贸易'),
(4, 4, '计算机软、硬件/互联网/IT'),
(5, 5, '电子/半导体/仪表仪器'),
(6, 6, '通信技术'),
(7, 7, '客户服务/技术支持'),
(8, 8, '行政/后勤'),
(9, 9, '人力资源'),
(10, 10, '高级管理'),
(11, 11, '生产/加工/制造'),
(12, 12, '质控/安检'),
(13, 13, '工程机械'),
(14, 14, '技工'),
(15, 15, '财会/审计/统计'),
(16, 16, '金融/银行/保险/证券/投资'),
(17, 17, '建筑/房地产/装修/物业'),
(18, 18, '交通/仓储/物流'),
(19, 19, '普通劳动力/家政服务'),
(20, 20, '零售业'),
(21, 21, '教育/培训'),
(22, 22, '咨询/顾问'),
(23, 23, '学术/科研'),
(24, 24, '法律'),
(25, 25, '美术/设计/创意'),
(26, 26, '编辑/文案/传媒/影视/新闻'),
(27, 27, '酒店/餐饮/旅游/娱乐'),
(28, 28, '化工'),
(29, 29, '能源/矿产/地质勘查'),
(30, 30, '医疗/护理/保健/美容'),
(31, 31, '生物/制药/医疗器械'),
(32, 32, '翻译（口译与笔译）'),
(33, 33, '公务员'),
(34, 34, '环境科学/环保'),
(35, 35, '农/林/牧/渔业'),
(36, 36, '兼职/临时/培训生/储备干部'),
(37, 37, '在校学生'),
(38, 99, '其他');

INSERT INTO `[#DB_PREFIX#]topic` (`topic_title`, `topic_description`) VALUES('默认话题', '默认话题');