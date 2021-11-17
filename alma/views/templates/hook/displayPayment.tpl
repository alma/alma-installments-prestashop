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

{assign var="iconDisplay" value=""}
{assign var="almaButton" value="alma-button-with-bkg"}
{if $old_prestashop_version}
    {assign var="iconDisplay" value="disable-arrow-icon"}
    {assign var="almaButton" value="alma-button"}
{/if}
{foreach from=$options item=option}
    {if $option.isDeferred}
        {include file="modules/alma/views/templates/hook/_partials/displayPayment_deferred.tpl" plans=$option.plans installmentText=$option.installmentText deferred_trigger_limit_days=$option.deferred_trigger_limit_days creditInfo=$option.creditInfo iconDisplay=$iconDisplay almaButton=$almaButton}
    {else}
        {include file="modules/alma/views/templates/hook/_partials/displayPayment_pnx.tpl" plans=$option.plans installmentText=$option.installmentText deferred_trigger_limit_days=$option.deferred_trigger_limit_days creditInfo=$option.creditInfo iconDisplay=$iconDisplay almaButton=$almaButton}
    {/if}
{/foreach}
{if $activateFragment}
<div id="almaFragments" data-apimode="{$apiMode}" data-merchantid="{$merchantId}" data-activatefragment="{$activateFragment}"></div>
<script type="text/javascript">
    (function($) {
        $(function() {                       
            $(".alma-fragments-deferred").click(function (e) {
                e.preventDefault();
                $(".display-fragment").remove();
                $(this)
                    .parent()
                    .parent()
                    .after(
                        '<div id="alma-payment" class="col-xs-12 display-fragment"></div>'
                    );
                processAlmaPayment(this.href);
            });
        
            $(".alma-fragments-pnx").click(function (e) {
                if (getInstallmentByUrl(this.href) <= 4) {
                    e.preventDefault();
                    $(".display-fragment").remove();
                    $(this)
                        .parent()
                        .parent()
                        .after(
                            '<div id="alma-payment" class="col-xs-12 display-fragment"></div>'
                        );
                    processAlmaPayment(this.href);
                }
            });
        });
    })(jQuery);
</script>    
{/if}
