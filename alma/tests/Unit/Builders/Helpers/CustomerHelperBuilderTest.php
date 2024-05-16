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

namespace Alma\PrestaShop\Tests\Unit\Builders\Helpers;

use Alma\PrestaShop\Builders\Helpers\CustomerHelperBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\CustomerFactory;
use Alma\PrestaShop\Helpers\CustomerHelper;
use Alma\PrestaShop\Helpers\OrderHelper;
use Alma\PrestaShop\Helpers\ValidateHelper;
use PHPUnit\Framework\TestCase;

class CustomerHelperBuilderTest extends TestCase
{
    /**
     * @var CustomerHelperBuilder
     */
    protected $customerHelperBuilder
    ;

    public function setUp()
    {
        $this->customerHelperBuilder = new CustomerHelperBuilder();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(CustomerHelper::class, $this->customerHelperBuilder->getInstance());
    }

    public function testGetContextFactory()
    {
        $this->assertInstanceOf(ContextFactory::class, $this->customerHelperBuilder->getContextFactory());
        $this->assertInstanceOf(ContextFactory::class, $this->customerHelperBuilder->getContextFactory(
            new ContextFactory()
        ));
    }

    public function testGetOrderHelper()
    {
        $this->assertInstanceOf(OrderHelper::class, $this->customerHelperBuilder->getOrderHelper());
        $this->assertInstanceOf(OrderHelper::class, $this->customerHelperBuilder->getOrderHelper(
            new OrderHelper()
        ));
    }

    public function testGetValidateHelper()
    {
        $this->assertInstanceOf(ValidateHelper::class, $this->customerHelperBuilder->getValidateHelper());
        $this->assertInstanceOf(ValidateHelper::class, $this->customerHelperBuilder->getValidateHelper(
            new ValidateHelper()
        ));
    }

    public function testGetCustomerFactory()
    {
        $this->assertInstanceOf(CustomerFactory::class, $this->customerHelperBuilder->getCustomerFactory());
        $this->assertInstanceOf(CustomerFactory::class, $this->customerHelperBuilder->getCustomerFactory(
            new CustomerFactory()
        ));
    }
}
