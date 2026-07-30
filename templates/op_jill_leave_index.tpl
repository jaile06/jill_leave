<{if $all_jill_leave|default:false}>
    <div id="jill_leave_save_msg"></div>

    <div class="table-responsive">
        <table data-toggle="table" data-pagination="true" data-search="true" data-search-highlight="true" data-mobile-responsive="true" class="table table-sm table-striped table-hover">
        <thead>
            <tr>
            <!--請假者姓名-->
            <th scope="col" data-field="leavers" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_LEAVERS}></th>
            <!--假別-->
            <th scope="col" data-field="cate_sn" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_CATE}></th>
            <!--是否導師-->
            <th scope="col" data-field="is_advisor" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_IS_ADVISOR}></th>
            <!--導師班級-->
            <th scope="col" data-field="grade_class" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_GRADE_CLASS}></th>
            <!--起始日期-->
            <th scope="col" data-field="start_date" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_START_DATE}></th>
            <!--結束日期-->
            <th scope="col" data-field="end_date" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_END_DATE}></th>
            <!--審核狀態-->
            <th scope="col" data-field="status" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_STATUS}></th>
            <!--申請時間-->
            <th scope="col" data-field="create_date" data-sortable="true" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_CREATE_DATE}></th>
                <{if $smarty.session.now_user|default:false}>
                    <th scope="col"><{$smarty.const._TAD_FUNCTION}></th>
                <{/if}>
            </tr>
        </thead>
        <tbody>
        <{foreach from=$all_jill_leave item=data}>
            <tr>

            <!--請假者姓名-->
            <td><a href="<{$xoops_url}>/modules/jill_leave/index.php?sn=<{$data.sn}>"><{$data.leavers}></a></td>

            <!--假別-->
            <td><{$data.cate_sn_title}></td>

            <!--是否導師-->
            <td class="text-center"><{$data.is_advisor_text}></td>

            <!--導師班級-->
            <td><{$data.grade_class}></td>

            <!--起始日期-->
            <td><{$data.start_date}></td>

            <!--結束日期-->
            <td><{$data.end_date}></td>

            <!--審核狀態-->
            <td class="text-center">
                <{if $data.can_update_status|default:false}>
                    <button type="button" class="badge border-0 bg-<{$data.status_class}> update-status" data-sn="<{$data.sn}>" data-status="<{$data.status}>" style="cursor: pointer;" title="點選可切換狀態"><{$data.status_text}></button>
                <{else}>
                    <span class="badge bg-<{$data.status_class}>"><{$data.status_text}></span>
                <{/if}>
            </td>

            <!--申請時間-->
            <td><{$data.create_date}></td>

                <{if $smarty.session.now_user|default:false}>
                    <td>
                        <{if $data.can_delete|default:false}>
                            <a href="javascript:jill_leave_destroy_func(<{$data.sn}>);" class="btn btn-sm btn-danger" aria-label="<{$smarty.const._TAD_DEL}>" title="<{$smarty.const._TAD_DEL}>"><i class="fa fa-trash"></i></a>
                        <{/if}>
                        <{if $data.can_edit|default:false}>
                            <a href="<{$xoops_url}>/modules/jill_leave/index.php?op=jill_leave_edit&sn=<{$data.sn}>" class="btn btn-sm btn-warning" aria-label="<{$smarty.const._TAD_EDIT}>" title="<{$smarty.const._TAD_EDIT}>"><i class="fa fa-pencil"></i></a>
                        <{/if}>
                        <{if $data.can_export_pdf|default:false}>
                            <a href="<{$xoops_url}>/modules/jill_leave/pdf.php?sn=<{$data.sn}>" class="btn btn-sm btn-info" aria-label="匯出 PDF" title="匯出 PDF"><i class="fa fa-file-pdf"></i></a>
                        <{/if}>
                    </td>
                <{/if}>
            </tr>
        <{/foreach}>
        </tbody>
        </table>
    </div>

    <{if $smarty.session.now_user|default:false}>
        <div class="text-end my-3">
            <a href="<{$xoops_url}>/modules/jill_leave/index.php?op=jill_leave_create" class="btn btn-info">
                <i class="fa fa-plus"></i> <{$smarty.const._TAD_ADD}>
            </a>
        </div>
    <{/if}>


<{else}>
    <div class="alert alert-warning text-center">
        <{if $smarty.session.now_user|default:false}>
            <a href="<{$xoops_url}>/modules/jill_leave/index.php?op=jill_leave_create" class="btn btn-info">
                <i class="fa fa-plus"></i> <{$smarty.const._TAD_ADD}>
            </a>
        <{else}>
            <h3><{$smarty.const._TAD_EMPTY}></h3>
        <{/if}>
    </div>
<{/if}>

<{if $smarty.session.jill_leave_adm|default:false}>
<script>
window.jillLeaveConfig = {
    ajaxUrl: '<{$xoops_url}>/modules/jill_leave/index.php',
    csrfToken: '<{$csrf_token}>',
    statusLabels: <{$status_labels_json|default:'{}'}>,
    errorMsg: '<{$smarty.const._MD_JILLLEAVE_TOKEN_ERROR|default:"系統錯誤，無法變更狀態。"}>'
};
</script>
<script src="<{$xoops_url}>/modules/jill_leave/js/jill_leave_index.js"></script>
<{/if}>
