{*
* 2018 Alma / Nabla SAS
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
* @author    Alma / Nabla SAS <contact@getalma.eu>
* @copyright 2018 Alma / Nabla SAS
* @license   https://opensource.org/licenses/MIT The MIT License
*}

{if isset($validation_error)}
    <div class="{$validation_error_classes}">
        {if $validation_error == 'missing_required_setting'}
            {l s='Please fill in all required settings' mod='alma'}
        {elseif $validation_error == 'alma_client_null'}
            {l s='Error while initializing Alma API client.' mod='alma'}
            <br>
            {l s='Activate logging in the debug options and check the logs for more details.' mod='alma'}
        {elseif $validation_error == 'live_authentication_error'}
            {l s='Could not connect to Alma using your Live API key.' mod='alma'}
            <br>
            <a href="https://dashboard.getalma.eu/security">
                {l s='Please double check your Live API key on your Alma dashboard.' mod='alma'}
            </a>
        {elseif $validation_error == 'test_authentication_error'}
            {l s='Could not connect to Alma using your Test API keys.' mod='alma'}
            <br>
            <a href="https://dashboard.getalma.eu/security">
                {l s='Please double check your Test API key on your Alma dashboard.' mod='alma'}
            </a>
        {elseif $validation_error == 'api_request_error'}
            {l s='API error:' mod='alma'}
            <br>
            {$error}
        {elseif $validation_error == 'inactive_live_account'}
            {if $level == 'warning'}
                {l s='Your Alma account is not activated yet. You won\'t be able to use Alma in Live mode.' mod='alma'}
                <br>
                <a href="https://dashboard.getalma.eu/security">
                    {l s='Activate your account on your Alma dashboard before switching to Live mode.' mod='alma'}
                </a>
            {else}
                {l s='Your Alma account needs to be activated before you can use Alma on your shop.' mod='alma'}
                <br>
                <a href="https://dashboard.getalma.eu/security">
                    {l s='Go to your Alma dashboard to activate your account.' mod='alma'}
                </a>
                <br>
                {l s='You can refresh/come back to this page when you are ready.' mod='alma'}
            {/if}
        {elseif $validation_error == 'inactive_test_account'}
            {if $level != 'warning'}
                {l s='Your Alma account needs to be activated before you can use Alma on your shop.' mod='alma'}
                <br>
                <a href="https://dashboard.getalma.eu/security">
                    {l s='Go to your Alma dashboard to activate your account.' mod='alma'}
                </a>
                <br>
                {l s='You can refresh/come back to this page when you are ready.' mod='alma'}
            {/if}
        {else}
            {$validation_error}
        {/if}
    </div>

{elseif isset($tip)}
    <div class="{$tip_classes}">
        {l s='Thanks for installing Alma!' mod='alma'}
        <br>
        {l s='You must start by filling in your API keys.' mod='alma'}
        <br>
        <a href="https://dashboard.getalma.eu/security">
            {l s='You can find them in your Alma dashboard' mod='alma'}
        </a>
    </div>
{else}
    <div class="{$success_classes}">
        {l s='Settings successfully updated' mod='alma'}
    </div>
{/if}
