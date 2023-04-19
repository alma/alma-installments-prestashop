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
{extends file="helpers/form/form.tpl"}

{block name="defaultForm"}
<form id="{if isset($fields.form.form.id_form)}{$fields.form.form.id_form|escape:'htmlall':'UTF-8'}{else}{if $table == null}configuration_form{else}{$table}_form{/if}{/if}" class="defaultForm {$name_controller}" action="{$current}&{if !empty($submit_action)}{$submit_action}=1{/if}&token={$token}" method="post" enctype="multipart/form-data" {if isset($style)}style="{$style}"{/if}>
    {if $form_id}
        <input type="hidden" name="{$identifier}" id="{$identifier}" value="{$form_id}" />
    {/if}
    {foreach $fields as $f => $fieldset}
        <fieldset id="fieldset_{$f}">
            {foreach $fieldset.form as $key => $field}
                {if $key == 'legend'}
                    <legend>
                        {if isset($field.image)}<img src="{$field.image}" alt="{$field.title|escape:'htmlall':'UTF-8'}" />{/if}
                        {$field.title}
                    </legend>
                {elseif $key == 'description' && $field}
                    <p class="description">{$field}</p>
                {elseif $key == 'input'}
                    <div class="form-wrapper">
                        {foreach $field as $input}
                        <div class="form-group{if isset($input.form_group_class)} {$input.form_group_class}{/if}{if $input.type == 'hidden'} hide{/if}"{if $input.name == 'id_state'} id="contains_states"{if !$contains_states} style="display:none;"{/if}{/if}{if isset($tabs) && isset($input.tab)} data-tab-id="{$input.tab}"{/if}>
                            {if $input.type == 'hidden'}
                                <input type="hidden" name="{$input.name}" id="{$input.name}" value="{$fields_value[$input.name]|escape:'htmlall':'UTF-8'}" />
                            {else}
                                {if $input.name == 'id_state'}
                                    <div id="contains_states" {if !$contains_states}style="display:none;"{/if}>
                                {/if}
                                {block name="label"}
                                    {if isset($input.label)}<label>{$input.label} </label>{/if}
                                {/block}
                                {block name="field"}
                                    <div class="margin-form{if isset($input.form_group_class)} {$input.form_group_class}{/if}">
                                    {block name="input"}
                                    {if $input.type == 'text' || $input.type == 'tags'}
                                        {if isset($input.lang) AND $input.lang}
                                            <div class="translatable">
                                                {foreach $languages as $language}
                                                    <div class="lang_{$language.id_lang}" style="display:{if $language.id_lang == $defaultFormLanguage}block{else}none{/if}; float: left;">
                                                        {if $input.type == 'tags'}
                                                            {literal}
                                                            <script type="text/javascript">
                                                                $().ready(function () {
                                                                    var input_id = '{/literal}{if isset($input.id)}{$input.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}{literal}';
                                                                    $('#'+input_id).tagify({delimiters: [13,44], addTagPrompt: '{/literal}{l s='Add tag' js=1}{literal}'});
                                                                    $({/literal}'#{$table}{literal}_form').submit( function() {
                                                                        $(this).find('#'+input_id).val($('#'+input_id).tagify('serialize'));
                                                                    });
                                                                });
                                                            </script>
                                                            {/literal}
                                                        {/if}
                                                        {assign var='value_text' value=$fields_value[$input.name][$language.id_lang]}
                                                        <input type="text"
                                                                name="{$input.name}_{$language.id_lang}"
                                                                id="{if isset($input.id)}{$input.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}"
                                                                value="{if isset($input.string_format) && $input.string_format}{$value_text|string_format:$input.string_format|escape:'htmlall':'UTF-8'}{else}{$value_text|escape:'htmlall':'UTF-8'}{/if}"
                                                                class="{if $input.type == 'tags'}tagify {/if}{if isset($input.class)}{$input.class}{/if}"
                                                                {if isset($input.size)}size="{$input.size}"{/if}
                                                                {if isset($input.maxlength)}maxlength="{$input.maxlength}"{/if}
                                                                {if isset($input.readonly) && $input.readonly}readonly="readonly"{/if}
                                                                {if isset($input.disabled) && $input.disabled}disabled="disabled"{/if}
                                                                {if isset($input.autocomplete) && !$input.autocomplete}autocomplete="off"{/if} />
                                                        {if !empty($input.hint)}<span class="hint" name="help_box">{$input.hint}<span class="hint-pointer">&nbsp;</span></span>{/if}
                                                    </div>
                                                {/foreach}
                                            </div>
                                        {else}
                                            {if $input.type == 'tags'}
                                                {literal}
                                                <script type="text/javascript">
                                                    $().ready(function () {
                                                        var input_id = '{/literal}{if isset($input.id)}{$input.id}{else}{$input.name}{/if}{literal}';
                                                        $('#'+input_id).tagify({delimiters: [13,44], addTagPrompt: '{/literal}{l s='Add tag'}{literal}'});
                                                        $({/literal}'#{$table}{literal}_form').submit( function() {
                                                            $(this).find('#'+input_id).val($('#'+input_id).tagify('serialize'));
                                                        });
                                                    });
                                                </script>
                                                {/literal}
                                            {/if}
                                            {assign var='value_text' value=$fields_value[$input.name]}
                                            <input type="text"
                                                    name="{$input.name}"
                                                    id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}"
                                                    value="{if isset($input.string_format) && $input.string_format}{$value_text|string_format:$input.string_format|escape:'htmlall':'UTF-8'}{else}{$value_text|escape:'htmlall':'UTF-8'}{/if}"
                                                    class="{if $input.type == 'tags'}tagify {/if}{if isset($input.class)}{$input.class}{/if}"
                                                    {if isset($input.size)}size="{$input.size}"{/if}
                                                    {if isset($input.maxlength)}maxlength="{$input.maxlength}"{/if}
                                                    {if isset($input.class)}class="{$input.class}"{/if}
                                                    {if isset($input.readonly) && $input.readonly}readonly="readonly"{/if}
                                                    {if isset($input.disabled) && $input.disabled}disabled="disabled"{/if}
                                                    {if isset($input.autocomplete) && !$input.autocomplete}autocomplete="off"{/if} />
                                            {if isset($input.suffix)}{$input.suffix}{/if}
                                            {if !empty($input.hint)}<span class="hint" name="help_box">{$input.hint}<span class="hint-pointer">&nbsp;</span></span>{/if}
                                        {/if}
                                    {elseif $input.type == 'select'}
                                        {if isset($input.options.query) && !$input.options.query && isset($input.empty_message)}
                                            {$input.empty_message}
                                            {$input.required = false}
                                            {$input.desc = null}
                                        {else}
                                            <select name="{$input.name}" class="{if isset($input.class)}{$input.class}{/if}"
                                                    id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}"
                                                    {if isset($input.multiple)}multiple="multiple" {/if}
                                                    {if isset($input.size)}size="{$input.size}"{/if}
                                                    {if isset($input.onchange)}onchange="{$input.onchange}"{/if}>
                                                {if isset($input.options.default)}
                                                    <option value="{$input.options.default.value}">{$input.options.default.label}</option>
                                                {/if}
                                                {if isset($input.options.optiongroup)}
                                                    {foreach $input.options.optiongroup.query AS $optiongroup}
                                                        <optgroup label="{$optiongroup[$input.options.optiongroup.label]}">
                                                            {foreach $optiongroup[$input.options.options.query] as $option}
                                                                <option value="{$option[$input.options.options.id]}"
                                                                    {if isset($input.multiple)}
                                                                        {foreach $fields_value[$input.name] as $field_value}
                                                                            {if $field_value == $option[$input.options.options.id]}selected="selected"{/if}
                                                                        {/foreach}
                                                                    {else}
                                                                        {if $fields_value[$input.name] == $option[$input.options.options.id]}selected="selected"{/if}
                                                                    {/if}
                                                                >{$option[$input.options.options.name]}</option>
                                                            {/foreach}
                                                        </optgroup>
                                                    {/foreach}
                                                {else}
                                                    {foreach $input.options.query AS $option}
                                                        {if is_object($option)}
                                                            <option value="{$option->$input.options.id}"
                                                                {if isset($input.multiple)}
                                                                    {foreach $fields_value[$input.name] as $field_value}
                                                                        {if $field_value == $option->$input.options.id}
                                                                            selected="selected"
                                                                        {/if}
                                                                    {/foreach}
                                                                {else}
                                                                    {if $fields_value[$input.name] == $option->$input.options.id}
                                                                        selected="selected"
                                                                    {/if}
                                                                {/if}
                                                            >{$option->$input.options.name}</option>
                                                        {elseif $option == "-"}
                                                            <option value="">-</option>
                                                        {else}
                                                            <option value="{$option[$input.options.id]}"
                                                                {if isset($input.multiple)}
                                                                    {foreach $fields_value[$input.name] as $field_value}
                                                                        {if $field_value == $option[$input.options.id]}
                                                                            selected="selected"
                                                                        {/if}
                                                                    {/foreach}
                                                                {else}
                                                                    {if $fields_value[$input.name] == $option[$input.options.id]}
                                                                        selected="selected"
                                                                    {/if}
                                                                {/if}
                                                            >{$option[$input.options.name]}</option>

                                                        {/if}
                                                    {/foreach}
                                                {/if}
                                            </select>
                                            {if !empty($input.hint)}<span class="hint" name="help_box">{$input.hint}<span class="hint-pointer">&nbsp;</span></span>{/if}
                                        {/if}
                                    {elseif $input.type == 'radio'}
                                        {foreach $input.values as $value}
                                            <input type="radio" name="{$input.name}" id="{$value.id}" value="{$value.value|escape:'htmlall':'UTF-8'}"
                                                    {if $fields_value[$input.name] == $value.value}checked="checked"{/if}
                                                    {if isset($input.disabled) && $input.disabled}disabled="disabled"{/if} />
                                            <label {if isset($input.class)}class="{$input.class}"{/if} for="{$value.id}">
                                             {if isset($input.is_bool) && $input.is_bool == true}
                                                {if $value.value == 1}
                                                    <img src="../img/admin/enabled.gif" alt="{$value.label}" title="{$value.label}" />
                                                {else}
                                                    <img src="../img/admin/disabled.gif" alt="{$value.label}" title="{$value.label}" />
                                                {/if}
                                             {else}
                                                {$value.label}
                                             {/if}
                                            </label>
                                            {if isset($input.br) && $input.br}<br />{/if}
                                            {if isset($value.p) && $value.p}<p>{$value.p}</p>{/if}
                                        {/foreach}
                                    {elseif $input.type == 'textarea'}
                                        {if isset($input.lang) AND $input.lang}
                                            <div class="translatable">
                                                {foreach $languages as $language}
                                                    <div class="lang_{$language.id_lang}" id="{$input.name}_{$language.id_lang}" style="display:{if $language.id_lang == $defaultFormLanguage}block{else}none{/if}; float: left;">
                                                        <textarea cols="{$input.cols}" rows="{$input.rows}" name="{$input.name}_{$language.id_lang}" {if isset($input.autoload_rte) && $input.autoload_rte}class="rte autoload_rte {if isset($input.class)}{$input.class}{/if}"{/if} >{$fields_value[$input.name][$language.id_lang]|escape:'htmlall':'UTF-8'}</textarea>
                                                    </div>
                                                {/foreach}
                                            </div>
                                        {else}
                                            <textarea name="{$input.name}" id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}" cols="{$input.cols}" rows="{$input.rows}" {if isset($input.autoload_rte) && $input.autoload_rte}class="rte autoload_rte {if isset($input.class)}{$input.class}{/if}"{/if}>{$fields_value[$input.name]|escape:'htmlall':'UTF-8'}</textarea>
                                        {/if}
                                    {elseif $input.type == 'checkbox'}
                                        {foreach $input.values.query as $value}
                                            {assign var=id_checkbox value=$input.name|cat:'_'|cat:$value[$input.values.id]}
                                            <input type="checkbox"
                                                name="{$id_checkbox}"
                                                id="{$id_checkbox}"
                                                class="{if isset($input.class)}{$input.class}{/if}"
                                                {if isset($value.val)}value="{$value.val|escape:'htmlall':'UTF-8'}"{/if}
                                                {if isset($fields_value[$id_checkbox]) && $fields_value[$id_checkbox]}checked="checked"{/if} />
                                            <label for="{$id_checkbox}" class="t"><strong>{$value[$input.values.name]}</strong></label><br />
                                        {/foreach}
                                    {elseif $input.type == 'alma_switch'}
                                        {foreach $input.values.query as $value}
                                            {assign var=id_checkbox value=$input.name|cat:'_'|cat:$value[$input.values.id]}
                                            <div class="alma_switch checkbox{if isset($input.expand) && strtolower($input.expand.default) == 'show'} hidden{/if}">
                                                {strip}
                                                    <label class="t switch" for="{$id_checkbox}">
                                                        <input type="checkbox"
                                                            name="{$id_checkbox}"
                                                            id="{$id_checkbox}"
                                                            class="{if isset($input.class)}{$input.class}{/if}"
                                                            {if isset($value.val)}value="{$value.val|escape:'htmlall':'UTF-8'}"{/if}
                                                            {if isset($fields_value[$id_checkbox]) && $fields_value[$id_checkbox]}checked="checked"{/if} />
                                                        <span class="slider round"></span>
                                                    </label>
                                                    <span class="switch-name">{$value[$input.values.name]}</span>
                                                {/strip}
                                            </div>
                                        {/foreach}
                                    {elseif $input.type == 'file'}
                                        {if isset($input.display_image) && $input.display_image}
                                            {if isset($fields_value[$input.name].image) && $fields_value[$input.name].image}
                                                <div id="image">
                                                    {$fields_value[$input.name].image}
                                                    <p align="center">{l s='File size' mod='alma'} {$fields_value[$input.name].size}{l s='kb' mod='alma'}</p>
                                                    <a href="{$current}&{$identifier}={$form_id}&token={$token}&deleteImage=1">
                                                        <img src="../img/admin/delete.gif" alt="{l s='Delete' mod='alma'}" /> {l s='Delete' mod='alma'}
                                                    </a>
                                                </div><br />
                                            {/if}
                                        {/if}

                                        {if isset($input.lang) AND $input.lang}
                                            <div class="translatable">
                                                {foreach $languages as $language}
                                                    <div class="lang_{$language.id_lang}" id="{$input.name}_{$language.id_lang}" style="display:{if $language.id_lang == $defaultFormLanguage}block{else}none{/if}; float: left;">
                                                        <input type="file" name="{$input.name}_{$language.id_lang}" {if isset($input.id)}id="{$input.id}_{$language.id_lang}"{/if} />

                                                    </div>
                                                {/foreach}
                                            </div>
                                        {else}
                                            <input type="file" name="{$input.name}" {if isset($input.id)}id="{$input.id}"{/if} />
                                        {/if}
                                        {if !empty($input.hint)}<span class="hint" name="help_box">{$input.hint}<span class="hint-pointer">&nbsp;</span></span>{/if}
                                    {elseif $input.type == 'password'}
                                        <input type="password"
                                                name="{$input.name}"
                                                size="{$input.size}"
                                                class="{if isset($input.class)}{$input.class}{/if}"
                                                value=""
                                                {if isset($input.autocomplete) && !$input.autocomplete}autocomplete="off"{/if} />
                                    {elseif $input.type == 'secret'}
                                        {assign var='value_text' value=$fields_value[$input.name]}
                                        <input type="password"
                                            name="{$input.name}"
                                            size="{$input.size}"
                                            class="{if isset($input.class)}{$input.class}{/if}"
                                            value="{if !empty($value_text)}{$input.placeholder|escape:'htmlall':'UTF-8'}{/if}"
                                            {if isset($input.autocomplete) && !$input.autocomplete}autocomplete="off"{/if} />
                                    {elseif $input.type == 'birthday'}
                                        {foreach $input.options as $key => $select}
                                            <select name="{$key}" class="{if isset($input.class)}{$input.class}{/if}">
                                                <option value="">-</option>
                                                {if $key == 'months'}
                                                    {*
                                                        This comment is useful to the translator tools /!\ do not remove them
                                                        {l s='January' mod='alma'}
                                                        {l s='February' mod='alma'}
                                                        {l s='March' mod='alma'}
                                                        {l s='April' mod='alma'}
                                                        {l s='May' mod='alma'}
                                                        {l s='June' mod='alma'}
                                                        {l s='July' mod='alma'}
                                                        {l s='August' mod='alma'}
                                                        {l s='September' mod='alma'}
                                                        {l s='October' mod='alma'}
                                                        {l s='November' mod='alma'}
                                                        {l s='December' mod='alma'}
                                                    *}
                                                    {foreach $select as $k => $v}
                                                        <option value="{$k}" {if $k == $fields_value[$key]}selected="selected"{/if}>{l s=$v mod='alma'}</option>
                                                    {/foreach}
                                                {else}
                                                    {foreach $select as $v}
                                                        <option value="{$v}" {if $v == $fields_value[$key]}selected="selected"{/if}>{$v}</option>
                                                    {/foreach}
                                                {/if}

                                            </select>
                                        {/foreach}
                                    {elseif $input.type == 'group'}
                                        {assign var=groups value=$input.values}
                                        {include file='helpers/form/form_group.tpl'}
                                    {elseif $input.type == 'shop'}
                                        {$input.html}
                                    {elseif $input.type == 'categories'}
                                        {include file='helpers/form/form_category.tpl' categories=$input.values}
                                    {elseif $input.type == 'categories_select'}
                                        {$input.category_tree}
                                    {elseif $input.type == 'asso_shop' && isset($asso_shop) && $asso_shop}
                                            {$asso_shop}
                                    {elseif $input.type == 'color'}
                                        <input type="color"
                                            size="{$input.size}"
                                            data-hex="true"
                                            {if isset($input.class)}class="{$input.class}"
                                            {else}class="color mColorPickerInput"{/if}
                                            name="{$input.name}"
                                            value="{$fields_value[$input.name]|escape:'htmlall':'UTF-8'}" />
                                    {elseif $input.type == 'date'}
                                        <input type="text"
                                            size="{$input.size}"
                                            data-hex="true"
                                            {if isset($input.class)}class="{$input.class}"
                                            {else}class="datepicker"{/if}
                                            name="{$input.name}"
                                            value="{$fields_value[$input.name]|escape:'htmlall':'UTF-8'}" />
                                    {elseif $input.type == 'free'}
                                        {$fields_value[$input.name]}
                                    {elseif $input.type == 'html'}
                                        {if isset($input.html_content)}
                                            {$input.html_content}
                                        {else}
                                            {$input.name}
                                        {/if}
                                    {/if}
                                    {if isset($input.required) && $input.required && $input.type != 'radio'} <sup>*</sup>{/if}
                                    {/block}{* end block input *}
                                    {block name="description"}
                                        {if isset($input.desc) && !empty($input.desc)}
                                        <div class="clear"></div>
                                            <div class="preference_description">
                                                {if is_array($input.desc)}
                                                    {foreach $input.desc as $p}
                                                        {if is_array($p)}
                                                            <span id="{$p.id}">{$p.text}</span><br />
                                                        {else}
                                                            {$p}<br />
                                                        {/if}
                                                    {/foreach}
                                                {else}
                                                    {$input.desc}
                                                {/if}
                                            </div>
                                        {/if}
                                    {/block}
                                    {if isset($input.lang) && isset($languages)}<div class="clear"></div>{/if}
                                    <div class="clear"></div>
                                    </div>
                                {/block}{* end block field *}
                                {if $input.name == 'id_state'}
                                    </div>
                                {/if}
                            {/if}
                        </div>
                        {/foreach}
                        {hook h='displayAdminForm' fieldset=$f}
                        {if isset($name_controller)}
                            {capture name=hookName assign=hookName}display{$name_controller|ucfirst|escape:'htmlall':'UTF-8'}Form{/capture}
                            {hook h=$hookName fieldset=$f}
                        {elseif isset($smarty.get.controller)}
                            {capture name=hookName assign=hookName}display{$smarty.get.controller|ucfirst|escape:'htmlall':'UTF-8'}Form{/capture}
                            {hook h=$hookName fieldset=$f}
                        {/if}
                    </div>
                {elseif $key == 'submit'}
                    <div class="margin-form">
                        <input type="submit"
                            id="{if isset($field.id)}{$field.id}{else}{$table}_form_submit_btn{/if}"
                            value="{$field.title}"
                            name="{if isset($field.name)}{$field.name}{else}{$submit_action}{/if}{if isset($field.stay) && $field.stay}AndStay{/if}"
                            {if isset($field.class)}class="{$field.class}"{/if} />
                    </div>
                {elseif $key == 'desc'}
                    <p class="clear">
                        {if is_array($field)}
                            {foreach $field as $k => $p}
                                {if is_array($p)}
                                    <span id="{$p.id}">{$p.text}</span><br />
                                {else}
                                    {$p}
                                    {if isset($field[$k+1])}<br />{/if}
                                {/if}
                            {/foreach}
                        {else}
                            {$field}
                        {/if}
                    </p>
                {/if}
                {block name="other_input"}{/block}
            {/foreach}
            {if $required_fields}
                <div class="small"><sup>*</sup> {l s='Required field' mod='alma'}</div>
            {/if}
        </fieldset>
        {block name="other_fieldsets"}{/block}
        {if isset($fields[$f+1])}<br />{/if}
    {/foreach}
