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
use Context;
use Language;
use Module;
use Product;
use Shop;
use Tools;
use Translate;

class Settings
{
    public static function l($str, $locale = null)
    {
        // if length is over 2 string then use ps 1.7 function
        if (strlen($locale) > 2) {
            return Translate::getModuleTranslation('alma', $str, 'settings', null, false, $locale);
        }

        return self::getModuleTranslation('alma', $str, 'settings', null, false, $locale);
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
            'ALMA_PAYMENT_BUTTON_TITLE',
            'ALMA_PAYMENT_BUTTON_DESC',
            'ALMA_DEFERRED_BUTTON_TITLE',
            'ALMA_DEFERRED_BUTTON_DESC',
            'ALMA_STATE_REFUND',
            'ALMA_STATE_REFUND_ENABLED',
            'ALMA_DISPLAY_ORDER_CONFIRMATION',
            'ALMA_EXCLUDED_CATEGORIES',
            'ALMA_NOT_ELIGIBLE_CATEGORIES',
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

    public static function getNonEligibleCategoriesMessage($idlang = null)
    {
        return self::getCustomFieldsByKeyConfig('ALMA_NOT_ELIGIBLE_CATEGORIES', $idlang);
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

    public static function customFields()
    {
        return [
            'ALMA_PAYMENT_BUTTON_TITLE' => 'Pay in %d installments',
            'ALMA_PAYMENT_BUTTON_DESC' => 'Pay in %d monthly installments with your credit card.',
            'ALMA_DEFERRED_BUTTON_TITLE' => 'Buy now Pay in %d days',
            'ALMA_DEFERRED_BUTTON_DESC' => 'Buy now pay in %d days with your credit card.',
            'ALMA_NOT_ELIGIBLE_CATEGORIES' => 'Your cart is not eligible for payments with Alma.'
        ];
    }

    public static function getCustomFieldsByIso($iso)
    {
        return self::getModuleTranslations('alma', self::customFields(), 'settings', $iso);
    }

    public static function getCustomFields()
    {
        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            $return[$language['id_lang']] = self::getCustomFieldsByIso($language['iso_code']);
        }

        return $return;
    }

    public static function getDefaultCustomFieldsByKeyConfig($keyConfig, $idlang = null)
    {
        $customFields = self::getCustomFields();
        foreach ($customFields as $keyIdLang => $fields) {
            $return[$keyIdLang] = [
                'locale' => Language::getIsoById($keyIdLang),
                'string' => $fields[$keyConfig],
            ];
        }

        if ($idlang) {
            return $return[$idlang];
        }

        return $return;
    }

    public static function getCustomFieldsByKeyConfig($keyConfig, $idlang = null)
    {
        $field = self::getDefaultCustomFieldsByKeyConfig($keyConfig, $idlang);

        $datasConfig = json_decode(self::get($keyConfig, json_encode($field)), true);
        foreach ($datasConfig as $key => $data) {
            $return[$key] = $data['string'];
        }

        if ($idlang) {
            return $return[$idlang];
        }
    
        return $return;
    }

    public static function getPaymentButtonTitle($idlang = null)
    {
        return self::getCustomFieldsByKeyConfig('ALMA_PAYMENT_BUTTON_TITLE', $idlang);
    }

    public static function getPaymentButtonDescription($idlang = null)
    {
        return self::getCustomFieldsByKeyConfig('ALMA_PAYMENT_BUTTON_DESC', $idlang);
    }

    public static function getPaymentButtonTitleDeferred($idlang = null)
    {
        return self::getCustomFieldsByKeyConfig('ALMA_DEFERRED_BUTTON_TITLE', $idlang);
    }

    public static function getPaymentButtonDescriptionDeferred($idlang = null)
    {
        return self::getCustomFieldsByKeyConfig('ALMA_DEFERRED_BUTTON_DESC', $idlang);
    }

    public static function displayOrderConfirmation()
    {
        // This option is mainly useful for pre-1.7 versions, where the default theme doesn't include a confirmation
        // page for third-party payment modules.
        $default = version_compare(_PS_VERSION_, '1.7', '<');

        return (bool) (int) self::get('ALMA_DISPLAY_ORDER_CONFIRMATION', $default);
    }

    public static function activePlans($onlyPnx = false)
    {
        $plans = [];
        $feePlans = json_decode(self::getFeePlans());

        foreach ($feePlans as $key => $feePlan) {
            if (1 == $feePlan->enabled) {
                $dataFromKey = self::getDataFromKey($key);
                if ($onlyPnx && $dataFromKey['deferredMonths'] === 0 && $dataFromKey['deferredDays'] === 0) {
                    $plans[] = [
                        'installmentsCount' => (int) $dataFromKey['installmentsCount'],
                        'minAmount' => $feePlan->min,
                        'maxAmount' => $feePlan->max,
                    ];
                }
            }
        }

        return $plans;
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

    public static function getDuration($plan)
    {
        if (isset($plan->deferred_days)) {
            return $plan->deferred_months * 30 + $plan->deferred_days;
        } else {
            return $plan->deferredMonths * 30 + $plan->deferredDays;
        }
    }

    public static function getDataFromKey($key)
    {
        preg_match("/general_(\d*)_(\d*)_(\d*)/", $key, $data);

        return [
            'installmentsCount' => (int) $data[1],
            'deferredDays' => (int) $data[2],
            'deferredMonths' => (int) $data[3],
        ];
    }

    public static function getTranslationsByLanguage($str, $nameConfig, $idlang = null)
    {
        // Allow PrestaShop's translation feature to detect those strings
        // $this->l($str, 'settings');
        $default = [];
        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            $locale = $language['iso_code'];
            if (array_key_exists('locale', $language)) {
                $locale = $language['locale'];
            }
            $default[$language['id_lang']] = [
                'locale' => $locale,
                'string' => self::l($str, $locale)
            ];
        }
        if ($idlang) {
            return $default[$idlang]['string'];
        }

        $datasConfig = json_decode(self::get($nameConfig, json_encode($default)), true);
        foreach ($datasConfig as $key => $data) {
            $return[$key] = $data['string'];
        }

        return $return;
    }

    public static function getModuleTranslations(
        $module,
        $arrayString,
        $source,
        $locale,
        $js = false,
        $escape = true
    ) {
        global $_MODULES, $_MODULE, $_LANGADM;

        static $langCache = [];
        // $_MODULES is a cache of translations for all module.
        // $translations_merged is a cache of wether a specific module's translations have already been added to $_MODULES
        static $translationsMerged = [];

        $name = $module instanceof Module ? $module->name : $module;

        $iso = $locale;

        if (!isset($translationsMerged[$name][$iso])) {
            $filesByPriority = [
                // PrestaShop 1.5 translations
                _PS_MODULE_DIR_ . $name . '/translations/' . $iso . '.php',
                // PrestaShop 1.4 translations
                _PS_MODULE_DIR_ . $name . '/' . $iso . '.php',
                // Translations in theme
                _PS_THEME_DIR_ . 'modules/' . $name . '/translations/' . $iso . '.php',
                _PS_THEME_DIR_ . 'modules/' . $name . '/' . $iso . '.php',
            ];
            foreach ($filesByPriority as $file) {
                if (file_exists($file)) {
                    include_once $file;
                    $_MODULES = !empty($_MODULES) ? array_merge($_MODULES, $_MODULE) : $_MODULE;
                }
            }
            $translationsMerged[$name][$iso] = true;
        }

        foreach ($arrayString as $keyConfig => $string) {
            $string = preg_replace("/\\\*'/", "\'", $string);
            $key = md5($string);

            $cacheKey = $name . '|' . $string . '|' . $source . '|' . (int) $js . '|' . $iso;

            if (isset($langCache[$cacheKey])) {
                $ret = $langCache[$cacheKey];
            } else {
                $currentKey = strtolower('<{' . $name . '}' . _THEME_NAME_ . '>' . $source) . '_' . $key;
                $defaultKey = strtolower('<{' . $name . '}prestashop>' . $source) . '_' . $key;
    
                if (!empty($_MODULES[$currentKey])) {
                    $ret = stripslashes($_MODULES[$currentKey]);
                } elseif (!empty($_MODULES[$defaultKey])) {
                    $ret = stripslashes($_MODULES[$defaultKey]);
                } elseif (!empty($_LANGADM)) {
                    // if translation was not found in module, look for it in AdminController or Helpers
                    $ret = stripslashes(Translate::getGenericAdminTranslation($string, $key, $_LANGADM));
                } else {
                    $ret = stripslashes($string);
                }
    
                if ($js) {
                    $ret = addslashes($ret);
                } elseif ($escape) {
                    $ret = htmlspecialchars($ret, ENT_COMPAT, 'UTF-8');
                }
    
                $langCache[$cacheKey] = $ret;
            }
            $rets[$keyConfig] = $langCache[$cacheKey];
        }

        return $rets;
    }

    public static function getModuleTranslation(
        $module,
        $originalString,
        $source,
        $sprintf = null,
        $js = false,
        $locale = null,
        $fallback = true,
        $escape = true
    ) {
        global $_MODULES, $_MODULE, $_LANGADM;

        static $langCache = [];
        // $_MODULES is a cache of translations for all module.
        // $translations_merged is a cache of wether a specific module's translations have already been added to $_MODULES
        static $translationsMerged = [];

        $name = $module instanceof Module ? $module->name : $module;

        $iso = $locale;

        if (!isset($translationsMerged[$name][$iso])) {
            $filesByPriority = [
                // PrestaShop 1.5 translations
                _PS_MODULE_DIR_ . $name . '/translations/' . $iso . '.php',
                // PrestaShop 1.4 translations
                _PS_MODULE_DIR_ . $name . '/' . $iso . '.php',
                // Translations in theme
                _PS_THEME_DIR_ . 'modules/' . $name . '/translations/' . $iso . '.php',
                _PS_THEME_DIR_ . 'modules/' . $name . '/' . $iso . '.php',
            ];
            foreach ($filesByPriority as $file) {
                if (file_exists($file)) {
                    include_once $file;
                    $_MODULES = !empty($_MODULES) ? array_merge($_MODULES, $_MODULE) : $_MODULE;
                }
            }
            $translationsMerged[$name][$iso] = true;
        }

        $string = preg_replace("/\\\*'/", "\'", $originalString);
        $key = md5($string);

        $cacheKey = $name . '|' . $string . '|' . $source . '|' . (int) $js . '|' . $iso;
        if (isset($langCache[$cacheKey])) {
            $ret = $langCache[$cacheKey];
        } else {
            $currentKey = strtolower('<{' . $name . '}' . _THEME_NAME_ . '>' . $source) . '_' . $key;
            $defaultKey = strtolower('<{' . $name . '}prestashop>' . $source) . '_' . $key;

            if ('controller' == substr($source, -10, 10)) {
                $file = substr($source, 0, -10);
                $currentKeyFile = strtolower('<{' . $name . '}' . _THEME_NAME_ . '>' . $file) . '_' . $key;
                $defaultKeyFile = strtolower('<{' . $name . '}prestashop>' . $file) . '_' . $key;
            }

            if (isset($currentKeyFile) && !empty($_MODULES[$currentKeyFile])) {
                $ret = stripslashes($_MODULES[$currentKeyFile]);
            } elseif (isset($defaultKeyFile) && !empty($_MODULES[$defaultKeyFile])) {
                $ret = stripslashes($_MODULES[$defaultKeyFile]);
            } elseif (!empty($_MODULES[$currentKey])) {
                $ret = stripslashes($_MODULES[$currentKey]);
            } elseif (!empty($_MODULES[$defaultKey])) {
                $ret = stripslashes($_MODULES[$defaultKey]);
            } elseif (!empty($_LANGADM)) {
                // if translation was not found in module, look for it in AdminController or Helpers
                $ret = stripslashes(Translate::getGenericAdminTranslation($string, $key, $_LANGADM));
            } else {
                $ret = stripslashes($string);
            }

            if ($sprintf !== null) {
                $ret = Translate::checkAndReplaceArgs($ret, $sprintf);
            }

            if ($js) {
                $ret = addslashes($ret);
            } elseif ($escape) {
                $ret = htmlspecialchars($ret, ENT_COMPAT, 'UTF-8');
            }

            if ($sprintf === null) {
                $langCache[$cacheKey] = $ret;
            } else {
                return $ret;
            }
        }

        return $langCache[$cacheKey];
    }
}
