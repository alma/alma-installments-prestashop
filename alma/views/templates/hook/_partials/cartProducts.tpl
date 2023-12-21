<div class="row py-1">
    <div class="col-md-11 row">

        <div class="product-line-grid-left col-md-3 col-xs-6">
            <span class="product-image media-middle" style="height:50px;">
                {if $product.default_image}
                    <img src="{$product.default_image.bySize.cart_default.url}" alt="{$product.name|escape:'quotes'}" loading="lazy">
                {else}
                    <img src="{$urls.no_picture_image.bySize.cart_default.url}" loading="lazy"/>
                {/if}
              </span>
        </div>

        <div class="product-line-grid-left col-md-3 col-xs-12">
            <div class="product-line-info">
                {$product.name}
            </div>

            <div class="product-line-info product-price h5">
                <div class="current-price">
                    <span class="price">{$product.price}</span>
                    {if $product.unit_price_full}
                        <div class="unit-price-cart">{$product.unit_price_full}</div>
                    {/if}
                </div>
            </div>

        </div>

        {if $hasInsurance == '1'}
            <div class="col-md-1 col-xs-12">
                 <span class="media-middle">
                    <img src="/modules/alma/views/img/add-circle.svg">
                 </span>
            </div>
            <div class="product-line-grid-left col-md-2 col-xs-6">
                <span class="product-image media-middle" style="height:50px;">
                    <img src="{$associatedInsurances[$idAlmaInsuranceProduct]['urlImage']}" alt="{$associatedInsurances[$idAlmaInsuranceProduct]['name']}" loading="lazy">
                </span>
            </div>
            <div class="product-line-grid-left col-md-3 col-xs-12">
                <div class="product-line-info">
                {$associatedInsurance.insuranceProduct->getFieldByLang('name', $idLanguage)|escape:'htmlall':'UTF-8'}
                </div>
                <div class="product-line-info">
                    <strong>
                        {$associatedInsurance.insuranceProductAttribute->reference|escape:'htmlall':'UTF-8'}
                    </strong>
                </div>
                <div class="product-line-info product-price h5">
                    <div class="current-price">
                        <span class="price">{Context::getContext()->currentLocale->formatPrice($associatedInsurance.price, $currency.iso_code)}</span>
                    </div>
                </div>
            </div>
        {/if}

    </div>

    {if $hasInsurance == '1'}
        <div class="col-md-1 text-xs-right">
            <a data-alma-association-id="{$idAlmaInsuranceProduct}"
               data-action="remove-product-with-insurance"
               data-token='{\Tools::getToken(false)|escape:'htmlall':'UTF-8'}'
               href="#"
               class="alma-remove-association"
               data-link='{$ajaxLinkAlmaRemoveAssociation|escape:'htmlall':'UTF-8'}'
            >
                <i class="material-icons float-xs-left">delete</i>
            </a>
        </div>
    {else}
        <div class="col-md-1 text-xs-right">
            <a data-product-id="{$product.id_product|escape:'javascript'}"
               data-product-attribute-id="{$product.id_product_attribute|escape:'javascript'}"
               data-product-customization-id="{$product.id_customization|escape:'javascript'}"
               data-action="remove-product-without-insurance"
               data-token='{\Tools::getToken(false)|escape:'htmlall':'UTF-8'}'
               data-link='{$ajaxLinkAlmaRemoveProduct|escape:'htmlall':'UTF-8'}'
               href="#"
               class="alma-remove-product"
            >
                <i class="material-icons float-xs-left">delete</i>
            </a>
        </div>
    {/if}
</div>