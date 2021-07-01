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

<div style="margin-bottom: 20px;">
    <p>
        {$desc|escape:'htmlall':'UTF-8'}
    </p>

    {foreach from=$options item=option name=counter}     
        <label for="{$option.key}">
        {assign var="checked" value=""}        
        {if $smarty.foreach.counter.iteration === 1}
            {assign var="checked" value="checked='checked'"}
        {/if}        
            <input {$checked} onclick="handleChangeAlmaDeferred(this);" autocomplete="off" type="radio" name="alma_deferred" value="{$option.link}" id="{$option.key}">
            {l s='+ %d days' sprintf=array($option.duration) mod='alma'}
            &nbsp;
        </label>
    {/foreach}
    <br>
    {foreach from=$options item=option name=counter}
        {assign var="display" value="display:none;"}
        {if $smarty.foreach.counter.iteration === 1}
            {assign var="display" value=""}
        {/if}        
        <span style="{$display}" class="alma-button--fee-plans alma-deferred-display" id="deferred_alma_{$option.key}">
            {include file="modules/alma/views/templates/hook/_partials/deferred.tpl" plans=$option.plans}
        </span>
    {/foreach}
</div>
<script type="text/javascript">
    ;handleChangeAlmaDeferred = function(e){      
        for (let el of document.querySelectorAll('.alma-deferred-display')) el.style.display = 'none';
        document.querySelector('#deferred_alma_'+e.id).style.display = "block";  
        let payment = document.querySelector( 'input[name="payment-option"]:checked');        
        let div = document.querySelector('div[id="pay-with-'+payment.id+'-form"');
        let form = div.querySelector('form');        
        form.action = e.value;
    };
</script>
