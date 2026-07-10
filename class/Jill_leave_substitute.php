<?php
namespace XoopsModules\Jill_leave;

use XoopsModules\Tadtools\SweetAlert;
use XoopsModules\Tadtools\Utility;
use XoopsModules\Tadtools\BootstrapTable;
use XoopsModules\Jill_leave\Tools;
use XoopsModules\Tadtools\My97DatePicker;



class Jill_leave_substitute
{
    // 過濾用變數的設定
    public static $filter_arr = [
        'int' => ['substitute_sn','sn'],   //數字類的欄位
        'html' => [], //含網頁語法的欄位（所見即所得的內容）
        'text' => [], //純大量文字欄位
        'json' => [], //內容為 json 格式的欄位
        'pass' => ['files'], //不予過濾的欄位
        'explode' => [],   //用分號隔開的欄位
    ];

    //取得 jill_leave_substitute 所有資料陣列 Jill_leave_substitute::get_all()
    public static function get_all($where_arr = [], $other_arr = [], $view_cols = [], $order_arr = [], $key_name = false, $get_value = '', $filter = 'read', $amount = '')
    {
        global $xoopsDB;

        $and_sql = Tools::get_and_where($where_arr);
        $view_col = Tools::get_view_col($view_cols);
        $order_sql = Tools::get_order($order_arr);
        $order = $amount ? '' : $order_sql;

        $sql = "SELECT {$view_col} FROM `" . $xoopsDB->prefix("jill_leave_substitute") . "` WHERE 1 {$and_sql} {$order}";

        // Utility::getPageBar($原sql語法, 每頁顯示幾筆資料, 最多顯示幾個頁數選項);
        if ($amount) {
            $PageBar = Utility::getPageBar($sql, $amount, 10, '', '', $_SESSION['bootstrap'], 'g2p', $order_sql);
            $bar = $PageBar['bar'];
            $sql = $PageBar['sql'];
            $total = $PageBar['total'];
        }

        $result = $xoopsDB->query($sql) or Utility::web_error($sql);
        $data_arr = [];
        $i = 0;
        
        while ($data = $xoopsDB->fetchArray($result)) {
            
            $data = Tools::filter_all_data($filter, $data, self::$filter_arr);

            foreach (self::$filter_arr['explode'] as $item) {
                $data[$item . '_arr'] = explode(';', $data[$item]);
            }

            // if (in_array('xxx', $other_arr) || in_array('all', $other_arr)) {
            //     $data['xxx'] = ooo::get_all();
            // }
            

            $new_key = $key_name ? $data[$key_name] : $i;
            $data_arr[$new_key] = $get_value ? $data[$get_value] : $data;
            $i++;
        }

        if ($amount) {
            return [$data_arr, $total, $bar];
        }else{
            return $data_arr;
        }
    }


    //刪除 jill_leave_substitute 某筆資料資料 Jill_leave_substitute::destroy()
    public static function destroy($substitute_sn = '')
    {
        global $xoopsDB;
        Tools::chk_is_adm('', '', __FILE__, __LINE__);

        $substitute_sn = (int) $substitute_sn;
        if (empty($substitute_sn)) {
            return;
        }

        $sql = "DELETE FROM `" . $xoopsDB->prefix("jill_leave_substitute") . "` WHERE `substitute_sn` = '{$substitute_sn}'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql);

        //連帶刪除節次明細
        $sql = "DELETE FROM `" . $xoopsDB->prefix("jill_leave_class") . "` WHERE `substitute_sn` = '" . (int) $substitute_sn . "'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql);
    }


