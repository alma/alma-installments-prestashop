{if $error_messages|@count > 0}
    <div class="{$validation_error_classes|escape:'htmlall':'UTF-8'}">
        <ul>
        {foreach from=$error_messages item=error_message}
            <li>{$error_message}</li>
        {/foreach}
        </ul>
    </div>
{/if}
{if $success}
    <div class="{$success_classes|escape:'htmlall':'UTF-8'}">
        {l s='Settings successfully updated' mod='alma'}
    </div>
{/if}
