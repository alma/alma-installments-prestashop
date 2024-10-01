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

use Alma\PrestaShop\Exceptions\CartException;
use Alma\PrestaShop\Exceptions\InsuranceProductException;
use Alma\PrestaShop\Factories\CartFactory;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\LinkFactory;
use Alma\PrestaShop\Factories\ProductFactory;
use Alma\PrestaShop\Factories\ToolsFactory;
use Alma\PrestaShop\Helpers\Admin\AdminInsuranceHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\ImageHelper;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\ProductRepository;
use Alma\PrestaShop\Services\AttributeGroupProductService;
use Alma\PrestaShop\Services\AttributeProductService;
use Alma\PrestaShop\Services\CartService;
use Alma\PrestaShop\Services\CombinationProductAttributeService;
use Alma\PrestaShop\Services\InsuranceApiService;
use Alma\PrestaShop\Services\InsuranceProductService;
use Alma\PrestaShop\Services\InsuranceService;
use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException;

class InsuranceProductServiceTest extends TestCase
{
    /**
     * @var InsuranceProductService
     */
    protected $insuranceProductServiceMock;
    /**
     * @var AlmaInsuranceProductRepository
     */
    protected $almaInsuranceProductRepository;
    /**
     * @var \Cart
     */
    protected $cartMock1;
    /**
     * @var \Cart
     */
    protected $cartMock2;
    /**
     * @var ProductFactory
     */
    protected $productFactoryMock;
    /**
     * @var \Product
     */
    protected $productMock;
    /**
     * @var \Product
     */
    protected $productInsuranceMock;
    /**
     * @var ToolsFactory
     */
    protected $toolsFactorySpy;
    /**
     * @var ContextFactory
     */
    protected $contextFactoryMock;
    /**
     * @var ImageHelper
     */
    protected $imageHelperMock;
    /**
     * @var ToolsHelper
     */
    protected $toolsHelperMock;
    /**
     * @var PriceHelper
     */
    protected $priceHelperMock;
    /**
     * @var LinkFactory
     */
    protected $linkFactoryMock;
    /**
     * @var \Link
     */
    protected $linkMock;
    /**
     * @var InsuranceProductService
     */
    protected $insuranceProductService;
    /**
     * @var \Language|(\Language&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Product|(\Product&\PHPUnit_Framework_MockObject_MockObject)
     */
    protected $insuranceProductMock;
    /**
     * @var \ObjectModel|(\ObjectModel&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectModelMock;
    /**
     * @var CartFactory
     */
    protected $cartFactoryMock;
    /**
     * @var CartService
     */
    protected $cartServiceMock;

