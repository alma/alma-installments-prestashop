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