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
        'pass' => ['files'], //不予過濾的欄位
        'explode' => [],   //用分號隔開的欄位
    ];

    //逐節班級＋科目一律打包成 JSON 存入 subject 欄位（中文不跳脱，級任科任統一格式）；兩者皆空則存空字串（日薪列無科目）
    public static function encode_subject($grade_class = '', $subject = '')
    {
        $grade_class = trim((string) $grade_class);
        $subject = trim((string) $subject);
        if ($grade_class === '' && $subject === '') {
            return '';
        }
        return json_encode(['grade_class' => $grade_class, 'subject' => $subject], JSON_UNESCAPED_UNICODE);
    }

    //把 subject 欄位解回 grade_class + subject（相容舊資料／級任的純文字）
    public static function decode_subject($raw = '')
    {
        $raw = (string) $raw;
        if (isset($raw[0]) && $raw[0] === '{') {
            $data = json_decode($raw, true);
            if (is_array($data) && array_key_exists('subject', $data)) {
                return [
                    'grade_class' => (string) ($data['grade_class'] ?? ''),
                    'subject' => (string) ($data['subject'] ?? ''),
                ];
            }
        }
        return ['grade_class' => '', 'subject' => $raw];
    }

    //把一列原始節次資料整理成顯示用（解析 subject、套 htmlspecialchars），供 show／總覽／Excel 共用
    public static function display_class($class = [])
    {
        $myts = \MyTextSanitizer::getInstance();
        $decoded = self::decode_subject($class['subject'] ?? '');
        $class['grade_class'] = $myts->htmlSpecialChars($decoded['grade_class']);
        $class['subject'] = $myts->htmlSpecialChars($decoded['subject']);
        $class['class_period'] = $myts->htmlSpecialChars((string) ($class['class_period'] ?? ''));
        $class['substitute_teacher'] = $myts->htmlSpecialChars((string) ($class['substitute_teacher'] ?? ''));
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
