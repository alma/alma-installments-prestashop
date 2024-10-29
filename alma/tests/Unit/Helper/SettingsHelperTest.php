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

namespace Alma\PrestaShop\Tests\Unit\Helper;

use Alma\API\Entities\FeePlan;
use Alma\PrestaShop\Builders\Helpers\SettingsHelperBuilder;
use Alma\PrestaShop\Factories\CategoryFactory;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Forms\InpageAdminFormBuilder;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ShopHelper;
use Alma\PrestaShop\Helpers\ValidateHelper;
use PHPUnit\Framework\TestCase;

class SettingsHelperTest extends TestCase
{
    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;
    /**
     * @var ShopHelper
     */
    protected $shopHelperMock;
    /**
     * @var ConfigurationHelper
     */
    protected $configurationHelperMock;
    /**
     * @var (SettingsHelper&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $settingsHelperMock;
    /**
     * @var \Alma\PrestaShop\Factories\CategoryFactory|(\Alma\PrestaShop\Factories\CategoryFactory&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryFactoryMock;
    /**
     * @var \Alma\PrestaShop\Factories\ContextFactory|(\Alma\PrestaShop\Factories\ContextFactory&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextFactoryMock;
    /**
     * @var \Alma\PrestaShop\Helpers\ValidateHelper|(\Alma\PrestaShop\Helpers\ValidateHelper&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validateHelperMock;

    public function setUp()
    {
        $this->shopHelperMock = $this->createMock(ShopHelper::class);
        $this->configurationHelperMock = $this->createMock(ConfigurationHelper::class);
        $this->categoryFactoryMock = $this->createMock(CategoryFactory::class);
        $this->contextFactoryMock = $this->createMock(ContextFactory::class);
        $this->validateHelperMock = $this->createMock(ValidateHelper::class);
        $this->settingsHelper = new SettingsHelper(
            $this->shopHelperMock,
            $this->configurationHelperMock,
            $this->categoryFactoryMock,
            $this->contextFactoryMock,
            $this->validateHelperMock
        );

        $this->settingsHelperMock = $this->getMockBuilder(SettingsHelper::class)
            ->setConstructorArgs([
                $this->shopHelperMock,
                $this->configurationHelperMock,
                $this->categoryFactoryMock,
                $this->contextFactoryMock,
                $this->validateHelperMock,
            ])
            ->setMethods(['isInPageEnabled', 'getKey'])
            ->getMock();
    }

    /**
     * @return void
     */
    public function testGetDurationWithUnderscore()
    {
        $plan = new FeePlan(
            [
                'deferred_days' => 15,
                'deferred_months' => 0,
            ]
        );

        $duration = $this->settingsHelper->getDuration($plan);

        $this->assertEquals('15', $duration);

        $plan = new FeePlan(
            [
                'deferred_days' => 15,
                'deferred_months' => 2,
            ]
        );

        $duration = $this->settingsHelper->getDuration($plan);

        $this->assertEquals('75', $duration);
    }

    /**
     * @return void
     */
    public function testGetDurationDeferredDaysWithoutUnderscore()
    {
        $plan = new FeePlan(
            [
                'deferredDays' => 0,
                'deferredMonths' => 1,
            ]
        );

        $duration = $this->settingsHelper->getDuration($plan);

        $this->assertEquals('30', $duration);

        $plan = new FeePlan(
            [
                'deferredDays' => 15,
                'deferredMonths' => 1,
            ]
        );

        $duration = $this->settingsHelper->getDuration($plan);

        $this->assertEquals('45', $duration);
    }

    public function testIsDeferredWithUnderscore()
    {
        $plan = new FeePlan(
            [
                'deferred_days' => 15,
                'deferred_months' => 1,
            ]
        );

        $duration = $this->settingsHelper->isDeferred($plan);

        $this->assertTrue($duration);

        $plan = new FeePlan(
            [
                'deferred_days' => 0,
                'deferred_months' => 1,
            ]
        );

        $duration = $this->settingsHelper->isDeferred($plan);

        $this->assertTrue($duration);

        $plan = new FeePlan(
            [
                'deferred_days' => 15,
                'deferred_months' => 0,
            ]
        );

        $duration = $this->settingsHelper->isDeferred($plan);

        $this->assertTrue($duration);

        $plan = new FeePlan(
            [
                'deferred_days' => 0,
                'deferred_months' => 0,
            ]
        );

        $duration = $this->settingsHelper->isDeferred($plan);

        $this->assertFalse($duration);
    }

