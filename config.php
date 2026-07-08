<?php
use Xmf\Request;
use XoopsModules\Jill_leave\Tools;
use XoopsModules\Tadtools\Utility;
use XoopsModules\Tadtools\FormValidator;

/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';
xoops_loadLanguage('modinfo', 'jill_leave');
$GLOBALS['xoopsOption']['template_main'] = 'jill_leave_adm.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';

/*-----------變數過濾----------*/
$op = Request::getString('op');

//僅管理員可存取模組設定頁
Tools::chk_is_adm('', '', __FILE__, __LINE__);

/*-----------執行動作判斷區----------*/
switch ($op) {

    //儲存所有設定
    case 'save_config':
        Utility::xoops_security_check();

        //清洗管理人員 Email (用分號隔開，去除句尾的分號或逗號及空白)
        $adm_email = trim(Request::getString('adm_email'));
        $adm_email = preg_replace('/[;,]+$/', '', $adm_email);
        $email_arr = array_filter(array_map('trim', explode(';', $adm_email)));
        $adm_email = implode(';', $email_arr);

        //清洗年級
        $grade = trim(Request::getString('grade'));
        $grade = preg_replace('/[;,]+$/', '', $grade);
        $grade_arr = array_filter(array_map('intval', explode(',', $grade)));
        $grade = implode(',', $grade_arr);

        //清洗節次 (用逗號隔開，去除句尾的分號或逗號及空白)
        $class_period = trim(Request::getString('class_period'));
        $class_period = preg_replace('/[;,]+$/', '', $class_period);
        $period_arr = array_filter(array_map('trim', explode(',', $class_period)));
        $class_period = implode(',', $period_arr);

        //全部統一走模組偏好設定
        Tools::set_module_config('adm_email', $adm_email);
        Tools::set_module_config('grade', $grade);
        Tools::set_module_config('class_room', (string) Request::getInt('class_room'));
        Tools::set_module_config('class_period', $class_period);

        //更新管理員 session
        unset($_SESSION['jill_leave_adm']);
        Tools::get_session();

        header("location: {$_SERVER['PHP_SELF']}");
        exit;

    //預設動作：顯示設定表單
    default:
        config_form();
        $op = 'jill_leave_config_form';
        break;
}

require_once __DIR__ . '/footer.php';

/*-----------功能函數區----------*/

//模組設定表單
function config_form()
{
    global $xoopsTpl;

    //全部從模組偏好設定讀取
    $moduleConfig = $GLOBALS['xoopsModuleConfig'] ?? [];

    $xoopsTpl->assign('adm_email', $moduleConfig['adm_email'] ?? '');
    $xoopsTpl->assign('grade', $moduleConfig['grade'] ?? '1,2,3,4,5,6');
    $xoopsTpl->assign('class_room', (int) ($moduleConfig['class_room'] ?? 10));
    $xoopsTpl->assign('class_period', $moduleConfig['class_period'] ?? _MI_JILLLEAVE_CLASS_PERIOD_DEFAULT);

    //套用formValidator驗證機制
    $formValidator = new FormValidator("#myForm", true);
    $formValidator->render();

    //加入Token安全機制
    Utility::token_form();
}
