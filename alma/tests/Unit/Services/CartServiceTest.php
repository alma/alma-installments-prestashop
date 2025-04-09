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

namespace Alma\PrestaShop\Tests\Unit\Services;

use Alma\PrestaShop\Exceptions\AlmaException;
use Alma\PrestaShop\Exceptions\CartException;
use Alma\PrestaShop\Factories\CartFactory;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\ToolsFactory;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\InsuranceProductHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Modules\OpartSaveCart\OpartSaveCartCartService;
use Alma\PrestaShop\Repositories\CartProductRepository;
use Alma\PrestaShop\Services\CartService;
use PHPUnit\Framework\TestCase;

class CartServiceTest extends TestCase
{
    /**
     * @var \Cart
     */
    protected $cartMock;
    /**
     * @var \Cart
     */
    protected $newCartMock;
    /**
     * @var ContextFactory
     */
    protected $contextFactoryMock;
    /**
     * @var OpartSaveCartCartService
     */
    protected $opartCartSaveServiceSpy;
    /**
     * @var CartService
     */
    protected $cartServiceMock;
    /**
     * @var ToolsFactory
     */
    protected $toolsFactoryMock;
    /**
     * @var CartFactory
     */
    protected $cartFactoryMock;
    /**
     * @var CartService
     */
    protected $cartService;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\ProductHelper|(\ProductHelper&\PHPUnit_Framework_MockObject_MockObject)
     */
    protected $productHelper;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Product|(\Product&\PHPUnit_Framework_MockObject_MockObject)
     */
    protected $productMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->cartMock = $this->createMock(\Cart::class);
        $this->newCartMock = \Mockery::mock(\Cart::class);
        $this->contextFactoryMock = $this->createMock(ContextFactory::class);
        $this->toolsFactoryMock = \Mockery::mock(ToolsFactory::class);
        $this->insuranceHelperMock = \Mockery::mock(InsuranceHelper::class);
        $this->insuranceProductHelperSpy = \Mockery::spy(InsuranceProductHelper::class);
        $this->cartFactoryMock = $this->createMock(CartFactory::class);
        $this->productHelperMock = $this->createMock(ProductHelper::class);
        $this->productMock = $this->createMock(\Product::class);
        $this->cartServiceMock = \Mockery::mock(
            CartService::class,
            [
                \Mockery::mock(CartProductRepository::class),
                $this->contextFactoryMock,
                $this->insuranceHelperMock,
                $this->insuranceProductHelperSpy,
                $this->toolsFactoryMock,
                $this->cartFactoryMock,
                $this->productHelperMock,
            ]
        )->makePartial();
        $this->cartService = new CartService(
            $this->createMock(CartProductRepository::class),
            $this->contextFactoryMock,
            $this->insuranceHelperMock,
            $this->insuranceProductHelperSpy,
            $this->toolsFactoryMock,
            $this->cartFactoryMock,
            $this->productHelperMock
        );
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        $this->cartMock = null;
        $this->newCartMock = null;
        $this->contextFactoryMock = null;
        $this->opartCartSaveServiceSpy = null;
        $this->cartServiceMock = null;
    }

    /**
     * @return void
     *
     * @throws \Alma\PrestaShop\Exceptions\AlmaException
     * @throws \PrestaShopException
     */
    public function testDuplicateAlmaInsuranceProductsIfExist()
    {
        $this->newCartMock->id = 2;
        $this->cartMock->id = 1;
        $this->insuranceHelperMock->shouldReceive('almaInsuranceProductsAlreadyExist')
            ->with($this->newCartMock)
            ->andReturn(true);
        $this->cartServiceMock->duplicateAlmaInsuranceProductsIfNotExist($this->newCartMock, $this->cartMock);
        $this->insuranceProductHelperSpy->shouldNotHaveReceived('duplicateAlmaInsuranceProducts');
    }

    /**
     * @return void
     *
     * @throws \Alma\PrestaShop\Exceptions\AlmaException
     * @throws \PrestaShopException
     */
    public function testDuplicateAlmaInsuranceProductsIfNotExist()
    {
        $this->newCartMock->id = 2;
        $this->cartMock->id = 1;
        $this->insuranceHelperMock->shouldReceive('almaInsuranceProductsAlreadyExist')
            ->with($this->newCartMock)
            ->andReturn(false);
        $this->cartServiceMock->duplicateAlmaInsuranceProductsIfNotExist($this->newCartMock, $this->cartMock);
        $this->insuranceProductHelperSpy->shouldHaveReceived('duplicateAlmaInsuranceProducts')->once();
    }

    /**
     * @throws AlmaException
     * @throws CartException
     */
    public function testDeleteProductWithoutProductId()
    {
        $this->cartMock->id = 12;

        $this->expectException(CartException::class);
        $this->cartService->deleteProductByCartId(false, $this->cartMock->id);
    }

    /**
     * @throws AlmaException
     * @throws CartException
     */
    public function testDeleteProductWithoutCartId()
    {
        $idProduct = 23;

        $this->expectException(CartException::class);
        $this->cartService->deleteProductByCartId($idProduct, $this->cartMock->id);
    }

    /**
     * @throws CartException
     * @throws AlmaException
     */
    public function testDeleteProductThrowExceptionIfGetAttributeCombinationsByProductIdReturnEmptyArray()
    {
        $languageId = 1;
        $this->productMock->id = 10;
        $this->cartMock->id = 12;
        $this->cartFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->cartMock->id)
            ->willReturn($this->cartMock);
        $this->contextFactoryMock->expects($this->once())
            ->method('getContextLanguageId')
            ->willReturn($languageId);
        $this->productHelperMock->expects($this->once())
            ->method('getAttributeCombinationsByProductId')
            ->with($this->productMock->id, $languageId)
            ->willThrowException(new CartException('Error attribute combinations'));
        $this->expectException(CartException::class);
        $this->cartService->deleteProductByCartId($this->productMock->id, $this->cartMock->id);
    }

    /**
     * @throws CartException
     * @throws AlmaException
     */
    public function testDeleteProductWithProductIdAndCartId()
    {
        $languageId = 1;
        $this->productMock->id = 10;
        $this->cartMock->id = 12;
        $this->cartMock->expects($this->exactly(4))
            ->method('deleteProduct')
            ->withConsecutive(
                [
                    $this->productMock->id, $this->attributeCombinationsData()[0]['id_product_attribute'],
                ],
                [
                    $this->productMock->id, $this->attributeCombinationsData()[1]['id_product_attribute'],
                ],
                [
                    $this->productMock->id, $this->attributeCombinationsData()[2]['id_product_attribute'],
                ],
                [
                    $this->productMock->id, $this->attributeCombinationsData()[3]['id_product_attribute'],
                ]
            )
            ->willReturnOnConsecutiveCalls(true, true, true, true);
        $this->cartFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->cartMock->id)
            ->willReturn($this->cartMock);
        $this->contextFactoryMock->expects($this->once())
            ->method('getContextLanguageId')
            ->willReturn($languageId);
        $this->productHelperMock->expects($this->once())
            ->method('getAttributeCombinationsByProductId')
            ->with($this->productMock->id, $languageId)
            ->willReturn($this->attributeCombinationsData());
        $this->assertTrue($this->cartService->deleteProductByCartId($this->productMock->id, $this->cartMock->id));
    }

    /**
     * @return array
     */
    public function attributeCombinationsData()
    {
        return [
            [
                'id_product_attribute' => '9',
                'id_product' => (string) $this->productMock->id,
                'reference' => 'demo_3',
                'supplier_reference' => '',
                'location' => '',
                'ean13' => '',
                'isbn' => '',
                'upc' => '',
                'mpn' => '',
                'wholesale_price' => '0.000000',
                'price' => '0.000000',
                'ecotax' => '0.000000',
                'quantity' => 1200,
                'weight' => '0.000000',
                'unit_price_impact' => '0.000000',
                'default_on' => '1',
                'minimal_quantity' => '1',
                'low_stock_threshold' => null,
                'low_stock_alert' => '0',
                'available_date' => '0000-00-00',
                'id_shop' => '1',
                'id_attribute_group' => '1',
                'is_color_group' => '0',
                'group_name' => null,
                'attribute_name' => null,
                'id_attribute' => '1',
            ],
            [
                'id_product_attribute' => '10',
                'id_product' => (string) $this->productMock->id,
                'reference' => 'demo_3',
                'supplier_reference' => '',
                'location' => '',
                'ean13' => '',
                'isbn' => '',
                'upc' => '',
                'mpn' => '',
                'wholesale_price' => '0.000000',
                'price' => '0.000000',
                'ecotax' => '0.000000',
                'quantity' => 300,
                'weight' => '0.000000',
                'unit_price_impact' => '0.000000',
                'default_on' => null,
                'minimal_quantity' => '1',
                'low_stock_threshold' => null,
                'low_stock_alert' => '0',
                'available_date' => '0000-00-00',
                'id_shop' => '1',
                'id_attribute_group' => '1',
                'is_color_group' => '0',
                'group_name' => null,
                'attribute_name' => null,
                'id_attribute' => '2',
            ],
            [
                'id_product_attribute' => '11',
                'id_product' => (string) $this->productMock->id,
                'reference' => 'demo_3',
                'supplier_reference' => '',
                'location' => '',
                'ean13' => '',
                'isbn' => '',
                'upc' => '',
                'mpn' => '',
                'wholesale_price' => '0.000000',
                'price' => '0.000000',
                'ecotax' => '0.000000',
                'quantity' => 300,
                'weight' => '0.000000',
                'unit_price_impact' => '0.000000',
                'default_on' => null,
                'minimal_quantity' => '1',
                'low_stock_threshold' => null,
                'low_stock_alert' => '0',
                'available_date' => '0000-00-00',
                'id_shop' => '1',
                'id_attribute_group' => '1',
                'is_color_group' => '0',
                'group_name' => null,
                'attribute_name' => null,
                'id_attribute' => '3',
            ],
            [
                'id_product_attribute' => '12',
                'id_product' => (string) $this->productMock->id,
                'reference' => 'demo_3',
                'supplier_reference' => '',
                'location' => '',
                'ean13' => '',
                'isbn' => '',
                'upc' => '',
                'mpn' => '',
                'wholesale_price' => '0.000000',
                'price' => '0.000000',
                'ecotax' => '0.000000',
                'quantity' => 300,
                'weight' => '0.000000',
                'unit_price_impact' => '0.000000',
                'default_on' => null,
                'minimal_quantity' => '1',
                'low_stock_threshold' => null,
                'low_stock_alert' => '0',
                'available_date' => '0000-00-00',
                'id_shop' => '1',
                'id_attribute_group' => '1',
                'is_color_group' => '0',
                'group_name' => null,
                'attribute_name' => null,
                'id_attribute' => '4',
            ],
        ];
    }
}
