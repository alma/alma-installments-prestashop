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

namespace Alma\PrestaShop\Tests\Unit\Helper;

use Alma\API\Entities\Order;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\CartProductRepository;
use Alma\PrestaShop\Repositories\ProductRepository;
use PHPUnit\Framework\TestCase;

class InsuranceHelperTest extends TestCase
{
    /**
     * @var InsuranceHelper
     */
    protected $insuranceHelper;
    /**
     * @var AlmaInsuranceProductRepository|(AlmaInsuranceProductRepository&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $insuranceProductRepository;
    /**
     * @var ProductRepository|(ProductRepository&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;
    /**
     * @var CartProductRepository|(CartProductRepository&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartProductRepository;
    /**
     * @var SettingsHelper|(SettingsHelper&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $settingsHelper;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Tools|(\Tools&\PHPUnit_Framework_MockObject_MockObject)
     */
    protected $toolsHelper;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->cartProductRepository = $this->createMock(CartProductRepository::class);
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->insuranceProductRepository = $this->createMock(AlmaInsuranceProductRepository::class);
        $this->toolsHelper = $this->createMock(ToolsHelper::class);
        $this->settingsHelper = $this->createMock(SettingsHelper::class);

        $this->cart = $this->createMock(\Cart::class);
        $context = $this->createMock(\Context::class);
        $context->cart = $this->cart;

        $this->contextFactory = \Mockery::mock(ContextFactory::class)->makePartial();
        $this->contextFactory->shouldReceive('getContext')->andReturn($context);

