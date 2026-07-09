<!--請假表單：套用 formValidator 驗證機制；代課卡片由 js/leave_form.js 依起訖日期動態生成-->
<div class="container py-1">
<form action="<{$smarty.server.PHP_SELF|escape}>" method="post" id="myForm">
    <!--請假者姓名（自動帶入登入者姓名，不可修改）-->
    <div class="form-floating mb-3">
        <input type="text" id="leavers" class="form-control" value="<{$leavers}>" placeholder="<{$smarty.const._MD_JILLLEAVE_LEAVERS}>" disabled>
        <label for="leavers"><{$smarty.const._MD_JILLLEAVE_LEAVERS}></label>
    </div>

    <!--是否導師 + 導師班級（同一行）-->
    <div class="row mb-3">
        <div class="col-md-1 d-flex align-items-center">
            <{$smarty.const._MD_JILLLEAVE_IS_ADVISOR}>
        </div>
        <div class="col-md-11">
            <div class="d-flex align-items-center flex-wrap">
                <!--導師 radio-->
                <div class="form-check form-check-inline">
                    <input type="radio" name="is_advisor" id="is_advisor_1" class="form-check-input validate[required]" value="1" <{if $is_advisor == 1}>checked="checked"<{/if}>>
                    <label class="form-check-label" for="is_advisor_1"><{$smarty.const._MD_JILLLEAVE_ADVISOR}></label>
                </div>
                <!--年級下拉-->
                <span class="mx-2">
                <select name="grade" id="grade" class="form-select form-select-sm d-inline-block w-auto validate[condRequired[is_advisor_1]]">
                    <option value="">請選擇年級</option>
                    <{foreach from=$grade_options item=g}>
                        <option value="<{$g}>" <{if $grade == $g}>selected<{/if}>><{$g}></option>
                    <{/foreach}>
                </select>
                </span>
                <span class="mx-1">年</span>
                <!--班級下拉-->
                <span class="mx-2">
                <select name="classroom" id="classroom" class="form-select form-select-sm d-inline-block w-auto validate[condRequired[is_advisor_1]]">
                    <option value="">請選擇班級</option>
                    <{section name=j start=1 loop=$class_room_max+1}>
                        <option value="<{$smarty.section.j.index}>" <{if $classroom == $smarty.section.j.index}>selected<{/if}>><{$smarty.section.j.index}></option>
                    <{/section}>
                </select>
                </span>
                <span class="mx-1">班</span>
                <!--科任 radio-->
                <div class="form-check form-check-inline ms-3">
                    <input type="radio" name="is_advisor" id="is_advisor_0" class="form-check-input validate[required]" value="0" <{if $is_advisor == 0}>checked="checked"<{/if}>>
                    <label class="form-check-label" for="is_advisor_0"><{$smarty.const._MD_JILLLEAVE_SUBJECT_TEACHER}></label>
                </div>
                <!--合併年級班級寫入 hidden-->
                <input type="hidden" name="grade_class" id="grade_class" value="<{$grade_class}>">
            </div>
        </div>
    </div>

    <!--假別-->
    <div class="form-floating mb-3">
        <select name="cate_sn" id="cate_sn" class="form-select validate[required]" size="1">
            <{foreach from=$cate_sn_options item=opt}>
                <option value="<{$opt.cate_sn}>" <{if $cate_sn==$opt.cate_sn}>selected<{/if}>><{$opt.cate_title}></option>
            <{/foreach}>
        </select>
        <label for="cate_sn"><{$smarty.const._MD_JILLLEAVE_CATE_CATE_TITLE}></label>
    </div>

    <!--起始日期 / 結束日期（選定後自動生成代課卡片）-->
    <div class="row">
        <div class="col">
            <{if $next_op == 'jill_leave_update'}>
                <div class="form-floating mb-3">
                    <input type="text" id="start_date_display" class="form-control" value="<{$start_date}>" placeholder="<{$smarty.const._MD_JILLLEAVE_START_DATE}>" disabled>
                    <input type="hidden" name="start_date" value="<{$start_date}>">
                    <label for="start_date_display"><{$smarty.const._MD_JILLLEAVE_START_DATE}></label>
                </div>
            <{else}>
                <div class="form-floating mb-3">
                    <input type="text" name="start_date" id="start_date" class="form-control validate[required]" value="<{$start_date}>" onClick="WdatePicker({dateFmt:'yyyy-MM-dd', startDate:'%y-%M-%d', onpicked:checkDates})" placeholder="<{$smarty.const._MD_JILLLEAVE_START_DATE}>">
                    <label for="start_date">請選擇<{$smarty.const._MD_JILLLEAVE_START_DATE}></label>
                </div>
            <{/if}>
        </div>
        <div class="col">
            <{if $next_op == 'jill_leave_update'}>
                <div class="form-floating mb-3">
                    <input type="text" id="end_date_display" class="form-control" value="<{$end_date}>" placeholder="<{$smarty.const._MD_JILLLEAVE_END_DATE}>" disabled>
                    <input type="hidden" name="end_date" value="<{$end_date}>">
                    <label for="end_date_display"><{$smarty.const._MD_JILLLEAVE_END_DATE}></label>
                </div>
            <{else}>
                <div class="form-floating mb-3">
                    <input type="text" name="end_date" id="end_date" class="form-control validate[required]" value="<{$end_date}>" onClick="WdatePicker({dateFmt:'yyyy-MM-dd', startDate:'%y-%M-%d', onpicked:checkDates})" placeholder="<{$smarty.const._MD_JILLLEAVE_END_DATE}>">
                    <label for="end_date">請選擇<{$smarty.const._MD_JILLLEAVE_END_DATE}></label>
                </div>
            <{/if}>
        </div>
    </div>

    <!--審核狀態（僅管理者可設定）-->
    <{if $smarty.session.jill_leave_adm|default:false}>
        <div class="mb-3">
            <div><{$smarty.const._MD_JILLLEAVE_STATUS}></div>
            <div class="form-check form-check-inline pt-2">
                <input type="radio" name="status" id="status_0" class="form-check-input" value="0" <{if $status == 0}>checked="checked"<{/if}>>
                <label class="form-check-label" for="status_0"><{$smarty.const._MD_JILLLEAVE_STATUS_0}></label>
            </div>
            <div class="form-check form-check-inline pt-2">
                <input type="radio" name="status" id="status_1" class="form-check-input" value="1" <{if $status == 1}>checked="checked"<{/if}>>
                <label class="form-check-label" for="status_1"><{$smarty.const._MD_JILLLEAVE_STATUS_1}></label>
            </div>
            <div class="form-check form-check-inline pt-2">
                <input type="radio" name="status" id="status_2" class="form-check-input" value="2" <{if $status == 2}>checked="checked"<{/if}>>
                <label class="form-check-label" for="status_2"><{$smarty.const._MD_JILLLEAVE_STATUS_2}></label>
            </div>
        </div>
    <{/if}>

    <!--代課資訊：每個代課日期一張卡片（跳過週日），由 js/leave_form.js 生成-->
    <div class="mb-3">
        <h5><i class="fa fa-users"></i> <{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_INFO}></h5>
        <div id="substitute_container"></div>
        <!--送出時由 JS 將卡片內容序列化為 substitute_date[] 等平行陣列隱藏欄位-->
        <div id="substitute_hidden"></div>
    </div>

    <div class="bar text-center">
        <!--請假者編號-->
        <input type="hidden" name="uid" value="<{$uid}>">
        <{$token_form|default:''}>
        <input type="hidden" name="op" value="<{$next_op|default:''}>">
        <input type="hidden" name="sn" value="<{$sn}>">

        <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk" aria-hidden="true"></i> <{$smarty.const._TAD_SAVE}></button>
    </div>
