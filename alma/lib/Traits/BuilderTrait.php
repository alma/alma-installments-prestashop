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

namespace Alma\PrestaShop\Traits;

use Alma\PrestaShop\Factories\AddressFactory;
use Alma\PrestaShop\Factories\CarrierFactory;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\CurrencyFactory;
use Alma\PrestaShop\Factories\CustomerFactory;
use Alma\PrestaShop\Factories\MediaFactory;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Factories\OrderStateFactory;
use Alma\PrestaShop\Factories\PhpFactory;
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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Trait BuilderTrait.
 */
trait BuilderTrait
{
    /**
     * @param ShopHelper $shopHelper
     *
     * @return ShopHelper
     */
    public function getShopHelper($shopHelper = null)
    {
        if ($shopHelper) {
            return $shopHelper;
        }

        return new ShopHelper();
    }

    /**
     * @param ConfigurationHelper $configurationHelper
     *
     * @return ConfigurationHelper
     */
    public function getConfigurationHelper($configurationHelper = null)
    {
        if ($configurationHelper) {
            return $configurationHelper;
        }

        return new ConfigurationHelper();
    }

    /**
     * @param LanguageHelper $languageHelper
     *
     * @return LanguageHelper
     */
    public function getLanguageHelper($languageHelper = null)
    {
        if ($languageHelper) {
            return $languageHelper;
        }

        return new LanguageHelper();
    }

    /**
     * @param ToolsHelper $toolsHelper
     *
     * @return ToolsHelper
     */
    public function getToolsHelper($toolsHelper = null)
    {
        if ($toolsHelper) {
            return $toolsHelper;
        }

        return new ToolsHelper();
    }

    /**
     * @param CurrencyHelper $currencyHelper
     *
     * @return CurrencyHelper
     */
    public function getCurrencyHelper($currencyHelper = null)
    {
        if ($currencyHelper) {
            return $currencyHelper;
        }

        return new CurrencyHelper(
            $this->getContextFactory(),
            $this->getValidateHelper(),
            $this->getCurrencyFactory()
        );
    }

    /**
     * @param LocaleHelper $localeHelper
     *
     * @return LocaleHelper
     */
    public function getLocaleHelper($localeHelper = null)
    {
        if ($localeHelper) {
            return $localeHelper;
        }

        return new LocaleHelper(
            $this->getLanguageHelper()
        );
    }

    /**
     * @param SettingsHelper $settingsHelper
     *
     * @return SettingsHelper
     */
    public function getSettingsHelper($settingsHelper = null)
    {
        if ($settingsHelper) {
            return $settingsHelper;
        }

        return new SettingsHelper(
            $this->getShopHelper(),
            $this->getConfigurationHelper()
        );
    }

    /**
     * @param ProductHelper $productHelper
     *
     * @return ProductHelper
     */
    public function getProductHelper($productHelper = null)
    {
        if ($productHelper) {
            return $productHelper;
        }

        return new ProductHelper();
    }

    /**
     * @param PriceHelper $priceHelper
     *
     * @return PriceHelper
     */
    public function getPriceHelper($priceHelper = null)
    {
        if ($priceHelper) {
            return $priceHelper;
        }

        return new PriceHelper(
            $this->getToolsHelper(),
            $this->getCurrencyHelper(),
            $this->getContextFactory()
        );
    }

    /**
     * @param ProductRepository $productRepository
     *
     * @return ProductRepository
     */
    public function getProductRepository($productRepository = null)
    {
        if ($productRepository) {
            return $productRepository;
        }

        return new ProductRepository();
    }

    /**
     * @param ModuleFactory $moduleFactory
     *
     * @return ModuleFactory
     */
    public function getModuleFactory($moduleFactory = null)
    {
        if ($moduleFactory) {
            return $moduleFactory;
        }

        return new ModuleFactory();
    }

    /**
     * @param ContextFactory $contextFactory
     *
     * @return ContextFactory
     */
    public function getContextFactory($contextFactory = null)
    {
        if ($contextFactory) {
            return $contextFactory;
        }

        return new ContextFactory();
    }

    /**
     * @param DateHelper $dateHelper
     *
     * @return DateHelper
     */
    public function getDateHelper($dateHelper = null)
    {
        if ($dateHelper) {
            return $dateHelper;
        }

        return new DateHelper();
    }

    /**
     * @param CustomFieldsHelper $customFieldsHelper
     *
     * @return CustomFieldsHelper
     */
    public function getCustomFieldsHelper($customFieldsHelper = null)
    {
        if ($customFieldsHelper) {
            return $customFieldsHelper;
        }

        return new CustomFieldsHelper(
            $this->getLanguageHelper(),
            $this->getLocaleHelper(),
            $this->getSettingsHelper(),
            $this->getModuleFactory()
        );
    }

    /**
     * @param TranslationHelper $translationHelper
     *
     * @return TranslationHelper
     */
    public function getTranslationHelper($translationHelper = null)
    {
        if ($translationHelper) {
            return $translationHelper;
        }

        return new TranslationHelper(
            $this->getModuleFactory()
        );
    }

