<?php
namespace XoopsModules\Jill_leave;

use XoopsModules\Tadtools\SweetAlert;
use XoopsModules\Tadtools\Utility;
use XoopsModules\Tadtools\BootstrapTable;
use XoopsModules\Tadtools\FancyBox;
use XoopsModules\Jill_leave\Tools;
use XoopsModules\Jill_leave\Jill_leave_cate;
use XoopsModules\Jill_leave\Jill_leave_substitute;
use XoopsModules\Jill_leave\Jill_leave_class;
use XoopsModules\Tadtools\FormValidator;
use XoopsModules\Tadtools\My97DatePicker;

class Jill_leave
{
    // 過濾用變數的設定
    public static $filter_arr = [
        'int' => ['sn', 'uid', 'cate_sn', 'is_advisor', 'status'], //數字類的欄位
        'html' => [], //含網頁語法的欄位（所見即所得的內容）
        'text' => [], //純大量文字欄位
        'json' => [], //內容為 json 格式的欄位
        'pass' => ['files'], //不予過濾的欄位
        'explode' => [], //用分號隔開的欄位
    ];

    //審核狀態文字
    public static function status_text($status = 0)
    {
        return match ((int) $status) {
            1 => _MD_JILLLEAVE_STATUS_1,
            2 => _MD_JILLLEAVE_STATUS_2,
            default => _MD_JILLLEAVE_STATUS_0,
        };
    }

    //列出所有 jill_leave 資料 Jill_leave::index()
    public static function index($where_arr = [], $other_arr = [], $view_cols = [], $order_arr = [], $amount = '')
    {
        global $xoopsTpl, $xoTheme;

        if ($amount) {
            list($all_jill_leave, $total, $bar) = self::get_all($where_arr, $other_arr, $view_cols, $order_arr, null, null, 'read', $amount);
            $xoopsTpl->assign('bar', $bar);
            $xoopsTpl->assign('total', $total);
        } else {
            $all_jill_leave = self::get_all($where_arr, $other_arr, $view_cols, $order_arr);
        }

        $xoopsTpl->assign('all_jill_leave', $all_jill_leave);
        Utility::test($all_jill_leave, 'all_jill_leave');

        //CSRF token（GET 刪除連結與 AJAX 狀態切換共用，不清除以供同頁多次操作）
        $token = $GLOBALS['xoopsSecurity']->createToken();
        $xoopsTpl->assign('csrf_token', $token);

        //刪除確認的JS
        $SweetAlert = new SweetAlert();
        $SweetAlert->render('jill_leave_destroy_func', "{$_SERVER['PHP_SELF']}?op=jill_leave_destroy&XOOPS_TOKEN_REQUEST={$token}&sn=", "sn");

        BootstrapTable::render();

        $fancybox = new FancyBox('.fancybox_jill_leave_sn');
        $fancybox->render();
    }

    //取得 jill_leave 所有資料陣列 Jill_leave::get_all()
    public static function get_all($where_arr = [], $other_arr = [], $view_cols = [], $order_arr = [], $key_name = false, $get_value = '', $filter = 'read', $amount = '')
    {
        global $xoopsDB;

        $and_sql = Tools::get_and_where($where_arr);
        $view_col = Tools::get_view_col($view_cols);
        $order_sql = Tools::get_order($order_arr);
        $order = $amount ? '' : $order_sql;

        $sql = "SELECT {$view_col} FROM `" . $xoopsDB->prefix("jill_leave") . "` WHERE 1 {$and_sql} {$order}";

        // Utility::getPageBar($原sql語法, 每頁顯示幾筆資料, 最多顯示幾個頁數選項);
        if ($amount) {
            $PageBar = Utility::getPageBar($sql, $amount, 10, '', '', $_SESSION['bootstrap'], 'g2p', $order_sql);
            $bar = $PageBar['bar'];
            $sql = $PageBar['sql'];
            $total = $PageBar['total'];
        }

        //假別名稱對照表
        $cate_title_arr = Jill_leave_cate::get_all([], [], ['cate_sn', 'cate_title'], [], 'cate_sn', 'cate_title');

        $result = $xoopsDB->query($sql) or Utility::web_error($sql);
        $data_arr = [];
        $i = 0;

        while ($data = $xoopsDB->fetchArray($result)) {

            //將 uid 編號轉換成使用者姓名（或帳號）
            $data['uid_name'] = Utility::get_name_by_uid($data['uid']);

            $data = Tools::filter_all_data($filter, $data, self::$filter_arr);

            //假別名稱、審核狀態、是否導師等顯示用欄位
            $data['cate_sn_title'] = $cate_title_arr[$data['cate_sn']] ?? '';
            $data['status_text'] = self::status_text($data['status']);
            $data['is_advisor_text'] = $data['is_advisor'] ? _MD_JILLLEAVE_ADVISOR : _MD_JILLLEAVE_SUBJECT_TEACHER;

            foreach (self::$filter_arr['explode'] as $item) {
                $data[$item . '_arr'] = explode(';', $data[$item]);
            }

            $new_key = $key_name ? $data[$key_name] : $i;
            $data_arr[$new_key] = $get_value ? $data[$get_value] : $data;
            $i++;
        }

        if ($amount) {
            return [$data_arr, $total, $bar];
        } else {
            return $data_arr;
        }
    }

