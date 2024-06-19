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
use Alma\PrestaShop\Factories\EligibilityFactory;
use Alma\PrestaShop\Helpers\SettingsHelper;
use PHPUnit\Framework\TestCase;

class FeePlanHelperTest extends TestCase
{
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
}
