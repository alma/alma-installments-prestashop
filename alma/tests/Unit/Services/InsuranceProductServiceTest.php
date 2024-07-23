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

use Alma\PrestaShop\Factories\CombinationFactory;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\LinkFactory;
use Alma\PrestaShop\Factories\ProductFactory;
use Alma\PrestaShop\Factories\ToolsFactory;
use Alma\PrestaShop\Helpers\Admin\AdminInsuranceHelper;
use Alma\PrestaShop\Helpers\ImageHelper;
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
    protected $cart;
    /**
     * @var \Cart
     */
    protected $newCart;
    /**
     * @var ProductFactory
     */
    protected $productFactoryMock;
    /**
     * @var CombinationFactory
     */
    protected $combinationFactoryMock;
    /**
     * @var \Combination
     */
    protected $combinationMock;
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

    public function setUp()
    {
        $this->linkMock = $this->createMock(\Link::class);
        $this->toolsFactorySpy = \Mockery::spy(ToolsFactory::class);
        $this->contextFactoryMock = \Mockery::mock(ContextFactory::class)->makePartial();
        $this->almaInsuranceProductRepository = $this->createMock(AlmaInsuranceProductRepository::class);
        $this->productFactoryMock = $this->createMock(ProductFactory::class);
        $this->combinationFactoryMock = $this->createMock(CombinationFactory::class);
        $this->linkFactoryMock = $this->createMock(LinkFactory::class);
        $this->linkFactoryMock->method('create')->willReturn($this->linkMock);
        $this->cart = $this->createMock(\Cart::class);
        $this->newCart = $this->createMock(\Cart::class);
        $this->context = $this->createMock(\Context::class);
        $this->languageMock = $this->createMock(\Language::class);
        $this->languageMock->id = 1;
        $this->shop = $this->createMock(\Shop::class);
        $this->shop->id = 1;
        $this->productMock = $this->createMock(\Product::class);
        $this->productInsuranceMock = $this->createMock(\Product::class);
        $this->combinationMock = $this->createMock(\Combination::class);
        $this->combinationMock2 = $this->createMock(\Combination::class);
        $this->context->shop = $this->shop;
        $this->context->language = $this->languageMock;
        $this->imageHelperMock = $this->createMock(ImageHelper::class);
        $this->toolsHelperMock = $this->createMock(ToolsHelper::class);
        $this->priceHelperMock = $this->createMock(PriceHelper::class);
        $this->insuranceProductServiceMock = \Mockery::mock(InsuranceProductService::class,
        [
            $this->productFactoryMock,
            $this->combinationFactoryMock,
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
        ])->makePartial();
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
     * // TODO : Need to fix the test
     *
     * @throws \PrestaShopDatabaseException
     */
    /*
    public function testGetItemCartInsuranceProductAttributes()
    {
        $expectedResult = [
            [
                'idInsuranceProduct' => 21,
                'nameInsuranceProduct' => 'Name insurance Alma',
                'urlImageInsuranceProduct' => '//url_image',
                'reference' => 'Reference Vol + Casse Alma',
                'unitPrice' => 47.899999999999999,
                'price' => '47.90 €',
                'quantity' => '2',
                'insuranceContractId' => 'insurance_contract_ABCD123',
                'idsAlmaInsuranceProduct' => '["22","23"]',
            ],
            [
                'idInsuranceProduct' => 21,
                'nameInsuranceProduct' => 'Name insurance Alma',
                'urlImageInsuranceProduct' => '//url_image',
                'reference' => 'Reference Vol Alma',
                'unitPrice' => 22.899999999999999,
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
                'price' => '47.90 €',
            ],
            [
                'nbInsurance' => '1',
                'id_product_insurance' => '21',
                'id_product_attribute_insurance' => '34',
                'unitPrice' => '2290',
                'price' => '22.90 €',
            ],
        ];
        $returnContractByProduct1 = [
            [
                'id_alma_insurance_product' => '22',
                'insurance_contract_id' => 'insurance_contract_ABCD123',
            ],
            [
                'id_alma_insurance_product' => '23',
                'insurance_contract_id' => 'insurance_contract_ABCD123',
            ],
        ];
        $returnContractByProduct2 = [
            [
                'id_alma_insurance_product' => '24',
                'insurance_contract_id' => 'insurance_contract_EFGH456',
            ],
        ];
        $this->productMock->id = 27;
        $cartId = 45;
        $insuranceProductId = 21;
        $this->productInsuranceMock->id = 21;
        $this->productInsuranceMock->name = [
            '1' => 'Name insurance Alma',
        ];
        $this->combinationMock->reference = 'Reference Vol + Casse Alma';
        $this->combinationMock2->reference = 'Reference Vol Alma';
        $this->productInsuranceMock->link_rewrite = [1 => ''];

        $this->toolsHelperMock->expects($this->exactly(2))
            ->method('getJsonValues')
            ->willReturnOnConsecutiveCalls('["22","23"]', '["24"]');
        $this->priceHelperMock->expects($this->exactly(2))
            ->method('convertPriceFromCents')
            ->willReturnOnConsecutiveCalls(47.899999999999999, 22.899999999999999);
        $this->linkMock->expects($this->exactly(2))
            ->method('getImageLink')
            ->willReturn('url_image');
        $this->almaInsuranceProductRepository->expects($this->exactly(2))
            ->method('getContractByProductAndCartIdAndShopAndInsuranceProductAttribute')
            ->willReturnOnConsecutiveCalls($returnContractByProduct1, $returnContractByProduct2);
        $this->combinationFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($this->combinationMock, $this->combinationMock2);
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
    */

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
}
