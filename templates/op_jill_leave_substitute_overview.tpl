<style>
    /* 狀態下拉選單美化 - 無障礙（a11y）高對比規格 */
    .status-select {
        font-weight: 600;
        min-width: 115px;
    }
    /* 狀態 0: 待審核 (橘色調，文字對比度 5.2:1) */
    .status-select-0 { background-color: #fffbeb !important; color: #b45309 !important; border-color: #fde68a !important; }
    /* 狀態 1: 已通過 (綠色調，文字對比度 5.1:1) */
    .status-select-1 { background-color: #ecfdf5 !important; color: #047857 !important; border-color: #a7f3d0 !important; }
    /* 狀態 2: 駁回 (紅色調，文字對比度 5.7:1) */
    .status-select-2 { background-color: #fef2f2 !important; color: #b91c1c !important; border-color: #fecaca !important; }
</style>

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

<{if $all_leaves|default:false}>
    <div class="table-responsive">
    <table class="table table-hover align-middle mt-2">
        <thead class="table-light border-bottom border-2">
            <tr>
                <th scope="col" class="py-3 px-3 text-dark fw-bold" style="width: 20%;"><{$smarty.const._MD_JILLLEAVE_LEAVERS}></th>
                <th scope="col" class="nowrap py-3 px-3 text-dark fw-bold" style="width: 15%;"><{$smarty.const._MD_JILLLEAVE_CATE_CATE_TITLE}></th>
                <th scope="col" class="nowrap py-3 px-3 text-dark fw-bold" style="width: 25%;">起迄日期</th>
                <th scope="col" class="py-3 px-3 text-dark fw-bold" style="width: 15%;">課務資訊</th>
                <th scope="col" class="py-3 px-3 text-dark fw-bold" style="width: 25%;"><{$smarty.const._TAD_FUNCTION}></th>
            </tr>
        </thead>
        <tbody>
            <{foreach from=$all_leaves item=leave name=lv}>
                <!-- 請假單標題行 -->
                <tr class="align-middle bg-white">
                    <td class="nowrap py-3 px-3">
                        <a href="<{$xoops_url}>/modules/jill_leave/index.php?sn=<{$leave.sn}>" class="fw-bold text-primary text-decoration-none">
                            <{$leave.leavers}>
                        </a>
                        <span class="text-secondary small ms-1"><{if $leave.is_advisor}>(<{$leave.grade_class}>)<{else}>(科任)<{/if}></span>
                    </td>
                    <td class="nowrap py-3 px-3">
                        <span class="fw-semibold text-dark"><{$leave.cate_title}></span>
                    </td>
                    <td class="nowrap py-3 px-3">
                        <span class="text-secondary small"><{$leave.start_date}> ~ <{$leave.end_date}></span>
                    </td>
                    <td class="py-3 px-3">
                        <{if $leave.substitutes}>
                            <button type="button" class="btn btn-sm btn-outline-secondary expand-toggle rounded" data-sn="<{$leave.sn}>" style="font-weight: 500; font-size: 0.85rem;" aria-expanded="false" aria-label="展開課務明細">
                                <i class="fa fa-plus me-1"></i> <{$leave.substitutes|@count}> 筆課程
                            </button>
                        <{else}>
                            <span class="text-muted small">無課務</span>
                        <{/if}>
                    </td>
                    <td class="nowrap py-3 px-3">
                        <select class="form-select form-select-sm update-status status-select status-select-<{$leave.status}> d-inline-block align-middle me-2" data-sn="<{$leave.sn}>" aria-label="變更 <{$leave.leavers}> 的審核狀態">
                            <option value="0" <{if $leave.status == 0}>selected<{/if}>>待審核</option>
                            <option value="1" <{if $leave.status == 1}>selected<{/if}>>已通過</option>
                            <option value="2" <{if $leave.status == 2}>selected<{/if}>>駁回</option>
                        </select>
                        
                        <div class="d-inline-flex gap-1 align-items-center align-middle">
                            <a href="<{$xoops_url}>/modules/jill_leave/pdf.php?sn=<{$leave.sn}>" class="btn btn-sm btn-outline-info" title="匯出 PDF" aria-label="匯出請假單 PDF (單號 <{$leave.sn}>)"><i class="fa fa-file-pdf"></i></a>
                            <a href="<{$xoops_url}>/modules/jill_leave/index.php?op=jill_leave_edit&sn=<{$leave.sn}>" class="btn btn-sm btn-warning" title="<{$smarty.const._TAD_EDIT}>" aria-label="編輯請假單 (單號 <{$leave.sn}>)"><i class="fa fa-pencil"></i></a>
                            <a href="javascript:jill_leave_destroy_func(<{$leave.sn}>);" class="btn btn-sm btn-danger" title="<{$smarty.const._TAD_DEL}>" aria-label="刪除請假單 (單號 <{$leave.sn}>)"><i class="fa fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <!-- 課務詳細行（預設隱藏） -->
                <{if $leave.substitutes}>
                    <tr class="substitute-detail table-light" data-sn="<{$leave.sn}>" style="display: none;">
                        <td colspan="5" class="p-0 border-bottom-0">
                            <div class="card card-body m-3 shadow-sm border-light">
                                <{foreach from=$leave.substitutes item=substitute_sn}>
                                    <{assign var=substitute value=$all_substitute_detail[$substitute_sn]}>
                                    <div class="mb-3 pb-3 border-bottom last-no-border">
                                        <div class="fw-bold text-dark border-start border-3 border-primary ps-2 mb-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fa fa-calendar-check text-primary me-1"></i> <{$substitute.substitute_date}>
                                            </div>
                                            <span class="badge bg-light text-dark border" style="font-size: 0.8rem; font-weight: normal;"><{$substitute.pay_text}> / <{$substitute.type_text}></span>
                                        </div>
                                        <div class="row g-2">
                                            <{foreach from=$substitute.classes item=class}>
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="p-2 mb-2 bg-light border rounded d-flex align-items-center justify-content-between small">
                                                        <div>
                                                            <span class="badge bg-secondary me-2"><{$class.class_period}></span>
                                                            <span class="fw-bold"><{if $class.grade_class}><{$class.grade_class}> <{/if}><{$class.subject}></span>
                                                        </div>
                                                        <div class="text-success fw-bold">
                                                            <i class="fa fa-user me-1"></i><{$class.substitute_teacher}>
                                                        </div>
                                                    </div>
                                                </div>
                                            <{foreachelse}>
                                                <div class="col-12 text-muted ps-3">無節次明細</div>
                                            <{/foreach}>
                                        </div>
                                    </div>
                                <{/foreach}>
                            </div>
                        </td>
                    </tr>
                <{/if}>
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
    // 展開/折疊課務詳細行
    $(document).on('click', '.expand-toggle', function(e){
        e.preventDefault();
        var $btn = $(this);
        var sn = $btn.data('sn');
        var $details = $('.substitute-detail[data-sn="' + sn + '"]');

        $details.slideToggle(200, function() {
            var isVisible = $details.is(':visible');
            $btn.attr('aria-expanded', isVisible ? 'true' : 'false');
        });
        $btn.find('i').toggleClass('fa-plus fa-minus');
    });

    // 審核狀態下拉選單變更
    $(document).on('change', '.update-status', function(e){
        var $select = $(this);
        var sn = $select.data('sn');
        var nextStatus = parseInt($select.val());
        
        // 更新狀態 Select 的樣式 class
        $select.removeClass('status-select-0 status-select-1 status-select-2')
               .addClass('status-select-' + nextStatus);

        $.post('index.php', {
            op: 'update_status',
            sn: sn,
            status: nextStatus,
            XOOPS_TOKEN_REQUEST: '<{$csrf_token}>'
        }, function(res){
            if (res.success) {
                // 更新成功
            } else {
                alert(res.message || '更新審核狀態失敗');
                // 恢復原值
                location.reload();
            }
        }, 'json').fail(function() {
            alert('系統錯誤，無法變更狀態。');
            location.reload();
        });
    });
});
</script>

