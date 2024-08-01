<?php
/**
 * 2018-2024 Alma SAS.
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
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Controllers\Hook;

use Alma\PrestaShop\Builders\Admin\InsuranceHelperBuilder as AdminInsuranceHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\CartHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\InsuranceHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\PriceHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\SettingsHelperBuilder;
use Alma\PrestaShop\Builders\Services\InsuranceProductServiceBuilder;
use Alma\PrestaShop\Exceptions\AlmaCartItemFactoryException;
use Alma\PrestaShop\Exceptions\InsuranceNotFoundException;
use Alma\PrestaShop\Factories\AlmaCartItemFactory;
use Alma\PrestaShop\Helpers\Admin\AdminInsuranceHelper;
use Alma\PrestaShop\Helpers\CartHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\ImageHelper;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\ProductRepository;
use Alma\PrestaShop\Services\InsuranceProductService;
use PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException;

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
    /**
     * @var InsuranceProductService
     */
    protected $insuranceProductService;
    /**
     * @var AlmaCartItemFactory
     */
    protected $almaCartItemFactory;
    /**
     * @var Logger
     */
    protected $logger;

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

        $cartHelperBuilder = new CartHelperBuilder();
        $this->cartHelper = $cartHelperBuilder->getInstance();

        $this->almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
        $this->imageHelper = new ImageHelper();
        $priceHelperBuilder = new PriceHelperBuilder();
        $this->priceHelper = $priceHelperBuilder->getInstance();

        $this->link = new \Link();

        $settingHelperBuilder = new SettingsHelperBuilder();
        $this->settingHelper = $settingHelperBuilder->getInstance();

        $insuranceProductServiceBuilder = new InsuranceProductServiceBuilder();
        $this->insuranceProductService = $insuranceProductServiceBuilder->getInstance();

        $this->almaCartItemFactory = new AlmaCartItemFactory();

        $this->logger = Logger::instance();

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
     * @throws LocalizationException
     */
    public function run($params)
    {
        try {
            $product = $this->almaCartItemFactory->create($params['product']);
        } catch (AlmaCartItemFactoryException $e) {
            $msg = 'Cannot display insurance cart item';
            $this->logger->error('[Alma] ' . $msg);

            $this->context->smarty->assign([
                'message' => $msg,
            ]);

            return $this->module->display($this->module->file, 'notificationError.tpl');
        }

        $insuranceProductId = $this->productRepository->getProductIdByReference(
            ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE,
            $this->context->language->id
        );

        if (!$insuranceProductId) {
            $msg = 'Insurance product not found';
            $this->logger->error('[Alma] ' . $msg);

            $this->context->smarty->assign([
                'message' => $msg,
            ]);

            return $this->module->display($this->module->file, 'notificationError.tpl');
        }

        $resultInsurance = [];
        $idProduct = $product->getId();
        $productAttributeId = $product->getIdProductAttribute();
        $staticPrice = $this->productHelper->getPriceStatic($idProduct, $productAttributeId);
        /**
         * @var \CartCore $cart
         */
        $cart = $params['cart'];

        if ($idProduct !== $insuranceProductId) {
            $resultInsurance = $this->insuranceProductService->getItemsCartInsuranceProductAttributes($product, $cart->id, $insuranceProductId);
        }

        $nbProductWithInsurance = 0;
        foreach ($resultInsurance as $insurance) {
            $nbProductWithInsurance += $insurance['quantity'];
        }

        $this->context->smarty->assign([
                'idCart' => $cart->id,
                'idLanguage' => $this->context->language->id,
                'nbProductWithoutInsurance' => $product->getQuantity() - $nbProductWithInsurance,
                'nbProductWithInsurance' => $nbProductWithInsurance,
                'product' => $product,
                'associatedInsurances' => $resultInsurance,
                'isAlmaInsurance' => $idProduct === $insuranceProductId ? 1 : 0,
                'ajaxLinkAlmaRemoveProduct' => $this->link->getModuleLink('alma', 'insurance', ['action' => 'removeProductFromCart']),
                'ajaxLinkAlmaRemoveAssociation' => $this->link->getModuleLink('alma', 'insurance', ['action' => 'removeAssociation']),
                'ajaxLinkAlmaRemoveAssociations' => $this->link->getModuleLink('alma', 'insurance', ['action' => 'removeAssociations']),
                'ajaxLinkRemoveInsuranceProduct' => $this->link->getModuleLink('alma', 'insurance', ['action' => 'removeInsuranceProduct']),
                'ajaxLinkRemoveInsuranceProducts' => $this->link->getModuleLink('alma', 'insurance', ['action' => 'removeInsuranceProducts']),
                'ajaxLinkAddInsuranceProduct' => $this->link->getModuleLink('alma', 'insurance', ['action' => 'addInsuranceProduct']),
                'token' => \Tools::getToken(false),
                'idProduct' => $idProduct,
                'iframeUrl' => sprintf(
                    '%s%s?cms_reference=%s&product_price=%s&product_quantity=%s&merchant_id=%s&customer_session_id=%s&cart_id=%s&is_in_cart=true',
                    $this->adminInsuranceHelper->envUrl(),
                    ConstantsHelper::FO_IFRAME_WIDGET_INSURANCE_PATH,
                    $this->insuranceHelper->createCmsReference($idProduct, $productAttributeId),
                    $this->priceHelper->convertPriceToCents($staticPrice),
                    $product->getQuantity(),
                    $this->settingHelper->getIdMerchant(),
                    $this->context->cookie->checksum,
                    $this->cartHelper->getCartIdFromContext()
                ),
                'insuranceSettings' => $this->adminInsuranceHelper->mapDbFieldsWithIframeParams(),
            ]);

        return $this->module->display($this->module->file, 'displayCartExtraProductActions.tpl');
    }
}
