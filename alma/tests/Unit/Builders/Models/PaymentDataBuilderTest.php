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

namespace Alma\PrestaShop\Tests\Unit\Builders\Models;

use Alma\PrestaShop\Builders\Models\PaymentDataBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Helpers\AddressHelper;
use Alma\PrestaShop\Helpers\CarrierHelper;
use Alma\PrestaShop\Helpers\CartHelper;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\CountryHelper;
use Alma\PrestaShop\Helpers\CustomerHelper;
use Alma\PrestaShop\Helpers\CustomFieldsHelper;
use Alma\PrestaShop\Helpers\LanguageHelper;
use Alma\PrestaShop\Helpers\LocaleHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ShopHelper;
use Alma\PrestaShop\Helpers\StateHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Model\CarrierData;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Model\PaymentData;
use Alma\PrestaShop\Model\ShippingData;
use PHPUnit\Framework\TestCase;

class PaymentDataBuilderTest extends TestCase
{
    /**
     * @var PaymentDataBuilder
     */
    protected $paymentDataBuilder;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var CarrierHelper
     */
    protected $carrierHelper;

    /**
     * @var ContextFactory
     */
    protected $contextFactory;

    /**
     * @var ToolsHelper
     */
    protected $toolsHelper;

    /**
     * @var LanguageHelper
     */
    protected $languageHelper;

    public function setUp()
    {
        $this->paymentDataBuilder = new PaymentDataBuilder();
        $this->contextFactory = new ContextFactory();
        $this->toolsHelper = new ToolsHelper();
        $this->languageHelper = new LanguageHelper();

        $this->settingsHelper = new SettingsHelper(
            new ShopHelper(),
            new ConfigurationHelper()
        );

        $this->priceHelper = \Mockery::mock(PriceHelper::class);

        $this->carrierHelper = new CarrierHelper(
            $this->contextFactory,
            new CarrierData()
        );
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(PaymentData::class, $this->paymentDataBuilder->getInstance());
    }

    public function testGetToolsHelper()
    {
        $this->assertInstanceOf(ToolsHelper::class, $this->paymentDataBuilder->getToolsHelper());
        $this->assertInstanceOf(ToolsHelper::class, $this->paymentDataBuilder->getToolsHelper(
            $this->toolsHelper
        ));
    }

    public function testGetSettingsHelper()
    {
        $this->assertInstanceOf(SettingsHelper::class, $this->paymentDataBuilder->getSettingsHelper());
        $this->assertInstanceOf(SettingsHelper::class, $this->paymentDataBuilder->getSettingsHelper(
            $this->settingsHelper
        ));
    }

    public function testPriceHelper()
    {
        $this->assertInstanceOf(PriceHelper::class, $this->paymentDataBuilder->getPriceHelper());
        $this->assertInstanceOf(PriceHelper::class, $this->paymentDataBuilder->getPriceHelper(
            $this->priceHelper
        ));
    }

    public function testGetCustomFieldsHelper()
    {
        $this->assertInstanceOf(CustomFieldsHelper::class, $this->paymentDataBuilder->getCustomFieldsHelper());
        $this->assertInstanceOf(CustomFieldsHelper::class, $this->paymentDataBuilder->getCustomFieldsHelper(
            $this->createMock(CustomFieldsHelper::class)
        ));
    }

    public function testGetCartData()
    {
        $this->assertInstanceOf(CartData::class, $this->paymentDataBuilder->getCartData());
        $this->assertInstanceOf(CartData::class, $this->paymentDataBuilder->getCartData(
            $this->createMock(CartData::class)
        ));
    }

    public function testGetShippingData()
    {
        $this->assertInstanceOf(ShippingData::class, $this->paymentDataBuilder->getShippingData());
        $this->assertInstanceOf(ShippingData::class, $this->paymentDataBuilder->getShippingData(
            $this->createMock(ShippingData::class)
        ));
    }

    public function testGetContextFactory()
    {
        $this->assertInstanceOf(ContextFactory::class, $this->paymentDataBuilder->getContextFactory());
        $this->assertInstanceOf(ContextFactory::class, $this->paymentDataBuilder->getContextFactory(
            $this->contextFactory
        ));
    }

    public function testGetAddressHelper()
    {
        $this->assertInstanceOf(AddressHelper::class, $this->paymentDataBuilder->getAddressHelper());
        $this->assertInstanceOf(AddressHelper::class, $this->paymentDataBuilder->getAddressHelper(
            new AddressHelper($this->toolsHelper, $this->contextFactory)
        ));
    }

    public function testGetCountryHelper()
    {
        $this->assertInstanceOf(CountryHelper::class, $this->paymentDataBuilder->getCountryHelper());
        $this->assertInstanceOf(CountryHelper::class, $this->paymentDataBuilder->getCountryHelper(
            new CountryHelper()
        ));
    }

    public function testGetLocaleHelper()
    {
        $this->assertInstanceOf(LocaleHelper::class, $this->paymentDataBuilder->getLocaleHelper());
        $this->assertInstanceOf(LocaleHelper::class, $this->paymentDataBuilder->getLocaleHelper(
            new LocaleHelper($this->languageHelper)
        ));
    }

    public function testGetStatesHelper()
    {
        $this->assertInstanceOf(StateHelper::class, $this->paymentDataBuilder->getStateHelper());
        $this->assertInstanceOf(StateHelper::class, $this->paymentDataBuilder->getStateHelper(
            new StateHelper()
        ));
    }

    public function testGetCustomerHelper()
    {
        $this->assertInstanceOf(CustomerHelper::class, $this->paymentDataBuilder->getCustomerHelper());
        $this->assertInstanceOf(CustomerHelper::class, $this->paymentDataBuilder->getCustomerHelper(
            $this->createMock(CustomerHelper::class)
        ));
    }

    public function testGetCartHelper()
    {
        $this->assertInstanceOf(CartHelper::class, $this->paymentDataBuilder->getCartHelper());
        $this->assertInstanceOf(CartHelper::class, $this->paymentDataBuilder->getCartHelper(
            $this->createMock(CartHelper::class)
        ));
    }

    public function testGetCarrierHelper()
    {
        $this->assertInstanceOf(CarrierHelper::class, $this->paymentDataBuilder->getCarrierHelper());
        $this->assertInstanceOf(CarrierHelper::class, $this->paymentDataBuilder->getCarrierHelper(
            $this->carrierHelper
        ));
    }
}
