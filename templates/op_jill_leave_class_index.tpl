<div class="container py-1">
<{if $all_jill_leave_class|default:false}>
    <{if $smarty.session.jill_leave_adm|default:false}>
        
    <{/if}>

    <div id="jill_leave_class_save_msg"></div>

    <table data-toggle="table" data-pagination="true" data-search="true" data-mobile-responsive="true" class="table table-sm table-striped table-hover">
        <thead>
            <tr>
            <!--關聯代課編號-->
            <th data-field="substitute_sn" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_CLASS_SUBSTITUTE_SN}></th>
            <!--關聯請假編號-->
            <th data-field="sn" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_CLASS_SN}></th>
            <!--節次 (例如: 1, 2, 早自習)-->
            <th data-field="class_period" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_CLASS_CLASS_PERIOD}></th>
            <!--科目-->
            <th data-field="subject" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_CLASS_SUBJECT}></th>
            <!--代課老師-->
            <th data-field="substitute_teacher" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_CLASS_SUBSTITUTE_TEACHER}></th>
                <{if $smarty.session.jill_leave_adm|default:false}>
                    <th><{$smarty.const._TAD_FUNCTION}></th>
                <{/if}>
            </tr>
        </thead>
        <{foreach from=$all_jill_leave_class key=k item=data name=all_jill_leave_class}>
            <tr>
            <!--關聯代課編號-->
            <td><{$data.substitute_sn}></td>

            <!--關聯請假編號-->
            <td><{$data.sn}></td>

            <!--節次 (例如: 1, 2, 早自習)-->
            <td><{$data.class_period}></td>

            <!--科目-->
            <td><a href="<{$xoops_url}>/modules/jill_leave/index.php?class_sn=<{$data.class_sn}>"><{$data.subject}></a></td>

            <!--代課老師-->
            <td><{$data.substitute_teacher}></td>

                <{if $smarty.session.jill_leave_adm|default:false}>
                    <td>
                        <a href="javascript:jill_leave_class_destroy_func(<{$data.class_sn}>);" class="btn btn-sm btn-danger" title="<{$smarty.const._TAD_DEL}>"><i class="fa fa-trash"></i></a>
                        <a href="<{$xoops_url}>/modules/jill_leave/index.php?op=jill_leave_class_edit&class_sn=<{$data.class_sn}>" class="btn btn-sm btn-warning" title="<{$smarty.const._TAD_EDIT}>"><i class="fa fa-pencil"></i></a>
                        
                    </td>
                <{/if}>
            </tr>
        <{/foreach}>
    </table>

    <{if $smarty.session.jill_leave_adm|default:false}>
        <div class="text-end my-3">
            <a href="<{$xoops_url}>/modules/jill_leave/index.php?op=jill_leave_class_create" class="btn btn-info">
                <i class="fa fa-plus"></i> <{$smarty.const._TAD_ADD}>
            </a>
        </div>
    <{/if}>

    <div class="bar"><{$bar|default:''}></div>
<{else}>
    <div class="alert alert-warning text-center">
        <{if $smarty.session.jill_leave_adm|default:false}>
            <a href="<{$xoops_url}>/modules/jill_leave/index.php?op=jill_leave_class_create" class="btn btn-info">
                <i class="fa fa-plus"></i> <{$smarty.const._TAD_ADD}>
            </a>
        <{else}>
            <h3><{$smarty.const._TAD_EMPTY}></h3>
        <{/if}>
    </div>
<{/if}>
</div>
