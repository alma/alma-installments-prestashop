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
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\InsuranceProductHelper;
use Alma\PrestaShop\Modules\OpartSaveCart\CartService as CartServiceAlias;
use Alma\PrestaShop\Repositories\CartProductRepository;
use Alma\PrestaShop\Services\CartService;
use PHPUnit\Framework\TestCase;

class CartServiceTest extends TestCase
{
    /**
     * @var \Cart|(\Cart&\Mockery\LegacyMockInterface)|(\Cart&\Mockery\MockInterface)|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $cartMock;
    /**
     * @var \Cart|(\Cart&\Mockery\LegacyMockInterface)|(\Cart&\Mockery\MockInterface)|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $newCartMock;
    /**
     * @var ContextFactory|(ContextFactory&\Mockery\LegacyMockInterface)|(ContextFactory&\Mockery\MockInterface)|\Mockery\LegacyMockInterface|\Mockery\MockInterface|(\Mockery\MockInterface&ContextFactory)
     */
    protected $contextFactoryMock;
    /**
     * @var CartServiceAlias|(CartServiceAlias&\Mockery\LegacyMockInterface)|(CartServiceAlias&\Mockery\MockInterface)|\Mockery\LegacyMockInterface|\Mockery\MockInterface|(\Mockery\MockInterface&CartServiceAlias)
     */
    protected $opartCartSaveServiceSpy;
    /**
     * @var \#M#C\Mockery.mock[]|(\#M#C\Mockery.mock[]&\Mockery\LegacyMockInterface)|(\#M#C\Mockery.mock[]&\Mockery\MockInterface)|\#P#C\Alma\PrestaShop\Tests\Unit\Services\CartServiceTest.contextFactoryMock[]|(\#P#C\Alma\PrestaShop\Tests\Unit\Services\CartServiceTest.contextFactoryMock[]&\Mockery\LegacyMockInterface)|(\#P#C\Alma\PrestaShop\Tests\Unit\Services\CartServiceTest.contextFactoryMock[]&\Mockery\MockInterface)|\#P#C\Alma\PrestaShop\Tests\Unit\Services\CartServiceTest.opartCartSaveServiceSpy[]|(\#P#C\Alma\PrestaShop\Tests\Unit\Services\CartServiceTest.opartCartSaveServiceSpy[]&\Mockery\LegacyMockInterface)|(\#P#C\Alma\PrestaShop\Tests\Unit\Services\CartServiceTest.opartCartSaveServiceSpy[]&\Mockery\MockInterface)|\#P#S\Alma\PrestaShop\Tests\Unit\Services\CartServiceTest.contextFactoryMock[]|(\#P#S\Alma\PrestaShop\Tests\Unit\Services\CartServiceTest.contextFactoryMock[]&\Mockery\LegacyMockInterface)|(\#P#S\Alma\PrestaShop\Tests\Unit\Services\CartServiceTest.contextFactoryMock[]&\Mockery\MockInterface)|\#P#S\Alma\PrestaShop\Tests\Unit\Services\CartServiceTest.opartCartSaveServiceSpy[]|(\#P#S\Alma\PrestaShop\Tests\Unit\Services\CartServiceTest.opartCartSaveServiceSpy[]&\Mockery\LegacyMockInterface)|(\#P#S\Alma\PrestaShop\Tests\Unit\Services\CartServiceTest.opartCartSaveServiceSpy[]&\Mockery\MockInterface)|CartService|(CartService&\Mockery\LegacyMockInterface)|(CartService&\Mockery\MockInterface)|\Mockery\LegacyMockInterface|\Mockery\MockInterface|(\Mockery\MockInterface&\#M#C\Mockery.mock[])|(\Mockery\MockInterface&\#P#C\Alma\PrestaShop\Tests\Unit\Services\CartServiceTest.contextFactoryMock[])|(\Mockery\MockInterface&\#P#C\Alma\PrestaShop\Tests\Unit\Services\CartServiceTest.opartCartSaveServiceSpy[])|(\Mockery\MockInterface&\#P#S\Alma\PrestaShop\Tests\Unit\Services\CartServiceTest.contextFactoryMock[])|(\Mockery\MockInterface&\#P#S\Alma\PrestaShop\Tests\Unit\Services\CartServiceTest.opartCartSaveServiceSpy[])|(\Mockery\MockInterface&CartService)
     */
    protected $cartServiceMock;
    /**
     * @var ToolsFactory|(ToolsFactory&\Mockery\LegacyMockInterface)|(ToolsFactory&\Mockery\MockInterface)|\Mockery\LegacyMockInterface|\Mockery\Mock|\Mockery\MockInterface|(\Mockery\MockInterface&ToolsFactory)
     */
    protected $toolsFactoryMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->cartMock = \Mockery::mock(\Cart::class);
        $this->newCartMock = \Mockery::mock(\Cart::class);
        $this->contextFactoryMock = \Mockery::mock(ContextFactory::class);
        $this->opartCartSaveServiceSpy = \Mockery::spy(CartServiceAlias::class);
        $this->toolsFactoryMock = \Mockery::mock(ToolsFactory::class);
        $this->insuranceHelperMock = \Mockery::mock(InsuranceHelper::class);
        $this->insuranceProductHelperSpy = \Mockery::spy(InsuranceProductHelper::class);
        $this->cartServiceMock = \Mockery::mock(
            CartService::class,
            [
                \Mockery::mock(CartProductRepository::class),
                $this->contextFactoryMock,
                $this->opartCartSaveServiceSpy,
                $this->insuranceHelperMock,
                $this->insuranceProductHelperSpy,
                $this->toolsFactoryMock,
            ]
        )->makePartial();
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
}