</form>
</div>

<!--代課日期卡片範本-->
<template id="substitute_card_tpl">
    <div class="card mb-3 substitute-card">
        <div class="card-header d-flex flex-wrap align-items-center gap-3">
            <strong class="me-auto"><i class="fa fa-calendar"></i> <span class="substitute-date-text"></span></strong>
            <!--支付方式-->
            <div class="d-inline-flex align-items-center">
                <span class="text-muted small me-2"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_PAY}></span>
                <div class="form-check form-check-inline">
                    <label class="form-check-label"><input type="radio" class="form-check-input pay-radio" value="self" checked> <{$smarty.const._MD_JILLLEAVE_PAY_SELF_FULL}></label>
                </div>
                <div class="form-check form-check-inline">
                    <label class="form-check-label"><input type="radio" class="form-check-input pay-radio" value="school"> <{$smarty.const._MD_JILLLEAVE_PAY_SCHOOL_FULL}></label>
                </div>
            </div>
            <!--代課類型-->
            <div class="d-inline-flex align-items-center">
                <span class="text-muted small me-2"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_TYPE}></span>
                <div class="form-check form-check-inline">
                    <label class="form-check-label"><input type="radio" class="form-check-input type-radio" value="daily" checked> <{$smarty.const._MD_JILLLEAVE_TYPE_DAILY}></label>
                </div>
                <div class="form-check form-check-inline">
                    <label class="form-check-label"><input type="radio" class="form-check-input type-radio" value="hour"> <{$smarty.const._MD_JILLLEAVE_TYPE_HOUR}></label>
                </div>
            </div>
            <!--同第一天按鈕（非第一張卡片才顯示）-->
            <button type="button" class="btn btn-sm btn-outline-info copy-first-day-btn d-none" title="<{$smarty.const._MD_JILLLEAVE_COPY_FIRST_DAY_TIP}>">
                <i class="fa fa-copy"></i> <{$smarty.const._MD_JILLLEAVE_COPY_FIRST_DAY}>
            </button>
        </div>
        <div class="card-body py-2">
            <!--日薪：整天一位代課老師-->
            <div class="daily-panel">
                <div class="row g-2 align-items-center">
                    <div class="col-auto"><span class="badge bg-secondary"><{$smarty.const._MD_JILLLEAVE_ALLDAY}></span></div>
                    <div class="col-auto">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label"><input type="radio" class="form-check-input teacher-opt" value="assign" checked> <{$smarty.const._MD_JILLLEAVE_TEACHER_ASSIGN}></label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label"><input type="radio" class="form-check-input teacher-opt" value="input"> <{$smarty.const._MD_JILLLEAVE_TEACHER_INPUT}></label>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <input type="text" class="form-control form-control-sm teacher-input" aria-label="<{$smarty.const._MD_JILLLEAVE_CLASS_SUBSTITUTE_TEACHER}>" placeholder="<{$smarty.const._MD_JILLLEAVE_CLASS_SUBSTITUTE_TEACHER}>" disabled>
                    </div>
                </div>
            </div>
            <!--鐘點：勾選節次逐節填寫（節次列由 JS 依 period_row_tpl 範本生成）-->
            <div class="hour-panel d-none"></div>
        </div>
    </div>
