<div class="container py-1">
<{if $all_jill_leave_cate|default:false}>
    <{if $smarty.session.jill_leave_adm|default:false}>
        <script type="text/javascript">
            $(document).ready(function(){
                $("#jill_leave_cate_sort").sortable({ opacity: 0.6, cursor: "move", update: function() {
                    var order = $(this).sortable("serialize");
                    $.post("<{$xoops_url}>/modules/jill_leave/cate.php", order + "&op=update_sort", function(msg){
                        $("#jill_leave_cate_save_msg").html(msg);
                    });
                }
                });
            });
        </script>
    <{/if}>

    <div id="jill_leave_cate_save_msg"></div>

    <table class="table table-sm table-striped table-hover">
        <thead>
            <tr>
            <!--假別名稱-->
            <th class="nowrap c"><{$smarty.const._MD_JILLLEAVE_CATE_CATE_TITLE}></th>
            <!--啟用狀態-->
            <th class="nowrap c"><{$smarty.const._MD_JILLLEAVE_CATE_ENABLE}></th>
                <{if $smarty.session.jill_leave_adm|default:false}>
                    <th><{$smarty.const._TAD_FUNCTION}></th>
                <{/if}>
            </tr>
        </thead>
        <tbody id="jill_leave_cate_sort">
        <{foreach from=$all_jill_leave_cate key=k item=data name=all_jill_leave_cate}>
            <tr id="tr-<{$data.cate_sn}>">
            <!--假別名稱-->
            <td><a href="<{$xoops_url}>/modules/jill_leave/cate.php?cate_sn=<{$data.cate_sn}>"><{$data.cate_title}></a></td>

            <!--啟用狀態-->
            <td class="text-center">
                <{if $smarty.session.jill_leave_adm|default:false}>
                    <a href="<{$xoops_url}>/modules/jill_leave/cate.php?op=update_enable&cate_sn=<{$data.cate_sn}>" title="點擊切換啟用狀態">
                        <{if $data.enable == 1}>
                            <img src="<{$xoops_url}>/modules/jill_leave/images/icons/on.png" alt="<{$smarty.const._MD_JILLLEAVE_CATE_ENABLE}>">
                        <{else}>
                            <img src="<{$xoops_url}>/modules/jill_leave/images/icons/off.png" alt="<{$smarty.const._MD_JILLLEAVE_CATE_ENABLE}>">
                        <{/if}>
                    </a>
                <{else}>
                    <{if $data.enable == 1}>
                        <img src="<{$xoops_url}>/modules/jill_leave/images/icons/on.png" alt="<{$smarty.const._MD_JILLLEAVE_CATE_ENABLE}>">
                    <{else}>
                        <img src="<{$xoops_url}>/modules/jill_leave/images/icons/off.png" alt="<{$smarty.const._MD_JILLLEAVE_CATE_ENABLE}>">
                    <{/if}>
                <{/if}>
            </td>

                <{if $smarty.session.jill_leave_adm|default:false}>
                    <td>
                        <a href="javascript:jill_leave_cate_destroy_func(<{$data.cate_sn}>);" class="btn btn-sm btn-danger" title="<{$smarty.const._TAD_DEL}>"><i class="fa fa-trash"></i></a>
                        <a href="<{$xoops_url}>/modules/jill_leave/cate.php?op=jill_leave_cate_edit&cate_sn=<{$data.cate_sn}>" class="btn btn-sm btn-warning" title="<{$smarty.const._TAD_EDIT}>"><i class="fa fa-pencil"></i></a>
                        <i class="fa fa-sort" aria-hidden="true"></i>
                    </td>
                <{/if}>
            </tr>
        <{/foreach}>
        </tbody>
    </table>

    <{if $smarty.session.jill_leave_adm|default:false}>
        <div class="text-end my-3">
            <a href="<{$xoops_url}>/modules/jill_leave/cate.php?op=jill_leave_cate_create" class="btn btn-info">
                <i class="fa fa-plus"></i> <{$smarty.const._TAD_ADD}>
            </a>
        </div>
    <{/if}>

    <div class="bar"><{$bar|default:''}></div>
<{else}>
    <div class="alert alert-warning text-center">
        <{if $smarty.session.jill_leave_adm|default:false}>
            <a href="<{$xoops_url}>/modules/jill_leave/cate.php?op=jill_leave_cate_create" class="btn btn-info">
                <i class="fa fa-plus"></i> <{$smarty.const._TAD_ADD}>
            </a>
        <{else}>
            <h3><{$smarty.const._TAD_EMPTY}></h3>
        <{/if}>
    </div>
<{/if}>
</div>

