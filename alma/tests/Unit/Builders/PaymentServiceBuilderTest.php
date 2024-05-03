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

use Alma\PrestaShop\Builders\PaymentServiceBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Helpers\AddressHelper;
use Alma\PrestaShop\Helpers\ApiHelper;
use Alma\PrestaShop\Helpers\CarrierHelper;
use Alma\PrestaShop\Helpers\CartHelper;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ContextHelper;
use Alma\PrestaShop\Helpers\CountryHelper;
use Alma\PrestaShop\Helpers\CurrencyHelper;
use Alma\PrestaShop\Helpers\CustomerHelper;
use Alma\PrestaShop\Helpers\CustomFieldsHelper;
use Alma\PrestaShop\Helpers\DateHelper;
use Alma\PrestaShop\Helpers\EligibilityHelper;
use Alma\PrestaShop\Helpers\LanguageHelper;
use Alma\PrestaShop\Helpers\LocaleHelper;
use Alma\PrestaShop\Helpers\MediaHelper;
use Alma\PrestaShop\Helpers\OrderHelper;
use Alma\PrestaShop\Helpers\OrderStateHelper;
use Alma\PrestaShop\Helpers\PaymentOptionHelper;
use Alma\PrestaShop\Helpers\PaymentOptionTemplateHelper;
use Alma\PrestaShop\Helpers\PlanHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ShopHelper;
use Alma\PrestaShop\Helpers\StateHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Helpers\TranslationHelper;
use Alma\PrestaShop\Helpers\ValidateHelper;
use Alma\PrestaShop\Model\CarrierData;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Model\PaymentData;
use Alma\PrestaShop\Model\ShippingData;
use Alma\PrestaShop\Repositories\OrderRepository;
use Alma\PrestaShop\Repositories\ProductRepository;
use Alma\PrestaShop\Services\PaymentService;
use PHPUnit\Framework\TestCase;

class PaymentServiceBuilderTest extends TestCase
{
    /**
     *
     * @var PaymentServiceBuilder $paymentServiceBuilder
     */
    protected $paymentServiceBuilder;

    /**
     * @var PriceHelper $priceHelper
     */
    protected $priceHelper;

    /**
     * @var SettingsHelper $settingsHelper
     */
    protected $settingsHelper;

    /**
     * @var CartData $cartData
     */
    protected $cartData;

    /**
     * @var ContextFactory $contextFactory
     */
    protected $contextFactory;

    /**
     * @var CarrierHelper $carrierHelper
     */
    protected $carrierHelper;

    /**
     * @var ToolsHelper $toolsHelper
     */
    protected $toolsHelper;
    /**
     * @var LanguageHelper $languageHelper
     */
    protected $languageHelper;

    /**
     * @var LocaleHelper $localeHelper
     */
    protected $localeHelper;

    /**
     * @var ModuleFactory $moduleFactory
     */
    protected $moduleFactory;

    /**
     * @var ClientHelper $clientHelper
     */
    protected $clientHelper;

    /**
     * @var ConfigurationHelper $configurationHelper
     */
    protected $configurationHelper;

    /**
     * @var CustomFieldsHelper $customFieldsHelper
     */
    protected $customFieldsHelper;

    public function setUp() {
        $this->paymentServiceBuilder = new PaymentServiceBuilder();
        $this->contextFactory = new ContextFactory();
        $this->toolsHelper = new ToolsHelper();
        $this->languageHelper = new LanguageHelper();
        $this->moduleFactory = new ModuleFactory();
        $this->clientHelper = new ClientHelper();
        $this->configurationHelper = new ConfigurationHelper();

        $this->localeHelper = new LocaleHelper(
            $this->languageHelper
        );
        $this->priceHelper = new PriceHelper(
            $this->toolsHelper,
            new CurrencyHelper()
        );

        $this->settingsHelper =  new SettingsHelper(
            new ShopHelper(),
            $this->configurationHelper
        );

        $this->cartData = new CartData(
            new ProductHelper(),
            $this->settingsHelper,
            $this->priceHelper,
            new ProductRepository()
        );

        $this->carrierHelper = new CarrierHelper(
            $this->contextFactory,
            new CarrierData()
        );

        $this->customFieldsHelper =   new CustomFieldsHelper(
            $this->languageHelper,
            $this->localeHelper,
            $this->settingsHelper
        );
    }


