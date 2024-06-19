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

use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\ToolsFactory;
use Alma\PrestaShop\Helpers\Admin\InsuranceHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
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
     * @var ToolsFactory
     */
    protected $toolsFactorySpy;
    /**
     * @var ContextFactory|(ContextFactory&\Mockery\LegacyMockInterface)|(ContextFactory&\Mockery\MockInterface)|\Mockery\LegacyMockInterface|\Mockery\MockInterface|(\Mockery\MockInterface&ContextFactory)
     */
    protected $contextFactoryMock;

    public function setUp()
    {
        $this->toolsFactorySpy = \Mockery::spy(ToolsFactory::class);
        $this->contextFactoryMock = \Mockery::mock(ContextFactory::class)->makePartial();
        $this->insuranceProductServiceMock = \Mockery::mock(InsuranceProductService::class,
        [
            \Mockery::mock(AlmaInsuranceProductRepository::class),
            $this->contextFactoryMock,
            \Mockery::mock(AttributeGroupProductService::class),
            \Mockery::mock(AttributeProductService::class),
            \Mockery::mock(CombinationProductAttributeService::class),
            \Mockery::mock(InsuranceService::class),
            \Mockery::mock(CartService::class),
            \Mockery::mock(ProductRepository::class),
            \Mockery::mock(ProductHelper::class),
            \Mockery::mock(InsuranceApiService::class),
            \Mockery::mock(PriceHelper::class),
            \Mockery::mock(InsuranceHelper::class),
            $this->toolsFactorySpy,
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

        $this->assertTrue($this->insuranceProductServiceMock->canHandleAddingProductInsurance());
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

        $this->assertFalse($this->insuranceProductServiceMock->canHandleAddingProductInsurance());
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

        $this->assertFalse($this->insuranceProductServiceMock->canHandleAddingProductInsurance());
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

        $this->assertFalse($this->insuranceProductServiceMock->canHandleAddingProductInsurance());
    }
}
