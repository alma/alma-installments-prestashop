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

use Alma\PrestaShop\Model\HookHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'alma/vendor/autoload.php';
require_once _PS_MODULE_DIR_ . 'alma/lib/Utils/smarty.php';
require_once _PS_MODULE_DIR_ . 'alma/autoloader.php';

class Alma extends PaymentModule
{
    const VERSION = '2.13.0';

    public $_path;
    public $local_path;

    /** @var string */
    public $file;

    /** @var string[] */
    public $limited_currencies;

    /**
     * @var HookHelper
     */
    public $hook;

    /**
     * @var true
     */
    public $bootstrap;

    /**
     * @var int
     */
    private $is_eu_compatible;

    /**
     * @var string
     */
    public $confirmUninstall;

    public function __construct()
    {
        $this->name = 'alma';
        $this->tab = 'payments_gateways';
        $this->version = '2.13.0';
        $this->author = 'Alma';
        $this->need_instance = false;
        $this->bootstrap = true;
        $this->controllers = ['payment', 'validation', 'ipn'];
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->module_key = 'ad25114b1fb02d9d8b8787b992a0ccdb';

        $this->limited_currencies = ['EUR'];

        $version = _PS_VERSION_;
        // Need to anticipate Fix bug #PSCFV-10990 Prestashop : https://github.com/PrestaShop/PrestaShop/commit/c69688cc1107e053aa2297fdcb40c70c08fa135f
        if (version_compare(_PS_VERSION_, '1.5.6.1', '<')) {
            $version = substr_replace($version, substr($version, -1) + 1, -1);
        }

        $this->ps_versions_compliancy = ['min' => '1.5.3.1', 'max' => $version];

        parent::__construct();

        $this->displayName = $this->l('1x 2x 3x 4x, D+15 or D+30 Alma - Payment in instalments and deferred', 'alma');
        $this->description = $this->l('Offer an easy and safe installments payments option to your customers', 'alma');
        $this->confirmUninstall = $this->l('Are you sure you want to deactivate Alma payments from your shop?', 'alma');

        $this->file = __FILE__;

        if (version_compare(_PS_VERSION_, '1.5.0.1', '<')) {
            $this->local_path = _PS_MODULE_DIR_ . $this->name . '/';
        }

        $this->hook = new HookHelper();
    }

    /**
     * Check parent install result then add log errors if any
     *
     * @param $coreInstall
     *
     * @return bool as result of parent::install() method
     */
    private function checkCoreInstall($coreInstall)
    {
        if (!$coreInstall) {
            $logger = \Alma\PrestaShop\Utils\Logger::loggerClass();
            $logger::addLog("Alma: Core module install failed (returned {$coreInstall})", 3);

            if (count($this->_errors) > 0) {
                $errors = implode(', ', $this->_errors);
                $logger::addLog("Alma: module install errors: {$errors})", 3);
            }

            return false;
        }

        return true;
    }

    /**
     * Insert module into datable.
     *
     * @override
     */
    public function install()
    {
        $coreInstall = parent::install();

        if (!$this->checkCoreInstall($coreInstall) ||
            !$this->checkDependencies() ||
            !$this->registerHooks()) {
            return false;
        }

        Tools::clearCache();

        // Enable Alma payment for all installed carriers in PrestaShop 1.7+
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->updateCarriersWithAlma();
        }

