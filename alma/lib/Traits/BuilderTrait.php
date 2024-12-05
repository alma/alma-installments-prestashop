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

namespace Alma\PrestaShop\Traits;

use Alma\API\Lib\PaymentValidator;
use Alma\PrestaShop\Factories\AddressFactory;
use Alma\PrestaShop\Factories\CarrierFactory;
use Alma\PrestaShop\Factories\CartFactory;
use Alma\PrestaShop\Factories\CategoryFactory;
use Alma\PrestaShop\Factories\CombinationFactory;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\CurrencyFactory;
use Alma\PrestaShop\Factories\CustomerFactory;
use Alma\PrestaShop\Factories\EligibilityFactory;
use Alma\PrestaShop\Factories\LinkFactory;
use Alma\PrestaShop\Factories\MediaFactory;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Factories\OrderStateFactory;
use Alma\PrestaShop\Factories\PhpFactory;
use Alma\PrestaShop\Factories\ProductFactory;
use Alma\PrestaShop\Factories\ToolsFactory;
use Alma\PrestaShop\Helpers\AddressHelper;
use Alma\PrestaShop\Helpers\Admin\AdminInsuranceHelper;
use Alma\PrestaShop\Helpers\Admin\TabsHelper;
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
use Alma\PrestaShop\Helpers\FeePlanHelper;
use Alma\PrestaShop\Helpers\ImageHelper;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\InsuranceProductHelper;
use Alma\PrestaShop\Helpers\LanguageHelper;
use Alma\PrestaShop\Helpers\LocaleHelper;
use Alma\PrestaShop\Helpers\MediaHelper;
use Alma\PrestaShop\Helpers\OrderHelper;
use Alma\PrestaShop\Helpers\OrderStateHelper;
use Alma\PrestaShop\Helpers\PaymentHelper;
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
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Model\CarrierData;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Model\PaymentData;
use Alma\PrestaShop\Model\ShippingData;
use Alma\PrestaShop\Modules\OpartSaveCart\OpartSaveCartCartRepository;
use Alma\PrestaShop\Modules\OpartSaveCart\OpartSaveCartCartService;
use Alma\PrestaShop\Proxy\ToolsProxy;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\CartProductRepository;
use Alma\PrestaShop\Repositories\OrderRepository;
use Alma\PrestaShop\Repositories\ProductRepository;
use Alma\PrestaShop\Services\AttributeGroupProductService;
use Alma\PrestaShop\Services\AttributeProductService;
use Alma\PrestaShop\Services\CartService;
use Alma\PrestaShop\Services\CombinationProductAttributeService;
use Alma\PrestaShop\Services\InsuranceApiService;
use Alma\PrestaShop\Services\InsuranceService;

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
            $this->getConfigurationHelper(),
            $this->getCategoryFactory(),
            $this->getContextFactory(),
            $this->getValidateHelper()
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

        return new ModuleFactory(
            $this->getToolsHelper()
        );
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
            $this->getCarrierHelper(),
            $this->getCartFactory(),
            $this->getOrderHelper()
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
            $this->getClientHelper(),
            $this->getToolsHelper(),
            $this->getInsuranceService(),
            $this->getConfigurationHelper(),
            $this->getAdminInsuranceHelper()
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
            $this->getPriceHelper(),
            $this->getApiHelper(),
            $this->getContextFactory(),
            $this->getFeePlanHelper(),
            $this->getPaymentHelper()
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

        return new MediaFactory(
            $this->getModuleFactory()
        );
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

    /**
     * @param TabsHelper $tabsHelper
     *
     * @return TabsHelper
     */
    public function getTabsHelper($tabsHelper = null)
    {
        if ($tabsHelper) {
            return $tabsHelper;
        }

        return new TabsHelper();
    }

    /**
     * @param AlmaInsuranceProductRepository $almaInsuranceProductRepository
     *
     * @return AlmaInsuranceProductRepository
     */
    public function getAlmaInsuranceProductRepository($almaInsuranceProductRepository = null)
    {
        if ($almaInsuranceProductRepository) {
            return $almaInsuranceProductRepository;
        }

        return new AlmaInsuranceProductRepository();
    }

    /**
     * @param InsuranceService $insuranceService
     *
     * @return InsuranceService
     */
    public function getInsuranceService($insuranceService = null)
    {
        if ($insuranceService) {
            return $insuranceService;
        }

        return new InsuranceService();
    }

    /**
     * @param AdminInsuranceHelper $insuranceHelper
     *
     * @return AdminInsuranceHelper
     */
    public function getAdminInsuranceHelper($insuranceHelper = null)
    {
        if ($insuranceHelper) {
            return $insuranceHelper;
        }

        return new AdminInsuranceHelper(
            $this->getModuleFactory(),
            $this->getTabsHelper(),
            $this->getConfigurationHelper(),
            $this->getAlmaInsuranceProductRepository()
        );
    }

    /**
     * @param CartFactory $cartFactory
     *
     * @return CartFactory
     */
    public function getCartFactory($cartFactory = null)
    {
        if ($cartFactory) {
            return $cartFactory;
        }

        return new CartFactory();
    }

    /**
     * @param ToolsFactory $toolsFactory
     *
     * @return ToolsFactory
     */
    public function getToolsFactory($toolsFactory = null)
    {
        if ($toolsFactory) {
            return $toolsFactory;
        }

        return new ToolsFactory();
    }

    /**
     * @param ImageHelper $imageHelper
     *
     * @return ImageHelper
     */
    public function getImageHelper($imageHelper = null)
    {
        if ($imageHelper) {
            return $imageHelper;
        }

        return new ImageHelper();
    }

    /**
     * @param EligibilityFactory $eligibilityFactory
     *
     * @return EligibilityFactory
     */
    public function getEligibilityFactory($eligibilityFactory = null)
    {
        if ($eligibilityFactory) {
            return $eligibilityFactory;
        }

        return new EligibilityFactory();
    }

    /**
     * @param FeePlanHelper $feePlanHelper
     *
     * @return FeePlanHelper
     */
    public function getFeePlanHelper($feePlanHelper = null)
    {
        if ($feePlanHelper) {
            return $feePlanHelper;
        }

        return new FeePlanHelper(
            new SettingsHelper(
                new ShopHelper(),
                new ConfigurationHelper(),
                new CategoryFactory(),
                new ContextFactory(),
                new ValidateHelper()
            ),
            new EligibilityFactory(),
            new PriceHelper(
                new ToolsHelper(),
                new CurrencyHelper(
                    new ContextFactory(),
                    new ValidateHelper(),
                    new CurrencyFactory()
                ),
                new ContextFactory()
            ),
            new ToolsProxy()
        );
    }

    /**
     * @param PaymentHelper $paymentHelper
     *
     * @return PaymentHelper
     */
    public function getPaymentHelper($paymentHelper = null)
    {
        if ($paymentHelper) {
            return $paymentHelper;
        }

        return new PaymentHelper($this->getPaymentData());
    }

    /**
     * @param CartProductRepository $cartProductRepository
     *
     * @return CartProductRepository
     */
    public function getCartProductRepository($cartProductRepository = null)
    {
        if ($cartProductRepository) {
            return $cartProductRepository;
        }

        return new CartProductRepository();
    }

    /**
     * @param $productFactory
     *
     * @return ProductFactory|mixed
     */
    public function getProductFactory($productFactory = null)
    {
        if ($productFactory) {
            return $productFactory;
        }

        return new ProductFactory();
    }

    /**
     * @param $combinationFactory
     *
     * @return CombinationFactory|mixed
     */
    public function getCombinationFactory($combinationFactory = null)
    {
        if ($combinationFactory) {
            return $combinationFactory;
        }

        return new CombinationFactory();
    }

    /**
     * @param $linkFactory
     *
     * @return LinkFactory|mixed
     */
    public function getLinkFactory($linkFactory = null)
    {
        if ($linkFactory) {
            return $linkFactory;
        }

        return new LinkFactory();
    }

    /**
     * @param OpartSaveCartCartService $cartService
     *
     * @return OpartSaveCartCartService
     */
    public function getOpartSaveCartCartService($cartService = null)
    {
        if ($cartService) {
            return $cartService;
        }

        return new OpartSaveCartCartService(
           $this->getModuleFactory(),
           $this->getOpartSaveCartRepository(),
           $this->getToolsFactory(),
           $this->getCartFactory()
        );
    }

    /**
     * @param OpartSaveCartCartRepository $opartSaveCartRepository
     *
     * @return OpartSaveCartCartRepository
     */
    public function getOpartSaveCartRepository($opartSaveCartRepository = null)
    {
        if ($opartSaveCartRepository) {
            return $opartSaveCartRepository;
        }

        return new OpartSaveCartCartRepository();
    }

    /**
     * @param InsuranceHelper $insuranceHelper
     *
     * @return InsuranceHelper
     */
    public function getInsuranceHelper($insuranceHelper = null)
    {
        if ($insuranceHelper) {
            return $insuranceHelper;
        }

        return new InsuranceHelper(
            $this->getCartProductRepository(),
            $this->getProductRepository(),
            $this->getAlmaInsuranceProductRepository(),
            $this->getContextFactory(),
            $this->getToolsHelper(),
            $this->getSettingsHelper()
        );
    }

    /**
     * @param InsuranceProductHelper $insuranceProductHelper
     *
     * @return InsuranceProductHelper
     */
    public function getInsuranceProductHelper($insuranceProductHelper = null)
    {
        if ($insuranceProductHelper) {
            return $insuranceProductHelper;
        }

        return new InsuranceProductHelper(
            $this->getAlmaInsuranceProductRepository(),
            $this->getContextFactory()
        );
    }

    /**
     * @param Logger $paymentHelper
     *
     * @return Logger
     */
    public function getLogger($logger = null)
    {
        if ($logger) {
            return $logger;
        }

        return new Logger();
    }

    /**
     * @param AttributeGroupProductService $attributeGroupProductService
     *
     * @return AttributeGroupProductService
     */
    public function getAttributeGroupProductService($attributeGroupProductService = null)
    {
        if ($attributeGroupProductService) {
            return $attributeGroupProductService;
        }

        return new AttributeGroupProductService();
    }

    /**
     * @param AttributeProductService $attributeProductService
     *
     * @return AttributeProductService
     */
    public function getAttributeProductService($attributeProductService = null)
    {
        if ($attributeProductService) {
            return $attributeProductService;
        }

        return new AttributeProductService();
    }

    /**
     * @param CombinationProductAttributeService $combinationProductAttributeService
     *
     * @return CombinationProductAttributeService
     */
    public function getCombinationProductAttributeService($combinationProductAttributeService = null)
    {
        if ($combinationProductAttributeService) {
            return $combinationProductAttributeService;
        }

        return new CombinationProductAttributeService();
    }

    /**
     * @param CartService $cartService
     *
     * @return CartService
     */
    public function getCartService($cartService = null)
    {
        if ($cartService) {
            return $cartService;
        }

        return new CartService(
            $this->getCartProductRepository(),
            $this->getContextFactory(),
            $this->getOpartSaveCartCartService(),
            $this->getInsuranceHelper(),
            $this->getInsuranceProductHelper(),
            $this->getToolsFactory(),
            $this->getCartFactory(),
            $this->getProductHelper()
        );
    }

    /**
     * @param InsuranceApiService $insuranceApiService
     *
     * @return InsuranceApiService
     */
    public function getInsuranceApiService($insuranceApiService = null)
    {
        if ($insuranceApiService) {
            return $insuranceApiService;
        }

        return new InsuranceApiService();
    }

    /**
     * @return PaymentValidator
     */
    public function getClientPaymentValidator()
    {
        return new PaymentValidator();
    }

    /**
     * @return CategoryFactory
     */
    public function getCategoryFactory()
    {
        return new CategoryFactory();
    }

    /**
     * @return ToolsProxy
     */
    public function getToolsProxy()
    {
        return new ToolsProxy();
    }
}
