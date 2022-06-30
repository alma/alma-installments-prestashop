<?php

/**
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Tests\Unit\Lib\Api;

use Alma\API\Endpoints\Results\Eligibility;
use Alma\PrestaShop\API\ClientHelper;
use PHPUnit\Framework\TestCase;
use Alma\PrestaShop\API\EligibilityHelper;
use Alma\PrestaShop\Model\PaymentData;
use Cart;
use Context;
use Language;
use Mockery;

class EligibilityHelperTest extends TestCase
{
    /**
     * Return input to test testEligibilityCheck
     * @return array[]
     */
    public function eligibilityDataProvider() {
        $dataAmountEligibe = [
            'eligible' => true,
            'reasons' => NULL,
            'constraints' => NULL,
            'payment_plan' => [
                [
                    'customer_fee' => 61,
                    'customer_interest' => 0,
                    'due_date' => 1656429874,
                    'purchase_amount' => 1701,
                    'total_amount' => 1762,
                ],
                [
                    'customer_fee' => 0,
                    'customer_interest' => 0,
                    'due_date' => 1659021874,
                    'purchase_amount' => 1699,
                    'total_amount' => 1699,
                ],
                [
                    'customer_fee' => 0,
                    'customer_interest' => 0,
                    'due_date' => 1661700274,
                    'purchase_amount' => 1699,
                    'total_amount' => 1699,
                ]
            ],
            'installments_count' => 3,
            'deferred_days' => 0,
            'deferred_months' => 0,
            'customer_total_cost_amount' => 61,
            'customer_total_cost_bps' => 120,
            'annual_interest_rate' => NULL,
        ];

        $dataAmountNotEligibe = [
            'eligible' => false,
            'reasons' => NULL,
            'constraints' => [
                'purchase_amount' => [
                    'minimum' => 5000,
                    'maximum' => 300000,
                ]
            ],
            'payment_plan' => NULL,
            'installments_count' => 3,
            'deferred_days' => 0,
            'deferred_months' => 0,
            'customer_total_cost_amount' => NULL,
            'customer_total_cost_bps' => NULL,
            'annual_interest_rate' => NULL,
        ];

        $expectedAmountEligibe = new Eligibility($dataAmountEligibe);
        $expectedAmountNotEligibe = new Eligibility($dataAmountNotEligibe);

        return [
            'purchase amount eligible in p3x' => [
                //data
                [
                    'purchase_amount' => 5099,
                    'queries' => [
                        [
                            'purchase_amount' => 5099,
                            'installments_count' => 3,
                            'deferred_days' => 0,
                            'deferred_months' => 0
                        ]
                    ],
                    'shipping_address' => [
                        'country' => 'FR'
                    ],
                    'billing_address' => [
                        'country' => 'FR'
                    ],
                    'locale' => 'en'
                ], 
                //data expected
                $expectedAmountEligibe
            ],
            'purchase amount not eligible in p3x' => [
                //data
                [
                    'purchase_amount' => 2600,
                    'queries' => [
                        [
                            'purchase_amount' => 2600,
                            'installments_count' => 3,
                            'deferred_days' => 0,
                            'deferred_months' => 0
                        ]
                    ],
                    'shipping_address' => [
                        'country' => 'FR'
                    ],
                    'billing_address' => [
                        'country' => 'FR'
                    ],
                    'locale' => 'en'
                ], 
                //data expected
                $expectedAmountNotEligibe
            ]
        ];
    }

    /**
     * @test
     * @dataProvider eligibilityDataProvider
     * @return void
     */
    public function testEligibilityCheck(array $eligibilityData, Eligibility $expectedEligibility)
    {
        $clientMock = Mockery::mock(ClientHelper::class);
        $clientMock->shouldReceive('defaultInstance')->andReturn($_ENV['ALMA_API_KEY']);
        $clientMock->shouldReceive('createInstance');

        $eligibilityHelper = Mockery::mock(EligibilityHelper::class)->shouldAllowMockingProtectedMethods()->makePartial();

        $contextMock = Mockery::mock(Context::class);
        $contextMock->cart = Mockery::mock(Cart::class);
        $contextMock->cart->shouldReceive('getOrderTotal')->andReturn(almaPriceFromCents($eligibilityData['purchase_amount']));
        $contextMock->cart->id_address_delivery = 5;
        $contextMock->cart->id_address_invoice = 5;
        $contextMock->language = Mockery::mock(Language::class);
        $contextMock->language->iso_code = 'en';

        $paymentDataMock = Mockery::mock(PaymentData::class);
        $paymentDataMock->shouldReceive('dataFromCart')->andReturn($eligibilityData);

        $eligibility = $eligibilityHelper->eligibilityCheck($contextMock);
        $expectedPaymentPlan = $expectedEligibility->getPaymentPlan();
        $eligibilityPaymentPlan = $eligibility[0]->getPaymentPlan();
        if (count($eligibilityPaymentPlan) > 0) {
            foreach ($eligibilityPaymentPlan as $key => $paymentPlan) {
                $expectedPaymentPlan[$key]['due_date'] = $paymentPlan['due_date'];
            }
        }
        $expectedEligibility->setPaymentPlan($expectedPaymentPlan);

        $this->assertEquals($expectedEligibility, $eligibility[0]);
    }
}
