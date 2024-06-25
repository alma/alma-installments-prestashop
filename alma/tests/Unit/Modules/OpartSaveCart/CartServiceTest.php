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

namespace Alma\PrestaShop\Tests\Unit\Modules\OpartSaveCart;

use Alma\PrestaShop\Builders\Modules\OpartSaveCart\CartServiceBuilder;
use Alma\PrestaShop\Factories\CartFactory;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Factories\ToolsFactory;
use Alma\PrestaShop\Modules\OpartSaveCart\CartRepository;
use Alma\PrestaShop\Modules\OpartSaveCart\CartService;
use PHPUnit\Framework\TestCase;

class CartServiceTest extends TestCase
{
    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var \Mockery\Mock|(\Mockery\MockInterface&CartServiceBuilder)
     */
    protected $cartServiceBuilderMock;
    /**
     * @var \Mockery\Mock|(\Mockery\MockInterface&CartRepository)
     */
    protected $cartRepositoryMock;
    /**
     * @var \Mockery\Mock|(\Mockery\MockInterface&ModuleFactory)
     */
    protected $moduleFactoryMock;
    /**
     * @var \Mockery\Mock|(\Mockery\MockInterface&ToolsFactory)
     */
    protected $toolsFactoryMock;
    /**
     * @var \Mockery\Mock|(\Mockery\MockInterface&CartFactory)
     */
    protected $cartFactoryMock;
    /**
     * @var \Mockery\Mock|(\Mockery\MockInterface&\Cart)
     */
    protected $cartMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->cartServiceBuilderMock = \Mockery::mock(CartServiceBuilder::class)->makePartial();
        $this->cartRepositoryMock = \Mockery::mock(CartRepository::class)->makePartial();
        $this->moduleFactoryMock = \Mockery::mock(ModuleFactory::class)->makePartial();
        $this->toolsFactoryMock = \Mockery::mock(ToolsFactory::class)->makePartial();
        $this->cartFactoryMock = \Mockery::mock(CartFactory::class)->makePartial();
        $this->cartService = $this->cartServiceBuilderMock->getInstance();
        $this->cartMock = \Mockery::mock(\Cart::class)->makePartial();
    }

    /**
     * @return void
     */
    protected function tearDown()
    {
        $this->cartServiceBuilderMock = null;
        $this->cartRepositoryMock = null;
        $this->moduleFactoryMock = null;
        $this->toolsFactoryMock = null;
        $this->cartFactoryMock = null;
        $this->cartService = null;
        $this->cartMock = null;
    }

    /**
     * @return void
     */
    public function testGetCartSavedFromOpartSaveCart()
    {
        $this->moduleFactoryMock->shouldReceive('isInstalled')->with('opartsavecart')->andReturn(true);
        $this->cartServiceBuilderMock->shouldReceive('getModuleFactory')->andReturn($this->moduleFactoryMock);

        $this->toolsFactoryMock->shouldReceive('getValue')->with('token')->andReturn('12345');
        $this->cartServiceBuilderMock->shouldReceive('getToolsFactory')->andReturn($this->toolsFactoryMock);

        $this->cartRepositoryMock->shouldReceive('getIdCartByToken')->andReturn(1);
        $this->cartServiceBuilderMock->shouldReceive('getOpartSaveCartRepository')->andReturn($this->cartRepositoryMock);

        $this->cartMock->id = 1;
        $this->cartFactoryMock->shouldReceive('create')->with(1)->andReturn($this->cartMock);
        $this->cartServiceBuilderMock->shouldReceive('getCartFactory')->andReturn($this->cartFactoryMock);

        $this->cartService = $this->cartServiceBuilderMock->getInstance();

        $result = $this->cartService->getCartSaved();
        $this->assertInstanceOf(\Cart::class, $result);
        $this->assertEquals(1, $result->id);
    }

    /**
     * @return void
     */
    public function testGetCartSavedNoModuleInstalled()
    {
        $this->moduleFactoryMock->shouldReceive('isInstalled')->with('opartsavecart')->andReturn(false);
        $this->cartServiceBuilderMock->shouldReceive('getModuleFactory')->andReturn($this->moduleFactoryMock);

        $this->cartService = $this->cartServiceBuilderMock->getInstance();

        $this->assertNull($this->cartService->getCartSaved());
    }

    /**
     * @return void
     */
    public function testGetCartSavedNoCart()
    {
        $this->moduleFactoryMock->shouldReceive('isInstalled')->with('opartsavecart')->andReturn(true);
        $this->cartServiceBuilderMock->shouldReceive('getModuleFactory')->andReturn($this->moduleFactoryMock);

        $this->toolsFactoryMock->shouldReceive('getValue')->with('token')->andReturn('12345');
        $this->cartServiceBuilderMock->shouldReceive('getToolsFactory')->andReturn($this->toolsFactoryMock);

        $this->cartRepositoryMock->shouldReceive('getIdCartByToken')->andReturn(false);
        $this->cartServiceBuilderMock->shouldReceive('getOpartSaveCartRepository')->andReturn($this->cartRepositoryMock);

        $this->cartService = $this->cartServiceBuilderMock->getInstance();

        $this->assertNull($this->cartService->getCartSaved());
    }

    /**
     * @return void
     */
    public function testGetCartSavedNoCartId()
    {
        $this->moduleFactoryMock->shouldReceive('isInstalled')->with('opartsavecart')->andReturn(true);
        $this->cartServiceBuilderMock->shouldReceive('getModuleFactory')->andReturn($this->moduleFactoryMock);

        $this->toolsFactoryMock->shouldReceive('getValue')->with('token')->andReturn('12345');
        $this->cartServiceBuilderMock->shouldReceive('getToolsFactory')->andReturn($this->toolsFactoryMock);

        $this->cartRepositoryMock->shouldReceive('getIdCartByToken')->andReturn(false);
        $this->cartServiceBuilderMock->shouldReceive('getOpartSaveCartRepository')->andReturn($this->cartRepositoryMock);

        $this->cartMock->id = null;
        $this->cartFactoryMock->shouldReceive('create')->with(1)->andReturn($this->cartMock);
        $this->cartServiceBuilderMock->shouldReceive('getCartFactory')->andReturn($this->cartFactoryMock);

        $this->cartService = $this->cartServiceBuilderMock->getInstance();

        $this->assertNull($this->cartService->getCartSaved());
    }
}
