{*
 * 2018-2024 Alma SAS
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
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *}
<div class="alma first-installation {$tip_classes|escape:'htmlall':'UTF-8'}" id="alma_first_installation">
    <h2>{l s='Thanks for installing Alma!' mod='alma'}</h2>
    <p>
        <strong>{l s='You need to create an Alma account before proceeding.' mod='alma'}</strong>
        <br />
        <a href="https://support.getalma.eu/hc/fr/articles/360007913920-D%C3%A9marrer-avec-le-paiement-en-plusieurs-fois-Alma-sur-mon-site-e-commerce" target="_blank">
            {l s='Read our getting started guide' mod='alma'}
        </a>
    </p>
    <p>
        <b>{l s='You can then fill in your API keys:' mod='alma'}</b>
        <br />
        {almaDisplayHtml}
            {l s='You can find your Live API key in %1$syour Alma dashboard%2$s' sprintf=['<a href="https://dashboard.getalma.eu/api" target=\"_blank\">', '</a>'] mod='alma'}
        {/almaDisplayHtml}
        <br />
        {almaDisplayHtml}
            {l s='To use the Test mode, you need your Test API key from %1$syour sandbox dasboard%2$s' sprintf=['<a href="https://dashboard.sandbox.getalma.eu/api" target=\"_blank\">', '</a>'] mod='alma'}
        {/almaDisplayHtml}
        <br />
    </p>
    <p>
        {l s='If you have any problems, please contact us by email at %1$ssupport@getalma.eu%2$s' sprintf=['<a href="mailto:support@getalma.eu">', '</a>'] mod='alma'}
    </p>
</div>
