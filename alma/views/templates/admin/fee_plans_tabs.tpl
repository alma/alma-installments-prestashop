{assign var='tabClasses' value=''}
<ul class='nav nav-tabs alma-pnx-tabs'>
    {foreach $fee_plans as $planKey => $feePlan name=loop}
        {* Loop to explode the plans for the selector in string *}
        {if $smarty.foreach.loop.first}
            {assign var='tabClasses' value=".tab-`$planKey`"}
        {else}
            {assign var='tabClasses' value="`$tabClasses`, .tab-`$planKey`"}
        {/if}
        <li {if $feePlan.firstPlanEnable === $planKey}class='active'{/if}>
            <a href='javascript:void(0)' data-target='{$planKey|escape:'htmlall':'UTF-8'}' data-toggle='tab'>
                {if $feePlan.active}
                    <i class="material-icons mi-check small text-success">check</i>
                {else}
                    <i class="material-icons mi-close small text-danger">close</i>
                {/if}
                {$feePlan.title|escape:'htmlall':'UTF-8'}
            </a>
        </li>
    {/foreach}
</ul>
<script type='text/javascript'>
    (function($) {
        $(function() {
            function showTab(tab) {
                $('{$tabClasses}').hide();
                $('.tab-' + tab).show();
            }
            showTab('general_3_0_0');
            $('.alma-pnx-tabs li a').click(function(e) {
                e.preventDefault();
                $('.alma-pnx-tabs li').removeClass('active');
                $(this).parent().addClass('active');
                const tab = $(this).data('target');
                showTab(tab);
            });
        });
    })(jQuery);
</script>
