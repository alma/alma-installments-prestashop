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
{capture assign='productRegularPriceInCent'}{$product.price_without_reduction|escape:'htmlall':'UTF-8' * 100}{/capture}
{capture assign='cmsReference'}{almaCmsReference product_id=$product.id_product product_attribute_id=$product.id_product_attribute regular_price=$product.price_without_reduction}{/capture}

<div class="col-md-12 py-1">
    <div class="item-alma-insurance">
        <div class="product-line-grid-left">
            <span class="product-image media-middle">
                <img src="/modules/alma/views/img/logos/shield.svg" alt="{l s='Alma insurance' mod='alma'}">
            </span>
        </div>
        <div class="product-line-grid-right">
            <div class="product-line-info">
                <span class="label">
                    {l s='Protect the rest of your products with' mod='alma'}
                    <strong>
                        Alma
                    </strong>
                </span>
            </div>
            <div class="alma-action-item-insurance">
                <a data-product-id="{$product.id_product|escape:'htmlall':'UTF-8'}"
                   data-product-attribute-id="{$product.id_product_attribute|escape:'htmlall':'UTF-8'}"
                   data-product-price="{$productRegularPriceInCent}"
                   data-product-customization-id="{$product.id_customization|intval}"
                   data-insurance-contract-id="{$associatedInsurance.insuranceContractId}"
                   data-remaining-quantity="{$nbProductWithoutInsurance}"
                   data-id-iframe="product-alma-iframe-{$cmsReference}"
                   data-action="add-insurance-product"
                   data-token='{\Tools::getToken(false)|escape:'htmlall':'UTF-8'}'
                   href="#"
                   id="add-insurance-product-{$cmsReference}"
                   class="btn-add-insurance-product alma-add-all-insurance-product"
                   data-link='{$ajaxLinkAddInsuranceProduct|escape:'htmlall':'UTF-8'}'
                >
                    {l s='I want to insure all the %1$s in my basket' sprintf=[$product.name] mod='alma'}
                </a>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
