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

namespace Alma\PrestaShop\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined('ALMA_MODE_TEST')) {
    define('ALMA_MODE_TEST', 'test');
}

if (!defined('ALMA_MODE_LIVE')) {
    define('ALMA_MODE_LIVE', 'live');
}

use Alma\API\Endpoints\Results\Eligibility;
use Alma\API\Entities\Payment;
use Alma\PrestaShop\Exceptions\AlmaException;
use Alma\PrestaShop\Exceptions\EncryptionException;
use Alma\PrestaShop\Factories\CategoryFactory;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Forms\ApiAdminFormBuilder;
use Alma\PrestaShop\Forms\ExcludedCategoryAdminFormBuilder;
use Alma\PrestaShop\Forms\InpageAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentOnTriggeringAdminFormBuilder;
use Alma\PrestaShop\Forms\ShareOfCheckoutAdminFormBuilder;

/**
 * Settings.
 */
class SettingsHelper
{
    const ALMA_FULLY_CONFIGURED = 'ALMA_FULLY_CONFIGURED';
    const ALMA_EXCLUDED_CATEGORIES = 'ALMA_EXCLUDED_CATEGORIES';
    /**
     * @var ShopHelper
     */
    protected $shopHelper;

    /**
     * @var ConfigurationHelper
     */
    protected $configurationHelper;
    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;
    /**
     * @var ContextFactory
     */
    protected $contextFactory;
    /**
     * @var ValidateHelper
     */
    protected $validateHelper;

    /**
     * @param ShopHelper $shopHelper
     * @param ConfigurationHelper $configurationHelper
     */
    public function __construct(
        $shopHelper,
        $configurationHelper,
        $categoryFactory,
        $contextFactory,
        $validateHelper
    ) {
        $this->shopHelper = $shopHelper;
        $this->configurationHelper = $configurationHelper;
        $this->categoryFactory = $categoryFactory;
        $this->contextFactory = $contextFactory;
        $this->validateHelper = $validateHelper;
    }

    /**
     * Translate strings.
     *
     * @param string $str
     *
     * @return string
     */
    public static function l($str)
    {
        return \Translate::getModuleTranslation('alma', $str, ConstantsHelper::SOURCE_CUSTOM_FIELDS);
    }

    /**
     * Get value from key in config.
     *
     * @param string $configKey
     * @param string $default
     *
     * @deprecated use getKey()
     *
     * @return false|mixed|string|null
     */
    public static function get($configKey, $default = null)
    {
        $idShop = \Shop::getContextShopID(true);
        $idShopGroup = \Shop::getContextShopGroupID(true);

        $value = \Configuration::get($configKey, null, $idShopGroup, $idShop, $default);

        // Configuration::get in PrestaShop 1.5 doesn't have a default argument, so we handle it here
        if (!$value && !\Configuration::hasKey($configKey, null, $idShopGroup, $idShop)) {
            $value = $default;
        }

        return $value;
    }

    /**
     * Get value from key in config.
     *
     * @param string $configKey
     * @param string $default
     *
     * @return false|string|null
     */
    public function getKey($configKey, $default = null)
    {
        $idShop = $this->shopHelper->getContextShopID(true);
        $idShopGroup = $this->shopHelper->getContextShopGroupID(true);

        $value = $this->configurationHelper->get($configKey, null, $idShopGroup, $idShop, $default);

        // Configuration::get in PrestaShop 1.5 doesn't have a default argument, so we handle it here
        if (
            !$value
            && !$this->configurationHelper->hasKey($configKey, null, $idShopGroup, $idShop)
        ) {
            $value = $default;
        }

        return $value;
    }

    /**
     * Update value in config.
     *
     * @param string $configKey
     * @param string $value
     *
     * @return void
     */
    public function updateKey($configKey, $value)
    {
        $idShop = $this->shopHelper->getContextShopID(true);
        $idShopGroup = $this->shopHelper->getContextShopGroupID(true);

        $this->configurationHelper->updateValue($configKey, $value, false, $idShopGroup, $idShop);
    }

    /**
     * Update value in config.
     *
     * @param string $configKey
     * @param string $value
     *
     * @deprecated use updateKey
     *
     * @return void
     */
    public static function updateValue($configKey, $value)
    {
        $idShop = \Shop::getContextShopID(true);
        $idShopGroup = \Shop::getContextShopGroupID(true);
        \Configuration::updateValue($configKey, $value, false, $idShopGroup, $idShop);
    }

