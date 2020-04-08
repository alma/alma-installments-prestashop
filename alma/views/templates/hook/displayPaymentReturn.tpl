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

<section class="order-confirmation">
    <h2>
        {l s='Your order has been placed successfully' mod='alma'}
    </h2>
    <h3>
        {l s='It will be processed soon.' mod='alma'}
    </h3>

    <p>
        {l s='Here is your order reference:' mod='alma'} <b>{$order_reference|escape:'htmlall':'UTF-8'}</b>
    </p>
    <p>
        {l s='You should receive a confirmation email shortly' mod='alma'}
    </p>

    <p>
        {l s='We appreciate your business' mod='alma'}
    </p>
</section>
