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

use Alma\API\Entities\Order;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\InsuranceHelper;
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
     * @return void
     */
    protected function setUp()
    {
        $this->cartProductRepository = $this->createMock(CartProductRepository::class);
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->insuranceProductRepository = $this->createMock(AlmaInsuranceProductRepository::class);
        $this->context = $this->createMock(\Context::class);
        $this->cart = $this->createMock(\Cart::class);
        $this->context->cart = $this->cart;
        $this->insuranceHelper = new InsuranceHelper(
            $this->cartProductRepository,
            $this->productRepository,
            $this->insuranceProductRepository,
            $this->context
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
        $this->context->cart->id = 1;
        $idInsuranceProduct = 1;
        $this->cartProductRepository->expects($this->once())
            ->method('hasProductInCart')
            ->with($idInsuranceProduct, $this->context->cart->id)
            ->willReturn($idProduct);
        $this->productRepository->expects($this->once())
            ->method('getProductIdByReference')
            ->with(ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE)
            ->willReturn($idInsuranceProduct);
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
}
