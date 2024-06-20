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

use Alma\PrestaShop\Builders\Helpers\PriceHelperBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Helpers\CurrencyHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use PHPUnit\Framework\TestCase;

class PriceHelperTest extends TestCase
{
    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    public function setUp()
    {
        $priceHelperBuilder = new PriceHelperBuilder();
        $this->priceHelper = $priceHelperBuilder->getInstance();
    }

    public function testConvertPriceToCents()
    {
        $this->assertEquals('10000', $this->priceHelper->convertPriceToCents(100));
        $this->assertEquals('10000', $this->priceHelper->convertPriceToCents(99.9999));
        $this->assertEquals('10000', $this->priceHelper->convertPriceToCents(100.0011));
    }

    public function testConvertPriceFromCents()
    {
        $this->assertEquals('100.0', $this->priceHelper->convertPriceFromCents(10000));
    }

    public function testFormatPriceToCentsByCurrencyId()
    {
        $toolsHelperMock = \Mockery::mock(ToolsHelper::class);
        $toolsHelperMock->shouldReceive('psVersionCompare')->with('1.7.6.0', '<')->andReturn(false);
        $toolsHelperMock->shouldReceive('displayPrice')->with(false, '10000', '1')->andReturn(false);

        $currencyHelperMock = \Mockery::mock(CurrencyHelper::class);
        $currencyHelperMock->shouldReceive('getCurrencyById')->with('1')->andReturn('1');

        $contextFactoryMock = \Mockery::mock(ContextFactory::class);
        $contextFactoryMock->shouldReceive('getCurrencyFromContext')->andReturn('1');

        $priceHelperMock = \Mockery::mock(PriceHelper::class, [$toolsHelperMock, $currencyHelperMock, $contextFactoryMock])->makePartial();
        $result = $priceHelperMock->formatPriceToCentsByCurrencyId('10000', 1);

        $this->assertEquals('100.00€', $result);

        $toolsHelperMock = \Mockery::mock(ToolsHelper::class);
        $toolsHelperMock->shouldReceive('psVersionCompare')->with('1.7.6.0', '<')->andReturn(false);
        $toolsHelperMock->shouldReceive('displayPrice')->with(false, '10000', '1')->andThrow(new \Exception());

        $currencyHelperMock = \Mockery::mock(CurrencyHelper::class);
        $currencyHelperMock->shouldReceive('getCurrencyById')->with('1')->andReturn('1');

        $contextFactoryMock = \Mockery::mock(ContextFactory::class);
        $contextFactoryMock->shouldReceive('getCurrencyFromContext')->andReturn('1');

        $priceHelperMock = \Mockery::mock(PriceHelper::class, [$toolsHelperMock, $currencyHelperMock, $contextFactoryMock])->makePartial();
        $result = $priceHelperMock->formatPriceToCentsByCurrencyId('10000');

        $this->assertEquals('100.00€', $result);
    }
}
