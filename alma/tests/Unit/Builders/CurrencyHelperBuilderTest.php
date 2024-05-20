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

namespace Alma\PrestaShop\Tests\Unit\Builders;

use Alma\PrestaShop\Builders\CurrencyHelperBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\CurrencyFactory;
use Alma\PrestaShop\Helpers\CurrencyHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ValidateHelper;
use PHPUnit\Framework\TestCase;

class CurrencyHelperBuilderTest extends TestCase
{
    /**
     * @var CurrencyHelperBuilder
     */
    protected $currencyHelperBuilder;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var ContextFactory
     */
    protected $contextFactory;

    public function setUp()
    {
        $this->currencyHelperBuilder = new CurrencyHelperBuilder();
        $this->contextFactory = new ContextFactory();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(CurrencyHelper::class, $this->currencyHelperBuilder->getInstance());
    }

    public function testGetContextFactory()
    {
        $this->assertInstanceOf(ContextFactory::class, $this->currencyHelperBuilder->getContextFactory());
        $this->assertInstanceOf(ContextFactory::class, $this->currencyHelperBuilder->getContextFactory(
            $this->contextFactory
        ));
    }

    public function testGetValidateHelper()
    {
        $this->assertInstanceOf(ValidateHelper::class, $this->currencyHelperBuilder->getValidateHelper());
        $this->assertInstanceOf(ValidateHelper::class, $this->currencyHelperBuilder->getValidateHelper(
            new ValidateHelper()
        ));
    }

    public function testGetCurrencyFactory()
    {
        $this->assertInstanceOf(CurrencyFactory::class, $this->currencyHelperBuilder->getCurrencyFactory());
        $this->assertInstanceOf(CurrencyFactory::class, $this->currencyHelperBuilder->getCurrencyFactory(
            new CurrencyFactory()
        ));
    }
}
