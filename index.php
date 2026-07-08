<?php
use Xmf\Request;
use XoopsModules\Jill_leave\Jill_leave;
use XoopsModules\Jill_leave\Jill_leave_class;

/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';

/*-----------變數過濾----------*/
$op = Request::getString('op');
$sn = Request::getInt('sn');
$cate_sn = Request::getInt('cate_sn');
$class_sn = Request::getInt('class_sn');

//點選直接更新審核狀態 (AJAX) - 必須在載入佈景與標頭前先處理並結束以回傳乾淨的 JSON
if ($op === 'update_status') {
    //僅管理員可更新狀態
    if (empty($_SESSION['jill_leave_adm'])) {
        echo json_encode(['success' => false, 'message' => _MD_JILLLEAVE_NO_PERMISSION]);
        exit;
    }
    $status = Request::getInt('status');
    $success = Jill_leave::update_status($sn, $status);
    echo json_encode(['success' => $success, 'status_text' => Jill_leave::status_text($status)]);
    exit;
}

$GLOBALS['xoopsOption']['template_main'] = 'jill_leave_index.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';

// 未登入警示（不跳轉）
if (empty($xoopsUser)) {
    $xoopsTpl->assign('show_login_alert', true);
    $op = ''; // 未登入不執行任何 action，不載入子樣板
} elseif (in_array(4, $xoopsUser->getGroups(), true)) {
    // 限制學生群組 (群組 ID 4) 不能使用請假模組
    $xoopsTpl->assign('show_login_alert', false);
    $xoopsTpl->assign('show_student_alert', true);
    $op = '';
}

/*-----------執行動作判斷區----------*/
if (!empty($xoopsUser) && !in_array(4, $xoopsUser->getGroups(), true)) {
    switch ($op) {

        //新增資料（含代課資訊批次儲存）
        case 'jill_leave_store':
            $sn = Jill_leave::store();
            header("location: {$_SERVER['PHP_SELF']}?sn=$sn");
            exit;

        //更新資料（含代課資訊批次儲存）
        case 'jill_leave_update':
            $where_arr['sn'] = $sn;
            Jill_leave::update($where_arr);
            header("location: {$_SERVER['PHP_SELF']}?sn=$sn");
            exit;

        //新增用表單
        case 'jill_leave_create':
            Jill_leave::create('', $cate_sn);
            break;

        //修改用表單
        case 'jill_leave_edit':
            Jill_leave::create($sn);
            $op = 'jill_leave_create';
            break;

        //刪除資料（連帶刪除代課與節次資料）
        case 'jill_leave_destroy':
            Jill_leave::destroy($sn);
            header("location: {$_SERVER['PHP_SELF']}");
            exit;

        //列出所有資料
        case 'jill_leave_index':
        default:
            if (!empty($sn)) {
                $where_arr['sn'] = $sn;
                Jill_leave::show($where_arr);
                $op = 'jill_leave_show';
                break;
            }

            //管理者可看全部，一般使用者僅列出個人請假紀錄
            $where_arr = [];
            if (empty($_SESSION['jill_leave_adm'])) {
                $now_uid = isset($xoopsUser) && is_object($xoopsUser) ? (int) $xoopsUser->uid() : 0;
                $where_arr['uid'] = $now_uid;
            }
            if (!empty($cate_sn)) {
                $where_arr['cate_sn'] = $cate_sn;
            }
            Jill_leave::index($where_arr, [], [], ['start_date' => 'DESC'], 20);
            $op = 'jill_leave_index';
            break;

        //顯示某筆資料
        case 'jill_leave_show':
            $where_arr['sn'] = $sn;
            Jill_leave::show($where_arr);
            break;
    }
}

require_once __DIR__ . '/footer.php';
