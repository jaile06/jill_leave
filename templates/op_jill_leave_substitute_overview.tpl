<div class="container py-1">
<!--月份篩選與匯出-->
<form action="<{$smarty.server.PHP_SELF|escape}>" method="get" class="row g-2 align-items-center mb-3">
    <div class="col-auto">
        <label for="month" class="col-form-label"><{$smarty.const._MD_JILLLEAVE_MONTH}></label>
    </div>
    <div class="col-auto">
        <input type="text" name="month" id="month" class="form-control" value="<{$month}>" onClick="WdatePicker({dateFmt:'yyyy-MM'})">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> <{$smarty.const._MD_JILLLEAVE_FILTER}></button>
    </div>
    <div class="col-auto">
        <a href="<{$smarty.server.PHP_SELF|escape}>?op=export_excel&month=<{$month}>" class="btn btn-success">
            <i class="fa fa-file-excel"></i> <{$smarty.const._MD_JILLLEAVE_EXPORT_EXCEL}>
        </a>
    </div>
</form>

<{if $all_substitute|default:false}>
    <div class="table-responsive">
    <table data-toggle="table" data-search="true" data-mobile-responsive="true" class="table table-sm table-striped table-hover">
        <thead>
            <tr>
                <th scope="col" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_SUBSTITUTE_DATE}></th>
                <th scope="col" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_LEAVERS}></th>
                <th scope="col" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_CATE_CATE_TITLE}></th>
                <th scope="col" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_GRADE_CLASS}></th>
                <th scope="col" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_CLASS_CLASS_PERIOD}> / <{$smarty.const._MD_JILLLEAVE_CLASS_SUBJECT}> / <{$smarty.const._MD_JILLLEAVE_CLASS_SUBSTITUTE_TEACHER}></th>
                <th scope="col" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_PAY}></th>
                <th scope="col" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_TYPE}></th>
                <th scope="col" class="nowrap c"><{$smarty.const._MD_JILLLEAVE_STATUS}></th>
                <th scope="col"><{$smarty.const._TAD_FUNCTION}></th>
            </tr>
        </thead>
        <tbody>
            <{foreach from=$all_substitute item=data}>
                <tr>
                    <td class="nowrap"><{$data.substitute_date}></td>
                    <td class="nowrap"><a href="<{$xoops_url}>/modules/jill_leave/index.php?sn=<{$data.sn}>"><{$data.leavers}></a></td>
                    <td class="nowrap"><{$data.cate_title}></td>
                    <td class="nowrap"><{$data.grade_class}></td>
                    <td>
                        <{foreach from=$data.classes item=class}>
                            <div><span class="badge bg-info"><{$class.class_period}></span> <{if $class.grade_class}><{$class.grade_class}> <{/if}><{$class.subject}> - <{$class.substitute_teacher}></div>
                        <{foreachelse}>
                            -
                        <{/foreach}>
                    </td>
                    <td class="nowrap c"><{$data.pay_text}></td>
                    <td class="nowrap c"><{$data.type_text}></td>
                    <td class="nowrap c">
                        <{if $data.status == 1}>
                            <button type="button" class="badge border-0 bg-success update-status" data-sn="<{$data.sn}>" data-status="1" style="cursor: pointer;" title="點選可切換狀態"><{$data.status_text}></button>
                        <{elseif $data.status == 2}>
                            <button type="button" class="badge border-0 bg-danger update-status" data-sn="<{$data.sn}>" data-status="2" style="cursor: pointer;" title="點選可切換狀態"><{$data.status_text}></button>
                        <{else}>
                            <button type="button" class="badge border-0 bg-secondary update-status" data-sn="<{$data.sn}>" data-status="0" style="cursor: pointer;" title="點選可切換狀態"><{$data.status_text}></button>
                        <{/if}>
                    </td>
                    <td class="nowrap">
                        <a href="javascript:jill_leave_destroy_func(<{$data.sn}>);" class="btn btn-sm btn-danger" title="<{$smarty.const._TAD_DEL}>"><i class="fa fa-trash"></i></a>
                        <a href="<{$xoops_url}>/modules/jill_leave/index.php?op=jill_leave_edit&sn=<{$data.sn}>" class="btn btn-sm btn-warning" title="<{$smarty.const._TAD_EDIT}>"><i class="fa fa-pencil"></i></a>
                        <a href="<{$xoops_url}>/modules/jill_leave/pdf.php?sn=<{$data.sn}>" class="btn btn-sm btn-info" title="匯出 PDF"><i class="fa fa-file-pdf"></i></a>
                    </td>
                </tr>
            <{/foreach}>
        </tbody>
    </table>
    </div>
<{else}>
    <div class="alert alert-warning text-center">
        <h3><{$smarty.const._TAD_EMPTY}></h3>
    </div>
<{/if}>
</div>

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
                status: nextStatus
            }, function(res){
                if (res.success) {
                    // 同步更新同一筆請假單的所有狀態顯示
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

