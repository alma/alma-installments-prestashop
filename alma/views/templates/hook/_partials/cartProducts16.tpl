{*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<tr>
    <td class="">
        <a href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute, false, false, true)|escape:'html':'UTF-8'}">
            <img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'small_default')|escape:'html':'UTF-8'}" alt="{$product.name|escape:'html':'UTF-8'}" {if isset($smallSize)}width="{$smallSize.width/2}" height="{$smallSize.height/2}" {/if} /></a>
    </td>
    <td class="">
        <p class="">
            <b>
                <a href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute, false, false, true)|escape:'html':'UTF-8'}">{$product.name|escape:'html':'UTF-8'}</a></p>
        </b>
        <ul class="price text-right" >
            {if !empty($product.gift)}
                <li class="gift-icon">{l s='Gift!'}</li>
            {else}
                {if !$priceDisplay}
                    <li class="price">{convertPrice price=$product.price_wt}</li>
                {else}
                    <li class="price">{convertPrice price=$product.price}</li>
                {/if}

            {/if}
        </ul>
    </td>

    {if $hasInsurance == '1'}
        <td>
            <img src="/modules/alma/views/img/add-circle.svg">
        </td>
        <td class="">
            <img src="/modules/alma/views/img/alma-insurance.jpg" {if isset($smallSize)}width="{$smallSize.width/1.5}" height="{$smallSize.height/1.5}" {/if} />
        </td>
        <td class="">
            <p class="">
                <b>
                {$associatedInsurance.insuranceProduct->getFieldByLang('name', $idLanguage)|escape:'htmlall':'UTF-8'}
                </b>
                <br>
                {$associatedInsurance.insuranceProductAttribute->reference|escape:'htmlall':'UTF-8'}

            </p>
            <ul class="price text-right">

            <li class="price">{$associatedInsurance.price|number_format} €</li>

            </ul>
        </td>
        <td>
            <a data-alma-association-id="{$idAlmaInsuranceProduct}"
               data-action="remove-product-with-insurance"
               data-token='{$token|escape:'htmlall':'UTF-8'}'
               rel="nofollow"

               class="alma-remove-association"
               data-link='{$ajaxLinkAlmaRemoveAssociation|escape:'htmlall':'UTF-8'}'
            >
                <i class="icon-trash"></i>
            </a>
        </td>
    {else}
        <td></td>
        <td></td>
        <td></td>
        <td>
            <a data-product-id="{$product.id_product|escape:'javascript'}"
                           data-product-attribute-id="{$product.id_product_attribute|escape:'javascript'}"
                           data-product-customization-id="{$product.id_customization|escape:'javascript'}"
                           data-action="remove-product-without-insurance"
                           data-token='{$token|escape:'htmlall':'UTF-8'}'
                           data-link='{$ajaxLinkAlmaRemoveProduct|escape:'htmlall':'UTF-8'}'
                           class="alma-remove-product"
            >
                <i class="icon-trash"></i>
            </a></td>
    {/if}

</tr>
