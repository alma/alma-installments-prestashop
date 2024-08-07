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

namespace Alma\PrestaShop\Tests\Unit\Builders\Helpers;

use Alma\PrestaShop\Builders\Helpers\PriceHelperBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Helpers\CurrencyHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use PHPUnit\Framework\TestCase;

class PriceHelperBuilderTest extends TestCase
{
    /**
     * @var PriceHelperBuilder
     */
    protected $priceHelperBuilder
    ;

    public function setUp()
    {
        $this->priceHelperBuilder = new PriceHelperBuilder();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(PriceHelper::class, $this->priceHelperBuilder->getInstance());
    }

    public function testGetToolsHelper()
    {
        $this->assertInstanceOf(ToolsHelper::class, $this->priceHelperBuilder->getToolsHelper());
        $this->assertInstanceOf(ToolsHelper::class, $this->priceHelperBuilder->getToolsHelper(
            new ToolsHelper()
        ));
    }

    public function testGetCurrencyHelper()
    {
        $this->assertInstanceOf(CurrencyHelper::class, $this->priceHelperBuilder->getCurrencyHelper());
        $this->assertInstanceOf(CurrencyHelper::class, $this->priceHelperBuilder->getCurrencyHelper(
            \Mockery::mock(CurrencyHelper::class)
        ));
    }

    public function testGetContextFactory()
    {
        $this->assertInstanceOf(ContextFactory::class, $this->priceHelperBuilder->getContextFactory());
        $this->assertInstanceOf(ContextFactory::class, $this->priceHelperBuilder->getContextFactory(
            new ContextFactory()
        ));
    }
}
