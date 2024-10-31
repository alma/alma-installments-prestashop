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

namespace Alma\PrestaShop\Helpers;

use Alma\API\Entities\MerchantData\CmsFeatures;
use Alma\API\Entities\MerchantData\CmsInfo;
use Alma\PrestaShop\Builders\Factories\ModuleFactoryBuilder;
use Alma\PrestaShop\Builders\Helpers\SettingsHelperBuilder;
use Alma\PrestaShop\Factories\CmsFeaturesFactory;
use Alma\PrestaShop\Factories\CmsInfoFactory;
use Alma\PrestaShop\Forms\CartEligibilityAdminFormBuilder;
use Alma\PrestaShop\Forms\DebugAdminFormBuilder;
use Alma\PrestaShop\Forms\InpageAdminFormBuilder;
use Alma\PrestaShop\Forms\PnxAdminFormBuilder;
use Alma\PrestaShop\Forms\ProductEligibilityAdminFormBuilder;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CmsDataHelper
{
    /**
     * @var \Alma\PrestaShop\Helpers\ConfigurationHelper
     */
    protected $configHelper;
    /**
     * @var \Alma\PrestaShop\Helpers\ModuleHelper
     */
    protected $moduleHelper;
    /**
     * @var \Alma\PrestaShop\Helpers\ThemeHelper
     */
    protected $themeHelper;
    /**
     * @var \Alma\PrestaShop\Factories\ModuleFactory
     */
    protected $moduleFactory;
    /**
     * @var \Alma\PrestaShop\Helpers\SettingsHelper
     */
    protected $settingsHelper;
    /**
     * @var \Alma\PrestaShop\Factories\CmsInfoFactory
     */
    protected $cmsInfoFactory;
    /**
     * @var \Alma\PrestaShop\Factories\CmsFeaturesFactory
     */
    protected $cmsFeatureFactory;

    /**
     * CmsDataHelper constructor.
     */
    public function __construct()
    {
        $this->configHelper = new ConfigurationHelper();
        $this->moduleHelper = new ModuleHelper();
        $this->themeHelper = new ThemeHelper();
        $moduleFactoryBuilder = new ModuleFactoryBuilder();
        $this->moduleFactory = $moduleFactoryBuilder->getInstance();
        $settingsHelperBuilder = new SettingsHelperBuilder();
        $this->settingsHelper = $settingsHelperBuilder->getInstance();
        $this->cmsInfoFactory = new CmsInfoFactory();
        $this->cmsFeatureFactory = new CmsFeaturesFactory();
    }

    /**
     * @return CmsInfo
     */
    public function getCmsInfoArray()
    {
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

        return $this->cmsInfoFactory->create($cmsInfoDataArray);
    }

    /**
     * @return CmsFeatures
     */
    public function getCmsFeatureArray()
    {
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
            'is_multisite' => \Shop::isFeatureActive(),
        ];

        return $this->cmsFeatureFactory->create($cmsFeatureDataArray);
    }

    /**
     * @return array
     */
    private function getCountriesRestrictions()
    {
        // TODO : Need to implement this method with the db ps_module_country
        return [];
    }

    /**
     * @return string
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
}
