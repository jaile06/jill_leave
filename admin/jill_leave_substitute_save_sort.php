<?php
require_once __DIR__ . '/header.php';
header('HTTP/1.1 200 OK');
$xoopsLogger->activated = false;
$sort = 1;
foreach ($_POST['tr'] as $primary_keys) {
    list($substitute_sn) = explode('-',$primary_keys);
    $sql = "update `" . $xoopsDB->prefix("jill_leave_substitute") . "` set ``='{$sort}' where `substitute_sn`='{$substitute_sn}'";
    $xoopsDB->queryF($sql) or die(_TAD_SORT_FAIL . " (" . date("Y-m-d H:i:s") . ")");
    $sort++;
}
echo _TAD_SORTED . " (" . date("Y-m-d H:i:s") . ")";;
