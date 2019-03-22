<?php
/**
 * 2018-2019 Alma SAS
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
 * @copyright 2018-2019 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'alma/vendor/autoload.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaSettings.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaLogger.php';

class Alma extends PaymentModule
{
    const VERSION = '1.0.1';

    public $_path;

    public function __construct()
    {
        $this->name = 'alma';
        $this->tab = 'payments_gateways';
        $this->version = self::VERSION;
        $this->author = 'Alma';
        $this->need_instance = false;
        $this->bootstrap = true;
        $this->module_key = 'ad25114b1fb02d9d8b8787b992a0ccdb';

        $this->limited_currencies = array('EUR');

        $this->ps_versions_compliancy = array('min' => '1.5.6.2', 'max' => _PS_VERSION_);

        parent::__construct();

        $this->displayName = $this->l('Alma Monthly Installments for PrestaShop', 'alma');
        $this->description = $this->l('Offer an easy and safe installments payments option to your customers', 'alma');
        $this->confirmUninstall = $this->l('Are you sure you want to deactivate Alma Monthly Installments from your shop?', 'alma');

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
        $Logger = AlmaLogger::loggerClass();

        $core_install = parent::install();
        if (!$core_install) {
            $Logger::addLog("Alma: Core module install failed (returned {$core_install})", 3);

            if (count($this->_errors) > 0) {
                $errors = implode(', ', $this->_errors);
                $Logger::addLog("Alma: module install errors: {$errors})", 3);
            }

            return false;
        }

        if (!$this->checkDependencies()) {
            return false;
        }

        $commonHooks = array('header', 'displayBackOfficeHeader', 'displayShoppingCartFooter');

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

        return true;
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
            'CONF_ALMA_VAR_FOREIGN',
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
            $this->context->controller->addCSS($this->_path . 'views/css/alma.css', 'all');
            $this->context->controller->addJS($this->_path . 'views/js/alma_error.js');

            if ($this->context->cookie->__get('alma_error')) {
                $this->context->smarty->assign(array(
                    'alma_error' => $this->context->cookie->__get('alma_error'),
                ));

                $this->context->cookie->__unset('alma_error');

                return $this->display($this->file, 'header.tpl');
            }
        }
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        $this->context->controller->addCSS($this->_path . 'views/css/admin/_configure/helpers/form/form.css', 'all');
    }

    private function runHookController($hookName, $params)
    {
        require_once dirname(__FILE__) . '/controllers/hook/' . $hookName . '.php';
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
