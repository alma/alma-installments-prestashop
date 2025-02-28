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

use Alma\PrestaShop\Factories\CartFactory;
use Alma\PrestaShop\Proxy\CartProxy;
use PHPUnit\Framework\TestCase;

class CartProxyTest extends TestCase
{
    /**
     * @var CartProxy
     */
    private $cartProxyPsAfter1770;
    /**
     * @var CartFactory
     */
    private $cartFactoryMock;
    /**
     * @var \Cart
     */
    private $cartMock;

    public function setUp()
    {
        $this->cartMock = $this->createMock(\Cart::class);
        $this->cartFactoryMock = $this->createMock(CartFactory::class);
        $this->cartProxy = new CartProxy($this->cartFactoryMock);
    }

    /**
     * @return void
     */
    public function testOrderExistsAfterPs1770()
    {
        $this->cartProxy->setPsVersion('1.7.7.7');
        $this->cartMock->id = 1;
        $this->cartFactoryMock->expects($this->once())
            ->method('create')
            ->with(1)
            ->willReturn($this->cartMock);
        $this->cartMock->expects($this->once())
            ->method('orderExists')
            ->willReturn(false);
        $this->assertFalse($this->cartProxy->orderExists($this->cartMock->id));
    }

    /**
     * @return void
     */
    public function testOrderExistsBeforePs1770()
    {
        $this->cartProxy->setPsVersion('1.7.6.7');
        $this->cartMock->id = 1;
        $this->cartFactoryMock->expects($this->never())
            ->method('create')
            ->with(1)
            ->willReturn($this->cartMock);
        $this->cartMock->expects($this->never())
            ->method('orderExists')
            ->willReturn(false);
        $this->cartProxy->orderExists($this->cartMock->id);
    }
}
