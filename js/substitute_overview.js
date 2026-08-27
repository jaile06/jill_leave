/**
 * 請假模組 (jill_leave) 代課總覽頁互動邏輯
 * 需要樣板先定義 window.jillLeaveConfig（ajaxUrl / csrfToken）
 */
$(function () {
    var cfg = window.jillLeaveConfig || {};

    // 展開/折疊課務詳細行
    $(document).on('click', '.expand-toggle', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $details = $('.substitute-detail[data-sn="' + $btn.data('sn') + '"]');

        $details.slideToggle(200, function () {
            $btn.attr('aria-expanded', $details.is(':visible') ? 'true' : 'false');
        });
        $btn.find('i').toggleClass('fa-plus fa-minus');
    });

    // 審核狀態下拉選單變更
    $(document).on('change', '.update-status', function () {
        var $select = $(this);
        var nextStatus = parseInt($select.val(), 10);

        // 更新狀態 Select 的樣式 class
        $select.removeClass('status-select-0 status-select-1 status-select-2')
               .addClass('status-select-' + nextStatus);

        $.post(cfg.ajaxUrl, {
            op: 'update_status',
            sn: $select.data('sn'),
            status: nextStatus,
            XOOPS_TOKEN_REQUEST: cfg.csrfToken
        }, function (res) {
            if (!res.success) {
                swal('', res.message || cfg.errorMsg, 'error');
                location.reload(); // 恢復原值
            }
        }, 'json').fail(function () {
            swal('', cfg.errorMsg, 'error');
            location.reload();
        });
    });
});
