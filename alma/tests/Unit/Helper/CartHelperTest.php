<?php
/**
 * 2018-2023 Alma SAS.
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

namespace Alma\PrestaShop\Tests\Unit\Helper;

use Alma\PrestaShop\Builders\CartHelperBuilder;
use Alma\PrestaShop\Exceptions\AlmaException;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Helpers\CarrierHelper;
use Alma\PrestaShop\Helpers\OrderStateHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Repositories\OrderRepository;
use PHPUnit\Framework\TestCase;

class CartHelperTest extends TestCase
{
    /**
     * @var \ContextCore|(\ContextCore&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;
    /**
     * @var ToolsHelper|(ToolsHelper&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $toolHelper;
    /**
     * @var PriceHelper|(PriceHelper&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceHelper;
    /**
     * @var CartData|(CartData&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartData;
    /**
     * @var OrderRepository|(OrderRepository&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepository;
    /**
     * @var OrderStateHelper|(OrderStateHelper&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderStateHelper;
    /**
     * @var CarrierHelper|(CarrierHelper&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $carrierHelper;

    public function testPreviousCartOrdered()
    {
    }

    public function testGetOrdersByCustomer()
    {
    }

    public function testGetCartTotal()
    {
    }

    /**
     * @return void
     */
    public function testGetCartIdFromContext()
    {
        $this->cart = $this->createMock(\Cart::class);
        $this->cart->id = 1;
        $this->context = $this->createMock(\Context::class);
        $this->context->cart = $this->cart;

        $contextFactory = \Mockery::mock(ContextFactory::class)->makePartial();
        $contextFactory->shouldReceive('getContext')->andReturn($this->context);
        $contextFactory->shouldReceive('getContextLanguageId')->andReturn(1);

        $cartHelperBuilder = \Mockery::mock(CartHelperBuilder::class)->makePartial();
        $cartHelperBuilder->shouldReceive('getContextFactory')->andReturn($contextFactory);
        $this->cartHelper = $cartHelperBuilder->getInstance();

        $this->assertEquals(1, $this->cartHelper->getCartIdFromContext());

        $this->cart = $this->createMock(\Cart::class);
        $this->cart->id = 1;
        $this->context = $this->createMock(\Context::class);
        $this->context->cart = $this->cart;

        $contextFactory = \Mockery::mock(ContextFactory::class)->makePartial();
        $contextFactory->shouldReceive('getContext')->andReturn($this->context);
        $contextFactory->shouldReceive('getContextLanguage')->andReturn(null);

        $cartHelperBuilder = \Mockery::mock(CartHelperBuilder::class)->makePartial();
        $cartHelperBuilder->shouldReceive('getContextFactory')->andReturn($contextFactory);

        $this->expectException(AlmaException::class);
        $this->cartHelper = $cartHelperBuilder->getInstance();
    }
}