</form>
{/block}

{block name="input"}
    {if $input.type == 'number'}
        {assign var='value_text' value=$fields_value[$input.name]}
            <input type="number"
                   name="{$input.name}"
                   id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}"
                   value="{$value_text|escape:'html':'UTF-8'}"
                   class="alma {if isset($input.class)}{$input.class}{/if}"
                    {if isset($input.size)} size="{$input.size}"{/if}
                    {if isset($input.min)} min="{$input.min}"{/if}
                    {if isset($input.max)} max="{$input.max}"{/if}
                    {if isset($input.readonly) && $input.readonly} readonly="readonly"{/if}
                    {if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}
                    {if isset($input.autocomplete) && !$input.autocomplete} autocomplete="off"{/if}
                    {if isset($input.required) && $input.required } required="required" {/if}
                    {if isset($input.placeholder) && $input.placeholder } placeholder="{$input.placeholder}"{/if}
            />
    {else if $input.type == 'alma_switch'}
        {foreach $input.values.query as $value}
            {assign var=id_checkbox value=$input.name|cat:'_'|cat:$value[$input.values.id]}
            <div class="alma_switch{if isset($input.readonly) && $input.readonly} readonly{/if} checkbox{if isset($input.expand) && strtolower($input.expand.default) == 'show'} hidden{/if}">
                {strip}
                    <label class="switch" for="{$id_checkbox}">
                        {if isset($input.readonly) && $input.readonly}
                            <input type="hidden" name="{$id_checkbox}" id="{$id_checkbox}" {if isset($fields_value[$id_checkbox]) && $fields_value[$id_checkbox]} value="{$fields_value[$id_checkbox]}"{/if} />                                
                            <input type="checkbox" name="{$id_checkbox}_visible" id="{$id_checkbox}_visible" class="{if isset($input.class)}{$input.class}{/if}"{if isset($value.val)} value="{$value.val|escape:'html':'UTF-8'}"{/if}{if isset($fields_value[$id_checkbox]) && $fields_value[$id_checkbox]} checked="checked"{/if} disabled="disabled" />
                        {else}
                            <input type="checkbox" name="{$id_checkbox}" id="{$id_checkbox}" class="{if isset($input.class)}{$input.class}{/if}"{if isset($value.val)} value="{$value.val|escape:'html':'UTF-8'}"{/if}{if isset($fields_value[$id_checkbox]) && $fields_value[$id_checkbox]} checked="checked"{/if} />
                        {/if}
                        <span class="slider round"></span>
                    </label>
                    <span class="switch-name">{$value[$input.values.name]}</span>
                {/strip}
            </div>
        {/foreach}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}


