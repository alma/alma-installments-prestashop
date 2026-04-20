<?php
/**
 * 2018-2026 Alma SAS.
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
 * @copyright 2018-2026 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

use PrestaShop\Module\Alma\Application\Service\AssetService;
use PrestaShop\Module\Alma\Application\Service\ModuleInstallerService;
use PrestaShop\Module\Alma\Application\Service\ModuleService;
use PrestaShop\Module\Alma\Application\Service\WidgetFrontendService;
use PrestaShop\Module\Alma\Infrastructure\Factory\HookServiceFactory;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PsAccountsInstaller\Installer\Installer;
use PrestaShopBundle\Translation\TranslatorInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

// Autoload here for the module definition
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class Alma extends PaymentModule implements WidgetInterface
{
    public $_path;
    public $local_path;

    /** @var string */
    public $file;

    /** @var string[] */
    public $limited_currencies;

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
        $this->version = '5.0.0';
        $this->author = 'Alma';
        $this->need_instance = false;
        $this->bootstrap = true;

        $this->controllers = ['payment', 'validation', 'ipn'];
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->module_key = 'ad25114b1fb02d9d8b8787b992a0ccdb';
        $this->limited_currencies = ['EUR'];

        $this->ps_versions_compliancy = ['min' => '1.7.8.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->trans('1x 2x 3x 4x, D+15 or D+30 Alma - Payment in instalments and deferred');
        $this->description = $this->trans('Offer an easy and safe installments payments option to your customers');
        $this->confirmUninstall = $this->trans('Are you sure you want to deactivate Alma payments from your shop?');

        $this->file = __FILE__;
    }

    /**
     * Executed during the installation module.
     * return always need begin with parent::install()
     *
     * @override
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    public function install(): bool
    {
        $languageRepository = new LanguageRepository();
        $psAccountsInstaller = new Installer('5.3');
        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');
        $moduleService = new ModuleService(
            $this,
            $languageRepository
        );
        $installerService = new ModuleInstallerService(
            $moduleService,
            Db::getInstance(),
            $psAccountsInstaller,
            $translator
        );

        // TODO : Check multi-shop functionnalities (https://devdocs.prestashop-project.org/1.7/development/multistore/)
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return parent::install() && $installerService->install();
    }

    /**
     * @return bool
     */
    public function uninstall(): bool
    {
        return parent::uninstall();
    }

    /**
     * Redirect to the configuration page when clicking on the "Configure" button of the module in the back office
     * Because the configuration page use a legacy controller thanks to tab, we need to redirect to it instead of using the getContent function to display the configuration page
     * @return void
     */
    public function getContent(): void
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminAlmaSettings'));
    }

    /**
     * Load assets on the front office
     * @return void
     */
    public function hookActionFrontControllerSetMedia(): bool
    {
        $assetService = new AssetService(
            $this,
            $this->context
        );
        return $assetService->checkAndLoadAssets();
    }

    /**
     * Enables the new translation system for Prestashop 1.7.6 and later.
     * @return bool
     */
    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    /**
     * Display widget in the cart page
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayShoppingCartFooter(array $params): string
    {
        return $this->renderWidget(WidgetFrontendService::WIDGET_HOOK_SHOPPING_CART_FOOTER, $params);
    }

    /**
     * Display widget in the product page
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayProductPriceBlock(array $params): string
    {
        if (($params['type'] ?? '') !== 'after_price') {
            return '';
        }

        return $this->renderWidget(WidgetFrontendService::WIDGET_HOOK_PRODUCT_PRICE_BLOCK, $params);
    }

    /**
     * Display widget with WidgetInterface
     *
     * @param string $hookName
     * @param array $configuration
     * @return string
     */
    public function renderWidget($hookName, array $configuration): string
    {
        $widgetFrontendService = HookServiceFactory::createWidgetService($this->context);
        return $widgetFrontendService->renderWidget($hookName);
    }

    /**
     * We return an empty array because we don't use this function to pass variables to the widget,
     * we use the WidgetFrontendService::getWidgetVariables function instead to move intelligence
     *
     * @param string $hookName
     * @param array $configuration
     * @return array
     */
    public function getWidgetVariables($hookName, array $configuration): array
    {
        return [];
    }
}
