<?php


require_once dirname(dirname(__DIR__)) . '/mainfile.php';
// 核心邏輯：在 PHP 8.2+ 屏蔽 Smarty 等第三方套件產生的動態屬性宣告棄用 (deprecated) 警告
error_reporting(error_reporting() & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require_once "interface.php";
