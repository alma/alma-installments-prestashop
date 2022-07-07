<?php
/**
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Utils\Settings;

class FrontHeaderHookController extends FrontendHookController
{
    public function run($params)
    {
        $controllerName = $this->currentControllerName();
        $handler = [$this, "handle${controllerName}Page"];

        $content = $this->injectAlmaAssets($params);

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

    private function injectAlmaAssets($params)
    {
        $widgetsCssUrl = 'https://cdn.jsdelivr.net/npm/@alma/widgets@2.11.1/dist/widgets.min.css';
        $widgetsJsUrl = 'https://cdn.jsdelivr.net/npm/@alma/widgets@2.11.1/dist/widgets.umd.js';
        $productScriptPath = 'views/js/alma-product.js';
        $productCssPath = 'views/css/alma-product.css';
        $cartScriptPath = 'views/js/alma-cart.js';

        $controller = $this->context->controller;
        $content = null;

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            // Cart widget
            if (Settings::showEligibilityMessage() &&
                    ($controller->php_self == 'order' && $controller->step == 0 || $controller->php_self == 'order-opc')
                    && (isset($controller->nbProducts) && $controller->nbProducts != 0)
                ) {
                if (version_compare(_PS_VERSION_, '1.5.6.2', '<')) {
                    $content .= '<link rel="stylesheet" href="' . $widgetsCssUrl . '">';
                } else {
                    $controller->addCSS($widgetsCssUrl);
                }
                $controller->addCSS($this->module->_path . $productCssPath);
                $controller->addJS($widgetsJsUrl);
                $controller->addJS($this->module->_path . $cartScriptPath);
            } elseif (Settings::showProductEligibility()
                && ($controller->php_self == 'product' || 'ProductController' == get_class($controller))) {
                // Product widget
                if (version_compare(_PS_VERSION_, '1.5.6.2', '<')) {
                    $content .= '<link rel="stylesheet" href="' . $widgetsCssUrl . '">';
                } else {
                    $controller->addCSS($widgetsCssUrl);
                }
                $controller->addCSS($this->module->_path . $productCssPath);
                $controller->addJS($widgetsJsUrl);
                $controller->addJS($this->module->_path . $productScriptPath);
            }
        } else {
            $moduleName = $this->module->name;
            $scriptPath = "modules/$moduleName/$productScriptPath";
            $cssPath = "modules/$moduleName/$productCssPath";
            $cartScriptPath = "modules/$moduleName/$cartScriptPath";

            if ($controller->php_self == 'cart' && Settings::showEligibilityMessage()) {
                $controller->registerStylesheet('alma-product-css', $cssPath);
                $controller->registerJavascript('alma-cart-script', $cartScriptPath, ['priority' => 1000]);
            } else {
                $controller->registerStylesheet('alma-product-css', $cssPath);
                $controller->registerJavascript('alma-product-script', $scriptPath, ['priority' => 1000]);
            }

            if (version_compare(_PS_VERSION_, '1.7.0.2', '>=')) {
                $controller->registerStylesheet('alma-remote-widgets-css', $widgetsCssUrl, ['server' => 'remote']);
                $controller->registerJavascript('alma-remote-widgets-js', $widgetsJsUrl, ['server' => 'remote']);
            } else {
                // For versions 1.7.0.0 and 1.7.0.1, it was impossible to register a remote script via FrontController
                // with the new registerJavascript method, and the deprecated addJS method had been changed to be just a
                // proxy to registerJavascript...
                $content .= <<<TAG
					<link rel="stylesheet" href="$widgetsCssUrl">
					<script src="$widgetsJsUrl"></script>
TAG;
            }
        }

        return $content;
    }
}
