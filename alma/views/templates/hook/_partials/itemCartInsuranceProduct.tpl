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
<div class="product-line-grid row py-1">
    <div class="product-line-grid-left col-md-3 col-xs-4">
        <span class="product-image media-middle">
            <img src="{$associatedInsurance.urlImageInsuranceProduct}" alt="{$associatedInsurance.nameInsuranceProduct|escape:'htmlall':'UTF-8'}" loading="lazy">
        </span>
    </div>
    <div class="product-line-grid-body col-md-4 col-xs-8">
        <div class="product-line-info">
            <span class="label">
                {$associatedInsurance.nameInsuranceProduct|escape:'htmlall':'UTF-8'}
            </span>
        </div>
        <div class="product-line-info">
            <span class="label">
                <strong>
                    {$associatedInsurance.reference|escape:'htmlall':'UTF-8'}
                </strong>
            </span>
        </div>

        <div class="product-line-info product-price h5">
            <div class="current-price">
                <span class="price">{Context::getContext()->currentLocale->formatPrice($associatedInsurance.price, $currency.iso_code)}</span>
            </div>
        </div>
    </div>
    <div class="product-line-grid-right product-line-actions col-md-5 col-xs-12">
        <div class="row">
            <div class="col-xs-4 hidden-md-up"></div>
            <div class="col-md-10 col-xs-6">
                <div class="row">
                    <div class="col-md-6 col-xs-6 qty">
                        <div class="input-group">
                            <input class="js-cart-line-product-quantity form-control"
                                   data-product-id="{$associatedInsurance.idInsuranceProduct|escape:'htmlall':'UTF-8'}"
                                   type="number"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   value="{$associatedInsurance.quantity|escape:'htmlall':'UTF-8'}"
                                   aria-label="{$associatedInsurance.nameInsuranceProduct|escape:'htmlall':'UTF-8'}"
                                   disabled=""
                                   style="display: block;">
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-2 price">
                        <span class="product-price">
                            <strong>
                                  {Context::getContext()->currentLocale->formatPrice($associatedInsurance.price * $associatedInsurance.quantity, $currency.iso_code)}
                            </strong>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-xs-2 text-xs-right">
                <div class="cart-line-product-actions">
                    <a data-alma-association-ids="{$associatedInsurance.idsAlmaInsuranceProduct|escape:'htmlall':'UTF-8'}"
                       data-action="remove-insurance-products"
                       data-token='{\Tools::getToken(false)|escape:'htmlall':'UTF-8'}'
                       href="#"
                       class="alma-remove-insurance-products"
                       data-link='{$ajaxLinkRemoveInsuranceProducts|escape:'htmlall':'UTF-8'}'
                    >
                        <i class="material-icons float-xs-left">delete</i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
{if $associatedInsurances|count !== 0 && $nbProductWithoutInsurance > 0}
    {include file="modules/alma/views/templates/hook/_partials/widgetAddInsuranceProducts.tpl"}
{/if}
