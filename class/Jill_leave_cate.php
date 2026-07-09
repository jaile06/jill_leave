<?php
namespace XoopsModules\Jill_leave;

use XoopsModules\Tadtools\SweetAlert;
use XoopsModules\Tadtools\Utility;
use XoopsModules\Tadtools\BootstrapTable;
use XoopsModules\Tadtools\FancyBox;
use XoopsModules\Jill_leave\Tools;
use XoopsModules\Tadtools\TadUpFiles;
use XoopsModules\Tadtools\FormValidator;
use XoopsModules\Jill_leave\Jill_leave;



class Jill_leave_cate
{
    // 過濾用變數的設定
    public static $filter_arr = [
        'int' => ['cate_sn','cate_sort','enable'],   //數字類的欄位
        'html' => [], //含網頁語法的欄位（所見即所得的內容）
        'text' => [], //純大量文字欄位
        'json' => [], //內容為 json 格式的欄位
        'pass' => ['files'], //不予過濾的欄位
        'explode' => [],   //用分號隔開的欄位
    ];

    //列出所有 jill_leave_cate 資料 Jill_leave_cate::index()
    public static function index($where_arr = [], $other_arr = [], $view_cols = [], $order_arr = [], $amount = '')
    {
        global $xoopsTpl, $xoTheme;

        if ($amount) {
            list($all_jill_leave_cate, $total, $bar) = self::get_all($where_arr, $other_arr, $view_cols, $order_arr, null, null, 'read', $amount);
            $xoopsTpl->assign('bar', $bar);
            $xoopsTpl->assign('total', $total);
        } else {
            $all_jill_leave_cate = self::get_all($where_arr, $other_arr, $view_cols, $order_arr);
        }

        $xoopsTpl->assign('all_jill_leave_cate', $all_jill_leave_cate);
        Utility::test($all_jill_leave_cate, 'all_jill_leave_cate');

        //刪除確認的JS
        $SweetAlert   = new SweetAlert();
        $SweetAlert->render('jill_leave_cate_destroy_func', "{$_SERVER['PHP_SELF']}?op=jill_leave_cate_destroy&cate_sn=", "cate_sn");
        
        Utility::get_jquery(true);
        BootstrapTable::render();

        $fancybox = new FancyBox('.fancybox_jill_leave_cate_cate_sn');
        $fancybox->render();
    }


    //取得 jill_leave_cate 所有資料陣列 Jill_leave_cate::get_all()
    public static function get_all($where_arr = [], $other_arr = [], $view_cols = [], $order_arr = [], $key_name = false, $get_value = '', $filter = 'read', $amount = '')
    {
        global $xoopsDB;

        $and_sql = Tools::get_and_where($where_arr);
        $view_col = Tools::get_view_col($view_cols);
        $order_sql = Tools::get_order($order_arr);
        $order = $amount ? '' : $order_sql;

        $sql = "SELECT {$view_col} FROM `" . $xoopsDB->prefix("jill_leave_cate") . "` WHERE 1 {$and_sql} {$order}";

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


    //以流水號秀出某筆 jill_leave_cate 資料內容 Jill_leave_cate::show()
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
            $all[$key] = Tools::filter($key, $value, 'read', self::$filter_arr);
        }

        

        $SweetAlert   = new SweetAlert();
        $SweetAlert->render('jill_leave_cate_destroy_func', "{$_SERVER['PHP_SELF']}?op=jill_leave_cate_destroy&cate_sn=", "cate_sn");

