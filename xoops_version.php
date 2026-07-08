<?php


$modversion = [];

//---模組基本資訊---//
$modversion['name']        = _MI_JILLLEAVE_NAME;
$modversion['version']     = $_SESSION['xoops_version'] >= 20511 ? '1.0.0-Stable' : '1.0';
$modversion['description'] = _MI_JILLLEAVE_DESC;
$modversion['author']      = _MI_JILLLEAVE_AUTHOR;
$modversion['credits']     = _MI_JILLLEAVE_CREDITS;
$modversion['help']        = 'page=help';
$modversion['license']     = 'GPL see LICENSE';
$modversion['image']       = "images/logo.png";
$modversion['dirname']     = basename(__DIR__);

//---模組狀態資訊---//
$modversion['release_date']        = '2026-07-14';
$modversion['module_website_url']  = 'https://github.com/jaile06';
$modversion['module_website_name'] = _MI_JILLLEAVE_AUTHOR_WEB;
$modversion['module_status']       = 'release';
$modversion['author_website_url']  = 'https://github.com/jaile06';
$modversion['author_website_name'] = _MI_JILLLEAVE_AUTHOR_WEB;
$modversion['min_php']             = '8.0';
$modversion['min_xoops']           = '2.5';

//---paypal資訊---//
$modversion['paypal'] = [
    'business' => 'tnjaile@gmail.com',
    'item_name' => 'Donation : ' . _MI_JILLLEAVE_AUTHOR,
    'amount' => 0,
    'currency_code' => 'USD',
];

//---安裝設定---//
$modversion['onInstall']   = "include/onInstall.php";
$modversion['onUpdate']    = "include/onUpdate.php";
$modversion['onUninstall'] = "include/onUninstall.php";



//---資料表架構---//
$modversion['sqlfile']['mysql'] = "sql/mysql.sql";
$modversion['tables'] = ['jill_leave', 'jill_leave_cate', 'jill_leave_class', 'jill_leave_substitute'];


//---後台使用系統選單---//
$modversion['system_menu'] = 1;

//---後台管理介面設定---//
$modversion['hasAdmin']   = 1;
$modversion['adminindex'] = 'admin/main.php';
$modversion['adminmenu']  = 'admin/menu.php';

//---前台主選單設定---//
$modversion['hasMain'] = 1;
$modversion['sub'][] = [
    ['name' => _MI_JILLLEAVE_INDEX, 'url'=> 'index.php'],
    ['name' => _MI_JILLLEAVE_CATE, 'url'=> 'cate.php'],
    ['name' => _MI_JILLLEAVE_SUBSTITUTE, 'url'=> 'substitute.php'],
    ['name' => _MI_JILLLEAVE_CONFIG, 'url'=> 'config.php'],
];


//---樣板設定---//
$modversion['templates'] = [
    ['file' => 'jill_leave_admin.tpl', 'description' => 'jill_leave_admin.tpl'],
    ['file' => 'jill_leave_index.tpl', 'description' => 'jill_leave_index.tpl'],
    ['file' => 'jill_leave_cate.tpl', 'description' => 'jill_leave_cate.tpl'],
    ['file' => 'jill_leave_substitute.tpl', 'description' => 'jill_leave_substitute.tpl'],
    ['file' => 'jill_leave_adm.tpl', 'description' => 'jill_leave_adm.tpl'],
];


//---區塊設定---//
$modversion['blocks'] = [
    [
        'file' => 'jill_leave_show.php',
        'name' => _MI_JILL_LEAVE_SHOW_BLOCK_NAME,
        'description' => _MI_JILL_LEAVE_SHOW_BLOCK_DESC,
        'show_func' => 'jill_leave_show',
        'edit_func' => 'jill_leave_show_edit',
        'options' => '10',
        'template' => 'jill_leave_show.tpl',
    ],
];

$modversion['config'][] = [
    'name' => 'adm_email',
    'title' => '_MI_JILLLEAVE_ADM_EMAIL',
    'description' => '_MI_JILLLEAVE_ADM_EMAIL_DESC',
    'formtype' => 'textbox',
    'valuetype' => 'text',
    'default' => '',
];

$modversion['config'][] = [
    'name' => 'grade',
    'title' => '_MI_JILLLEAVE_GRADE',
    'description' => '_MI_JILLLEAVE_GRADE_DESC',
    'formtype' => 'json',
    'valuetype' => '',
    'default' => '1,2,3,4,5,6',
    'options' => [1,2,3,4,5,6],
];
$modversion['config'][] = [
    'name' => 'class_room',
    'title' => '_MI_JILLLEAVE_CLASS_ROOM',
    'description' => '_MI_JILLLEAVE_CLASS_ROOM_DESC',
    'formtype' => 'textbox',
    'valuetype' => 'int',
    'default' => '10',
];

$modversion['config'][] = [
    'name' => 'class_period',
    'title' => '_MI_JILLLEAVE_CLASS_PERIOD',
    'description' => '_MI_JILLLEAVE_CLASS_PERIOD_DESC',
    'formtype' => 'textbox',
    'valuetype' => 'text',
    'default' => _MI_JILLLEAVE_CLASS_PERIOD_DEFAULT,
];