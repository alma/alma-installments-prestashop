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
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Helpers\CarrierHelper;
use Alma\PrestaShop\Helpers\OrderHelper;
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

    public function setUp()
    {
        $this->cart = $this->createMock(\Cart::class);
    }

    /**
     * @return void
     */
    public function testPreviousCartOrdered()
    {
        $this->cart->id = 1;
        $cartItem = [
            [
                'sku' => 'demo_1',
                'vendor' => 'Studio Design',
                'title' => 'Hummingbird printed t-shirt',
                'variant_title' => 'Color - White, Size - S',
                'quantity' => 1,
                'unit_price' => 33600,
                'line_price' => 33600,
                'is_gift' => false,
                'categories' => ['men'],
                'url' => 'http://prestashop-a-1-7-8-7.local.test/men/1-1-hummingbird-printed-t-shirt.html#/1-size-s/8-color-white',
                'picture_url' => 'http://prestashop-a-1-7-8-7.local.test/2-large_default/hummingbird-printed-t-shirt.jpg',
                'requires_shipping' => true,
                'taxes_included' => true,
            ],
        ];
        $expected = [
            [
                'purchase_amount' => 33600,
                'created' => 1714654826,
                'payment_method' => 'Payments by check',
                'alma_payment_external_id' => null,
                'current_state' => 'Payment accepted',
                'shipping_method' => 'PrestaShop',
                'items' => $cartItem,
                ],
        ];

        $expectedOrders = [
            [
                'id_cart' => '183',
                'date_add' => '2024-05-02 15:00:26',
                'payment' => 'Payments by check',
                'current_state' => '1',
                'module' => 'ps_checkpayment',
                'transaction_id' => null,
            ],
        ];

        $cartData = \Mockery::mock(CartData::class)->makePartial();
        $cartData->shouldReceive('getCartItems')->andReturn($cartItem);

        $orderHelper = \Mockery::mock(OrderHelper::class)->makePartial();
        $orderHelper->shouldReceive('getOrdersByCustomer', [1, 10])->andReturn($expectedOrders);

        $toolsHelper = \Mockery::mock(ToolsHelper::class)->makePartial();
        $toolsHelper->shouldReceive('psRound', [336.00])->andReturn(336.00);

        $orderStateHelper = \Mockery::mock(OrderStateHelper::class)->makePartial();
        $orderStateHelper->shouldReceive('getNameById', '1')->andReturn('Payment accepted');

        $carrierHelper = \Mockery::mock(CarrierHelper::class)->makePartial();
        $carrierHelper->shouldReceive('getParentCarrierNameById', '1')->andReturn('PrestaShop');

        $priceHelper = \Mockery::mock(PriceHelper::class)->makePartial();
        $priceHelper->shouldReceive('convertPriceToCents', '33600')->andReturn('33600');

        $cartHelperBuilder = \Mockery::mock(CartHelperBuilder::class)->makePartial();
        $cartHelperBuilder->shouldReceive('getCartData')->andReturn($cartData);
        $cartHelperBuilder->shouldReceive('getOrderHelper')->andReturn($orderHelper);
        $cartHelperBuilder->shouldReceive('getToolsHelper')->andReturn($toolsHelper);
        $cartHelperBuilder->shouldReceive('getOrderStateHelper')->andReturn($orderStateHelper);
        $cartHelperBuilder->shouldReceive('getCarrierHelper')->andReturn($carrierHelper);
        $cartHelperBuilder->shouldReceive('getPriceHelper')->andReturn($priceHelper);

        $cartHelper = $cartHelperBuilder->getInstance();

        $this->assertEquals($expected, $cartHelper->previousCartOrdered(1));
    }

    /**
     * @throws \Exception
     */
    public function testGetCartTotal()
    {
        $this->cart->method('getOrderTotal')->willReturn(364.589);

        $toolHelper = $this->createMock(ToolsHelper::class);
        $toolHelper->expects($this->once())
            ->method('psRound')
            ->with(364.589)
            ->willReturn(364.51);

        $priceHelper = $this->createMock(PriceHelper::class);
        $priceHelper->expects($this->once())
            ->method('convertPriceToCents')
            ->with(364.51)
            ->willReturn(36451);

        $cartHelperBuilder = \Mockery::mock(CartHelperBuilder::class)->makePartial();
        $cartHelperBuilder->shouldReceive('getToolsHelper')->andReturn($toolHelper);
        $cartHelperBuilder->shouldReceive('getPriceHelper')->andReturn($priceHelper);

        $cartHelper = $cartHelperBuilder->getInstance();

        $this->assertEquals(36451, $cartHelper->getCartTotal($this->cart));
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

        $cartHelperBuilder = \Mockery::mock(CartHelperBuilder::class)->makePartial();
        $cartHelperBuilder->shouldReceive('getContextFactory')->andReturn($contextFactory);
        $cartHelper = $cartHelperBuilder->getInstance();

        $this->assertEquals(1, $cartHelper->getCartIdFromContext());
    }
}
