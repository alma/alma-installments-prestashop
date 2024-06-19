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

namespace Alma\PrestaShop\Tests\Unit\Builders\Helpers;

use Alma\PrestaShop\Builders\Helpers\CartHelperBuilder;
use Alma\PrestaShop\Factories\CartFactory;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\OrderStateFactory;
use Alma\PrestaShop\Helpers\CarrierHelper;
use Alma\PrestaShop\Helpers\CartHelper;
use Alma\PrestaShop\Helpers\OrderStateHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Repositories\OrderRepository;
use PHPUnit\Framework\TestCase;

class CartHelperBuilderTest extends TestCase
{
    /**
     * @var CartHelperBuilder
     */
    protected $cartHelperBuilder;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var ContextFactory
     */
    protected $contextFactory;

    public function setUp()
    {
        $this->cartHelperBuilder = new CartHelperBuilder();
        $this->contextFactory = new ContextFactory();
        $this->priceHelper = \Mockery::mock(PriceHelper::class);
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(CartHelper::class, $this->cartHelperBuilder->getInstance());
    }

    public function testGetContextFactory()
    {
        $this->assertInstanceOf(ContextFactory::class, $this->cartHelperBuilder->getContextFactory());
        $this->assertInstanceOf(ContextFactory::class, $this->cartHelperBuilder->getContextFactory(
            $this->contextFactory
        ));
    }

    /**
     * @covers \Alma\PrestaShop\Builders\Helpers\AddressHelperBuilder::getToolsHelper
     *
     * @return void
     */
    public function testGetToolsHelperTest()
    {
        $this->assertInstanceOf(ToolsHelper::class, $this->cartHelperBuilder->getToolsHelper());
        $this->assertInstanceOf(ToolsHelper::class, $this->cartHelperBuilder->getToolsHelper(
            new ToolsHelper()
        ));
    }

    public function testGetPriceHelper()
    {
        $this->assertInstanceOf(PriceHelper::class, $this->cartHelperBuilder->getPriceHelper());
        $this->assertInstanceOf(PriceHelper::class, $this->cartHelperBuilder->getPriceHelper(
            $this->priceHelper
        ));
    }

    public function testGetCarteData()
    {
        $this->assertInstanceOf(CartData::class, $this->cartHelperBuilder->getCartData());
        $this->assertInstanceOf(CartData::class, $this->cartHelperBuilder->getPriceHelper(
            $this->createMock(CartData::class)
        ));
    }

    public function testGetOrderRepository()
    {
        $this->assertInstanceOf(OrderRepository::class, $this->cartHelperBuilder->getOrderRepository());
        $this->assertInstanceOf(OrderRepository::class, $this->cartHelperBuilder->getOrderRepository(
            new OrderRepository()
        ));
    }

    public function testGetOrderStateHelper()
    {
        $this->assertInstanceOf(OrderStateHelper::class, $this->cartHelperBuilder->getOrderStateHelper());
        $this->assertInstanceOf(OrderStateHelper::class, $this->cartHelperBuilder->getOrderStateHelper(
            new OrderStateHelper($this->contextFactory, new OrderStateFactory())
        ));
    }

    public function testGetCarrierHelper()
    {
        $this->assertInstanceOf(CarrierHelper::class, $this->cartHelperBuilder->getCarrierHelper());
        $this->assertInstanceOf(CarrierHelper::class, $this->cartHelperBuilder->getCarrierHelper(
            $this->createMock(CarrierHelper::class)
        ));
    }

    public function testGetCartFactory()
    {
        $this->assertInstanceOf(CartFactory::class, $this->cartHelperBuilder->getCartFactory());
        $this->assertInstanceOf(CartFactory::class, $this->cartHelperBuilder->getCartFactory(
            $this->createMock(CartFactory::class)
        ));
    }
}
