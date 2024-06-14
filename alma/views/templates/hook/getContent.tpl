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
{if isset($validation_error)}
    <div class="{$validation_error_classes|escape:'htmlall':'UTF-8'}">
        {if $validation_error == 'missing_required_setting'}
            {l s='Please fill in all required settings' mod='alma'}
        {elseif $validation_error == 'missing_key_for_live_mode'}
            {l s='Please provide your Live API key to operate in Live mode' mod='alma'}
        {elseif $validation_error == 'missing_key_for_test_mode'}
            {l s='Please provide your Test API key to operate in Test mode' mod='alma'}
        {elseif $validation_error == 'alma_client_null'}
            {l s='Error while initializing Alma API client.' mod='alma'}
            <br>
            {l s='Activate logging in the debug options and check the logs for more details.' mod='alma'}
        {elseif $validation_error == 'live_authentication_error'}
            {l s='Could not connect to Alma using your Live API key.' mod='alma'}
            <br>
            <a href="https://dashboard.getalma.eu/api" target="_blank">
                {l s='Please double check your Live API key on your Alma dashboard.' mod='alma'}
            </a>
        {elseif $validation_error == 'test_authentication_error'}
            {l s='Could not connect to Alma using your Test API keys.' mod='alma'}
            <br>
            <a href="https://dashboard.sandbox.getalma.eu/api" target="_blank">
                {l s='Please double check your Test API key on your Alma dashboard.' mod='alma'}
            </a>
        {elseif $validation_error == 'api_request_error'}
            {l s='API error:' mod='alma'}
            <br>
            {$error|escape:'htmlall':'UTF-8'}
        {elseif $validation_error == 'inactive_live_account'}
            {if isset($level) && $level == 'warning'}
                {l s='Your Alma account is not activated yet. You won\'t be able to use Alma in Live mode.' mod='alma'}
                <br>
                <a href="https://dashboard.getalma.eu/" target="_blank">
                    {l s='Activate your account on your Alma dashboard before switching to Live mode.' mod='alma'}
                </a>
            {else}
                {l s='Your Alma account needs to be activated before you can use Alma on your shop.' mod='alma'}
                <br>
                <a href="https://dashboard.getalma.eu/">
                    {l s='Go to your Alma dashboard to activate your account.' mod='alma'}
                </a>
                <br>
                {l s='You can refresh/come back to this page when you are ready.' mod='alma'}
            {/if}
        {elseif $validation_error == 'inactive_test_account'}
            {if isset($level) && $level != 'warning'}
                {l s='Your Alma account needs to be activated before you can use Alma on your shop.' mod='alma'}
                <br>
                <a href="https://dashboard.sandbox.getalma.eu/" target="_blank">
                    {l s='Go to your Alma dashboard to activate your account.' mod='alma'}
                </a>
                <br>
                {l s='You can refresh/come back to this page when you are ready.' mod='alma'}
            {/if}
        {elseif $validation_error == 'pnx_min_amount'}
            {if $n == 1 && $deferred_days > 0 && $deferred_months == 0}
                {l s='Minimum amount for deferred + %1$d days plan must be within %2$d and %3$d.' sprintf=array($deferred_days, $min, $max) mod='alma'}
            {elseif $n == 1 && $deferred_days == 0 && $deferred_months > 0}
                {l s='Minimum amount for deferred + %1$d months plan must be within %2$d and %3$d.' sprintf=array($deferred_months, $min, $max) mod='alma'}
            {else}
                {l s='Minimum amount for %1$d-installment plan must be within %2$d and %3$d.' sprintf=array($n, $min, $max) mod='alma'}
            {/if}
        {elseif $validation_error == 'pnx_max_amount'}
            {if $n == 1 && $deferred_days > 0 && $deferred_months == 0}
                {l s='Maximum amount for deferred %1$d days plan must be within %2$d and %3$d.' sprintf=array($deferred_days, $min, $max) mod='alma'}
            {elseif $n == 1 && $deferred_days == 0 && $deferred_months > 0}
                {l s='Maximum amount for deferred %1$d months plan must be within %2$d and %3$d.' sprintf=array($deferred_months, $min, $max) mod='alma'}
            {else}
                {l s='Maximum amount for %1$d-installment plan must be within %2$d and %3$d.' sprintf=array($n, $min, $max) mod='alma'}
            {/if}
        {elseif $validation_error == 'soc_api_error'}
            {l s='Impossible to save the Share of Checkout settings, please try again later' mod='alma'}
        {elseif $validation_error == 'custom_error' && isset($validation_message)}
            {$validation_message|escape:'htmlall':'UTF-8'}
        {else}
            {$validation_error|escape:'htmlall':'UTF-8'}
        {/if}
    </div>

