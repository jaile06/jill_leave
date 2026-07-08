<?php

use XoopsModules\Jill_leave\Tools;
if (!class_exists('XoopsModules\Jill_leave\Tools')) {
    require XOOPS_ROOT_PATH . '/modules/jill_leave/preloads/autoloader.php';
}

Tools::get_session();
$interface_menu[_MD_JILLLEAVE_INDEX]="index.php";
$interface_icon[_MD_JILLLEAVE_INDEX]="fa-home";

//管理功能僅 leave_adm 可見
if (!empty($_SESSION['jill_leave_adm'])) {
    // 代理人設定：使用使用者代人或代理關係圖示 (fa-user-friends)
    $interface_menu[_MD_JILLLEAVE_SUBSTITUTE]="substitute.php";
    $interface_icon[_MD_JILLLEAVE_SUBSTITUTE]="fa-user-friends";
    // 參數設定：使用齒輪圖示 (fa-cog)
    $interface_menu[_MD_JILLLEAVE_CONFIG]="config.php";
    $interface_icon[_MD_JILLLEAVE_CONFIG]="fa-cog";
    // 類別管理：使用階層或清單/標籤圖示 (fa-list-alt 或 fa-tags)
    $interface_menu[_MD_JILLLEAVE_CATE]="cate.php";
    $interface_icon[_MD_JILLLEAVE_CATE]="fa-tags";
}

