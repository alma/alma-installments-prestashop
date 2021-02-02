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

<form id="alma-refund" method="POST" action="{$actionUrl}" class="defaultForm form-horizontal">
    <input type="hidden" class="alma" name="orderId" required value="{$order.id}"/>
    <div class="panel" id="alma_refunds">
        <div class="panel-heading">
            <img src="{$iconPath}"/>
            {l s='Alma refund' mod='alma'}
        </div>
        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-3"></label>
                <div class="col-lg-9">
                    <p>
                        Texte : remboursement alma + changement de status si total bla bla bla toussa toussa...
                    </p>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3 required"> {l s='Refund type:' mod='alma'}</label>
                <div class="col-lg-9">                    
                    <div class="radio t">
                        <label>
                            <input type="radio" class="refundType" name="refundType" value="total" checked="checked"/>
                            {if $order.ordersId}
                                {l s='Refund the integrity of this purchase' mod='alma'}
                                <br>
                                <i>{l s='Refund this order (id : %1$d) and all linked orders (id : %2$s)' sprintf=array($order.id, $order.ordersId) mod='alma'}
                                <br>
                                {l s='Total amount : %s' sprintf=$order.ordersTotalAmount mod='alma'}
                                </i>
                            {else}
                                {l s='Total' mod='alma'}
                            {/if}

                        </label>
                    </div>
                    {if $order.ordersId}
                        <div class="radio t">
                            <label>
                                <input type="radio" class="refundType" name="refundType" value="partial_multi" />                            
                                {l s='Only this purchase (%s)' sprintf=$order.maxAmount mod='alma'}
                            </label>
                        </div>
                    {/if}
                    <div class="radio t">
                        <label>
                            <input type="radio" class="refundType" name="refundType" value="partial"/>
                            {l s='Partial' mod='alma'}
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group" id="amountDisplay" style="display: none">
                <label class="control-label col-lg-3 required"> {l s='Amount (Max. %s):' sprintf=$order.maxAmount mod='alma'}</label>
                <div class="col-lg-9">
                    <div class="input-group">
                        <span class="input-group-addon">{$order.currencySymbol}</span>
                        <input type="text" class="alma" value="" name="amount"
                               placeholder="{l s='Amount to refund...' mod='alma'}"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer clear">
            <button type="submit" class="button btn btn-primary button-medium pull-right">
                <span>{l s='Process this refund' mod='alma'}</button>
        </div>
    </div>
</form>

{include file='./_partials/refunds.tpl'}