{elseif isset($tip)}
    <div class="{$tip_classes|escape:'htmlall':'UTF-8'}" id="alma_first_installation">
        <p>
            {l s='Thanks for installing Alma!' mod='alma'}
            <br>
            <b>{l s='You need to create an Alma account before proceeding.' mod='alma'}</b>
            <br>
            <a href="https://support.getalma.eu/hc/fr/articles/360007913920-D%C3%A9marrer-avec-le-paiement-en-plusieurs-fois-Alma-sur-mon-site-e-commerce" target="_blank">
                {l s='Read our getting started guide' mod='alma'}
            </a>
        </p>
        <br>
        <p>
            <b>{l s='You can then fill in your API keys:' mod='alma'}</b>
            <br>
            {almaDisplayHtml}
                {l s='You can find your Live API key in %1$syour Alma dashboard%2$s' sprintf=['<a href="https://dashboard.getalma.eu/api" target=\"_blank\">', '</a>'] mod='alma'}
            {/almaDisplayHtml}
            <br>
            {almaDisplayHtml}
                {l s='To use the Test mode, you need your Test API key from %1$syour sandbox dasboard%2$s' sprintf=['<a href="https://dashboard.sandbox.getalma.eu/api" target=\"_blank\">', '</a>'] mod='alma'}
            {/almaDisplayHtml}
            <br>
        </p>
        <br>
        <p>
            En cas de problème, contactez-nous par email à <a href="mailto:support@getalma.eu">support@getalma.eu</a>
        </p>
    </div>
{elseif $updated}
    <div class="{$success_classes|escape:'htmlall':'UTF-8'}">
        {l s='Settings successfully updated' mod='alma'}
    </div>
{/if}

{if isset($hasPSAccount) &&  $hasPSAccount}
    <div class="ps-account-container">
        <div class="ps-account-steps-banner">
            <div class="ps-account-text-container">
                <div class="ps-account-title"> {l s='To use Alma, please follow these steps' mod='alma'}</div>
                <ol>
                    <li>
                        <div class="ps-account-list-title">{l s='1. Associate PrestaShop account (just below)' mod='alma'}</div>
                    </li>
                    <li>
                        <div class="ps-account-list-title">{l s='2. Create an Alma account' mod='alma'}</div>
                        <div>
                            <a href="https://support.getalma.eu/hc/fr/articles/360007913920-D%C3%A9marrer-avec-le-paiement-en-plusieurs-fois-Alma-sur-mon-site-e-commerce" target="_blank">
                                {l s='Consult our getting started guide' mod='alma'}
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="ps-account-list-title">{l s='3. Enter your API key' mod='alma'}</div>
                        <div>
                            {almaDisplayHtml}
                            {l s='Find your API live key on your %1$s Alma dashboard%2$s' sprintf=['<a href="https://dashboard.getalma.eu/api" target="_blank">', '</a'] mod='alma'}
                            {/almaDisplayHtml}
                        </div>
                        <div>
                            {almaDisplayHtml}
                            {l s='To use Test mode, retrieve your Test API key from your %1$s sandbox dashboard%2$s' sprintf=['<a href="https://dashboard.sandbox.getalma.eu/api" target="_blank">', '</a'] mod='alma'}
                            {/almaDisplayHtml}
                        </div>
                    </li>
                </ol>


            </div>
            <svg xmlns="http://www.w3.org/2000/svg" width="320" height="206" viewBox="0 0 320 206" fill="none">
                <path d="M208.543 16.2549C230.059 16.2549 252.619 29.0533 263.307 69.0556L309.695 235.915H263.984C260.936 223.52 253.82 212.505 243.772 204.63C233.724 196.756 221.325 192.476 208.557 192.476C195.789 192.476 183.39 196.756 173.342 204.63C163.295 212.505 156.178 223.52 153.13 235.915H107.419L153.807 69.0556C164.467 29.0533 187.027 16.2549 208.543 16.2549Z" fill="#FA5022"/>
                <path d="M64.7515 288.063L130.589 51.1795C145.718 -5.61027 177.737 -23.7798 208.274 -23.7798C238.812 -23.7798 270.83 -5.61027 285.999 51.1795L351.837 288.063" stroke="#FFCF00" stroke-width="29.3157"/>
                <path d="M173.265 158.98C182.528 149.866 194.658 144.84 207.228 144.908C219.809 144.846 231.949 149.882 241.217 159.008L217.458 64.5067C215.207 55.3447 211.831 52.6016 207.228 52.6016C202.625 52.6016 199.274 55.4818 196.998 64.5067L173.265 158.98Z" fill="#E7F8FA"/>
                <path d="M-31.1194 521.931L94.4756 69.7241C123.336 -38.6863 184.415 -73.3716 242.67 -73.3716C300.925 -73.3716 362.004 -38.6863 390.941 69.7241L516.536 521.931" stroke="#60D2DF" stroke-width="10.0518"/>
            </svg>
        </div>
        <prestashop-accounts></prestashop-accounts>
    </div>
    <script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}" rel=preload></script>

    <script>
        /*********************
         * PrestaShop Account *
         * *******************/
        window?.psaccountsVue?.init();
    </script>

    {if isset($hasKey) &&  !$hasKey}
        <script>
            window.onload = function() {
                if (window.psaccountsVue.isOnboardingCompleted() != true) {
                    document.getElementById("alma_config_form").remove()
                    document.getElementById("alma_first_installation").remove()
                }
            }
        </script>
    {/if}
{/if}
{if isset($suggestPSAccount) &&  $suggestPSAccount}

    <div class="alert alert-dismissible alert-info">
        <h4>
            {l s='We offer to download the PrestaShop Account module ' mod='alma'}
        </h4>
        <p>
            {l s='Link your store to your PrestaShop account to take full advantage of the modules offered by the PrestaShop Marketplace and optimize your experience.'}
        </p>
        {almaDisplayHtml}
        {l s='You can find the module %1$shere%2$s' sprintf=['<a href="https://addons.prestashop.com/en/administrative-tools/49648-prestashop-account.html" target=\"_blank\">', '</a>'] mod='alma'}
        {/almaDisplayHtml}
    </div>

{/if}