</template>

<!--鐘點節次列範本-->
<template id="period_row_tpl">
    <div class="row g-2 align-items-center mb-1 period-row">
        <!--勾選節次（手機4格，平板以上1格）-->
        <div class="col-4 col-sm-3 col-md-1">
            <div class="form-check">
                <label class="form-check-label"><input type="checkbox" class="form-check-input period-check"> <span class="period-text"></span></label>
            </div>
        </div>
        <!--科任逐節班級（年級下拉＋班級文字框），級任隱藏；手機佔8格-->
        <div class="col-8 col-sm-9 col-md-3 grade-class-wrap">
            <div class="input-group input-group-sm">
                <select class="form-select gc-grade" aria-label="<{$smarty.const._MD_JILLLEAVE_GRADE_SELECT}>" disabled>
                    <option value=""><{$smarty.const._MD_JILLLEAVE_GRADE_SELECT}></option>
                    <{foreach from=$grade_options item=g}>
                        <option value="<{$g}>"><{$g}></option>
                    <{/foreach}>
                </select>
                <span class="input-group-text">年</span>
                <select class="form-select gc-class" aria-label="<{$smarty.const._MD_JILLLEAVE_CLASS_INPUT}>" disabled>
                    <option value=""><{$smarty.const._MD_JILLLEAVE_CLASS_INPUT}></option>
                    <{section name=j start=1 loop=$class_room_max+1}>
                        <option value="<{$smarty.section.j.index}>"><{$smarty.section.j.index}></option>
                    <{/section}>
                </select>
                <span class="input-group-text">班</span>
            </div>
        </div>
        <!--科目（手機整行）-->
        <div class="col-12 col-md-2">
            <input type="text" class="form-control form-control-sm subject-input" aria-label="<{$smarty.const._MD_JILLLEAVE_CLASS_SUBJECT}>" placeholder="<{$smarty.const._MD_JILLLEAVE_CLASS_SUBJECT}>" disabled>
        </div>
        <!--代課老師選項（手機整行）-->
        <div class="col-12 col-md-auto">
            <div class="form-check form-check-inline">
                <label class="form-check-label"><input type="radio" class="form-check-input teacher-opt" value="assign" checked disabled> <{$smarty.const._MD_JILLLEAVE_TEACHER_ASSIGN}></label>
            </div>
            <div class="form-check form-check-inline">
                <label class="form-check-label"><input type="radio" class="form-check-input teacher-opt" value="input" disabled> <{$smarty.const._MD_JILLLEAVE_TEACHER_INPUT}></label>
            </div>
        </div>
        <!--代課老師姓名（手機整行）-->
        <div class="col-12 col-md">
            <input type="text" class="form-control form-control-sm teacher-input" aria-label="<{$smarty.const._MD_JILLLEAVE_CLASS_SUBSTITUTE_TEACHER}>" placeholder="<{$smarty.const._MD_JILLLEAVE_CLASS_SUBSTITUTE_TEACHER}>" disabled>
        </div>
    </div>