    public function setUp()
    {
        $this->linkMock = $this->createMock(\Link::class);
        $this->toolsFactorySpy = \Mockery::spy(ToolsFactory::class);
        $this->contextFactoryMock = \Mockery::mock(ContextFactory::class)->makePartial();
        $this->almaInsuranceProductRepository = $this->createMock(AlmaInsuranceProductRepository::class);
        $this->productFactoryMock = $this->createMock(ProductFactory::class);
        $this->linkFactoryMock = $this->createMock(LinkFactory::class);
        $this->linkFactoryMock->method('create')->willReturn($this->linkMock);
        $this->cartMock1 = $this->createMock(\Cart::class);
        $this->cartMock1->id = 15;
        $this->cartMock2 = $this->createMock(\Cart::class);
        $this->cartMock2->id = 17;
        $this->cartFactoryMock = $this->createMock(CartFactory::class);
        $this->context = $this->createMock(\Context::class);
        $this->productRepositoryMock = $this->createMock(ProductRepository::class);
        $this->languageMock = $this->createMock(\Language::class);
        $this->languageMock->id = 1;
        $this->shop = $this->createMock(\Shop::class);
        $this->shop->id = 1;
        $this->productMock = $this->createMock(\Product::class);
        $this->productInsuranceMock = $this->createMock(\Product::class);
        $this->context->shop = $this->shop;
        $this->context->language = $this->languageMock;
        $this->imageHelperMock = $this->createMock(ImageHelper::class);
        $this->toolsHelperMock = $this->createMock(ToolsHelper::class);
        $this->priceHelperMock = $this->createMock(PriceHelper::class);
        $this->cartServiceMock = $this->createMock(CartService::class);
        $this->insuranceProductServiceMock = \Mockery::mock(InsuranceProductService::class,
        [
            $this->productFactoryMock,
            $this->linkFactoryMock,
            $this->almaInsuranceProductRepository,
            $this->contextFactoryMock,
            \Mockery::mock(AttributeGroupProductService::class),
            \Mockery::mock(AttributeProductService::class),
            \Mockery::mock(CombinationProductAttributeService::class),
            \Mockery::mock(InsuranceService::class),
            \Mockery::mock(CartService::class),
            \Mockery::mock(ProductRepository::class),
            \Mockery::mock(ProductHelper::class),
            \Mockery::mock(InsuranceApiService::class),
            $this->priceHelperMock,
            \Mockery::mock(AdminInsuranceHelper::class),
            $this->toolsFactorySpy,
            $this->imageHelperMock,
            $this->toolsHelperMock,
            $this->cartFactoryMock,
        ])->makePartial();
        $this->insuranceProductService = new InsuranceProductService(
            $this->productFactoryMock,
            $this->linkFactoryMock,
            $this->almaInsuranceProductRepository,
            $this->contextFactoryMock,
            $this->createMock(AttributeGroupProductService::class),
            $this->createMock(AttributeProductService::class),
            $this->createMock(CombinationProductAttributeService::class),
            $this->createMock(InsuranceService::class),
            $this->cartServiceMock,
            $this->productRepositoryMock,
            $this->createMock(ProductHelper::class),
            $this->createMock(InsuranceApiService::class),
            $this->priceHelperMock,
            $this->createMock(InsuranceHelper::class),
            $this->toolsFactorySpy,
            $this->imageHelperMock,
            $this->toolsHelperMock,
            $this->cartFactoryMock
        );
        $this->insuranceProductMock = $this->createMock(\Product::class);
        $this->insuranceProductMock->id = '10';
        $this->insuranceProductMock->active = true;
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        $this->toolsFactorySpy = null;
        $this->contextFactoryMock = null;
        $this->insuranceProductServiceMock = null;
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws LocalizationException
     */
    public function testGetItemCartInsuranceProductAttributes()
    {
        $expectedResult = [
            [
                'idInsuranceProduct' => 21,
                'nameInsuranceProduct' => 'Name insurance Alma',
                'urlImageInsuranceProduct' => '//url_image',
                'reference' => 'Reference Vol + Casse Alma',
                'unitPrice' => '47.90 €',
                'price' => '95.80 €',
                'quantity' => '2',
                'insuranceContractId' => 'insurance_contract_ABCD123',
                'idsAlmaInsuranceProduct' => '["22","23"]',
            ],
            [
                'idInsuranceProduct' => 21,
                'nameInsuranceProduct' => 'Name insurance Alma',
                'urlImageInsuranceProduct' => '//url_image',
                'reference' => 'Reference Vol Alma',
                'unitPrice' => '22.90 €',
                'price' => '22.90 €',
                'quantity' => '1',
                'insuranceContractId' => 'insurance_contract_EFGH456',
                'idsAlmaInsuranceProduct' => '["24"]',
            ],
        ];
        $returnGetCountInsuranceProductAttribute = [
            [
                'nbInsurance' => '2',
                'id_product_insurance' => '21',
                'id_product_attribute_insurance' => '33',
                'unitPrice' => '4790',
                'price' => '9580',
            ],
            [
                'nbInsurance' => '1',
                'id_product_insurance' => '21',
                'id_product_attribute_insurance' => '34',
                'unitPrice' => '2290',
                'price' => '2290',
            ],
        ];
        $returnContractByProduct1 = [
            [
                'id_alma_insurance_product' => '22',
                'insurance_contract_id' => 'insurance_contract_ABCD123',
                'insurance_contract_name' => 'Reference Vol + Casse Alma',
            ],
            [
                'id_alma_insurance_product' => '23',
                'insurance_contract_id' => 'insurance_contract_ABCD123',
                'insurance_contract_name' => 'Reference Vol + Casse Alma',
            ],
        ];
        $returnContractByProduct2 = [
            [
                'id_alma_insurance_product' => '24',
                'insurance_contract_id' => 'insurance_contract_EFGH456',
                'insurance_contract_name' => 'Reference Vol Alma',
            ],
        ];
        $this->productMock->id = 27;
        $cartId = 45;
        $insuranceProductId = 21;
        $this->productInsuranceMock->id = 21;
        $this->productInsuranceMock->name = [
            '1' => 'Name insurance Alma',
        ];
        $this->productInsuranceMock->link_rewrite = [1 => ''];

        $this->toolsHelperMock->expects($this->exactly(2))
            ->method('getJsonValues')
            ->willReturnOnConsecutiveCalls('["22","23"]', '["24"]');
        $this->priceHelperMock->expects($this->exactly(4))
            ->method('convertPriceFromCents');
        $this->toolsHelperMock->expects($this->exactly(4))
            ->method('displayPrice')
            ->willReturnOnConsecutiveCalls('47.90 €', '95.80 €', '22.90 €', '22.90 €');
        $this->linkMock->expects($this->exactly(2))
            ->method('getImageLink')
            ->willReturn('url_image');
        $this->almaInsuranceProductRepository->expects($this->exactly(2))
            ->method('getContractByProductAndCartIdAndShopAndInsuranceProductAttribute')
            ->willReturnOnConsecutiveCalls($returnContractByProduct1, $returnContractByProduct2);
        $this->productInsuranceMock->expects($this->once())
            ->method('getImages')
            ->willReturn([
                [
                    'id_image' => 59,
                ],
            ]);
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->productInsuranceMock);
        $this->almaInsuranceProductRepository->expects($this->once())
            ->method('getCountInsuranceProductAttributeByProductAndCartIdAndShopId')
            ->willReturn($returnGetCountInsuranceProductAttribute);
        $this->assertEquals(
            $expectedResult,
            $this->insuranceProductServiceMock->getItemsCartInsuranceProductAttributes($this->productMock, $cartId, $insuranceProductId)
        );
    }

