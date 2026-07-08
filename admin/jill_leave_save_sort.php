<?php
require_once __DIR__ . '/header.php';
header('HTTP/1.1 200 OK');
$xoopsLogger->activated = false;
$sort = 1;
foreach ($_POST['tr'] as $primary_keys) {
    list($sn) = explode('-',$primary_keys);
    $sql = "update `" . $xoopsDB->prefix("jill_leave") . "` set ``='{$sort}' where `sn`='{$sn}'";
    $xoopsDB->queryF($sql) or die(_TAD_SORT_FAIL . " (" . date("Y-m-d H:i:s") . ")");
    $sort++;
}
echo _TAD_SORTED . " (" . date("Y-m-d H:i:s") . ")";;
