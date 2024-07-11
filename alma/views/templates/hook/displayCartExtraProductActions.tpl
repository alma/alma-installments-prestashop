{*
 * 2018-2024 Alma SAS
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
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *}
{capture assign='cmsReference'}{almaCmsReference product_id=$product.id product_attribute_id=$product.id_product_attribute regular_price=$product.price_without_reduction}{/capture}

<div class="alma-data-product"
     data-reference="{$product.reference}"
     data-id-product="{$product.id}"
     data-id-cart="{$idCart}"
     data-is-alma-insurance="{$isAlmaInsurance}"
     data-no-insurance-associated="{$associatedInsurances|count}"
>
    <div class="actions-alma-insurance-product" style="display:none">
        {if $associatedInsurances}
            {foreach $associatedInsurances as $idProductAttributeInsurance => $associatedInsurance}
                {include file="modules/alma/views/templates/hook/_partials/itemCartInsuranceProduct.tpl"}
            {/foreach}
        {/if}
    </div>
    {if $insuranceSettings.isInCartWidgetActivated}
        <div class="widget-alma-insurance-cart-item" style="display:none">
            <iframe
                    id="product-alma-iframe-{$cmsReference}"
                    class="cart-item-alma-iframe"
                    style="display:none"
                    data-product-id="{$product.id|escape:'htmlall':'UTF-8'}"
                    data-product-attribute-id="{$product.id_product_attribute|escape:'htmlall':'UTF-8'}"
                    data-product-customization-id="{$product.id_customization|intval}"
                    data-token='{$token|escape:'htmlall':'UTF-8'}'
                    data-link='{$ajaxLinkAddInsuranceProduct|escape:'htmlall':'UTF-8'}'
                    src="{$iframeUrl}">
            </iframe>
        </div>
    {/if}

</div>