    public function testIsDeferred()
    {
        $plan = new FeePlan(
            [
                'deferredDays' => 15,
                'deferredMonths' => 1,
            ]
        );

        $duration = $this->settingsHelper->isDeferred($plan);

        $this->assertTrue($duration);

        $plan = new FeePlan(
            [
                'deferredDays' => 0,
                'deferredMonths' => 1,
            ]
        );

        $duration = $this->settingsHelper->isDeferred($plan);

        $this->assertTrue($duration);

        $plan = new FeePlan(
            [
                'deferredDays' => 15,
                'deferredMonths' => 0,
            ]
        );

        $duration = $this->settingsHelper->isDeferred($plan);

        $this->assertTrue($duration);

        $plan = new FeePlan(
            [
                'deferredDays' => 0,
                'deferredMonths' => 0,
            ]
        );

        $duration = $this->settingsHelper->isDeferred($plan);

        $this->assertFalse($duration);
    }

    public function testGetKeyWithValue()
    {
        $key = 'TEST_KEY';

        $shopHelperMock = \Mockery::mock(ShopHelper::class);
        $shopHelperMock->shouldReceive('getContextShopID')->with(true)->andReturn(1);
        $shopHelperMock->shouldReceive('getContextShopGroupID')->with(true)->andReturn(1);

        $configurationHelperMock = \Mockery::mock(ConfigurationHelper::class);
        $configurationHelperMock->shouldReceive('get')->with($key, null, 1, 1, null)->andReturn('valeurTest');
        $configurationHelperMock->shouldReceive('hasKey')->with($key, null, 1, 1)->andReturn(false);

        $settingsHelperBuilder = \Mockery::mock(SettingsHelperBuilder::class)->makePartial();
        $settingsHelperBuilder->shouldReceive('getShopHelper')->andReturn($shopHelperMock);
        $settingsHelperBuilder->shouldReceive('getConfigurationHelper')->andReturn($configurationHelperMock);
        $settingsHelper = $settingsHelperBuilder->getInstance();

        $result = $settingsHelper->getKey($key);

        $this->assertEquals('valeurTest', $result);
    }

    public function testGetKeyWithoutValue()
    {
        $key = 'TEST_KEY';
        $defaultValue = 'default_value';

        $shopHelperMock = \Mockery::mock(ShopHelper::class);
        $shopHelperMock->shouldReceive('getContextShopID')->with(true)->andReturn(1);
        $shopHelperMock->shouldReceive('getContextShopGroupID')->with(true)->andReturn(1);

        $configurationHelperMock = \Mockery::mock(ConfigurationHelper::class);
        $configurationHelperMock->shouldReceive('get')->with($key, null, 1, 1, $defaultValue)->andReturn(null);
        $configurationHelperMock->shouldReceive('hasKey')->with($key, null, 1, 1)->andReturn(false);

        $settingsHelperBuilder = \Mockery::mock(SettingsHelperBuilder::class)->makePartial();
        $settingsHelperBuilder->shouldReceive('getShopHelper')->andReturn($shopHelperMock);
        $settingsHelperBuilder->shouldReceive('getConfigurationHelper')->andReturn($configurationHelperMock);
        $settingsHelper = $settingsHelperBuilder->getInstance();

        $result = $settingsHelper->getKey($key, $defaultValue);

        $this->assertEquals($defaultValue, $result);
    }

    public function testIsPaymentTriggerEnabledByState()
    {
        $keyName = 'ALMA_PAYMENT_ON_TRIGGERING_ENABLED';

        $settingsHelperMock = $this->getSettingsHelperMockForIsPaymentTriggerEnabledByState($keyName);
        $settingsHelperMock->shouldReceive('getKey')->with($keyName)->andReturn(true);

        $this->assertTrue($settingsHelperMock->isPaymentTriggerEnabledByState());

        $settingsHelperMock->shouldReceive('getKey')->with($keyName)->andReturn(1);

        $this->assertTrue($settingsHelperMock->isPaymentTriggerEnabledByState());

        $settingsHelperMock->shouldReceive('getKey')->with($keyName)->andReturn('1');

        $this->assertTrue($settingsHelperMock->isPaymentTriggerEnabledByState());

        $configurationHelperMock = \Mockery::mock(ConfigurationHelper::class);
        $configurationHelperMock->shouldReceive('get')->with($keyName, null, 1, 1, '')->andReturn(false);
        $configurationHelperMock->shouldReceive('hasKey')->with($keyName, null, 1, 1)->andReturn(false);

        $settingsHelperMock = $this->getSettingsHelperMockForIsPaymentTriggerEnabledByState($keyName, false);
        $settingsHelperMock->shouldReceive('getKey')->with($keyName)->andReturn(false);

        $this->assertFalse($settingsHelperMock->isPaymentTriggerEnabledByState());

        $settingsHelperMock->shouldReceive('getKey')->with($keyName)->andReturn('0');

        $this->assertFalse($settingsHelperMock->isPaymentTriggerEnabledByState());

        $settingsHelperMock->shouldReceive('getKey')->with($keyName)->andReturn(0);

        $this->assertFalse($settingsHelperMock->isPaymentTriggerEnabledByState());
    }