    //取得某假單的代課資料（含節次明細，巢狀陣列）Jill_leave_substitute::get_all_by_leave()
    public static function get_all_by_leave($sn = 0)
    {
        global $xoopsDB;

        $sn = (int) $sn;
        if (empty($sn)) {
            return [];
        }

        $substitutes = self::get_all(['sn' => $sn], [], [], ['substitute_date' => 'ASC'], 'substitute_sn');

        if (empty($substitutes)) {
            return [];
        }

        //以原始資料取回節次（filter=''），再經 display_class 解析 subject（JSON→班級＋科目）並套顯示過濾
        $classes = Jill_leave_class::get_all(['sn' => $sn], [], [], ['class_sn' => 'ASC'], false, '', '');
        foreach ($substitutes as $substitute_sn => $substitute) {
            $substitutes[$substitute_sn]['classes'] = [];
            $substitutes[$substitute_sn]['pay_text'] = ($substitute['pay'] == 'school') ? _MD_JILLLEAVE_PAY_SCHOOL : _MD_JILLLEAVE_PAY_SELF;
            $substitutes[$substitute_sn]['type_text'] = ($substitute['type'] == 'hour') ? _MD_JILLLEAVE_TYPE_HOUR : _MD_JILLLEAVE_TYPE_DAILY;
        }
        foreach ($classes as $class) {
            $class = Jill_leave_class::display_class($class);
            if (isset($substitutes[$class['substitute_sn']])) {
                $substitutes[$class['substitute_sn']]['classes'][] = $class;
            }
        }

        return array_values($substitutes);
    }


    //取得某假單的代課資料（攤平為表單批次列）Jill_leave_substitute::get_rows_by_leave()
    public static function get_rows_by_leave($sn = 0)
    {
        $rows = [];
        foreach (self::get_all_by_leave($sn) as $substitute) {
            if (empty($substitute['classes'])) {
                //僅有代課日期而無節次
                $rows[] = [
                    'substitute_date' => $substitute['substitute_date'],
                    'pay' => $substitute['pay'],
                    'type' => $substitute['type'],
                    'class_period' => '',
                    'subject' => '',
                    'grade_class' => '',
                    'substitute_teacher' => '',
                ];
                continue;
            }
            foreach ($substitute['classes'] as $class) {
                $rows[] = [
                    'substitute_date' => $substitute['substitute_date'],
                    'pay' => $substitute['pay'],
                    'type' => $substitute['type'],
                    'class_period' => $class['class_period'],
                    'subject' => $class['subject'],
                    'grade_class' => $class['grade_class'] ?? '',
                    'substitute_teacher' => $class['substitute_teacher'],
                ];
            }
        }
        return $rows;
    }