        if ($mode == "return") {
            return $all;
        } elseif ($mode == "assign_all") {
            $xoopsTpl->assign('jill_leave_cate', $all);
        } else {
            foreach ($all as $key => $value) {
                $xoopsTpl->assign($key, $value);
            }
        }
    }


    //以流水號取得某筆 jill_leave_cate 資料 Jill_leave_cate::get()
    public static function get($where_arr = [], $other_arr = [], $filter = 'read', $only_key = '')
    {
        global $xoopsDB;

        if (empty($where_arr)) {
            redirect_header($_SERVER['HTTP_REFERER'], 3, "無查詢條件：" . __FILE__ . __LINE__);
        }

        $and_sql = Tools::get_and_where($where_arr);

        $sql = "SELECT * FROM `" . $xoopsDB->prefix("jill_leave_cate") . "` WHERE 1 $and_sql";
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


    //jill_leave_cate 編輯表單
    public static function create($cate_sn = '' )
    {
        global $xoopsTpl, $xoopsUser;
        Tools::chk_is_adm('', '', __FILE__, __LINE__);

        //抓取預設值
        $jill_leave_cate = (!empty($cate_sn)) ? self::get(['cate_sn' =>$cate_sn]) : [];

        //預設值設定
        
        $def['cate_sn'] = $cate_sn;
        $def['cate_title'] = '';
        $def['cate_sort'] = self::max_sort();
        $def['enable'] = 1;

        if (empty($jill_leave_cate)) {
            $jill_leave_cate = $def;
        }

        foreach ($jill_leave_cate as $key => $value) {
            $xoopsTpl->assign($key, Tools::filter($key, $value, 'edit', self::$filter_arr));
        }

        $op = (!empty($cate_sn)) ? "jill_leave_cate_update" : "jill_leave_cate_store";
        $xoopsTpl->assign('next_op', $op);

        //套用formValidator驗證機制
        $formValidator = new FormValidator("#myForm", true);
        $formValidator->render();

        
    
        //加入Token安全機制
        Utility::token_form();
    }


    //新增資料到 jill_leave_cate Jill_leave_cate::store()
    public static function store($data_arr = [])
    {
        global $xoopsDB, $xoopsUser;
        Tools::chk_is_adm('', '', __FILE__, __LINE__);

        //XOOPS表單安全檢查
        if (empty($data_arr)) {
            Utility::xoops_security_check();
            $data_arr = $_POST;
        }

        //僅取用已知欄位，禁止 $$key 展開 POST（防止覆寫區域變數注入 SQL）
        $cate_title = Tools::filter('cate_title', $data_arr['cate_title'] ?? '', 'write', self::$filter_arr);
        $cate_sort = Tools::filter('cate_sort', $data_arr['cate_sort'] ?? 0, 'write', self::$filter_arr);
        $enable = Tools::filter('enable', $data_arr['enable'] ?? 0, 'write', self::$filter_arr);

        $sql = "INSERT INTO `" . $xoopsDB->prefix("jill_leave_cate") . "` (
            `cate_title`, 
            `cate_sort`, 
            `enable`
        ) VALUES(
            '{$cate_title}', 
            '{$cate_sort}', 
            '{$enable}'
        )";
        $xoopsDB->queryF($sql) or Utility::web_error($sql);

        //取得最後新增資料的流水編號
        $cate_sn = $xoopsDB->getInsertId();
        
        return $cate_sn;
    }


    //更新 jill_leave_cate 某一筆資料 Jill_leave_cate::update()
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
            $sql = "UPDATE `" . $xoopsDB->prefix("jill_leave_cate") . "` SET
            $update_cols WHERE 1 $and";
        } else {
            //XOOPS表單安全檢查
            Utility::xoops_security_check(__FILE__, __LINE__);

            //僅取用已知欄位，禁止 $$key 展開 POST（防止覆寫 $and 注入 WHERE）
            $cate_title = Tools::filter('cate_title', $_POST['cate_title'] ?? '', 'write', self::$filter_arr);
            $cate_sort = Tools::filter('cate_sort', $_POST['cate_sort'] ?? 0, 'write', self::$filter_arr);
            $enable = Tools::filter('enable', $_POST['enable'] ?? 0, 'write', self::$filter_arr);

            $sql = "UPDATE `" . $xoopsDB->prefix("jill_leave_cate") . "` SET
            `cate_title` = '{$cate_title}', 
            `cate_sort` = '{$cate_sort}', 
            `enable` = '{$enable}'
            WHERE 1 $and";
        }
        $xoopsDB->queryF($sql) or Utility::web_error($sql);
        
        return $where_arr['cate_sn'];
    }


    //刪除 jill_leave_cate 某筆資料資料 Jill_leave_cate::destroy()
    public static function destroy($cate_sn = '')
    {
        global $xoopsDB;
        Tools::chk_is_adm('', '', __FILE__, __LINE__);

        if(empty($cate_sn)) {
            return;
        }

        $and = '';
        if($cate_sn){
        $and .= "and `cate_sn` = '$cate_sn'";
    }
    

        $sql = "DELETE FROM `" . $xoopsDB->prefix("jill_leave_cate") . "`
        WHERE 1 $and";
        $xoopsDB->queryF($sql) or Utility::web_error($sql);
        
    }




    //自動取得 jill_leave_cate 的最新排序 Jill_leave_cate::get_max()
    public static function max_sort()
    {
        global $xoopsDB;
        $sql = "select max(`cate_sort`) from `" . $xoopsDB->prefix("jill_leave_cate") . "`";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql);
        list($sort) = $xoopsDB->fetchRow($result);
        return ++$sort;
    }


    //AJAX 拖曳排序，批次更新 cate_sort Jill_leave_cate::update_sort()
    public static function update_sort()
    {
        global $xoopsDB;
        Tools::chk_is_adm('', '', __FILE__, __LINE__);

        if (empty($_POST['tr']) || !is_array($_POST['tr'])) {
            return _TAD_SORT_FAIL;
        }

        $sort = 1;
        foreach ($_POST['tr'] as $cate_sn) {
            $cate_sn = (int) $cate_sn;
            $sql = "UPDATE `" . $xoopsDB->prefix("jill_leave_cate") . "` SET `cate_sort` = '{$sort}' WHERE `cate_sn` = '{$cate_sn}'";
            $xoopsDB->queryF($sql) or die(_TAD_SORT_FAIL . " (" . date("Y-m-d H:i:s") . ")");
            $sort++;
        }
        return _TAD_SORTED . " (" . date("Y-m-d H:i:s") . ")";
    }

    // 更新啟用狀態
    public static function update_enable($cate_sn = 0)
    {
        global $xoopsDB;
        $cate_sn = (int) $cate_sn;
        if (empty($cate_sn)) {
            return false;
        }
        Tools::chk_is_adm('', '', __FILE__, __LINE__);
        
        $sql = "SELECT `enable` FROM `" . $xoopsDB->prefix("jill_leave_cate") . "` WHERE `cate_sn` = '{$cate_sn}'";
        $result = $xoopsDB->query($sql);
        list($enable) = $xoopsDB->fetchRow($result);
        
        $new_enable = ($enable == 1) ? 0 : 1;
        
        $sql = "UPDATE `" . $xoopsDB->prefix("jill_leave_cate") . "` SET `enable` = '{$new_enable}' WHERE `cate_sn` = '{$cate_sn}'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql);
        return true;
    }
}
