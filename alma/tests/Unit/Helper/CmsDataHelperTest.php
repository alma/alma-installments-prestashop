<?php

namespace Unit\Helper;

use Alma\API\Client;
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
use Alma\PrestaShop\Model\AlmaModuleModel;
use Alma\PrestaShop\Model\ShopModel;
use PHPUnit\Framework\TestCase;

class CmsDataHelperTest extends TestCase
{
    protected $moduleHelper;
    protected $themeHelper;
    protected $almaModuleModel;
    protected $settingsHelper;
    protected $cmsDataHelper;
    protected $toolsHelper;
    /**
     * @var ShopModel
     */
    protected $shopModel;

    public function setUp()
    {
        $this->moduleHelper = $this->createMock(ModuleHelper::class);
        $this->themeHelper = $this->createMock(ThemeHelper::class);
        $this->almaModuleModel = $this->createMock(AlmaModuleModel::class);
        $this->settingsHelper = $this->createMock(SettingsHelper::class);
        $this->toolsHelper = $this->createMock(ToolsHelper::class);
        $this->shopModel = $this->createMock(ShopModel::class);
        $this->cmsDataHelper = new CmsDataHelper(
            $this->moduleHelper,
            $this->themeHelper,
            $this->almaModuleModel,
            $this->settingsHelper,
            $this->toolsHelper,
            $this->shopModel
        );
    }

    public function tearDown()
    {
        $this->moduleHelper = null;
        $this->themeHelper = null;
        $this->almaModuleModel = null;
        $this->settingsHelper = null;
        $this->cmsDataHelper = null;
        $this->toolsHelper = null;
        $this->shopModel = null;
    }

    /**
     * @return void
     */
    public function testGetCmsInfoArray()
    {
        $this->toolsHelper->method('getPsVersion')->willReturn('1.2.3');
        $this->moduleHelper->method('getModuleList')->willReturn(['moduleList']);
        $this->themeHelper->method('getThemeName')->willReturn('ThemeName');
        $this->themeHelper->method('getThemeVersion')->willReturn('2.4.9');
        $this->almaModuleModel->method('getVersion')->willReturn('4.3.2');
        $expected = [
            'cms_name' => 'Prestashop',
            'cms_version' => '1.2.3',
            'third_parties_plugins' => ['moduleList'],
            'theme_name' => 'ThemeName',
            'theme_version' => '2.4.9',
            'language_name' => 'PHP',
            'language_version' => phpversion(),
            'alma_plugin_version' => '4.3.2',
            'alma_sdk_name' => 'ALMA-PHP-CLIENT',
            'alma_sdk_version' => Client::VERSION,
        ];

        $this->assertEquals($expected, $this->cmsDataHelper->getCmsInfoArray());
    }

    /**
     * @dataProvider getCmsFeatureArrayDataProvider
     *
     * @return void
     */
    public function testGetCmsFeatureArray($jsonFeePlans, $getPosition, $expected)
    {
        $this->settingsHelper->method('getKey')->willReturnMap(
            [
                [SettingsHelper::ALMA_FULLY_CONFIGURED, null, false],
                [CartEligibilityAdminFormBuilder::ALMA_SHOW_ELIGIBILITY_MESSAGE, null, false],
                [ProductEligibilityAdminFormBuilder::ALMA_SHOW_PRODUCT_ELIGIBILITY, null, false],
                [PnxAdminFormBuilder::ALMA_FEE_PLANS, null, $jsonFeePlans],
                [InpageAdminFormBuilder::ALMA_ACTIVATE_INPAGE, null, true],
                [DebugAdminFormBuilder::ALMA_ACTIVATE_LOGGING, null, true],
                [ProductEligibilityAdminFormBuilder::ALMA_WIDGET_POSITION_SELECTOR, null, '#selectorCss'],
            ]
        );
        $this->shopModel->method('isMultisite')->willReturn(false);
        $this->almaModuleModel->method('getPosition')->willReturn($getPosition);

        $this->assertEquals($expected, $this->cmsDataHelper->getCmsFeatureArray());
    }

    /**
     * @return array[]
     */
    public function getCmsFeatureArrayDataProvider()
    {
        return [
            'With fee plans and getPosition' => [
                'jsonFeePlans' => '{"general_1_0_0":{"enabled":"1"}}',
                'getPosition' => '4',
                'expected' => [
                    'alma_enabled' => false,
                    'widget_cart_activated' => false,
                    'widget_product_activated' => false,
                    'used_fee_plans' => ['general_1_0_0' => ['enabled' => '1']],
                    'payment_method_position' => 4,
                    'in_page_activated' => true,
                    'log_activated' => true,
                    'excluded_categories' => null,
                    'specific_features' => [],
                    'country_restriction' => [],
                    'custom_widget_css' => (bool) '#selectorCss',
                    'is_multisite' => false,
                ],
            ],
            'Without fee plans and with getPosition' => [
                'jsonFeePlans' => '{}',
                'getPosition' => '3',
                'expected' => [
                    'alma_enabled' => false,
                    'widget_cart_activated' => false,
                    'widget_product_activated' => false,
                    'used_fee_plans' => null,
                    'payment_method_position' => 3,
                    'in_page_activated' => true,
                    'log_activated' => true,
                    'excluded_categories' => null,
                    'specific_features' => [],
                    'country_restriction' => [],
                    'custom_widget_css' => (bool) '#selectorCss',
                    'is_multisite' => false,
                ],
            ],
            'With fee plans and without getPosition' => [
                'jsonFeePlans' => '{"general_1_0_0":{"enabled":"1"}}',
                'getPosition' => '',
                'expected' => [
                    'alma_enabled' => false,
                    'widget_cart_activated' => false,
                    'widget_product_activated' => false,
                    'used_fee_plans' => ['general_1_0_0' => ['enabled' => '1']],
                    'payment_method_position' => 0,
                    'in_page_activated' => true,
                    'log_activated' => true,
                    'excluded_categories' => null,
                    'specific_features' => [],
                    'country_restriction' => [],
                    'custom_widget_css' => (bool) '#selectorCss',
                    'is_multisite' => false,
                ],
            ],
        ];
    }
}
