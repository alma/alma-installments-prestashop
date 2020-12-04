{*
 * 2018-2019 Alma SAS
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
 * @copyright 2018-2019 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *}

<div id="alma-excluded">
    <p>{l s='You can not offer Alma when selling weapons, medical equipment and dematerialized products (gift card, license keys for video games, software, ...)' mod='alma'}</p>
    <p>{l s='In case of failure to pay relating to an order containing a product of this list, your insurance Alma will not cover you.' mod='alma'}</p>
    <br />
    <p>
        {l s='You can use' mod='alma'}
        <strong>
            <a href='{$excludedLink}' title="{l s='exclusion lists' mod='alma'}">{l s='exclusion lists' mod='alma'}</a>
        </strong>
        {l s='to be compliant with these restrictions.' mod='alma'}</p>
    <br />
    <p><strong>{l s='Excluded categories : ' mod='alma'}</strong>&nbsp;{l s='%s' mod='alma' sprintf=$excludedCategories}</p>
</div>
