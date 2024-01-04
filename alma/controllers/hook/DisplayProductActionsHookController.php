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
     * @param $module
     */
    public function __construct($module)
    {
        $this->insuranceHelper = new InsuranceHelper();
        $this->adminInsuranceHelper = new AdminInsuranceHelper($module);
        $this->productHelper = new ProductHelper();
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
     * @throws \PrestaShopException
     */
    public function run($params)
    {
        /**
         * @var \Product $product
         */
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $product = $params['product'];

            $productId = $product->id;

            $productAttributeId = property_exists($product, 'id_product_attribute') ? $product->id_product_attribute : null;
        } else {
            $productParams = isset($params['product']) ? $params['product'] : [];

            $productId = isset($productParams['id_product'])
                ? $productParams['id_product']
                : \Tools::getValue('id_product');

            $productAttributeId = isset($productParams['id_product_attribute'])
                ? $productParams['id_product_attribute']
                : null;
        }
        
        $cmsReference = $productId . '-' . $productAttributeId;

        $regularPrice = $this->productHelper->getRegularPrice($productId, $productAttributeId);
        $regularPriceInCents = PriceHelper::convertPriceToCents($regularPrice);

        $merchantId = SettingsHelper::getMerchantId();
        $addToCartLink = '';
        $oldPSVersion = false;
        $settings = $this->handleSettings($merchantId);

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $link = new \Link;
            $ajaxAddToCart = $link->getModuleLink('alma', 'insurance', ["action" => "addToCartPS16"]);
            $addToCartLink = ' data-link16="' . $ajaxAddToCart . '" data-token="'.\Tools::getToken(false).'" ';
            $oldPSVersion = true;
        }

        $this->context->smarty->assign([
            'addToCartLink' => $addToCartLink,
            'oldPSVersion' => $oldPSVersion,
            'settingsInsurance' => $settings,
            'iframeUrl' => $this->adminInsuranceHelper->envUrl() . ConstantsHelper::FO_IFRAME_WIDGET_INSURANCE_PATH . '?cms_reference=' . $cmsReference . '&product_price=' . $regularPriceInCents . '&merchant_id=' . $merchantId,
            'scriptModalUrl' => $this->adminInsuranceHelper->envUrl() . ConstantsHelper::SCRIPT_MODAL_WIDGET_INSURANCE_PATH,
        ]);

        return $this->module->display($this->module->file, 'displayProductActions.tpl');
    }

    /**
     * @return false|string
     * @throws \PrestaShopException
     */
    private function handleSettings($merchantId)
    {
        $settings = $this->adminInsuranceHelper->mapDbFieldsWithIframeParams();
        $settings['merchant_id'] = $merchantId;

        return json_encode($settings);
    }
}
