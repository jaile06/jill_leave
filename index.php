<?php
use Xmf\Request;
use XoopsModules\Jill_leave\Jill_leave;

/*----------- 引入基礎設定與檔頭 -----------*/
require_once __DIR__ . '/header.php';

/*----------- HTTP 請求變數過濾與擷取 -----------*/
$op      = Request::getString('op');
$sn      = Request::getInt('sn');
$cate_sn = Request::getInt('cate_sn');

/*----------- AJAX 審核狀態即時更新處理 -----------*/
// 點選直接更新審核狀態 (AJAX) - 必須在載入 XOOPS 佈景主題前處理並輸出 JSON
if ($op === 'update_status') {
    global $xoopsLogger, $xoopsSecurity;
    $xoopsLogger->activated = false;
    header('Content-Type: application/json');

    // 權限檢查：僅管理者權限可變更審核狀態
    if (empty($_SESSION['jill_leave_adm'])) {
        echo json_encode(['success' => false, 'message' => _MD_JILLLEAVE_NO_PERMISSION]);
        exit;
    }

    // CSRF 安全 Token 檢查 (check(false) 避免清除 token 允許同頁多次切換)
    if (!$xoopsSecurity->check(false)) {
        echo json_encode(['success' => false, 'message' => _MD_JILLLEAVE_TOKEN_ERROR]);
        exit;
    }

    $status  = Request::getInt('status');
    $success = Jill_leave::update_status($sn, $status);

    echo json_encode([
        'success'     => $success,
        'status_text' => Jill_leave::status_text($status),
    ]);
    exit;
}

/*----------- 載入 XOOPS 系統前端頁首與樣板設定 -----------*/
$GLOBALS['xoopsOption']['template_main'] = 'jill_leave_index.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';

/*----------- 使用者身份與存取權限驗證 -----------*/
$uid       = $xoopsUser ? (int) $xoopsUser->uid() : 0;
$isStudent = $xoopsUser && in_array(4, $xoopsUser->getGroups(), true);

if (empty($xoopsUser)) {
    // 未登入者顯示登入提示
    $xoopsTpl->assign('show_login_alert', true);
} elseif ($isStudent) {
    // 學生群組 (ID: 4) 禁止使用本請假系統
    $xoopsTpl->assign('show_login_alert', false);
    $xoopsTpl->assign('show_student_alert', true);
} else {
    /*----------- 核心業務邏輯動作路由與處理 -----------*/
    switch ($op) {

        // 新增請假單資料 (含代課資訊)
        case 'jill_leave_store':
            $sn = Jill_leave::store();
            header("location: {$_SERVER['PHP_SELF']}?sn={$sn}");
            exit;

        // 更新請假單資料
        case 'jill_leave_update':
            Jill_leave::update(['sn' => $sn]);
            header("location: {$_SERVER['PHP_SELF']}?sn={$sn}");
            exit;

        // 顯示新增請假單表單
        case 'jill_leave_create':
            Jill_leave::create('', $cate_sn);
            break;

        // 顯示編輯請假單表單
        case 'jill_leave_edit':
            Jill_leave::create($sn);
            $op = 'jill_leave_create';
            break;

        // 刪除請假單 (含連帶刪除代課與節次資料)
        case 'jill_leave_destroy':
            if (!$GLOBALS['xoopsSecurity']->check(false)) {
                redirect_header($_SERVER['PHP_SELF'], 3, _MD_JILLLEAVE_TOKEN_ERROR);
            }
            Jill_leave::destroy($sn);
            header("location: {$_SERVER['PHP_SELF']}");
            exit;

        // 顯示特定單筆請假單內容
        case 'jill_leave_show':
            Jill_leave::show(['sn' => $sn]);
            break;

        // 請假單列表 (預設動作)
        case 'jill_leave_index':
        default:
            if (!empty($sn)) {
                Jill_leave::show(['sn' => $sn]);
                $op = 'jill_leave_show';
                break;
            }

            // 僅列出當前登入使用者的請假紀錄
            $where_arr = ['uid' => $uid];
            if (!empty($cate_sn)) {
                $where_arr['cate_sn'] = $cate_sn;
            }

            Jill_leave::index($where_arr, [], [], ['start_date' => 'DESC'], 20);
            $op = 'jill_leave_index';
            break;
    }
}

/*----------- 載入 XOOPS 系統前端頁尾 -----------*/
require_once __DIR__ . '/footer.php';
