<?php


require __DIR__ . '/header.php';
//xoops_cp_header();


$adminObject = \Xmf\Module\Admin::getInstance();

$adminObject->displayNavigation(basename(__FILE__));
$adminObject::setPaypal('xoopsfoundation@gmail.com');

// 修正 Xmf Admin displayAbout 排版錯置（#about label 浮動問題）
echo '<style>#about label, #about text { float: none; display: inline-block; width: auto; text-align: left; padding-right: 0; }</style>';

$adminObject->displayAbout(false);

require __DIR__ . '/footer.php';
