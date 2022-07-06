{*
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *}

{if $payment}
<section class="order-confirmation">
    <div class="alma-confirmation--logo">
        <img src="/modules/alma/views/img/logos/logo_alma.svg" alt="Alma" />
    </div>
    <h2>
        {$wording.paymentAlmaSuccessful}
    </h2>
    <h3>
        {$wording.orderReference}
    </h3>

    <p>
        {$wording.detailsPayment}
    </p>
    <div class="alma-fee-plan--block">
    {foreach from=$payment->payment_plan item=plan key=k name=counter}
        <span class="alma-fee-plan--description">
            <span class="alma-fee-plan--date">
            {if $payment->deferred_trigger}
                {if $smarty.foreach.counter.iteration === 1}
                    {$wording.today}
                {elseif $smarty.foreach.counter.iteration === 2}
                    {$wording.atShipping}
                {else}
                    {assign var='nb_installment_month' value=$plan->time_delta_from_start['months']}
                    {if $plan->time_delta_from_start['days'] >= 30 && $plan->time_delta_from_start['months'] == 0}
                        {assign var='nb_installment_month' value='1'}
                    {/if}
                    {l s='%s month later' sprintf=[$nb_installment_month] mod='alma'}
                {/if}
            {else}
                {if $smarty.foreach.counter.iteration === 1}
                    {$wording.today}
                {else}
                    {dateFormat date=$plan->due_date|date_format:"%Y-%m-%d" full=0}
                {/if}
            {/if}
            </span>
            <span class="alma-fee-plan--amount">
                {almaFormatPrice cents=$plan->purchase_amount + $plan->customer_fee + $plan->customer_interest}
                {if $plan->customer_fee > 0}
                    {capture assign='fees'}{almaFormatPrice cents=$payment->payment_plan[0]->customer_fee}{/capture}
                    <small style="display: block">
                        {$plan->textIncludinfFees}
                    </small>
                {/if}
            </span>
        </span>
    {/foreach}
    </div>
    <p>
        {$wording.youReceiveConfirmationEmail}
    </p>
    <p>
        {$wording.toFollowPaymentLinkAlma}
    </p>
    <p>
        {$wording.weAppreciateBusiness}
    </p>
</section>
{/if}