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


{capture assign='amount'}{almaFormatPrice cents=$plans[0].purchase_amount + $plans[0].customer_fee}{/capture}
{capture assign='due_date'}{dateFormat date=$plans[0].due_date|date_format:"%Y-%m-%d" full=0}{/capture}
{capture assign='fees'}{almaFormatPrice cents=$plans[0].customer_fee}{/capture}
{capture assign='zero'}{almaFormatPrice cents=000}{/capture}
<span class="alma-deferred--description">
    {l s='%1$s Today then %2$s on %3$s' sprintf=[$zero, $amount,$due_date] mod='alma'}
    <br>
    <small>
        {if $plans[0].customer_fee > 0}
            {l s='(Including fees: %s)' sprintf=[$fees] mod='alma'}
        {else}
            {l s='(No additional fees)' mod='alma'}
        {/if}
    </small>
</span>
