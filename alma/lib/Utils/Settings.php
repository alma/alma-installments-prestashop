<?php
/**
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Utils;

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined('ALMA_MODE_TEST')) {
    define('ALMA_MODE_TEST', 'test');
}

if (!defined('ALMA_MODE_LIVE')) {
    define('ALMA_MODE_LIVE', 'live');
}

use Alma\PrestaShop\Forms\ExcludedCategoryAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentOnTriggeringAdminFormBuilder;
use Alma\PrestaShop\Forms\ShareOfCheckoutAdminFormBuilder;
use Alma\PrestaShop\Model\CategoryAdapter;
use Category;
use Configuration;
use Language;
use Product;
use Shop;
use Tools;
use Translate;

class Settings
{
    public static function l($str)
    {
        return Translate::getModuleTranslation('alma', $str, CustomFieldsHelper::SOURCE_CUSTOM_FIELDS);
    }

    public static function get($configKey, $default = null)
    {
        $idShop = Shop::getContextShopID(true);
        $idShopGroup = Shop::getContextShopGroupID(true);

        $value = Configuration::get($configKey, null, $idShopGroup, $idShop, $default);

        // Configuration::get in PrestaShop 1.5 doesn't have a default argument, so we handle it here
        if (!$value && !Configuration::hasKey($configKey, null, $idShopGroup, $idShop)) {
            $value = $default;
        }

        return $value;
    }

    public static function updateValue($configKey, $value)
    {
        $idShop = Shop::getContextShopID(true);
        $idShopGroup = Shop::getContextShopGroupID(true);
        Configuration::updateValue($configKey, $value, false, $idShopGroup, $idShop);
    }

    public static function deleteAllValues()
    {
        $configKeys = [
            'ALMA_FULLY_CONFIGURED',
            'ALMA_ACTIVATE_LOGGING',
            ShareOfCheckoutAdminFormBuilder::ALMA_ACTIVATE_SHARE_OF_CHECKOUT,
            'ALMA_API_MODE',
            'ALMA_MERCHANT_ID',
            'ALMA_LIVE_API_KEY',
            'ALMA_TEST_API_KEY',
            'ALMA_SHOW_DISABLED_BUTTON',
            'ALMA_SHOW_ELIGIBILITY_MESSAGE',
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE,
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC,
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE,
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC,
            PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE,
            PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC,
            ExcludedCategoryAdminFormBuilder::ALMA_NOT_ELIGIBLE_CATEGORIES,
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
        ];

        foreach ($configKeys as $configKey) {
            Configuration::deleteByName($configKey);
        }

        return true;
    }

    /* Getters */
    public static function isFullyConfigured()
    {
        return (bool) (int) self::get('ALMA_FULLY_CONFIGURED', false);
    }

    public static function canLog()
    {
        return (bool) (int) self::get('ALMA_ACTIVATE_LOGGING', false);
    }

    public static function canShareOfCheckout()
    {
        return (bool) (int) self::get(ShareOfCheckoutAdminFormBuilder::ALMA_ACTIVATE_SHARE_OF_CHECKOUT, false);
    }

    public static function getActiveMode()
    {
        return self::get('ALMA_API_MODE', ALMA_MODE_TEST);
    }

    public static function getActiveAPIKey()
    {
        if (self::getActiveMode() == ALMA_MODE_LIVE) {
            return self::get('ALMA_LIVE_API_KEY');
        } else {
            return self::get('ALMA_TEST_API_KEY');
        }
    }

    public static function getLiveKey()
    {
        return self::get('ALMA_LIVE_API_KEY', '');
    }

    public static function getTestKey()
    {
        return self::get('ALMA_TEST_API_KEY', '');
    }

    public static function showDisabledButton()
    {
        return (bool) (int) self::get('ALMA_SHOW_DISABLED_BUTTON', true);
    }

    public static function showEligibilityMessage()
    {
        return (bool) (int) self::get('ALMA_SHOW_ELIGIBILITY_MESSAGE', true);
    }

    public static function showCartWidgetIfNotEligible()
    {
        return (bool) (int) self::get('ALMA_CART_WDGT_NOT_ELGBL', true);
    }

    public static function showProductWidgetIfNotEligible()
    {
        return (bool) (int) self::get('ALMA_PRODUCT_WDGT_NOT_ELGBL', true);
    }

    public static function showCategoriesWidgetIfNotEligible()
    {
        return (bool) (int) self::get('ALMA_CATEGORIES_WDGT_NOT_ELGBL', true);
    }

    /**
     * Get key description payment trigger
     *
     * @return string
     */
    public static function getKeyDescriptionPaymentTrigger()
    {
        return self::get('ALMA_DESCRIPTION_TRIGGER', PaymentOnTriggeringAdminFormBuilder::ALMA_DESCRIPTION_TRIGGER_AT_SHIPPING);
    }

    public static function activePlans()
    {
        $plans = [];
        $feePlans = json_decode(self::getFeePlans());

        foreach ($feePlans as $key => $feePlan) {
            if (1 == $feePlan->enabled) {
                $dataFromKey = self::getDataFromKey($key);
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
     * Get locale by id_lang with condition NL for widget (provisional)
     *
     * @param int $idLang
     *
     * @return string
     */
    public static function localeByIdLangForWidget($idLang)
    {
        $locale = Language::getIsoById($idLang);

        if ($locale == 'nl') {
            $locale = 'nl-NL';
        }

        return $locale;
    }

    public static function getRefundState()
    {
        return (int) self::get('ALMA_STATE_REFUND', 7);
    }

    public static function isRefundEnabledByState()
    {
        return (bool) self::get('ALMA_STATE_REFUND_ENABLED', 0);
    }

    public static function getPaymentTriggerState()
    {
        return (int) self::get('ALMA_STATE_TRIGGER', 4);
    }

    public static function isPaymentTriggerEnabledByState()
    {
        return (bool) self::get('ALMA_PAYMENT_ON_TRIGGERING_ENABLED', 0);
    }

    public static function getExcludedCategories()
    {
        $categories = self::get('ALMA_EXCLUDED_CATEGORIES');
        if (null !== $categories && 'null' !== $categories) {
            return (array) json_decode($categories);
        }

        return [];
    }

    public static function getExcludedCategoryNames()
    {
        $categories = self::getExcludedCategories();
        if (!$categories) {
            return [];
        }

        $categories = Category::getCategories(
            false,
            false,
            false,
            sprintf('AND c.`id_category` IN (%s)', implode(',', $categories))
        );
        $categoriesName = [];
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $categoriesName[] = $category['name'];
            }
        }

        return $categoriesName;
    }

    public static function addExcludedCategories($idCategory)
    {
        $excludedCategories = self::getExcludedCategories();

        $category = CategoryAdapter::fromCategory($idCategory);
        if (!$category) {
            return;
        }

        // Add the selected category and all its children categories
        $categoriesToExclude = array_merge([$idCategory], $category->getAllChildrenIds());
        $excludedCategories = array_merge($excludedCategories, array_diff($categoriesToExclude, $excludedCategories));

        self::updateExcludedCategories($excludedCategories);
    }

    public static function removeExcludedCategories($idCategory)
    {
        $excludedCategories = self::getExcludedCategories();

        $category = CategoryAdapter::fromCategory($idCategory);
        if (!$category) {
            return;
        }

        // Remove the selected categories and all its children categories
        $categoriesToRemove = array_merge([$idCategory], $category->getAllChildrenIds());
        $excludedCategories = array_diff($excludedCategories, $categoriesToRemove);

        self::updateExcludedCategories($excludedCategories);
    }

    /**
     * Update ALMA_EXCLUDED_CATEGORIES value
     *
     * @param array $categories
     *
     * @return void
     */
    private static function updateExcludedCategories($categories)
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            self::updateValue('ALMA_EXCLUDED_CATEGORIES', Tools::jsonEncode($categories));
        } else {
            self::updateValue('ALMA_EXCLUDED_CATEGORIES', json_encode($categories));
        }
    }

    /**
     * @param $productId int The product ID to check for exclusion
     *
     * @return bool Whether this product belongs to an excluded category
     */
    public static function isProductExcluded($productId)
    {
        $excludedCategories = [];

        foreach (self::getExcludedCategories() as $categoryId) {
            $excludedCategories[] = ['id_category' => (int) $categoryId];
        }

        return Product::idIsOnCategoryId($productId, $excludedCategories);
    }

    public static function showProductEligibility()
    {
        return (bool) self::get('ALMA_SHOW_PRODUCT_ELIGIBILITY', 1);
    }

    public static function getProductPriceQuerySelector()
    {
        $default = '[itemprop=price],#our_price_display';

        return self::get('ALMA_PRODUCT_PRICE_SELECTOR', $default);
    }

    public static function getProductWidgetPositionQuerySelector()
    {
        $default = '';

        return self::get('ALMA_WIDGET_POSITION_SELECTOR', $default);
    }

    public static function isWidgetCustomPosition()
    {
        return (bool) (int) self::get('ALMA_WIDGET_POSITION_CUSTOM', false);
    }

    public static function getCartWidgetPositionQuerySelector()
    {
        $default = '';

        return self::get('ALMA_CART_WDGT_POS_SELECTOR', $default);
    }

    public static function isCartWidgetCustomPosition()
    {
        return (bool) (int) self::get('ALMA_CART_WIDGET_POSITION_CUSTOM', false);
    }

    public static function getProductAttrQuerySelector()
    {
        $default = '#buy_block .attribute_select';

        return self::get('ALMA_PRODUCT_ATTR_SELECTOR', $default);
    }

    public static function getProductAttrRadioQuerySelector()
    {
        $default = '#buy_block .attribute_radio';

        return self::get('ALMA_PRODUCT_ATTR_RADIO_SELECTOR', $default);
    }

    public static function getProductColorPickQuerySelector()
    {
        $default = '#buy_block .color_pick';

        return self::get('ALMA_PRODUCT_COLOR_PICK_SELECTOR', $default);
    }

    public static function getProductQuantityQuerySelector()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $default = '#buy_block #quantity_wanted';
        } else {
            $default = '#quantity_wanted';
        }

        return self::get('ALMA_PRODUCT_QUANTITY_SELECTOR', $default);
    }

    public static function getMerchantId()
    {
        return self::get('ALMA_MERCHANT_ID');
    }

    public static function keyForFeePlan($plan)
    {
        return self::key(
            $plan->kind,
            intval($plan->installments_count),
            intval($plan->deferred_days),
            intval($plan->deferred_months)
        );
    }

    private static function key(
        $planKind,
        $installmentsCount,
        $deferredDays,
        $deferredMonths
    ) {
        return implode('_', [$planKind, $installmentsCount, $deferredDays, $deferredMonths]);
    }

    public static function getFeePlans()
    {
        return self::get('ALMA_FEE_PLANS');
    }

    public static function isDeferred($plan)
    {
        if (isset($plan->deferred_days)) {
            return 0 < $plan->deferred_days || 0 < $plan->deferred_months;
        } else {
            return 0 < $plan->deferredDays || 0 < $plan->deferredMonths;
        }
    }

    /**
     * Check if is deferred trigger by value in feeplans and enabled in config
     *
     * @param object $feePlans
     * @param string|null $key
     *
     * @return bool
     */
    public static function isDeferredTriggerLimitDays($feePlans, $key = null)
    {
        if (!empty($key)) {
            $isDeferredTriggerLimitDay = !empty($feePlans->$key->deferred_trigger_limit_days);
        } else {
            $isDeferredTriggerLimitDay = !empty($feePlans['deferred_trigger_limit_days']);
        }

        return $isDeferredTriggerLimitDay && Settings::isPaymentTriggerEnabledByState();
    }

    public static function getDuration($plan)
    {
        if (isset($plan->deferred_days)) {
            return $plan->deferred_months * 30 + $plan->deferred_days;
        } else {
            return $plan->deferredMonths * 30 + $plan->deferredDays;
        }
    }

    /**
     * Fee plan from key
     *
     * @param string $key
     *
     * @return array feePlans
     */
    public static function getDataFromKey($key)
    {
        $feePlans = json_decode(self::getFeePlans());
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
}
