<?php
/**
 * 2018-2023 Alma SAS
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

use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Utils\Settings;
use FrontController;

class FrontHeaderHookController extends FrontendHookController
{
    const INPAGE_SCRIPT_PATH = 'views/js/alma-fragments.js';
    const INPAGE_JS_URL = 'https://cdn.jsdelivr.net/npm/@alma/in-page@1.x.x/dist/index.umd.js';
    const WIDGETS_CSS_URL = 'https://cdn.jsdelivr.net/npm/@alma/widgets@3.x.x/dist/widgets.min.css';
    const WIDGETS_JS_URL = 'https://cdn.jsdelivr.net/npm/@alma/widgets@3.x.x/dist/widgets.umd.js';
    const PRODUCT_SCRIPT_PATH = 'views/js/alma-product.js';
    const PRODUCT_CSS_PATH = 'views/css/alma-product.css';
    const CART_SCRIPT_PATH = 'views/js/alma-cart.js';
    const ID_INPAGE_JS = 'alma-remote-fragments-js';
    const ID_INPAGE_SCRIPT = 'alma-fragments-script';
    const ID_WIDGETS_JS = 'alma-remote-widgets-js';
    const ID_WIDGETS_CSS = 'alma-remote-widgets-css';
    const ID_PRODUCT_CSS = 'alma-product-css';
    const ID_PRODUCT_SCRIPT = 'alma-product-script';
    const ID_CART_SCRIPT = 'alma-cart-script';

    /**
     * @var FrontController
     */
    private $controller;

    /**
     * @var int|mixed|string|null
     */
    private $moduleName;

    /**
     * @param $module
     */
    public function __construct($module)
    {
        parent::__construct($module);
        $this->controller = $this->context->controller;
        $this->moduleName = $this->module->name;
    }

    /**
     * @param $params
     * @return string
     */
    public function run($params)
    {
        $controllerName = $this->currentControllerName();
        $handler = [$this, "handle{$controllerName}Page"];

        $content = $this->injectAlmaAssets();
        if (Settings::isFragmentEnabled()) {
            $content = $this->assetsInPage();
        }

        if (is_callable($handler)) {
            return $content . call_user_func_array($handler, [$params]);
        }

        return $content;
    }

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
     * @return string|null
     */
    private function injectAlmaAssets()
    {
        $content = null;

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            if (Settings::showEligibilityMessage()
                && ($this->controller->php_self == 'order' && $this->controller->step == 0 || $this->controller->php_self == 'order-opc')
                && (isset($this->controller->nbProducts) && $this->controller->nbProducts != 0)
            ) {
                // Cart widget
                if (version_compare(_PS_VERSION_, '1.5.6.2', '<')) {
                    $content .= '<link rel="stylesheet" href="' . self::WIDGETS_CSS_URL . '">';
                } else {
                    $this->controller->addCSS(self::WIDGETS_CSS_URL);
                }
                $this->controller->addCSS($this->module->_path . self::PRODUCT_CSS_PATH);
                $this->controller->addJS(self::WIDGETS_JS_URL);
                $this->controller->addJS($this->module->_path . self::CART_SCRIPT_PATH);
            } elseif (Settings::showProductEligibility()
                && ($this->controller->php_self == 'product' || 'ProductController' == get_class($this->controller))) {
                // Product widget
                if (version_compare(_PS_VERSION_, '1.5.6.2', '<')) {
                    $content .= '<link rel="stylesheet" href="' . self::WIDGETS_CSS_URL . '">';
                } else {
                    $this->controller->addCSS(self::WIDGETS_CSS_URL);
                }
                $this->controller->addCSS($this->module->_path . self::PRODUCT_CSS_PATH);
                $this->controller->addJS(self::WIDGETS_JS_URL);
                $this->controller->addJS($this->module->_path . self::PRODUCT_SCRIPT_PATH);
            }
        } else {
            $scriptPath = "modules/$this->moduleName/" . self::PRODUCT_SCRIPT_PATH;
            $cssPath = "modules/$this->moduleName/" . self::PRODUCT_CSS_PATH;
            $cartScriptPath = "modules/$this->moduleName/" . self::CART_SCRIPT_PATH;

            if ($this->controller->php_self == 'cart' && Settings::showEligibilityMessage()) {
                $this->controller->registerStylesheet(self::ID_PRODUCT_CSS, $cssPath);
                $this->controller->registerJavascript(self::ID_CART_SCRIPT, $cartScriptPath, ['priority' => 1000]);
            } else {
                $this->controller->registerStylesheet(self::ID_PRODUCT_CSS, $cssPath);
                $this->controller->registerJavascript(self::ID_PRODUCT_SCRIPT, $scriptPath, ['priority' => 1000]);
            }

            if (version_compare(_PS_VERSION_, '1.7.0.2', '>=')) {
                $this->controller->registerStylesheet(self::ID_WIDGETS_CSS, self::WIDGETS_CSS_URL, ['server' => 'remote']);
                $this->controller->registerJavascript(self::ID_WIDGETS_JS, self::WIDGETS_JS_URL, ['server' => 'remote']);
            } else {
                // For versions 1.7.0.0 and 1.7.0.1, it was impossible to register a remote script via FrontController
                // with the new registerJavascript method, and the deprecated addJS method had been changed to be just a
                // proxy to registerJavascript...
                $content .= <<<TAG
                <link rel="stylesheet" href="{${self::WIDGETS_CSS_URL}}">
                <script src="{${self::WIDGETS_JS_URL}}"></script>
TAG;
            }
        }

        return $content;
    }

    /**
     * @return string|null
     */
    private function assetsInPage()
    {
        $content = null;

        if ($this->controller->php_self == 'order' || $this->controller->php_self == 'order-opc') {
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                if ($this->controller->step == 3) {
                    $this->controller->addJS(self::INPAGE_JS_URL);
                    $this->controller->addJS($this->module->_path . self::INPAGE_SCRIPT_PATH);
                    $this->controller->removeJS($this->module->_path . self::WIDGETS_JS_URL);
                }
            } else {
                $this->controller->registerJavascript(
                    self::ID_INPAGE_SCRIPT,
                    "modules/$this->moduleName/" . self::INPAGE_SCRIPT_PATH,
                    ['priority' => 1000, 'position' => 'head']
                );

                if (version_compare(_PS_VERSION_, '1.7.0.2', '>=')) {
                    $this->controller->registerJavascript(
                        self::ID_INPAGE_JS,
                        self::INPAGE_JS_URL,
                        ['server' => 'remote', 'position' => 'head']
                    );
                    $this->controller->unregisterJavascript(self::ID_WIDGETS_JS);
                } else {
                    $content .= <<<TAG
                    <script src="{${self::INPAGE_JS_URL}}"></script>
TAG;
                }
            }
        }

        return $content;
    }
}
