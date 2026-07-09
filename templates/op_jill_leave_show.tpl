<div class="container py-1">
<h1 class="my text-center">
    <a href="<{$smarty.server.PHP_SELF|escape}>" class="text-black-50" aria-label="<{$smarty.const._TAD_BACK_PAGE}>" data-bs-toggle="tooltip" title="<{$smarty.const._TAD_BACK_PAGE}>">
        <i class="fa-solid fa-turn-up fa-rotate-270"></i>
    </a>
    <{$leavers}>
</h1>

<div class="text-center">
    <{if $can_edit|default:false}>
        <a href="javascript:jill_leave_destroy_func(<{$sn}>);" class="btn btn-sm btn-danger" aria-label="<{$smarty.const._TAD_DEL}>" data-bs-toggle="tooltip" title="<{$smarty.const._TAD_DEL}>"><i class="fa fa-times" aria-hidden="true"></i></a>
        <a href="<{$xoops_url}>/modules/jill_leave/index.php?op=jill_leave_edit&sn=<{$sn}>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="<{$smarty.const._TAD_EDIT}>"><i class="fa fa-pencil" aria-hidden="true"></i> <{$smarty.const._TAD_EDIT}></a>
    <{/if}>
    <{if $can_manage|default:false}>
        <a href="<{$xoops_url}>/modules/jill_leave/index.php?op=jill_leave_create" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="<{$smarty.const._TAD_ADD}>"><i class="fa fa-plus" aria-hidden="true"></i> <{$smarty.const._TAD_ADD}></a>
        <a href="<{$xoops_url}>/modules/jill_leave/pdf.php?sn=<{$sn}>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="匯出 PDF"><i class="fa fa-file-pdf" aria-hidden="true"></i> PDF</a>
    <{/if}>
</div>
<div class="text-center">
    <div class="alert alert-warning d-inline-block text-center py-1 px-4 my-3 mx-auto">
        <i class="fa fa-user"></i> <{$uid_name}>
        <i class="fa fa-calendar"></i> <{$update_date}>
        <i class="fa fa-folder-open"></i> <{$cate_sn_title}>
    </div>
</div>

<div class="vtb mt-3">
<!--是否導師-->
<ul>
    <li class="w2 vtitle"><{$smarty.const._MD_JILLLEAVE_IS_ADVISOR}></li>
    <li class="w8"><{$is_advisor_text}></li>
</ul>

<!--導師班級（科任無單一班級，班級列於下方代課明細）-->
<{if $grade_class|default:''}>
<ul>
    <li class="w2 vtitle"><{$smarty.const._MD_JILLLEAVE_GRADE_CLASS}></li>
    <li class="w8"><{$grade_class}></li>
</ul>
<{/if}>

<!--起始日期-->
<ul>
    <li class="w2 vtitle"><{$smarty.const._MD_JILLLEAVE_START_DATE}></li>
    <li class="w8"><{$start_date}></li>
</ul>

<!--結束日期-->
<ul>
    <li class="w2 vtitle"><{$smarty.const._MD_JILLLEAVE_END_DATE}></li>
    <li class="w8"><{$end_date}></li>
</ul>

<!--審核狀態-->
<ul>
    <li class="w2 vtitle"><{$smarty.const._MD_JILLLEAVE_STATUS}></li>
    <li class="w8"><{$status_text}></li>
</ul>
</div>

<!--代課資訊-->
<{if $substitutes|default:false}>
    <h4 class="mt-4"><i class="fa fa-users"></i> <{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_INFO}></h4>
    <div class="table-responsive">
        <table class="table table-sm table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_SUBSTITUTE_DATE}></th>
                    <th scope="col"><{$smarty.const._MD_JILLLEAVE_CLASS_CLASS_PERIOD}></th>
                    <th scope="col"><{$smarty.const._MD_JILLLEAVE_GRADE_CLASS}></th>
                    <th scope="col"><{$smarty.const._MD_JILLLEAVE_CLASS_SUBJECT}></th>
                    <th scope="col"><{$smarty.const._MD_JILLLEAVE_CLASS_SUBSTITUTE_TEACHER}></th>
                    <th scope="col"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_PAY}></th>
                    <th scope="col"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_TYPE}></th>
                </tr>
            </thead>
            <tbody>
                <{foreach from=$substitutes item=substitute}>
                    <{if $substitute.classes|default:false}>
                        <{foreach from=$substitute.classes item=class}>
                            <tr>
                                <td><{$substitute.substitute_date}></td>
                                <td><{$class.class_period}></td>
                                <td><{$class.grade_class|default:$grade_class}></td>
                                <td><{$class.subject}></td>
                                <td><{$class.substitute_teacher}></td>
                                <td><{$substitute.pay_text}></td>
                                <td><{$substitute.type_text}></td>
                            </tr>
                        <{/foreach}>
                    <{else}>
                        <tr>
                            <td><{$substitute.substitute_date}></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><{$substitute.pay_text}></td>
                            <td><{$substitute.type_text}></td>
                        </tr>
                    <{/if}>
                <{/foreach}>
            </tbody>
        </table>
    </div>
<{/if}>
</div>