    /**
     * @return void
     */
    public function testCanHandleAddingProductInsuranceWithInputsToAddInsurance()
    {
        $this->toolsFactorySpy->shouldReceive('getIsset')
            ->with('alma_id_insurance_contract')
            ->andReturn('insurance_contract_abcd1234');

        $this->toolsFactorySpy->shouldReceive('getValue')
            ->with('add')
            ->andReturn(1);

        $this->toolsFactorySpy->shouldReceive('getValue')
            ->with('action')
            ->andReturn('update');

        $this->assertTrue($this->insuranceProductServiceMock->canHandleAddingProductInsuranceOnce());
    }

    /**
     * @return void
     */
    public function testCanHandleAddingProductInsuranceWithoutInputInsurance()
    {
        $this->toolsFactorySpy->shouldNotHaveReceived('getIsset');

        $this->toolsFactorySpy->shouldReceive('getValue')
            ->with('add')
            ->andReturn(1);

        $this->toolsFactorySpy->shouldReceive('getValue')
            ->with('action')
            ->andReturn('update');

        $this->assertFalse($this->insuranceProductServiceMock->canHandleAddingProductInsuranceOnce());
    }

    /**
     * @return void
     */
    public function testCanHandleAddingProductInsuranceWithInputInsuranceWithoutInputAdd()
    {
        $this->toolsFactorySpy->shouldReceive('getIsset')
            ->with('alma_id_insurance_contract')
            ->andReturn('insurance_contract_abcd1234');

        $this->toolsFactorySpy->shouldReceive('getValue')
            ->with('delete')
            ->andReturn(1);

        $this->toolsFactorySpy->shouldReceive('getValue')
            ->with('action')
            ->andReturn('update');

        $this->assertFalse($this->insuranceProductServiceMock->canHandleAddingProductInsuranceOnce());
    }

    /**
     * @return void
     */
    public function testCanHandleAddingProductInsuranceWithInputInsuranceWithInputAddAndActionShow()
    {
        $this->toolsFactorySpy->shouldReceive('getIsset')
            ->with('alma_id_insurance_contract')
            ->andReturn('insurance_contract_abcd1234');

        $this->toolsFactorySpy->shouldReceive('getValue')
            ->with('add')
            ->andReturn(1);

        $this->toolsFactorySpy->shouldReceive('getValue')
            ->with('action')
            ->andReturn('show');

        $this->assertFalse($this->insuranceProductServiceMock->canHandleAddingProductInsuranceOnce());
    }

    /**
     * @dataProvider insuranceProductStateWithWrongParamsDataProvider
     *
     * @param $state
     *
     * @return void
     *
     * @throws InsuranceProductException
     * @throws \PrestaShopException
     */
    public function testHandleInsuranceProductStateWithWrongParam($state)
    {
        $this->expectException(InsuranceProductException::class);
        $this->insuranceProductService->handleInsuranceProductState($state);
    }

