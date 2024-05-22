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

namespace Alma\PrestaShop\Tests\Unit\Modules\OpartSaveCart;

use Alma\PrestaShop\Modules\OpartSaveCart\CartRepository;
use Alma\PrestaShop\Modules\OpartSaveCart\CartService;
use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManager;

class CartServiceTest extends TestCase
{
    protected $cartSaveService;
    protected $moduleManagerBuilder;

    protected function setUp()
    {
        $this->cartSaveRepository = $this->createMock(CartRepository::class);
        $this->moduleManagerBuilder = $this->createMock(ModuleManager::class);
        $this->cartSaveService = new CartService(
            $this->moduleManagerBuilder,
            $this->cartSaveRepository
        );
    }

    /**
     * @return void
     */
    public function testGetCartSavedFromOpartSaveCart()
    {
        $expected = [
            'id_cart' => 1,
            'id_customer' => 1,
            'token' => 'token',
            'name' => '',
            'reminded' => 0,
            'date_add' => '2019-01-01 00:00:00',
        ];
        $this->cartSaveRepository->method('getCurrentCartForOpartSaveCart')
            ->with('token')
            ->willReturn($expected);
        $this->moduleManagerBuilder->expects($this->once())
            ->method('isInstalled')
            ->with('opartsavecart')
            ->willReturn(true);
        $this->assertEquals($expected, $this->cartSaveService->getCartSaved('token'));
    }

    /**
     * @return void
     */
    public function testGetCartSavedNoModuleInstalled()
    {
        $this->moduleManagerBuilder->expects($this->once())
            ->method('isInstalled')
            ->with('opartsavecart')
            ->willReturn(false);
        $this->assertFalse($this->cartSaveService->getCartSaved('token'));
    }
}
