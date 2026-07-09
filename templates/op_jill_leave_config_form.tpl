<!--模組設定表單（管理人員 + 偏好設定）-->
<div class="container py-1">
<form action="<{$smarty.server.PHP_SELF|escape}>" method="post" id="myForm">
    <div class="card">
        <div class="card-header">
            <i class="fa fa-cog"></i> <{$smarty.const._MD_JILLLEAVE_CONFIG}>
        </div>
        <div class="card-body">

            <!--管理人員 Email-->
            <div class="form-floating mb-3">
                <input type="text" name="adm_email" id="adm_email" class="form-control" value="<{$adm_email}>" placeholder="<{$smarty.const._MI_JILLLEAVE_ADM_EMAIL}>">
                <label for="adm_email"><{$smarty.const._MI_JILLLEAVE_ADM_EMAIL}></label>
            </div>
            <div class="form-text mb-3"><{$smarty.const._MI_JILLLEAVE_ADM_EMAIL_DESC}></div>

            <hr>

            <!--年級設定（checkbox 多選）-->
            <div class="mb-3">
                <label class="form-label fw-bold"><{$smarty.const._MI_JILLLEAVE_GRADE}></label>
                <div class="form-text mb-2"><{$smarty.const._MI_JILLLEAVE_GRADE_DESC}></div>
                <div class="d-flex flex-wrap gap-3" id="grade_checkboxes"></div>
                <input type="hidden" name="grade" id="grade_hidden" value="<{$grade}>">
            </div>

            <hr>

            <!--最多班級數-->
            <div class="form-floating mb-3">
                <input type="number" name="class_room" id="class_room" class="form-control" min="1" max="99"
                       value="<{$class_room}>" placeholder="<{$smarty.const._MI_JILLLEAVE_CLASS_ROOM}>" required>
                <label for="class_room"><{$smarty.const._MI_JILLLEAVE_CLASS_ROOM}></label>
            </div>
            <div class="form-text mb-3"><{$smarty.const._MI_JILLLEAVE_CLASS_ROOM_DESC}></div>

            <hr>

            <!--節次設定-->
            <div class="form-floating mb-3">
                <input type="text" name="class_period" id="class_period" class="form-control"
                       value="<{$class_period}>" placeholder="<{$smarty.const._MI_JILLLEAVE_CLASS_PERIOD}>" required>
                <label for="class_period"><{$smarty.const._MI_JILLLEAVE_CLASS_PERIOD}></label>
            </div>
            <div class="form-text mb-3"><{$smarty.const._MI_JILLLEAVE_CLASS_PERIOD_DESC}></div>

            <!--送出-->
            <div class="bar text-center">
                <{$token_form|default:''}>
                <input type="hidden" name="op" value="save_config">
                <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk" aria-hidden="true"></i> <{$smarty.const._TAD_SAVE}></button>
            </div>
        </div>
    </div>
</form>
</div>

<script>
(function(){
    // 動態產生 1~12 年級 checkbox，依目前設定值打勾
    var current = '<{$grade}>'.split(',').map(function(s){ return s.trim(); });
    var container = document.getElementById('grade_checkboxes');
    for(var g = 1; g <= 12; g++){
        var checked = current.indexOf(String(g)) !== -1 ? ' checked' : '';
        container.innerHTML += '<div class="form-check">' +
            '<input class="form-check-input grade-chk" type="checkbox" value="' + g + '" id="grade_' + g + '"' + checked + '>' +
            '<label class="form-check-label" for="grade_' + g + '">' + g + '年級</label>' +
            '</div>';
    }

    // 提交前處理與清洗格式
    document.getElementById('myForm').addEventListener('submit', function(e){
        // 1. 處理 adm_email (用分號隔開，去除句尾的分號、逗號與前後多餘空白)
        var emailInput = document.getElementById('adm_email');
        if (emailInput) {
            var emailVal = emailInput.value.trim();
            emailVal = emailVal.replace(/[;,]+$/, '').trim();
            var emailArr = emailVal.split(';').map(function(s){ return s.trim(); }).filter(function(s){ return s !== ''; });
            emailInput.value = emailArr.join(';');
        }

        // 2. 處理 class_period (用逗號隔開，去除句尾的分號、逗號與前後多餘空白)
        var periodInput = document.getElementById('class_period');
        if (periodInput) {
            var periodVal = periodInput.value.trim();
            periodVal = periodVal.replace(/[;,]+$/, '').trim();
            var periodArr = periodVal.split(',').map(function(s){ return s.trim(); }).filter(function(s){ return s !== ''; });
            periodInput.value = periodArr.join(',');
        }

        // 3. 處理 grade 勾選值
        var checked = [];
        document.querySelectorAll('.grade-chk:checked').forEach(function(el){
            checked.push(el.value);
        });
        document.getElementById('grade_hidden').value = checked.join(',');
    });
})();
</script>
