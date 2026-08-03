<?php
use Xmf\Request;
use XoopsModules\Jill_leave\Jill_leave;
use XoopsModules\Jill_leave\Jill_leave_class;

/*----------- 引入基礎設定與檔頭 -----------*/
require_once __DIR__ . '/header.php';

$op     = Request::getString('op');
$sn     = Request::getInt('sn');
$cateSn = Request::getInt('cate_sn');
$isSwap = Request::getInt('swap') > 0;

/* ===== 1. AJAX API 優先攔截（不載入佈景） ===== */
if ($op === 'update_status') {
    handleStatusUpdate($sn);
}

/* ===== 1-1. 補調課異動後節次與鐘點請假衝突檢查 AJAX API ===== */
if ($op === 'check_hour_conflict') {
    handleHourConflictCheck();
}

/* ===== 2. 載入佈景頁首 ===== */
$GLOBALS['xoopsOption']['template_main'] = 'jill_leave_index.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';

/* ===== 3. 權限驗證閘門 ===== */
$uid = Jill_leave::requireEditableUser();

/* ===== 4. 核心 CRUD 路由 (僅登入且非學生執行，回傳解析後的 $op 供 footer.php 載入樣板) ===== */
if ($uid > 0) {
    $op = handleAction($op, $sn, $cateSn, $uid, $isSwap);
} else {
    // 未通過權限驗證，清空 $op 防止透過 URL 直接載入受保護的子樣板
    $op = '';
}

/* ===== 5. 載入佈景頁尾 ===== */
require_once __DIR__ . '/footer.php';

/*=============================================================================
 * 私有輔助函式區
 *=============================================================================*/

/**
 * 處理 AJAX 審核狀態變更
 *
 * @param int $sn 請假單流水號
 */
function handleStatusUpdate(int $sn): void
{
    global $xoopsLogger, $xoopsSecurity;
    $xoopsLogger->activated = false;
    header('Content-Type: application/json; charset=utf-8');

    // 管理員權限檢查
    if (!Jill_leave::isAdmin()) {
        echo json_encode(['success' => false, 'message' => _MD_JILLLEAVE_NO_PERMISSION]);
        exit;
    }
    // CSRF Token 檢查
    if (!$xoopsSecurity->check(false)) {
        echo json_encode(['success' => false, 'message' => _MD_JILLLEAVE_TOKEN_ERROR]);
        exit;
    }

    $status = Request::getInt('status');
    // 審核狀態數值白名單驗證 (0: 待審, 1: 通過, 2: 退回)
    if (!in_array($status, [0, 1, 2], true)) {
        echo json_encode(['success' => false, 'message' => _MD_JILLLEAVE_TOKEN_ERROR]);
        exit;
    }

    $success = Jill_leave::update_status($sn, $status);
    echo json_encode([
        'success'     => $success,
        'status_text' => Jill_leave::status_text($status),
    ]);
    exit;
}

/**
 * 處理補調課異動後節次與鐘點請假衝突檢查 AJAX 請求
 *
 * 接收 POST 參數：
 * - swap_slots[]：格式 '日期|節次'
 * - exclude_sn：編輯模式排除自身假單 sn（可選）
 *
 * 回傳 JSON：{ conflicts: [{date, period, sn}, ...] }
 */
function handleHourConflictCheck(): void
{
    global $xoopsUser, $xoopsLogger;
    $xoopsLogger->activated = false;
    header('Content-Type: application/json; charset=utf-8');

    // 須為登入使用者
    if (empty($xoopsUser)) {
        echo json_encode(['conflicts' => []]);
        exit;
    }

    $uid = (int) $xoopsUser->uid();
    $exclude_sn = (int) ($_POST['exclude_sn'] ?? 0);
    $raw_slots = is_array($_POST['swap_slots'] ?? null) ? $_POST['swap_slots'] : [];

    // 解析 '日期|節次' 格式
    $swap_slots = [];
    foreach ($raw_slots as $slot_str) {
        $parts = explode('|', (string) $slot_str, 2);
        if (count($parts) === 2 && trim($parts[0]) !== '' && trim($parts[1]) !== '') {
            $swap_slots[] = ['date' => trim($parts[0]), 'period' => trim($parts[1])];
        }
    }

    $conflicts = Jill_leave_class::find_slot_conflicts($uid, $swap_slots, $exclude_sn);
    echo json_encode(['conflicts' => $conflicts]);
    exit;
}

/**
 * 執行 CRUD 業務邏輯路由
 *
 * @param string $op     動作名稱
 * @param int    $sn     請假單流水號
 * @param int    $cateSn 分類流水號
 * @param int    $uid    使用者 UID
 * @param bool   $isSwap 是否為補調課單入口
 * @return string 解析與確定執行的 $op 動作名稱
 */
function handleAction(string $op, int $sn, int $cateSn, int $uid, bool $isSwap = false): string
{
    global $xoopsSecurity;

    switch ($op) {
        // 新增請假單資料
        case 'jill_leave_store':
            $newSn = Jill_leave::store();
            Jill_leave::redirect("?sn={$newSn}");
            return $op;

        // 更新請假單資料
        case 'jill_leave_update':
            Jill_leave::update(['sn' => $sn]);
            Jill_leave::redirect("?sn={$sn}");
            return $op;

        // 顯示新增請假單表單
        case 'jill_leave_create':
            Jill_leave::create('', $cateSn, $isSwap);
            return 'jill_leave_create';

        // 顯示編輯請假單表單
        case 'jill_leave_edit':
            Jill_leave::create($sn);
            return 'jill_leave_create';

        // 刪除請假單
        case 'jill_leave_destroy':
            if (!$xoopsSecurity->check(false)) {
                redirect_header(XOOPS_URL . '/modules/jill_leave/index.php', 3, _MD_JILLLEAVE_TOKEN_ERROR);
                exit;
            }
            Jill_leave::destroy($sn);
            Jill_leave::redirect();
            return $op;

        // 顯示特定單筆請假單內容
        case 'jill_leave_show':
            Jill_leave::show(['sn' => $sn]);
            return 'jill_leave_show';

        // 請假單列表 (預設)
        default:
            if ($sn > 0) {
                Jill_leave::show(['sn' => $sn]);
                return 'jill_leave_show';
            }
            $where = ['uid' => $uid];
            if ($cateSn > 0) {
                $where['cate_sn'] = $cateSn;
            }
            Jill_leave::index($where, [], [], ['start_date' => 'DESC']);
            return 'jill_leave_index';
    }
}
