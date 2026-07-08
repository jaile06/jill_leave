<?php

use XoopsModules\Tadtools\Utility;

/*-----------秀出結果區--------------*/
$xoopsTpl->assign('toolbar', Utility::toolbar_bootstrap($interface_menu, false, $interface_icon));
$xoopsTpl->assign('now_op', $op);
$xoTheme->addStylesheet('modules/jill_leave/css/module.css?t=' . time());
$xoTheme->addStylesheet('modules/tadtools/css/vtb.css?t=' . time());
require_once XOOPS_ROOT_PATH . '/footer.php';
