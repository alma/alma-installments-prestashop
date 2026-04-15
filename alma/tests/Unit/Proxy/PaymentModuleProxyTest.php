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

namespace Alma\PrestaShop\Tests\Unit\Proxy;

use Alma\PrestaShop\Proxy\CartProxy;
use Alma\PrestaShop\Proxy\PaymentModuleProxy;
use PHPUnit\Framework\TestCase;

class PaymentModuleProxyTest extends TestCase
{
    public function setUp()
    {
        $this->cartProxyMock = $this->createMock(CartProxy::class);
        $this->moduleMock = $this->createMock(\Alma::class);
        $this->paymentModuleProxy = new PaymentModuleProxy(
            $this->moduleMock,
            $this->cartProxyMock
        );
    }

    /**
     * @throws \PrestaShopException
     */
    public function testValidateOrderWithOrderAlreadyExist()
    {
        $cartId = 1;
        $this->cartProxyMock->expects($this->once())
            ->method('orderExists')
            ->with($cartId)
            ->willReturn(true);
        $this->moduleMock->expects($this->never())
            ->method('validateOrder');
        $this->assertFalse($this->paymentModuleProxy->validateOrder(
            $cartId,
            2,
            10000,
            'Alma - Pay now',
            null,
            ['transaction_id' => 'payment_id'],
            1,
            false,
            'secure_key'
        ));
    }

    /**
     * @throws \PrestaShopException
     */
    public function testValidateOrderWithoutOrderAlreadyExist()
    {
        $cartId = 1;
        $this->cartProxyMock->expects($this->once())
            ->method('orderExists')
            ->with($cartId)
            ->willReturn(false);
        $this->moduleMock->expects($this->once())
            ->method('validateOrder');
        $this->assertNull($this->paymentModuleProxy->validateOrder(
            $cartId,
            2,
            10000,
            'Alma - Pay now',
            null,
            ['transaction_id' => 'payment_id'],
            1,
            false,
            'secure_key'
        ));
    }
}
