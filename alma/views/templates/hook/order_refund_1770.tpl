{*
 * 2018-2021 Alma SAS
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
 * @copyright 2018-2021 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *}

<div class="alert alert-success alma-success"  style="display:none" data-alert="success"></div>
<div class="alert alert-danger alma-danger" style="display:none" data-alert="danger"></div>

<div class="card mt-2">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <h3 class="card-header-title">
                    <img src="{$iconPath}"/>
                    {l s='Alma refund' mod='alma'}
                </h3>
            </div>
            <div class="col-md-6">
                <div class="progress alma-progress" style="height: 20px; {if !$refund}display:none{/if}">
                    <div class="progress-bar" role="progressbar" style="line-height: 10px;width: {$refund.percentRefund}%" aria-valuenow="{$refund.percentRefund}" aria-valuemin="0" aria-valuemax="100">{$refund.totalRefundAmount} / {$order.ordersTotalAmount}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form id="alma-refund" method="POST" action="{$actionUrl}" class="defaultForm form-horizontal form-alma {if $refund.percentRefund >= 100}disabled{/if}">
            <input type="hidden" class="alma" name="orderId" required value="{$order.id}"/>  
            <div class="row">
                <div class="col">
                    <p>
                        {l s='Text : refund alma + change status if total bla bla bla toussa toussa...' mod="alma"}
                    </p>
                </div>
            </div>
            <div class="form-group row">
                <label class='control-label col-lg-2'>                
                    <span class="text-danger">*</span> {l s='Refund type:' mod='alma'}
                </label>                
                <div class="col-sm">
                    <div class="alma--form_order_type_refund">
                        {if $order.ordersId}
                            <div class="radio t">
                                <label>
                                    <input type="radio" autocomplete="off" class="refundType" name="refundType" value="partial_multi" />                            
                                    {l s='Only this purchase (%s)' sprintf=$order.maxAmount mod='alma'}
                                </label>
                            </div>
                        {/if}
                        <div class="radio t">
                            <input type="radio" autocomplete="off" class="refundType" id="total" name="refundType" value="total" checked="checked"/>
                            <label>
                                {if $order.ordersId}
                                    {l s='Refund the integrity of this purchase : ' mod='alma'}
                                    <i>{l s='Refund this order (id : %1$d) and all linked orders (id : %2$s)' sprintf=array($order.id, $order.ordersId) mod='alma'}
                                    {l s='Total amount : %s' sprintf=$order.ordersTotalAmount mod='alma'}
                                    </i>
                                {else}
                                    {l s='Total' mod='alma'}
                                {/if}
                            </label>
                        </div>
                        <div class="radio t">
                            <label>
                                <input type="radio" autocomplete="off" class="refundType" name="refundType" value="partial"/>
                                {l s='Partial' mod='alma'}
                            </label>
                        </div>
                        <div class="form-group" id="amountDisplay" style="display: none">
                            <label class="form-control-label" for="amount">
                                <span class="text-danger">*</span> {l s='Amount (Max. %s):' sprintf=$order.ordersTotalAmount mod='alma'}
                            </label>
                            <div class="input-group">
                                <input 
                                    type="text" 
                                    class="alma" 
                                    name="amount" 
                                    autocomplete="off" 
                                    id="amount" 
                                    placeholder="{l s='Amount to refund...' mod='alma'}"
                                />
                                <div class="input-group-append">
                                    <div class="input-group-text">{$order.currencySymbol}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-right">
            <button type="submit" class="button btn btn-primary button-medium pull-right">
                <span>{l s='Process this refund' mod='alma'}</button>
        </div>
        </form>
    </div>
</div>

{include file='./_partials/refunds.tpl'}
