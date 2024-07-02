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

use Alma\PrestaShop\Builders\Models\OrderStateHelperBuilder;
use Alma\PrestaShop\Exceptions\AlmaException;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\OrderStateFactory;
use Alma\PrestaShop\Helpers\OrderStateHelper;
use PHPUnit\Framework\TestCase;

class OrderStateHelperTest extends TestCase
{
    /**
     * @var \Alma\PrestaShop\Helpers\OrderStateHelper
     */
    protected $orderStateHelper;

    public function setUp()
    {
        $orderStateHelperBuilder = new OrderStateHelperBuilder();
        $this->orderStateHelper = $orderStateHelperBuilder->getOrderStateHelper();
    }

    public function testGetNameById()
    {
        $this->assertEquals('Awaiting check payment', $this->orderStateHelper->getNameById(1));

        $contextFactory = \Mockery::mock(ContextFactory::class)->makePartial();
        $contextFactory->shouldReceive('getContext')->andReturn(null);

        $orderStateFactory = \Mockery::mock(OrderStateFactory::class);

        $orderStateHelper = \Mockery::mock(OrderStateHelper::class, [$contextFactory, $orderStateFactory])->makePartial();

        $this->expectException(AlmaException::class);
        $orderStateHelper->getNameById(1);
    }
}
