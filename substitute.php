<?php
use Xmf\Request;
use XoopsModules\Jill_leave\Tools;
use XoopsModules\Jill_leave\Jill_leave;
use XoopsModules\Jill_leave\Jill_leave_substitute;

/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';

/*-----------變數過濾----------*/
$op = Request::getString('op');
$sn = Request::getInt('sn');
$substitute_sn = Request::getInt('substitute_sn');
//月份一律驗成 yyyy-mm，之後才能安全併進導轉網址（get_overview_data 也是同一條規則）
$month = Request::getString('month', date('Y-m'));
$month = preg_match('/^\d{4}-\d{2}$/', $month) ? $month : date('Y-m');

//僅管理者可存取代課總覽
Tools::chk_permission();

//匯出鐘點費清冊 Excel（例外流程：直接輸出檔案流，不走樣板）
if ($op == 'export_excel') {
    global $xoopsLogger;
    $xoopsLogger->activated = false;
    Jill_leave_substitute::export_excel($month);
    exit;
}

$GLOBALS['xoopsOption']['template_main'] = 'jill_leave_wrap.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';

/*-----------執行動作判斷區----------*/
switch ($op) {

    //刪除任意假單（連帶刪除代課與節次資料）
    case 'jill_leave_destroy':
        //CSRF 檢查（GET 連結帶 XOOPS_TOKEN_REQUEST）
        if (!$GLOBALS['xoopsSecurity']->check(false)) {
            redirect_header(XOOPS_URL . '/modules/jill_leave/substitute.php', 3, _MD_JILLLEAVE_TOKEN_ERROR);
        }
        Jill_leave::destroy($sn);
        header("Location: " . XOOPS_URL . "/modules/jill_leave/substitute.php?month=$month");
        exit;

    //刪除單筆代課資料（連帶節次明細）
    case 'jill_leave_substitute_destroy':
        //CSRF 檢查（GET 連結帶 XOOPS_TOKEN_REQUEST）
        if (!$GLOBALS['xoopsSecurity']->check(false)) {
            redirect_header(XOOPS_URL . '/modules/jill_leave/substitute.php', 3, _MD_JILLLEAVE_TOKEN_ERROR);
        }
        Jill_leave_substitute::destroy($substitute_sn);
        header("Location: " . XOOPS_URL . "/modules/jill_leave/substitute.php?month=$month");
        exit;

    //預設動作：依月份篩選全校請假/代課總覽
    case 'jill_leave_substitute_overview':
    default:
        Jill_leave_substitute::overview($month);
        $op = 'jill_leave_substitute_overview';
        break;
}

require_once __DIR__ . '/footer.php';
