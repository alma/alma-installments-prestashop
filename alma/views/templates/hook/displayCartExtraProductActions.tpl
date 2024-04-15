{*
 * 2018-2023 Alma SAS
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *}
<div class="alma-data-product"
     data-reference="{$product->reference}"
     data-id-product="{$product->id}"
     data-id-cart="{$idCart}"
     data-is-alma-insurance="{$isAlmaInsurance}"
     data-no-insurance-associated="{$associatedInsurances|count}"
>
    <div class="actions-alma-insurance-product" style="display:none">
        {foreach from=$associatedInsurances item=$associatedInsurance key=$idAlmaInsuranceProduct}
            {include file="modules/alma/views/templates/hook/_partials/cartProducts.tpl" hasInsurance='1'}
        {/foreach}

        {if $associatedInsurances|count !== 0}
            {for $var=1 to $nbProductWithoutInsurance  }
                {include file="modules/alma/views/templates/hook/_partials/cartProducts.tpl" hasInsurance='0'}
            {/for}
        {/if}
    </div>
    <div class="widget-alma-insurance-cart-item" style="display:none">
        <iframe id="product-alma-iframe" src="https://protect.sandbox.almapay.com/almaProductInPageWidget.html?cms_reference={$product->id}-{$product->id_product_attribute}&amp;product_price={$product->price_without_reduction * 100}&amp;merchant_id=merchant_11xYpTY1GTkww5uWFKFdOllK82S1r7j5v5&amp;customer_session_id=testexplo&amp;cart_id={$idCart}"></iframe>
    </div>

</div>
