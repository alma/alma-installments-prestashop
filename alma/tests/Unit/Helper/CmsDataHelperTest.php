<?php

namespace Unit\Helper;

use Alma\API\Client;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Forms\CartEligibilityAdminFormBuilder;
use Alma\PrestaShop\Forms\DebugAdminFormBuilder;
use Alma\PrestaShop\Forms\InpageAdminFormBuilder;
use Alma\PrestaShop\Forms\PnxAdminFormBuilder;
use Alma\PrestaShop\Forms\ProductEligibilityAdminFormBuilder;
use Alma\PrestaShop\Helpers\CmsDataHelper;
use Alma\PrestaShop\Helpers\ModuleHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ThemeHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use PHPUnit\Framework\TestCase;

class CmsDataHelperTest extends TestCase
{
    protected $moduleHelper;
    protected $themeHelper;
    protected $moduleFactory;
    protected $settingsHelper;
    protected $cmsDataHelper;
    protected $toolsHelper;

    public function setUp()
    {
        $this->moduleHelper = $this->createMock(ModuleHelper::class);
        $this->themeHelper = $this->createMock(ThemeHelper::class);
        $this->moduleFactory = $this->createMock(ModuleFactory::class);
        $this->settingsHelper = $this->createMock(SettingsHelper::class);
        $this->toolsHelper = $this->createMock(ToolsHelper::class);
        $this->cmsDataHelper = new CmsDataHelper(
            $this->moduleHelper,
            $this->themeHelper,
            $this->moduleFactory,
            $this->settingsHelper,
            $this->toolsHelper
        );
    }

    public function tearDown()
    {
        $this->moduleHelper = null;
        $this->themeHelper = null;
        $this->moduleFactory = null;
        $this->settingsHelper = null;
        $this->cmsDataHelper = null;
        $this->toolsHelper = null;
    }

    /**
     * @return void
     */
    public function testGetCmsInfoArray()
    {
        $this->toolsHelper->method('getPsVersion')->willReturn('1.2.3');
        $this->moduleHelper->method('getModuleList')->willReturn(['moduleList']);
        $this->themeHelper->method('getThemeNameWithVersion')->willReturn('ThemeName');
        $this->moduleFactory->method('getModuleVersion')->willReturn('4.3.2');
        $expected = [
            'cms_name' => 'Prestashop',
            'cms_version' => '1.2.3',
            'third_parties_plugins' => ['moduleList'],
            'themes' => 'ThemeName',
            'language_name' => 'PHP',
            'language_version' => phpversion(),
            'alma_plugin_version' => '4.3.2',
            'alma_sdk_name' => 'ALMA-PHP-CLIENT',
            'alma_sdk_version' => Client::VERSION,
        ];

        $this->assertEquals($expected, $this->cmsDataHelper->getCmsInfoArray());
    }

    /**
     * @return void
     */
    public function testGetCmsFeatureArray()
    {
        $this->settingsHelper->method('getKey')->willReturnMap(
            [
                [SettingsHelper::ALMA_FULLY_CONFIGURED, null, false],
                [CartEligibilityAdminFormBuilder::ALMA_SHOW_ELIGIBILITY_MESSAGE, null, false],
                [ProductEligibilityAdminFormBuilder::ALMA_SHOW_PRODUCT_ELIGIBILITY, null, false],
                [PnxAdminFormBuilder::ALMA_FEE_PLANS, null, '{"general_1_0_0":{"enabled":"1"}}'],
                [InpageAdminFormBuilder::ALMA_ACTIVATE_INPAGE, null, true],
                [DebugAdminFormBuilder::ALMA_ACTIVATE_LOGGING, null, true],
                [ProductEligibilityAdminFormBuilder::ALMA_WIDGET_POSITION_SELECTOR, null, '#selectorCss'],
            ]
        );
        $expected = [
            'alma_enabled' => false,
            'widget_cart_activated' => false,
            'widget_product_activated' => false,
            'used_fee_plans' => ['general_1_0_0' => ['enabled' => '1']],
            'in_page_activated' => true,
            'log_activated' => true,
            'excluded_categories' => null,
            'specific_features' => [],
            'country_restriction' => [],
            'custom_widget_css' => (bool) '#selectorCss',
            'is_multisite' => \Shop::isFeatureActive(),
        ];

        $this->assertEquals($expected, $this->cmsDataHelper->getCmsFeatureArray());
    }
}
