ALTER TABLE xx_jill_leave_substitute MODIFY COLUMN `type` ENUM('daily','hour','swap') NOT NULL DEFAULT 'daily' COMMENT '代課類型 (daily:日薪 hour:鐘點 swap:補調課)';
UPDATE xx_jill_leave_substitute SET type='swap' WHERE sn=4;
