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

use Alma\PrestaShop\Utils\OrderHelper;
use PHPUnit\Framework\TestCase;
use Alma\PrestaShop\Utils\ShareOfCheckoutHelper;
use Mockery;

class ShareOfCheckoutHelperTest extends TestCase
{
    /**
     * @dataProvider dateErrorDataProvider
     * @return void
     */
    public function testShareDaysNoDate($dateErrorData)
    {
        $shareOfCheckoutHelperMock = Mockery::mock(ShareOfCheckoutHelper::class)->shouldAllowMockingProtectedMethods()->makePartial();
        $shareOfCheckoutHelperMock->shouldReceive('getEnabledDate')->andReturn($dateErrorData);
        $this->assertNull($shareOfCheckoutHelperMock->shareDays());
    }

    public function testShareDaysReturnTrueWithValidTimestamp()
    {
        $shareOfCheckoutHelperMock = Mockery::mock(ShareOfCheckoutHelper::class)->shouldAllowMockingProtectedMethods()->makePartial();
        $shareOfCheckoutHelperMock->shouldReceive('getEnabledDate')->andReturn('1654041601');
        $shareOfCheckoutHelperMock->shouldReceive('getDatesInInterval')->andReturn(['2022-01-01','2022-01-03']);
        $shareOfCheckoutHelperMock->shouldReceive('putDay');
        $this->assertTrue($shareOfCheckoutHelperMock->shareDays());
    }

    public function testGetPayload()
    {
        $orderHelperMock = Mockery::mock(OrderHelper::class);
        $shareOfCheckoutHelper = new ShareOfCheckoutHelper($orderHelperMock);
        $shareOfCheckoutHelper->setStartDate('2022-01-01');
        $payload = $shareOfCheckoutHelper->getPayload();
       
        $expectedPayload = [
            'start_time' => '1640991600',
            'end_time' => '1641077999',
            'orders' => [],
            'payment_methods' => [],
        ];

        $this->assertEquals($expectedPayload, $payload);
    }

    public function testGetTotalPaymentMethods()
    {   
        $orderHelperMock = Mockery::mock(OrderHelper::class);
        $orderHelperMock->shouldReceive('getOrders')->andReturn([]);
        $shareOfCheckoutHelper = new ShareOfCheckoutHelper($orderHelperMock);
        $getTotalPaymentMethods = $shareOfCheckoutHelper->getTotalPaymentMethods();
        $this->assertEquals([], $getTotalPaymentMethods);
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
