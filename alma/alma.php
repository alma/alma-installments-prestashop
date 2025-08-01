<?php
/**
 * 2018-2024 Alma SAS.
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
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

// Autoload here for the module definition
require_once _PS_MODULE_DIR_ . 'alma/vendor/autoload.php';

class Alma extends PaymentModule
{
    const VERSION = '4.10.0';
    const PS_ACCOUNTS_VERSION_REQUIRED = '5.3.0';

    public $_path;
    public $local_path;

    /** @var string */
    public $file;

    /** @var string[] */
    public $limited_currencies;

    /**
     * @var Alma\PrestaShop\Helpers\HookHelper
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

    /**
     * @var \PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer
     */
    protected $container;

    public function __construct()
    {
        $this->name = 'alma';
        $this->tab = 'payments_gateways';
        $this->version = '4.10.0';
        $this->author = 'Alma';
        $this->need_instance = false;
        $this->bootstrap = true;
        $controllers = ['payment', 'validation', 'ipn'];

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $controllers[] = 'insurance';
            $controllers[] = 'subscription';
            $controllers[] = 'cancellation';
        }

        $this->controllers = $controllers;
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
    }

    /**
     * @return array[]
     */
    public function dataTabs()
    {
        return [
            'alma' => [
                'name' => 'Alma',
                'parent' => null,
                'position' => null,
                'icon' => null,
            ],
            'AdminAlmaConfig' => [
                'name' => $this->l('Configure'),
                'parent' => 'alma',
                'position' => 1,
                'icon' => 'tune',
            ],
            'AdminAlmaCategories' => [
                'name' => $this->l('Excluded categories'),
                'parent' => 'alma',
                'position' => 2,
                'icon' => 'not_interested',
            ],
            'AdminAlmaRefunds' => [
                'name' => false,
                'parent' => 'alma',
                'position' => null,
                'icon' => null,
            ],
            'AdminAlmaShareOfCheckout' => [
                'name' => false,
                'parent' => 'alma',
                'position' => null,
                'icon' => null,
            ],
        ];
    }

    /**
     * Check parent install result then add log errors if any.
     *
     * @param $coreInstall
     *
     * @return bool as result of parent::install() method
     */
    private function checkCoreInstall($coreInstall)
    {
        if (!$coreInstall) {
            $logger = \Alma\PrestaShop\Factories\LoggerFactory::loggerClass();
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
     * Check if PS Account is compatible.
     *
     * @return void
     *
     * @throws \Alma\PrestaShop\Exceptions\CompatibilityPsAccountsException
     */
    private function checkPsAccountsPresence()
    {
        $configurationProxy = new \Alma\PrestaShop\Proxy\ConfigurationProxy();
        $toolsHelper = new \Alma\PrestaShop\Helpers\ToolsHelper();
        if ($configurationProxy->isDevMode()) {
            throw new \Alma\PrestaShop\Exceptions\CompatibilityPsAccountsException('[Alma] Debug mode is activated');
        }

        if (
            !class_exists(\Symfony\Component\Config\ConfigCache::class)
            || !class_exists(\PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer::class)
        ) {
            throw new \Alma\PrestaShop\Exceptions\CompatibilityPsAccountsException('[Alma] Classes don\'t exist for PS Account');
        }
        if (
            $toolsHelper->psVersionCompare('1.6', '<')
        ) {
            throw new \Alma\PrestaShop\Exceptions\CompatibilityPsAccountsException('[Alma] Prestashop version lower than 1.6');
        }
    }

    /**
     * Check if PS Account is installed and up to date, minimal version required 5.0.
     *
     * @return void
     *
     * @throws \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException
     * @throws \Alma\PrestaShop\Exceptions\CompatibilityPsAccountsException
     */
    private function checkPsAccountsCompatibility()
    {
        $this->checkPsAccountsPresence();
        $psAccountsModule = \Module::getInstanceByName('ps_accounts');
        if (!$psAccountsModule) {
            throw new \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException('[Alma] PS Account is not installed');
        }

        if ($psAccountsModule->version < self::PS_ACCOUNTS_VERSION_REQUIRED) {
            throw new \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException('[Alma] PS Account is not up to date, minimal version required ' . self::PS_ACCOUNTS_VERSION_REQUIRED);
        }
    }

    /**
     * Insert module into datable.
     *
     * @override
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    public function install()
    {
        $tabsHelper = new \Alma\PrestaShop\Helpers\Admin\TabsHelper();
        $almaBusinessDataRepository = new \Alma\PrestaShop\Repositories\AlmaBusinessDataRepository();

        try {
            $this->checkPsAccountsPresence();
            $psAccountsService = new \Alma\PrestaShop\Services\PsAccountsService($this, $this->context);
            $psAccountsService->install();
        } catch (\Alma\PrestaShop\Exceptions\CompatibilityPsAccountsException $e) {
            \Alma\PrestaShop\Factories\LoggerFactory::instance()->info($e->getMessage());
        }

        $coreInstall = parent::install();

        if (!$this->checkCoreInstall($coreInstall)
            || !$this->checkDependencies()
            || !$this->registerHooks()) {
            return false;
        }

        Tools::clearCache();

        // Enable Alma payment for all installed carriers in PrestaShop 1.7+
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->updateCarriersWithAlma();
        }

        $almaBusinessDataRepository->createTable();

        return $tabsHelper->installTabs($this->dataTabs());
    }

    /**
     * @return bool
     *
     * @throws PrestaShopException
     */
    public function uninstall()
    {
        $result = parent::uninstall() && \Alma\PrestaShop\Helpers\SettingsHelper::deleteAllValues();

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

        $tabsHelper = new \Alma\PrestaShop\Helpers\Admin\TabsHelper();

        return $result && $tabsHelper->uninstallTabs($this->dataTabs());
    }

    /**
     * Try to register mandatory hooks.
     *
     * @return bool
     */
    public function registerHooks()
    {
        $hook = new \Alma\PrestaShop\Helpers\HookHelper();
        $hooks = $hook->almaRegisterHooks();

        foreach ($hooks as $hook) {
            $this->registerHook($hook);
        }

        return true;
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    protected function requireAlmaAutoload()
    {
        // And the autoload here to make our Composer classes available everywhere!
        require_once _PS_MODULE_DIR_ . 'alma/lib/smarty.php';
        require_once _PS_MODULE_DIR_ . 'alma/vendor/autoload.php';
    }

    /**
     * @return void
     */
    public function hookActionAdminControllerInitBefore()
    {
        $this->requireAlmaAutoload();
    }

    /**
     * @return void
     */
    public function hookModuleRoutes()
    {
        $this->requireAlmaAutoload();
    }

    /**
     * @param $params
     *
     * @return mixed|null
     */
    public function hookDisplayProductPriceBlock($params)
    {
        return $this->runHookController('displayProductPriceBlock', $params);
    }

    /**
     * displayProductButtons is registered on PrestaShop 1.5 only, as displayProductPriceBlock wasn't available then.
     *
     * @param $params
     *
     * @return mixed|void|null
     */
    public function hookDisplayProductButtons($params)
    {
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            return $this->runHookController('displayProductPriceBlock', $params);
        }
    }

    /**
     * Hook to modify the order table before Ps 1.7.5
     *
     * @param $params
     *
     * @return mixed|null
     */
    public function hookActionAdminOrdersListingFieldsModifier($params)
    {
        return $this->runHookController('actionAdminOrdersListingFieldsModifier', $params);
    }

    /**
     * Hook to modify the order table after Ps 1.7.5
     *
     * @param $params
     *
     * @return mixed|null
     */
    public function hookActionOrderGridQueryBuilderModifier($params)
    {
        return $this->runHookController('actionOrderGridQueryBuilderModifier', $params);
    }

    /**
     * Hook to modify the order table after Ps 1.7.5
     *
     * @param $params
     *
     * @return mixed|null
     */
    public function hookActionOrderGridDefinitionModifier($params)
    {
        return $this->runHookController('actionOrderGridDefinitionModifier', $params);
    }

    /**
     * Hook action after add cart
     * Used for Event Alma Business Data
     *
     * @param $params
     *
     * @return mixed|null
     */
    public function hookActionCartSave($params)
    {
        return $this->runHookController('actionCartSave', $params);
    }

    /**
     * Hook action after validate order
     *
     * @param $params
     *
     * @return mixed|null
     */
    public function hookActionValidateOrder($params)
    {
        return $this->runHookController('actionValidateOrder', $params);
    }

    /**
     * Check php lib dependencies and versions.
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

        $opensslException = $this->l('Alma requires OpenSSL >= 1.0.1', 'alma');
        if (!defined('OPENSSL_VERSION_TEXT')) {
            $result = false;
            $this->_errors[] = $opensslException;
        }

        preg_match('/^(?:Libre|Open)SSL ([\d.]+)/', OPENSSL_VERSION_TEXT, $matches);
        if (empty($matches[1])) {
            $result = false;
            $this->_errors[] = $opensslException;
        }

        if (!version_compare($matches[1], '1.0.1', '>=')) {
            $result = false;
            $this->_errors[] = $opensslException;
        }

        return $result;
    }

    /**
     * @param $hookName
     * @param $params
     *
     * @return mixed|null
     */
    private function runHookController($hookName, $params)
    {
        $hookName = Tools::ucfirst(preg_replace('/[^a-zA-Z0-9]/', '', $hookName));

        require_once dirname(__FILE__) . "/controllers/hook/{$hookName}HookController.php";
        $controllerName = "Alma\PrestaShop\Controllers\Hook\\{$hookName}HookController";

        // check if override exist for hook controllers
        if (file_exists(dirname(__FILE__) . "/../../override/modules/alma/controllers/hook/{$hookName}HookController.php")) {
            require_once dirname(__FILE__) . "/../../override/modules/alma/controllers/hook/{$hookName}HookController.php";
            $controllerName = "Alma\PrestaShop\Controllers\Hook\\{$hookName}HookControllerOverride";
        }

        $controller = new $controllerName($this);

        if ($controller->canRun()) {
            return $controller->run($params);
        }

        return null;
    }

    /**
     * @return void
     */
    public function viewAccess()
    {
        // Simply redirect to the default module's configuration page
        $location = \Alma\PrestaShop\Helpers\LinkHelper::getAdminLinkAlmaDashboard();

        Tools::redirectAdmin($location);
    }

    /**
     * @return mixed|null
     */
    public function getContent()
    {
        $suggestPSAccounts = false;
        $isPsAccountsCompatible = true;

        try {
            $this->checkPsAccountsCompatibility();
        } catch (\Alma\PrestaShop\Exceptions\CompatibilityPsAccountsException $e) {
            $isPsAccountsCompatible = false;
        } catch (\PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException $e) {
            $suggestPSAccounts = true;
        }

        return $this->runHookController('getContent', ['isPsAccountsCompatible' => $isPsAccountsCompatible, 'suggestPSAccounts' => $suggestPSAccounts]);
    }

    /**
     * To handle hook Header for some Prestashop versions
     *
     * @param $params
     *
     * @return mixed|null
     */
    public function hookDisplayHeader($params)
    {
        return $this->hookHeader($params);
    }

    /**
     * @param $params
     *
     * @return mixed|null
     */
    public function hookHeader($params)
    {
        return $this->runHookController('frontHeader', $params);
    }

    /**
     * @param $params
     *
     * @return mixed|null
     */
    public function hookDisplayBackOfficeHeader($params)
    {
        return $this->runHookController('displayBackOfficeHeader', $params);
    }

    /**
     * @param $params
     *
     * @return mixed|null
     */
    public function hookPaymentOptions($params)
    {
        return $this->runHookController('paymentOptions', $params);
    }

    /**
     * @param $params
     *
     * @return mixed|null
     */
    public function hookDisplayPaymentEU($params)
    {
        $params['for_eu_compliance_module'] = true;

        return $this->runHookController('paymentOptions', $params);
    }

    /**
     * @param $params
     *
     * @return mixed|null
     */
    public function hookDisplayPayment($params)
    {
        return $this->runHookController('displayPayment', $params);
    }

    /**
     * Deprecated for version 1.7
     *
     * @param $params
     *
     * @return false|mixed|string|null
     */
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

    /**
     * @param $params
     *
     * @return mixed|string|null
     */
    // New name of displayPaymentReturn hook for 1.7
    public function hookPaymentReturn($params)
    {
        try {
            return $this->runHookController('displayPaymentReturn', $params);
        } catch (\Alma\PrestaShop\Exceptions\RenderPaymentException $e) {
            return '';
        }
    }

    /**
     * @param $params
     *
     * @return mixed|null
     */
    public function hookDisplayShoppingCartFooter($params)
    {
        return $this->runHookController('displayShoppingCartFooter', $params);
    }

    /**
     * @param $params
     *
     * @return mixed|null
     */
    public function hookDisplayAdminOrder($params)
    {
        return $this->runHookController('displayRefunds', $params);
    }

    /**
     * @param $params
     *
     * @return mixed|null
     */
    public function hookDisplayAdminOrderMain($params)
    {
        return $this->runHookController('displayRefunds', $params);
    }

    /**
     * @param $params
     *
     * @return mixed|null
     */
    public function hookActionOrderStatusPostUpdate($params)
    {
        return $this->runHookController('state', $params);
    }

    /**
     * Hook action DisplayAdminAfterHeader.
     */
    public function hookDisplayAdminAfterHeader($params)
    {
        return $this->runHookController('displayAdminAfterHeader', $params);
    }

    /**
     * @param $params
     *
     * @return void
     */
    public function hookActionObjectUpdateAfter($params)
    {
        $orderFactory = new Alma\PrestaShop\Factories\OrderFactory();
        $clientHelper = new Alma\PrestaShop\Helpers\ClientHelper();
        $carrierFactory = new Alma\PrestaShop\Factories\CarrierFactory();
        $actionObjectUpdateAfter = new Alma\PrestaShop\Controllers\Hook\ActionObjectUpdateAfter($orderFactory, $clientHelper, $carrierFactory);
        $actionObjectUpdateAfter->run($params);
    }
}
