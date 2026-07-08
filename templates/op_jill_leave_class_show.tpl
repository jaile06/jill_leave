<div class="container py-1">
<h1 class="my text-center">
    <a href="<{$smarty.server.PHP_SELF}>" class="text-black-50" data-bs-toggle="tooltip" title="<{$smarty.const._TAD_BACK_PAGE}>">
        <i class="fa-solid fa-turn-up fa-rotate-270"></i>
    </a>
    <{$subject}>
</h1>

<div class="text-center">
    <{if $smarty.session.jill_leave_adm|default:false}>
        <a href="javascript:jill_leave_class_destroy_func(<{$class_sn}>);" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="<{$smarty.const._TAD_DEL}>"><i class="fa fa-times" aria-hidden="true"></i></a>
        <a href="<{$xoops_url}>/modules/jill_leave/index.php?op=jill_leave_class_edit&class_sn=<{$class_sn}>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="<{$smarty.const._TAD_EDIT}>"><i class="fa fa-pencil" aria-hidden="true"></i> <{$smarty.const._TAD_EDIT}></a>
        <a href="<{$xoops_url}>/modules/jill_leave/index.php?op=jill_leave_class_create" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="<{$smarty.const._TAD_ADD}>"><i class="fa fa-plus" aria-hidden="true"></i> <{$smarty.const._TAD_ADD}></a>
    <{/if}>
</div>



<div class="vtb mt-3">
<!--關聯代課編號-->
<ul>
    <li class="w2 vtitle"><{$smarty.const._MD_JILLLEAVE_CLASS_SUBSTITUTE_SN}></li>
    <li class="w8"><{$substitute_sn}></li>
</ul>

<!--關聯請假編號-->
<ul>
    <li class="w2 vtitle"><{$smarty.const._MD_JILLLEAVE_CLASS_SN}></li>
    <li class="w8"><{$sn}></li>
</ul>

<!--節次 (例如: 1, 2, 早自習)-->
<ul>
    <li class="w2 vtitle"><{$smarty.const._MD_JILLLEAVE_CLASS_CLASS_PERIOD}></li>
    <li class="w8"><{$class_period}></li>
</ul>

<!--代課老師-->
<ul>
    <li class="w2 vtitle"><{$smarty.const._MD_JILLLEAVE_CLASS_SUBSTITUTE_TEACHER}></li>
    <li class="w8"><{$substitute_teacher}></li>
</ul>
</div>
</div>