    /**
     * Delete all values config.
     *
     * @return bool true
     */
    public static function deleteAllValues()
    {
        $configKeys = [
            self::ALMA_FULLY_CONFIGURED,
            'ALMA_ACTIVATE_LOGGING',
            ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_STATE,
            ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_DATE,
            'ALMA_SOC_CRON_TASK',
            'ALMA_API_MODE',
            'ALMA_MERCHANT_ID',
            'ALMA_LIVE_API_KEY',
            'ALMA_TEST_API_KEY',
            'ALMA_SHOW_DISABLED_BUTTON',
            'ALMA_SHOW_ELIGIBILITY_MESSAGE',
            PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_TITLE,
            PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_DESC,
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE,
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC,
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE,
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC,
            PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE,
            PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC,
            ExcludedCategoryAdminFormBuilder::ALMA_NOT_ELIGIBLE_CATEGORIES,
            InpageAdminFormBuilder::ALMA_ACTIVATE_INPAGE,
            InpageAdminFormBuilder::ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR,
            InpageAdminFormBuilder::ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR,
            'ALMA_STATE_REFUND',
            'ALMA_STATE_REFUND_ENABLED',
            'ALMA_STATE_TRIGGER',
            'ALMA_PAYMENT_ON_TRIGGERING_ENABLED',
            PaymentOnTriggeringAdminFormBuilder::ALMA_DESCRIPTION_TRIGGER,
            'ALMA_EXCLUDED_CATEGORIES',
            'ALMA_SHOW_PRODUCT_ELIGIBILITY',
            'ALMA_FEE_PLANS',
            'ALMA_PRODUCT_ATTR_RADIO_SELECTOR',
            'ALMA_PRODUCT_ATTR_SELECTOR',
            'ALMA_PRODUCT_COLOR_PICK_SELECTOR',
            'ALMA_PRODUCT_PRICE_SELECTOR',
            'ALMA_PRODUCT_QUANTITY_SELECTOR',
            'ALMA_WIDGET_POSITION_SELECTOR',
            'ALMA_WIDGET_POSITION_CUSTOM',
            'ALMA_CART_WDGT_POS_SELECTOR',
            'ALMA_CART_WIDGET_POSITION_CUSTOM',
            'ALMA_CART_WDGT_NOT_ELGBL',
            'ALMA_PRODUCT_WDGT_NOT_ELGBL',
            'ALMA_CATEGORIES_WDGT_NOT_ELGBL',
            ConstantsHelper::ALMA_ALLOW_INSURANCE,
            ConstantsHelper::ALMA_ACTIVATE_INSURANCE,
            ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_PRODUCT,
            ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_CART,
            ConstantsHelper::ALMA_SHOW_INSURANCE_POPUP_CART,
            CmsDataHelper::ALMA_CMSDATA_DATE,
        ];

        foreach ($configKeys as $configKey) {
            \Configuration::deleteByName($configKey);
        }

        return true;
    }

    /**
     * @return bool
     */
    public static function isFullyConfigured()
    {
        return (bool) (int) static::get(self::ALMA_FULLY_CONFIGURED, false);
    }

    /**
     * @return bool
     */
    public static function canLog()
    {
        return (bool) (int) static::get('ALMA_ACTIVATE_LOGGING', false);
    }

    /**
     * Get value ALMA_SHARE_OF_CHECKOUT_STATE.
     *
     * @return string
     */
    public static function getShareOfCheckoutStatus()
    {
        $default = ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_CONSENT_UNSET;

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $default = ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_CONSENT_NO;
        }

