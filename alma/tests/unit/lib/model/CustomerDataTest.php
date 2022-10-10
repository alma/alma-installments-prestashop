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

namespace Alma\PrestaShop\Tests\Unit\Lib\Model;

use PHPUnit\Framework\TestCase;
use Mockery;
use Order;

class CustomerDataTest extends TestCase
{
    /**
     * test get carts
     * @dataProvider getCartsDataProvider
     *
     * @return void
     */
    public function testGetCarts($data, $expected)
    {
        $dataProcessed = $data;
        $this->assertEquals($expected, $dataProcessed);
    }

    public function getCartsDataProvider()
    {
        $cartsMock = [];
        $cartsFactory = [
            
        ];
        $expected = [];

        foreach ($cartsFactory as $cartFactory) {
            $cartMock = Mockery::mock(Cart::class);
            $cartMock->id = $cartFactory['id'];
            $cartsMock[] = $cartMock;
        }

        return [
            'carts' => [
                $cartsMock,
                $expected
            ]
        ];
    }
}
