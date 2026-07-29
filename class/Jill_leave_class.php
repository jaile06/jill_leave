<?php
namespace XoopsModules\Jill_leave;

use XoopsModules\Tadtools\Utility;
use XoopsModules\Jill_leave\Tools;

class Jill_leave_class
{
    // 過濾用變數的設定
    public static $filter_arr = [
        'int' => ['class_sn','substitute_sn','sn'],   //數字類的欄位
        'html' => [], //含網頁語法的欄位（所見即所得的內容）
        'text' => [], //純大量文字欄位
        'json' => [], //內容為 json 格式的欄位
        'pass' => ['files', 'subject'], //不予過濾的欄位（subject 為 JSON 格式，由 display_class 自行處理）
        'explode' => [],   //用分號隔開的欄位
    ];

    // ponytail: 遺課處理方式（委託代課／自己補課／與他人調課）與異動後課程，一律收進既有的 subject JSON，
    // 不新增資料表欄位。handle 省略即為舊行為「委託代課」，舊資料零遷移。
    // 需要跨假單雙向記錄（對方老師名下也生一筆）時才需另立資料表。
    public static $handle_keys = ['handle', 'swap_date', 'swap_period', 'swap_subject'];

    //處理方式顯示文字
    public static function handle_text($handle = 'substitute')
    {
        return match ((string) $handle) {
            'makeup' => _MD_JILLLEAVE_HANDLE_MAKEUP,
            'swap' => _MD_JILLLEAVE_HANDLE_SWAP,
            default => _MD_JILLLEAVE_HANDLE_SUBSTITUTE,
        };
    }

    //逐節班級＋科目一律打包成 JSON 存入 subject 欄位（中文不跳脱，級任科任統一格式）；兩者皆空則存空字串（日薪列無科目）
    public static function encode_subject($grade_class = '', $subject = '', $extra = [])
    {
        $data = [
            'grade_class' => trim((string) $grade_class),
            'subject' => trim((string) $subject),
        ];

        //僅寫入非預設值，代課列的 JSON 與舊資料格式完全相同
        foreach (self::$handle_keys as $key) {
            $value = trim((string) ($extra[$key] ?? ''));
            if ($value !== '' && !($key === 'handle' && $value === 'substitute')) {
                $data[$key] = $value;
            }
        }

        if ($data['grade_class'] === '' && $data['subject'] === '' && count($data) === 2) {
            return '';
        }
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    //把 subject 欄位解回 grade_class + subject + 處理方式（相容舊資料／級任的純文字）
    public static function decode_subject($raw = '')
    {
        $raw = (string) $raw;
        $default = [
            'grade_class' => '',
            'subject' => '',
            'handle' => 'substitute',
            'swap_date' => '',
            'swap_period' => '',
            'swap_subject' => '',
        ];
        if (isset($raw[0]) && $raw[0] === '{') {
            $data = json_decode($raw, true);
            if (is_array($data) && array_key_exists('subject', $data)) {
                return array_map('strval', array_merge($default, array_intersect_key($data, $default)));
            }
        }
        return array_merge($default, ['subject' => $raw]);
    }

    //把一列原始節次資料整理成顯示用（解析 subject），供 show／總覽／Excel／表單回填共用
    //$escape=true 套 htmlspecialchars（HTML 顯示用）；$escape=false 保留原始字串
    //（表單回填走 JS 的 .val()，不是 HTML 渲染，escape 過的字串會在畫面上雙重轉義顯示 &amp;）
    public static function display_class($class = [], $escape = true)
    {
        $decoded = self::decode_subject($class['subject'] ?? '');
        $class['grade_class'] = $decoded['grade_class'];
        $class['subject'] = $decoded['subject'];
        //處理方式與異動後課程（代課列 handle 為 substitute，swap_* 為空字串）
        $class['handle'] = $decoded['handle'];
        $class['handle_text'] = self::handle_text($decoded['handle']);
        $class['swap_date'] = $decoded['swap_date'];
        $class['swap_period'] = $decoded['swap_period'];
        $class['swap_subject'] = $decoded['swap_subject'];
        $class['class_period'] = (string) ($class['class_period'] ?? '');
        $class['substitute_teacher'] = (string) ($class['substitute_teacher'] ?? '');

        if ($escape) {
            $myts = \MyTextSanitizer::getInstance();
            foreach (['grade_class', 'subject', 'swap_date', 'swap_period', 'swap_subject', 'class_period', 'substitute_teacher'] as $key) {
                $class[$key] = $myts->htmlSpecialChars($class[$key]);
            }
        }

        $class['class_sn'] = (int) ($class['class_sn'] ?? 0);
        $class['substitute_sn'] = (int) ($class['substitute_sn'] ?? 0);
        $class['sn'] = (int) ($class['sn'] ?? 0);
        return $class;
    }

    //取得 jill_leave_class 所有資料陣列 Jill_leave_class::get_all()
    public static function get_all($where_arr = [], $other_arr = [], $view_cols = [], $order_arr = [], $key_name = false, $get_value = '', $filter = 'read', $amount = '')
    {
        global $xoopsDB;

        $and_sql = Tools::get_and_where($where_arr);
        $view_col = Tools::get_view_col($view_cols);
        $order_sql = Tools::get_order($order_arr);
        $order = $amount ? '' : $order_sql;

        $sql = "SELECT {$view_col} FROM `" . $xoopsDB->prefix("jill_leave_class") . "` WHERE 1 {$and_sql} {$order}";

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
}
