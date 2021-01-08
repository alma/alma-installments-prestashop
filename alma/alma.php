<?php

/**
 * 2018-2021 Alma SAS
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
 * @copyright 2018-2021 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'alma/vendor/autoload.php';
require_once _PS_MODULE_DIR_ . 'alma/lib/Utils/smarty.php';

class Alma extends PaymentModule
{
    const VERSION = '2.0.0';

    public $_path;
    public $local_path;

    /** @var string */
    public $file;

    /** @var string[] */
    public $limited_currencies;

    public function __construct()
    {
        $this->name = 'alma';
        $this->tab = 'payments_gateways';
        $this->version = '2.0.0';
        $this->author = 'Alma';
        $this->need_instance = false;
        $this->bootstrap = true;
        $this->controllers = ['payment', 'validation', 'ipn'];
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->module_key = 'ad25114b1fb02d9d8b8787b992a0ccdb';

        $this->limited_currencies = ['EUR'];

        $version = explode('.', _PS_VERSION_);
        $version[3] = (int) $version[3] + 1;
        $version = implode('.', $version);

        $this->ps_versions_compliancy = ['min' => '1.5.3.1', 'max' => $version];

        parent::__construct();

        $this->displayName = $this->l('Alma Monthly Installments for PrestaShop', 'alma');
        $this->description = $this->l('Offer an easy and safe installments payments option to your customers', 'alma');
        // phpcs:ignore
        $this->confirmUninstall = $this->l('Are you sure you want to deactivate Alma Monthly Installments from your shop?', 'alma');

        $this->file = __FILE__;

        if (version_compare(_PS_VERSION_, '1.5.0.1', '<')) {
            $this->local_path = _PS_MODULE_DIR_ . $this->name . '/';
        }
    }

    public function install()
    {
        $Logger = Alma\PrestaShop\Utils\Logger::loggerClass();

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

        $commonHooks = [
            'header',
            'displayBackOfficeHeader',
            'displayShoppingCartFooter',
            'actionOrderStatusPostUpdate',
            'displayAdminOrder',
        ];

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $paymentHooks = ['paymentOptions', 'displayPaymentReturn'];
        } else {
            $paymentHooks = ['displayPayment', 'displayPaymentEU', 'displayPaymentReturn'];
        }

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $productHooks = ['displayProductPriceBlock'];
        } else {
            $productHooks = ['displayProductButtons'];
        }

        foreach (array_merge($commonHooks, $paymentHooks, $productHooks) as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        Tools::clearCache();

        // Enable Alma payment for all installed carriers in PrestaShop 1.7+
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->updateCarriersWithAlma();
        }

        return $this->installTabs();
    }

    private function updateCarriersWithAlma()
    {
        $id_module = $this->id;
        $id_shop = (int) $this->context->shop->id;
        $id_lang = $this->context->language->id;
        $carriers = Carrier::getCarriers($id_lang, false, false, false, null, Carrier::ALL_CARRIERS);
        $values = null;
        foreach ($carriers as $carrier) {
            $values .= "({$id_module},{$id_shop},{$carrier['id_reference']}),";
        }
        $values = rtrim($values, ',');
        Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'module_carrier` WHERE `id_module` = ' . $id_module
        );
        Db::getInstance()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'module_carrier` (`id_module`, `id_shop`, `id_reference`) VALUES ' . $values
        );
    }

    public function hookDisplayProductPriceBlock($params)
    {
        return $this->runHookController('displayProductPriceBlock', $params);
    }

    // displayProductButtons is registered on PrestaShop 1.5 only, as displayProductPriceBlock wasn't available then
    public function hookDisplayProductButtons($params)
    {
        return $this->runHookController('displayProductPriceBlock', $params);
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
        $result = parent::uninstall() && Alma\PrestaShop\Utils\Settings::deleteAllValues();

        $paymentModuleConf = [
            'CONF_ALMA_FIXED',
            'CONF_ALMA_VAR',
            'CONF_ALMA_FIXED_FOREIGN',
            'CONF_ALMA_VAR_FOREIGN',
        ];

        foreach ($paymentModuleConf as $configKey) {
            if (Configuration::hasKey($configKey)) {
                $result = $result && Configuration::deleteByName($configKey);
            }
        }

        return $result && $this->uninstallTabs();
    }

    public function installTabs()
    {
        return $this->installTab('alma', 'Alma')
            && $this->installTab('AdminAlmaConfig', $this->l('Configuration'), 'alma', 1, 'tune')
            && $this->installTab('AdminAlmaCategories', $this->l('Excluded categories'), 'alma', 2, 'not_interested');
    }

    private function uninstallTabs()
    {
        return $this->uninstallTab('AdminAlmaCategories')
            && $this->uninstallTab('AdminAlmaConfig')
            && $this->uninstallTab('alma');
    }

    private function installTab($class, $name, $parent = null, $position = null, $icon = null)
    {
        $tab = Tab::getInstanceFromClassName($class);
        $tab->active = 1;
        $tab->class_name = $class;
        $tab->name = [];

        if ($position) {
            $tab->position = $position;
        }

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }

        if ($parent) {
            if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                if ($icon) {
                    $tab->icon = $icon;
                }
            }

            $parentTab = Tab::getInstanceFromClassName($parent);
            $tab->id_parent = $parentTab->id;
        } else {
            $tab->id_parent = 0;
        }

        $tab->module = $this->name;

        return $tab->save();
    }

    private function uninstallTab($class)
    {
        $tab = Tab::getInstanceFromClassName($class);
        if (!Validate::isLoadedObject($tab)) {
            return true;
        }

        return $tab->delete();
    }

    private function runHookController($hookName, $params)
    {
        $hookName = Tools::ucfirst(preg_replace('/[^a-zA-Z0-9]/', '', $hookName));

        require_once dirname(__FILE__) . "/controllers/hook/${hookName}HookController.php";
        $ControllerName = "Alma\PrestaShop\Controllers\Hook\\${hookName}HookController";
        $controller = new $ControllerName($this);

        if ($controller->canRun()) {
            return $controller->run($params);
        } else {
            return null;
        }
    }

    public function getContent()
    {
        return $this->runHookController('getContent', null);
    }

    public function hookHeader($params)
    {
        return $this->runHookController('frontHeader', $params);
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        $this->context->controller->addCSS($this->_path . 'views/css/admin/_configure/helpers/form/form.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/admin/almaPage.css', 'all');
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

    public function hookDisplayAdminOrder($params)
    {
        return $this->runHookController('displayRefunds', $params);
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        return $this->runHookController('state', $params);
    }
}
