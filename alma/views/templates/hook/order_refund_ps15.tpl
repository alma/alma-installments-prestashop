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
<br>
<div class="alma-success conf" style="display:none"></div>
<div class="alma-danger error" style="display:none"></div>
<fieldset>
    <legend>
        <img src="{$iconPath|escape:'htmlall':'UTF-8'}"/>
        {$wording.title}
    </legend>
    <form id="alma-refund"  method="POST" action="{$actionUrl|escape:'htmlall':'UTF-8'}" class="defaultForm form-horizontal form-alma {if $refund.percentRefund >= 100}disabled{/if}">
        <input type="hidden" class="alma" name="orderId" required value="{$order.id|escape:'htmlall':'UTF-8'}" />
        {if $refund.percentRefund > 0}
            <table cellspacing="0" cellpadding="0" class="table tableDnD alma-table-refund" style="float:right; {if !$refund}display:none;{/if}">
                <thead>
                <tr> 
                    <th>{l s='Amount refunded' mod='alma'}</th>
                    <th>{l s='Total' mod='alma'}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="refundAmount">
                            {$refund.totalRefundPrice|escape:'htmlall':'UTF-8'}
                        </td>
                        <td>
                            {$order.paymentTotalPrice|escape:'htmlall':'UTF-8'}
                        </td>
                        <td>
                            <img src="../img/admin/money.gif" />
                        </td>
                    </tr>
                </tbody>
            </table>
        {/if}
        <p>
            {$wording.description}
        </p>
        <div class="clear"></div>
        <label>{$wording.labelTypeRefund}</label>
        <div class="margin-form">
            {if $order.ordersId}
                <input type="radio" autocomplete="off" class="refundType" name="refundType" value="partial_multi" />
                <label>{$wording.labelRadioRefundOneOrder}</label>
                <div class="clear"></div>
            {/if} 
            <input type="radio" autocomplete="off" name="refundType" value="total" checked="checked"/>
            <label>
                {if $order.ordersId}
                    {$wording.labelRadioRefundAllOrder}
                    <br>
                    <i>{$wording.labelRadioRefundAllOrderInfoId}
                    <br>
                    {$wording.labelRadioRefundAllOrderInfoAmount}
                    </i>
                {else}
                    {$wording.labelRadioRefundTotalAmout}
                {/if}
            </label>
            <div class="clear"></div>
            <input type="radio" autocomplete="off" name="refundType" value="partial" />
            <label>{$wording.labelRadioRefundPartial}</label>
            <div class="clear"></div>
        </div>
        <div style="display:none;" id="amountDisplay">
            <label>
                {$wording.labelAmoutRefundPartial}
            </label>
            <div class="margin-form">
                <input type="text" autocomplete="off" class="alma-input-number" id="amount" value="" name="amount" placeholder="{$wording.placeholderInputRefundPartial}" />
            </div>
        </div>  
        <div class="clear"></div>
        <div class="margin-form">
            <input type="submit" class="button" value="{$wording.buttonRefund}" />
        </div>
    </form>
</fieldset>

{include file='./_partials/refunds.tpl'}
