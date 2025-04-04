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

use Alma\API\Client;
use Alma\PrestaShop\Builders\Helpers\SettingsHelperBuilder;
use Alma\PrestaShop\Forms\CartEligibilityAdminFormBuilder;
use Alma\PrestaShop\Forms\DebugAdminFormBuilder;
use Alma\PrestaShop\Forms\InpageAdminFormBuilder;
use Alma\PrestaShop\Forms\PnxAdminFormBuilder;
use Alma\PrestaShop\Forms\ProductEligibilityAdminFormBuilder;
use Alma\PrestaShop\Model\AlmaModuleModel;
use Alma\PrestaShop\Model\ShopModel;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CmsDataHelper
{
    const ALMA_CMSDATA_DATE = 'ALMA_CMSDATA_DATE';
    /**
     * @var ModuleHelper
     */
    protected $moduleHelper;
    /**
     * @var ThemeHelper
     */
    protected $themeHelper;
    /**
     * @var AlmaModuleModel
     */
    protected $almaModuleModel;
    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;
    /**
     * @var ToolsHelper
     */
    protected $toolsHelper;
    /**
     * @var ShopModel
     */
    protected $shopModel;

    /**
     * @param ModuleHelper $moduleHelper
     * @param ThemeHelper $themeHelper
     * @param AlmaModuleModel $almaModuleModel
     * @param SettingsHelper $settingsHelper
     * @param ToolsHelper $toolsHelper
     * @param ShopModel $shopModel
     */
    public function __construct(
        $moduleHelper = null,
        $themeHelper = null,
        $almaModuleModel = null,
        $settingsHelper = null,
        $toolsHelper = null,
        $shopModel = null
    ) {
        if (!$moduleHelper) {
            $moduleHelper = new ModuleHelper();
        }
        $this->moduleHelper = $moduleHelper;

        if (!$themeHelper) {
            $themeHelper = new ThemeHelper();
        }
        $this->themeHelper = $themeHelper;

        if (!$almaModuleModel) {
            $almaModuleModel = new AlmaModuleModel();
        }
        $this->almaModuleModel = $almaModuleModel;

        if (!$settingsHelper) {
            $settingsHelper = (new SettingsHelperBuilder())->getInstance();
        }
        $this->settingsHelper = $settingsHelper;

        if (!$toolsHelper) {
            $toolsHelper = new ToolsHelper();
        }
        $this->toolsHelper = $toolsHelper;

        if (!$shopModel) {
            $shopModel = new ShopModel();
        }
        $this->shopModel = $shopModel;
    }

    /**
     * @return array
     */
    public function getCmsInfoArray()
    {
        return [
            'cms_name' => 'Prestashop',
            'cms_version' => $this->toolsHelper->getPsVersion(),
            'third_parties_plugins' => $this->moduleHelper->getModuleList(),
            'theme_name' => $this->themeHelper->getThemeName(),
            'theme_version' => $this->themeHelper->getThemeVersion(),
            'language_name' => 'PHP',
            'language_version' => phpversion(),
            'alma_plugin_version' => $this->almaModuleModel->getVersion(),
            'alma_sdk_name' => 'ALMA-PHP-CLIENT',
            'alma_sdk_version' => Client::VERSION,
        ];
    }

    /**
     * @return array
     */
    public function getCmsFeatureArray()
    {
        return [
            'alma_enabled' => (bool) (int) $this->settingsHelper->getKey(SettingsHelper::ALMA_FULLY_CONFIGURED), // clef fully configured
            'widget_cart_activated' => (bool) (int) $this->settingsHelper->getKey(CartEligibilityAdminFormBuilder::ALMA_SHOW_ELIGIBILITY_MESSAGE),
            'widget_product_activated' => (bool) (int) $this->settingsHelper->getKey(ProductEligibilityAdminFormBuilder::ALMA_SHOW_PRODUCT_ELIGIBILITY),
            'used_fee_plans' => $this->getUsedFeePlans(),
            'payment_method_position' => (int) $this->almaModuleModel->getPosition(),
            'in_page_activated' => (bool) (int) $this->settingsHelper->getKey(InpageAdminFormBuilder::ALMA_ACTIVATE_INPAGE),
            'log_activated' => (bool) (int) $this->settingsHelper->getKey(DebugAdminFormBuilder::ALMA_ACTIVATE_LOGGING),
            'excluded_categories' => $this->settingsHelper->getCategoriesExcludedNames(),
            'specific_features' => [], // no specific features in Prestashop
            'country_restriction' => $this->getCountriesRestrictions(),
            'custom_widget_css' => (bool) $this->settingsHelper->getKey(ProductEligibilityAdminFormBuilder::ALMA_WIDGET_POSITION_SELECTOR),
            'is_multisite' => $this->shopModel->isMultisite(),
        ];
    }

    /**
     * @return array
     */
    private function getCountriesRestrictions()
    {
        return [];
    }

    /**
     * @return array|null
     */
    private function getUsedFeePlans()
    {
        $feePlans = json_decode($this->settingsHelper->getKey(PnxAdminFormBuilder::ALMA_FEE_PLANS), true);

        if (empty($feePlans)) {
            return null;
        }

        return $feePlans;
    }
}