        return $this->installTabs();
    }

    /**
     * Try to register mandatory hooks
     *
     * @return bool
     */
    private function registerHooks()
    {
        $hooks = $this->hook->almaRegisterHooks();

        foreach ($hooks as $hook) {
            $this->registerHook($hook);
        }

        return true;
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

    /**
     * Check php lib dependencies and versions
     *
     * @return bool
     */
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
        $result = parent::uninstall() && \Alma\PrestaShop\Utils\Settings::deleteAllValues();

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
            && $this->installTab('AdminAlmaCategories', $this->l('Excluded categories'), 'alma', 2, 'not_interested')
            && $this->installTab('AdminAlmaRefunds', false, 'alma')
            && $this->installTab('AdminAlmaShareOfCheckout', false, 'alma');
    }

    public function uninstallTabs()
    {
        return $this->uninstallTab('AdminAlmaCategories')
            && $this->uninstallTab('AdminAlmaRefunds')
            && $this->uninstallTab('AdminAlmaConfig')
            && $this->uninstallTab('AdminAlmaShareOfCheckout')
            && $this->uninstallTab('alma');
    }

    /**
     * Add Alma in backoffice menu
     *
     * @param string $class class controller
     * @param string $name tab title
     * @param string|null $parent parent class name
     * @param int|null $position order in menu
     * @param string|null $icon fontAwesome class icon
     *
     * @return bool if save succefuly
     */
    private function installTab($class, $name, $parent = null, $position = null, $icon = null)
    {
        $tab = Tab::getInstanceFromClassName($class);
        $tab->active = $name !== false;
        $tab->class_name = $class;
        $tab->name = [];

        if ($position) {
            $tab->position = $position;
        }

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }

        $tab->id_parent = 0;
        if ($parent) {
            if (version_compare(_PS_VERSION_, '1.7', '>=') && $icon) {
                $tab->icon = $icon;
            }

            $parentTab = Tab::getInstanceFromClassName($parent);
            $tab->id_parent = $parentTab->id;
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

        require_once dirname(__FILE__) . "/controllers/hook/{$hookName}HookController.php";
        $ControllerName = "Alma\PrestaShop\Controllers\Hook\\{$hookName}HookController";

        // check if override exist for hook controllers
        if (file_exists(dirname(__FILE__) . "/../../override/modules/alma/controllers/hook/{$hookName}HookController.php")) {
            require_once dirname(__FILE__) . "/../../override/modules/alma/controllers/hook/{$hookName}HookController.php";
            $ControllerName = "Alma\PrestaShop\Controllers\Hook\\{$hookName}HookControllerOverride";
        }

        $controller = new $ControllerName($this);

        if ($controller->canRun()) {
            return $controller->run($params);
        } else {
            return null;
        }
    }

    public function viewAccess()
    {
        // Simply redirect to the default module's configuration page
        $location = \Alma\PrestaShop\Utils\LinkHelper::getAdminLinkAlmaDashboard();

        Tools::redirectAdmin($location);
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
        return $this->runHookController('displayBackOfficeHeader', $params);
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

    // Deprecated for version 1.7
    public function hookDisplayPaymentReturn($params)
    {
        try {
            return $this->runHookController('displayPaymentReturn', $params);
        } catch (\Alma\PrestaShop\Exceptions\RenderPaymentException $e) {
            $module = Module::getInstanceByName('alma');
            $this->context->smarty->assign([
                'payment' => null,
            ]);

            return $module->display($module->file, 'displayPaymentReturn.tpl');
        }
    }

    // New name of displayPaymentReturn hook for 1.7
    public function hookPaymentReturn($params)
    {
        try {
            return $this->runHookController('displayPaymentReturn', $params);
        } catch (\Alma\PrestaShop\Exceptions\RenderPaymentException $e) {
            return '';
        }
    }

    public function hookDisplayShoppingCartFooter($params)
    {
        return $this->runHookController('displayShoppingCartFooter', $params);
    }

    public function hookDisplayAdminOrder($params)
    {
        return $this->runHookController('displayRefunds', $params);
    }

    public function hookDisplayAdminOrderMain($params)
    {
        return $this->runHookController('displayRefunds', $params);
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        return $this->runHookController('state', $params);
    }

    /**
     * Hook action DisplayAdminAfterHeader
     */
    public function hookDisplayAdminAfterHeader($params)
    {
        return $this->runHookController('displayAdminAfterHeader', $params);
    }
}
