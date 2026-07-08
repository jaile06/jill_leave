<?php
use XoopsModules\Tadtools\Utility;
if (!class_exists('XoopsModules\Tadtools\Utility')) {
    require XOOPS_ROOT_PATH . '/modules/tadtools/preloads/autoloader.php';
}



function xoops_module_install_jill_leave(&$module)
{

    Utility::mk_dir(XOOPS_ROOT_PATH . "/uploads/jill_leave");
    Utility::mk_dir(XOOPS_ROOT_PATH . "/uploads/jill_leave/file");
    Utility::mk_dir(XOOPS_ROOT_PATH . "/uploads/jill_leave/image");
    Utility::mk_dir(XOOPS_ROOT_PATH . "/uploads/jill_leave/image/.thumbs");

    return true;
}
