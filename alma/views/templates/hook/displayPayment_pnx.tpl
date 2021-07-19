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

{assign var="iconDisplay" value=""}
{assign var="almaButton" value="alma-button-with-bkg"}
{if $old_prestashop_version}
    {assign var="iconDisplay" value="disable-arrow-icon"}
     {assign var="almaButton" value="alma-button"}
{/if}

{if $disabled}
    <div class="row">
        <div class="col-xs-12">
            <p class="payment_module">
                <a href="#" onclick="return false;" class="disabled {$almaButton} {$iconDisplay}">
                    <span class="alma-button--logo">
                        <img src="{$logo|escape:'htmlall':'UTF-8'}" alt="Alma">
                    </span>
                    <span class="alma-button--text">
                        <span class="alma-button--title">{$title|escape:'htmlall':'UTF-8'}</span>
                        <span class="alma-button--description">
                            {if $desc}
                                <br>
                                <span class="alma-button--description">{$desc|escape:'htmlall':'UTF-8'}</span>
                            {/if}
                            <br><br>                            
                            {l s='Alma Monthly Installments are not available for this order' mod='alma'}
                        </span>
                    </span>
                </a>
            </p>
        </div>
    </div>
{else}
    {if $options}
        <div class="row">
            <div class="col-xs-12">
                <p class="payment_module">
                    <a  class="alma-fragments {$almaButton} {$iconDisplay}">
                        <span class="alma-button--logo">
                            <img src="{$logo|escape:'htmlall':'UTF-8'}" alt="Alma">
                        </span>
                        <span class="alma-button--text">
                            <span class="alma-button--title">{$title|escape:'htmlall':'UTF-8'}</span>
                            {if $desc}
                                <br>
                                <span class="alma-button--description">{$desc|escape:'htmlall':'UTF-8'}</span>
                            {/if}
                            <span class="alma-button--fee-plans">
                                {foreach from=$options item=option name=counter}                               
                                    {assign var="checked" value=""}
                                    {if $smarty.foreach.counter.iteration === 1}
                                        {assign var="checked" value="checked='checked'"}
                                    {/if}
                                    <input autocomplete="off" type="radio" name="alma_pnx" value="{$option.link}" {$checked} id="alma_p{$option.pnx}x">
                                    <label for="alma_p{$option.pnx}x">
                                        <img style="float:none;" src="{$option.logo_pnx|escape:'htmlall':'UTF-8'}" alt=""/>
                                    </label>
                                {/foreach}
                                {foreach from=$options item=option name=counter}
                                    {assign var="display" value="display:none;"}
                                    {if $smarty.foreach.counter.iteration === 1}
                                        {assign var="display" value=""}
                                    {/if}                             
                                    <span style="{$display}" class="alma-button--fee-plans alma-fee-plans-display" id="fee_plans_alma_p{$option.pnx}x">
                                        {include file="modules/alma/views/templates/hook/_partials/feePlan.tpl" plans=$option.plans oneLiner=true}
                                    </span>
                                {/foreach}
                            </span>
                            <br>
                            {if $old_prestashop_version}
                                <span class="button_large" id="processAlmaPnx">
                            {else}
                                <button type="submit" id="processAlmaPnx" class="button btn btn-default standard-checkout">
                            {/if}
                            <span>
                                {l s='Confirm & pay' mod='alma'}
                                <i class="icon-chevron-right right"></i>
                            </span>
                            {if $old_prestashop_version}
                                </span>
                            {else}
                                </button>
                            {/if}
                        </span>
                    </a>
                </p>
            </div>
        </div>
    {/if}
    <script type="text/javascript">
        (function($) {
            $(function() {
                $('input[name=alma_pnx]').on('change', function(){
                    $(".display-fragment").remove();
                    $('.alma-fee-plans-display').hide();                
                    $('#fee_plans_'+this.id).show();
                });

                 $("#processAlmaPnx").click(function (e) {
                    e.preventDefault();
                    $(".display-fragment").remove();
                    $(this)
                        .parent()
                        .parent()
                        .after(
                            '<div id="alma-payment" class="col-xs-12 display-fragment"></div>'
                        );
                    let url = $("input[name=alma_pnx]:checked").val();
                    processAlmaPayment(url);
                });          
            });
        })(jQuery);
    </script>
{/if}
