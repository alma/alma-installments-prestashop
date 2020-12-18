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

include_once _PS_MODULE_DIR_ . 'alma/vendor/autoload.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/utils/smarty.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaSettings.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaLogger.php';

class Alma extends PaymentModule
{
    const VERSION = '1.3.1';

    public $_path;
    public $local_path;

    public function __construct()
    {
        $this->name = 'alma';
        $this->tab = 'payments_gateways';
        $this->version = '1.3.1';
        $this->author = 'Alma';
        $this->need_instance = false;
        $this->bootstrap = true;
        $this->controllers = array('payment', 'validation', 'ipn');
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->module_key = 'ad25114b1fb02d9d8b8787b992a0ccdb';

        $this->limited_currencies = array('EUR');

        $this->ps_versions_compliancy = array('min' => '1.5.6.2', 'max' => _PS_VERSION_);

        parent::__construct();

        $this->displayName = $this->l('Alma Monthly Installments for PrestaShop', 'alma');
        $this->description = $this->l('Offer an easy and safe installments payments option to your customers', 'alma');
        $this->confirmUninstall = $this->l('Are you sure you want to deactivate Alma Monthly Installments from your shop?', 'alma');

        $this->file = __FILE__;

        if (version_compare(_PS_VERSION_, '1.5.0.1', '<')) {
            $this->local_path = _PS_MODULE_DIR_ . $this->name . '/';
        }
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

        $commonHooks = array(
            'header',
            'displayBackOfficeHeader',
            'displayShoppingCartFooter',
            'actionOrderSlipAdd',
            'actionOrderStatusPostUpdate',
        );

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $paymentHooks = array('paymentOptions', 'displayPaymentReturn');
        } else {
            $paymentHooks = array('displayPayment', 'displayPaymentEU', 'displayPaymentReturn');
        }

        foreach (array_merge($commonHooks, $paymentHooks) as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

		return $this->installTabs();
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

    public function installTabs()
    {
        return $this->installTab('alma', 'Alma')
			&& $this->installTab('AdminAlmaCategories', $this->l('Excluded categories'), 'alma', 'not_interested');
    }

    protected function installTab($class, $name, $parent = null, $icon = null)
    {

        $tabId = (int) Tab::getIdFromClassName($class);
        if (!$tabId) {
            $tabId = null;
        }

        $tab = new Tab($tabId);
        $tab->active = 1;
        $tab->class_name = $class;
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }
        if ($parent) {
            if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                if ($icon) {
                    $tab->icon = $icon;
                }
            }
            $tab->id_parent = (int)Tab::getIdFromClassName($parent);
        } else {
            $tab->id_parent = 0;
        }
        $tab->module = $this->name;

        return $tab->save();
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
        $this->context->controller->addCSS($this->_path . 'views/css/admin/almaPage.css', 'all');
    }

    private function runHookController($hookName, $params)
    {
        $hookName = preg_replace("/[^a-zA-Z0-9]/", "", $hookName);

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

    public function hookDisplayPaymentEU($params)
    {
        $params['for_eu_compliance_module'] = true;
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

    public function hookActionOrderSlipAdd($params)
    {
        return $this->runHookController('refund', $params);
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        return $this->runHookController('state', $params);
    }
}
