{*
 * 2018-2020 Alma SAS
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
 * @copyright 2018-2020 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *}

{if $oneLiner}
    {$installmentsCount=count($plans)}
    {capture assign='firstAmount'}{almaFormatPrice price=($plans[0].purchase_amount + $plans[0].customer_fee) / 100}{/capture}
    {capture assign='fees'}{almaFormatPrice price=($plans[0].customer_fee) / 100}{/capture}
    {capture assign='nextAmounts'}{almaFormatPrice price=($plans[1].purchase_amount + $plans[1].customer_fee) / 100}{/capture}

    <span class="alma-fee-plan--description">
        {almaDisplayHtml}
            {l s='%1$s today then %2$d&#8239;⨉&#8239;%3$s' sprintf=[$firstAmount, $installmentsCount - 1, $nextAmounts] mod='alma'}
        {/almaDisplayHtml}

        <br>
        <small>
            {if $plans[0].customer_fee > 0}
                {l s='(Including fees: %s)' sprintf=[$fees] mod='alma'}
            {else}
                {l s='(No additional fees)' mod='alma'}
            {/if}
        </small>
    </span>
{else}
    {foreach from=$plans item=v name=counter}
        {$amount=($v.purchase_amount + $v.customer_fee) / 100}

        <span class="alma-fee-plan--description">
            <span class="alma-fee-plan--date">
                {if $smarty.foreach.counter.iteration === 1}
                    {l s='Today' mod='alma'}
                {else}
                    {dateFormat date=$v.due_date|date_format:"%Y-%m-%d" full=0}
                {/if}
            </span>
            <span class="alma-fee-plan--amount">
                {almaFormatPrice price=$amount}
                {if $v.customer_fee > 0}
                    {capture assign='fees'}{almaFormatPrice price=($plans[0].customer_fee) / 100}{/capture}
                    <small style="display: block">
                        {l s='(Including fees: %s)' sprintf=[$fees] mod='alma'}
                    </small>
                {/if}
            </span>
        </span>
    {/foreach}
{/if}