    //以流水號秀出某筆 jill_leave 資料內容 Jill_leave::show()
    public static function show($where_arr = [], $other_arr = [], $mode = '')
    {
        global $xoopsTpl;

        if (empty($where_arr)) {
            redirect_header($_SERVER['HTTP_REFERER'], 3, _MD_JILLLEAVE_NO_CONDITION . '：' . __FILE__ . __LINE__);
        }

        $all = self::get($where_arr, $other_arr);
        if (empty($all) || empty($all['sn'])) {
            return false;
        }

        //將 uid 編號轉換成使用者姓名（或帳號）
        $all['uid_name'] = Utility::get_name_by_uid($all['uid']);
        $xoopsTpl->assign('uid_name', $all['uid_name']);

        //取得分類資料(jill_leave_cate)
        $jill_leave_cate_arr = Jill_leave_cate::get(['cate_sn' => $all['cate_sn']]);
        $xoopsTpl->assign('jill_leave_cate_arr', $jill_leave_cate_arr);
        $xoopsTpl->assign('cate_sn_title', $jill_leave_cate_arr['cate_title'] ?? '');

        //顯示用欄位
        $all['status_text'] = self::status_text($all['status']);
        $all['is_advisor_text'] = $all['is_advisor'] ? _MD_JILLLEAVE_ADVISOR : _MD_JILLLEAVE_SUBJECT_TEACHER;
        $xoopsTpl->assign('status_text', $all['status_text']);
        $xoopsTpl->assign('is_advisor_text', $all['is_advisor_text']);

        //取得代課資訊（含節次明細）
        $substitutes = Jill_leave_substitute::get_all_by_leave($all['sn']);
        $xoopsTpl->assign('substitutes', $substitutes);

        //是否可管理本筆資料
        $xoopsTpl->assign('can_manage', Tools::chk_own($all['uid'], 'return'));

        //CSRF token（GET 刪除連結用，不清除以供同頁多次操作）
        $token = $GLOBALS['xoopsSecurity']->createToken();
        $xoopsTpl->assign('csrf_token', $token);

        $SweetAlert = new SweetAlert();
        $SweetAlert->render('jill_leave_destroy_func', "{$_SERVER['PHP_SELF']}?op=jill_leave_destroy&XOOPS_TOKEN_REQUEST={$token}&sn=", "sn");

        if ($mode == "return") {
            return $all;
        } elseif ($mode == "assign_all") {
            $xoopsTpl->assign('jill_leave', $all);
        } else {
            foreach ($all as $key => $value) {
                $xoopsTpl->assign($key, $value);
            }
        }
    }

    //以流水號取得某筆 jill_leave 資料 Jill_leave::get()
    public static function get($where_arr = [], $other_arr = [], $filter = 'read', $only_key = '')
    {
        global $xoopsDB;

        if (empty($where_arr)) {
            redirect_header($_SERVER['HTTP_REFERER'], 3, _MD_JILLLEAVE_NO_CONDITION . '：' . __FILE__ . __LINE__);
        }

        $and_sql = Tools::get_and_where($where_arr);

        $sql = "SELECT * FROM `" . $xoopsDB->prefix("jill_leave") . "` WHERE 1 $and_sql";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql);
        $data = $xoopsDB->fetchArray($result);
        if (empty($data)) {
            return [];
        }
        $data = Tools::filter_all_data($filter, $data, self::$filter_arr);

        foreach (self::$filter_arr['explode'] as $item) {
            $data[$item . '_arr'] = explode(';', $data[$item]);
        }

