<?php

use XoopsModules\Tadtools\Utility;

/*-----------秀出結果區--------------*/
$xoopsTpl->assign('toolbar', Utility::toolbar_bootstrap($interface_menu, false, $interface_icon));
$xoopsTpl->assign('now_op', $op);

//以模組版本號破快取：用 time() 會讓瀏覽器每次重抓，等於沒有快取
$ver = isset($GLOBALS['xoopsModule']) && is_object($GLOBALS['xoopsModule']) ? $GLOBALS['xoopsModule']->getVar('version') : '1';
$xoopsTpl->assign('asset_ver', $ver);

$xoTheme->addStylesheet("modules/jill_leave/css/module.css?v={$ver}");
$xoTheme->addStylesheet("modules/tadtools/css/vtb.css?v={$ver}");
$xoTheme->addScript("modules/jill_leave/js/jill_leave.js?v={$ver}");
require_once XOOPS_ROOT_PATH . '/footer.php';
