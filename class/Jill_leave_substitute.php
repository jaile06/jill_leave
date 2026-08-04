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
        Tools::chk_permission();

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
    //$escape=false 供表單回填／PDF 等非 HTML 顯示情境使用，避免雙重轉義
    public static function get_all_by_leave($sn = 0, $escape = true)
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
            //補調課不涉及代課鐘點費，支付方式顯示「無」
            $substitutes[$substitute_sn]['pay_text'] = ($substitute['type'] === 'swap') ? _MD_JILLLEAVE_PAY_NONE : (($substitute['pay'] == 'school') ? _MD_JILLLEAVE_PAY_SCHOOL : _MD_JILLLEAVE_PAY_SELF);
            $substitutes[$substitute_sn]['type_text'] = match ($substitute['type']) { 'hour' => _MD_JILLLEAVE_TYPE_HOUR, 'swap' => _MD_JILLLEAVE_TYPE_SWAP, default => _MD_JILLLEAVE_TYPE_DAILY };
        }
        foreach ($classes as $class) {
            $class = Jill_leave_class::display_class($class, $escape);
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
        //供表單回填用，取原始未轉義字串（畫面上以 jQuery .val() 填值，非 HTML 渲染）
        foreach (self::get_all_by_leave($sn, false) as $substitute) {
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
                    'handle' => $class['handle'] ?? 'substitute',
                    'swap_date' => $class['swap_date'] ?? '',
                    'swap_period' => $class['swap_period'] ?? '',
                    'swap_subject' => $class['swap_subject'] ?? '',
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
            //補調課不涉及代課鐘點費，支付方式顯示「無」
            $data['pay_text'] = ($data['type'] === 'swap') ? _MD_JILLLEAVE_PAY_NONE : (($data['pay'] == 'school') ? _MD_JILLLEAVE_PAY_SCHOOL : _MD_JILLLEAVE_PAY_SELF);
            $data['type_text'] = match ($data['type']) { 'hour' => _MD_JILLLEAVE_TYPE_HOUR, 'swap' => _MD_JILLLEAVE_TYPE_SWAP, default => _MD_JILLLEAVE_TYPE_DAILY };
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
                    //補調課單無對應 jill_leave_cate 資料（cate_sn=0），假別名稱固定顯示「補調課」
                    'cate_title' => ((int) $data['cate_sn'] === 0) ? _MD_JILLLEAVE_TYPE_SWAP : $data['cate_title'],
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
        Tools::chk_permission();

        list($leaves, $all_substitute_detail, $month) = self::get_overview_data($month);

        //待審核(0)排最前，其次已通過(1)、駁回(2)；同狀態維持原日期順序
        usort($leaves, function ($a, $b) {
            return $a['status'] <=> $b['status'];
        });

        //依單別分頁籤：日薪／鐘點代課單（cate_sn>0）與補調課單（cate_sn=0）
        $tabs = [
            ['id' => 'sub', 'title' => _MD_JILLLEAVE_TAB_SUBSTITUTE, 'leaves' => []],
            ['id' => 'swap', 'title' => _MD_JILLLEAVE_TYPE_SWAP, 'leaves' => []],
        ];
        foreach ($leaves as $leave) {
            $tabs[((int) $leave['cate_sn'] === 0) ? 1 : 0]['leaves'][] = $leave;
        }

        $xoopsTpl->assign('leave_tabs', $tabs);
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
        Tools::chk_permission();
        require_once XOOPS_ROOT_PATH . '/modules/tadtools/vendor/autoload.php';

        list($leaves, $all_substitute_detail, $month) = self::get_overview_data($month);

        //僅列入已通過 (status=1) 之假單
        $leaves = array_values(array_filter($leaves, static function ($leave) {
            return (int) ($leave['status'] ?? 0) === 1;
        }));

        $excel = new \PHPExcel();
        $excel->getProperties()->setTitle(_MD_JILLLEAVE_EXPORT_TITLE . $month);
        
        // -------------------------------------------------------------
        // Sheet 0: 鐘點費代課清冊 (僅含委託代課)
        // -------------------------------------------------------------
        $sheet0 = $excel->setActiveSheetIndex(0);
        $sheet0->setTitle($month . ' 鐘點費代課');

        $titles0 = [
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
        foreach ($titles0 as $col => $title) {
            $sheet0->setCellValueByColumnAndRow($col, 1, $title);
        }

        // -------------------------------------------------------------
        // Sheet 1: 補調課備查表 (含自己補課與與他人調課)
        // -------------------------------------------------------------
        $sheet1 = $excel->createSheet(1);
        $sheet1->setTitle('補調課備查表');

        $titles1 = [
            '原請假日期',
            _MD_JILLLEAVE_LEAVERS,
            _MD_JILLLEAVE_CATE_CATE_TITLE,
            _MD_JILLLEAVE_GRADE_CLASS,
            '原節次',
            '原科目',
            _MD_JILLLEAVE_HANDLE,
            _MD_JILLLEAVE_CHANGED_DATE,
            _MD_JILLLEAVE_CHANGED_PERIOD,
            '對調/補課教師',
            _MD_JILLLEAVE_SWAP_SUBJECT,
        ];
        foreach ($titles1 as $col => $title) {
            $sheet1->setCellValueByColumnAndRow($col, 1, $title);
        }

        // 收集所有要匯出的資料列
        $export_data0 = []; // 代課
        $export_data1 = []; // 補調課

        foreach ($leaves as $leave) {
            foreach ($leave['substitutes'] as $substitute_sn) {
                $substitute = $all_substitute_detail[$substitute_sn] ?? null;
                if (!$substitute) continue;
                $classes = $substitute['classes'] ?: [['class_period' => '', 'subject' => '', 'grade_class' => '', 'substitute_teacher' => '']];
                foreach ($classes as $class) {
                    $handle = $class['handle'] ?? 'substitute';
                    $grade_class = ($class['grade_class'] ?? '') !== '' ? $class['grade_class'] : $leave['grade_class'];

                    if ($substitute['type'] === 'swap' || $handle !== 'substitute') {
                        // 補調課資料 -> 進入 Sheet 1
                        $handle_text = ($handle === 'makeup') ? _MD_JILLLEAVE_HANDLE_MAKEUP : _MD_JILLLEAVE_HANDLE_SWAP;
                        $export_data1[] = [
                            'date'         => $substitute['substitute_date'],
                            'leaver'       => $leave['leavers'],
                            'cate_title'   => $leave['cate_title'],
                            'grade_class'  => $grade_class,
                            'class_period' => $class['class_period'],
                            'subject'      => $class['subject'],
                            'handle_text'  => $handle_text,
                            'swap_date'    => $class['swap_date'] ?? '',
                            'swap_period'  => $class['swap_period'] ?? '',
                            'teacher'      => $class['substitute_teacher'] ?? '',
                            'swap_subject' => $class['swap_subject'] ?? '',
                        ];
                    } else {
                        // 鐘點費代課資料 -> 進入 Sheet 0
                        $export_data0[] = [
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
        }

        // 排序函數
        $sorter = static function ($a, $b) {
            $date_compare = strcmp($a['date'], $b['date']);
            if ($date_compare !== 0) return $date_compare;
            $leaver_compare = strcmp($a['leaver'], $b['leaver']);
            if ($leaver_compare !== 0) return $leaver_compare;
            return strcmp($a['class_period'], $b['class_period']);
        };
        usort($export_data0, $sorter);
        usort($export_data1, $sorter);

        // 寫入 Sheet 0
        $row = 2;
        foreach ($export_data0 as $data) {
            $sheet0->setCellValueByColumnAndRow(0, $row, $data['date']);
            $sheet0->setCellValueByColumnAndRow(1, $row, $data['leaver']);
            $sheet0->setCellValueByColumnAndRow(2, $row, $data['cate_title']);
            $sheet0->setCellValueByColumnAndRow(3, $row, $data['grade_class']);
            $sheet0->setCellValueByColumnAndRow(4, $row, $data['class_period']);
            $sheet0->setCellValueByColumnAndRow(5, $row, $data['subject']);
            $sheet0->setCellValueByColumnAndRow(6, $row, $data['substitute_teacher']);
            $sheet0->setCellValueByColumnAndRow(7, $row, $data['pay_text']);
            $sheet0->setCellValueByColumnAndRow(8, $row, $data['type_text']);
            $row++;
        }

        // 寫入 Sheet 1
        $row = 2;
        foreach ($export_data1 as $data) {
            $sheet1->setCellValueByColumnAndRow(0, $row, $data['date']);
            $sheet1->setCellValueByColumnAndRow(1, $row, $data['leaver']);
            $sheet1->setCellValueByColumnAndRow(2, $row, $data['cate_title']);
            $sheet1->setCellValueByColumnAndRow(3, $row, $data['grade_class']);
            $sheet1->setCellValueByColumnAndRow(4, $row, $data['class_period']);
            $sheet1->setCellValueByColumnAndRow(5, $row, $data['subject']);
            $sheet1->setCellValueByColumnAndRow(6, $row, $data['handle_text']);
            $sheet1->setCellValueByColumnAndRow(7, $row, $data['swap_date']);
            $sheet1->setCellValueByColumnAndRow(8, $row, $data['swap_period']);
            $sheet1->setCellValueByColumnAndRow(9, $row, $data['teacher']);
            $sheet1->setCellValueByColumnAndRow(10, $row, $data['swap_subject']);
            $row++;
        }

        $excel->setActiveSheetIndex(0);

        $filename = "jill_leave_{$month}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        exit;
    }


}
