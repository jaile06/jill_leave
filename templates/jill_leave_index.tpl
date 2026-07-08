<{$toolbar|default:''}>
<{if $show_login_alert|default:false}>
    <div class="alert alert-danger text-center my-3">
        <h4><i class="fa fa-exclamation-triangle"></i> 請先登入才能請假</h4>
    </div>
<{/if}>
<{if $show_student_alert|default:false}>
    <div class="alert alert-warning text-center my-3">
        <h4><i class="fa fa-exclamation-triangle"></i> 學生帳號無權限使用此系統。</h4>
    </div>
<{/if}>
<{if $now_op|default:false}>
    <{include file="$xoops_rootpath/modules/$xoops_dirname/templates/op_`$now_op`.tpl"}>
<{/if}>

<script language="JavaScript" type="text/javascript">
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>