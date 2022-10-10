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

namespace Alma\PrestaShop\Tests\Unit\Lib\Utils;

use Alma\PrestaShop\Utils\CustomerOrderHelper;
use Context;
use Customer;
use PHPUnit\Framework\TestCase;
use Mockery;
use Order;

class CustomerOrderHelperTest extends TestCase
{
    /**
     * test previous orders
     * @dataProvider previousOrdersDataProvider
     *
     * @return void
     */
    public function testPreviousOrders($data, $expected)
    {
        $carrierMock = Mockery::mock(Carrier::class);
        $context = Mockery::mock(Context::class);
        $customer = Mockery::mock(Customer::class);
        $carrierMock->shouldReceive('getNameById')->andReturn($data);

        $customerOrderHelper = new CustomerOrderHelper($context, $customer);
        $previousOrder = $customerOrderHelper->previousOrders(1);
        $this->assertEquals($expected, $previousOrder);
    }

    public function previousOrdersDataProvider()
    {
        $expected = [
            "previous_orders" => [
                [
                    'purchase_amount' => 2100,
                    'payment_method' => 'alma',
                    'shipping_method' => 'Prestashop'
                ] 
            ]
        ];

        return [
            'payment data' => [
                self::carts(),
                $expected
            ]
        ];
    }

    public function carts()
    {
        $cartsMock = [];
        $cartsFactory = [
            [
                'id_carrier' => 1
            ]
        ];

        foreach ($cartsFactory as $cartFactory) {
            $cartMock = Mockery::mock(Cart::class);
            $cartMock->id_carrier = $cartFactory['id_carrier'];
            $cartsMock[] = $cartMock;
        }

        return $cartsMock;
    }
}
