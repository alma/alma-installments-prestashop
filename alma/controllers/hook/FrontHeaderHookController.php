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

use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ShopHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Services\InsuranceService;

class FrontHeaderHookController extends FrontendHookController
{
    /**
     * @var \FrontController
     */
    private $controller;

    /**
     * @var int|mixed|string|null
     */
    private $moduleName;

    /**
     * @var InsuranceHelper
     */
    protected $insuranceHelper;

    /**
     * @var InsuranceService
     */
    protected $insuranceService;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @codeCoverageIgnore
     *
     * @param $module
     */
    public function __construct($module)
    {
        parent::__construct($module);
        $this->controller = $this->context->controller;
        $this->moduleName = $this->module->name;
        $this->settingsHelper = new SettingsHelper(new ShopHelper(), new ConfigurationHelper());
        $this->insuranceHelper = new InsuranceHelper();
        $this->insuranceService = new InsuranceService();
    }

    /**
     * @param $params
     *
     * @return string
     */
    public function run($params)
    {
        $controllerName = $this->currentControllerName();
        $handler = [$this, "handle{$controllerName}Page"];

        $content = $this->assetsWidgets();

        if ($this->settingsHelper->isInPageEnabled()) {
            $content .= $this->assetsInPage();
        }

        if (is_callable($handler)) {
            return $content . call_user_func_array($handler, [$params]);
        }

        return $content;
    }

    /**
     * @return bool
     */
    public function displayWidgetOnProductPage()
    {
        return SettingsHelper::showProductEligibility() && ($this->iAmInProductPage() || $this->iAmInHomePage());
    }

    /**
     * @return bool
     */
    private function displayWidgetOnCartPage()
    {
        return SettingsHelper::showEligibilityMessage() && $this->iAmInCartPage() && $this->cartIsNotEmpty();
    }

    /**
     * @return bool
     */
    private function iAmInProductPage()
    {
        return 'product' == $this->controller->php_self || 'ProductController' == get_class($this->controller);
    }

    /**
     * @return bool
     */
    protected function iAmInOrderPage()
    {
        return 'order' == $this->controller->php_self || 'OrderController' == get_class($this->controller);
    }

    /**
     * @return bool
     */
    private function iAmInHomePage()
    {
        return 'index' == $this->controller->php_self || 'IndexController' == get_class($this->controller);
    }

    /**
     * @return bool
     */
    private function cartIsNotEmpty()
    {
        return !empty($this->context->cart->getProducts());
    }

    /**
     * @return bool
     */
    private function iAmInCartPage()
    {
        // Step is available on Prestashop < 1.7
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            return ('order' == $this->controller->php_self && 0 == $this->controller->step)
                || 'order-opc' == $this->controller->php_self;
        }

