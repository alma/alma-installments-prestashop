<?php
/**
 * 2018 Alma / Nabla SAS
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
 * @author    Alma / Nabla SAS <contact@getalma.eu>
 * @copyright 2018 Alma / Nabla SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_ . 'alma/vendor/autoload.php');
include_once(_PS_MODULE_DIR_ . 'alma/includes/AlmaSettings.php');

class Alma extends PaymentModule
{
    public $_path;

    public function __construct()
    {
        $this->name = 'alma';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Alma';
        $this->need_instance = false;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Alma Monthly Payments for PrestaShop', 'alma');
        $this->description = $this->l('Offer a secure split payment option to your customers', 'alma');
        $this->confirmUninstall = $this->l(
            'Are you sure you want to deactivate Alma Monthly Payments from your shop?',
            'alma'
        );

        $this->limited_countries = array('FR');
        $this->limited_currencies = array('EUR');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->currencies = true;
            $this->currencies_mode = 'checkbox';
        }

        $this->file = __FILE__;
    }

    public function getContent()
    {
        return $this->runHookController('getContent', null);
    }

    public function install()
    {
        if (!$this->checkDependencies()) {
            return false;
        }

        $iso_code = Tools::strtoupper(Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT')));

        if (in_array($iso_code, $this->limited_countries) == false) {
            $limited_countries = var_export($this->limited_countries, true);
            $this->_errors[] = $this->l(
                "This module is not available in your country ({$iso_code} not in {$limited_countries})"
            );
            return false;
        }

        $commonHooks = array('header', 'displayShoppingCartFooter');

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $paymentHooks = array('paymentOptions', 'displayPaymentReturn');
        } else {
            $paymentHooks = array('displayPayment', 'displayPaymentReturn');
        }

        foreach (array_merge($commonHooks, $paymentHooks) as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        return parent::install();
    }

    private function checkDependencies()
    {
        $result = true;
        if (!function_exists('curl_init')) {
            $result = false;
            $this->_errors[] = $this->l('Alma requires the CURL PHP extension.', 'alma');
        }

        if (!function_exists('json_decode')) {
            $result = false;
            $this->_errors[] = $this->l('Alma requires the JSON PHP extension.', 'alma');
        }

        $openssl_exception = $this->l('Alma requires OpenSSL >= 1.0.1', 'alma');
        if (!defined('OPENSSL_VERSION_TEXT')) {
            $result = false;
            $this->_errors[] = $openssl_exception;
        }

        preg_match('/^(?:Libre|Open)SSL ([\d.]+)/', OPENSSL_VERSION_TEXT, $matches);
        if (empty($matches[1])) {
            $result = false;
            $this->_errors[] = $openssl_exception;
        }

        if (!version_compare($matches[1], '1.0.1', '>=')) {
            $result = false;
            $this->_errors[] = $openssl_exception;
        }

        return $result;
    }

    public function uninstall()
    {
        $result = parent::uninstall() && AlmaSettings::deleteAllValues();

        $paymentModuleConf = array(
            'CONF_ALMA_FIXED',
            'CONF_ALMA_VAR',
            'CONF_ALMA_FIXED_FOREIGN',
            'CONF_ALMA_VAR_FOREIGN'
        );

        foreach ($paymentModuleConf as $configKey) {
            if (Configuration::hasKey($configKey)) {
                $result = $result && Configuration::deleteByName($configKey);
            }
        }

        return $result;
    }

    public function hookHeader($params)
    {
        if ($this->context->controller->php_self == 'order-opc' || $this->context->controller->php_self == 'order') {
            $this->context->controller->addCSS($this->_path.'views/css/alma.css', 'all');
            $this->context->controller->addJS($this->_path.'views/js/alma_error.js');

            if ($this->context->cookie->__get('alma_error')) {
                $this->context->smarty->assign(array(
                    "alma_error" => $this->context->cookie->__get('alma_error')
                ));

                $this->context->cookie->__unset('alma_error');

                return $this->display($this->file, 'header.tpl');
            }
        }
    }

    private function runHookController($hookName, $params)
    {
        require_once(dirname(__FILE__).'/controllers/hook/'.$hookName.'.php');
        $controller_name = $this->name . $hookName . 'Controller';
        $controller = new $controller_name($this);

        if ($controller->canRun()) {
            return $controller->run($params);
        } else {
            return null;
        }
    }

    public function hookPaymentOptions($params)
    {
        return $this->runHookController('paymentOptions', $params);
    }

    public function hookDisplayPayment($params)
    {
        return $this->runHookController('displayPayment', $params);
    }

    public function hookDisplayPaymentReturn($params)
    {
        return $this->runHookController('displayPaymentReturn', $params);
    }

    public function hookDisplayShoppingCartFooter($params)
    {
        return $this->runHookController('displayShoppingCartFooter', $params);
    }
}
