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
{$installmentsCount=count($plans)}
{capture assign='fixedAPR'}{l s='Fixed APR' mod='alma'}{/capture}
{capture assign='cartTotal'}{l s='Cart total' mod='alma'}{/capture}
{capture assign='costCredit'}{l s='Cost of credit' mod='alma'}{/capture}
{capture assign='total'}{l s='Total' mod='alma'}{/capture}
{capture assign='firstAmount'}{almaFormatPrice cents=$plans[0].total_amount}{/capture}
{capture assign='fees'}{almaFormatPrice cents=$plans[0].customer_fee}{/capture}
{capture assign='nextAmounts'}{almaFormatPrice cents=$plans[1].total_amount}{/capture}

{if $oneLiner}
    <span class="alma-fee-plan--description">
        {almaDisplayHtml}
            {$installmentText}
        {/almaDisplayHtml}
        {if 4 >= $installmentsCount}
            {include file="modules/alma/views/templates/hook/_partials/fees.tpl" customer_fee=$plans[0].customer_fee fees=$fees}
        {/if}
    </span>

    {if $installmentsCount > 4}
        <br><br>
        <strong>{l s='Your credit' mod='alma'}</strong>
        <br>
        <span class="alma-fee-plan--description">
            <span class="alma-credit-desc-left">{$cartTotal}</span>
            <span class="alma-credit-desc-right">{almaFormatPrice cents=$creditInfo.totalCart}</span>
        </span>
        <br>
        <span class="alma-fee-plan--description">
            <span class="alma-credit-desc-left">{$costCredit}</span>
            <span class="alma-credit-desc-right">{almaFormatPrice cents=$creditInfo.costCredit}</span>
        </span>
        <br>
        <span class="alma-fee-plan--description">
            <span class="alma-credit-desc-left">{$fixedAPR}</span>
            <span class="alma-credit-desc-right">{$creditInfo.taeg / 100}%</span>
        </span>
        <br>
        <span class="alma-fee-plan--description">            
            <span style="float:left;"><b>{$total}</b></span>
            <span style="float:right;"><b>{almaFormatPrice cents=$creditInfo.totalCredit}</b></span>
        </span>
    {/if}
{else}
    {if $installmentsCount > 4}
        <span>
        {almaDisplayHtml}
            {l s='%1$s today then %2$d x %3$s' sprintf=[$firstAmount, $installmentsCount - 1, $nextAmounts] mod='alma'}
        {/almaDisplayHtml}        
        </span>
        <br>
        <br>
        <strong>{l s='Your credit' mod='alma'}</strong>
        <span class="alma-fee-plan--description">
            <span class="alma-fee-plan--date">{$cartTotal}</span>
            <span class="alma-fee-plan--amount">{almaFormatPrice cents=$creditInfo.totalCart}</span>
        </span>
        <span class="alma-fee-plan--description">
            <span class="alma-fee-plan--date">{$costCredit}</span>
            <span class="alma-fee-plan--amount">{almaFormatPrice cents=$creditInfo.costCredit}</span>
        </span>
        <span class="alma-fee-plan--description">
            <span class="alma-fee-plan--date">{$fixedAPR}</span>
            <span class="alma-fee-plan--amount">{$creditInfo.taeg / 100}%</span>
        </span>
        <strong>
        <span class="alma-fee-plan--description">            
                <span class="alma-fee-plan--date">{$total}</span>
                <span class="alma-fee-plan--amount">{almaFormatPrice cents=$creditInfo.totalCredit}</span>            
        </span>
        </strong>
        <br>        
    {else}
        {foreach from=$plans item=v key=k name=counter}
            <span class="alma-fee-plan--description">
                <span class="alma-fee-plan--date">
                    {$v.human_date}
                </span>
                <span class="alma-fee-plan--amount">
                    {almaFormatPrice cents=$v.total_amount}
                    {if $v.customer_fee > 0}
                        {capture assign='fees'}{almaFormatPrice cents=$plans[0].customer_fee}{/capture}
                        <small style="display: block">
                            {include file="modules/alma/views/templates/hook/_partials/customerFees.tpl" fees=$fees}
                        </small>
                    {/if}
                </span>
            </span>
        {/foreach}
    {/if}
{/if}