    /**
     * @param CarrierData $carrierData
     *
     * @return CarrierData
     */
    public function getCarrierData($carrierData = null)
    {
        if ($carrierData) {
            return $carrierData;
        }

        return new CarrierData();
    }

    /**
     * @param CartData $cartData
     *
     * @return CartData
     */
    public function getCartData($cartData = null)
    {
        if ($cartData) {
            return $cartData;
        }

        return new CartData(
            $this->getProductHelper(),
            $this->getSettingsHelper(),
            $this->getPriceHelper(),
            $this->getProductRepository()
        );
    }

    /**
     * @param OrderRepository $orderRepository
     *
     * @return OrderRepository
     */
    public function getOrderRepository($orderRepository = null)
    {
        if ($orderRepository) {
            return $orderRepository;
        }

        return new OrderRepository();
    }

    /**
     * @param OrderStateHelper $orderStateHelper
     *
     * @return OrderStateHelper
     */
    public function getOrderStateHelper($orderStateHelper = null)
    {
        if ($orderStateHelper) {
            return $orderStateHelper;
        }

        return new OrderStateHelper(
            $this->getContextFactory(),
            $this->getOrderStateFactory()
        );
    }

    /**
     * @param CarrierHelper $carrierHelper
     *
     * @return CarrierHelper
     */
    public function getCarrierHelper($carrierHelper = null)
    {
        if ($carrierHelper) {
            return $carrierHelper;
        }

        return new CarrierHelper(
            $this->getContextFactory(),
            $this->getCarrierData()
        );
    }

    /**
     * @param OrderHelper $orderHelper
     *
     * @return OrderHelper
     */
    public function getOrderHelper($orderHelper = null)
    {
        if ($orderHelper) {
            return $orderHelper;
        }

        return new OrderHelper();
    }

    /**
     * @param ValidateHelper $validateHelper
     *
     * @return ValidateHelper
     */
    public function getValidateHelper($validateHelper = null)
    {
        if ($validateHelper) {
            return $validateHelper;
        }

        return new ValidateHelper();
    }

    /**
     * @param ShippingData $shippingData
     *
     * @return ShippingData
     */
    public function getShippingData($shippingData = null)
    {
        if ($shippingData) {
            return $shippingData;
        }

        return new ShippingData(
            $this->getPriceHelper(),
            $this->getCarrierFactory()
        );
    }

    /**
     * @param AddressHelper $addressHelper
     *
     * @return AddressHelper
     */
    public function getAddressHelper($addressHelper = null)
    {
        if ($addressHelper) {
            return $addressHelper;
        }

        return new AddressHelper(
            $this->getToolsHelper(),
            $this->getContextFactory()
        );
    }

    /**
     * @param CountryHelper $countryHelper
     *
     * @return CountryHelper
     */
    public function getCountryHelper($countryHelper = null)
    {
        if ($countryHelper) {
            return $countryHelper;
        }

        return new CountryHelper();
    }

    /**
     * @param StateHelper $stateHelper
     *
     * @return StateHelper
     */
    public function getStateHelper($stateHelper = null)
    {
        if ($stateHelper) {
            return $stateHelper;
        }

        return new StateHelper();
    }

    /**
     * @param CustomerHelper $customerHelper
     *
     * @return CustomerHelper
     */
    public function getCustomerHelper($customerHelper = null)
    {
        if ($customerHelper) {
            return $customerHelper;
        }

        return new CustomerHelper(
            $this->getContextFactory(),
            $this->getOrderHelper(),
            $this->getValidateHelper(),
            $this->getCustomerFactory()
        );
    }

    /**
     * @param CartHelper $cartHelper
     *
     * @return CartHelper
     */
    public function getCartHelper($cartHelper = null)
    {
        if ($cartHelper) {
            return $cartHelper;
        }

        return new CartHelper(
            $this->getContextFactory(),
            $this->getToolsHelper(),
            $this->getPriceHelper(),
            $this->getCartData(),
            $this->getOrderRepository(),
            $this->getOrderStateHelper(),
            $this->getCarrierHelper()
        );
    }

    /**
     * @param ClientHelper $clientHelper
     *
     * @return ClientHelper
     */
    public function getClientHelper($clientHelper = null)
    {
        if ($clientHelper) {
            return $clientHelper;
        }

        return new ClientHelper();
    }

    /**
     * @param PaymentData $paymentData
     *
     * @return PaymentData
     */
    public function getPaymentData($paymentData = null)
    {
        if ($paymentData) {
            return $paymentData;
        }

        return new PaymentData(
            $this->getToolsHelper(),
            $this->getSettingsHelper(),
            $this->getPriceHelper(),
            $this->getCustomFieldsHelper(),
            $this->getCartData(),
            $this->getShippingData(),
            $this->getContextFactory(),
            $this->getAddressHelper(),
            $this->getCountryHelper(),
            $this->getLocaleHelper(),
            $this->getStateHelper(),
            $this->getCustomerHelper(),
            $this->getCartHelper(),
            $this->getCarrierHelper(),
            $this->getAddressFactory()
        );
    }

