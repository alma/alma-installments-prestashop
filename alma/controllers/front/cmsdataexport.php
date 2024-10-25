<?php

use Alma\API\Entities\MerchantData\CmsFeatures;
use Alma\API\Entities\MerchantData\CmsInfo;
use Alma\API\Lib\PayloadFormatter;
use Alma\PrestaShop\Builders\Factories\ModuleFactoryBuilder;
use Alma\PrestaShop\Builders\Helpers\SettingsHelperBuilder;
use Alma\PrestaShop\Forms\CartEligibilityAdminFormBuilder;
use Alma\PrestaShop\Forms\DebugAdminFormBuilder;
use Alma\PrestaShop\Forms\InpageAdminFormBuilder;
use Alma\PrestaShop\Forms\PnxAdminFormBuilder;
use Alma\PrestaShop\Forms\ProductEligibilityAdminFormBuilder;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ModuleHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ThemeHelper;
use Alma\PrestaShop\Traits\AjaxTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * AlmaCmsDataExportModuleFrontController
 */
class AlmaCmsDataExportModuleFrontController extends ModuleFrontController
{
    use AjaxTrait;

    private $configHelper;
    /**
     * @var ModuleHelper
     */
    protected $moduleHelper;
    /**
     * @var ThemeHelper
     */
    protected $themeHelper;
    /**
     * @var ModuleFactoryBuilder
     */
    protected $moduleFactory;
    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    public function __construct()
    {
        parent::__construct();
        $this->configHelper = new ConfigurationHelper();
        $this->moduleHelper = new ModuleHelper();
        $this->themeHelper = new ThemeHelper();
        $moduleFactoryBuilder = new ModuleFactoryBuilder();
        $this->moduleFactory = $moduleFactoryBuilder->getInstance();
        $settingsHelperBuilder = new SettingsHelperBuilder();
        $this->settingsHelper = $settingsHelperBuilder->getInstance();
    }

    public function postProcess()
    {
        parent::postProcess();

        $cmsInfoDataArray = [
            'cms_name' => 'Prestashop',
            'cms_version' => _PS_VERSION_,
            'third_parties_plugins' => $this->moduleHelper->getModuleList(),
            'themes' => $this->themeHelper->getThemeNameWithVersion(),
            'language_name' => 'PHP',
            'language_version' => phpversion(),
            'alma_plugin_version' => $this->moduleFactory->getModuleVersion(),
            'alma_sdk_name' => 'ALMA-PHP-CLIENT',
            'alma_sdk_version' => $this->getPhpClientVersion(),
        ];

        $cmsFeatureDataArray = [
            'alma_enabled' => (bool) (int) $this->configHelper->get(SettingsHelper::ALMA_FULLY_CONFIGURED), // clef fully configured
            'widget_cart_activated' => (bool) (int) $this->configHelper->get(CartEligibilityAdminFormBuilder::ALMA_SHOW_ELIGIBILITY_MESSAGE),
            'widget_product_activated' => (bool) (int) $this->configHelper->get(ProductEligibilityAdminFormBuilder::ALMA_SHOW_PRODUCT_ELIGIBILITY),
            'used_fee_plans' => json_decode($this->configHelper->get(PnxAdminFormBuilder::ALMA_FEE_PLANS), true),
            //'payment_method_position' => null, // not applicable - position is set in the used_fee_plans
            'in_page_activated' => (bool) (int) $this->configHelper->get(InpageAdminFormBuilder::ALMA_ACTIVATE_INPAGE),
            'log_activated' => (bool) (int) $this->configHelper->get(DebugAdminFormBuilder::ALMA_ACTIVATE_LOGGING),
            'excluded_categories' => $this->settingsHelper->getCategoriesExcludedNames(),
            //'excluded_categories_activated' => '',// not applicable - it's not possible to disable the exclusion
            'specific_features' => [], // no specific features in Prestashop
            'country_restriction' => $this->getCountriesRestrictions(),
            'custom_widget_css' => $this->configHelper->get(ProductEligibilityAdminFormBuilder::ALMA_WIDGET_POSITION_SELECTOR),
            'is_multisite' => Shop::isFeatureActive(),
        ];
        $cmsInfo = new CmsInfo($cmsInfoDataArray);
        $cmsFeature = new CmsFeatures($cmsFeatureDataArray);
        $payload = (new PayloadFormatter())->formatConfigurationPayload($cmsInfo, $cmsFeature);
        $this->ajaxRenderAndExit(json_encode(['success' => $payload]));
    }

    private function getCountriesRestrictions()
    {
        return [];
    }

    /**
     * @return mixed|string
     */
    private function getPhpClientVersion()
    {
        $installedFile = _PS_MODULE_DIR_ . 'alma/vendor/composer/installed.json';

        if (file_exists($installedFile)) {
            $installedData = json_decode(file_get_contents($installedFile), true);
            foreach ($installedData as $packages) {
                foreach ($packages as $package) {
                    if ($package['name'] === 'alma/alma-php-client') {
                        return $package['version'];
                    }
                }
            }
        }

        return '';
    }

    public function getTestData()
    {
        return $this->configHelper->get('TEST_DATA');
    }
}
