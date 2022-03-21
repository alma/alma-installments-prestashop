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
    {foreach from=$payment->payment_plan item=plan key=k name=counter}
        <span class="alma-fee-plan--description">
            <span class="alma-fee-plan--date">
            {if $payment->deferred_trigger}
                {if $smarty.foreach.counter.iteration === 1}
                    {l s='Today' mod='alma'}
                {elseif $smarty.foreach.counter.iteration === 2}
                    {l s='At shipping' mod='alma'}
                {else}
                    {l s='%s month later' sprintf=[$plan->time_delta_from_start['months']] mod='alma'}
                {/if}
            {else}
                {if $smarty.foreach.counter.iteration === 1}
                    {l s='Today' mod='alma'}
                {else}
                    {dateFormat date=$plan->due_date|date_format:"%Y-%m-%d" full=0}
                {/if}
            {/if}
            </span>
            <span class="alma-fee-plan--amount">
                {almaFormatPrice cents=$plan->purchase_amount + $plan->customer_fee}
                {if $plan->customer_fee > 0}
                    {capture assign='fees'}{almaFormatPrice cents=$payment->payment_plan[0]->customer_fee}{/capture}
                    <small style="display: block">
                        {l s='(Including fees: %s)' sprintf=[$fees] mod='alma'}
                    </small>
                {/if}
            </span>
        </span>
    {/foreach}
    </div>
    <p>
        {l s='You should receive a confirmation email shortly' mod='alma'}
    </p>
    <p>
        {l s='To check your payment\'s progress, change you card or pay in advance:' mod='alma'} <a href="{$payment->url|escape:'htmlall':'UTF-8'}" target="_blank" title="{l s='follow its deadlines' mod='alma'}">{l s='click here' mod='alma'}</a>
    </p>
    <p>
        {l s='We appreciate your business' mod='alma'}
    </p>
</section>
