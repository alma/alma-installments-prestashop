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
    protected $paymentServiceBuilder
    ;
    public function setUp() {
        $this->paymentServiceBuilder = new PaymentServiceBuilder();
    }


    public function testGetInstance() {
        $this->assertInstanceOf(PaymentService::class, $this->paymentServiceBuilder->getInstance());
    }

    public function testGetContextFactory() {
        $this->assertInstanceOf(ContextFactory::class, $this->paymentServiceBuilder->getContextFactory());
        $this->assertInstanceOf(ContextFactory::class, $this->paymentServiceBuilder->getContextFactory(
            new ContextFactory())
        );
    }

    public function testModuleContextFactory() {
        $this->assertInstanceOf(ModuleFactory::class, $this->paymentServiceBuilder->getModuleFactory());
        $this->assertInstanceOf(ModuleFactory::class, $this->paymentServiceBuilder->getModuleFactory(
            new ModuleFactory())
        );
    }

    public function testGetSettingsHelper() {
        $this->assertInstanceOf(SettingsHelper::class, $this->paymentServiceBuilder->getSettingsHelper());
        $this->assertInstanceOf(SettingsHelper::class, $this->paymentServiceBuilder->getSettingsHelper(
            new SettingsHelper(
                new ShopHelper(),
                new ConfigurationHelper()
            ))
        );
    }

    public function testGetLocaleHelper() {
        $this->assertInstanceOf(LocaleHelper::class, $this->paymentServiceBuilder->getLocaleHelper());
        $this->assertInstanceOf(LocaleHelper::class, $this->paymentServiceBuilder->getLocaleHelper(
            new LocaleHelper(
                new LanguageHelper()
            ))
        );
    }

    public function testGetToolsHelper() {
        $this->assertInstanceOf(ToolsHelper::class, $this->paymentServiceBuilder->getToolsHelper());
        $this->assertInstanceOf(ToolsHelper::class, $this->paymentServiceBuilder->getToolsHelper(
            new ToolsHelper()
        ));
    }

    public function testGetEligibilityHelper() {
        $this->assertInstanceOf(EligibilityHelper::class, $this->paymentServiceBuilder->getEligibilityHelper());
        $this->assertInstanceOf(EligibilityHelper::class, $this->paymentServiceBuilder->getEligibilityHelper(
            new EligibilityHelper(
                new PaymentData(
                    new ToolsHelper(),
                    new SettingsHelper(
                        new ShopHelper(),
                        new ConfigurationHelper()
                    ),
                    new PriceHelper(
                        new ToolsHelper(),
                        new CurrencyHelper()
                    ),
                    new CustomFieldsHelper(
                        new LanguageHelper(),
                        new LocaleHelper(
                            new LanguageHelper()
                        ),
                        new SettingsHelper(
                            new ShopHelper(),
                            new ConfigurationHelper()
                        )
                    ),
                    new CartData(
                        new ProductHelper(),
                        new SettingsHelper(
                            new ShopHelper(),
                            new ConfigurationHelper()
                        ),
                        new PriceHelper(
                            new ToolsHelper(),
                            new CurrencyHelper()
                        ),
                        new ProductRepository()
                    ),
                    new ShippingData(
                        new PriceHelper(
                            new ToolsHelper(),
                            new CurrencyHelper()
                        ),
                        new CarrierHelper(
                            new ContextFactory(),
                            new CarrierData()
                        )
                    ),
                    new ContextFactory(),
                    new AddressHelper(
                        new ToolsHelper()
                    ),
                    new CountryHelper(),
                    new LocaleHelper(
                        new LanguageHelper()
                    ),
                    new StateHelper(),
                    new CustomerHelper(
                        new ContextFactory(),
                        new OrderHelper(),
                        new ValidateHelper()
                    ),
                    new CartHelper(
                        new ContextFactory(),
                        new ToolsHelper(),
                        new PriceHelper(
                            new ToolsHelper(),
                            new CurrencyHelper()
                        ),
                        new CartData(
                            new ProductHelper(),
                            new SettingsHelper(
                                new ShopHelper(),
                                new ConfigurationHelper()
                            ),
                            new PriceHelper(
                                new ToolsHelper(),
                                new CurrencyHelper()
                            ),
                            new ProductRepository()
                        ),
                        new OrderRepository(),
                        new OrderStateHelper(
                            new ContextFactory()
                        ),
                        new CarrierHelper(
                            new ContextFactory(),
                            new CarrierData()
                        )
                    ),
                    new CarrierHelper(
                        new ContextFactory(),
                        new CarrierData()
                    )
                ),
                new PriceHelper(
                    new ToolsHelper(),
                    new CurrencyHelper()
                ),
                new ClientHelper(),
                new SettingsHelper(
                    new ShopHelper(),
                    new ConfigurationHelper()
                ),
                new ApiHelper(
                    new ModuleFactory(),
                    new ClientHelper()
                ),
                new ContextFactory()
            )
        ));
    }

    public function testGetPriceHelper() {
        $this->assertInstanceOf(PriceHelper::class, $this->paymentServiceBuilder->getPriceHelper());
        $this->assertInstanceOf(PriceHelper::class, $this->paymentServiceBuilder->getPriceHelper(
            new PriceHelper(
                new ToolsHelper(),
                new CurrencyHelper()
            )
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
            new CustomFieldsHelper(
                new LanguageHelper(),
                new LocaleHelper(
                    new LanguageHelper()
                ),
                new SettingsHelper(
                    new ShopHelper(),
                    new ConfigurationHelper()
                )
            )
        ));
    }

    public function testGetCartData() {
        $this->assertInstanceOf(CartData::class, $this->paymentServiceBuilder->getCartData());
        $this->assertInstanceOf(CartData::class, $this->paymentServiceBuilder->getCartData(
            new CartData(
                new ProductHelper(),
                new SettingsHelper(
                    new ShopHelper(),
                    new ConfigurationHelper()
                ),
                new PriceHelper(
                    new ToolsHelper(),
                    new CurrencyHelper()
                ),
                new ProductRepository()
            )
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
                new ModuleFactory(),
                new ContextFactory(),
                new DateHelper(),
                new SettingsHelper(
                    new ShopHelper(),
                    new ConfigurationHelper()
                ),
                new CustomFieldsHelper(
                    new LanguageHelper(),
                    new LocaleHelper(new LanguageHelper()),
                    new SettingsHelper(
                        new ShopHelper(),
                        new ConfigurationHelper()
                    )
                ),
                new TranslationHelper(
                    new ModuleFactory()
                )
            )
        ));
    }

    public function testGetConfigurationHelper() {
        $this->assertInstanceOf(ConfigurationHelper::class, $this->paymentServiceBuilder->getConfigurationHelper());
        $this->assertInstanceOf(ConfigurationHelper::class, $this->paymentServiceBuilder->getConfigurationHelper(
            new ConfigurationHelper()
        ));
    }

    public function testGetTranslationHelper() {
        $this->assertInstanceOf(TranslationHelper::class, $this->paymentServiceBuilder->getTranslationHelper());
        $this->assertInstanceOf(TranslationHelper::class, $this->paymentServiceBuilder->getTranslationHelper(
            new TranslationHelper(
                new ModuleFactory()
            )
        ));
    }

    public function testGetCartHelper() {
        $this->assertInstanceOf(CartHelper::class, $this->paymentServiceBuilder->getCartHelper());
        $this->assertInstanceOf(CartHelper::class, $this->paymentServiceBuilder->getCartHelper(
            new CartHelper(
                new ContextFactory(),
                new ToolsHelper(),
                new PriceHelper(
                    new ToolsHelper(),
                    new CurrencyHelper()
                ),
                new CartData(
                    new ProductHelper(),
                    new SettingsHelper(
                        new ShopHelper(),
                        new ConfigurationHelper()
                    ),
                    new PriceHelper(
                        new ToolsHelper(),
                        new CurrencyHelper()
                    ),
                    new ProductRepository()
                ),
                new OrderRepository(),
                new OrderStateHelper(
                    new ContextFactory()
                ),
                new CarrierHelper(
                    new ContextFactory(),
                    new CarrierData()
                )
            )
        ));
    }

    public function testGetPaymentOptionTemplateHelper() {
        $this->assertInstanceOf(PaymentOptionTemplateHelper::class, $this->paymentServiceBuilder->getPaymentOptionTemplateHelper());
        $this->assertInstanceOf(PaymentOptionTemplateHelper::class, $this->paymentServiceBuilder->getPaymentOptionTemplateHelper(
            new PaymentOptionTemplateHelper(
                new ContextFactory(),
                new ModuleFactory(),
                new SettingsHelper(
                    new ShopHelper(),
                    new ConfigurationHelper()
                ),
                new ConfigurationHelper(),
                new TranslationHelper(
                    new ModuleFactory()
                ),
                new PriceHelper(
                    new ToolsHelper(),
                    new CurrencyHelper()
                ),
                new DateHelper()
            )
        ));
    }

    public function testGetPaymentOptionHelper() {
        $this->assertInstanceOf(PaymentOptionHelper::class, $this->paymentServiceBuilder->getPaymentOptionHelper());
        $this->assertInstanceOf(PaymentOptionHelper::class, $this->paymentServiceBuilder->getPaymentOptionHelper(
            new PaymentOptionHelper(
                new ContextFactory(),
                new ModuleFactory(),
                new SettingsHelper(
                    new ShopHelper(),
                    new ConfigurationHelper()
                ),
                new CustomFieldsHelper(
                    new LanguageHelper(),
                    new LocaleHelper(
                        new LanguageHelper()
                    ),
                    new SettingsHelper(
                        new ShopHelper(),
                        new ConfigurationHelper()
                    )
                ),
                new MediaHelper(),
                new ConfigurationHelper(),
                new PaymentOptionTemplateHelper(
                    new ContextFactory(),
                    new ModuleFactory(),
                    new SettingsHelper(
                        new ShopHelper(),
                        new ConfigurationHelper()
                    ),
                    new ConfigurationHelper(),
                    new TranslationHelper(
                        new ModuleFactory()
                    ),
                    new PriceHelper(
                        new ToolsHelper(),
                        new CurrencyHelper()
                    ),
                    new DateHelper()
                )
            )
        ));
    }
}
