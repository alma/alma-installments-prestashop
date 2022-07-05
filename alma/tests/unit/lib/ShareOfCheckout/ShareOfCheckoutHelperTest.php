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

namespace Alma\PrestaShop\Tests\Unit\Lib\ShareOfCheckout;

use Alma\PrestaShop\ShareOfCheckout\OrderHelper;
use Alma\PrestaShop\ShareOfCheckout\ShareOfCheckoutHelper;
use PHPUnit\Framework\TestCase;
use Mockery;
use Order;

class ShareOfCheckoutHelperTest extends TestCase
{
    const EUR_CURRENCY_CODE = 1;
    const USD_CURRENCY_CODE = 2;

    protected function setUp()
    {
        $this->orderHelperMock = Mockery::mock(OrderHelper::class);
    }


    /**
     * @dataProvider dateErrorDataProvider
     * @return void
     */
    public function testShareDaysNoDate($dateErrorData)
    {
        $shareOfCheckoutHelperMock = Mockery::mock(ShareOfCheckoutHelper::class, [$this->orderHelperMock])->shouldAllowMockingProtectedMethods()->makePartial();
        $shareOfCheckoutHelperMock->shouldReceive('getEnabledDate')->andReturn($dateErrorData);
        $this->assertNull($shareOfCheckoutHelperMock->shareDays());
    }

    public function testShareDaysReturnTrueWithValidTimestamp()
    {
        $this->orderHelperMock->shouldReceive('resetOrderList');
        $shareOfCheckoutHelperMock = Mockery::mock(ShareOfCheckoutHelper::class, [$this->orderHelperMock])->shouldAllowMockingProtectedMethods()->makePartial();
        $shareOfCheckoutHelperMock->shouldReceive('getEnabledDate')->andReturn('1654041601');
        $shareOfCheckoutHelperMock->shouldReceive('getDatesInInterval')->andReturn(['2022-01-01','2022-01-03']);
        $shareOfCheckoutHelperMock->shouldReceive('putDay');
        $this->assertTrue($shareOfCheckoutHelperMock->shareDays());
    }

    /**
     * test get Payload
     * @dataProvider ordersGetPayload
     *
     * @return void
     */
    public function testGetPayload($ordersMock, $expectedPayload)
    {
        $orderHelperMock = Mockery::mock(OrderHelper::class);
        $orderHelperMock->shouldReceive('getOrdersByDate')->andReturn($ordersMock);

        $shareOfCheckoutHelper = new ShareOfCheckoutHelper($orderHelperMock);
        $shareOfCheckoutHelper->setStartDate('2022-01-01');
        $payload = $shareOfCheckoutHelper->getPayload();

        $this->assertEquals($expectedPayload, $payload);
    }

    /**
     * test Payment methods
     * @dataProvider ordersTotalPaymentMethods
     *
     * @return void
     */
    public function testGetTotalPaymentMethods($ordersMock, $expectedTotalPaymentMethods)
    {   
        $orderHelperMock = Mockery::mock(OrderHelper::class);
        $orderHelperMock->shouldReceive('getOrdersByDate')->andReturn($ordersMock);
        
        $shareOfCheckoutHelper = new ShareOfCheckoutHelper($orderHelperMock);
        $getTotalPaymentMethods = $shareOfCheckoutHelper->getTotalPaymentMethods();
        $this->assertEquals($expectedTotalPaymentMethods, $getTotalPaymentMethods);
    }

    /**
     * test Payment methods
     * @dataProvider ordersTotalOrders
     *
     * @return void
     */
    public function testGetTotalOrders($ordersMock, $expectedTotalOrders)
    {   
        $orderHelperMock = Mockery::mock(OrderHelper::class);
        $orderHelperMock->shouldReceive('getOrdersByDate')->andReturn($ordersMock);
        
        $shareOfCheckoutHelper = new ShareOfCheckoutHelper($orderHelperMock);
        $getTotalOrders = $shareOfCheckoutHelper->getTotalOrders();
        $this->assertEquals($expectedTotalOrders, $getTotalOrders);
    }

    public function ordersTotalOrders() {
        $expectedTotalOrders = [
            [
                "total_order_count" => 2,
                "total_amount" => 15500,
                "currency" => "EUR"
            ],
            [
                "total_order_count" => 2,
                "total_amount" => 30000,
                "currency" => "USD"
            ]
        ];

        return [
            'order total orders' => [
                self::orders(),
                $expectedTotalOrders
            ]
        ];
    }

    public function ordersTotalPaymentMethods() {
        $expectedTotalPaymentMethods = [
            [
                "payment_method_name" => "alma",
                "orders" => [
                    [
                        "order_count" => 2,
                        "amount" => 15500,
                        "currency" => "EUR"
                    ],
                    [
                        "order_count" => 1,
                        "amount" => 10000,
                        "currency" => "USD"
                    ]
                ]
            ],
            [
                "payment_method_name" => "paypal",
                "orders" => [
                    [
                        "order_count" => 1,
                        "amount" => 20000,
                        "currency" => "USD"
                    ]
                ]
            ]
        ];

        return [
            'order total payment methods' => [
                self::orders(),
                $expectedTotalPaymentMethods
            ]
        ];
    }

    public function ordersGetPayload()
    {
        $expectedPayload = [
            'start_time' => 1640991600,
            'end_time' => 1641077999,
            'orders' => [
                [
                    "total_order_count"=> 2,
                    "total_amount"=> 15500,
                    "currency"=> "EUR"
                ],
                [
                    "total_order_count"=> 2,
                    "total_amount"=> 30000,
                    "currency"=> "USD"
                ]
            ],
            'payment_methods' => [
                [
                    "payment_method_name" => "alma",
                    "orders" => [
                        [
                            "order_count" => 2,
                            "amount" => 15500,
                            "currency" => "EUR"
                        ],
                        [
                            "order_count" => 1,
                            "amount" => 10000,
                            "currency" => "USD"
                        ]
                    ]
                ],
                [
                    "payment_method_name" => "paypal",
                    "orders" => [
                        [
                            "order_count" => 1,
                            "amount" => 20000,
                            "currency" => "USD"
                        ]
                    ]
                ]
            ],
        ];

        return [
            'order get payload' => [
                self::orders(),
                $expectedPayload
            ]
        ];
    }

    public function orders()
    {
        $ordersMock = [];
        $ordersFactory = [
            [
                'id_currency' => self::EUR_CURRENCY_CODE,
                'total_paid_tax_incl' => 100.00,
                'module' => 'alma',
            ],
            [
                'id_currency' => self::USD_CURRENCY_CODE,
                'total_paid_tax_incl' => 200.00,
                'module' => 'paypal',
            ],
            [
                'id_currency' => self::EUR_CURRENCY_CODE,
                'total_paid_tax_incl' => 55.00,
                'module' => 'alma',
            ],
            [
                'id_currency' => self::USD_CURRENCY_CODE,
                'total_paid_tax_incl' => 100.00,
                'module' => 'alma',
            ],
        ];

        foreach($ordersFactory as $orderFactory) {
            $orderMock = Mockery::mock(Order::class);
            $orderMock->id_currency = $orderFactory['id_currency'];
            $orderMock->total_paid_tax_incl = $orderFactory['total_paid_tax_incl'];
            $orderMock->module = $orderFactory['module'];
            $ordersMock[] = $orderMock;
        }

        return $ordersMock;
    }

    public function dateErrorDataProvider()
    {
        return [
            'Date is false' => [
                'date' => false
            ],
            'Date is empty' => [
                'date' => ''
            ],
            'Date is 0' => [
                'date' => 0
            ],
        ];
    }
}
