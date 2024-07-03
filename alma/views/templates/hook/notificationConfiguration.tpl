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
{if isset($hasPSAccount) && $hasPSAccount}
    <div class="ps-account-container">
        {if isset($hasKey) && !$hasKey}
            {include file="./_partials/notificationFirstInstallation.tpl"}
            <div class="alma ps-account alert alert-info">
                <h2>
                    {l s='To use Alma, please follow these steps' mod='alma'}
                </h2>
                <ol>
                    <li>
                        <strong>
                            {almaDisplayHtml}
                                {l s='Associate PrestaShop account (%1$sjust below%2$s)' sprintf=['<a id="alma-associate-shop-button" href="#">', "</a>"] mod='alma'}
                            {/almaDisplayHtml}
                        </strong>
                    </li>
                    <li><strong>{l s='Create an Alma account' mod='alma'}</strong>
                        <p>
                            <a href="https://support.getalma.eu/hc/fr/articles/360007913920-D%C3%A9marrer-avec-le-paiement-en-plusieurs-fois-Alma-sur-mon-site-e-commerce" target="_blank">
                                {l s='Consult our getting started guide' mod='alma'}
                            </a>
                        </p>
                    </li>
                    <li>
                        <strong>{l s='Enter your API key' mod='alma'}</strong>
                        <p>
                            {almaDisplayHtml}
                                {l s='Find your API live key on your %1$s Alma dashboard%2$s' sprintf=['<a href="https://dashboard.getalma.eu/api" target="_blank">', '</a>'] mod='alma'}
                            {/almaDisplayHtml}
                        <br />
                            {almaDisplayHtml}
                                {l s='To use Test mode, retrieve your Test API key from your %1$ssandbox dashboard%2$s' sprintf=['<a href="https://dashboard.sandbox.getalma.eu/api" target="_blank">', '</a>'] mod='alma'}
                            {/almaDisplayHtml}
                        </p>
                    </li>
                </ol>
            </div>
        {/if}
        <prestashop-accounts></prestashop-accounts>
    </div>

    <script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}" rel=preload></script>

    <script>
        /*********************
         * PrestaShop Account *
         * *******************/
        window?.psaccountsVue?.init();
    </script>

    {if isset($hasKey) && !$hasKey}
        <script>
            window.onload = function() {
                psAccountIsCompleted = window.psaccountsVue.isOnboardingCompleted();
                if (psAccountIsCompleted != true) {
                    document.getElementById("alma_config_form").remove()
                    document.getElementById("alma_first_installation").remove()
                } else {
                    //Hide ps account notification
                    document.querySelector(".alma.ps-account.alert").remove()
                }
            }
        </script>
    {/if}
{/if}
{if isset($suggestPSAccount) && $suggestPSAccount}
    <div class="alma alert alert-dismissible alert-info">
        <h2>
            {l s='We offer to download the PrestaShop Account module ' mod='alma'}
        </h2>
        <p>
            {l s='Link your store to your PrestaShop account to take full advantage of the modules offered by the PrestaShop Marketplace and optimize your experience.'}
        </p>
        {almaDisplayHtml}
            {l s='You can find the module %1$shere%2$s' sprintf=['<a href="https://addons.prestashop.com/en/administrative-tools/49648-prestashop-account.html" target=\"_blank\">', '</a>'] mod='alma'}
        {/almaDisplayHtml}
    </div>
{/if}
