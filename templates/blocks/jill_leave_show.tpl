<{if $block.leaves|default:false}>
    <ul class="list-group list-group-flush jill-leave-block">
        <{foreach from=$block.leaves item=leave}>
            <li class="list-group-item">
                <i class="fa fa-user" aria-hidden="true"></i> <{$leave.leavers}>
                <!-- 亮色徽章搭配深色文字，符合 WCAG AA 4.5:1 對比度 -->
                <span class="badge bg-info text-dark"><{$leave.cate_title}></span>
                <div class="small text-muted">
                    <i class="fa fa-calendar" aria-hidden="true"></i> <{$leave.start_date}> ~ <{$leave.end_date}>
                </div>
            </li>
        <{/foreach}>
    </ul>
<{else}>
    <div class="text-center text-muted"><{$smarty.const._MB_JILLLEAVE_SHOW_EMPTY}></div>
<{/if}>
