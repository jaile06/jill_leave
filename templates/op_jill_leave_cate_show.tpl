<div class="container py-1">
<h1 class="my text-center">
    <a href="<{$smarty.server.PHP_SELF}>" class="text-black-50" data-bs-toggle="tooltip" title="<{$smarty.const._TAD_BACK_PAGE}>">
        <i class="fa-solid fa-turn-up fa-rotate-270"></i>
    </a>
    <{$cate_title}>
</h1>

<div class="text-center">
    <{if $smarty.session.jill_leave_adm|default:false}>
        <a href="javascript:jill_leave_cate_destroy_func(<{$cate_sn}>);" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="<{$smarty.const._TAD_DEL}>"><i class="fa fa-times" aria-hidden="true"></i></a>
        <a href="<{$xoops_url}>/modules/jill_leave/cate.php?op=jill_leave_cate_edit&cate_sn=<{$cate_sn}>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="<{$smarty.const._TAD_EDIT}>"><i class="fa fa-pencil" aria-hidden="true"></i> <{$smarty.const._TAD_EDIT}></a>
        <a href="<{$xoops_url}>/modules/jill_leave/cate.php?op=jill_leave_cate_create" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="<{$smarty.const._TAD_ADD}>"><i class="fa fa-plus" aria-hidden="true"></i> <{$smarty.const._TAD_ADD}></a>
    <{/if}>
</div>



<div class="vtb mt-3">
<!--啟用狀態 (1:啟用 0:停用)-->
<ul>
    <li class="w2 vtitle"><{$smarty.const._MD_JILLLEAVE_CATE_ENABLE}></li>
    <li class="w8"><{if $enable == 1}>啟用<{else}>停用<{/if}></li>
</ul>
</div>
</div>
