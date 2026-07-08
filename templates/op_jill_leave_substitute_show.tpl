<div class="container py-1">
<div class="text-center">
    <{if $smarty.session.jill_leave_adm|default:false}>
        <a href="javascript:jill_leave_substitute_destroy_func(<{$substitute_sn}>);" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="<{$smarty.const._TAD_DEL}>"><i class="fa fa-times" aria-hidden="true"></i></a>
        <a href="<{$xoops_url}>/modules/jill_leave/substitute.php?op=jill_leave_substitute_edit&substitute_sn=<{$substitute_sn}>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="<{$smarty.const._TAD_EDIT}>"><i class="fa fa-pencil" aria-hidden="true"></i> <{$smarty.const._TAD_EDIT}></a>
        <a href="<{$xoops_url}>/modules/jill_leave/substitute.php?op=jill_leave_substitute_create" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="<{$smarty.const._TAD_ADD}>"><i class="fa fa-plus" aria-hidden="true"></i> <{$smarty.const._TAD_ADD}></a>
    <{/if}>
</div>



<div class="vtb mt-3">
<!--關聯請假編號-->
<ul>
    <li class="w2 vtitle"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_SN}></li>
    <li class="w8"><{$sn}></li>
</ul>

<!--代課日期-->
<ul>
    <li class="w2 vtitle"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_SUBSTITUTE_DATE}></li>
    <li class="w8"><{$substitute_date}></li>
</ul>

<!--支付方式 (self:自費 school:公費)-->
<ul>
    <li class="w2 vtitle"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_PAY}></li>
    <li class="w8"><{$pay}></li>
</ul>

<!--代課類型 (daily:日薪 hour:鐘點)-->
<ul>
    <li class="w2 vtitle"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_TYPE}></li>
    <li class="w8"><{$type}></li>
</ul>
</div>
</div>
