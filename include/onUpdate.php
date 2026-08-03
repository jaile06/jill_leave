<?php

function xoops_module_update_jill_leave($module, $old_version)
{
    global $xoopsDB;

    $table = $xoopsDB->prefix('jill_leave_cate');
    $result = $xoopsDB->query("SHOW COLUMNS FROM `{$table}` LIKE 'force_pay'");
    if ($result && $xoopsDB->getRowsNum($result) === 0) {
        $xoopsDB->queryF("ALTER TABLE `{$table}` ADD COLUMN `force_pay` enum('','self','school') NOT NULL DEFAULT '' COMMENT '鎖定支付方式（空:不鎖定 self:自費 school:公費）'");
    }

    return true;
}
