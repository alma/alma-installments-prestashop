<br>
<small>
    {if $customer_fee > 0}
        {include file="modules/alma/views/templates/hook/_partials/customerFees.tpl" fees=$fees}
    {else}
        {l s='(No additional fees)' mod='alma'}
    {/if}
</small>
