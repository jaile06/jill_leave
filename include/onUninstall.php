<?php


function xoops_module_uninstall_jill_leave($module)
{
    global $xoopsDB;
    $date = date("Ymd");

    rename(XOOPS_ROOT_PATH . "/uploads/jill_leave", XOOPS_ROOT_PATH . "/uploads/jill_leave_bak_{$date}");

    return true;
}
