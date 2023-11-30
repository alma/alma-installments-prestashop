<div class="alma-data-product"
     data-reference="{$product->reference}"
     data-id-product="{$product->id}"
     data-id-cart="{$idCart}"
     data-is-alma-insurance="{$isAlmaInsurance}"
     data-no-insurance-associated="{$associatedInsurances|count}"
>

    <table class="actions-alma-insurance-product container" style="display:none">
            {foreach from=$associatedInsurances item=associatedInsurance key=idAlmaInsuranceProduct}
                {include file="modules/alma/views/templates/hook/_partials/cartProducts16.tpl" hasInsurance='1'}
            {/foreach}

            {if $associatedInsurances|count !== 0}
                {for $var=1 to $nbProductWithoutInsurance  }
                    {include file="modules/alma/views/templates/hook/_partials/cartProducts16.tpl" hasInsurance='0'}
                {/for}
            {/if}
    </table>

</div>