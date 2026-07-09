<!--管理人員設定表單-->
<div class="container py-1">
<form action="<{$smarty.server.PHP_SELF|escape}>" method="post" id="myForm">
    <div class="card">
        <div class="card-header">
            <i class="fa fa-user-gear"></i> <{$smarty.const._MD_JILLLEAVE_ADM}>
        </div>
        <div class="card-body">
            <div class="form-floating mb-3">
                <input type="text" name="adm_email" id="adm_email" class="form-control" value="<{$adm_email}>" placeholder="<{$smarty.const._MD_JILLLEAVE_ADM_EMAIL}>">
                <label for="adm_email"><{$smarty.const._MD_JILLLEAVE_ADM_EMAIL}></label>
            </div>
            <div class="form-text mb-3"><{$smarty.const._MD_JILLLEAVE_ADM_EMAIL_DESC}></div>

            <div class="bar text-center">
                <{$token_form|default:''}>
                <input type="hidden" name="op" value="save_admin_email">
                <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk" aria-hidden="true"></i> <{$smarty.const._TAD_SAVE}></button>
            </div>
        </div>
    </div>
</form>
</div>

