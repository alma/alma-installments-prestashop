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
<tr>
    <td class="">
        <a href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute, false, false, true)|escape:'html':'UTF-8'}">
            <img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'small_default')|escape:'html':'UTF-8'}" alt="{$product.name|escape:'html':'UTF-8'}" {if isset($smallSize)}width="{$smallSize.width/2}" height="{$smallSize.height/2}" {/if} /></a>
    </td>
    <td class="">
        <p class="">
            <b>
                <a href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute, false, false, true)|escape:'html':'UTF-8'}">{$product.name|escape:'html':'UTF-8'}</a></p>
        </b>
        <ul class="price text-right" >
            {if !empty($product.gift)}
                <li class="gift-icon">{l s='Gift!'}</li>
            {else}
                {if !$priceDisplay}
                    <li class="price">{convertPrice price=$product.price_wt}</li>
                {else}
                    <li class="price">{convertPrice price=$product.price}</li>
                {/if}

            {/if}
        </ul>
    </td>

    {if $hasInsurance == '1'}
        <td>
            <img src="/modules/alma/views/img/add-circle.svg">
        </td>
        <td class="">
            <img src="{$associatedInsurances[$idAlmaInsuranceProduct]['urlImage']}" {if isset($smallSize)}width="{$smallSize.width/1.5}" height="{$smallSize.height/1.5}" {/if} />
        </td>
        <td class="">
            <p class="">
                <b>
                {$associatedInsurance.insuranceProduct->getFieldByLang('name', $idLanguage)|escape:'htmlall':'UTF-8'}
                </b>
                <br>
                {$associatedInsurance.insuranceProductAttribute->reference|escape:'htmlall':'UTF-8'}

            </p>
            <ul class="price text-right">

            <li class="price">{$associatedInsurance.price|number_format} €</li>

            </ul>
        </td>
        <td>
            <a data-alma-association-id="{$idAlmaInsuranceProduct}"
               data-action="remove-product-with-insurance"
               data-token='{$token|escape:'htmlall':'UTF-8'}'
               rel="nofollow"

               class="alma-remove-association"
               data-link='{$ajaxLinkAlmaRemoveAssociation|escape:'htmlall':'UTF-8'}'
            >
                <i class="icon-trash"></i>
            </a>
        </td>
    {else}
        <td></td>
        <td></td>
        <td></td>
        <td>
            <a data-product-id="{$product.id_product|escape:'javascript'}"
                           data-product-attribute-id="{$product.id_product_attribute|escape:'javascript'}"
                           data-product-customization-id="{$product.id_customization|escape:'javascript'}"
                           data-action="remove-product-without-insurance"
                           data-token='{$token|escape:'htmlall':'UTF-8'}'
                           data-link='{$ajaxLinkAlmaRemoveProduct|escape:'htmlall':'UTF-8'}'
                           class="alma-remove-product"
            >
                <i class="icon-trash"></i>
            </a></td>
    {/if}

</tr>
