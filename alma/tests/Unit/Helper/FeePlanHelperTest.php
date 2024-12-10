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

use Alma\API\Endpoints\Results\Eligibility;
use Alma\API\Entities\FeePlan;
use Alma\PrestaShop\Builders\Helpers\FeePlanHelperBuilder;
use Alma\PrestaShop\Exceptions\PnxFormException;
use Alma\PrestaShop\Factories\EligibilityFactory;
use Alma\PrestaShop\Helpers\FeePlanHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Proxy\ToolsProxy;
use PHPUnit\Framework\TestCase;

class FeePlanHelperTest extends TestCase
{
    /**
     * @var \Alma\PrestaShop\Helpers\SettingsHelper
     */
    protected $settingsHelperMock;
    /**
     * @var \Alma\PrestaShop\Factories\EligibilityFactory
     */
    protected $eligibilityFactoryMock;
    /**
     * @var \Alma\PrestaShop\Helpers\PriceHelper
     */
    protected $priceHelperMock;
    /**
     * @var \Alma\PrestaShop\Proxy\ToolsProxy
     */
    protected $toolsProxyMock;
    /**
     * @var \Alma\PrestaShop\Helpers\FeePlanHelper
     */
    protected $feePlanHelper;
    /**
     * @var \Alma\PrestaShop\Tests\Unit\Helper\FeePlansDataProvider
     */
    protected $feePlansDataProvider;

    public function setUp()
    {
        $this->settingsHelperMock = $this->createMock(SettingsHelper::class);
        $this->eligibilityFactoryMock = $this->createMock(EligibilityFactory::class);
        $this->priceHelperMock = $this->createMock(PriceHelper::class);
        $this->toolsProxyMock = $this->createMock(ToolsProxy::class);

        $this->feePlansDataProvider = new FeePlansDataProvider();
        $this->feePlanHelper = new FeePlanHelper(
            $this->settingsHelperMock,
            $this->eligibilityFactoryMock,
            $this->priceHelperMock,
            $this->toolsProxyMock
        );
    }