    /**
     * @dataProvider insuranceProductStateWithRightParamsDataProvider
     *
     * @param $state
     *
     * @return void
     *
     * @throws InsuranceProductException
     * @throws \PrestaShopException
     */
    public function testHandleInsuranceProductStateWithRightParams($state)
    {
        $this->productRepositoryMock->expects($this->once())
            ->method('getProductIdByReference')
            ->with(ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE, $this->languageMock->id)
            ->willReturn($this->insuranceProductMock->id);
        $this->insuranceProductMock->expects($this->once())
            ->method('save');
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->insuranceProductMock->id)
            ->willReturn($this->insuranceProductMock);
        $this->insuranceProductService->handleInsuranceProductState($state);
    }

    /**
     * @return void
     *
     * @throws InsuranceProductException
     * @throws \PrestaShopDatabaseException
     */
    public function testRemoveInsuranceProductsNotOrderedWithNoCartsReturned()
    {
        $this->almaInsuranceProductRepository->expects($this->once())
            ->method('getCartsNotOrdered')
            ->willReturn([]);
        $this->assertTrue($this->insuranceProductService->removeInsuranceProductsNotOrdered());
    }

    /**
     * @throws InsuranceProductException
     * @throws \PrestaShopDatabaseException
     */
    public function testRemoveInsuranceProductsNotOrderedWithCartIdsAndWithoutInsuranceProducts()
    {
        $this->almaInsuranceProductRepository->expects($this->once())
            ->method('getCartsNotOrdered')
            ->willReturn([
                ['id_cart' => $this->cartMock1->id],
                ['id_cart' => $this->cartMock2->id],
            ]);
        $this->productRepositoryMock->expects($this->once())
            ->method('getProductIdByReference')
            ->with(ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE)
            ->willReturn(false);
        $this->expectException(InsuranceProductException::class);
        $this->insuranceProductService->removeInsuranceProductsNotOrdered();
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws InsuranceProductException
     */
    public function testRemoveInsuranceProductsNotOrderedThrowExceptionByDeleteProductByCartId()
    {
        $this->almaInsuranceProductRepository->expects($this->once())
            ->method('getCartsNotOrdered')
            ->willReturn([
                ['id_cart' => $this->cartMock1->id],
                ['id_cart' => $this->cartMock2->id],
            ]);
        $this->productRepositoryMock->expects($this->once())
            ->method('getProductIdByReference')
            ->with(ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE)
            ->willReturn($this->insuranceProductMock->id);
        $this->cartServiceMock->expects($this->exactly(2))
            ->method('deleteProductByCartId')
            ->withConsecutive(
                [$this->insuranceProductMock->id, $this->cartMock1->id],
                [$this->insuranceProductMock->id, $this->cartMock2->id]
            )
            ->willReturnOnConsecutiveCalls(
                true,
                $this->throwException(new CartException("Product id and cart id are required. ProductId: {$this->insuranceProductMock->id}, cartId: {$this->cartMock2->id}"))
            );

        $this->expectException(InsuranceProductException::class);
        $this->insuranceProductService->removeInsuranceProductsNotOrdered();
    }

    /**
     * @throws InsuranceProductException
     * @throws \PrestaShopDatabaseException
     */
    public function testRemoveInsuranceProductsNotOrderedWithRightData()
    {
        $this->almaInsuranceProductRepository->expects($this->once())
            ->method('getCartsNotOrdered')
            ->willReturn([
                ['id_cart' => $this->cartMock1->id],
                ['id_cart' => $this->cartMock2->id],
                ['id_cart' => '34'],
            ]);
        $this->productRepositoryMock->expects($this->once())
            ->method('getProductIdByReference')
            ->with(ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE)
            ->willReturn($this->insuranceProductMock->id);
        $this->cartServiceMock->expects($this->exactly(3))
            ->method('deleteProductByCartId')
            ->withConsecutive(
                [$this->insuranceProductMock->id, $this->cartMock1->id],
                [$this->insuranceProductMock->id, $this->cartMock2->id],
                [$this->insuranceProductMock->id, '34']
            )
            ->willReturnOnConsecutiveCalls(true, true, false);

        $this->almaInsuranceProductRepository->expects($this->once())
            ->method('deleteAssociationsByCartIds')
            ->with("{$this->cartMock1->id}, {$this->cartMock2->id}, 34");

        $this->insuranceProductService->removeInsuranceProductsNotOrdered();
    }

    public function insuranceProductStateWithWrongParamsDataProvider()
    {
        return [
            'With a string' => ['toto'],
            'With an object' => [\stdClass::class],
            'With an array' => [[]],
            'With a number' => [5],
            'With null' => [null],
        ];
    }

    public function insuranceProductStateWithRightParamsDataProvider()
    {
        return [
            'With true' => [true],
            'With false' => [false],
            'With a string false' => ['false'],
            'With a string true' => ['true'],
            'With a string 0' => ['0'],
            'With a string 1' => ['1'],
            'With a int 0' => [0],
            'With a int 1' => [1],
        ];
    }
}