        if ($only_key) {
            return $data[$only_key];
        } else {
            return $data;
        }
    }

    //jill_leave 編輯表單
    public static function create($sn = '', $cate_sn = '')
    {
        global $xoopsDB, $xoopsTpl, $xoopsUser;

        //抓取預設值
        $jill_leave = (!empty($sn)) ? self::get(['sn' => $sn], [], 'edit') : [];

        //僅管理者或本人可編輯
        if (!empty($jill_leave)) {
            Tools::chk_own($jill_leave['uid']);
        } elseif (empty($xoopsUser)) {
            redirect_header(XOOPS_URL . '/modules/jill_leave/index.php', 3, _MD_JILLLEAVE_NO_PERMISSION);
        }

        //假別選單（僅啟用中的假別）
        $cate_sn_options_array = Jill_leave_cate::get_all(['enable' => 1], [], ['cate_sn', 'cate_title'], ['cate_sort' => 'ASC']);
        $xoopsTpl->assign("cate_sn_options", $cate_sn_options_array);

        //預設值設定
        $user_uid = $xoopsUser ? $xoopsUser->uid() : 0;
        $def['sn'] = $sn;
        $def['uid'] = $user_uid;
        $def['leavers'] = $xoopsUser ? Utility::get_name_by_uid($user_uid) : '';
        // 預設假別為排序第一個
        $def['cate_sn'] = $cate_sn ?: ($cate_sn_options_array[0]['cate_sn'] ?? '');
        $def['is_advisor'] = 0;
        $def['grade_class'] = '';
        $def['start_date'] = date("Y-m-d");
        $def['end_date'] = date("Y-m-d");
        $def['status'] = 0;
        $def['create_date'] = date("Y-m-d H:i:s");
        $def['update_date'] = date("Y-m-d H:i:s");

        if (empty($jill_leave)) {
            $jill_leave = $def;
        }

        foreach ($jill_leave as $key => $value) {
            $xoopsTpl->assign($key, $value);
        }

        // 從 grade_class 拆出年級和班級供下拉選單預選
        $grade = '';
        $classroom = '';
        if (!empty($jill_leave['grade_class']) && preg_match('/^(\d+)年(\d+)班$/', $jill_leave['grade_class'], $m)) {
            $grade = $m[1];
            $classroom = $m[2];
        }
        $xoopsTpl->assign('grade', $grade);
        $xoopsTpl->assign('classroom', $classroom);

        //從後臺偏好設定讀取年級選項與最大班級數與節次設定
        $moduleConfig = $GLOBALS['xoopsModuleConfig'] ?? [];
        $grade_conf = $moduleConfig['grade'] ?? '1,2,3,4,5,6';
        $grade_options = array_map('intval', array_filter(explode(',', $grade_conf)));
        $class_room_max = (int) ($moduleConfig['class_room'] ?? 10);
        $class_period_conf = $moduleConfig['class_period'] ?? '早自修,第1節,第2節,第3節,第4節,第5節,第6節,第7節';
        $class_period_options = array_filter(array_map('trim', explode(',', $class_period_conf)));
        
        $xoopsTpl->assign('grade_options', $grade_options);
        $xoopsTpl->assign('class_room_max', $class_room_max);
        $xoopsTpl->assign('class_period_options', $class_period_options);

        $op = (!empty($sn)) ? "jill_leave_update" : "jill_leave_store";
        $xoopsTpl->assign('next_op', $op);

        //套用formValidator驗證機制
        $formValidator = new FormValidator("#myForm", true);
        $formValidator->render();

        //既有的代課資訊（編輯時帶入表單批次列）
        $substitute_rows = (!empty($sn)) ? Jill_leave_substitute::get_rows_by_leave($sn) : [];
        $xoopsTpl->assign('substitute_rows', $substitute_rows);

        My97DatePicker::render();

        //加入Token安全機制
        Utility::token_form();
    }

    //新增資料到 jill_leave Jill_leave::store()
    public static function store()
    {
        global $xoopsDB, $xoopsUser;

        //XOOPS表單安全檢查
        Utility::xoops_security_check();

        //僅登入者可申請
        if (empty($xoopsUser)) {
            redirect_header(XOOPS_URL . '/modules/jill_leave/index.php', 3, _MD_JILLLEAVE_NO_PERMISSION);
        }

        // 請假者姓名強制從登入者取得，不接受前端 POST 值
        $leavers = Tools::filter('leavers', Utility::get_name_by_uid($xoopsUser->uid()), 'write', self::$filter_arr);
        $cate_sn = Tools::filter('cate_sn', $_POST['cate_sn'] ?? 0, 'write', self::$filter_arr);
        $is_advisor = Tools::filter('is_advisor', $_POST['is_advisor'] ?? 0, 'write', self::$filter_arr);
        $grade_class = Tools::filter('grade_class', $_POST['grade_class'] ?? '', 'write', self::$filter_arr);
        $start_date = Tools::filter('start_date', $_POST['start_date'] ?? '', 'write', self::$filter_arr);
        $end_date = Tools::filter('end_date', $_POST['end_date'] ?? '', 'write', self::$filter_arr);

        //一般使用者僅能建立自己的假單且狀態固定為待審核，管理者可指定狀態
        $uid = ($xoopsUser) ? $xoopsUser->uid() : 0;

        //檢查同一人是否在相同日期區間已有假單（日期區間重疊判斷）
        $chk_sql = "SELECT COUNT(*) FROM `" . $xoopsDB->prefix("jill_leave") . "`
            WHERE `uid` = '{$uid}'
            AND `start_date` <= '{$end_date}'
            AND `end_date` >= '{$start_date}'";
        list($dup_count) = $xoopsDB->fetchRow($xoopsDB->query($chk_sql));
        if ($dup_count > 0) {
            redirect_header(XOOPS_URL . '/modules/jill_leave/index.php', 3, _MD_JILLLEAVE_DUPLICATE_LEAVE);
        }
        $status = !empty($_SESSION['jill_leave_adm']) ? Tools::filter('status', $_POST['status'] ?? 0, 'write', self::$filter_arr) : 0;
        $create_date = date("Y-m-d H:i:s", xoops_getUserTimestamp(time()));
        $update_date = date("Y-m-d H:i:s", xoops_getUserTimestamp(time()));

        $sql = "INSERT INTO `" . $xoopsDB->prefix("jill_leave") . "` (
            `uid`,
            `leavers`,
            `cate_sn`,
            `is_advisor`,
            `grade_class`,
            `start_date`,
            `end_date`,
            `status`,
            `create_date`,
            `update_date`
        ) VALUES(
            '{$uid}',
            '{$leavers}',
            '{$cate_sn}',
            '{$is_advisor}',
            '{$grade_class}',
            '{$start_date}',
            '{$end_date}',
            '{$status}',
            '{$create_date}',
            '{$update_date}'
        )";

        $xoopsDB->queryF($sql) or Utility::web_error($sql);

        //取得最後新增資料的流水編號
        $sn = $xoopsDB->getInsertId();
        
        //跨表寫入（PHP端把關）：MyISAM 不支援 Transaction，代課資料寫入失敗時手動刪除主表資料
        if (!self::save_substitutes($sn)) {

            self::destroy_related($sn);
            $del_sql = "DELETE FROM `" . $xoopsDB->prefix("jill_leave") . "` WHERE `sn` = '{$sn}'";
            $xoopsDB->queryF($del_sql);
            redirect_header(XOOPS_URL . '/modules/jill_leave/index.php', 3, _MD_JILLLEAVE_SUBSTITUTE_SAVE_FAIL);
        }

        return $sn;
    }

    //更新 jill_leave 某一筆資料 Jill_leave::update()
    public static function update($where_arr = [], $data_arr = [])
    {
        global $xoopsDB, $xoopsUser;

        $and = Tools::get_and_where($where_arr);

        if (!empty($data_arr)) {
            Tools::chk_is_adm('', '', __FILE__, __LINE__);
            $col_arr = [];

            foreach ($data_arr as $key => $value) {
                $value = Tools::filter($key, $value, 'write', self::$filter_arr);
                $col_arr[] = "`$key` = '{$value}'";
            }
            $update_cols = implode(', ', $col_arr);
            $sql = "UPDATE `" . $xoopsDB->prefix("jill_leave") . "` SET
            $update_cols WHERE 1 $and";
            $xoopsDB->queryF($sql) or Utility::web_error($sql);

            return $where_arr['sn'];
        }

        //XOOPS表單安全檢查
        Utility::xoops_security_check(__FILE__, __LINE__);

        //僅管理者或本人可更新
        $old = self::get($where_arr, [], '');
        if (empty($old)) {
            redirect_header(XOOPS_URL . '/modules/jill_leave/index.php', 3, _MD_JILLLEAVE_NO_CONDITION);
        }
        Tools::chk_own($old['uid']);

        // 請假者姓名強制從原始資料取得，不接受前端 POST 值
        $leavers = Tools::filter('leavers', $old['leavers'] ?? Utility::get_name_by_uid($xoopsUser->uid()), 'write', self::$filter_arr);
        $cate_sn = Tools::filter('cate_sn', $_POST['cate_sn'] ?? 0, 'write', self::$filter_arr);
        $is_advisor = Tools::filter('is_advisor', $_POST['is_advisor'] ?? 0, 'write', self::$filter_arr);
        $grade_class = Tools::filter('grade_class', $_POST['grade_class'] ?? '', 'write', self::$filter_arr);
        $start_date = Tools::filter('start_date', $_POST['start_date'] ?? '', 'write', self::$filter_arr);
        $end_date = Tools::filter('end_date', $_POST['end_date'] ?? '', 'write', self::$filter_arr);

        //更新時檢查日期重疊（排除自身 sn）
        $self_sn = (int) ($where_arr['sn'] ?? 0);
        $uid_chk = (int) $old['uid'];
        $chk_sql = "SELECT COUNT(*) FROM `" . $xoopsDB->prefix("jill_leave") . "`
            WHERE `uid` = '{$uid_chk}'
            AND `start_date` <= '{$end_date}'
            AND `end_date` >= '{$start_date}'
            AND `sn` != '{$self_sn}'";
        list($dup_count) = $xoopsDB->fetchRow($xoopsDB->query($chk_sql));
        if ($dup_count > 0) {
            redirect_header(XOOPS_URL . '/modules/jill_leave/index.php?sn=' . $self_sn, 3, _MD_JILLLEAVE_DUPLICATE_LEAVE);
        }

        //一般使用者更新後回到待審核，管理者可指定狀態
        $status = !empty($_SESSION['jill_leave_adm']) ? Tools::filter('status', $_POST['status'] ?? 0, 'write', self::$filter_arr) : 0;
        $update_date = date("Y-m-d H:i:s", xoops_getUserTimestamp(time()));

        $sql = "UPDATE `" . $xoopsDB->prefix("jill_leave") . "` SET
        `leavers` = '{$leavers}',
        `cate_sn` = '{$cate_sn}',
        `is_advisor` = '{$is_advisor}',
        `grade_class` = '{$grade_class}',
        `start_date` = '{$start_date}',
        `end_date` = '{$end_date}',
        `status` = '{$status}',
        `update_date` = '{$update_date}'
        WHERE 1 $and";
        $xoopsDB->queryF($sql) or Utility::web_error($sql);

        //重建代課資料（先刪後建）
        $sn = (int) ($where_arr['sn'] ?? 0);
        self::destroy_related($sn);
        if (!self::save_substitutes($sn)) {
            redirect_header(XOOPS_URL . '/modules/jill_leave/index.php?sn=' . $sn, 3, _MD_JILLLEAVE_SUBSTITUTE_SAVE_FAIL);
        }

        return $sn;
    }

    //批次儲存代課資訊（jill_leave_substitute + jill_leave_class），失敗回傳 false
    public static function save_substitutes($sn = 0)
    {
        global $xoopsDB;

        $sn = (int) $sn;
        if (empty($sn) || empty($_POST['substitute_date']) || !is_array($_POST['substitute_date'])) {
            return true; //沒有代課資料視為成功
        }

        //同一代課日期共用一筆 jill_leave_substitute
        $substitute_sn_arr = [];

        //級任逐節沿用上方導師班級，讓每節 subject 都自帶班級（與科任統一為 JSON）
        $is_advisor = (int) ($_POST['is_advisor'] ?? 0);
        $main_grade_class = trim((string) ($_POST['grade_class'] ?? ''));

        foreach ($_POST['substitute_date'] as $i => $date) {
            $date = $xoopsDB->escape(trim($date));
            $class_period = Tools::filter('class_period', $_POST['class_period'][$i] ?? '', 'write', Jill_leave_class::$filter_arr);
            $substitute_teacher = Tools::filter('substitute_teacher', $_POST['substitute_teacher'][$i] ?? '', 'write', Jill_leave_class::$filter_arr);
            $pay = in_array($_POST['pay'][$i] ?? '', ['self', 'school'], true) ? $_POST['pay'][$i] : 'self';
            $type = in_array($_POST['type'][$i] ?? '', ['daily', 'hour'], true) ? $_POST['type'][$i] : 'daily';

            //逐節班級＋科目一律合併為 JSON 存入 subject 欄位（級任沿用導師班級）
            $subject_raw = trim((string) ($_POST['subject'][$i] ?? ''));
            $grade_class_period = trim((string) ($_POST['class_grade_class'][$i] ?? ''));
            if ($type === 'hour' && $grade_class_period === '' && $is_advisor) {
                $grade_class_period = $main_grade_class;
            }
            $subject = $xoopsDB->escape(Jill_leave_class::encode_subject($grade_class_period, $subject_raw));

            //空白列跳過
            if (empty($date) || (empty($class_period) && $subject_raw === '' && $grade_class_period === '' && empty($substitute_teacher))) {
                continue;
            }

            if (!isset($substitute_sn_arr[$date])) {
                $sql = "INSERT INTO `" . $xoopsDB->prefix("jill_leave_substitute") . "` (
                    `sn`, `substitute_date`, `pay`, `type`
                ) VALUES ('{$sn}', '{$date}', '{$pay}', '{$type}')";
                if (!$xoopsDB->queryF($sql)) {
                    return false;
                }
                $substitute_sn_arr[$date] = $xoopsDB->getInsertId();
            }
            $substitute_sn = $substitute_sn_arr[$date];

            $sql = "INSERT INTO `" . $xoopsDB->prefix("jill_leave_class") . "` (
                `substitute_sn`, `sn`, `class_period`, `subject`, `substitute_teacher`
            ) VALUES ('{$substitute_sn}', '{$sn}', '{$class_period}', '{$subject}', '{$substitute_teacher}')";
            if (!$xoopsDB->queryF($sql)) {
                return false;
            }
        }

        return true;
    }

    //刪除某假單所屬的代課與節次資料
    public static function destroy_related($sn = 0)
    {
        global $xoopsDB;

        $sn = (int) $sn;
        if (empty($sn)) {
            return;
        }

        $sql = "DELETE FROM `" . $xoopsDB->prefix("jill_leave_substitute") . "` WHERE `sn` = '{$sn}'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql);

        $sql = "DELETE FROM `" . $xoopsDB->prefix("jill_leave_class") . "` WHERE `sn` = '{$sn}'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql);
    }

    //刪除 jill_leave 某筆資料資料 Jill_leave::destroy()（連帶刪除代課與節次資料）
    public static function destroy($sn = '')
    {
        global $xoopsDB;

        $sn = (int) $sn;
        if (empty($sn)) {
            return;
        }

        //僅管理者或本人可刪除
        $old = self::get(['sn' => $sn], [], '');
        if (empty($old)) {
            return;
        }
        Tools::chk_own($old['uid']);

        $sql = "DELETE FROM `" . $xoopsDB->prefix("jill_leave") . "` WHERE `sn` = '{$sn}'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql);

        self::destroy_related($sn);
    }

    //取得請假公告資料（區塊用）：end_date >= 今天 且 status = 1 已通過
    public static function announcement($limit = 10)
    {
        global $xoopsDB;

        $limit = (int) $limit ?: 10;
        $today = date('Y-m-d');

        $sql = "SELECT a.*, c.`cate_title` FROM `" . $xoopsDB->prefix("jill_leave") . "` AS a
            LEFT JOIN `" . $xoopsDB->prefix("jill_leave_cate") . "` AS c ON a.`cate_sn` = c.`cate_sn`
            WHERE a.`end_date` >= '{$today}' AND a.`status` = '1'
            ORDER BY a.`start_date` LIMIT {$limit}";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql);

        $data_arr = [];
        while ($data = $xoopsDB->fetchArray($result)) {
            $data = Tools::filter_all_data('read', $data, self::$filter_arr);
            $data['uid_name'] = Utility::get_name_by_uid($data['uid']);
            $data_arr[] = $data;
        }

        return $data_arr;
    }

    // 更新審核狀態
    public static function update_status($sn, $status)
    {
        global $xoopsDB;
        $sn = (int) $sn;
        $status = in_array((int) $status, [0, 1, 2], true) ? (int) $status : 0;
        $sql = "UPDATE `" . $xoopsDB->prefix("jill_leave") . "` SET `status` = '{$status}' WHERE `sn` = '{$sn}'";
        return $xoopsDB->queryF($sql) ? true : false;
    }
}
