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

namespace Unit\Services;

use Alma\PrestaShop\Factories\CombinationFactory;
use Alma\PrestaShop\Factories\ProductFactory;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Services\InsuranceProductService;
use PHPUnit\Framework\TestCase;

class InsuranceProductServiceTest extends TestCase
{
    /**
     * @var InsuranceProductService
     */
    protected $insuranceProductService;
    /**
     * @var AlmaInsuranceProductRepository|(AlmaInsuranceProductRepository&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $almaInsuranceProductRepository;
    /**
     * @var \Cart|(\Cart&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cart;
    /**
     * @var \Cart|(\Cart&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $newCart;
    /**
     * @var ProductFactory|(ProductFactory&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactoryMock;
    /**
     * @var CombinationFactory|(CombinationFactory&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $combinationFactoryMock;
    /**
     * @var \Combination|(\Combination&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $combinationMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Product|(\Product&\PHPUnit_Framework_MockObject_MockObject)
     */
    protected $productMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Product|(\Product&\PHPUnit_Framework_MockObject_MockObject)
     */
    protected $productInsuranceMock;

    protected function setUp()
    {
        $this->almaInsuranceProductRepository = $this->createMock(AlmaInsuranceProductRepository::class);
        $this->productFactoryMock = $this->createMock(ProductFactory::class);
        $this->combinationFactoryMock = $this->createMock(CombinationFactory::class);
        $this->linkMock = $this->createMock(\Link::class);
        $this->insuranceProductService = new InsuranceProductService(
            $this->productFactoryMock,
            $this->combinationFactoryMock,
            $this->linkMock,
            $this->almaInsuranceProductRepository
        );
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
    }

    /**
     * @throws \PrestaShopDatabaseException
     */
    public function testDuplicateInsuranceProductsWithOneInsuranceProduct()
    {
        $almaInsuranceProduct = [[
            'id_product' => '1',
            'id_product_attribute' => '1',
            'id_customization' => '0',
            'id_product_insurance' => '20',
            'id_product_attribute_insurance' => '42',
            'id_address_delivery' => '7',
            'price' => '2150',
            'insurance_contract_id' => 'insurance_contract_3CR76PNE04X7hHEY7V65rZ',
            'cms_reference' => '1-1',
            'product_price' => '35000',
        ]];
        $this->cart->id = 1;
        $this->newCart->id = 2;
        $this->almaInsuranceProductRepository->expects($this->once())
            ->method('add');
        $this->almaInsuranceProductRepository->expects($this->once())
            ->method('getByCartIdAndShop')
            ->with($this->cart->id, $this->context->shop->id)
            ->willReturn($almaInsuranceProduct);
        $this->insuranceProductService->duplicateInsuranceProducts($this->cart, $this->newCart);
    }

    /**
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     */
    public function testDuplicateInsuranceProductsWithTwoInsuranceProduct()
    {
        $almaInsuranceProduct = [
            [
                'id_product' => '1',
                'id_product_attribute' => '1',
                'id_customization' => '0',
                'id_product_insurance' => '20',
                'id_product_attribute_insurance' => '42',
                'id_address_delivery' => '7',
                'price' => '2150',
                'insurance_contract_id' => 'insurance_contract_3CR76PNE04X7hHEY7V65rZ',
                'cms_reference' => '1-1',
                'product_price' => '35000',
            ],
            [
                'id_product' => '2',
                'id_product_attribute' => '1',
                'id_customization' => '0',
                'id_product_insurance' => '20',
                'id_product_attribute_insurance' => '42',
                'id_address_delivery' => '7',
                'price' => '2250',
                'insurance_contract_id' => 'insurance_contract_ABC',
                'cms_reference' => '2-1',
                'product_price' => '25000',
            ], ];
        $this->cart->id = 1;
        $this->newCart->id = 2;
        $this->almaInsuranceProductRepository->expects($this->exactly(2))
            ->method('add');
        $this->almaInsuranceProductRepository->expects($this->once())
            ->method('getByCartIdAndShop')
            ->with($this->cart->id, $this->context->shop->id)
            ->willReturn($almaInsuranceProduct);
        $this->insuranceProductService->duplicateInsuranceProducts($this->cart, $this->newCart);
    }

    public function testGetItemCartInsuranceProductAttributes()
    {
        $expectedResult = [
            [
                'idInsuranceProduct' => 21,
                'nameInsuranceProduct' => 'Name insurance Alma',
                'urlImageInsuranceProduct' => '//http://url_image',
                'reference' => 'Reference Vol + Casse Alma',
                'price' => 47.899999999999999,
                'quantity' => '2',
                'insuranceContractId' => 'insurance_contract_ABCD123',
                'idsAlmaInsuranceProduct' => '["22","23"]',
            ],
            [
                'idInsuranceProduct' => 21,
                'nameInsuranceProduct' => 'Name insurance Alma',
                'urlImageInsuranceProduct' => '//http://url_image',
                'reference' => 'Reference Vol Alma',
                'price' => 22.899999999999999,
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
                'price' => '4790',
            ],
            [
                'nbInsurance' => '1',
                'id_product_insurance' => '21',
                'id_product_attribute_insurance' => '34',
                'price' => '2290',
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

        $this->linkMock->expects($this->exactly(2))
            ->method('getImageLink')
            ->willReturn('http://url_image');
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
            $this->insuranceProductService->getItemsCartInsuranceProductAttributes($this->productMock, $cartId, $insuranceProductId)
        );
    }
}