    /**
     * @param ApiHelper $apiHelper
     *
     * @return ApiHelper
     */
    public function getApiHelper($apiHelper = null)
    {
        if ($apiHelper) {
            return $apiHelper;
        }

        return new ApiHelper(
            $this->getModuleFactory(),
            $this->getClientHelper()
        );
    }

    /**
     * @param MediaHelper $mediaHelper
     *
     * @return MediaHelper
     */
    public function getMediaHelper($mediaHelper = null)
    {
        if ($mediaHelper) {
            return $mediaHelper;
        }

        return new MediaHelper(
            $this->getMediaFactory(),
            $this->getModuleFactory(),
            $this->getPhpFactory()
        );
    }

    /**
     * @param PaymentOptionTemplateHelper $paymentOptionTemplateHelper
     *
     * @return PaymentOptionTemplateHelper
     */
    public function getPaymentOptionTemplateHelper($paymentOptionTemplateHelper = null)
    {
        if ($paymentOptionTemplateHelper) {
            return $paymentOptionTemplateHelper;
        }

        return new PaymentOptionTemplateHelper(
            $this->getContextFactory(),
            $this->getModuleFactory(),
            $this->getSettingsHelper(),
            $this->getConfigurationHelper(),
            $this->getTranslationHelper(),
            $this->getPriceHelper(),
            $this->getDateHelper()
        );
    }

    /**
     * @param EligibilityHelper $eligibilityHelper
     *
     * @return EligibilityHelper
     */
    public function getEligibilityHelper($eligibilityHelper = null)
    {
        if ($eligibilityHelper) {
            return $eligibilityHelper;
        }

        return new EligibilityHelper(
            $this->getPaymentData(),
            $this->getPriceHelper(),
            $this->getClientHelper(),
            $this->getSettingsHelper(),
            $this->getApiHelper(),
            $this->getContextFactory()
        );
    }

    /**
     * @param ContextHelper $contextHelper
     *
     * @return ContextHelper
     */
    public function getContextHelper($contextHelper = null)
    {
        if ($contextHelper) {
            return $contextHelper;
        }

        return new ContextHelper(
            $this->getContextFactory(),
            $this->getModuleFactory()
        );
    }

    /**
     * @param PlanHelper $planHelper
     *
     * @return PlanHelper
     */
    public function getPlanHelper($planHelper = null)
    {
        if ($planHelper) {
            return $planHelper;
        }

        return new PlanHelper(
            $this->getModuleFactory(),
            $this->getContextFactory(),
            $this->getDateHelper(),
            $this->getSettingsHelper(),
            $this->getCustomFieldsHelper(),
            $this->getTranslationHelper()
        );
    }

    /**
     * @param PaymentOptionHelper $paymentOptionHelper
     *
     * @return PaymentOptionHelper
     */
    public function getPaymentOptionHelper($paymentOptionHelper = null)
    {
        if ($paymentOptionHelper) {
            return $paymentOptionHelper;
        }

        return new PaymentOptionHelper(
            $this->getContextFactory(),
            $this->getModuleFactory(),
            $this->getSettingsHelper(),
            $this->getCustomFieldsHelper(),
            $this->getMediaHelper(),
            $this->getConfigurationHelper(),
            $this->getPaymentOptionTemplateHelper(),
            $this->getMediaFactory()
        );
    }

    /**
     * @param AddressFactory $addressFactory
     *
     * @return AddressFactory
     */
    public function getAddressFactory($addressFactory = null)
    {
        if ($addressFactory) {
            return $addressFactory;
        }

        return new AddressFactory();
    }

    /**
     * @param OrderStateFactory $orderStateFactory
     *
     * @return OrderStateFactory
     */
    public function getOrderStateFactory($orderStateFactory = null)
    {
        if ($orderStateFactory) {
            return $orderStateFactory;
        }

        return new OrderStateFactory();
    }

    /**
     * @param MediaFactory $mediaFactory
     *
     * @return MediaFactory
     */
    public function getMediaFactory($mediaFactory = null)
    {
        if ($mediaFactory) {
            return $mediaFactory;
        }

        return new MediaFactory();
    }

    /**
     * @param PhpFactory $phpFactory
     *
     * @return PhpFactory
     */
    public function getPhpFactory($phpFactory = null)
    {
        if ($phpFactory) {
            return $phpFactory;
        }

        return new PhpFactory();
    }

    /**
     * @param CarrierFactory $carrierFactory
     *
     * @return CarrierFactory
     */
    public function getCarrierFactory($carrierFactory = null)
    {
        if ($carrierFactory) {
            return $carrierFactory;
        }

        return new CarrierFactory();
    }

    /**
     * @param CustomerFactory $customerFactory
     *
     * @return CustomerFactory
     */
    public function getCustomerFactory($customerFactory = null)
    {
        if ($customerFactory) {
            return $customerFactory;
        }

        return new CustomerFactory();
    }

    /**
     * @param CurrencyFactory $currencyFactory
     *
     * @return CurrencyFactory
     */
    public function getCurrencyFactory($currencyFactory = null)
    {
        if ($currencyFactory) {
            return $currencyFactory;
        }

        return new CurrencyFactory();
    }
}
