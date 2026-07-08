<div class="container py-1">
<{if $all_jill_leave_substitute|default:false}>
    <{if $smarty.session.jill_leave_adm|default:false}>
        
    <{/if}>

    <div id="jill_leave_substitute_save_msg"></div>

    <table data-toggle="table" data-pagination="true" data-search="true" data-mobile-responsive="true" class="table table-sm table-striped table-hover">
        <thead>
            <tr>
            <!--關聯請假編號-->
            <th data-field="sn" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_SN}></th>
            <!--代課日期-->
            <th data-field="substitute_date" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_SUBSTITUTE_DATE}></th>
            <!--支付方式 (self:自費 school:公費)-->
            <th data-field="pay" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_PAY}></th>
            <!--代課類型 (daily:日薪 hour:鐘點)-->
            <th data-field="type" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_TYPE}></th>
                <{if $smarty.session.jill_leave_adm|default:false}>
                    <th><{$smarty.const._TAD_FUNCTION}></th>
                <{/if}>
            </tr>
        </thead>
        <{foreach from=$all_jill_leave_substitute key=k item=data name=all_jill_leave_substitute}>
            <tr>
            <!--關聯請假編號-->
            <td><{$data.sn}></td>

            <!--代課日期-->
            <td><{$data.substitute_date}></td>

            <!--支付方式 (self:自費 school:公費)-->
            <td><{$data.pay}></td>

            <!--代課類型 (daily:日薪 hour:鐘點)-->
            <td><{$data.type}></td>

                <{if $smarty.session.jill_leave_adm|default:false}>
                    <td>
                        <a href="javascript:jill_leave_substitute_destroy_func(<{$data.substitute_sn}>);" class="btn btn-sm btn-danger" title="<{$smarty.const._TAD_DEL}>"><i class="fa fa-trash"></i></a>
                        <a href="<{$xoops_url}>/modules/jill_leave/substitute.php?op=jill_leave_substitute_edit&substitute_sn=<{$data.substitute_sn}>" class="btn btn-sm btn-warning" title="<{$smarty.const._TAD_EDIT}>"><i class="fa fa-pencil"></i></a>
                        
                    </td>
                <{/if}>
            </tr>
        <{/foreach}>
    </table>

    <{if $smarty.session.jill_leave_adm|default:false}>
        <div class="text-end my-3">
            <a href="<{$xoops_url}>/modules/jill_leave/substitute.php?op=jill_leave_substitute_create" class="btn btn-info">
                <i class="fa fa-plus"></i> <{$smarty.const._TAD_ADD}>
            </a>
        </div>
    <{/if}>

    <div class="bar"><{$bar|default:''}></div>
<{else}>
    <div class="alert alert-warning text-center">
        <{if $smarty.session.jill_leave_adm|default:false}>
            <a href="<{$xoops_url}>/modules/jill_leave/substitute.php?op=jill_leave_substitute_create" class="btn btn-info">
                <i class="fa fa-plus"></i> <{$smarty.const._TAD_ADD}>
            </a>
        <{else}>
            <h3><{$smarty.const._TAD_EMPTY}></h3>
        <{/if}>
    </div>
<{/if}>
</div>
