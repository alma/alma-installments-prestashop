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

namespace Alma\PrestaShop\Tests\Unit\Helper;

use Alma\PrestaShop\Builders\Helpers\CustomerHelperBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Helpers\OrderHelper;
use Alma\PrestaShop\Helpers\ValidateHelper;
use PHPUnit\Framework\TestCase;

class CustomerHelperTest extends TestCase
{
    public function testGetCustomer()
    {
        // Test with an id in the context cart customer
        $contextFactory = \Mockery::mock(ContextFactory::class);
        $contextFactory->shouldReceive('getContextCartCustomerId')->andReturn(1);

        $customerHelperBuilder = \Mockery::mock(CustomerHelperBuilder::class)->makePartial();
        $customerHelperBuilder->shouldReceive('getContextFactory')->andReturn($contextFactory);
        $customerHelper = $customerHelperBuilder->getInstance();

        $this->assertInstanceOf(\Customer::class, $customerHelper->getCustomer());

        // Test with the customer object get from the context
        $customer = new \Customer();
        $customer->firstname = 'test';
        $customer->lastname = 'test';
        $customer->email = 'test@test.fr';
        $customer->passwd = '12345';
        $customer->save();

        $contextFactory = \Mockery::mock(ContextFactory::class);
        $contextFactory->shouldReceive('getContextCartCustomerId')->andReturn(null);
        $contextFactory->shouldReceive('getContextCustomer')->andReturn($customer);
        $contextFactory->shouldReceive('getContextCartCustomerId')->andReturn(1);
        $contextFactory->shouldReceive('getContextCartId')->andReturn(1);

        $customerHelperBuilder = \Mockery::mock(CustomerHelperBuilder::class)->makePartial();
        $customerHelperBuilder->shouldReceive('getContextFactory')->andReturn($contextFactory);
        $customerHelper = $customerHelperBuilder->getInstance();

        $this->assertInstanceOf(\Customer::class, $customerHelper->getCustomer());

        // Test with no customer at all
        $contextFactory = \Mockery::mock(ContextFactory::class);
        $contextFactory->shouldReceive('getContextCartCustomerId')->andReturn(null);
        $contextFactory->shouldReceive('getContextCustomer')->andReturn(null);
        $contextFactory->shouldReceive('getContextCartCustomerId')->andReturn(1);
        $contextFactory->shouldReceive('getContextCartId')->andReturn(1);

        $customerHelperBuilder = \Mockery::mock(CustomerHelperBuilder::class)->makePartial();
        $customerHelperBuilder->shouldReceive('getContextFactory')->andReturn($contextFactory);
        $customerHelper = $customerHelperBuilder->getInstance();

        $this->assertNull($customerHelper->getCustomer());
    }

    public function testValidateCustomer()
    {
        $validateHelper = \Mockery::mock(ValidateHelper::class);
        $validateHelper->shouldReceive('isLoadedObject', [new \stdClass()])->andReturn(false);

        $contextFactory = \Mockery::mock(ContextFactory::class);
        $contextFactory->shouldReceive('getContextCartCustomerId')->andReturn(1);
        $contextFactory->shouldReceive('getContextCartId')->andReturn(1);

        $customerHelperBuilder = \Mockery::mock(CustomerHelperBuilder::class)->makePartial();
        $customerHelperBuilder->shouldReceive('getValidateHelper')->andReturn($validateHelper);
        $customerHelperBuilder->shouldReceive('getContextFactory')->andReturn($contextFactory);
        $customerHelper = $customerHelperBuilder->getInstance();

        $this->assertNull($customerHelper->validateCustomer(new \stdClass()));

        $customer = new \Customer();
        $customer->firstname = 'test';
        $customer->lastname = 'test';
        $customer->email = 'test@test.fr';
        $customer->passwd = '12345';
        $customer->save();

        $customerHelperBuilder = new CustomerHelperBuilder();
        $customerHelper = $customerHelperBuilder->getInstance();

        $this->assertInstanceOf(\Customer::class, $customerHelper->validateCustomer($customer));
    }

    public function testIsNewCustomer()
    {
        $orderHelper = \Mockery::mock(OrderHelper::class)->makePartial();
        $orderHelper->shouldReceive('getCustomerNbOrders', [1])->andReturn(0);

        $customerHelperBuilder = \Mockery::mock(CustomerHelperBuilder::class)->makePartial();
        $customerHelperBuilder->shouldReceive('getOrderHelper')->andReturn($orderHelper);
        $customerHelper = $customerHelperBuilder->getInstance();

        $this->assertTrue($customerHelper->isNewCustomer(1));

        $orderHelper = \Mockery::mock(OrderHelper::class)->makePartial();
        $orderHelper->shouldReceive('getCustomerNbOrders', [1])->andReturn(10);

        $customerHelperBuilder = \Mockery::mock(CustomerHelperBuilder::class)->makePartial();
        $customerHelperBuilder->shouldReceive('getOrderHelper')->andReturn($orderHelper);
        $customerHelper = $customerHelperBuilder->getInstance();

        $this->assertFalse($customerHelper->isNewCustomer(1));
    }
}
