<?php
/**
 * 2018-2023 Alma SAS.
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
 */

namespace Alma\PrestaShop\Controllers\Hook;

use Alma\PrestaShop\Builders\Admin\InsuranceHelperBuilder as AdminInsuranceHelperBuilder;
use Alma\PrestaShop\Builders\CartHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\PriceHelperBuilder;
use Alma\PrestaShop\Builders\InsuranceHelperBuilder;
use Alma\PrestaShop\Builders\SettingsHelperBuilder;
use Alma\PrestaShop\Exceptions\InsuranceNotFoundException;
use Alma\PrestaShop\Helpers\Admin\InsuranceHelper as AdminInsuranceHelper;
use Alma\PrestaShop\Helpers\CartHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\ImageHelper;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\ProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class DisplayCartExtraProductActionsHookController extends FrontendHookController
{
    /** @var Alma */
    protected $module;

    /**
     * @var InsuranceHelper
     */
    protected $insuranceHelper;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var AlmaInsuranceProductRepository
     */
    protected $almaInsuranceProductRepository;
    /**
     * @var ImageHelper
     */
    protected $imageHelper;
    /**
     * @var \Link
     */
    protected $link;
    /**
     * @var AdminInsuranceHelper
     */
    protected $adminInsuranceHelper;
    /**
     * @var ProductHelper
     */
    protected $productHelper;
    /**
     * @var PriceHelper
     */
    protected $priceHelper;
    /**
     * @var CartHelper
     */
    protected $cartHelper;
    /**
     * @var SettingsHelper
     */
    protected $settingHelper;

    /*
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @param $module
     */
    public function __construct($module)
    {
        $insuranceHelperBuilder = new InsuranceHelperBuilder();
        $this->insuranceHelper = $insuranceHelperBuilder->getInstance();

        $adminInsuranceHelperBuilder = new AdminInsuranceHelperBuilder();
        $this->adminInsuranceHelper = $adminInsuranceHelperBuilder->getInstance();

        $this->productRepository = new ProductRepository();
        $this->productHelper = new ProductHelper();

        $priceHelperBuilder = new PriceHelperBuilder();
        $this->priceHelper = $priceHelperBuilder->getInstance();
        $cartHelperBuilder = new CartHelperBuilder();
        $this->cartHelper = $cartHelperBuilder->getInstance();

        $this->almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
        $this->imageHelper = new ImageHelper();
        $priceHelperBuilder = new PriceHelperBuilder();
        $this->priceHelper = $priceHelperBuilder->getInstance();

        $this->link = new \Link();

        $settingHelperBuilder = new SettingsHelperBuilder();
        $this->settingHelper = $settingHelperBuilder->getInstance();

        parent::__construct($module);
    }

    /**
     * @return bool
     */
    public function canRun()
    {
        return parent::canRun()
            && $this->insuranceHelper->isInsuranceAllowedInProductPage();
    }

    /**
     * @param $params
     *
     * @return mixed
     *
     * @throws InsuranceNotFoundException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function run($params)
    {
        /**
         * @var \ProductCore $product
         */
        $product = $params['product'];

        /**
         * @var \CartCore $cart
         */
        $cart = $params['cart'];

        $insuranceProductId = $this->productRepository->getProductIdByReference(
            ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE,
            $this->context->language->id
        );

        if (!$insuranceProductId) {
            throw new InsuranceNotFoundException();
        }

        $resultInsurance = [];

        $idProduct = $product->id;
        $productAttributeId = $product->id_product_attribute;
        $productQuantity = $product->quantity;
        $template = 'displayCartExtraProductActions.tpl';
        $cmsReference = $this->insuranceHelper->createCmsReference($idProduct, $productAttributeId);
        $regularPrice = $this->productHelper->getRegularPrice($idProduct, $productAttributeId);
        $regularPriceInCents = $this->priceHelper->convertPriceToCents($regularPrice);
        $merchantId = $this->settingHelper->getIdMerchant();

        if ($idProduct !== $insuranceProductId) {
            $almaInsurances = $this->almaInsuranceProductRepository->getIdsByCartIdAndShopAndProduct(
                $product,
                $cart->id,
                $this->context->shop->id
            );

            foreach ($almaInsurances as $almaInsurance) {
                $almaInsuranceProduct = new \ProductCore((int) $almaInsurance['id_product_insurance']);
                $almaProductAttribute = new \CombinationCore((int) $almaInsurance['id_product_attribute_insurance']);
                $idImage = $almaInsuranceProduct->getImages($this->context->language->id)[0]['id_image'];
                $linkRewrite = $almaInsuranceProduct->link_rewrite[$this->context->language->id];
                $resultInsurance[$almaInsurance['id_alma_insurance_product']] = [
                    'insuranceProduct' => $almaInsuranceProduct,
                    'insuranceProductAttribute' => $almaProductAttribute,
                    'price' => $this->priceHelper->convertPriceFromCents($almaInsurance['price']),
                    'name' => $almaInsuranceProduct->name[$this->context->language->id],
                    'urlImage' => '//' . $this->link->getImageLink(
                        $linkRewrite,
                        $idImage,
                        $this->imageHelper->getFormattedImageTypeName('cart')
                    ),
                ];
            }
        }

        $ajaxLinkRemoveProduct = $this->link->getModuleLink('alma', 'insurance', ['action' => 'removeProductFromCart']);
        $ajaxLinkRemoveAssociation = $this->link->getModuleLink('alma', 'insurance', ['action' => 'removeAssociation']);
        $ajaxLinkRemoveInsuranceProduct = $this->link->getModuleLink('alma', 'insurance', ['action' => 'removeInsuranceProduct']);
        $ajaxLinkAddInsuranceProduct = $this->link->getModuleLink('alma', 'insurance', ['action' => 'addInsuranceProduct']);

        $this->context->smarty->assign([
                'idCart' => $cart->id,
                'idLanguage' => $this->context->language->id,
                'nbProductWithoutInsurance' => $productQuantity - count($resultInsurance),
                'product' => $product,
                'associatedInsurances' => $resultInsurance,
                'isAlmaInsurance' => $idProduct === $insuranceProductId ? 1 : 0,
                'ajaxLinkAlmaRemoveProduct' => $ajaxLinkRemoveProduct,
                'ajaxLinkAlmaRemoveAssociation' => $ajaxLinkRemoveAssociation,
                'ajaxLinkRemoveInsuranceProduct' => $ajaxLinkRemoveInsuranceProduct,
                'ajaxLinkAddInsuranceProduct' => $ajaxLinkAddInsuranceProduct,
                'token' => \Tools::getToken(false),
                'idProduct' => $idProduct,
                'iframeUrl' => sprintf(
                    '%s%s?cms_reference=%s&product_price=%s&merchant_id=%s&customer_session_id=%s&cart_id=%s&is_in_cart=true',
                    $this->adminInsuranceHelper->envUrl(),
                    ConstantsHelper::FO_IFRAME_WIDGET_INSURANCE_PATH,
                    $cmsReference,
                    $regularPriceInCents,
                    $merchantId,
                    $this->context->session->getId(),
                    $this->cartHelper->getCartIdFromContext()
                ),
                'insuranceSettings' => $this->adminInsuranceHelper->mapDbFieldsWithIframeParams(),
            ]);

        return $this->module->display($this->module->file, $template);
    }
}
