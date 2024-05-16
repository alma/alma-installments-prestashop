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

use Alma\PrestaShop\Builders\Helpers\PaymentOptionHelperBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\MediaFactory;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\CustomFieldsHelper;
use Alma\PrestaShop\Helpers\MediaHelper;
use Alma\PrestaShop\Helpers\PaymentOptionHelper;
use Alma\PrestaShop\Helpers\PaymentOptionTemplateHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ShopHelper;
use PHPUnit\Framework\TestCase;

class PaymentOptionHelperBuilderTest extends TestCase
{
    /**
     * @var PaymentOptionHelperBuilder
     */
    protected $paymentOptionHelperBuilder;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    public function setUp()
    {
        $this->paymentOptionHelperBuilder = new PaymentOptionHelperBuilder();
        $this->settingsHelper = new SettingsHelper(
            new ShopHelper(),
            new ConfigurationHelper()
        );
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(PaymentOptionHelper::class, $this->paymentOptionHelperBuilder->getInstance());
    }

    public function testGetContextFactory()
    {
        $this->assertInstanceOf(ContextFactory::class, $this->paymentOptionHelperBuilder->getContextFactory());
        $this->assertInstanceOf(ContextFactory::class, $this->paymentOptionHelperBuilder->getContextFactory(
            new ContextFactory()
        ));
    }

    public function testGetModuleFactory()
    {
        $this->assertInstanceOf(ModuleFactory::class, $this->paymentOptionHelperBuilder->getModuleFactory());
        $this->assertInstanceOf(ModuleFactory::class, $this->paymentOptionHelperBuilder->getModuleFactory(
            new ModuleFactory()
        ));
    }

    public function testGetSettingsHelper()
    {
        $this->assertInstanceOf(SettingsHelper::class, $this->paymentOptionHelperBuilder->getSettingsHelper());
        $this->assertInstanceOf(SettingsHelper::class, $this->paymentOptionHelperBuilder->getSettingsHelper(
            $this->settingsHelper
        ));
    }

    public function testGetCustomFieldsHelper()
    {
        $this->assertInstanceOf(CustomFieldsHelper::class, $this->paymentOptionHelperBuilder->getCustomFieldsHelper());
        $this->assertInstanceOf(CustomFieldsHelper::class, $this->paymentOptionHelperBuilder->getCustomFieldsHelper(
            $this->createMock(CustomFieldsHelper::class)
        ));
    }

    public function testGetMediaHelper()
    {
        $this->assertInstanceOf(MediaHelper::class, $this->paymentOptionHelperBuilder->getMediaHelper());
        $this->assertInstanceOf(MediaHelper::class, $this->paymentOptionHelperBuilder->getMediaHelper(
            $this->createMock(MediaHelper::class)
        ));
    }

    public function testGetConfigurationHelper()
    {
        $this->assertInstanceOf(ConfigurationHelper::class, $this->paymentOptionHelperBuilder->getConfigurationHelper());
        $this->assertInstanceOf(ConfigurationHelper::class, $this->paymentOptionHelperBuilder->getConfigurationHelper(
            new ConfigurationHelper()
        ));
    }

    public function testGetPaymentOptionTemplateHelper()
    {
        $this->assertInstanceOf(PaymentOptionTemplateHelper::class, $this->paymentOptionHelperBuilder->getPaymentOptionTemplateHelper());
        $this->assertInstanceOf(PaymentOptionTemplateHelper::class, $this->paymentOptionHelperBuilder->getPaymentOptionTemplateHelper(
            $this->createMock(PaymentOptionTemplateHelper::class)
        ));
    }

    public function testGetMediaFactory()
    {
        $this->assertInstanceOf(MediaFactory::class, $this->paymentOptionHelperBuilder->getMediaFactory());
        $this->assertInstanceOf(MediaFactory::class, $this->paymentOptionHelperBuilder->getMediaFactory(
            $this->createMock(MediaFactory::class)
        ));
    }
}
