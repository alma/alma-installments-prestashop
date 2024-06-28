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

namespace Alma\PrestaShop\Tests\Unit\Builders\Services;

use Alma\PrestaShop\Builders\Services\CartServiceBuilder;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\InsuranceProductHelper;
use Alma\PrestaShop\Modules\OpartSaveCart\OpartSaveCartCartService as OpartSaveCartCartService;
use Alma\PrestaShop\Repositories\CartProductRepository;
use Alma\PrestaShop\Services\CartService;
use PHPUnit\Framework\TestCase;

class CartServiceBuilderTest extends TestCase
{
    public function setUp()
    {
        $this->cartServiceBuilder = new CartServiceBuilder();
    }

    /**
     * @return void
     */
    public function testGetInstance()
    {
        $this->assertInstanceOf(CartService::class, $this->cartServiceBuilder->getInstance());
    }

    /**
     * @return void
     */
    public function testGetOpartSaveCartCartService()
    {
        $this->assertInstanceOf(OpartSaveCartCartService::class, $this->cartServiceBuilder->getOpartSaveCartCartService());
        $this->assertInstanceOf(OpartSaveCartCartService::class, $this->cartServiceBuilder->getOpartSaveCartCartService(
            \Mockery::mock(OpartSaveCartCartService::class)
        ));
    }

    /**
     * @return void
     */
    public function testGetInsuranceHelper()
    {
        $this->assertInstanceOf(InsuranceHelper::class, $this->cartServiceBuilder->getInsuranceHelper());
        $this->assertInstanceOf(InsuranceHelper::class, $this->cartServiceBuilder->getInsuranceHelper(
            new InsuranceHelper()
        ));
    }

    /**
     * @return void
     */
    public function testGetCartProductRepository()
    {
        $this->assertInstanceOf(CartProductRepository::class, $this->cartServiceBuilder->getCartProductRepository());
        $this->assertInstanceOf(CartProductRepository::class, $this->cartServiceBuilder->getCartProductRepository(
            \Mockery::mock(CartProductRepository::class)
        ));
    }

    /**
     * @return void
     */
    public function testGetInsuranceProductHelper()
    {
        $this->assertInstanceOf(InsuranceProductHelper::class, $this->cartServiceBuilder->getInsuranceProductHelper());
        $this->assertInstanceOf(InsuranceProductHelper::class, $this->cartServiceBuilder->getInsuranceProductHelper(
            \Mockery::mock(InsuranceProductHelper::class)
        ));
    }
}
