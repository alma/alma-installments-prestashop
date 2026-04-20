{if $displayPsAccounts}
    {if !$isPsAccountsLinked}
        {include file="./_partials/notification_ps_account.tpl"}
    {/if}

    <prestashop-accounts></prestashop-accounts>
    <script src="{$urlAccountsCdn}" rel=preload></script>

    <script>
        /*********************
         * PrestaShop Account *
         * *******************/
        window?.psaccountsVue?.init();
    </script>
{/if}
{$notifications}
{if !$displayPsAccounts || $isPsAccountsLinked || $isConfiguredModule}
    {if !$isConfiguredModule}
        {include file="./_partials/notification_first_installation.tpl"}
    {/if}

    {$form}
{/if}
