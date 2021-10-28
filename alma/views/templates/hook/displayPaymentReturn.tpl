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

{capture assign='fixedAPR'}{l s='Fixed APR' mod='alma'}{/capture}
{capture assign='cartTotal'}{l s='Cart total' mod='alma'}{/capture}
{capture assign='costCredit'}{l s='Cost of credit' mod='alma'}{/capture}
{capture assign='total'}{l s='Total' mod='alma'}{/capture}

<section class="order-confirmation">
    <div class="alma-confirmation--logo">
        <img src="/modules/alma/views/img/logos/logo_alma.svg" alt="Alma" />
    </div>
    <h2>
        {l s='Your payment with Alma was successful' mod='alma'}
    </h2>
    <h3>
        {l s='Here is your order reference:' mod='alma'} {$order_reference|escape:'htmlall':'UTF-8'}
    </h3>

    <p>
        {l s='Details for your payment:' mod='alma'} <b>{$payment_order->payment_method|escape:'htmlall':'UTF-8'}</b>
    </p>
    <div class="alma-fee-plan--block">
        <strong>{l s='Your credit' mod='alma'}</strong>
        {foreach from=$payment->payment_plan item=plan name=counter}
            <span class="alma-fee-plan--description">
                <span class="alma-fee-plan--date">
                    {if $smarty.foreach.counter.iteration === 1}
                        {l s='Today' mod='alma'}
                    {else}
                        {dateFormat date=$plan->due_date|date_format:"%Y-%m-%d" full=0}
                    {/if}
                </span>
                <span class="alma-fee-plan--amount">
                    {almaFormatPrice cents=$plan->purchase_amount + $plan->customer_fee + $plan->customer_interest}
                    {if $plan->customer_fee > 0}
                        {capture assign='fees'}{almaFormatPrice cents=$payment->payment_plan[0].customer_fee}{/capture}
                        <small style="display: block">
                            {l s='(Including fees: %s)' sprintf=[$fees] mod='alma'}
                        </small>
                    {/if}
                </span>
            </span>
        {/foreach}
        {if 4 < $payment->installments_count}
            <strong>
            <span class="alma-fee-plan--description">            
                    <span class="alma-fee-plan--date">{$total}</span>
                    <span class="alma-fee-plan--amount">{almaFormatPrice cents=$total_credit}</span>            
            </span>
            </strong>

            <hr class="alma-fee-plan--space" />
            <span class="alma-fee-plan--description">
                <span class="alma-fee-plan--date">{$cartTotal}</span>
                <span class="alma-fee-plan--amount">{almaFormatPrice cents=$purchase_amount}</span>
            </span>
            <span class="alma-fee-plan--description">
                <span class="alma-fee-plan--date">{$costCredit}</span>
                <span class="alma-fee-plan--amount">{almaFormatPrice cents=$customer_interest_total}</span>
            </span>

            
            <span class="alma-fee-plan--description">
                <span class="alma-fee-plan--date">{$fixedAPR}</span>
                <span class="alma-fee-plan--amount">{$annual_interest_rate * 100}%</span>
            </span>
        {/if}
    </div>
    <p>
        {l s='You should receive a confirmation email shortly' mod='alma'}
    </p>
    <p>
        {l s='To check your payment\'s progress, change you card or pay in advance:' mod='alma'} <a href="{$payment->url}" target="_blank" title="{l s='follow its deadlines' mod='alma'}">{l s='click here' mod='alma'}</a>
    </p>
    <p>
        {l s='We appreciate your business' mod='alma'}
    </p>
</section>
