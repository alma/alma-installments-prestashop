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

use Alma\PrestaShop\Builders\EligibilityHelperBuilder;
use Alma\PrestaShop\Factories\AddressFactory;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Helpers\AddressHelper;
use Alma\PrestaShop\Helpers\ApiHelper;
use Alma\PrestaShop\Helpers\CarrierHelper;
use Alma\PrestaShop\Helpers\CartHelper;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\CountryHelper;
use Alma\PrestaShop\Helpers\CurrencyHelper;
use Alma\PrestaShop\Helpers\CustomerHelper;
use Alma\PrestaShop\Helpers\CustomFieldsHelper;
use Alma\PrestaShop\Helpers\EligibilityHelper;
use Alma\PrestaShop\Helpers\LanguageHelper;
use Alma\PrestaShop\Helpers\LocaleHelper;
use Alma\PrestaShop\Helpers\OrderHelper;
use Alma\PrestaShop\Helpers\OrderStateHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ShopHelper;
use Alma\PrestaShop\Helpers\StateHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Helpers\ValidateHelper;
use Alma\PrestaShop\Model\CarrierData;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Model\PaymentData;
use Alma\PrestaShop\Model\ShippingData;
use Alma\PrestaShop\Repositories\OrderRepository;
use Alma\PrestaShop\Repositories\ProductRepository;
use PHPUnit\Framework\TestCase;

class EligibilityHelperBuilderTest extends TestCase
{

    /**
     *
     * @var EligibilityHelperBuilder $eligibilityHelperBuilder
     */
    protected $eligibilityHelperBuilder;

        /**
         *
         * @var PriceHelper $priceHelper
         */
    protected $priceHelper;

    /**
     *
     * @var SettingsHelper $settingsHelper
     */
    protected $settingsHelper;

    /**
     *
     * @var CarrierHelper $carrierHelper
     */
    protected $carrierHelper;

    /**
     *
     * @var ContextFactory $contextFactory
     */
    protected $contextFactory;

    /**
     *
     * @var LanguageHelper $languageHelper
     */
    protected $languageHelper;

    /**
     *
     * @var ToolsHelper $toolsHelper
     */
    protected $toolsHelper;



    public function setUp() {
        $this->eligibilityHelperBuilder = new EligibilityHelperBuilder();
        $this->contextFactory = new ContextFactory();
        $this->languageHelper = new LanguageHelper();
        $this->toolsHelper = new ToolsHelper();
        $this->localeHelper = new LocaleHelper($this->languageHelper);

        $this->settingsHelper = new SettingsHelper(
            new ShopHelper(),
            new ConfigurationHelper()
        );
        $this->priceHelper = new PriceHelper(
            $this->toolsHelper,
            new CurrencyHelper()
        );
        $this->carrierHelper = new CarrierHelper(
            $this->contextFactory,
            new CarrierData()
        );
    }


    public function testGetInstance() {
        $this->assertInstanceOf(EligibilityHelper::class, $this->eligibilityHelperBuilder->getInstance());
    }

    public function testGetPaymentData() {
        $this->assertInstanceOf(PaymentData::class, $this->eligibilityHelperBuilder->getPaymentData());
        $this->assertInstanceOf(PaymentData::class, $this->eligibilityHelperBuilder->getPaymentData(
            new PaymentData(
                $this->toolsHelper,
                $this->settingsHelper,
                $this->priceHelper,
                new CustomFieldsHelper(
                    $this->languageHelper,
                    $this->localeHelper,
                    $this->settingsHelper
                ),
                new CartData(
                    new ProductHelper(),
                    $this->settingsHelper,
                    $this->priceHelper,
                    new ProductRepository()
                ),
                new ShippingData(
                    $this->priceHelper,
                    $this->carrierHelper
                ),
                $this->contextFactory ,
                new AddressHelper($this->toolsHelper, $this->contextFactory),
                new CountryHelper(),
                $this->localeHelper,
                new StateHelper(),
                new CustomerHelper(
                    $this->contextFactory ,
                    new OrderHelper(),
                    new ValidateHelper()
                ),
                new CartHelper(
                    $this->contextFactory ,
                    $this->toolsHelper,
                    $this->priceHelper,
                    new CartData(
                        new ProductHelper(),
                        $this->settingsHelper,
                        $this->priceHelper,
                        new ProductRepository()
                    ),
                    new OrderRepository(),
                    new OrderStateHelper($this->contextFactory),
                    $this->carrierHelper
                ),
                $this->carrierHelper,
                new AddressFactory()
            )
        ));
    }

    public function testGetPriceHelper() {
        $this->assertInstanceOf(PriceHelper::class, $this->eligibilityHelperBuilder->getPriceHelper());
        $this->assertInstanceOf(PriceHelper::class, $this->eligibilityHelperBuilder->getPriceHelper(
            $this->priceHelper
        ));
    }

    public function testGetClientHelper() {
        $this->assertInstanceOf(ClientHelper::class, $this->eligibilityHelperBuilder->getClientHelper());
        $this->assertInstanceOf(ClientHelper::class, $this->eligibilityHelperBuilder->getClientHelper(
            new ClientHelper()
        ));
    }

    public function testGetSettingsHelper() {
        $this->assertInstanceOf(SettingsHelper::class, $this->eligibilityHelperBuilder->getSettingsHelper());
        $this->assertInstanceOf(SettingsHelper::class, $this->eligibilityHelperBuilder->getSettingsHelper(
            $this->settingsHelper
        ));
    }

    public function testGetApiHelper() {
        $this->assertInstanceOf(ApiHelper::class, $this->eligibilityHelperBuilder->getApiHelper());
        $this->assertInstanceOf(ApiHelper::class, $this->eligibilityHelperBuilder->getApiHelper(
            new ApiHelper(
                new ModuleFactory(),
                new ClientHelper()
            )
        ));
    }

    public function testGetContextFactory() {
        $this->assertInstanceOf(ContextFactory::class, $this->eligibilityHelperBuilder->getContextFactory());
        $this->assertInstanceOf(ContextFactory::class, $this->eligibilityHelperBuilder->getContextFactory(
            $this->contextFactory
        ));
    }
}
