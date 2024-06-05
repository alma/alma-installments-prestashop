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

use Alma\PrestaShop\Builders\Helpers\PaymentOptionTemplateHelperBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\DateHelper;
use Alma\PrestaShop\Helpers\PaymentOptionTemplateHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\TranslationHelper;
use PHPUnit\Framework\TestCase;

class PaymentOptionTemplateHelperBuilderTest extends TestCase
{
    /**
     * @var PaymentOptionTemplateHelperBuilder
     */
    protected $paymentOptionTemplateHelperBuilder
    ;

    public function setUp()
    {
        $this->paymentOptionTemplateHelperBuilder = new PaymentOptionTemplateHelperBuilder();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(PaymentOptionTemplateHelper::class, $this->paymentOptionTemplateHelperBuilder->getInstance());
    }

    public function testGetContextFactory()
    {
        $this->assertInstanceOf(ContextFactory::class, $this->paymentOptionTemplateHelperBuilder->getContextFactory());
        $this->assertInstanceOf(ContextFactory::class, $this->paymentOptionTemplateHelperBuilder->getContextFactory(
            new ContextFactory()
        ));
    }

    public function testGetSettingsHelper()
    {
        $this->assertInstanceOf(SettingsHelper::class, $this->paymentOptionTemplateHelperBuilder->getSettingsHelper());
        $this->assertInstanceOf(SettingsHelper::class, $this->paymentOptionTemplateHelperBuilder->getSettingsHelper(
            $this->createMock(SettingsHelper::class)
        ));
    }

    public function testGetConfigurationHelper()
    {
        $this->assertInstanceOf(ConfigurationHelper::class, $this->paymentOptionTemplateHelperBuilder->getConfigurationHelper());
        $this->assertInstanceOf(ConfigurationHelper::class, $this->paymentOptionTemplateHelperBuilder->getConfigurationHelper(
            new ConfigurationHelper()
        ));
    }

    public function testGetTranslationHelper()
    {
        $this->assertInstanceOf(TranslationHelper::class, $this->paymentOptionTemplateHelperBuilder->getTranslationHelper());
        $this->assertInstanceOf(TranslationHelper::class, $this->paymentOptionTemplateHelperBuilder->getTranslationHelper(
            $this->createMock(TranslationHelper::class)
        ));
    }

    public function testGetPriceHelper()
    {
        $this->assertInstanceOf(PriceHelper::class, $this->paymentOptionTemplateHelperBuilder->getPriceHelper());
        $this->assertInstanceOf(PriceHelper::class, $this->paymentOptionTemplateHelperBuilder->getPriceHelper(
            $this->createMock(PriceHelper::class)
        ));
    }

    public function testGetDateHelper()
    {
        $this->assertInstanceOf(DateHelper::class, $this->paymentOptionTemplateHelperBuilder->getDateHelper());
        $this->assertInstanceOf(DateHelper::class, $this->paymentOptionTemplateHelperBuilder->getDateHelper(
            new DateHelper()
        ));
    }
}
