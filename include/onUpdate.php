<?php
use XoopsModules\Tadtools\Utility;
use XoopsModules\Jill_leave\Update;

if (!class_exists('XoopsModules\Tadtools\Utility')) {
    require XOOPS_ROOT_PATH . '/modules/tadtools/preloads/autoloader.php';
}
if (!class_exists('XoopsModules\Jill_leave\Update')) {
    require dirname(__DIR__) . '/preloads/autoloader.php';
}



function xoops_module_update_jill_leave($module, $old_version)
{
    global $xoopsDB;

    //if(Update::chk_1()) Update::go_1();

    return true;
}
