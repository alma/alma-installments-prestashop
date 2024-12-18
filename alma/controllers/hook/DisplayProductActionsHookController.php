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

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Builders\Admin\InsuranceHelperBuilder as AdminInsuranceHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\CartHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\InsuranceHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\PriceHelperBuilder;
use Alma\PrestaShop\Helpers\Admin\AdminInsuranceHelper;
use Alma\PrestaShop\Helpers\CartHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;

class DisplayProductActionsHookController extends FrontendHookController
{
    /** @var Alma */
    protected $module;

    /**
     * @var InsuranceHelper
     */
    protected $insuranceHelper;
    /**
     * @var AdminInsuranceHelper
     */
    protected $adminInsuranceHelper;
    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @var CartHelper
     */
    protected $cartHelper;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @param $module
     */
    public function __construct($module)
    {
        parent::__construct($module);

        $insuranceHelperBuilder = new InsuranceHelperBuilder();
        $this->insuranceHelper = $insuranceHelperBuilder->getInstance();

        $adminInsuranceHelperBuilder = new AdminInsuranceHelperBuilder();
        $this->adminInsuranceHelper = $adminInsuranceHelperBuilder->getInstance();

        $this->productHelper = new ProductHelper();

        $priceHelperBuilder = new PriceHelperBuilder();
        $this->priceHelper = $priceHelperBuilder->getInstance();

        $cartHelperBuilder = new CartHelperBuilder();
        $this->cartHelper = $cartHelperBuilder->getInstance();
    }

    /**
     * @return bool
     */
    public function canRun()
    {
        return parent::canRun()
            && \Tools::strtolower($this->currentControllerName()) == 'product'
            && $this->insuranceHelper->isInsuranceAllowedInProductPage();
    }

    /**
     * @param $params
     *
     * @return mixed
     *
     * @throws \PrestaShopException
     */
    public function run($params)
    {
        $productParams = isset($params['product']) ? $params['product'] : [];

        $productId = isset($productParams['id_product'])
            ? $productParams['id_product']
            : \Tools::getValue('id_product');

        $productAttributeId = isset($productParams['id_product_attribute'])
            ? $productParams['id_product_attribute']
            : null;

        $quantityWanted = isset($productParams['quantity_wanted']) ? $productParams['quantity_wanted'] : 1;

        $cmsReference = $this->insuranceHelper->createCmsReference($productId, $productAttributeId);

        $staticPrice = $this->productHelper->getPriceStatic($productId, $productAttributeId);
        $staticPriceInCents = $this->priceHelper->convertPriceToCents($staticPrice);
        $productName = isset($productParams['name']) ? $productParams['name'] : '';

        $merchantId = SettingsHelper::getMerchantId();

        $this->context->smarty->assign([
            'productDetails' => $this->handleProductDetails($params),
            'settingsInsurance' => $this->handleSettings($merchantId),
            'iframeUrl' => sprintf(
                '%s%s?cms_reference=%s&product_price=%s&product_quantity=%s&product_name=%s&merchant_id=%s&customer_session_id=%s&cart_id=%s',
                $this->adminInsuranceHelper->envUrl(),
                ConstantsHelper::FO_IFRAME_WIDGET_INSURANCE_PATH,
                $cmsReference,
                $staticPriceInCents,
                $quantityWanted,
                $productName,
                $merchantId,
                $this->context->cookie->checksum,
                $this->cartHelper->getCartIdFromContext()
            ),
        ]);

        return $this->module->display($this->module->file, 'displayProductActions.tpl');
    }

    /**
     * @return false|string
     *
     * @throws \PrestaShopException
     */
    protected function handleSettings($merchantId)
    {
        $settings = $this->adminInsuranceHelper->mapDbFieldsWithIframeParams();
        $settings['merchant_id'] = $merchantId;
        $settings['cart_id'] = $this->cartHelper->getCartIdFromContext();
        $settings['session_id'] = $this->context->cookie->checksum;

        return json_encode($settings);
    }

    /**
     * @param $params
     *
     * @return false|string
     */
    protected function handleProductDetails($params)
    {
        $productDetails = [];

        if (isset($params['product'])) {
            $productDetails = $params['product'];
        }

        return json_encode($productDetails);
    }
}