        return static::get(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_STATE, $default);
    }

    /**
     * Get true if the consent SoC isn't answered.
     *
     * @return bool
     */
    public static function isShareOfCheckoutNoAnswered()
    {
        return ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_CONSENT_UNSET == \Configuration::get(
            ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_STATE
        );
    }

    /**
     * @deprecated use isShareOfCheckoutNoAnswered in ShareOfCheckoutHelper
     * Get true if the consent SoC isn't answered
     *
     * @return bool
     */
    public static function isShareOfCheckoutAnswered()
    {
        $state = \Configuration::get(
            ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_STATE,
            null,
            null,
            null,
            null
        );

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            if (false !== $state) {
                return true;
            }

            return $state;
        }

        return isset($state);
    }

    /**
     * Get true if the settings SoC isn't saved.
     *
     * @return bool
     */
    public static function isShareOfCheckoutSetting()
    {
        return !(false === \Configuration::get(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_STATE));
    }

    /**
     * @deprecated
     * Get true if need to hide SoC form
     *
     * @return bool
     */
    public static function shouldHideShareOfCheckoutForm()
    {
        return (SettingsHelper::isShareOfCheckoutNoAnswered() && ALMA_MODE_LIVE === SettingsHelper::getActiveMode())
                || (!SettingsHelper::isShareOfCheckoutSetting() && ALMA_MODE_LIVE === SettingsHelper::getActiveMode())
                || ALMA_MODE_LIVE !== SettingsHelper::getActiveMode();
    }

    /**
     * Return true if we need to display the SoC form.
     *
     * @return bool
     */
    public function shouldDisplayShareOfCheckoutForm()
    {
        return !static::shouldHideShareOfCheckoutForm();
    }

    /**
     * Get the date of consent SoC in Prestashop database.
     *
     * @return int|null
     */
    public static function getTimeConsentShareOfCheckout()
    {
        return static::get(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_DATE, '');
    }

    /**
     * Get current timestamp.
     *
     * @return int
     */
    public static function getCurrentTimestamp()
    {
        $date = new \DateTime();

        return $date->getTimestamp();
    }

    /**
     * Get API mode saved in Prestashop database.
     *
     * @deprecated use getModeActive
     *
     * @return string
     */
    public static function getActiveMode()
    {
        return static::get('ALMA_API_MODE', ALMA_MODE_TEST);
    }

    /**
     * Get API mode saved in Prestashop database.
     *
     * @return string
     */
    public function getModeActive()
    {
        return $this->getKey('ALMA_API_MODE', ALMA_MODE_TEST);
    }

    /**
     * Get decrypted API key of selected mode or empty string
     *
     * @return string
     */
    public static function getActiveAPIKey()
    {
        if (ALMA_MODE_LIVE == static::getActiveMode()) {
            return static::getLiveKey();
        }

        return static::getTestKey();
    }

    /**
     * Get decrypted API key Live or empty string
     *
     * @return string
     */
    public static function getLiveKey()
    {
        $apiKey = static::get(ApiAdminFormBuilder::ALMA_LIVE_API_KEY, null);

        if (!$apiKey) {
            return '';
        }
        // Check if the key is already decrypted
        if (false !== strpos($apiKey, ConstantsHelper::BEGIN_LIVE_API_KEY)) {
            return $apiKey;
        }

        $encryption = new EncryptionHelper();

        try {
            return $encryption->decrypt($apiKey);
        } catch (EncryptionException $e) {
            return '';
        }
    }

    /**
     * Get decrypted API key Test or empty string
     *
     * @return string
     */
    public static function getTestKey()
    {
        $apiKey = static::get(ApiAdminFormBuilder::ALMA_TEST_API_KEY, null);
        if (!$apiKey) {
            return '';
        }
        // Check if the key is already decrypted
        if (false !== strpos($apiKey, ConstantsHelper::BEGIN_TEST_API_KEY)) {
            return $apiKey;
        }

        $encryption = new EncryptionHelper();

        try {
            return $encryption->decrypt($apiKey);
        } catch (EncryptionException $e) {
            return '';
        }
    }

    /**
     * Get value ALMA_SHOW_DISABLED_BUTTON.
     *
     * @return bool
     */
    public static function showDisabledButton()
    {
        return (bool) (int) static::get('ALMA_SHOW_DISABLED_BUTTON', true);
    }

    /**
     * Get value ALMA_SHOW_ELIGIBILITY_MESSAGE.
     *
     * @return bool
     */
    public static function showEligibilityMessage()
    {
        return (bool) (int) static::get('ALMA_SHOW_ELIGIBILITY_MESSAGE', true);
    }

    /**
     * Get value ALMA_CART_WDGT_NOT_ELGBL.
     *
     * @return bool
     */
    public static function showCartWidgetIfNotEligible()
    {
        return (bool) (int) static::get('ALMA_CART_WDGT_NOT_ELGBL', true);
    }

    /**
     * Get value ALMA_PRODUCT_WDGT_NOT_ELGBL.
     *
     * @return bool
     */
    public static function showProductWidgetIfNotEligible()
    {
        return (bool) (int) static::get('ALMA_PRODUCT_WDGT_NOT_ELGBL', true);
    }

    /**
     * Get value ALMA_CATEGORIES_WDGT_NOT_ELGBL.
     *
     * @return bool
     */
    public static function showCategoriesWidgetIfNotEligible()
    {
        return (bool) (int) static::get('ALMA_CATEGORIES_WDGT_NOT_ELGBL', true);
    }

    /**
     * Get key description payment trigger.
     *
     * @return string
     */
    public static function getKeyDescriptionPaymentTrigger()
    {
        return static::get(
            'ALMA_DESCRIPTION_TRIGGER',
            PaymentOnTriggeringAdminFormBuilder::ALMA_DESCRIPTION_TRIGGER_AT_SHIPPING
        );
    }

    /**
     * @return bool
     */
    public function isInPageEnabled()
    {
        return (bool) (int) $this->getKey(InpageAdminFormBuilder::ALMA_ACTIVATE_INPAGE, InpageAdminFormBuilder::ALMA_INPAGE_DEFAULT_VALUE);
    }

    /**
     * @return array
     */
    public function getInPageSettings()
    {
        return [
            'enabled' => $this->isInPageEnabled(),
            'paymentButtonSelector' => $this->getKey(InpageAdminFormBuilder::ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR, InpageAdminFormBuilder::ALMA_INPAGE_DEFAULT_VALUE_PAYMENT_BUTTON_SELECTOR),
            'placeOrderButtonSelector' => $this->getKey(InpageAdminFormBuilder::ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR, InpageAdminFormBuilder::ALMA_INPAGE_DEFAULT_VALUE_PLACE_ORDER_BUTTON_SELECTOR),
        ];
    }

    /**
     * Return activated plans.
     *
     * @return array
     */
    public function activePlans()
    {
        $plans = [];
        $feePlans = json_decode($this->getAlmaFeePlans());

        foreach ($feePlans as $key => $feePlan) {
            if (1 == $feePlan->enabled) {
                $dataFromKey = $this->getDataFromKey($key);
                $plans[] = [
                    'installmentsCount' => (int) $dataFromKey['installmentsCount'],
                    'minAmount' => $feePlan->min,
                    'maxAmount' => $feePlan->max,
                    'deferredDays' => (int) $dataFromKey['deferredDays'],
                    'deferredMonths' => (int) $dataFromKey['deferredMonths'],
                ];
            }
        }

        return $plans;
    }

    /**
     * @return int
     */
    public static function getRefundState()
    {
        return (int) static::get('ALMA_STATE_REFUND', 7);
    }

    /**
     * @return bool
     */
    public static function isRefundEnabledByState()
    {
        return (bool) static::get('ALMA_STATE_REFUND_ENABLED', 0);
    }

    /**
     * @return int
     */
    public static function getPaymentTriggerState()
    {
        return (int) static::get('ALMA_STATE_TRIGGER', 4);
    }

    /**
     * @return int
     */
    public static function getPaymentError()
    {
        return (int) static::get('ALMA_PAYMENT_ERROR', 8);
    }

    /**
     * @return bool
     */
    public function isPaymentTriggerEnabledByState()
    {
        return (bool) $this->getKey('ALMA_PAYMENT_ON_TRIGGERING_ENABLED', 0);
    }

    /**
     * @return array
     */
    public function getExcludedCategories()
    {
        $categories = $this->getKey('ALMA_EXCLUDED_CATEGORIES');

        if (
            null !== $categories
            && 'null' !== $categories
        ) {
            return (array) json_decode($categories);
        }

        return [];
    }

    /**
     * @return array
     */
    public function getExcludedCategoryNames()
    {
        $categories = $this->getExcludedCategories();

        if (!$categories) {
            return [];
        }

        $categories = \Category::getCategories(
            false,
            false,
            false,
            sprintf('AND c.`id_category` IN (%s)', implode(',', $categories))
        );

        $categoriesName = [];

        foreach ($categories as $category) {
            $categoriesName[] = $category['name'];
        }

        return $categoriesName;
    }

    /**
     * @param int $idCategory
     *
     * @return void
     */
    public function addExcludedCategories($idCategory)
    {
        $excludedCategories = $this->getExcludedCategories();

        $category = CategoryHelper::fromCategory($idCategory);

        if (!$category) {
            return;
        }

        // Add the selected category and all its children categories
        $categoriesToExclude = array_merge([$idCategory], $category->getAllChildrenIds());
        $excludedCategories = array_merge($excludedCategories, array_diff($categoriesToExclude, $excludedCategories));

        self::updateExcludedCategories($excludedCategories);
    }

    /**
     * @param int $idCategory
     *
     * @return void
     */
    public function removeExcludedCategories($idCategory)
    {
        $excludedCategories = $this->getExcludedCategories();

        $category = CategoryHelper::fromCategory($idCategory);

        if (!$category) {
            return;
        }

        // Remove the selected categories and all its children categories
        $categoriesToRemove = array_merge([$idCategory], $category->getAllChildrenIds());
        $excludedCategories = array_diff($excludedCategories, $categoriesToRemove);

        self::updateExcludedCategories($excludedCategories);
    }

    /**
     * Update ALMA_EXCLUDED_CATEGORIES value.
     *
     * @param array $categories
     *
     * @return void
     */
    private static function updateExcludedCategories($categories)
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $categories = \Tools::jsonEncode($categories);
        } else {
            $categories = json_encode($categories);
        }

        static::updateValue('ALMA_EXCLUDED_CATEGORIES', $categories);
    }

    /**
     * @param int $productId The product ID to check for exclusion
     *
     * @return bool Whether this product belongs to an excluded category
     */
    public function isProductExcluded($productId)
    {
        $excludedCategories = [];

        foreach ($this->getExcludedCategories() as $categoryId) {
            $excludedCategories[] = ['id_category' => (int) $categoryId];
        }

        return \Product::idIsOnCategoryId($productId, $excludedCategories);
    }

    /**
     * @return bool
     */
    public static function showProductEligibility()
    {
        return (bool) static::get('ALMA_SHOW_PRODUCT_ELIGIBILITY', 1);
    }

    /**
     * @return false|mixed|string|null
     */
    public static function getProductPriceQuerySelector()
    {
        return static::get('ALMA_PRODUCT_PRICE_SELECTOR', '[itemprop=price],#our_price_display,.modal-body .current-price-value');
    }

    /**
     * @return false|mixed|string|null
     */
    public static function getProductWidgetPositionQuerySelector()
    {
        return static::get('ALMA_WIDGET_POSITION_SELECTOR', '');
    }

    /**
     * @return bool
     */
    public static function isWidgetCustomPosition()
    {
        return (bool) (int) static::get('ALMA_WIDGET_POSITION_CUSTOM', false);
    }

    /**
     * @return false|mixed|string|null
     */
    public static function getCartWidgetPositionQuerySelector()
    {
        return static::get('ALMA_CART_WDGT_POS_SELECTOR', '');
    }

    /**
     * @return bool
     */
    public static function isCartWidgetCustomPosition()
    {
        return (bool) (int) static::get('ALMA_CART_WIDGET_POSITION_CUSTOM', false);
    }

    /**
     * @return false|mixed|string|null
     */
    public static function getProductAttrQuerySelector()
    {
        return static::get('ALMA_PRODUCT_ATTR_SELECTOR', '#buy_block .attribute_select');
    }

    /**
     * @return false|mixed|string|null
     */
    public static function getProductAttrRadioQuerySelector()
    {
        return static::get('ALMA_PRODUCT_ATTR_RADIO_SELECTOR', '#buy_block .attribute_radio');
    }

    /**
     * @return false|mixed|string|null
     */
    public static function getProductColorPickQuerySelector()
    {
        return static::get('ALMA_PRODUCT_COLOR_PICK_SELECTOR', '#buy_block .color_pick');
    }

    /**
     * @return false|mixed|string|null
     */
    public static function getProductQuantityQuerySelector()
    {
        $default = '#quantity_wanted';

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $default = '#buy_block #quantity_wanted';
        }

        return static::get('ALMA_PRODUCT_QUANTITY_SELECTOR', $default);
    }

    /**
     * @return false|mixed|string|null
     *
     * @deprecated use getIdMerchant
     */
    public static function getMerchantId()
    {
        return static::get('ALMA_MERCHANT_ID');
    }

    /**
     * @return false|mixed|string|null
     */
    public function getIdMerchant()
    {
        return $this->getKey('ALMA_MERCHANT_ID');
    }

    /**
     * @param \Alma\API\Entities\FeePlan $plan
     *
     * @return string
     */
    public function keyForFeePlan($plan)
    {
        return $this->key(
            $plan->kind,
            (int) $plan->installments_count,
            (int) $plan->deferred_days,
            (int) $plan->deferred_months
        );
    }

    /**
     * @deprecated use static function keyForInstallmentPlanStatic
     *
     * @param Eligibility $plan
     *
     * @return string
     */
    public function keyForInstallmentPlan($plan)
    {
        return $this->key(
            'general',
            (int) $plan->installmentsCount,
            (int) $plan->deferredDays,
            (int) $plan->deferredMonths
        );
    }

    /**
     * Get plan key from Eligibility
     *
     * @param Eligibility $plan
     *
     * @return string
     */
    public static function planKeyFromEligibilityPlan($plan)
    {
        return implode('_', ['general', (int) $plan->installmentsCount, (int) $plan->deferredDays, (int) $plan->deferredMonths]);
    }

    /**
     * Get plan key from Payment
     *
     * @param Payment $payment
     *
     * @return string
     */
    public static function planKeyFromPayment($payment)
    {
        return implode('_', ['general', (int) $payment->installments_count, (int) $payment->deferred_days, (int) $payment->deferred_months]);
    }

    /**
     * @deprecated use keyForInstallmentPlanStatic or planKeyFromPayment instead
     *
     * @param string $planKind
     * @param int $installmentsCount
     * @param int $deferredDays
     * @param int $deferredMonths
     *
     * @return string
     */
    public function key(
        $planKind,
        $installmentsCount,
        $deferredDays,
        $deferredMonths
    ) {
        return implode('_', [$planKind, $installmentsCount, $deferredDays, $deferredMonths]);
    }

    /**
     * @return false|mixed|string|null
     *
     * @deprecated use getAlmaFeePlans
     */
    public static function getFeePlans()
    {
        return static::get('ALMA_FEE_PLANS');
    }

    /**
     * @return false|mixed|string|null
     */
    public function getAlmaFeePlans()
    {
        return $this->getKey('ALMA_FEE_PLANS');
    }

    /**
     * @param \Alma\API\Entities\FeePlan $plan
     *
     * @deprecated use PlanHelper->isDeferred
     *
     * @return bool
     */
    public function isDeferred($plan)
    {
        if (isset($plan->deferred_days)) {
            return 0 < $plan->deferred_days || 0 < $plan->deferred_months;
        }

        return 0 < $plan->deferredDays || 0 < $plan->deferredMonths;
    }

    /**
     * Check if is deferred trigger by value in fee plans and enabled in config.
     *
     * @param object|array $feePlans
     * @param string|null $key
     *
     * @return bool
     */
    public function isDeferredTriggerLimitDays($feePlans, $key = null)
    {
        if (!empty($key)) {
            $isDeferredTriggerLimitDay = !empty($feePlans->$key->deferred_trigger_limit_days);
        } else {
            $isDeferredTriggerLimitDay = !empty($feePlans['deferred_trigger_limit_days']);
        }

        return $isDeferredTriggerLimitDay && $this->isPaymentTriggerEnabledByState();
    }

    /**
     * @param \Alma\API\Entities\FeePlan $plan
     *
     * @return float|int
     */
    public function getDuration($plan)
    {
        if (isset($plan->deferred_days)) {
            return ($plan->deferred_months * 30) + $plan->deferred_days;
        }

        return ($plan->deferredMonths * 30) + $plan->deferredDays;
    }

    /**
     * Fee plan from key.
     *
     * @param string $key
     *
     * @return array feePlans
     */
    public function getDataFromKey($key)
    {
        $feePlans = json_decode($this->getAlmaFeePlans());
        preg_match("/general_(\d*)_(\d*)_(\d*)/", $key, $data);

        $dataFromKey = [
            'installmentsCount' => (int) $data[1],
            'deferredDays' => (int) $data[2],
            'deferredMonths' => (int) $data[3],
        ];

        if (isset($feePlans->$key->deferred_trigger_limit_days)) {
            $dataFromKey['deferred_trigger_limit_days'] = (int) $feePlans->$key->deferred_trigger_limit_days;
        }

        return $dataFromKey;
    }

    /**
     * @return array
     */
    public function getCategoriesExcludedNames()
    {
        $categories = $this->configurationHelper->get(self::ALMA_EXCLUDED_CATEGORIES);
        if (!$categories) {
            return [];
        }

        $categoriesNames = [];

        foreach (json_decode($categories) as $id) {
            try {
                $category = $this->categoryFactory->create($id, $this->contextFactory->getContextLanguageId());
            } catch (AlmaException $e) {
                $category = $this->categoryFactory->create($id);
            }
            if ($this->validateHelper->isLoadedObject($category) && $category->name !== null) {
                $categoriesNames[] = $category->name;
            }
        }

        return $categoriesNames;
    }
}
