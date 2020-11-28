<?php
/**
 * 2018-2020 Alma SAS
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
 * @copyright 2018-2020 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'alma/includes/hooks/AlmaFrontHookController.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/api/AlmaClient.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/utils/AlmaSettings.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/api/AlmaEligibilityHelper.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/utils/functions.php';

class AlmaFrontHeaderController extends AlmaFrontHookController
{
    public function run($params)
    {
        $controllerName = preg_replace("/[[:^alnum:]]+/", "", $this->context->controller->php_self);
        $handler = array($this, "handle${controllerName}Page");

        if (is_callable($handler)) {
            return call_user_func_array($handler, array($params));
        }
    }

    private function handleOrderPage($params)
    {
        $this->context->controller->addCSS($this->module->_path . 'views/css/alma.css', 'all');
        $this->context->controller->addJS($this->module->_path . 'views/js/alma_error.js');

        if ($this->context->cookie->__get('alma_error')) {
            $this->context->smarty->assign(array(
                'alma_error' => $this->context->cookie->__get('alma_error'),
            ));

            $this->context->cookie->__unset('alma_error');

            return $this->module->display($this->module->file, 'frontHeaderError.tpl');
        }
    }

    private function handleOrderOpcPage($params)
    {
        return $this->handleOrderPage($params);
    }

    private function handleProductPage($params)
    {
        if (AlmaSettings::showProductEligibility()) {
            $this->context->controller->addCSS($this->module->_path . 'views/css/alma-widgets.umd.css', 'all');
            $this->context->controller->addJS($this->module->_path . 'views/js/alma-widgets.umd.min.js', 'all');
            $this->context->controller->addCSS($this->module->_path . 'views/css/alma-product.css', 'all');
            $this->context->controller->addJS($this->module->_path . 'views/js/alma-product.js', 'all');
            return null;
        }
    }
}
