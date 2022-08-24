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
        {else}
            {$validation_error|escape:'htmlall':'UTF-8'}
        {/if}
    </div>

{elseif isset($tip)}
    <div class="{$tip_classes|escape:'htmlall':'UTF-8'}">
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
{elseif isset($share_of_checkout)}
    <div class="alma {$tip_classes|escape:'htmlall':'UTF-8'}">
        <div class="row">
            <h2>{l s='Increase your performance & get insights !' mod='alma'}</h2>
            <p>
                {l s='By accepting this option, enable Alma to analyse the usage of your payment methods, [1]get more informations to perform[/1] and share this data with you. You can [2]unsubscribe and erase your data[/2] at any moment.' tags=['<b>', '<a href="">'] mod='alma'}
            </p>
            <p>
                {l s='[1]Know more about collected data[/1]' tags=['<a href="" class="accordion">'] mod='alma'}
            </p>
        </div>
        <div class="row">
            <p>
                <a class="btn btn-default" href="">{l s='Reject' mod='alma'}</a>
                <a class="btn btn-primary" href="">{l s='Accept' mod='alma'}</a>
            </p>
        </div>
    </div>
{else}
    <div class="{$success_classes|escape:'htmlall':'UTF-8'}">
        {l s='Settings successfully updated' mod='alma'}
    </div>
{/if}