    public function testIsDeferredTriggerLimitDaysTestStateTrue()
    {
        $keyName = 'ALMA_PAYMENT_ON_TRIGGERING_ENABLED';

        $settingsHelperMock = $this->getSettingsHelperMockForIsPaymentTriggerEnabledByState($keyName);
        $settingsHelperMock->shouldReceive('getKey')->with($keyName)->andReturn(true);
        $settingsHelperMock->shouldReceive('isPaymentTriggerEnabledByState')->andReturn(true);

        $feePlans = new \stdClass();
        $feePlans->maclee = new \stdClass();
        $feePlans->maclee->deferred_trigger_limit_days = '0';
        $feePlans->maclee2 = new \stdClass();
        $feePlans->maclee2->deferred_trigger_limit_days = '1';

        $this->assertTrue($settingsHelperMock->isDeferredTriggerLimitDays($feePlans, 'maclee2'));
        $this->assertFalse($settingsHelperMock->isDeferredTriggerLimitDays($feePlans, 'maclee'));
        $this->assertFalse($settingsHelperMock->isDeferredTriggerLimitDays($feePlans, 'cleinconnue'));
        $this->assertTrue($settingsHelperMock->isDeferredTriggerLimitDays(
            [
                'deferred_trigger_limit_days' => 1,
                'test' => 1,
            ]
        ));
        $this->assertFalse($settingsHelperMock->isDeferredTriggerLimitDays(
            [
                'deferred_trigger_limit_days' => 0,
                'test' => 0,
            ]
        ));
        $this->assertFalse($settingsHelperMock->isDeferredTriggerLimitDays(['test' => 1]));
        $this->assertFalse($settingsHelperMock->isDeferredTriggerLimitDays([]));
    }

    public function testIsDeferredTriggerLimitDaysTestStateFalse()
    {
        $keyName = 'ALMA_PAYMENT_ON_TRIGGERING_ENABLED';

        $settingsHelperMock = $this->getSettingsHelperMockForIsPaymentTriggerEnabledByState($keyName);
        $settingsHelperMock->shouldReceive('getKey')->with($keyName)->andReturn(true);
        $settingsHelperMock->shouldReceive('isPaymentTriggerEnabledByState')->andReturn(false);

        $feePlans = new \stdClass();
        $feePlans->maclee = new \stdClass();
        $feePlans->maclee->deferred_trigger_limit_days = '0';
        $feePlans->maclee2 = new \stdClass();
        $feePlans->maclee2->deferred_trigger_limit_days = '1';

        $this->assertFalse($settingsHelperMock->isDeferredTriggerLimitDays($feePlans, 'maclee2'));
        $this->assertFalse($settingsHelperMock->isDeferredTriggerLimitDays($feePlans, 'maclee'));
        $this->assertFalse($settingsHelperMock->isDeferredTriggerLimitDays($feePlans, 'cleinconnue'));
        $this->assertFalse($settingsHelperMock->isDeferredTriggerLimitDays(
            [
                'deferred_trigger_limit_days' => 1,
                'test' => 1,
            ]
        ));
        $this->assertFalse($settingsHelperMock->isDeferredTriggerLimitDays(
            [
                'deferred_trigger_limit_days' => 0,
                'test' => 0,
            ]
        ));
        $this->assertFalse($settingsHelperMock->isDeferredTriggerLimitDays(['test' => 1]));
        $this->assertFalse($settingsHelperMock->isDeferredTriggerLimitDays([]));
    }

    protected function getSettingsHelperMockForIsPaymentTriggerEnabledByState($keyName, $keyValue = true)
    {
        $shopHelperMock = \Mockery::mock(ShopHelper::class);
        $shopHelperMock->shouldReceive('getContextShopID')->with(true)->andReturn(1);
        $shopHelperMock->shouldReceive('getContextShopGroupID')->with(true)->andReturn(1);

        $configurationHelperMock = \Mockery::mock(ConfigurationHelper::class);
        $configurationHelperMock->shouldReceive('get')->with($keyName, null, 1, 1, '')->andReturn($keyValue);
        $configurationHelperMock->shouldReceive('hasKey')->with($keyName, null, 1, 1)->andReturn(false);

        $categoryFactoryMock = \Mockery::mock(CategoryFactory::class);
        $contextFactoryMock = \Mockery::mock(ContextFactory::class);
        $validateHelperMock = \Mockery::mock(ValidateHelper::class);

        return \Mockery::mock(SettingsHelper::class, [
            $shopHelperMock,
            $configurationHelperMock,
            $categoryFactoryMock,
            $contextFactoryMock,
            $validateHelperMock,
        ])->shouldAllowMockingProtectedMethods()->makePartial();
    }

