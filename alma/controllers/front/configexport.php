<?php

use Alma\API\Lib\PayloadFormatter;
use Alma\PrestaShop\Traits\AjaxTrait;
use Alma\API\Entities\MerchantData\CmsInfo;
use Alma\API\Entities\MerchantData\CmsFeatures;
use Alma\PrestaShop\Helpers\ConfigurationHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * AlmaConfiguration_ExportModuleFrontController
 */
class AlmaConfigExportModuleFrontController extends ModuleFrontController
{
    use AjaxTrait;

    private $configHelper;

    public function __construct()
    {
        parent::__construct();
        $this->configHelper = new ConfigurationHelper();
    }

    public function postProcess()
    {
        parent::postProcess();

        $cmsInfoDataArray = [
            'cms_name' => 'Prestashop',
            'cms_version' => _PS_VERSION_,
            'third_parties_plugins' => $this->getModuleListe(),
            'themes' => $this->getThemeNameWithVersion(),
            'language_name' => 'PHP',
            'language_version' => phpversion(),
            'alma_plugin_version' => $this->getAlmaModuleVersion(),
            'alma_sdk_name' => 'ALMA-PHP-CLIENT',
            'alma_sdk_version' => $this->getPhpClientVersion(),
        ];

        $cmsFeatureDataArray = [
            'alma_enabled' => (bool)(int)$this->configHelper->get('ALMA_FULLY_CONFIGURED'), // clef fully configured
            'widget_cart_activated' => (bool)(int)$this->configHelper->get('ALMA_SHOW_ELIGIBILITY_MESSAGE'),
            'widget_product_activated' => (bool)(int)$this->configHelper->get('ALMA_SHOW_PRODUCT_ELIGIBILITY'),
            'used_fee_plans' => json_decode($this->configHelper->get('ALMA_FEE_PLANS'), true),
            //'payment_method_position' => null, // not applicable - position is set in the used_fee_plans
            'in_page_activated' => (bool)(int)$this->configHelper->get('ALMA_ACTIVATE_INPAGE'),
            'log_activated' => (bool)(int)$this->configHelper->get('ALMA_ACTIVATE_LOGGING'),
            'excluded_categories' => $this->getCategoriesName(),
            //'excluded_categories_activated' => '',// not applicable - it's not possible to disable the exclusion
            'specific_features' => [], // no scpecific features in Prestashop
            'country_restriction' => $this->getCountriesRestrictions(),
            'custom_widget_css' => $this->configHelper->get('ALMA_WIDGET_POSITION_SELECTOR'),
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

    private function getCategoriesName()
    {
        $categories = $this->configHelper->get('ALMA_EXCLUDED_CATEGORIES');
        if (!$categories) {
            return [];
        }

        $categoryNames = [];

        foreach (json_decode($categories) as $id) {
            // TODO extract to a helper to test
            $category = new Category($id, Context::getContext()->language->id);
            if (Validate::isLoadedObject($category)) {
                $categoryNames[] = $category->name;
            }
        }
        return $categoryNames;
    }

    private function getThemeNameWithVersion()
    {
        $themeName = Context::getContext()->shop->theme_name;
        $themeConfigPath = _PS_THEME_DIR_ . 'config/theme.yml';

        // WARNING : NOT COMPATIBLE WITH PS 1.6
        if (file_exists($themeConfigPath)) {
            $themeConfig = \Symfony\Component\Yaml\Yaml::parseFile($themeConfigPath);
            $themeVersion = $themeConfig['version'] ?: 'undefined';
            $themeName = $themeName . ' ' . $themeVersion;
        }
        return $themeName;
    }

    private function getModuleListe()
    {
        $modulesArray = [];
        $modules = Module::getModulesInstalled();
        foreach ($modules as $module) {
            $modulesArray[] = [
                'name' => $module['name'],
                'version' => $module['version'],
            ];
        }
        return $modulesArray;
    }

    private function getAlmaModuleVersion()
    {
        $module = Module::getInstanceByName('alma');
        return $module->version;
    }

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

    public function setConfigHelper($configHelperClass)
    {
        $this->configHelper = $configHelperClass;
    }

    public function getTestData()
    {
        return $this->configHelper->get('TEST_DATA');
    }
}
