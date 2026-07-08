CREATE TABLE `jill_leave` (
  `sn` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '請假編號',
  `uid` mediumint(8) unsigned NOT NULL COMMENT '請假者編號',
  `leavers` varchar(50) NOT NULL COMMENT '請假者姓名',
  `cate_sn` smallint(5) unsigned NOT NULL COMMENT '假別編號',
  `is_advisor` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否導師 (1:是 0:否)',
  `grade_class` varchar(50) NOT NULL COMMENT '導師班級',
  `start_date` date NOT NULL COMMENT '起始日期',
  `end_date` date NOT NULL COMMENT '結束日期',
  `status` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '審核狀態 (0:待審核 1:已通過 2:駁回)',
  `create_date` datetime NOT NULL COMMENT '申請時間',
  `update_date` datetime DEFAULT NULL COMMENT '最後更新時間',
  PRIMARY KEY (`sn`),
  KEY `idx_uid` (`uid`),
  KEY `idx_cate_sn` (`cate_sn`),
  KEY `idx_dates` (`start_date`, `end_date`),
  KEY `idx_status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='請假主表';

CREATE TABLE `jill_leave_cate` (
  `cate_sn` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '假別編號',
  `cate_title` varchar(100) NOT NULL COMMENT '假別名稱',
  `cate_sort` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT '假別排序',
  `enable` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '啟用狀態 (1:啟用 0:停用)',
  PRIMARY KEY (`cate_sn`),
  KEY `idx_sort` (`cate_sort`),
  KEY `idx_enable` (`enable`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='假別分類表';


CREATE TABLE `jill_leave_class` (
  `class_sn` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '節次編號',
  `substitute_sn` mediumint(8) unsigned NOT NULL COMMENT '關聯代課編號',
  `sn` mediumint(8) unsigned NOT NULL COMMENT '關聯請假編號',
  `class_period` varchar(20) NOT NULL COMMENT '節次 (例如: 1, 2, 早自習)',
  `subject` varchar(100) NOT NULL COMMENT '科目',
  `substitute_teacher` varchar(50) NOT NULL COMMENT '代課老師',
  PRIMARY KEY (`class_sn`),
  KEY `idx_substitute_sn` (`substitute_sn`),
  KEY `idx_sn` (`sn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='代課節次明細表';

CREATE TABLE `jill_leave_substitute` (
  `substitute_sn` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '代課編號',
  `sn` mediumint(8) unsigned NOT NULL COMMENT '關聯請假編號',
  `substitute_date` date NOT NULL COMMENT '代課日期',
  `pay` enum('self','school') NOT NULL DEFAULT 'self' COMMENT '支付方式 (self:自費 school:公費)',
  `type` enum('daily','hour') NOT NULL DEFAULT 'daily' COMMENT '代課類型 (daily:日薪 hour:鐘點)',
  PRIMARY KEY (`substitute_sn`),
  KEY `idx_sn` (`sn`),
  KEY `idx_date` (`substitute_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='代課日期表';


INSERT INTO `jill_leave_cate` (`cate_title`, `cate_sort`, `enable`) VALUES
('事假', 1, 1),
('病假', 2, 1),
('公假', 3, 1),
('婚假', 4, 1),
('喪假', 5, 1),
('產假', 6, 1);