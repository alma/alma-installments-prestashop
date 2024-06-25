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

use Alma\PrestaShop\Builders\Helpers\InsuranceProductHelperBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Helpers\InsuranceProductHelper;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use PHPUnit\Framework\TestCase;

class InsuranceProductHelperTest extends TestCase
{
    /**
     * @var InsuranceProductHelper
     */
    protected $insuranceProductHelper;
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
     * @var \Mockery\Mock|(\Mockery\MockInterface&InsuranceProductHelperBuilder)
     */
    private $insuranceProductHelperBuilderMock;

    protected function setUp()
    {
        $this->insuranceProductHelperBuilderMock = \Mockery::mock(InsuranceProductHelperBuilder::class)->makePartial();
        $this->insuranceProductHelper = $this->insuranceProductHelperBuilderMock->getInstance();
        $this->almaInsuranceProductRepository = \Mockery::mock(AlmaInsuranceProductRepository::class)->makePartial();

        $this->context = \Mockery::mock(\Context::class);
        $this->contextFactory = \Mockery::mock(ContextFactory::class)->makePartial();
        $this->contextFactory->shouldReceive('getContext')->andReturn($this->context);

        $this->cart = $this->createMock(\Cart::class);
        $this->newCart = $this->createMock(\Cart::class);
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

        $this->almaInsuranceProductRepositorySpy = \Mockery::spy(AlmaInsuranceProductRepository::class)->makePartial();

        $this->almaInsuranceProductRepositorySpy->shouldReceive('getByCartIdAndShop')
            ->with($this->cart->id, $this->context->shop->id)
            ->andReturn($almaInsuranceProduct);

        $this->almaInsuranceProductRepositorySpy->shouldReceive('add')->andReturn(null);

        $this->insuranceProductHelper = \Mockery::mock(InsuranceProductHelper::class, [
            $this->almaInsuranceProductRepositorySpy,
          $this->contextFactory,
        ])->makePartial();

        $this->insuranceProductHelper->duplicateAlmaInsuranceProducts($this->cart->id, $this->newCart->id);
        $this->almaInsuranceProductRepositorySpy->shouldHaveReceived('add')->once();
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

        $this->almaInsuranceProductRepositorySpy = \Mockery::spy(AlmaInsuranceProductRepository::class)->makePartial();

        $this->almaInsuranceProductRepositorySpy->shouldReceive('getByCartIdAndShop')
            ->with($this->cart->id, $this->context->shop->id)
            ->andReturn($almaInsuranceProduct);

        $this->almaInsuranceProductRepositorySpy->shouldReceive('add')->andReturn(null);

        $this->insuranceProductHelper = \Mockery::mock(InsuranceProductHelper::class, [
            $this->almaInsuranceProductRepositorySpy,
            $this->contextFactory,
        ])->makePartial();

        $this->insuranceProductHelper->duplicateAlmaInsuranceProducts($this->cart->id, $this->newCart->id);
        $this->almaInsuranceProductRepositorySpy->shouldHaveReceived('add')->twice();
    }
}
