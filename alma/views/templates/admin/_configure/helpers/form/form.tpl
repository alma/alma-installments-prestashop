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

{extends file="helpers/form/form.tpl"}

{block name="input"}
    {if $input.type == 'number'}
        {assign var='value_text' value=$fields_value[$input.name]}
            <input type="number"
                   name="{$input.name|escape:'htmlall':'UTF-8'}"
                   id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}"
                   value="{$value_text|escape:'html':'UTF-8'}"
                   class="alma {if isset($input.class)}{$input.class|escape:'htmlall':'UTF-8'}{/if}"
                    {if isset($input.size)} size="{$input.size|escape:'htmlall':'UTF-8'}"{/if}
                    {if isset($input.min)} min="{$input.min|escape:'htmlall':'UTF-8'}"{/if}
                    {if isset($input.max)} max="{$input.max|escape:'htmlall':'UTF-8'}"{/if}
                    {if isset($input.readonly) && $input.readonly} readonly="readonly"{/if}
                    {if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}
                    {if isset($input.autocomplete) && !$input.autocomplete} autocomplete="off"{/if}
                    {if isset($input.required) && $input.required } required="required" {/if}
                    {if isset($input.placeholder) && $input.placeholder } placeholder="{$input.placeholder|escape:'htmlall':'UTF-8'}"{/if}
            />
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
