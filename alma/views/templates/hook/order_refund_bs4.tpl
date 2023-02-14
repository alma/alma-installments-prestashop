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
<div class="alert alert-success alma-success"  style="display:none" data-alert="success"></div>
<div class="alert alert-danger alma-danger" style="display:none" data-alert="danger"></div>

<div class="card mt-2">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <h3 class="card-header-title">
                    <img src="{$iconPath|escape:'htmlall':'UTF-8'}"/>
                    {$wording.title|escape:'htmlall':'UTF-8'}
                </h3>
            </div>
            <div class="col-md-6">
                {if $refund.percentRefund > 0}
                    <div class="progress alma-progress" style="height: 20px; {if !$refund}display:none{/if}">
                        <div class="progress-bar" role="progressbar" style="line-height: 10px;width: {$refund.percentRefund|escape:'htmlall':'UTF-8'}%" aria-valuenow="{$refund.percentRefund|escape:'htmlall':'UTF-8'}" aria-valuemin="0" aria-valuemax="100">{$refund.totalRefundPrice|escape:'htmlall':'UTF-8'} / {$order.paymentTotalPrice|escape:'htmlall':'UTF-8'}</div>
                    </div>
                {/if}
            </div>
        </div>
    </div>
    <div class="card-body">
        <form id="alma-refund" method="POST" action="{$actionUrl|escape:'htmlall':'UTF-8'}" class="defaultForm form-horizontal form-alma {if $refund.percentRefund|string_format:"%.2f" >= 100}disabled{/if}">
            <input type="hidden" class="alma" name="orderId" required value="{$order.id|escape:'htmlall':'UTF-8'}"/>  
            <div class="row">
                <div class="col">
                    <p>
                        {$wording.description}
                    </p>
                </div>
            </div>
            <div class="form-group row">
                <label class='control-label text-right col-lg-4'>                
                    <span class="text-danger">*</span> {$wording.labelTypeRefund|escape:'htmlall':'UTF-8'}
                </label>                
                <div class="col-sm">
                    {if $order.ordersId}
                        <div class="radio t">
                            <label>
                                <input type="radio" autocomplete="off" class="refundType form-check-input" name="refundType" value="partial_multi" />                            
                                {$wording.labelRadioRefundOneOrder|escape:'htmlall':'UTF-8'}
                            </label>
                        </div>
                    {/if}
                    <div class="radio t">
                        <label>
                            <input type="radio" autocomplete="off" class="refundType form-check-input" id="total" name="refundType" value="total" checked="checked"/>
                            {if $order.ordersId}
                                {$wording.labelRadioRefundAllOrder|escape:'htmlall':'UTF-8'}<br />
                                <i>{$wording.labelRadioRefundAllOrderInfoId|escape:'htmlall':'UTF-8'}<br />
                                {$wording.labelRadioRefundAllOrderInfoAmount|escape:'htmlall':'UTF-8'}
                                </i>
                            {else}
                                {$wording.labelRadioRefundTotalAmout|escape:'htmlall':'UTF-8'}
                            {/if}
                        </label>
                    </div>
                    <div class="radio t">
                        <label>
                            <input type="radio" autocomplete="off" class="refundType form-check-input" name="refundType" value="partial"/>
                            {$wording.labelRadioRefundPartial|escape:'htmlall':'UTF-8'}
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group row" id="amountDisplay" style="display: none">
                <label class='control-label text-right col-lg-4' for="amount">
                    <span class="text-danger">*</span> {$wording.labelAmoutRefundPartial|escape:'htmlall':'UTF-8'}
                </label>
                <div class="col-sm">
                    <div class="input-group">
                        <input 
                            type="text" 
                            class="alma" 
                            name="amount" 
                            autocomplete="off" 
                            id="amount" 
                            placeholder="{$wording.placeholderInputRefundPartial|escape:'htmlall':'UTF-8'}"
                        />
                        <div class="input-group-append">
                            <div class="input-group-text">{$order.currencySymbol|escape:'htmlall':'UTF-8'}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-right">
            <button type="submit" class="button btn btn-primary button-medium pull-right">
                <span>{$wording.buttonRefund|escape:'htmlall':'UTF-8'}</button>
        </div>
        </form>
    </div>
</div>

{include file='./_partials/refunds.tpl'}