        return 'cart' == $this->controller->php_self;
    }

    /**
     * @return bool
     */
    private function iAmInPaymentPage()
    {
        // Step is available on Prestashop < 1.7
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            return ('order' == $this->controller->php_self && 3 == $this->controller->step)
                || 'order-opc' == $this->controller->php_self;
        }

        return 'order' == $this->controller->php_self;
    }

    /**
     * (we are not sure that this function is still called).
     *
     * @param $params
     *
     * @return false|string|null
     */
    private function handleOrderPage($params)
    {
        $this->context->controller->addCSS($this->module->_path . 'views/css/alma.css', 'all');
        $this->context->controller->addJS($this->module->_path . 'views/js/alma_error.js');

        if ($this->context->cookie->__get('alma_error')) {
            $this->context->smarty->assign([
                'alma_error' => $this->context->cookie->__get('alma_error'),
            ]);

            $this->context->cookie->__unset('alma_error');

            return $this->module->display($this->module->file, 'frontHeaderError.tpl');
        }

        return null;
    }

    private function handleOrderOpcPage($params)
    {
        return $this->handleOrderPage($params);
    }

    /**
     * @return string
     */
    private function assetsWidgets()
    {
        $content = '';

        // Insurance Assets
        if (
            $this->insuranceHelper->isInsuranceActivated()
            && version_compare(_PS_VERSION_, '1.7', '>=')
        ) {
            $this->manageInsuranceAssetsAfter17();
        }

        if (
            $this->displayWidgetOnCartPage()
            || $this->displayWidgetOnProductPage()
        ) {
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $content .= $this->manageAssetVersionForPrestashopBefore17();
            }

            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                $content .= $this->manageAssetVersionForPrestashopAfter17();
            }
        }

        return $content;
    }

    /**
     * @return string
     */
    protected function manageInsuranceAssetsBefore17()
    {
        $content = '';

        $this->controller->addJS($this->module->_path . ConstantsHelper::INSURANCE_16_SCRIPT_PATH);

        if ($this->insuranceHelper->hasInsuranceInCart()) {
            $this->controller->addJS($this->module->_path . ConstantsHelper::MINI_CART_INSURANCE_16_SCRIPT_PATH);
            $text = $this->module->l('To manage your purchases with Assurance, please go to the checkout page.');
            $content .= '<input type="hidden" value="' . $text . '" id="alma-mini-cart-insurance-message">';

            if ($this->iAmInOrderPage()) {
                $this->controller->addJS($this->module->_path . ConstantsHelper::ORDER_INSURANCE_16_SCRIPT_PATH);
            }

            if ($this->iAmInCartPage()) {
                $this->controller->addJS($this->module->_path . ConstantsHelper::CART_INSURANCE_16_SCRIPT_PATH);
            }
        }

        if (
            $this->insuranceHelper->isInsuranceAllowedInProductPage()
            && $this->iAmInProductPage()
        ) {
            $this->controller->addJS($this->module->_path . ConstantsHelper::PRODUCT_INSURANCE_16_SCRIPT_PATH);
        }

        return $content;
    }

    /**
     * Manage assets for Prestashop Before 1.7.
     *
     * @return string
     */
    protected function manageAssetVersionForPrestashopBefore17()
    {
        $content = '';

        $this->controller->addJS(ConstantsHelper::WIDGETS_JS_URL);
        $this->controller->addJS($this->module->_path . ConstantsHelper::PRODUCT_SCRIPT_PATH);

        if ($this->displayWidgetOnCartPage()) {
            $this->controller->addJS($this->module->_path . ConstantsHelper::CART_SCRIPT_PATH);
        }

        if ($this->displayWidgetOnProductPage()) {
            $this->controller->addCSS($this->module->_path . ConstantsHelper::PRODUCT_CSS_PATH);
        }

        if (version_compare(_PS_VERSION_, '1.5.6.2', '<')) {
            $content .= '<link rel="stylesheet" href="' . ConstantsHelper::WIDGETS_CSS_URL . '">';
        }

        if (version_compare(_PS_VERSION_, '1.5.6.2', '>=')) {
            $this->controller->addCSS(ConstantsHelper::WIDGETS_CSS_URL);
        }

        return $content;
    }

    public function manageInsuranceAssetsAfter17()
    {
        if (
            $this->insuranceHelper->isInsuranceAllowedInProductPage()
            && $this->iAmInProductPage()
        ) {
            $this->controller->addJS($this->module->_path . ConstantsHelper::PRODUCT_INSURANCE_SCRIPT_PATH);

            $this->controller->registerStylesheet(
                ConstantsHelper::INSURANCE_PRODUCT_CSS_ID,
                "modules/$this->moduleName/" . ConstantsHelper::INSURANCE_PRODUCT_CSS_PATH
            );
        }

        if (
            $this->iAmInCartPage()
            && $this->cartIsNotEmpty()
        ) {
            $this->controller->addJS($this->module->_path . ConstantsHelper::CART_INSURANCE_SCRIPT_PATH);

            $this->controller->registerStylesheet(
                ConstantsHelper::INSURANCE_PRODUCT_CSS_ID,
                "modules/$this->moduleName/" . ConstantsHelper::INSURANCE_PRODUCT_CSS_PATH
            );
        }

        if (
            $this->insuranceHelper->isInsuranceAllowedInProductPage()
            && $this->iAmInOrderPage()
        ) {
            $this->controller->addJS($this->module->_path . ConstantsHelper::ORDER_INSURANCE_SCRIPT_PATH);
        }
    }

    /**
     * Manage assets for Prestashop after 1.7
     *
     * @return string
     */
    protected function manageAssetVersionForPrestashopAfter17()
    {
        $content = '';

        $scriptPath = "modules/$this->moduleName/" . ConstantsHelper::PRODUCT_SCRIPT_PATH;
        $cssPath = "modules/$this->moduleName/" . ConstantsHelper::PRODUCT_CSS_PATH;
        $cartScriptPath = "modules/$this->moduleName/" . ConstantsHelper::CART_SCRIPT_PATH;

        $this->controller->registerStylesheet(ConstantsHelper::PRODUCT_CSS_ID, $cssPath);

        if ($this->displayWidgetOnCartPage()) {
            $this->controller->registerJavascript(ConstantsHelper::CART_SCRIPT_ID, $cartScriptPath, ['priority' => 1000]);
        }

        if ($this->displayWidgetOnProductPage()) {
            $this->controller->registerJavascript(ConstantsHelper::PRODUCT_SCRIPT_ID, $scriptPath, ['priority' => 1000]);
        }

        if (version_compare(_PS_VERSION_, ConstantsHelper::PRESTASHOP_VERSION_1_7_0_2, '<')) {
            // For versions 1.7.0.0 and 1.7.0.1, it was impossible to register a remote script via FrontController
            // with the new registerJavascript method, and the deprecated addJS method had been changed to be just a
            // proxy to registerJavascript...
            $content = <<<TAG
                    <link rel="stylesheet" href="{${ConstantsHelper::WIDGETS_CSS_URL}}">
                    <script src="{${ConstantsHelper::WIDGETS_JS_URL}}"></script>
TAG;
        }

        if (version_compare(_PS_VERSION_, ConstantsHelper::PRESTASHOP_VERSION_1_7_0_2, '>=')) {
            $this->controller->registerStylesheet(ConstantsHelper::WIDGETS_CSS_ID, ConstantsHelper::WIDGETS_CSS_URL, ['server' => 'remote']);
            $this->controller->registerJavascript(ConstantsHelper::WIDGETS_JS_ID, ConstantsHelper::WIDGETS_JS_URL, ['server' => 'remote']);
        }

        return $content;
    }

    /**
     * @return string|null
     */
    private function assetsInPage()
    {
        $content = null;

        if ($this->iAmInPaymentPage()) {
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $this->controller->addJS(ConstantsHelper::INPAGE_JS_URL);
                $this->controller->addJS($this->module->_path . ConstantsHelper::INPAGE_SCRIPT_PATH);

                if (version_compare(_PS_VERSION_, '1.6.0.2', '>')) {
                    $this->controller->removeJS($this->module->_path . ConstantsHelper::WIDGETS_JS_URL);
                }
            }
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                $this->controller->registerJavascript(
                    ConstantsHelper::INPAGE_SCRIPT_ID,
                    "modules/$this->moduleName/" . ConstantsHelper::INPAGE_SCRIPT_PATH,
                    ['priority' => 1000]
                );

                if (version_compare(_PS_VERSION_, ConstantsHelper::PRESTASHOP_VERSION_1_7_0_2, '<')) {
                    // For versions 1.7.0.0 and 1.7.0.1, it was impossible to register a remote script via FrontController
                    // with the new registerJavascript method, and the deprecated addJS method had been changed to be just a
                    // proxy to registerJavascript...
                    $content .= <<<TAG
                    <script src="{${ConstantsHelper::INPAGE_JS_URL}}"></script>
TAG;
                }
                if (version_compare(_PS_VERSION_, ConstantsHelper::PRESTASHOP_VERSION_1_7_0_2, '>=')) {
                    $this->controller->registerJavascript(
                        ConstantsHelper::INPAGE_JS_ID,
                        ConstantsHelper::INPAGE_JS_URL,
                        ['server' => 'remote']
                    );
                    $this->controller->unregisterJavascript(ConstantsHelper::WIDGETS_JS_ID);
                }
            }
        }

        return $content;
    }
}
