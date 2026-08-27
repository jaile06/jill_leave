<{$toolbar|default:''}>
<{if $now_op|default:false}>
    <{include file="$xoops_rootpath/modules/$xoops_dirname/templates/op_`$now_op`.tpl"}>
<{/if}>
<{* tooltip 初始化在 js/jill_leave.js，由 footer.php 統一掛載 *}>
