<div class="container py-1">

<!--套用formValidator驗證機制-->
<form action="<{$smarty.server.PHP_SELF}>" method="post" id="myForm" enctype="multipart/form-data">
    <div class="row">

        <!--代課日期 date-->
        <div class="col-md-12">
            <div class="form-floating mb-3">
                <input type="text" name="substitute_date" id="substitute_date" class="form-control validate[required]" value="<{$substitute_date}>" onClick="WdatePicker({dateFmt:'yyyy-MM-dd', startDate:'%y-%M-%d'})" placeholder="<{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_SUBSTITUTE_DATE}>">
                <label for="substitute_date"><{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_SUBSTITUTE_DATE}></label>
            </div>
        </div>

    <!--支付方式 (self:自費 school:公費)-->
    <div class="row">
        <div class="col-md-2">
            <{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_PAY}>
        </div>
        <div class="col-md-10">
            <div class="form-check form-check-inline pt-2">
                <input type="radio" name="pay" id="pay_self=自費" class="form-check-input" value="self=自費" class="validate[required]" <{if $pay == "self=自費"}>checked="checked"<{/if}>>
                <label class="form-check-label" for="pay_self=自費">self=自費</label>
            </div>
            <div class="form-check form-check-inline pt-2">
                <input type="radio" name="pay" id="pay_school=公費" class="form-check-input" value="school=公費" class="validate[required]" <{if $pay == "school=公費"}>checked="checked"<{/if}>>
                <label class="form-check-label" for="pay_school=公費">school=公費</label>
            </div>
        </div>
    </div>

    <!--代課類型 (daily:日薪 hour:鐘點)-->
    <div class="row">
        <div class="col-md-2">
            <{$smarty.const._MD_JILLLEAVE_SUBSTITUTE_TYPE}>
        </div>
        <div class="col-md-10">
            <div class="form-check form-check-inline pt-2">
                <input type="radio" name="type" id="type_daily=日薪" class="form-check-input" value="daily=日薪" class="validate[required]" <{if $type == "daily=日薪"}>checked="checked"<{/if}>>
                <label class="form-check-label" for="type_daily=日薪">daily=日薪</label>
            </div>
            <div class="form-check form-check-inline pt-2">
                <input type="radio" name="type" id="type_hour=鐘點" class="form-check-input" value="hour=鐘點" class="validate[required]" <{if $type == "hour=鐘點"}>checked="checked"<{/if}>>
                <label class="form-check-label" for="type_hour=鐘點">hour=鐘點</label>
            </div>
        </div>
    </div>
    </div>

    <div class="bar text-center">
        
        <!--關聯請假編號-->
        <input type='hidden' name="sn" value="<{$sn}>">
        <{$token_form|default:''}>
        <input type="hidden" name="op" value="<{$next_op|default:''}>">
        <input type="hidden" name="substitute_sn" value="<{$substitute_sn}>">
    
        <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk" aria-hidden="true"></i> <{$smarty.const._TAD_SAVE}></button>
    </div>
</form>
</div>