    public function testCheckFeePlans()
    {
        $feePlan1 = new FeePlan(
            [
                'installments_count' => 1,
                'kind' => null,
                'deferred_months' => null,
                'deferred_days' => null,
                'deferred_trigger_limit_days' => null,
                'max_purchase_amount' => null,
                'min_purchase_amount' => null,
                'allowed' => null,
                'merchant_fee_variable' => null,
                'merchant_fee_fixed' => null,
                'customer_fee_variable' => null,
                'customer_landing_rate' => null,
                'customer_fee_fixed' => null,
                'enabled' => 1,
            ]
        );
        $feePlans = json_encode(
            [
               $feePlan1,
                new FeePlan(
                    ['installments_count' => 2, 'enabled' => 0]
                ),
            ]
        );

        $settingsHelper = \Mockery::mock(SettingsHelper::class)->makePartial();
        $settingsHelper->shouldReceive('getAlmaFeePlans')->andReturn($feePlans);

        $feePlanHelperBuilder = \Mockery::mock(FeePlanHelperBuilder::class)->makePartial();
        $feePlanHelperBuilder->shouldReceive('getSettingsHelper')->andReturn($settingsHelper);

        $feePlanHelper = $feePlanHelperBuilder->getInstance();
        $result = $feePlanHelper->checkFeePlans();

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]->installments_count);

        $settingsHelper = \Mockery::mock(SettingsHelper::class)->makePartial();
        $settingsHelper->shouldReceive('getAlmaFeePlans')->andReturn(json_encode([]));

        $feePlanHelperBuilder = \Mockery::mock(FeePlanHelperBuilder::class)->makePartial();
        $feePlanHelperBuilder->shouldReceive('getSettingsHelper')->andReturn($settingsHelper);

        $feePlanHelper = $feePlanHelperBuilder->getInstance();
        $result = $feePlanHelper->checkFeePlans();

        $this->assertCount(0, $result);
    }

    public function testGetNotEligibleFeePlans()
    {
        $eligibility = new Eligibility(
            [
                'installments_count' => 1,
                'deferred_days' => 0,
                'deferred_months' => 0,
                'eligible' => true,
                'constraints' => [
                    'purchase_amount' => [
                        'minimum' => 10,
                        'maximum' => 200,
                    ],
                ],
            ]
        );

        $eligibilityFactory = \Mockery::mock(EligibilityFactory::class)->makePartial();
        $eligibilityFactory->shouldReceive('createEligibility')->andReturn($eligibility);

        $installementData = [
            'installmentsCount' => 1,
            'deferredDays' => 0,
            'deferredMonths' => 0,
        ];

        $settingsHelper = \Mockery::mock(SettingsHelper::class)->makePartial();
        $settingsHelper->shouldReceive('getDataFromKey')->andReturn($installementData);

        $feePlanHelperBuilder = \Mockery::mock(FeePlanHelperBuilder::class)->makePartial();
        $feePlanHelperBuilder->shouldReceive('getEligibilityFactory')->andReturn($eligibilityFactory);
        $feePlanHelperBuilder->shouldReceive('getSettingsHelper')->andReturn($settingsHelper);

        $feePlanHelper = $feePlanHelperBuilder->getInstance();

        $feePlans = [
            new FeePlan(['min' => 1, 'max' => 200]),
        ];

        $this->assertEquals([$eligibility], $feePlanHelper->getNotEligibleFeePlans($feePlans, 300));
    }

    public function testGetEligibleFeePlans()
    {
        $installementData = [
            'installmentsCount' => 1,
            'deferredDays' => 0,
            'deferredMonths' => 0,
        ];

        $settingsHelper = \Mockery::mock(SettingsHelper::class)->makePartial();
        $settingsHelper->shouldReceive('getDataFromKey')->andReturn($installementData);

        $feePlanHelperBuilder = \Mockery::mock(FeePlanHelperBuilder::class)->makePartial();
        $feePlanHelperBuilder->shouldReceive('getSettingsHelper')->andReturn($settingsHelper);

        $feePlanHelper = $feePlanHelperBuilder->getInstance();

        $feePlans = [
            new FeePlan(['min' => 1, 'max' => 1000]),
            new FeePlan(['min' => 1, 'max' => 200]),
        ];

        $this->assertEquals([$installementData], $feePlanHelper->getEligibleFeePlans($feePlans, 300));
    }

    public function testGetEligibleFeePlansOnLimitMinMax()
    {
        $installmentCountOne = [
            'installmentsCount' => 1,
            'deferredDays' => 0,
            'deferredMonths' => 0,
        ];
        $installmentCountThree = [
            'installmentsCount' => 3,
            'deferredDays' => 0,
            'deferredMonths' => 0,
        ];

        $installmentDataArray = [
            $installmentCountOne,
            $installmentCountThree,
        ];

        $this->settingsHelperMock->expects($this->exactly(2))->method('getDataFromKey')->willReturnOnConsecutiveCalls($installmentCountOne, $installmentCountThree);

        $feePlans = [
            'general_1_0_0' => new FeePlan(['min' => 100, 'max' => 1000]),
            'general_3_0_0' => new FeePlan(['min' => 50, 'max' => 100]),
        ];

        $this->assertEquals($installmentDataArray, $this->feePlanHelper->getEligibleFeePlans($feePlans, 100));
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\PnxFormException
     */
    public function testCheckLimitsSaveFeePlanWithWrongMinAmountInPnxThrowException()
    {
        $feePlans = [
            $this->feePlansDataProvider->planPayNow(),
            $this->feePlansDataProvider->planP2x(),
            $this->feePlansDataProvider->planP3x(),
            $this->feePlansDataProvider->planP4x(),
            $this->feePlansDataProvider->planDeferred15(),
        ];
        $this->settingsHelperMock->expects($this->exactly(2))
            ->method('keyForFeePlan')
            ->willReturnOnConsecutiveCalls(
                'general_1_0_0',
                'general_2_0_0'
            );
        $this->settingsHelperMock->expects($this->once())
            ->method('isDeferred')
            ->willReturnOnConsecutiveCalls(false, false, false);
        $this->priceHelperMock->expects($this->exactly(4))
            ->method('convertPriceToCents')
            ->withConsecutive(
                [1],
                [2000],
                [50],
                [2000]
            )
            ->willReturnOnConsecutiveCalls(100, 200000, 4000, 200000);
        $this->toolsProxyMock->expects($this->exactly(6))
            ->method('getValue')
            ->withConsecutive(
                ['ALMA_general_1_0_0_MIN_AMOUNT'],
                ['ALMA_general_1_0_0_MAX_AMOUNT'],
                ['ALMA_general_1_0_0_ENABLED_ON'],

                ['ALMA_general_2_0_0_MIN_AMOUNT'],
                ['ALMA_general_2_0_0_MAX_AMOUNT'],
                ['ALMA_general_2_0_0_ENABLED_ON']
            )
            ->willReturnOnConsecutiveCalls(
                1, 2000, true,
                50, 2000, true
            );
        $this->priceHelperMock->expects($this->exactly(4))
            ->method('convertPriceFromCents')
            ->willReturnOnConsecutiveCalls(1, 2000, 50, 2000);
        $this->expectException(PnxFormException::class);
        $this->expectExceptionMessage('Minimum amount for 2-installment plan must be within 50 and 2000.');
        $this->feePlanHelper->checkLimitsSaveFeePlans($feePlans);
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\PnxFormException
     */
    public function testCheckLimitsSaveFeePlanWithWrongMinAmountInDeferredDaysThrowException()
    {
        $feePlans = [
            $this->feePlansDataProvider->planPayNow(),
            $this->feePlansDataProvider->planP2x(),
            $this->feePlansDataProvider->planP3x(),
            $this->feePlansDataProvider->planP4x(),
            $this->feePlansDataProvider->planDeferred15(),
        ];
        $this->settingsHelperMock->expects($this->exactly(5))
            ->method('keyForFeePlan')
            ->willReturnOnConsecutiveCalls(
                'general_1_0_0',
                'general_2_0_0',
                'general_3_0_0',
                'general_4_0_0',
                'general_1_15_0'
            );
        $this->settingsHelperMock->expects($this->exactly(3))
            ->method('isDeferred')
            ->willReturnOnConsecutiveCalls(false, false, false);
        $this->priceHelperMock->expects($this->exactly(10))
            ->method('convertPriceToCents')
            ->withConsecutive(
                [1],
                [2000],
                [50],
                [2000],
                [50],
                [2000],
                [50],
                [2000],
                [40],
                [2000]
            )
            ->willReturnOnConsecutiveCalls(100, 200000, 5000, 200000, 5000, 200000, 5000, 200000, 4000, 200000);
        $this->toolsProxyMock->expects($this->exactly(15))
            ->method('getValue')
            ->withConsecutive(
                ['ALMA_general_1_0_0_MIN_AMOUNT'],
                ['ALMA_general_1_0_0_MAX_AMOUNT'],
                ['ALMA_general_1_0_0_ENABLED_ON'],

                ['ALMA_general_2_0_0_MIN_AMOUNT'],
                ['ALMA_general_2_0_0_MAX_AMOUNT'],
                ['ALMA_general_2_0_0_ENABLED_ON'],

                ['ALMA_general_3_0_0_MIN_AMOUNT'],
                ['ALMA_general_3_0_0_MAX_AMOUNT'],
                ['ALMA_general_3_0_0_ENABLED_ON'],

                ['ALMA_general_4_0_0_MIN_AMOUNT'],
                ['ALMA_general_4_0_0_MAX_AMOUNT'],
                ['ALMA_general_4_0_0_ENABLED_ON'],

                ['ALMA_general_1_15_0_MIN_AMOUNT'],
                ['ALMA_general_1_15_0_MAX_AMOUNT'],
                ['ALMA_general_1_15_0_ENABLED_ON']
            )
            ->willReturnOnConsecutiveCalls(
                1, 2000, true,
                50, 2000, true,
                50, 2000, true,
                50, 2000, true,
                40, 2000, true
            );
        $this->priceHelperMock->expects($this->exactly(10))
            ->method('convertPriceFromCents')
            ->willReturnOnConsecutiveCalls(1, 2000, 50, 2000, 50, 2000, 50, 2000, 50, 2000);
        $this->expectException(PnxFormException::class);
        $this->expectExceptionMessage('Minimum amount for deferred + 15 days plan must be within 50 and 2000.');
        $this->feePlanHelper->checkLimitsSaveFeePlans($feePlans);
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\PnxFormException
     */
    public function testCheckLimitsSaveFeePlanWithWrongMaxAmountInPnxThrowException()
    {
        $feePlans = [
            $this->feePlansDataProvider->planPayNow(),
            $this->feePlansDataProvider->planP2x(),
            $this->feePlansDataProvider->planP3x(),
            $this->feePlansDataProvider->planP4x(),
            $this->feePlansDataProvider->planDeferred15(),
        ];
        $this->settingsHelperMock->expects($this->exactly(2))
            ->method('keyForFeePlan')
            ->willReturnOnConsecutiveCalls(
                'general_1_0_0',
                'general_2_0_0'
            );
        $this->settingsHelperMock->expects($this->once())
            ->method('isDeferred')
            ->willReturnOnConsecutiveCalls(false, false, false);
        $this->priceHelperMock->expects($this->exactly(4))
            ->method('convertPriceToCents')
            ->withConsecutive(
                [1],
                [2000],
                [50],
                [3000]
            )
            ->willReturnOnConsecutiveCalls(100, 200000, 5000, 300000);
        $this->toolsProxyMock->expects($this->exactly(6))
            ->method('getValue')
            ->withConsecutive(
                ['ALMA_general_1_0_0_MIN_AMOUNT'],
                ['ALMA_general_1_0_0_MAX_AMOUNT'],
                ['ALMA_general_1_0_0_ENABLED_ON'],

                ['ALMA_general_2_0_0_MIN_AMOUNT'],
                ['ALMA_general_2_0_0_MAX_AMOUNT'],
                ['ALMA_general_2_0_0_ENABLED_ON']
            )
            ->willReturnOnConsecutiveCalls(
                1, 2000, true,
                50, 3000, true
            );
        $this->priceHelperMock->expects($this->exactly(4))
            ->method('convertPriceFromCents')
            ->willReturnOnConsecutiveCalls(1, 2000, 50, 2000);
        $this->expectException(PnxFormException::class);
        $this->expectExceptionMessage('Maximum amount for 2-installment plan must be within 50 and 2000.');
        $this->feePlanHelper->checkLimitsSaveFeePlans($feePlans);
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\PnxFormException
     */
    public function testCheckLimitsSaveFeePlanWithWrongMaxAmountInDeferredDaysThrowException()
    {
        $feePlans = [
            $this->feePlansDataProvider->planPayNow(),
            $this->feePlansDataProvider->planP2x(),
            $this->feePlansDataProvider->planP3x(),
            $this->feePlansDataProvider->planP4x(),
            $this->feePlansDataProvider->planDeferred15(),
        ];
        $this->settingsHelperMock->expects($this->exactly(5))
            ->method('keyForFeePlan')
            ->willReturnOnConsecutiveCalls(
                'general_1_0_0',
                'general_2_0_0',
                'general_3_0_0',
                'general_4_0_0',
                'general_1_15_0'
            );
        $this->settingsHelperMock->expects($this->exactly(3))
            ->method('isDeferred')
            ->willReturnOnConsecutiveCalls(false, false, false);
        $this->priceHelperMock->expects($this->exactly(10))
            ->method('convertPriceToCents')
            ->withConsecutive(
                [1],
                [2000],
                [50],
                [2000],
                [50],
                [2000],
                [50],
                [2000],
                [50],
                [3000]
            )
            ->willReturnOnConsecutiveCalls(100, 200000, 5000, 200000, 5000, 200000, 5000, 200000, 5000, 300000);
        $this->toolsProxyMock->expects($this->exactly(15))
            ->method('getValue')
            ->withConsecutive(
                ['ALMA_general_1_0_0_MIN_AMOUNT'],
                ['ALMA_general_1_0_0_MAX_AMOUNT'],
                ['ALMA_general_1_0_0_ENABLED_ON'],

                ['ALMA_general_2_0_0_MIN_AMOUNT'],
                ['ALMA_general_2_0_0_MAX_AMOUNT'],
                ['ALMA_general_2_0_0_ENABLED_ON'],

                ['ALMA_general_3_0_0_MIN_AMOUNT'],
                ['ALMA_general_3_0_0_MAX_AMOUNT'],
                ['ALMA_general_3_0_0_ENABLED_ON'],

                ['ALMA_general_4_0_0_MIN_AMOUNT'],
                ['ALMA_general_4_0_0_MAX_AMOUNT'],
                ['ALMA_general_4_0_0_ENABLED_ON'],

                ['ALMA_general_1_15_0_MIN_AMOUNT'],
                ['ALMA_general_1_15_0_MAX_AMOUNT'],
                ['ALMA_general_1_15_0_ENABLED_ON']
            )
            ->willReturnOnConsecutiveCalls(
                1, 2000, true,
                50, 2000, true,
                50, 2000, true,
                50, 2000, true,
                50, 3000, true
            );
        $this->priceHelperMock->expects($this->exactly(10))
            ->method('convertPriceFromCents')
            ->willReturnOnConsecutiveCalls(1, 2000, 50, 2000, 50, 2000, 50, 2000, 50, 2000);
        $this->expectException(PnxFormException::class);
        $this->expectExceptionMessage('Maximum amount for deferred + 15 days plan must be within 50 and 2000.');
        $this->feePlanHelper->checkLimitsSaveFeePlans($feePlans);
    }

    /**
     * @return void
     *
     * @throws \Alma\PrestaShop\Exceptions\PnxFormException
     */
    public function testCheckLimitsSaveFeePlanWithoutError()
    {
        $feePlans = [
            $this->feePlansDataProvider->planPayNow(),
            $this->feePlansDataProvider->planP2x(),
            $this->feePlansDataProvider->planP3x(),
            $this->feePlansDataProvider->planP4x(),
            $this->feePlansDataProvider->planDeferred15(),
        ];
        $this->settingsHelperMock->expects($this->exactly(5))
            ->method('keyForFeePlan')
            ->willReturnOnConsecutiveCalls(
                'general_1_0_0',
                'general_2_0_0',
                'general_3_0_0',
                'general_4_0_0',
                'general_1_15_0'
            );
        $this->settingsHelperMock->expects($this->exactly(3))
            ->method('isDeferred')
            ->willReturnOnConsecutiveCalls(false, false, false);
        $this->priceHelperMock->expects($this->exactly(10))
            ->method('convertPriceToCents')
            ->withConsecutive(
                [1],
                [2000],
                [50],
                [2000],
                [50],
                [2000],
                [50],
                [2000],
                [50],
                [2000]
            )
            ->willReturnOnConsecutiveCalls(100, 5000, 5000, 5000, 5000, 200000, 200000, 200000, 200000, 200000);
        $this->toolsProxyMock->expects($this->exactly(15))
            ->method('getValue')
            ->withConsecutive(
                ['ALMA_general_1_0_0_MIN_AMOUNT'],
                ['ALMA_general_1_0_0_MAX_AMOUNT'],
                ['ALMA_general_1_0_0_ENABLED_ON'],

                ['ALMA_general_2_0_0_MIN_AMOUNT'],
                ['ALMA_general_2_0_0_MAX_AMOUNT'],
                ['ALMA_general_2_0_0_ENABLED_ON'],

                ['ALMA_general_3_0_0_MIN_AMOUNT'],
                ['ALMA_general_3_0_0_MAX_AMOUNT'],
                ['ALMA_general_3_0_0_ENABLED_ON'],

                ['ALMA_general_4_0_0_MIN_AMOUNT'],
                ['ALMA_general_4_0_0_MAX_AMOUNT'],
                ['ALMA_general_4_0_0_ENABLED_ON'],

                ['ALMA_general_1_15_0_MIN_AMOUNT'],
                ['ALMA_general_1_15_0_MAX_AMOUNT'],
                ['ALMA_general_1_15_0_ENABLED_ON']
            )
            ->willReturnOnConsecutiveCalls(
                1, 2000, true,
                50, 2000, true,
                50, 2000, true,
                50, 2000, true,
                50, 2000, true
            );
        $this->priceHelperMock->expects($this->exactly(10))
            ->method('convertPriceFromCents')
            ->willReturnOnConsecutiveCalls(
                1, 2000,
                50, 2000,
                50, 2000,
                50, 2000,
                50, 2000
            );

        $this->feePlanHelper->checkLimitsSaveFeePlans($feePlans);
    }
}
