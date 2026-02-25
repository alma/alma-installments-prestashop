{assign var='tabClasses' value=''}
<ul class='nav nav-tabs alma-pnx-tabs'>
    {foreach $fee_plans as $planKey => $feePlan name=loop}
        {* Loop to explode the plans for the selector in string *}
        {if $smarty.foreach.loop.first}
            {assign var='tabClasses' value=".tab-`$planKey`"}
        {else}
            {assign var='tabClasses' value="`$tabClasses`, .tab-`$planKey`"}
        {/if}
        <li {if $feePlan.active}class='active'{/if}>
            <a href='#{$planKey|escape:'htmlall':'UTF-8'}' data-toggle='tab'>{$feePlan.title|escape:'htmlall':'UTF-8'}</a>
        </li>
    {/foreach}
</ul>
<script type='text/javascript'>
    $(document).ready(function() {
        function showTab(tab) {
            $('{$tabClasses}').hide();
            $('.tab-' + tab).show();
        }
        showTab('general_3_0_0');
        $('.alma-pnx-tabs a').click(function(e) {
            e.preventDefault();
            $('.alma-pnx-tabs li').removeClass('active');
            $(this).parent().addClass('active');
            const tab = $(this).attr('href').replace('#', '');
            showTab(tab);
        });
    });
</script>
