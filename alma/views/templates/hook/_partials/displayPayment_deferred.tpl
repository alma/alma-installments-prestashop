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
{if $option.disabled}
    <div class="row">
        <div class="col-xs-12">
            <p class="payment_module">
                <a href="#" onclick="return false;" class="disabled {$almaButton} {$iconDisplay}">
                    <span class="alma-button--logo">
                        <img src="{$option.logo|escape:'htmlall':'UTF-8'}" alt="Alma">
                    </span>
                    <span class="alma-button--text">
                        <span class="alma-button--title">{$option.text|escape:'htmlall':'UTF-8'}</span>
                        <span class="alma-button--description">
                            {if $option.desc}
                                <br>
                                <span class="alma-button--description">{$option.desc|escape:'htmlall':'UTF-8'}</span>
                            {/if}
                            <br><br>
                            {l s='Alma Deferred Payment is not available for this order' mod='alma'}
                        </span>
                    </span>
                </a>
            </p>
        </div>
    </div>
{else}
    {if $options}
        <div class="row">
            <div class="col-xs-12">
                <p class="payment_module">
                    <a href="{$option.link}" class="{if $option.isInPageEnabled}alma-inpage ps16{/if} {$almaButton} {$iconDisplay}" id="payment-option-{$option.paymentOptionKey}">
                        <span class="alma-button--logo">
                            <img src="{$option.logo|escape:'htmlall':'UTF-8'}" alt="Alma">
                        </span>
                        <span class="alma-button--text">
                            <span class="alma-button--title">{$option.text|escape:'htmlall':'UTF-8'}</span>
                            {if $option.desc}
                                <br>
                                <span class="alma-button--description">{$option.desc|escape:'htmlall':'UTF-8'}</span>
                            {/if}
                            <span class="alma-button--fee-plans">
                                {include file="modules/alma/views/templates/hook/_partials/deferred.tpl" plans=$option.plans installmentText=$option.installmentText creditInfo=$option.creditInfo oneLiner=true}
                            </span>
                        </span>
                    </a>
                </p>
            </div>
            {if $option.isInPageEnabled}
                <div id="alma-inpage-payment-option-{$option.paymentOptionKey|escape:'htmlall':'UTF-8'}"
                     class="alma-inpage-payment-options"
                     data-apimode="{$apiMode|escape:'htmlall':'UTF-8'}"
                     data-merchantid="{$merchantId|escape:'htmlall':'UTF-8'}"
                     data-isinpageenabled="{$option.isInPageEnabled|escape:'htmlall':'UTF-8'}"
                     data-installment="{$option.pnx|escape:'htmlall':'UTF-8'}"
                     data-purchaseamount="{$creditInfo.totalCart|escape:'htmlall':'UTF-8'}"
                     data-locale="{$option.locale|escape:'htmlall':'UTF-8'}">
                    <div id="alma-inpage-iframe-plan-{$option.paymentOptionKey|escape:'htmlall':'UTF-8'}" class="alma-inpage-iframe"></div>
                </div>
            {/if}
        </div>
    {/if}    
{/if}
 