    public function testKey()
    {
        $result = $this->settingsHelper->key('general', '1', '15', '0');
        $this->assertEquals('general_1_15_0', $result);
    }

    public function testKeyForInstallmentPlan()
    {
        $plan = new FeePlan(
            [
                'installmentsCount' => 1,
                'deferredDays' => 15,
                'deferredMonths' => 2,
            ]
        );

        $result = $this->settingsHelper->keyForInstallmentPlan($plan);

        $this->assertEquals('general_1_15_2', $result);
    }

    public function testKeyForFeePlan()
    {
        $plan = new FeePlan(
            [
                'kind' => 'pos',
                'installments_count' => 1,
                'deferred_days' => 15,
                'deferred_months' => 2,
            ]
        );

        $result = $this->settingsHelper->keyForFeePlan($plan);

        $this->assertEquals('pos_1_15_2', $result);
    }

    public function testGetExcludedCategories()
    {
        $settingsHelperMock = $this->getSettingsHelperMockForIsPaymentTriggerEnabledByState('ALMA_EXCLUDED_CATEGORIES');
        $settingsHelperMock->shouldReceive('getKey')->with('ALMA_EXCLUDED_CATEGORIES')->andReturn(null);

        $result = $settingsHelperMock->getExcludedCategories();
        $this->assertEquals([], $result);

        $settingsHelperMock = $this->getSettingsHelperMockForIsPaymentTriggerEnabledByState('ALMA_EXCLUDED_CATEGORIES');
        $settingsHelperMock->shouldReceive('getKey')->with('ALMA_EXCLUDED_CATEGORIES')->andReturn('null');

        $result = $settingsHelperMock->getExcludedCategories();
        $this->assertEquals([], $result);

        $settingsHelperMock = $this->getSettingsHelperMockForIsPaymentTriggerEnabledByState('ALMA_EXCLUDED_CATEGORIES');
        $settingsHelperMock->shouldReceive('getKey')->with('ALMA_EXCLUDED_CATEGORIES')->andReturn('["cate1","cate2"]');
        $result = $settingsHelperMock->getExcludedCategories();

        $this->assertEquals(['cate1', 'cate2'], $result);

        $settingsHelperMock = $this->getSettingsHelperMockForIsPaymentTriggerEnabledByState('ALMA_EXCLUDED_CATEGORIES');
        $settingsHelperMock->shouldReceive('getKey')->with('ALMA_EXCLUDED_CATEGORIES')->andReturn('["cate1""WrongJSON"]');
        $result = $settingsHelperMock->getExcludedCategories();

        $this->assertEquals([], $result);
    }

    /**
     * @dataProvider inPageSettingsDataProvider
     *
     * @return void
     */
    public function testGetInPageSettingsDefaultValues($isInPageEnabled, $paymentButtonSelector, $placeOrderButtonSelector)
    {
        $this->settingsHelperMock->expects($this->once())
            ->method('isInPageEnabled')
            ->willReturn($isInPageEnabled);
        $this->settingsHelperMock->expects($this->exactly(2))
            ->method('getKey')
            ->withConsecutive(
                [InpageAdminFormBuilder::ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR, InpageAdminFormBuilder::ALMA_INPAGE_DEFAULT_VALUE_PAYMENT_BUTTON_SELECTOR],
                [InpageAdminFormBuilder::ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR, InpageAdminFormBuilder::ALMA_INPAGE_DEFAULT_VALUE_PLACE_ORDER_BUTTON_SELECTOR]
            )
            ->willReturnOnConsecutiveCalls(
                $paymentButtonSelector,
                $placeOrderButtonSelector
            );

        $expected = [
            'enabled' => $isInPageEnabled,
            'paymentButtonSelector' => $paymentButtonSelector,
            'placeOrderButtonSelector' => $placeOrderButtonSelector,
        ];

        $this->assertEquals($expected, $this->settingsHelperMock->getInPageSettings());
    }

    /**
     * @return array[]
     */
    public function inPageSettingsDataProvider()
    {
        return [
            'Default values' => [
                'enabled' => false,
                'paymentButtonSelector' => InpageAdminFormBuilder::ALMA_INPAGE_DEFAULT_VALUE_PAYMENT_BUTTON_SELECTOR,
                'placeOrderButtonSelector' => InpageAdminFormBuilder::ALMA_INPAGE_DEFAULT_VALUE_PLACE_ORDER_BUTTON_SELECTOR,
            ],
            'in-page disable and selector custom' => [
                'enabled' => false,
                'paymentButtonSelector' => 'payment button selector custom',
                'placeOrderButtonSelector' => 'place order selector custom',
            ],
            'in-page enable and selector custom' => [
                'enabled' => true,
                'paymentButtonSelector' => 'payment button selector custom',
                'placeOrderButtonSelector' => 'place order selector custom',
            ],
        ];
    }
}
