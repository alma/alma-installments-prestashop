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

namespace Alma\PrestaShop\Tests\Unit\Builders\Services;

use Alma\PrestaShop\Builders\Services\InsuranceProductServiceBuilder;
use Alma\PrestaShop\Services\AttributeGroupProductService;
use Alma\PrestaShop\Services\AttributeProductService;
use Alma\PrestaShop\Services\CartService;
use Alma\PrestaShop\Services\CombinationProductAttributeService;
use Alma\PrestaShop\Services\InsuranceApiService;
use PHPUnit\Framework\TestCase;

class InsuranceProductServiceBuilderTest extends TestCase
{
    protected $insuranceProductServiceBuilder;

    public function setUp()
    {
        $this->insuranceProductServiceBuilder = new InsuranceProductServiceBuilder();
    }

    /**
     * @return void
     */
    public function testGetAttributeGroupProductService()
    {
        $this->assertInstanceOf(AttributeGroupProductService::class, $this->insuranceProductServiceBuilder->getAttributeGroupProductService());
        $this->assertInstanceOf(AttributeGroupProductService::class, $this->insuranceProductServiceBuilder->getAttributeGroupProductService(
            new AttributeGroupProductService()
        ));
    }

    /**
     * @return void
     */
    public function testGetAttributeProductService()
    {
        $this->assertInstanceOf(AttributeProductService::class, $this->insuranceProductServiceBuilder->getAttributeProductService());
        $this->assertInstanceOf(AttributeProductService::class, $this->insuranceProductServiceBuilder->getAttributeProductService(
            new AttributeProductService()
        ));
    }

    /**
     * @return void
     */
    public function testGetCombinationProductAttributeService()
    {
        $this->assertInstanceOf(CombinationProductAttributeService::class, $this->insuranceProductServiceBuilder->getCombinationProductAttributeService());
        $this->assertInstanceOf(CombinationProductAttributeService::class, $this->insuranceProductServiceBuilder->getCombinationProductAttributeService(
            new CombinationProductAttributeService()
        ));
    }

    /**
     * @return void
     */
    public function testGetCartService()
    {
        $this->assertInstanceOf(CartService::class, $this->insuranceProductServiceBuilder->getCartService());
        $this->assertInstanceOf(CartService::class, $this->insuranceProductServiceBuilder->getCartService(
            \Mockery::mock(CartService::class)
        ));
    }

    /**
     * @return void
     */
    public function testGetInsuranceApiService()
    {
        $this->assertInstanceOf(InsuranceApiService::class, $this->insuranceProductServiceBuilder->getInsuranceApiService());
        $this->assertInstanceOf(InsuranceApiService::class, $this->insuranceProductServiceBuilder->getInsuranceApiService(
            new InsuranceApiService()
        ));
    }
}
