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

use Alma\PrestaShop\Builders\Helpers\AddressHelperBuilder;
use Alma\PrestaShop\Exceptions\AlmaException;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Helpers\ToolsHelper;
use PHPUnit\Framework\TestCase;

class AddressHelperTest extends TestCase
{
    public function setUp()
    {
        $addressHelperBuilder = new AddressHelperBuilder();
        $this->addressHelper = $addressHelperBuilder->getAddressHelper();
    }

    public function testGetAddressFromCustomer()
    {
        $idCustomer = rand(10000, 20000);
        $customer = new \Customer();
        $customer->id = $idCustomer;
        $customer->id_lang = 1;
        $customer->lastname = 'test';
        $customer->firstname = 'firstname';
        $customer->email = 'firstname@test.fr';
        $customer->passwd = '12345';
        $customer->save();

        $address = new \Address();
        $address->id_country = '8';
        $address->country = 'US';
        $address->state = 'CA';
        $address->city = 'San Francisco';
        $address->address1 = 'Boulevard 110';
        $address->zip = '94102';
        $address->phone = '(555) 555-5555';
        $address->alias = 'home';
        $address->lastname = 'test';
        $address->firstname = 'firstname';
        $address->id_customer = $idCustomer;
        $address->save();

        $address2 = new \Address();
        $address2->country = 'FR';
        $address2->id_country = '2';
        $address2->city = 'Paris';
        $address2->address1 = '12 rue paris';
        $address2->zip = '75000';
        $address2->alias = 'vacation';
        $address2->lastname = 'test';
        $address2->firstname = 'firstname';
        $address2->id_customer = $idCustomer;
        $address2->save();

        $result = $this->addressHelper->getAddressFromCustomer($customer);
        $this->assertEquals(2, count($result));

        $toolsHelper = \Mockery::mock(ToolsHelper::class)->makePartial();
        $toolsHelper->shouldReceive('psVersionCompare', ['1.5.4.0', '<', '1'])->andReturn(true);
        $addressHelperBuilder = \Mockery::mock(AddressHelperBuilder::class)->makePartial();
        $addressHelperBuilder->shouldReceive('getToolsHelper')->andReturn($toolsHelper);

        $addressHelper = $addressHelperBuilder->getAddressHelper();

        $result = $addressHelper->getAddressFromCustomer($customer);

        $this->assertEquals(2, count($result));

        $toolsHelper = \Mockery::mock(ToolsHelper::class)->makePartial();
        $toolsHelper->shouldReceive('psVersionCompare', ['1.5.4.0', '<', '1'])->andReturn(true);

        $contextFactory = \Mockery::mock(ContextFactory::class)->makePartial();
        $contextFactory->shouldReceive('getContext')->andReturn(null);

        $addressHelperBuilder = \Mockery::mock(AddressHelperBuilder::class)->makePartial();
        $addressHelperBuilder->shouldReceive('getToolsHelper')->andReturn($toolsHelper);
        $addressHelperBuilder->shouldReceive('getContextFactory')->andReturn($contextFactory);

        $addressHelper = $addressHelperBuilder->getAddressHelper();

        $this->expectException(AlmaException::class);
        $addressHelper->getAddressFromCustomer($customer);
    }
}
