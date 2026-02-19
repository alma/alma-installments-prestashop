{if displayPsAccounts}
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
{if isPsAccountsLinked}
    {$form}
{/if}
