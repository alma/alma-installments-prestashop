<div class="alma-data-product"
     data-reference="{$product->reference}"
     data-id-product="{$product->id}"
     data-id-cart="{$idCart}"
     data-is-alma-insurance="{$isAlmaInsurance}"
     data-no-insurance-associated="{$associatedInsurances|count}"
>

    <div class="actions-alma-insurance-product container" style="display:none">
        {foreach from=$associatedInsurances item=$associatedInsurance}
            <div class="row py-1">
                <div class="col-md-11">
                    <div class="product-line-grid-left col-md-2 col-xs-6">
                      <span class="product-image media-middle" style="height:50px;">
                          {if $product.default_image}
                              <img src="{$product.default_image.bySize.cart_default.url}"
                                   alt="{$product.name|escape:'quotes'}" loading="lazy">

{else}

                              <img src="{$urls.no_picture_image.bySize.cart_default.url}" loading="lazy"/>
                          {/if}
                        </span>
                    </div>
                    <div class="product-line-grid-left col-md-5 col-xs-12">
                        <div class="product-line-info">
                            <a class="label"  data-id_customization="{$product.id_customization|intval}">{$product.name}</a>
                        </div>
                        <div class="product-line-info product-price h5 {if $product.has_discount}has-discount{/if}">
                            {if $product.has_discount}
                                <div class="product-discount">
                                    <span class="regular-price">{$product.regular_price}</span>
                                    {if $product.discount_type === 'percentage'}
                                        <span class="discount discount-percentage">
                    -{$product.discount_percentage_absolute}
                  </span>
                                    {else}
                                        <span class="discount discount-amount">
                    -{$product.discount_to_display}
                  </span>
                                    {/if}
                                </div>
                            {/if}
                            <div class="current-price">
                                <span class="price">{$product.price}</span>
                                {if $product.unit_price_full}
                                    <div class="unit-price-cart">{$product.unit_price_full}</div>
                                {/if}
                            </div>
                            {foreach from=$product.attributes key="attribute" item="value"}
                                <div class="product-line-info {$attribute|lower}">
                                    <span class="label">{$attribute}:</span>
                                    <span class="value">{$value}</span>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                    <div class="product-line-grid-left col-md-1 col-xs-12">
                        +
                    </div>
                    <div class="product-line-grid-left col-md-4 col-xs-12">
                        {$associatedInsurance.insuranceProduct->getFieldByLang('name', $idLanguage)|escape:'htmlall':'UTF-8'}
                        <br/>
                        {$associatedInsurance.insuranceProductAttribute->getFieldByLang('name', $idLanguage)|escape:'htmlall':'UTF-8'}
                        :
                        <strong>{$associatedInsurance.price|number_format} €</strong>
                    </div>





                </div>
                <div class="col-md-1 text-xs-right">
                    <i class="material-icons float-xs-left">delete</i>
                </div>
            </div>
        {/foreach}
        {if $associatedInsurances|count !== 0}
            {for $var=1 to $nbProductWithoutInsurance  }
                <div class="row py-1">
                    <div class="col-md-11">
                        <div class="product-line-grid-left col-md-2 col-xs-6">
                      <span class="product-image media-middle" style="height:50px;">
                          {if $product.default_image}
                              <img src="{$product.default_image.bySize.cart_default.url}"
                                   alt="{$product.name|escape:'quotes'}" loading="lazy">

{else}

                              <img src="{$urls.no_picture_image.bySize.cart_default.url}" loading="lazy"/>
                          {/if}
                        </span>
                        </div>
                        <div class="product-line-grid-left col-md-5 col-xs-12">
                            <div class="product-line-info">
                                <a class="label"  data-id_customization="{$product.id_customization|intval}">{$product.name}</a>
                            </div>
                            <div class="product-line-info product-price h5 {if $product.has_discount}has-discount{/if}">
                                {if $product.has_discount}
                                    <div class="product-discount">
                                        <span class="regular-price">{$product.regular_price}</span>
                                        {if $product.discount_type === 'percentage'}
                                            <span class="discount discount-percentage">
                    -{$product.discount_percentage_absolute}
                  </span>
                                        {else}
                                            <span class="discount discount-amount">
                    -{$product.discount_to_display}
                  </span>
                                        {/if}
                                    </div>
                                {/if}
                                <div class="current-price">
                                    <span class="price">{$product.price}</span>
                                    {if $product.unit_price_full}
                                        <div class="unit-price-cart">{$product.unit_price_full}</div>
                                    {/if}
                                </div>
                                {foreach from=$product.attributes key="attribute" item="value"}
                                    <div class="product-line-info {$attribute|lower}">
                                        <span class="label">{$attribute}:</span>
                                        <span class="value">{$value}</span>
                                    </div>
                                {/foreach}
                            </div>
                        </div>


                    </div>
                    <div class="col-md-1 text-xs-right">
                        <i class="material-icons float-xs-left">delete</i>
                    </div>
                </div>
            {/for}
        {/if}
    </div>

</div>