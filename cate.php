<?php
use Xmf\Request;
use XoopsModules\Jill_leave\Tools;
use XoopsModules\Jill_leave\Jill_leave_cate;

/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';

/*-----------變數過濾----------*/
$op = Request::getString('op');
$cate_sn = Request::getInt('cate_sn');

//僅 leave_adm 可存取假別分類管理
Tools::chk_is_adm('', '', __FILE__, __LINE__);

//AJAX 拖曳排序（直接輸出訊息，不走樣板）
if ($op == 'update_sort') {
    global $xoopsLogger;
    $xoopsLogger->activated = false;
    echo Jill_leave_cate::update_sort();
    exit;
}

$GLOBALS['xoopsOption']['template_main'] = 'jill_leave_wrap.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';

/*-----------執行動作判斷區----------*/
switch ($op) {

    //點擊切換狀態
    case 'update_enable':
        Jill_leave_cate::update_enable($cate_sn);
        header("location: {$_SERVER['PHP_SELF']}");
        exit;

    //新增資料
    case 'jill_leave_cate_store':
        $cate_sn = Jill_leave_cate::store();
        header("location: {$_SERVER['PHP_SELF']}?cate_sn=$cate_sn");
        exit;

    //更新資料
    case 'jill_leave_cate_update':
        $where_arr['cate_sn'] = $cate_sn;
        Jill_leave_cate::update($where_arr);
        header("location: {$_SERVER['PHP_SELF']}?cate_sn=$cate_sn");
        exit;

    //新增用表單
    case 'jill_leave_cate_create':
        Jill_leave_cate::create();
        break;

    //修改用表單
    case 'jill_leave_cate_edit':
        Jill_leave_cate::create($cate_sn);
        $op = 'jill_leave_cate_create';
        break;

    //刪除資料
    case 'jill_leave_cate_destroy':
        Jill_leave_cate::destroy($cate_sn);
        header("location: {$_SERVER['PHP_SELF']}");
        exit;

    //顯示某筆資料
    case 'jill_leave_cate_show':
        $where_arr['cate_sn'] = $cate_sn;
        Jill_leave_cate::show($where_arr);
        break;

    //預設動作：列出所有假別（依排序）
    case 'jill_leave_cate_index':
    default:
        if (empty($cate_sn)) {
            Jill_leave_cate::index([], [], [], ['cate_sort' => 'ASC']);
            $op = 'jill_leave_cate_index';
        } else {
            $where_arr['cate_sn'] = $cate_sn;
            Jill_leave_cate::show($where_arr);
            $op = 'jill_leave_cate_show';
        }
        break;
}

require_once __DIR__ . '/footer.php';