    //取得某月份全校請假/代課總覽資料 Jill_leave_substitute::get_overview_data()
    public static function get_overview_data($month = '')
    {
        global $xoopsDB;

        $month = preg_match('/^\d{4}-\d{2}$/', (string) $month) ? $month : date('Y-m');

        $sql = "SELECT s.`substitute_sn`, s.`sn`, s.`substitute_date`, s.`pay`, s.`type`,
            l.`uid`, l.`leavers`, l.`cate_sn`, l.`is_advisor`, l.`grade_class`, l.`start_date`, l.`end_date`, l.`status`,
            c.`cate_title`
            FROM `" . $xoopsDB->prefix("jill_leave_substitute") . "` AS s
            LEFT JOIN `" . $xoopsDB->prefix("jill_leave") . "` AS l ON s.`sn` = l.`sn`
            LEFT JOIN `" . $xoopsDB->prefix("jill_leave_cate") . "` AS c ON l.`cate_sn` = c.`cate_sn`
            WHERE DATE_FORMAT(s.`substitute_date`, '%Y-%m') = '" . $xoopsDB->escape($month) . "'
            ORDER BY s.`sn`, s.`substitute_date`";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql);

        $data_arr = [];
        $substitute_sns = [];
        $leaves_data = []; // ponytail: group substitutes by leave sn
        $detail_map = []; // ponytail: map substitute_sn to detail data for excel/template
        while ($data = $xoopsDB->fetchArray($result)) {
            $data = Tools::filter_all_data('read', $data, self::$filter_arr);
            $data['status_text'] = Jill_leave::status_text($data['status']);
            $data['pay_text'] = ($data['pay'] == 'school') ? _MD_JILLLEAVE_PAY_SCHOOL : _MD_JILLLEAVE_PAY_SELF;
            $data['type_text'] = ($data['type'] == 'hour') ? _MD_JILLLEAVE_TYPE_HOUR : _MD_JILLLEAVE_TYPE_DAILY;
            $data['classes'] = [];
            $data_arr[$data['substitute_sn']] = $data;
            $detail_map[$data['substitute_sn']] = $data;
            $substitute_sns[] = (int) $data['substitute_sn'];

            if (!isset($leaves_data[$data['sn']])) {
                $leaves_data[$data['sn']] = [
                    'sn' => $data['sn'],
                    'leavers' => $data['leavers'],
                    'is_advisor' => $data['is_advisor'],
                    'grade_class' => $data['grade_class'],
                    'cate_sn' => $data['cate_sn'],
                    'cate_title' => $data['cate_title'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'status' => $data['status'],
                    'status_text' => $data['status_text'],
                    'substitutes' => [],
                ];
            }
            $leaves_data[$data['sn']]['substitutes'][] = $data['substitute_sn'];
        }

        //一次撈出所有節次明細
        if ($substitute_sns) {
            $in = implode(',', $substitute_sns);
            $sql = "SELECT * FROM `" . $xoopsDB->prefix("jill_leave_class") . "` WHERE `substitute_sn` IN ({$in}) ORDER BY `class_sn`";
            $result = $xoopsDB->query($sql) or Utility::web_error($sql);
            while ($class = $xoopsDB->fetchArray($result)) {
                //解析 subject（JSON→班級＋科目）並套顯示過濾
                $class = Jill_leave_class::display_class($class);
                if (isset($detail_map[$class['substitute_sn']])) {
                    $detail_map[$class['substitute_sn']]['classes'][] = $class;
                }
            }
        }

        return [array_values($leaves_data), $detail_map, $month];
    }


    //管理者代課總覽（依月份篩選）Jill_leave_substitute::overview()
    public static function overview($month = '')
    {
        global $xoopsTpl;
        Tools::chk_is_adm('', '', __FILE__, __LINE__);

        list($leaves, $all_substitute_detail, $month) = self::get_overview_data($month);

        //待審核(0)排最前，其次已通過(1)、駁回(2)；同狀態維持原日期順序
        usort($leaves, function ($a, $b) {
            return $a['status'] <=> $b['status'];
        });

        $xoopsTpl->assign('all_leaves', $leaves);
        $xoopsTpl->assign('all_substitute_detail', $all_substitute_detail);
        $xoopsTpl->assign('month', $month);

        //CSRF token（GET 刪除連結與 AJAX 狀態切換共用，不清除以供同頁多次操作）
        $token = $GLOBALS['xoopsSecurity']->createToken();
        $xoopsTpl->assign('csrf_token', $token);

        //刪除假單（連帶代課資料）確認的JS
        $SweetAlert = new SweetAlert();
        $SweetAlert->render('jill_leave_destroy_func', "{$_SERVER['PHP_SELF']}?op=jill_leave_destroy&month={$month}&XOOPS_TOKEN_REQUEST={$token}&sn=", "sn");

        BootstrapTable::render();
        My97DatePicker::render();
    }


    //匯出鐘點費清冊 Excel（例外流程：直接輸出檔案流，不走 footer 樣板）
    public static function export_excel($month = '')
    {
        Tools::chk_is_adm('', '', __FILE__, __LINE__);

        // 暫時開啟 display_errors 以供偵錯
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE);
        ini_set('display_errors', 1);
        /*
        if (ob_get_level()) {
            ob_end_clean();
        }
        */

        require_once XOOPS_ROOT_PATH . '/modules/tadtools/vendor/autoload.php';

        list($leaves, $all_substitute_detail, $month) = self::get_overview_data($month);

        //鐘點費清冊僅列入已通過（status=1）之假單，待審核／駁回者不予列入（總覽頁仍顯示全部狀態供管理者審核）
        $leaves = array_values(array_filter($leaves, static function ($leave) {
            return (int) ($leave['status'] ?? 0) === 1;
        }));

        $excel = new \PHPExcel();
        $excel->getProperties()->setTitle(_MD_JILLLEAVE_EXPORT_TITLE . $month);
        $sheet = $excel->setActiveSheetIndex(0);
        $sheet->setTitle($month);

        //標題列
        $titles = [
            _MD_JILLLEAVE_SUBSTITUTE_SUBSTITUTE_DATE,
            _MD_JILLLEAVE_LEAVERS,
            _MD_JILLLEAVE_CATE_CATE_TITLE,
            _MD_JILLLEAVE_GRADE_CLASS,
            _MD_JILLLEAVE_CLASS_CLASS_PERIOD,
            _MD_JILLLEAVE_CLASS_SUBJECT,
            _MD_JILLLEAVE_CLASS_SUBSTITUTE_TEACHER,
            _MD_JILLLEAVE_SUBSTITUTE_PAY,
            _MD_JILLLEAVE_SUBSTITUTE_TYPE,
        ];
        foreach ($titles as $col => $title) {
            $sheet->setCellValueByColumnAndRow($col, 1, $title);
        }

        // 收集所有要匯出的資料列
        $export_data = [];
        foreach ($leaves as $leave) {
            foreach ($leave['substitutes'] as $substitute_sn) {
                $substitute = $all_substitute_detail[$substitute_sn] ?? null;
                if (!$substitute) continue;
                $classes = $substitute['classes'] ?: [['class_period' => '', 'subject' => '', 'grade_class' => '', 'substitute_teacher' => '']];
                foreach ($classes as $class) {
                    // 班級欄：科任逐節班級優先，無則用導師班級
                    $grade_class = ($class['grade_class'] ?? '') !== '' ? $class['grade_class'] : $leave['grade_class'];
                    $export_data[] = [
                        'date'               => $substitute['substitute_date'],
                        'leaver'             => $leave['leavers'],
                        'cate_title'         => $leave['cate_title'],
                        'grade_class'        => $grade_class,
                        'class_period'       => $class['class_period'],
                        'subject'            => $class['subject'],
                        'substitute_teacher' => $class['substitute_teacher'],
                        'pay_text'           => $substitute['pay_text'],
                        'type_text'          => $substitute['type_text'],
                    ];
                }
            }
        }

        // 以代課日期為第一優先進行排序 (若日期相同，則再比對請假人與節次)
        usort($export_data, static function ($a, $b) {
            $date_compare = strcmp($a['date'], $b['date']);
            if ($date_compare !== 0) {
                return $date_compare;
            }
            $leaver_compare = strcmp($a['leaver'], $b['leaver']);
            if ($leaver_compare !== 0) {
                return $leaver_compare;
            }
            return strcmp($a['class_period'], $b['class_period']);
        });

        // 資料列寫入 Excel
        $row = 2;
        foreach ($export_data as $data) {
            $sheet->setCellValueByColumnAndRow(0, $row, $data['date']);
            $sheet->setCellValueByColumnAndRow(1, $row, $data['leaver']);
            $sheet->setCellValueByColumnAndRow(2, $row, $data['cate_title']);
            $sheet->setCellValueByColumnAndRow(3, $row, $data['grade_class']);
            $sheet->setCellValueByColumnAndRow(4, $row, $data['class_period']);
            $sheet->setCellValueByColumnAndRow(5, $row, $data['subject']);
            $sheet->setCellValueByColumnAndRow(6, $row, $data['substitute_teacher']);
            $sheet->setCellValueByColumnAndRow(7, $row, $data['pay_text']);
            $sheet->setCellValueByColumnAndRow(8, $row, $data['type_text']);
            $row++;
        }

        //改用 Excel2007 (xlsx) 寫入器：PHPExcel 的 Excel5 (xls) 寫入器內部仍使用 PHP8 已移除的
        //大括號字串/陣列偏移語法（Writer/Excel5/Parser.php），在 PHP 8.0+ 下無論有無公式一律 Fatal error。
        //Excel2007 寫入器不會經過該檔案，故可在不修改 vendor 套件的情況下修復匯出功能。
        $filename = "jill_leave_{$month}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        /*
        if (ob_get_level()) {
            ob_clean();
        }
        */
        $writer->save('php://output');
        exit;
    }


}
