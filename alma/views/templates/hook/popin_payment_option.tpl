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

<div class='alma notify-popin {$psVersion}'>
    <a href='#' class='alma-close'><i class="icon icon-times"></i><i class="material-icons close">close</i></a>
    <div class='alma-open'>
        <div class='icon-pastille icon-p3x'></div>
        <h3>{l s='Pay in 3x or 4x without fees with Alma.' mod='alma'}</h3>
        <a href='#'>{l s='See more' mod='alma'}</a>
    </div>
</div>
<div class='alma banner-notify-popin {$psVersion}'>
    <a href='#' class='alma-close'><i class="icon icon-times"></i><i class="material-icons close">close</i></a>
    <div class='logo-alma'><img src='/modules/alma/views/img/logos/alma_payment_logos.svg' alt='Alma' width='45' /></div>
    <ul class='alma installment-plan list'>
        {foreach from=$installmentPlans item=plan}
            <li>{$plan}</li>
        {/foreach}
    </ul>
    <h3>{l s='Pay in 3x or 4x without fees with Alma.' mod='alma'}</h3>

    <ul class="alma reinsurance list">
        <li class="lightning">
            <span>{l s='Fast and without surprises' mod='alma'}</span>
            <p class="tip">
                {l s='Payment validated in a few minutes. You know exactly what you pay.' mod='alma'}
            </p>
        </li>
        <li class="coins">
            <span>{l s='At your own pace' mod='alma'}</span>
            <p class="tip">
                {l s='Pay your installments in advance or later free of charge. Control your budget to buy with confidence.' mod='alma'}
            </p>
        </li>
        <li class="check">
            <span>{l s='100% secure' mod='alma'}</span>
            <p class="tip">
                {l s='All your personal data is encrypted and your banking information is protected.' mod='alma'}
            </p>
        </li>
    </ul>
</div>