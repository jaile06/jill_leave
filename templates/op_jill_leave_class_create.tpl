<div class="container py-1">

<!--套用formValidator驗證機制-->
<form action="<{$smarty.server.PHP_SELF}>" method="post" id="myForm" enctype="multipart/form-data">
    <div class="row">

        <!--節次 (例如: 1, 2, 早自習)-->
        <div class="col-md-12">
            <div class="form-floating mb-3">
                <input type="text" name="class_period" id="class_period" class="form-control validate[required, minSize[1], maxSize[20]]" value="<{$class_period}>" placeholder="<{$smarty.const._MD_JILLLEAVE_CLASS_CLASS_PERIOD}>">
                <label for="class_period"><{$smarty.const._MD_JILLLEAVE_CLASS_CLASS_PERIOD}></label>
            </div>
        </div>

        <!--科目-->
        <div class="col-md-12">
            <div class="form-floating mb-3">
                <input type="text" name="subject" id="subject" class="form-control validate[required]" value="<{$subject}>" placeholder="<{$smarty.const._MD_JILLLEAVE_CLASS_SUBJECT}>">
                <label for="subject"><{$smarty.const._MD_JILLLEAVE_CLASS_SUBJECT}></label>
            </div>
        </div>

        <!--代課老師-->
        <div class="col-md-12">
            <div class="form-floating mb-3">
                <input type="text" name="substitute_teacher" id="substitute_teacher" class="form-control validate[required, minSize[1], maxSize[50]]" value="<{$substitute_teacher}>" placeholder="<{$smarty.const._MD_JILLLEAVE_CLASS_SUBSTITUTE_TEACHER}>">
                <label for="substitute_teacher"><{$smarty.const._MD_JILLLEAVE_CLASS_SUBSTITUTE_TEACHER}></label>
            </div>
        </div>
    </div>

    <div class="bar text-center">
        
        <!--關聯請假編號-->
        <input type='hidden' name="sn" value="<{$sn}>">
        <{$token_form|default:''}>
        <input type="hidden" name="op" value="<{$next_op|default:''}>">
        <input type="hidden" name="class_sn" value="<{$class_sn}>">
    
        <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk" aria-hidden="true"></i> <{$smarty.const._TAD_SAVE}></button>
    </div>
</form>

</div>
