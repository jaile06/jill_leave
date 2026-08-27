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
                <input type="text" name="adm_email" id="adm_email" class="form-control" value="<{$adm_email|escape:'htmlall':'UTF-8'}>" placeholder="<{$smarty.const._MI_JILLLEAVE_ADM_EMAIL}>">
                <label for="adm_email"><{$smarty.const._MI_JILLLEAVE_ADM_EMAIL}></label>
            </div>
            <div class="form-text mb-3"><{$smarty.const._MI_JILLLEAVE_ADM_EMAIL_DESC}></div>

            <hr>

            <!--年級設定（checkbox 多選）-->
            <div class="mb-3">
                <label class="form-label fw-bold"><{$smarty.const._MI_JILLLEAVE_GRADE}></label>
                <div class="form-text mb-2"><{$smarty.const._MI_JILLLEAVE_GRADE_DESC}></div>
                <div class="d-flex flex-wrap gap-3" id="grade_checkboxes"></div>
                <input type="hidden" name="grade" id="grade_hidden" value="<{$grade|escape:'htmlall':'UTF-8'}>">
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
                       value="<{$class_period|escape:'htmlall':'UTF-8'}>" placeholder="<{$smarty.const._MI_JILLLEAVE_CLASS_PERIOD}>" required>
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

<script src="<{$xoops_url}>/modules/jill_leave/js/config_form.js?v=<{$asset_ver|default:'1'}>"></script>
