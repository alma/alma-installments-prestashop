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

use Alma\PrestaShop\Builders\Helpers\CurrencyHelperBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\CurrencyFactory;
use PHPUnit\Framework\TestCase;

class CurrencyHelperTest extends TestCase
{
    public function testConvertPriceToCenStr()
    {
        $currencyHelperBuilder = new CurrencyHelperBuilder();
        $currencyHelper = $currencyHelperBuilder->getInstance();

        $this->assertInstanceOf(\Currency::class, $currencyHelper->getCurrencyById(1));

        $currencyFactory = \Mockery::mock(CurrencyFactory::class)->makePartial();
        $currencyFactory->shouldReceive('getCurrencyInstance', [1])->andReturn(null);

        $contextFactory = \Mockery::mock(ContextFactory::class)->makePartial();
        $contextFactory->shouldReceive('getCurrencyFromContext')->andReturn(null);

        $currencyHelperBuilder = \Mockery::mock(CurrencyHelperBuilder::class)->makePartial();
        $currencyHelperBuilder->shouldReceive('getCurrencyFactory')->andReturn($currencyFactory);
        $currencyHelperBuilder->shouldReceive('getContextFactory')->andReturn($contextFactory);

        $currencyHelper = $currencyHelperBuilder->getInstance();

        $this->assertNull($currencyHelper->getCurrencyById(1));
    }
}
