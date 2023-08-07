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
<div class="alma-fee-plan--block">
    <p>
        {$desc|escape:'htmlall':'UTF-8'}
    </p>
    {if $isInPageEnabled}
        <div class="alma-inpage" data-apimode="{$apiMode|escape:'htmlall':'UTF-8'}" data-merchantid="{$merchantId|escape:'htmlall':'UTF-8'}" data-isinpageenabled="{$isInPageEnabled|escape:'htmlall':'UTF-8'}" data-installment="{$installment|escape:'htmlall':'UTF-8'}" data-purchaseamount="{$creditInfo.totalCart|escape:'htmlall':'UTF-8'}" data-locale="{$locale|escape:'htmlall':'UTF-8'}"></div>
    {else}
        {include file="modules/alma/views/templates/hook/_partials/deferred.tpl" plans=$plans}
    {/if}
</div>