        $this->insuranceHelper = new InsuranceHelper(
            $this->cartProductRepository,
            $this->productRepository,
            $this->insuranceProductRepository,
            $this->contextFactory,
            $this->toolsHelper,
            $this->settingsHelper
        );
        $this->order = $this->createMock(Order::class);
    }

    /**
     * Given an order with value up to zero we return false
     *
     * @return void
     */
    public function testCanRefundOrderWithNbNotCancelledValueUpToZeroReturnFalse()
    {
        $this->order->id = 1;
        $this->order->id_shop = 1;
        $this->insuranceProductRepository->expects($this->once())
            ->method('canRefundOrder')
            ->with($this->order->id, $this->order->id_shop)
            ->willReturn([
                'nbNotCancelled' => 1,
            ]);

        $this->assertFalse($this->insuranceHelper->canRefundOrder($this->order));
    }

    /**
     * Given an order with value zero we return true
     *
     * @return void
     */
    public function testCanRefundOrderWithNbNotCancelledValueZeroReturnTrue()
    {
        $this->order->id = 1;
        $this->order->id_shop = 1;
        $this->insuranceProductRepository->expects($this->once())
            ->method('canRefundOrder')
            ->with($this->order->id, $this->order->id_shop)
            ->willReturn([
                'nbNotCancelled' => 0,
            ]);

        $this->assertTrue($this->insuranceHelper->canRefundOrder($this->order));
    }

    /**
     * Given a reference product we don't get an id insurance product and return false
     *
     * @return void
     */
    public function testHasInsuranceInCartWithNoIdInsuranceProductReturnFalse()
    {
        $this->productRepository->expects($this->once())
            ->method('getProductIdByReference')
            ->with(ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE)
            ->willReturn(null);

        $this->assertFalse($this->insuranceHelper->hasInsuranceInCart());
    }

    /**
     * Given an id insurance product and a cart id we return bool if the product is in the cart
     *
     * @dataProvider cartWithOrWithoutInsuranceProduct
     *
     * @return void
     */
    public function testHasInsuranceInCartWithIdInsuranceProductReturnBool($expected, $idProduct)
    {
        $this->cart = $this->createMock(\Cart::class);
        $context = $this->createMock(\Context::class);
        $context->cart = $this->cart;
        $context->cart->id = 1;

        $this->contextFactory = \Mockery::mock(ContextFactory::class)->makePartial();
        $this->contextFactory->shouldReceive('getContext')->andReturn($context);

        $idInsuranceProduct = 1;

        $this->cartProductRepository->expects($this->once())
            ->method('hasProductInCart')
            ->with($idInsuranceProduct, $context->cart->id)
            ->willReturn($idProduct);

        $this->productRepository->expects($this->once())
            ->method('getProductIdByReference')
            ->with(ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE)
            ->willReturn($idInsuranceProduct);

        $this->insuranceHelper = new InsuranceHelper(
            $this->cartProductRepository,
            $this->productRepository,
            $this->insuranceProductRepository,
            $this->contextFactory,
            $this->toolsHelper,
            $this->settingsHelper
        );

        $this->assertEquals($expected, $this->insuranceHelper->hasInsuranceInCart());
    }

    /**
     * Given an id product and id product attribute we return a cms reference
     *
     * @dataProvider productIdAndProductAttributeIdForCmsReference
     *
     * @param $productId
     * @param $productAttributeId
     * @param $expected
     *
     * @return void
     */
    public function testCreateCmsReferenceWithProductIdAndProductAttributeId($productId, $productAttributeId, $expected)
    {
        $this->assertEquals($expected, $this->insuranceHelper->createCmsReference($productId, $productAttributeId));
    }

    /**
     * Given a cart we check if insurance products exist
     *
     * @return void
     */
    public function testCheckInsuranceProductsExist()
    {
        $this->cart->id = 1;
        $this->cart->id_shop = 1;
        $cart = $this->cart;
        $this->insuranceProductRepository->expects($this->once())
            ->method('hasInsuranceForCartIdAndShop')
            ->with($cart->id, $cart->id_shop)
            ->willReturn(true);
        $this->assertTrue($this->insuranceHelper->almaInsuranceProductsAlreadyExist($cart));
    }

    /**
     * Given a cart we check if insurance products don't exist
     *
     * @return void
     */
    public function testCheckInsuranceProductsDontExist()
    {
        $this->cart->id = 1;
        $this->cart->id_shop = 1;
        $cart = $this->cart;
        $this->insuranceProductRepository->expects($this->once())
            ->method('hasInsuranceForCartIdAndShop')
            ->with($cart->id, $cart->id_shop)
            ->willReturn(false);
        $this->assertFalse($this->insuranceHelper->almaInsuranceProductsAlreadyExist($cart));
    }

    /**
     * Given a true parameter for all insurance settings we return true
     *
     * @return void
     */
    public function testIsInsuranceAllowedInProductPageWithAllParameterTrue()
    {
        $this->settingsHelper->expects($this->exactly(3))
            ->method('getKey')
            ->withConsecutive(
                [
                    ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_PRODUCT, false,
                ],
                [
                    ConstantsHelper::ALMA_ALLOW_INSURANCE, false,
                ],
                [
                    ConstantsHelper::ALMA_ACTIVATE_INSURANCE, false,
                ]
            )
            ->willReturn(true);

        $this->toolsHelper->expects($this->any())
            ->method('psVersionCompare')
            ->with('1.7', '>=')
            ->willReturn(true);

        $this->assertTrue($this->insuranceHelper->isInsuranceAllowedInProductPage());
    }

    /**
     * Given a false parameter for widget insurance settings we return false
     *
     * @return void
     */
    public function testIsInsuranceAllowedInProductPageWithWidgetParameterFalse()
    {
        $this->settingsHelper->expects($this->exactly(1))
            ->method('getKey')
            ->withConsecutive([
                ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_PRODUCT, false,
            ])
            ->willReturn(false);

        $this->toolsHelper->expects($this->any())
            ->method('psVersionCompare')
            ->with('1.7', '>=')
            ->willReturn(true);

        $this->assertFalse($this->insuranceHelper->isInsuranceAllowedInProductPage());
    }

    /**
     * Given a false parameter for allow insurance settings we return false
     *
     * @return void
     */
    public function testIsInsuranceAllowedInProductPageWithAllowInsuranceParameterFalse()
    {
        $this->settingsHelper->expects($this->exactly(2))
            ->method('getKey')
            ->withConsecutive(
                [
                    ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_PRODUCT, false,
                ],
                [
                    ConstantsHelper::ALMA_ALLOW_INSURANCE, false,
                ]
            )
            ->willReturnOnConsecutiveCalls(true, false);

        $this->toolsHelper->expects($this->any())
            ->method('psVersionCompare')
            ->with('1.7', '>=')
            ->willReturn(true);

        $this->assertFalse($this->insuranceHelper->isInsuranceAllowedInProductPage());
    }

    /**
     * Given a false parameter for activate insurance settings we return false
     *
     * @return void
     */
    public function testIsInsuranceAllowedInProductPageWithActivateInsuranceParameterFalse()
    {
        $this->settingsHelper->expects($this->exactly(3))
            ->method('getKey')
            ->withConsecutive(
                [
                    ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_PRODUCT, false,
                ],
                [
                    ConstantsHelper::ALMA_ALLOW_INSURANCE, false,
                ],
                [
                    ConstantsHelper::ALMA_ACTIVATE_INSURANCE, false,
                ]
            )
            ->willReturnOnConsecutiveCalls(true, true, false);

        $this->toolsHelper->expects($this->any())
            ->method('psVersionCompare')
            ->with('1.7', '>=')
            ->willReturn(true);

        $this->assertFalse($this->insuranceHelper->isInsuranceAllowedInProductPage());
    }

    /**
     * Given a true parameter for all insurance settings we return true
     *
     * @return void
     */
    public function testIsInsuranceAllowedInCartPageWithAllParameterTrue()
    {
        $this->settingsHelper->expects($this->exactly(3))
            ->method('getKey')
            ->withConsecutive(
                [
                    ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_CART, false,
                ],
                [
                    ConstantsHelper::ALMA_ALLOW_INSURANCE, false,
                ],
                [
                    ConstantsHelper::ALMA_ACTIVATE_INSURANCE, false,
                ]
            )
            ->willReturn(true);

        $this->toolsHelper->expects($this->any())
            ->method('psVersionCompare')
            ->with('1.7', '>=')
            ->willReturn(true);

        $this->assertTrue($this->insuranceHelper->isInsuranceAllowedInCartPage());
    }

    /**
     * Given a false parameter for widget insurance settings we return false
     *
     * @return void
     */
    public function testIsInsuranceAllowedInCartPageWithWidgetParameterFalse()
    {
        $this->settingsHelper->expects($this->exactly(1))
            ->method('getKey')
            ->withConsecutive([
                ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_CART, false,
            ])
            ->willReturn(false);

        $this->toolsHelper->expects($this->any())
            ->method('psVersionCompare')
            ->with('1.7', '>=')
            ->willReturn(true);

        $this->assertFalse($this->insuranceHelper->isInsuranceAllowedInCartPage());
    }

    /**
     * Given a false parameter for allow insurance settings we return false
     *
     * @return void
     */
    public function testIsInsuranceAllowedInCartPageWithAllowInsuranceParameterFalse()
    {
        $this->settingsHelper->expects($this->exactly(2))
            ->method('getKey')
            ->withConsecutive(
                [
                    ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_CART, false,
                ],
                [
                    ConstantsHelper::ALMA_ALLOW_INSURANCE, false,
                ]
            )
            ->willReturnOnConsecutiveCalls(true, false);

        $this->toolsHelper->expects($this->any())
            ->method('psVersionCompare')
            ->with('1.7', '>=')
            ->willReturn(true);

        $this->assertFalse($this->insuranceHelper->isInsuranceAllowedInCartPage());
    }

    /**
     * Given a false parameter for activate insurance settings we return false
     *
     * @return void
     */
    public function testIsInsuranceAllowedInCartPageWithActivateInsuranceParameterFalse()
    {
        $this->settingsHelper->expects($this->exactly(3))
            ->method('getKey')
            ->withConsecutive(
                [
                    ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_CART, false,
                ],
                [
                    ConstantsHelper::ALMA_ALLOW_INSURANCE, false,
                ],
                [
                    ConstantsHelper::ALMA_ACTIVATE_INSURANCE, false,
                ]
            )
            ->willReturnOnConsecutiveCalls(true, true, false);

        $this->toolsHelper->expects($this->any())
            ->method('psVersionCompare')
            ->with('1.7', '>=')
            ->willReturn(true);

        $this->assertFalse($this->insuranceHelper->isInsuranceAllowedInCartPage());
    }

    /**
     * Given a true parameter for all insurance settings we return true
     *
     * @return void
     */
    public function testIsInsuranceActivatedWithAllParameterTrue()
    {
        $this->settingsHelper->expects($this->exactly(2))
            ->method('getKey')
            ->withConsecutive(
                [
                    ConstantsHelper::ALMA_ALLOW_INSURANCE, false,
                ],
                [
                    ConstantsHelper::ALMA_ACTIVATE_INSURANCE, false,
                ]
            )
            ->willReturn(true);

        $this->toolsHelper->expects($this->any())
            ->method('psVersionCompare')
            ->with('1.7', '>=')
            ->willReturn(true);

        $this->assertTrue($this->insuranceHelper->isInsuranceActivated());
    }

    /**
     * Given a false parameter for widget insurance settings we return false
     *
     * @return void
     */
    public function testIsInsuranceActivatedWithAllowParameterFalse()
    {
        $this->settingsHelper->expects($this->exactly(1))
            ->method('getKey')
            ->withConsecutive([
                ConstantsHelper::ALMA_ALLOW_INSURANCE, false,
            ])
            ->willReturn(false);

        $this->toolsHelper->expects($this->any())
            ->method('psVersionCompare')
            ->with('1.7', '>=')
            ->willReturn(true);

        $this->assertFalse($this->insuranceHelper->isInsuranceActivated());
    }

    /**
     * Given a false parameter for allow insurance settings we return false
     *
     * @return void
     */
    public function testIsInsuranceActivatedWithActiveParameterFalse()
    {
        $this->settingsHelper->expects($this->exactly(2))
            ->method('getKey')
            ->withConsecutive(
                [
                    ConstantsHelper::ALMA_ALLOW_INSURANCE, false,
                ],
                [
                    ConstantsHelper::ALMA_ACTIVATE_INSURANCE, false,
                ]
            )
            ->willReturnOnConsecutiveCalls(true, false);

        $this->toolsHelper->expects($this->any())
            ->method('psVersionCompare')
            ->with('1.7', '>=')
            ->willReturn(true);

        $this->assertFalse($this->insuranceHelper->isInsuranceActivated());
    }

    /**
     * @return array[]
     */
    public function productIdAndProductAttributeIdForCmsReference()
    {
        return [
            'product id and product attribute id' => [
                'productId' => 1,
                'productAttributeId' => 1,
                'expected' => '1-1',
            ],
            'product id and product attribute id zero' => [
                'productId' => 1,
                'productAttributeId' => 0,
                'expected' => '1',
            ],
            'product id and no product attribute id' => [
                'productId' => 1,
                'productAttributeId' => null,
                'expected' => '1',
            ],
            'no product id and product attribute id' => [
                'productId' => null,
                'productAttributeId' => 1,
                'expected' => null,
            ],
            'no product id and no product attribute id' => [
                'productId' => null,
                'productAttributeId' => null,
                'expected' => null,
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function cartWithOrWithoutInsuranceProduct()
    {
        return [
            'cart with insurance product' => [
                'expected' => true,
                'idProduct' => 1,
            ],
            'cart without insurance product' => [
                'expected' => false,
                'idProduct' => null,
            ],
        ];
    }

    protected function tearDown()
    {
        $this->cartProductRepository = null;
        $this->productRepository = null;
        $this->insuranceProductRepository = null;
        $this->contextFactory = null;
        $this->cart = null;
        $this->settingsHelper = null;
        $this->toolsHelper = null;
        $this->insuranceHelper = null;
        $this->order = null;
    }
}
