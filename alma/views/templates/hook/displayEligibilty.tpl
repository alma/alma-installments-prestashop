<div class="alma-product-eligibility-block">
    {if $isEligible}
        <div class="alma-text-left">
            <div class="alma-text-big alma-text-black alma-margin-bottom">{l s='Pay in %s installments' sprintf=array($installments) mod='alma'}</div>
            {foreach from=$plans item=plan}
                <div class="alma-text-medium alma-text-black">
                    <span class="alma-plan-circle">{$plan['installments']}x</span>
                    <span class="alma-plan-text">{$plan['message']}</span>
                </div>
            {/foreach}
        </div>
        <div class="alma-text-left alma-margin-top">
            <span class="alma-logo">
                <img src="{$logo|escape:'htmlall':'UTF-8'}" alt="Alma" />
            </span>
            <span>
                <a data-toggle="modal" href="#alma-modal">{l s='How it works' mod='alma'}</a>
            </span>
        </div>
    {else}
        <div class="alma-text-center alma-text-medium alma-text-black">{l s='Pay in installments for any purchase between %s € and %s €' sprintf=array($purchaseMin, $purchaseMax) mod='alma'}</div>
        <div class="alma-text-center">
            <span class="alma-logo">
                <img src="{$logo|escape:'htmlall':'UTF-8'}" alt="Alma" />
            </span>
            <span>
                <a data-toggle="modal" href="#alma-modal">{l s='How it works' mod='alma'}</a>
            </span>
        </div>
    {/if}
</div>

<div class="modal fade" id="alma-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <i class="material-icons">&#215;</i>
                </button>
                <h4 class="modal-title">
                    <span class="alma-logo">
                        <img src="{$logo|escape:'htmlall':'UTF-8'}" alt="Alma" />
                    </span>
                </h4>
            </div>
            <div class="modal-body">
                Lorem Ipsum
            </div>
      </div>
  </div>
</div>

<script type="text/javascript">
    (function($) {
        $(function() {
            $('#alma-modal').appendTo('body');
        });
    })(jQuery);
</script>
<!--
Payer en %s fois
Payer en plusieurs fois pour tout achat entre %s  et %s
Comment ça marche ?
-->
