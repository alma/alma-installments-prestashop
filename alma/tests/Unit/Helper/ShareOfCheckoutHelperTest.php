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

use Alma\PrestaShop\Builders\Helpers\ShareOfCheckoutHelperBuilder;
use Alma\PrestaShop\Helpers\OrderHelper;
use Mockery;
use Order;
use PHPUnit\Framework\TestCase;

class ShareOfCheckoutHelperTest extends TestCase
{
    const EUR_CURRENCY_CODE = 1;
    const USD_CURRENCY_CODE = 2;

    protected function setUp()
    {
        $this->orderHelperMock = Mockery::mock(OrderHelper::class);
    }

    /**
     * test get Payload
     *
     * @dataProvider ordersGetPayload
     *
     * @param $ordersMock
     * @param $expectedPayload
     *
     * @return void
     */
    public function testGetPayload($ordersMock, $expectedPayload)
    {
        $orderHelperMock = Mockery::mock(OrderHelper::class);
        $orderHelperMock->shouldReceive('getOrdersByDate')->andReturn($ordersMock);

        $shareOfCheckoutHelperBuilder = \Mockery::mock(ShareOfCheckoutHelperBuilder::class)->makePartial();
        $shareOfCheckoutHelperBuilder->shouldReceive('getOrderHelper')->andReturn($orderHelperMock);
        $shareOfCheckoutHelper = $shareOfCheckoutHelperBuilder->getInstance();

        $payload = $shareOfCheckoutHelper->getPayload('2022-01-01');

        $this->assertEquals($expectedPayload, $payload);
    }

    /**
     * test Payment methods
     *
     * @dataProvider ordersTotalPaymentMethods
     *
     * @return void
     */
    public function testGetTotalPaymentMethods($ordersMock, $expectedTotalPaymentMethods)
    {
        $shareOfCheckoutHelperBuilder = new ShareOfCheckoutHelperBuilder();
        $shareOfCheckoutHelper = $shareOfCheckoutHelperBuilder->getInstance();

        $getTotalPaymentMethods = $shareOfCheckoutHelper->getTotalPaymentMethods($ordersMock);

        $this->assertEquals($expectedTotalPaymentMethods, $getTotalPaymentMethods);
    }

    /**
     * test Payment methods
     *
     * @dataProvider ordersTotalOrders
     *
     * @param $ordersMock
     * @param $expectedTotalOrders
     *
     * @return void
     */
    public function testGetTotalOrders($ordersMock, $expectedTotalOrders)
    {
        $shareOfCheckoutHelperBuilder = new ShareOfCheckoutHelperBuilder();
        $shareOfCheckoutHelper = $shareOfCheckoutHelperBuilder->getInstance();

        $getTotalOrders = $shareOfCheckoutHelper->getTotalOrders($ordersMock);
        $this->assertEquals($expectedTotalOrders, $getTotalOrders);
    }

    /**
     * @return array[]
     */
    public function ordersTotalOrders()
    {
        $expectedTotalOrders = [
            [
                'total_order_count' => 2,
                'total_amount' => 15500,
                'currency' => 'EUR',
            ],
            [
                'total_order_count' => 2,
                'total_amount' => 30000,
                'currency' => 'USD',
            ],
        ];

        return [
            'order total orders' => [
                static::orders(),
                $expectedTotalOrders,
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function ordersTotalPaymentMethods()
    {
        $expectedTotalPaymentMethods = [
            [
                'payment_method_name' => 'alma',
                'orders' => [
                    [
                        'order_count' => 2,
                        'amount' => 15500,
                        'currency' => 'EUR',
                    ],
                    [
                        'order_count' => 1,
                        'amount' => 10000,
                        'currency' => 'USD',
                    ],
                ],
            ],
            [
                'payment_method_name' => 'paypal',
                'orders' => [
                    [
                        'order_count' => 1,
                        'amount' => 20000,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        return [
            'order total payment methods' => [
                static::orders(),
                $expectedTotalPaymentMethods,
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function ordersGetPayload()
    {
        $expectedPayload = [
            'start_time' => 1640991600,
            'end_time' => 1641077999,
            'orders' => [
                [
                    'total_order_count' => 2,
                    'total_amount' => 15500,
                    'currency' => 'EUR',
                ],
                [
                    'total_order_count' => 2,
                    'total_amount' => 30000,
                    'currency' => 'USD',
                ],
            ],
            'payment_methods' => [
                [
                    'payment_method_name' => 'alma',
                    'orders' => [
                        [
                            'order_count' => 2,
                            'amount' => 15500,
                            'currency' => 'EUR',
                        ],
                        [
                            'order_count' => 1,
                            'amount' => 10000,
                            'currency' => 'USD',
                        ],
                    ],
                ],
                [
                    'payment_method_name' => 'paypal',
                    'orders' => [
                        [
                            'order_count' => 1,
                            'amount' => 20000,
                            'currency' => 'USD',
                        ],
                    ],
                ],
            ],
        ];

        return [
            'order get payload' => [
                static::orders(),
                $expectedPayload,
            ],
        ];
    }

    /**
     * @return array
     */
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

        foreach ($ordersFactory as $orderFactory) {
            $orderMock = Mockery::mock(Order::class);
            $orderMock->id_currency = $orderFactory['id_currency'];
            $orderMock->total_paid_tax_incl = $orderFactory['total_paid_tax_incl'];
            $orderMock->module = $orderFactory['module'];
            $ordersMock[] = $orderMock;
        }

        return $ordersMock;
    }

    /**
     * @return array
     */
    public function dateErrorDataProvider()
    {
        return [
            'Date is false' => [
                'date' => false,
            ],
            'Date is true' => [
                'date' => true,
            ],
            'Date is empty' => [
                'date' => '',
            ],
            'Date is 0' => [
                'date' => 0,
            ],
            'Date is invalid string' => [
                'date' => 'invalid date string here',
            ],
        ];
    }
}
