<{if $block.leaves|default:false}>
    <ul class="list-group list-group-flush jill-leave-block">
        <{foreach from=$block.leaves item=leave}>
            <li class="list-group-item">
                <i class="fa fa-user"></i> <{$leave.leavers}>
                <span class="badge bg-info"><{$leave.cate_title}></span>
                <div class="small text-muted">
                    <i class="fa fa-calendar"></i> <{$leave.start_date}> ~ <{$leave.end_date}>
                </div>
            </li>
        <{/foreach}>
    </ul>
<{else}>
    <div class="text-center text-muted"><{$smarty.const._MB_JILLLEAVE_SHOW_EMPTY}></div>
<{/if}>