    public function testGetInstance() {
        $this->assertInstanceOf(PaymentService::class, $this->paymentServiceBuilder->getInstance());
    }

    public function testGetContextFactory() {
        $this->assertInstanceOf(ContextFactory::class, $this->paymentServiceBuilder->getContextFactory());
        $this->assertInstanceOf(ContextFactory::class, $this->paymentServiceBuilder->getContextFactory(
            $this->contextFactory
        ));
    }

    public function testModuleContextFactory() {
        $this->assertInstanceOf(ModuleFactory::class, $this->paymentServiceBuilder->getModuleFactory());
        $this->assertInstanceOf(ModuleFactory::class, $this->paymentServiceBuilder->getModuleFactory(
            $this->moduleFactory
        ));
    }

    public function testGetSettingsHelper() {
        $this->assertInstanceOf(SettingsHelper::class, $this->paymentServiceBuilder->getSettingsHelper());
        $this->assertInstanceOf(SettingsHelper::class, $this->paymentServiceBuilder->getSettingsHelper(
            $this->settingsHelper
        ));
    }

    public function testGetLocaleHelper() {
        $this->assertInstanceOf(LocaleHelper::class, $this->paymentServiceBuilder->getLocaleHelper());
        $this->assertInstanceOf(LocaleHelper::class, $this->paymentServiceBuilder->getLocaleHelper(
            $this->localeHelper
        ));
    }

    public function testGetToolsHelper() {
        $this->assertInstanceOf(ToolsHelper::class, $this->paymentServiceBuilder->getToolsHelper());
        $this->assertInstanceOf(ToolsHelper::class, $this->paymentServiceBuilder->getToolsHelper(
            $this->toolsHelper
        ));
    }

    public function testGetEligibilityHelper() {
        $this->assertInstanceOf(EligibilityHelper::class, $this->paymentServiceBuilder->getEligibilityHelper());
        $this->assertInstanceOf(EligibilityHelper::class, $this->paymentServiceBuilder->getEligibilityHelper(
            new EligibilityHelper(
                new PaymentData(
                    $this->toolsHelper,
                    $this->settingsHelper,
                    $this->priceHelper,
                    $this->customFieldsHelper,
                    $this->cartData,
                    new ShippingData(
                        $this->priceHelper,
                        $this->carrierHelper
                    ),
                    $this->contextFactory,
                    new AddressHelper($this->toolsHelper),
                    new CountryHelper(),
                    $this->localeHelper,
                    new StateHelper(),
                    new CustomerHelper(
                        $this->contextFactory,
                        new OrderHelper(),
                        new ValidateHelper()
                    ),
                    new CartHelper(
                        $this->contextFactory,
                        $this->toolsHelper,
                        $this->priceHelper,
                        $this->cartData,
                        new OrderRepository(),
                        new OrderStateHelper($this->contextFactory),
                        $this->carrierHelper
                    ),
                    $this->carrierHelper
                ),
                $this->priceHelper,
                $this->clientHelper,
                $this->settingsHelper,
                new ApiHelper(
                    $this->moduleFactory ,
                    $this->clientHelper
                ),
                $this->contextFactory
            )
        ));
    }

    public function testGetPriceHelper() {
        $this->assertInstanceOf(PriceHelper::class, $this->paymentServiceBuilder->getPriceHelper());
        $this->assertInstanceOf(PriceHelper::class, $this->paymentServiceBuilder->getPriceHelper(
            $this->priceHelper
        ));
    }

    public function testGetDateHelper() {
        $this->assertInstanceOf(DateHelper::class, $this->paymentServiceBuilder->getDateHelper());
        $this->assertInstanceOf(DateHelper::class, $this->paymentServiceBuilder->getDateHelper(
            new DateHelper()
        ));
    }

    public function testGetCustomFieldsHelper() {
        $this->assertInstanceOf(CustomFieldsHelper::class, $this->paymentServiceBuilder->getCustomFieldsHelper());
        $this->assertInstanceOf(CustomFieldsHelper::class, $this->paymentServiceBuilder->getCustomFieldsHelper(
            $this->customFieldsHelper
        ));
    }

