/**
 * 請假模組 (jill_leave) 模組設定表單互動邏輯
 * 目前設定值直接讀表單裡的 hidden input，不需要另外從樣板傳資料進來
 */
(function () {
    var gradeHidden = document.getElementById('grade_hidden');
    var container = document.getElementById('grade_checkboxes');
    var form = document.getElementById('myForm');
    if (!gradeHidden || !container || !form) {
        return;
    }

    // 動態產生 1~12 年級 checkbox，依目前設定值打勾
    var current = gradeHidden.value.split(',').map(function (s) { return s.trim(); });
    for (var g = 1; g <= 12; g++) {
        var checked = current.indexOf(String(g)) !== -1 ? ' checked' : '';
        container.innerHTML += '<div class="form-check">' +
            '<input class="form-check-input grade-chk" type="checkbox" value="' + g + '" id="grade_' + g + '"' + checked + '>' +
            '<label class="form-check-label" for="grade_' + g + '">' + g + '年級</label>' +
            '</div>';
    }

    // 清洗以 $sep 分隔的清單欄位：去句尾分隔符、逐項 trim、濾空、重組（對應 PHP 端的 clean_list()）
    function cleanListInput(id, sep) {
        var input = document.getElementById(id);
        if (!input) {
            return;
        }
        var parts = input.value.trim().replace(/[;,]+$/, '').split(sep);
        input.value = parts.map(function (s) { return s.trim(); })
                           .filter(function (s) { return s !== ''; })
                           .join(sep);
    }

    // 提交前處理與清洗格式
    form.addEventListener('submit', function () {
        cleanListInput('adm_email', ';');
        cleanListInput('class_period', ',');

        var checked = [];
        document.querySelectorAll('.grade-chk:checked').forEach(function (el) {
            checked.push(el.value);
        });
        gradeHidden.value = checked.join(',');
    });
})();
