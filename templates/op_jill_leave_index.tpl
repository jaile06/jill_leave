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
        <{foreach from=$all_jill_leave key=k item=data name=all_jill_leave}>
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
                <{if $smarty.session.jill_leave_adm|default:false}>
                    <{if $data.status == 1}>
                        <button type="button" class="badge border-0 bg-success update-status" data-sn="<{$data.sn}>" data-status="1" style="cursor: pointer;" title="點選可切換狀態"><{$data.status_text}></button>
                    <{elseif $data.status == 2}>
                        <button type="button" class="badge border-0 bg-danger update-status" data-sn="<{$data.sn}>" data-status="2" style="cursor: pointer;" title="點選可切換狀態"><{$data.status_text}></button>
                    <{else}>
                        <button type="button" class="badge border-0 bg-secondary update-status" data-sn="<{$data.sn}>" data-status="0" style="cursor: pointer;" title="點選可切換狀態"><{$data.status_text}></button>
                    <{/if}>
                <{else}>
                    <{if $data.status == 1}>
                        <span class="badge bg-success"><{$data.status_text}></span>
                    <{elseif $data.status == 2}>
                        <span class="badge bg-danger"><{$data.status_text}></span>
                    <{else}>
                        <span class="badge bg-secondary"><{$data.status_text}></span>
                    <{/if}>
                <{/if}>
            </td>

            <!--申請時間-->
            <td><{$data.create_date}></td>

                <{if $smarty.session.now_user|default:false}>
                    <td>
                        <{if $smarty.session.jill_leave_adm|default:false or $data.uid == $smarty.session.now_user.uid|default:0}>
                            <{* 已通過的假單僅管理員可刪除/編輯 *}>
                            <{if $smarty.session.jill_leave_adm|default:false or $data.status != 1}>
                                <a href="javascript:jill_leave_destroy_func(<{$data.sn}>);" class="btn btn-sm btn-danger" aria-label="<{$smarty.const._TAD_DEL}>" title="<{$smarty.const._TAD_DEL}>"><i class="fa fa-trash"></i></a>
                                <a href="<{$xoops_url}>/modules/jill_leave/index.php?op=jill_leave_edit&sn=<{$data.sn}>" class="btn btn-sm btn-warning" aria-label="<{$smarty.const._TAD_EDIT}>" title="<{$smarty.const._TAD_EDIT}>"><i class="fa fa-pencil"></i></a>
                            <{/if}>
                            <a href="<{$xoops_url}>/modules/jill_leave/pdf.php?sn=<{$data.sn}>" class="btn btn-sm btn-info" aria-label="匯出 PDF" title="匯出 PDF"><i class="fa fa-file-pdf"></i></a>
                        <{/if}>
                    </td>
                <{/if}>
            </tr>
        <{/foreach}>
    </table>
    </div>

    <{if $smarty.session.now_user|default:false}>
        <div class="text-end my-3">
            <a href="<{$xoops_url}>/modules/jill_leave/index.php?op=jill_leave_create&cate_sn=<{$cate_sn|default:''}>" class="btn btn-info">
                <i class="fa fa-plus"></i> <{$smarty.const._TAD_ADD}>
            </a>
        </div>
    <{/if}>

    <div class="bar"><{$bar|default:''}></div>
<{else}>
    <div class="alert alert-warning text-center">
        <{if $smarty.session.now_user|default:false}>
            <a href="<{$xoops_url}>/modules/jill_leave/index.php?op=jill_leave_create&cate_sn=<{$cate_sn|default:''}>" class="btn btn-info">
                <i class="fa fa-plus"></i> <{$smarty.const._TAD_ADD}>
            </a>
        <{else}>
            <h3><{$smarty.const._TAD_EMPTY}></h3>
        <{/if}>
    </div>
<{/if}>
</div>

<{if $smarty.session.jill_leave_adm|default:false}>
<script>
$(function(){
    // 使用事件委託，防止 bootstrapTable 重新分頁/搜尋後事件失效
    $(document).on('click', '.update-status', function(e){
        e.preventDefault();
        var $span = $(this);
        if ($span.next('.status-select').length > 0) return; // 防止重複觸發

        var sn = $span.data('sn');
        var status = $span.data('status');
        var hasChanged = false;

        // 建立 select 下拉選單
        var $select = $('<select class="form-select form-select-sm status-select" style="width: auto; display: inline-block; padding: 2px 8px; font-size: 0.85rem;"></select>');
        $select.append('<option value="0">待審核</option>');
        $select.append('<option value="1">已通過</option>');
        $select.append('<option value="2">駁回</option>');
        $select.val(status);

        $span.hide();
        $span.after($select);
        $select.focus();

        // 變更狀態
        $select.on('change', function(){
            hasChanged = true;
            var nextStatus = parseInt($(this).val());
            $.post('index.php', {
                op: 'update_status',
                sn: sn,
                status: nextStatus,
                XOOPS_TOKEN_REQUEST: '<{$csrf_token}>'
            }, function(res){
                if (res.success) {
                    var $allBadges = $('.update-status[data-sn="' + sn + '"]');
                    $allBadges.each(function(){
                        var $b = $(this);
                        $b.data('status', nextStatus);
                        $b.text(res.status_text);
                        $b.removeClass('bg-success bg-danger bg-secondary');
                        if (nextStatus === 1) {
                            $b.addClass('bg-success');
                        } else if (nextStatus === 2) {
                            $b.addClass('bg-danger');
                        } else {
                            $b.addClass('bg-secondary');
                        }
                        $b.show();
                    });
                    $select.remove();
                } else {
                    alert(res.message || '更新審核狀態失敗');
                    $span.show();
                    $select.remove();
                }
            }, 'json').fail(function() {
                alert('系統錯誤，無法變更狀態。');
                $span.show();
                $select.remove();
            });
        });

        // 失去焦點復原
        $select.on('blur', function(){
            setTimeout(function(){
                if (!hasChanged) {
                    $span.show();
                    $select.remove();
                }
            }, 250);
        });
    });
});
</script>
<{/if}>