</template>

<script type="text/javascript">
// 語系文字與既有資料設定，供 js/leave_form.js 使用
var LEAVE_FORM = {
    weekdays: '<{$smarty.const._MD_JILLLEAVE_WEEKDAYS}>'.split(','),
    periods: <{$class_period_options|@json_encode nofilter}>,
    allday_text: '<{$smarty.const._MD_JILLLEAVE_ALLDAY}>',
    assign_text: '<{$smarty.const._MD_JILLLEAVE_TEACHER_ASSIGN}>',
    start_date: '<{$start_date}>',
    end_date: '<{$end_date}>',
    msg: {
        date_order: '<{$smarty.const._MD_JILLLEAVE_MSG_DATE_ORDER}>',
        no_period: '<{$smarty.const._MD_JILLLEAVE_MSG_NO_PERIOD}>',
        no_subject: '<{$smarty.const._MD_JILLLEAVE_MSG_NO_SUBJECT}>',
        no_teacher: '<{$smarty.const._MD_JILLLEAVE_MSG_NO_TEACHER}>',
        no_grade_class: '<{$smarty.const._MD_JILLLEAVE_MSG_NO_GRADE_CLASS}>'
    },
    copy_first_day: '<{$smarty.const._MD_JILLLEAVE_COPY_FIRST_DAY}>',
    copy_first_day_tip: '<{$smarty.const._MD_JILLLEAVE_COPY_FIRST_DAY_TIP}>',
    existing: <{$substitute_rows|@json_encode nofilter}>
};
</script>
<script type="text/javascript" src="<{$xoops_url}>/modules/jill_leave/js/leave_form.js"></script>
