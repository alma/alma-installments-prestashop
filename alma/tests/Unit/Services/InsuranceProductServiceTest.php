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

    protected function setUp()
    {
        $this->almaInsuranceProductRepository = $this->createMock(AlmaInsuranceProductRepository::class);
        $this->insuranceProductService = new InsuranceProductService(
            $this->almaInsuranceProductRepository
        );
        $this->cart = $this->createMock(\Cart::class);
        $this->newCart = $this->createMock(\Cart::class);
        $this->context = $this->createMock(\Context::class);
        $this->shop = $this->createMock(\Shop::class);
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
        $this->shop->id = 1;
        $this->context->shop = $this->shop;
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
        $this->shop->id = 1;
        $this->context->shop = $this->shop;
        $this->almaInsuranceProductRepository->expects($this->exactly(2))
            ->method('add');
        $this->almaInsuranceProductRepository->expects($this->once())
            ->method('getByCartIdAndShop')
            ->with($this->cart->id, $this->context->shop->id)
            ->willReturn($almaInsuranceProduct);
        $this->insuranceProductService->duplicateInsuranceProducts($this->cart, $this->newCart);
    }
}
