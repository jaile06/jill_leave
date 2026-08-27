/**
 * 請假模組 (jill_leave) 全站共用行為
 * 由 footer.php 統一掛載，所有走 footer.php 的頁面都會載入
 */
$(function () {
    // Bootstrap tooltip（同時相容 BS4 的 data-toggle 與 BS5 的 data-bs-toggle）
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-bs-toggle="tooltip"]').tooltip();
});
