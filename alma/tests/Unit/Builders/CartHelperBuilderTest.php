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

namespace Alma\PrestaShop\Tests\Unit\Builders;

use Alma\PrestaShop\Builders\CartHelperBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Helpers\CarrierHelper;
use Alma\PrestaShop\Helpers\CartHelper;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\CurrencyHelper;
use Alma\PrestaShop\Helpers\OrderStateHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ShopHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Model\CarrierData;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Repositories\OrderRepository;
use Alma\PrestaShop\Repositories\ProductRepository;
use PHPUnit\Framework\TestCase;

class CartHelperBuilderTest extends TestCase
{
    /**
     *
     * @var CartHelperBuilder $cartHelperBuilder
     */
    protected $cartHelperBuilder;

    /**
     * @var PriceHelper $priceHelper
     */
    protected $priceHelper;

    /**
     * @var ContextFactory $contextFactory
     */
    protected $contextFactory;

    public function setUp() {
        $this->cartHelperBuilder = new CartHelperBuilder();
        $this->contextFactory = new ContextFactory();
        $this->priceHelper =  new PriceHelper(
            new ToolsHelper(),
            new CurrencyHelper()
        );
    }


    public function testGetInstance() {
        $this->assertInstanceOf(CartHelper::class, $this->cartHelperBuilder->getInstance());
    }

    public function testGetContextFactory() {
        $this->assertInstanceOf(ContextFactory::class, $this->cartHelperBuilder->getContextFactory());
        $this->assertInstanceOf(ContextFactory::class, $this->cartHelperBuilder->getContextFactory(
            $this->contextFactory
        ));
    }

    /**
     * @covers \Alma\PrestaShop\Builders\AddressHelperBuilder::getToolsHelper
     * @return void
     */
    public function testGetToolsHelperTest() {
        $this->assertInstanceOf(ToolsHelper::class, $this->cartHelperBuilder->getToolsHelper());
        $this->assertInstanceOf(ToolsHelper::class, $this->cartHelperBuilder->getToolsHelper(
            new ToolsHelper()
        ));
    }

    public function testGetPriceHelper() {
        $this->assertInstanceOf(PriceHelper::class, $this->cartHelperBuilder->getPriceHelper());
        $this->assertInstanceOf(PriceHelper::class, $this->cartHelperBuilder->getPriceHelper(
            $this->priceHelper
        ));
    }

    public function testGetCarteData()
    {
        $this->assertInstanceOf(CartData::class, $this->cartHelperBuilder->getCartData());
        $this->assertInstanceOf(CartData::class, $this->cartHelperBuilder->getPriceHelper(
            new CartData(
                new ProductHelper(),
                new SettingsHelper(
                  new ShopHelper(),
                  new ConfigurationHelper()
                ),
                $this->priceHelper,
                new ProductRepository()
            )
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
            new OrderStateHelper($this->contextFactory)
        ));
    }

    public function testGetCarrierHelper()
    {
        $this->assertInstanceOf(CarrierHelper::class, $this->cartHelperBuilder->getCarrierHelper());
        $this->assertInstanceOf(CarrierHelper::class, $this->cartHelperBuilder->getCarrierHelper(
            new CarrierHelper(
                $this->contextFactory,
                new CarrierData()
            )
        ));
    }
}
