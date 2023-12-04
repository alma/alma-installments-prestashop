<div class="alma-data-cart-product-insurance"
     data-id-product="{$productId}"
     data-id-cart="{$idCart}"
     data-is-alma-insurance="{$isAlmaInsurance}"
     data-no-insurance-associated="{$associatedInsurances|count}"
>

    <div class="actions-alma-insurance-product container" style="display:none;"   >
        <table style ="border: 1px solid #d6d4d4;border-collapse: collapse;">
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

</div>