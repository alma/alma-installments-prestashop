<?php
/**
 * 2018-2023 Alma SAS
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
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Tests\integration\lib\API;

use Alma\PrestaShop\API\EligibilityHelper;
use Cart;
use Context;
use Language;
use Mockery;
use PHPUnit\Framework\TestCase;

class EligibilityHelperTest extends TestCase
{
    /**
     * Return input to integration test testEligibilityCheck
     *
     * @return array[]
     */
    public function eligibilityDataProvider()
    {
        return [
            'purchase amount eligible' => [
                // cart amount Data
                [
                    'cart_amount' => 50.99,
                ],
                // data expected
                [
                    'eligible' => true,
                ],
            ],
            'purchase min amount not eligible' => [
                // cart amount Data
                [
                    'cart_amount' => 26.00,
                ],
                // data expected
                [
                    'eligible' => false,
                ],
            ],
            'purchase max amount not eligible' => [
                // cart amount Data
                [
                    'cart_amount' => 4000.00,
                ],
                // data expected
                [
                    'eligible' => false,
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider eligibilityDataProvider
     *
     * @return void
     */
    public function testEligibilityCheck(array $cartData, array $expectedEligibility)
    {
        $contextMock = Mockery::mock(Context::class);
        $contextMock->cart = Mockery::mock(Cart::class);
        $contextMock->cart->shouldReceive('getOrderTotal')->andReturn($cartData['cart_amount']);
        $contextMock->language = Mockery::mock(Language::class);
        $contextMock->language->iso_code = 'en';

        $eligibility = EligibilityHelper::eligibilityCheck($contextMock);
        $this->assertEquals($expectedEligibility['eligible'], $eligibility[0]->isEligible);
    }
}
