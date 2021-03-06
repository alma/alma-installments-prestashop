<?php

/**
 * 2018-2021 Alma SAS
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
 * @copyright 2018-2021 Alma SAS
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

use Alma\PrestaShop\Model\CategoryAdapter;
use Category;
use Configuration;
use Product;
use Shop;
use Tools;
use Translate;

class Settings
{
    public static function l($str)
    {
        return Translate::getModuleTranslation('alma', $str, 'settings');
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
            'ALMA_API_MODE',
            'ALMA_MERCHANT_ID',
            'ALMA_LIVE_API_KEY',
            'ALMA_TEST_API_KEY',
            'ALMA_SHOW_DISABLED_BUTTON',
            'ALMA_SHOW_ELIGIBILITY_MESSAGE',
            'ALMA_IS_ELIGIBLE_MESSAGE',
            'ALMA_NOT_ELIGIBLE_MESSAGE',
            'ALMA_PAYMENT_BUTTON_TITLE',
            'ALMA_PAYMENT_BUTTON_DESC',
            'ALMA_P2X_ENABLED',
            'ALMA_P3X_ENABLED',
            'ALMA_P4X_ENABLED',
            'ALMA_P2X_MIN_AMOUNT',
            'ALMA_P3X_MIN_AMOUNT',
            'ALMA_P4X_MIN_AMOUNT',
            'ALMA_P2X_MAX_AMOUNT',
            'ALMA_P3X_MAX_AMOUNT',
            'ALMA_P4X_MAX_AMOUNT',
            'ALMA_PNX_MAX_N',
            'ALMA_STATE_REFUND',
            'ALMA_STATE_REFUND_ENABLED',
            'ALMA_DISPLAY_ORDER_CONFIRMATION',
            'ALMA_EXCLUDED_CATEGORIES',
            'ALMA_NOT_ELIGIBLE_CATEGORIES',
            'ALMA_SHOW_PRODUCT_ELIGIBILITY',
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

    public static function getEligibilityMessage()
    {
        // Allow PrestaShop's translation feature to detect those strings
        // $this->l('Your cart is eligible for monthly installments.', 'settings');
        $default = self::l('Your cart is eligible for monthly installments.');

        return self::get('ALMA_IS_ELIGIBLE_MESSAGE', $default);
    }

    public static function getNonEligibilityMessage()
    {
        // Allow PrestaShop's translation feature to detect those strings
        // $this->l('Your cart is not eligible for monthly installments.', 'settings');
        $default = self::l('Your cart is not eligible for monthly installments.');

        return self::get('ALMA_NOT_ELIGIBLE_MESSAGE', $default);
    }

    public static function getNonEligibleCategoriesMessage()
    {
        // Allow PrestaShop's translation feature to detect those strings
        // $this->l('Your cart is not eligible for monthly installments.', 'settings');
        $default = self::l('Your cart is not eligible for monthly installments.');

        return self::get('ALMA_NOT_ELIGIBLE_CATEGORIES', $default);
    }

    public static function showEligibilityMessage()
    {
        return (bool) (int) self::get('ALMA_SHOW_ELIGIBILITY_MESSAGE', true);
    }

    public static function getNonEligibilityMinAmountMessage($minimum)
    {
        // Allow PrestaShop's translation feature to detect those strings
        // $this->l('(Minimum amount: %s)', 'settings');
        $default = sprintf(
            self::l('(Minimum amount: %s)'),
            almaFormatPrice($minimum)
        );

        return $default;
    }

    public static function getNonEligibilityMaxAmountMessage($maximum)
    {
        // Allow PrestaShop's translation feature to detect those strings
        // $this->l('(Maximum amount: %s)', 'settings');
        $default = sprintf(
            self::l('(Maximum amount: %s)'),
            almaFormatPrice($maximum)
        );

        return $default;
    }

    public static function getPaymentButtonTitle()
    {
        // Allow PrestaShop's translation feature to detect those strings
        // $this->l('Pay in %d installments', 'settings');
        $default = self::l('Pay in %d installments');

        return self::get('ALMA_PAYMENT_BUTTON_TITLE', $default);
    }

    public static function getPaymentButtonDescription()
    {
        // Allow PrestaShop's translation feature to detect those strings
        // $this->l('Pay in %d monthly installments with your credit card.', 'settings');
        $default = self::l('Pay in %d monthly installments with your credit card.');

        return self::get('ALMA_PAYMENT_BUTTON_DESC', $default);
    }

    public static function displayOrderConfirmation()
    {
        // This option is mainly useful for pre-1.7 versions, where the default theme doesn't include a confirmation
        // page for third-party payment modules.
        $default = version_compare(_PS_VERSION_, '1.7', '<');

        return (bool) (int) self::get('ALMA_DISPLAY_ORDER_CONFIRMATION', $default);
    }

    public static function isInstallmentPlanEnabled($n, $merchant = null)
    {
        $default = ($n === 3);

        if ($merchant) {
            $plan = self::getMerchantFeePlan($merchant, $n);

            if ($plan && !$plan['allowed']) {
                return false;
            }
        }

        return (bool) (int) self::get("ALMA_P${n}X_ENABLED", $default);
    }

    public static function installmentPlansMaxN()
    {
        return (int) self::get('ALMA_PNX_MAX_N', 3);
    }

    private static function getMerchantFeePlan($merchant, $n)
    {
        foreach ($merchant->fee_plans as $plan) {
            if ($plan['installments_count'] === $n) {
                return $plan;
            }
        }

        return null;
    }

    public static function installmentPlanMinAmount($n, $merchant = null)
    {
        $default = 10000;

        if ($merchant) {
            $plan = self::getMerchantFeePlan($merchant, $n);

            if ($plan) {
                $default = $plan['min_purchase_amount'];
            }
        }

        return (int) self::get("ALMA_P${n}X_MIN_AMOUNT", $default);
    }

    public static function installmentPlanMaxAmount($n, $merchant = null)
    {
        $default = 100000;

        if ($merchant) {
            $plan = self::getMerchantFeePlan($merchant, $n);

            if ($plan) {
                $default = $plan['max_purchase_amount'];
            }
        }

        return (int) self::get("ALMA_P${n}X_MAX_AMOUNT", $default);
    }

    public static function installmentPlanSortOrder($n)
    {
        return (int) self::get("ALMA_P${n}X_SORT_ORDER", (int) $n);
    }

    public static function activePlans()
    {
        $plans = [];

        for ($n = 2; $n <= self::installmentPlansMaxN(); ++$n) {
            if (self::isInstallmentPlanEnabled($n)) {
                $plans[] = [
                    'installmentsCount' => $n,
                    'minAmount' => self::installmentPlanMinAmount($n),
                    'maxAmount' => self::installmentPlanMaxAmount($n),
                ];
            }
        }

        return $plans;
    }

    public static function activeInstallmentsCounts()
    {
        $installmentsCounts = [];

        foreach (self::activePlans() as $plan) {
            $installmentsCounts[] = $plan['installmentsCount'];
        }

        return $installmentsCounts;
    }

    public static function getRefundState()
    {
        return (int) self::get('ALMA_STATE_REFUND', 7);
    }

    public static function isRefundEnabledByState()
    {
        return (bool) self::get('ALMA_STATE_REFUND_ENABLED', 0);
    }

    public static function getExcludedCategories()
    {
        $categories = self::get('ALMA_EXCLUDED_CATEGORIES');
        if (null !== $categories && 'null' !== $categories) {
            return json_decode($categories);
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
        $default = '#buy_block #quantity_wanted';

        return self::get('ALMA_PRODUCT_QUANTITY_SELECTOR', $default);
    }

    public static function getMerchantId()
    {
        return self::get('ALMA_MERCHANT_ID');
    }
}
