/**
 * 請假模組 (jill_leave) 前端列表與即時審核狀態變更互動邏輯
 */
$(function () {
    var ajaxUrl   = window.jillLeaveConfig.ajaxUrl;
    var csrfToken = window.jillLeaveConfig.csrfToken;

    // 審核狀態 Badge 點擊切換事件 (使用事件委託防止表格重載/分頁後失效)
    $(document).on('click', '.update-status', function (e) {
        e.preventDefault();
        var $btn = $(this);
        if ($btn.next('.status-select').length) return; // 防止重複觸發

        var sn         = $btn.data('sn');
        var status     = $btn.data('status');
        var hasChanged = false;

        // 建立狀態選擇下拉選單
        var $select = $('<select class="form-select form-select-sm status-select" style="width:auto;display:inline-block;padding:2px 8px;font-size:.85rem;"></select>');

        // 動態注入後端語系標籤
        $.each(window.jillLeaveConfig.statusLabels, function (val, label) {
            $select.append($('<option>').val(val).text(label));
        });
        $select.val(status);

        $btn.hide().after($select);
        $select.focus();

        // 變更狀態發送 AJAX 請求
        $select.on('change', function () {
            hasChanged = true;
            var nextStatus = parseInt($(this).val(), 10);
            $.post(ajaxUrl, {
                op: 'update_status',
                sn: sn,
                status: nextStatus,
                XOOPS_TOKEN_REQUEST: csrfToken
            }, function (res) {
                if (res.success) {
                    updateBadges(sn, nextStatus, res.status_text);
                    $select.remove();
                } else {
                    alert(res.message || '更新審核狀態失敗');
                    $btn.show();
                    $select.remove();
                }
            }, 'json').fail(function () {
                alert(window.jillLeaveConfig.errorMsg || '系統錯誤，無法變更狀態。');
                $btn.show();
                $select.remove();
            });
        });

        // 失去焦點自動復原
        $select.on('blur', function () {
            setTimeout(function () {
                if (!hasChanged) {
                    $btn.show();
                    $select.remove();
                }
            }, 250);
        });
    });

    /**
     * 同步更新頁面中對應單筆紀錄的所有 Badge 標籤
     */
    function updateBadges(sn, status, text) {
        var cls = { 0: 'bg-secondary', 1: 'bg-success', 2: 'bg-danger' };
        $('.update-status[data-sn="' + sn + '"]').each(function () {
            $(this).data('status', status)
                   .text(text)
                   .removeClass('bg-success bg-danger bg-secondary')
                   .addClass(cls[status] || 'bg-secondary')
                   .show();
        });
    }
});
