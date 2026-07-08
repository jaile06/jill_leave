<?php

use XoopsModules\Jill_leave\Jill_leave;

//區塊主函式 (jill_leave_show)：顯示 end_date >= 今天 且 status = 1 (已通過) 的請假紀錄
function jill_leave_show($options)
{
    if (!class_exists('XoopsModules\Jill_leave\Jill_leave')) {
        require XOOPS_ROOT_PATH . '/modules/jill_leave/preloads/autoloader.php';
    }
    xoops_loadLanguage('main', 'jill_leave');
    xoops_loadLanguage('blocks', 'jill_leave');

    $limit = empty($options[0]) ? 10 : (int) $options[0];

    $block = [];
    $block['leaves'] = Jill_leave::announcement($limit);
    $block['jill_leave_url'] = XOOPS_URL . '/modules/jill_leave/index.php';

    return $block;
}

//區塊編輯函式 (jill_leave_show_edit)
function jill_leave_show_edit($options)
{
    xoops_loadLanguage('blocks', 'jill_leave');

    $form = "
    <ol class='my-form'>
        <li>" . _MB_JILLLEAVE_SHOW_LIMIT . "：
            <input type='number' name='options[0]' value='{$options[0]}' size='4'>
        </li>
    </ol>
    ";
    return $form;
}