    public function testGetCartData() {
        $this->assertInstanceOf(CartData::class, $this->paymentServiceBuilder->getCartData());
        $this->assertInstanceOf(CartData::class, $this->paymentServiceBuilder->getCartData(
            $this->cartData
        ));
    }

    public function testGetContextHelper() {
        $this->assertInstanceOf(ContextHelper::class, $this->paymentServiceBuilder->getContextHelper());
        $this->assertInstanceOf(ContextHelper::class, $this->paymentServiceBuilder->getContextHelper(
            new ContextHelper()
        ));
    }

    public function testGetMediaHelper() {
        $this->assertInstanceOf(MediaHelper::class, $this->paymentServiceBuilder->getMediaHelper());
        $this->assertInstanceOf(MediaHelper::class, $this->paymentServiceBuilder->getMediaHelper(
            new MediaHelper()
        ));
    }

    public function testGetPlanHelper() {
        $this->assertInstanceOf(PlanHelper::class, $this->paymentServiceBuilder->getPlanHelper());
        $this->assertInstanceOf(PlanHelper::class, $this->paymentServiceBuilder->getPlanHelper(
            new PlanHelper(
                $this->moduleFactory ,
                $this->contextFactory,
                new DateHelper(),
                $this->settingsHelper,
                $this->customFieldsHelper,
                new TranslationHelper(
                    $this->moduleFactory
                )
            )
        ));
    }

    public function testGetConfigurationHelper() {
        $this->assertInstanceOf(ConfigurationHelper::class, $this->paymentServiceBuilder->getConfigurationHelper());
        $this->assertInstanceOf(ConfigurationHelper::class, $this->paymentServiceBuilder->getConfigurationHelper(
            $this->configurationHelper
        ));
    }

    public function testGetTranslationHelper() {
        $this->assertInstanceOf(TranslationHelper::class, $this->paymentServiceBuilder->getTranslationHelper());
        $this->assertInstanceOf(TranslationHelper::class, $this->paymentServiceBuilder->getTranslationHelper(
            new TranslationHelper($this->moduleFactory)
        ));
    }

    public function testGetCartHelper() {
        $this->assertInstanceOf(CartHelper::class, $this->paymentServiceBuilder->getCartHelper());
        $this->assertInstanceOf(CartHelper::class, $this->paymentServiceBuilder->getCartHelper(
            new CartHelper(
                $this->contextFactory,
                $this->toolsHelper,
                $this->priceHelper,
                $this->cartData,
                new OrderRepository(),
                new OrderStateHelper($this->contextFactory),
                $this->carrierHelper
            )
        ));
    }

    public function testGetPaymentOptionTemplateHelper() {
        $this->assertInstanceOf(PaymentOptionTemplateHelper::class, $this->paymentServiceBuilder->getPaymentOptionTemplateHelper());
        $this->assertInstanceOf(PaymentOptionTemplateHelper::class, $this->paymentServiceBuilder->getPaymentOptionTemplateHelper(
            new PaymentOptionTemplateHelper(
                $this->contextFactory,
                $this->moduleFactory ,
                $this->settingsHelper,
                $this->configurationHelper,
                new TranslationHelper($this->moduleFactory),
                $this->priceHelper,
                new DateHelper()
            )
        ));
    }

    public function testGetPaymentOptionHelper() {
        $this->assertInstanceOf(PaymentOptionHelper::class, $this->paymentServiceBuilder->getPaymentOptionHelper());
        $this->assertInstanceOf(PaymentOptionHelper::class, $this->paymentServiceBuilder->getPaymentOptionHelper(
            new PaymentOptionHelper(
                $this->contextFactory,
                $this->moduleFactory,
                $this->settingsHelper,
                $this->customFieldsHelper,
                new MediaHelper(),
                $this->configurationHelper,
                new PaymentOptionTemplateHelper(
                    $this->contextFactory,
                    $this->moduleFactory,
                    $this->settingsHelper,
                    $this->configurationHelper,
                    new TranslationHelper($this->moduleFactory),
                    $this->priceHelper,
                    new DateHelper()
                )
            )
        ));
    }
}
