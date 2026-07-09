<div class="container py-1">

<!--套用formValidator驗證機制-->
<form action="<{$smarty.server.PHP_SELF|escape}>" method="post" id="myForm" enctype="multipart/form-data">
    <div class="row">

        <!--假別名稱-->
        <div class="col-md-12">
            <div class="form-floating mb-3">
                <input type="text" name="cate_title" id="cate_title" class="form-control validate[required]" value="<{$cate_title}>" placeholder="<{$smarty.const._MD_JILLLEAVE_CATE_CATE_TITLE}>">
                <label for="cate_title"><{$smarty.const._MD_JILLLEAVE_CATE_CATE_TITLE}></label>
            </div>
        </div>

        <!--假別排序-->
        <div class="col-md-6">
            <div class="form-floating mb-3">
                <input type="text" name="cate_sort" id="cate_sort" class="form-control " value="<{$cate_sort}>" placeholder="<{$smarty.const._MD_JILLLEAVE_CATE_CATE_SORT}>">
                <label for="cate_sort"><{$smarty.const._MD_JILLLEAVE_CATE_CATE_SORT}></label>
            </div>
        </div>

    <!--啟用狀態-->
    <div class="row">
        <div class="col-md-2">
            <{$smarty.const._MD_JILLLEAVE_CATE_ENABLE}>
        </div>
        <div class="col-md-10">
            <div class="form-check form-check-inline pt-2">
                <input type="radio" name="enable" id="enable_1" class="form-check-input" value="1" class="validate[required]" <{if $enable == "1" || $enable == "1=啟用" || $enable|default:'' === ''}>checked="checked"<{/if}>>
                <label class="form-check-label" for="enable_1">啟用</label>
            </div>
            <div class="form-check form-check-inline pt-2">
                <input type="radio" name="enable" id="enable_0" class="form-check-input" value="0" class="validate[required]" <{if $enable == "0" || $enable == "0=停用"}>checked="checked"<{/if}>>
                <label class="form-check-label" for="enable_0">停用</label>
            </div>
        </div>
    </div>
    </div>

    <div class="bar text-center">
        
        <!--假別編號-->
        <input type='hidden' name="cate_sn" value="<{$cate_sn}>">
        <{$token_form|default:''}>
        <input type="hidden" name="op" value="<{$next_op|default:''}>">
        <input type="hidden" name="cate_sn" value="<{$cate_sn}>">
    
        <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk" aria-hidden="true"></i> <{$smarty.const._TAD_SAVE}></button>
    </div>
</form>
</div>
