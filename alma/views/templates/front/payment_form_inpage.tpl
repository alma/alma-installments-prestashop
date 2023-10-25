<form id="alma-inpage-plan-{$keyPlan|escape:'htmlall':'UTF-8'}" class="alma-inpage"
      data-action="{$action}"
      data-apimode="{$apiMode|escape:'htmlall':'UTF-8'}"
      data-merchantid="{$merchantId|escape:'htmlall':'UTF-8'}"
      data-isinpageenabled="{$isInPageEnabled|escape:'htmlall':'UTF-8'}"
      data-installment="{$installment|escape:'htmlall':'UTF-8'}"
      data-purchaseamount="{$creditInfo.totalCart|escape:'htmlall':'UTF-8'}"
      data-locale="{$locale|escape:'htmlall':'UTF-8'}">
    <div id="alma-inpage-iframe-plan-{$keyPlan|escape:'htmlall':'UTF-8'}" class="alma-inpage-iframe"></div>
</form>