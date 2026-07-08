<?php
namespace XoopsModules\Jill_leave;

use XoopsModules\Tadtools\SweetAlert;
use XoopsModules\Tadtools\Utility;
use XoopsModules\Tadtools\BootstrapTable;
use XoopsModules\Tadtools\FancyBox;
use XoopsModules\Jill_leave\Tools;
use XoopsModules\Tadtools\TadUpFiles;
use XoopsModules\Tadtools\FormValidator;



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

    //列出所有 jill_leave_class 資料 Jill_leave_class::index()
    public static function index($where_arr = [], $other_arr = [], $view_cols = [], $order_arr = [], $amount = '')
    {
        global $xoopsTpl, $xoTheme;

        if ($amount) {
            list($all_jill_leave_class, $total, $bar) = self::get_all($where_arr, $other_arr, $view_cols, $order_arr, null, null, 'read', $amount);
            $xoopsTpl->assign('bar', $bar);
            $xoopsTpl->assign('total', $total);
        } else {
            $all_jill_leave_class = self::get_all($where_arr, $other_arr, $view_cols, $order_arr);
        }

        $xoopsTpl->assign('all_jill_leave_class', $all_jill_leave_class);
        Utility::test($all_jill_leave_class, 'all_jill_leave_class');

        //刪除確認的JS
        $SweetAlert   = new SweetAlert();
        $SweetAlert->render('jill_leave_class_destroy_func', "{$_SERVER['PHP_SELF']}?op=jill_leave_class_destroy&class_sn=", "class_sn");
        
        BootstrapTable::render();

        $fancybox = new FancyBox('.fancybox_jill_leave_class_class_sn');
        $fancybox->render();
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


    //以流水號秀出某筆 jill_leave_class 資料內容 Jill_leave_class::show()
    public static function show($where_arr = [], $other_arr = [], $mode = '')
    {
        global $xoopsTpl;

        if (empty($where_arr)) {
            redirect_header($_SERVER['HTTP_REFERER'], 3, "無查詢條件：" . __FILE__ . __LINE__);
        }

        $all = self::get($where_arr, $other_arr);
        if (empty($all)) {
            return false;
        }

        foreach ($all as $key => $value) {
            $value = Tools::filter($key, $value, 'read', self::$filter_arr);
            $all[$key] = $value;
            $$key = $value;
        }

        

        $SweetAlert   = new SweetAlert();
        $SweetAlert->render('jill_leave_class_destroy_func', "{$_SERVER['PHP_SELF']}?op=jill_leave_class_destroy&class_sn=", "class_sn");

        if ($mode == "return") {
            return $all;
        } elseif ($mode == "assign_all") {
            $xoopsTpl->assign('jill_leave_class', $all);
        } else {
            foreach ($all as $key => $value) {
                $xoopsTpl->assign($key, $value);
            }
        }
    }


    //以流水號取得某筆 jill_leave_class 資料 Jill_leave_class::get()
    public static function get($where_arr = [], $other_arr = [], $filter = 'read', $only_key = '')
    {
        global $xoopsDB;

        if (empty($where_arr)) {
            redirect_header($_SERVER['HTTP_REFERER'], 3, "無查詢條件：" . __FILE__ . __LINE__);
        }

        $and_sql = Tools::get_and_where($where_arr);

        $sql = "SELECT * FROM `" . $xoopsDB->prefix("jill_leave_class") . "` WHERE 1 $and_sql";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql);
        $data = $xoopsDB->fetchArray($result);
        $data = Tools::filter_all_data($filter, $data, self::$filter_arr);
        

        // if (in_array('xxx', $other_arr) || in_array('all', $other_arr)) {
        //     $data['xxx'] = ooo::get_all();
        // }

        foreach (self::$filter_arr['explode'] as $item) {
            $data[$item . '_arr'] = explode(';', $data[$item]);
        }

        if ($only_key) {
            return $data[$only_key];
        } else {
            return $data;
        }
    }


    //jill_leave_class 編輯表單
    public static function create($class_sn = '' )
    {
        global $xoopsTpl, $xoopsUser;
        Tools::chk_is_adm('', '', __FILE__, __LINE__);

        //抓取預設值
        $jill_leave_class = (!empty($class_sn)) ? self::get(['class_sn' =>$class_sn]) : [];

        //預設值設定
        
        $def['class_sn'] = $class_sn;

        if (empty($jill_leave_class)) {
            $jill_leave_class = $def;
        }

        foreach ($jill_leave_class as $key => $value) {
            $value = Tools::filter($key, $value, 'edit', self::$filter_arr);
            $$key = isset($jill_leave_class[$key]) ? $jill_leave_class[$key] : $def[$key];
            $xoopsTpl->assign($key, $value);
        }

        $op = (!empty($class_sn)) ? "jill_leave_class_update" : "jill_leave_class_store";
        $xoopsTpl->assign('next_op', $op);

        //套用formValidator驗證機制
        $formValidator = new FormValidator("#myForm", true);
        $formValidator->render();

        
    
        //加入Token安全機制
        Utility::token_form();
    }


    //新增資料到 jill_leave_class Jill_leave_class::store()
    public static function store($data_arr = [])
    {
        global $xoopsDB, $xoopsUser;
        Tools::chk_is_adm('', '', __FILE__, __LINE__);

        //XOOPS表單安全檢查
        if (empty($data_arr)) {
            Utility::xoops_security_check();
            $data_arr = $_POST;
        }

        foreach ($data_arr as $key => $value) {
            $$key = Tools::filter($key, $value, 'write', self::$filter_arr);
        }

        

        $sql = "INSERT INTO `" . $xoopsDB->prefix("jill_leave_class") . "` (
            `substitute_sn`, 
            `sn`, 
            `class_period`, 
            `subject`, 
            `substitute_teacher`
        ) VALUES(
            '{$substitute_sn}', 
            '{$sn}', 
            '{$class_period}', 
            '{$subject}', 
            '{$substitute_teacher}'
        )";
        $xoopsDB->queryF($sql) or Utility::web_error($sql);

        //取得最後新增資料的流水編號
        $class_sn = $xoopsDB->getInsertId();
        
        return $class_sn;
    }


    //更新 jill_leave_class 某一筆資料 Jill_leave_class::update()
    public static function update($where_arr=[], $data_arr = [])
    {
        global $xoopsDB, $xoopsUser;
        Tools::chk_is_adm('', '', __FILE__, __LINE__);

        $and = Tools::get_and_where($where_arr);

        if (!empty($data_arr)) {
            $col_arr = [];

            foreach ($data_arr as $key => $value) {
                $value = Tools::filter($key, $value, 'write', self::$filter_arr);
                $col_arr[] = "`$key` = '{$value}'";
            }
            $update_cols = implode(', ', $col_arr);
            $sql = "UPDATE `" . $xoopsDB->prefix("jill_leave_class") . "` SET
            $update_cols WHERE 1 $and";
        } else {
            //XOOPS表單安全檢查
            Utility::xoops_security_check(__FILE__, __LINE__);

            foreach ($_POST as $key => $value) {
                $$key = Tools::filter($key, $value, 'write', self::$filter_arr);
            }
            

            $sql = "UPDATE `" . $xoopsDB->prefix("jill_leave_class") . "` SET 
            `substitute_sn` = '{$substitute_sn}', 
            `sn` = '{$sn}', 
            `class_period` = '{$class_period}', 
            `subject` = '{$subject}', 
            `substitute_teacher` = '{$substitute_teacher}'
            WHERE 1 $and";
        }
        $xoopsDB->queryF($sql) or Utility::web_error($sql);
        
        return $where_arr['class_sn'];
    }


    //刪除 jill_leave_class 某筆資料資料 Jill_leave_class::destroy()
    public static function destroy($class_sn = '')
    {
        global $xoopsDB;
        Tools::chk_is_adm('', '', __FILE__, __LINE__);

        if(empty($class_sn)) {
            return;
        }

        $and = '';
        if($class_sn){
        $and .= "and `class_sn` = '$class_sn'";
    }
    

        $sql = "DELETE FROM `" . $xoopsDB->prefix("jill_leave_class") . "`
        WHERE 1 $and";
        $xoopsDB->queryF($sql) or Utility::web_error($sql);
        
    }





}
