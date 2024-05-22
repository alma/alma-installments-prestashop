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

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Helpers\Admin\InsuranceHelper as AdminInsuranceHelper;
use Alma\PrestaShop\Helpers\CarrierHelper;
use Alma\PrestaShop\Helpers\CartHelper;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\CurrencyHelper;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\OrderStateHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ShopHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Model\CarrierData;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Repositories\OrderRepository;
use Alma\PrestaShop\Repositories\ProductRepository;

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
        $this->insuranceHelper = new InsuranceHelper();
        $this->adminInsuranceHelper = new AdminInsuranceHelper($module);
        $this->productHelper = new ProductHelper();
        $toolsHelper = new ToolsHelper();
        $this->priceHelper = new PriceHelper($toolsHelper, new CurrencyHelper());

        $this->cartHelper = new CartHelper(
            $this->context,
            new ToolsHelper(),
            $this->priceHelper,
            new CartData(
                $this->productHelper,
                new SettingsHelper(new ShopHelper(), new ConfigurationHelper()),
                $this->priceHelper,
                new ProductRepository()
            ),
            new OrderRepository(),
            new OrderStateHelper($this->context),
            new CarrierHelper($this->context, new CarrierData())
        );

        parent::__construct($module);
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

        $cmsReference = $this->insuranceHelper->createCmsReference($productId, $productAttributeId);

        $staticPrice = $this->productHelper->getPriceStatic($productId, $productAttributeId);
        $staticPriceInCents = $this->priceHelper->convertPriceToCents($staticPrice);

        $merchantId = SettingsHelper::getMerchantId();
        $settings = $this->handleSettings($merchantId);

        $this->context->smarty->assign([
            'settingsInsurance' => $settings,
            'iframeUrl' => sprintf(
                '%s%s?cms_reference=%s&product_price=%s&merchant_id=%s&customer_session_id=%s&cart_id=%s',
                $this->adminInsuranceHelper->envUrl(),
                ConstantsHelper::FO_IFRAME_WIDGET_INSURANCE_PATH,
                $cmsReference,
                $staticPriceInCents,
                $merchantId,
                $this->context->session->getId(),
                $this->cartHelper->getCartIdFromContext()
            ),
            'scriptModalUrl' => sprintf(
                '%s%s',
                $this->adminInsuranceHelper->envUrl(),
                ConstantsHelper::SCRIPT_MODAL_WIDGET_INSURANCE_PATH
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
        $settings['session_id'] = $this->context->session->getId();

        return json_encode($settings);
    }
}
