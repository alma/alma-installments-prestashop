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
use Alma\PrestaShop\Builders\Helpers\EligibilityHelperBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Helpers\ApiHelper;
use Alma\PrestaShop\Helpers\FeePlanHelper;
use Alma\PrestaShop\Helpers\PaymentHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use PHPUnit\Framework\TestCase;

class EligibilityHelperTest extends TestCase
{
    public function testEligibilityCheck()
    {
        $priceHelper = \Mockery::mock(PriceHelper::class)->makePartial();
        $priceHelper->shouldReceive('convertPriceToCents', [200.00])->andReturn(20000);

        $cart = $this->createMock(\Cart::class);
        $cart->method('getOrderTotal')->willReturn(200.00);

        $contextFactory = \Mockery::mock(ContextFactory::class)->makePartial();
        $contextFactory->shouldReceive('getContextCart')->andReturn($cart);

        $feePlansHelper = \Mockery::mock(FeePlanHelper::class)->makePartial();
        $feePlansHelper->shouldReceive('checkFeePlans')->andReturn([]);
        $feePlansHelper->shouldReceive('getNotEligibleFeePlans')->andReturn([]);
        $feePlansHelper->shouldReceive('getEligibleFeePlans')->andReturn([
            [
                'installmentsCount' => 1,
                'deferredDays' => 0,
                'deferredMonths' => 0,
            ],
        ]);

        $paymentHelper = \Mockery::mock(PaymentHelper::class)->makePartial();
        $paymentHelper->shouldReceive('checkPaymentData', [])->andReturn([]);

        $eligibility = new Eligibility(
            [
                'installments_count' => 1,
                'deferred_days' => 0,
                'deferred_months' => 0,
                'eligible' => true,
                'constraints' => [
                    'purchase_amount' => [
                        'minimum' => 10,
                        'maximum' => 1000,
                    ],
                ],
            ]
        );
        $apiHelper = \Mockery::mock(ApiHelper::class)->makePartial();
        $apiHelper->shouldReceive('getPaymentEligibility', [])->andReturn($eligibility);

        $eligibilityHelperBuilder = \Mockery::mock(EligibilityHelperBuilder::class)->makePartial();
        $eligibilityHelperBuilder->shouldReceive('getPriceHelper')->andReturn($priceHelper);
        $eligibilityHelperBuilder->shouldReceive('getContextFactory')->andReturn($contextFactory);
        $eligibilityHelperBuilder->shouldReceive('getFeePlanHelper')->andReturn($feePlansHelper);
        $eligibilityHelperBuilder->shouldReceive('getPaymentHelper')->andReturn($paymentHelper);
        $eligibilityHelperBuilder->shouldReceive('getApiHelper')->andReturn($apiHelper);

        $eligibilityHelper = $eligibilityHelperBuilder->getInstance();

        $this->assertEquals([$eligibility], $eligibilityHelper->eligibilityCheck());

        $priceHelper = \Mockery::mock(PriceHelper::class)->makePartial();
        $priceHelper->shouldReceive('convertPriceToCents', [200.00])->andReturn(20000);

        $cart = $this->createMock(\Cart::class);
        $cart->method('getOrderTotal')->willReturn(200.00);

        $contextFactory = \Mockery::mock(ContextFactory::class)->makePartial();
        $contextFactory->shouldReceive('getContextCart')->andReturn($cart);

        $feePlansHelper = \Mockery::mock(FeePlanHelper::class)->makePartial();
        $feePlansHelper->shouldReceive('checkFeePlans')->andReturn([]);
        $feePlansHelper->shouldReceive('getNotEligibleFeePlans')->andReturn([]);
        $feePlansHelper->shouldReceive('getEligibleFeePlans')->andReturn([]);

        $paymentHelper = \Mockery::mock(PaymentHelper::class)->makePartial();
        $paymentHelper->shouldReceive('checkPaymentData', [])->andReturn([]);

        $eligibilityHelperBuilder = \Mockery::mock(EligibilityHelperBuilder::class)->makePartial();
        $eligibilityHelperBuilder->shouldReceive('getPriceHelper')->andReturn($priceHelper);
        $eligibilityHelperBuilder->shouldReceive('getContextFactory')->andReturn($contextFactory);
        $eligibilityHelperBuilder->shouldReceive('getFeePlanHelper')->andReturn($feePlansHelper);
        $eligibilityHelperBuilder->shouldReceive('getPaymentHelper')->andReturn($paymentHelper);

        $eligibilityHelper = $eligibilityHelperBuilder->getInstance();

        $this->assertEquals([], $eligibilityHelper->eligibilityCheck());
    }
}
