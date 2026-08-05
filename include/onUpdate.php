<?php

function xoops_module_update_jill_leave($module, $old_version)
{
    global $xoopsDB;

    // jill_leave_cate：新增支付方式鎖定欄位
    $table = $xoopsDB->prefix('jill_leave_cate');
    $result = $xoopsDB->query("SHOW COLUMNS FROM `{$table}` LIKE 'force_pay'");
    if ($result && $xoopsDB->getRowsNum($result) === 0) {
        $xoopsDB->queryF("ALTER TABLE `{$table}` ADD COLUMN `force_pay` enum('','self','school') NOT NULL DEFAULT '' COMMENT '鎖定支付方式（空:不鎖定 self:自費 school:公費）'");
    }

    // jill_leave_class.subject：varchar(100) 改 text，容納 JSON（含班級/科目/處理方式）
    $table = $xoopsDB->prefix('jill_leave_class');
    $result = $xoopsDB->query("SHOW FULL COLUMNS FROM `{$table}` LIKE 'subject'");
    if ($result && ($col = $xoopsDB->fetchArray($result)) && stripos($col['Type'], 'text') === false) {
        $xoopsDB->queryF("ALTER TABLE `{$table}` MODIFY COLUMN `subject` text NOT NULL COMMENT '科目（JSON 格式：含班級、科目、處理方式等）'");
    }

    // jill_leave_substitute.type：enum 新增 swap（補調課）
    $table = $xoopsDB->prefix('jill_leave_substitute');
    $result = $xoopsDB->query("SHOW FULL COLUMNS FROM `{$table}` LIKE 'type'");
    if ($result && ($col = $xoopsDB->fetchArray($result)) && stripos($col['Type'], 'swap') === false) {
        $xoopsDB->queryF("ALTER TABLE `{$table}` MODIFY COLUMN `type` enum('daily','hour','swap') NOT NULL DEFAULT 'daily' COMMENT '代課類型 (daily:日薪 hour:鐘點 swap:補調課)'");
    }
    // 舊版 enum 未含 swap 時寫入的資料被截斷，補回正確類型
    $xoopsDB->queryF("UPDATE `{$table}` SET `type` = 'swap' WHERE `sn` = 4 AND `type` != 'swap'");

    return true;
}
