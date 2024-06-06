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

namespace Alma\PrestaShop\Tests\Unit\Services;

use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\InsuranceProductHelper;
use Alma\PrestaShop\Modules\OpartSaveCart\CartService as CartServiceAlias;
use Alma\PrestaShop\Repositories\CartProductRepository;
use Alma\PrestaShop\Services\CartService;
use PHPUnit\Framework\TestCase;

class CartServiceTest extends TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testDuplicateCartWithoutCurrentCart()
    {
        $cartMock = \Mockery::mock(\Cart::class);
        $contextFactoryMock = \Mockery::mock(ContextFactory::class)->makePartial();
        $contextFactoryMock->shouldReceive('getContextCart')->andReturn(null);

        $opartCartSaveServiceSpy = \Mockery::spy(CartServiceAlias::class)->makePartial();
        $opartCartSaveServiceSpy->shouldReceive('getCartSaved');

        $cartServiceSpy = \Mockery::spy(
            CartService::class,
            [
                \Mockery::mock(CartProductRepository::class),
                $contextFactoryMock,
                $opartCartSaveServiceSpy,
                \Mockery::mock(InsuranceHelper::class),
                \Mockery::mock(InsuranceProductHelper::class),
            ]
        )->makePartial();
        $cartServiceSpy->shouldReceive('duplicateInsuranceProductsInDB');

        $cartServiceSpy->duplicateCart($cartMock);

        $opartCartSaveServiceSpy->shouldNotHaveReceived('getCartSaved');
        $cartServiceSpy->shouldNotHaveReceived('duplicateInsuranceProductsInDB');
    }

    public function testDuplicateCartWithCurrentCartAndCartIdNull()
    {
        $cartMock = \Mockery::mock(\Cart::class);
        $cartMock->id = null;

        $newCartMock = \Mockery::mock(\Cart::class);
        $newCartMock->id = 2;

        $contextFactoryMock = \Mockery::mock(ContextFactory::class)->makePartial();
        $contextFactoryMock->shouldReceive('getContextCart')->andReturn($cartMock);

        $opartCartSaveServiceSpy = \Mockery::spy(CartServiceAlias::class)->makePartial();
        $opartCartSaveServiceSpy->shouldReceive('getCartSaved')->andReturn($newCartMock);

        $cartServiceSpy = \Mockery::spy(
            CartService::class,
            [
                \Mockery::mock(CartProductRepository::class),
                $contextFactoryMock,
                $opartCartSaveServiceSpy,
                \Mockery::mock(InsuranceHelper::class),
                \Mockery::mock(InsuranceProductHelper::class),
            ]
        )->makePartial();
        $cartServiceSpy->shouldReceive('duplicateInsuranceProductsInDB');

        $cartServiceSpy->duplicateCart($cartMock);

        $opartCartSaveServiceSpy->shouldHaveReceived('getCartSaved');
        $cartServiceSpy->shouldHaveReceived('duplicateInsuranceProductsInDB');
    }

    public function testDuplicateCartWithCurrentCartAndCartIdDifferentNewCart()
    {
        $cartMock = \Mockery::mock(\Cart::class);
        $cartMock->id = 1;

        $newCartMock = \Mockery::mock(\Cart::class);
        $newCartMock->id = 2;

        $contextFactoryMock = \Mockery::mock(ContextFactory::class)->makePartial();
        $contextFactoryMock->shouldReceive('getContextCart')->andReturn($cartMock);

        $opartCartSaveServiceSpy = \Mockery::spy(CartServiceAlias::class)->makePartial();
        $opartCartSaveServiceSpy->shouldReceive('getCartSaved');

        $cartServiceSpy = \Mockery::spy(
            CartService::class,
            [
                \Mockery::mock(CartProductRepository::class),
                $contextFactoryMock,
                $opartCartSaveServiceSpy,
                \Mockery::mock(InsuranceHelper::class),
                \Mockery::mock(InsuranceProductHelper::class),
            ]
        )->makePartial();
        $cartServiceSpy->shouldReceive('duplicateInsuranceProductsInDB');

        $cartServiceSpy->duplicateCart($newCartMock);

        $opartCartSaveServiceSpy->shouldNotHaveReceived('getCartSaved');
        $cartServiceSpy->shouldHaveReceived('duplicateInsuranceProductsInDB');
    }

    public function testDuplicateCartWithCurrentCartAndCartIdSameNewCart()
    {
        $cartMock = \Mockery::mock(\Cart::class);
        $cartMock->id = 1;

        $newCartMock = \Mockery::mock(\Cart::class);
        $newCartMock->id = 1;

        $contextFactoryMock = \Mockery::mock(ContextFactory::class)->makePartial();
        $contextFactoryMock->shouldReceive('getContextCart')->andReturn($cartMock);

        $opartCartSaveServiceSpy = \Mockery::spy(CartServiceAlias::class)->makePartial();
        $opartCartSaveServiceSpy->shouldReceive('getCartSaved');

        $cartServiceSpy = \Mockery::spy(
            CartService::class,
            [
                \Mockery::mock(CartProductRepository::class),
                $contextFactoryMock,
                $opartCartSaveServiceSpy,
                \Mockery::mock(InsuranceHelper::class),
                \Mockery::mock(InsuranceProductHelper::class),
            ]
        )->makePartial();
        $cartServiceSpy->shouldReceive('duplicateInsuranceProductsInDB');

        $cartServiceSpy->duplicateCart($newCartMock);

        $opartCartSaveServiceSpy->shouldNotHaveReceived('getCartSaved');
        $cartServiceSpy->shouldNotHaveReceived('duplicateInsuranceProductsInDB');
    }
}
