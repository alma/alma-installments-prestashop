<?php
/**
 * 2018-2020 Alma SAS
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
 * @copyright 2018-2020 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined('ALMA_MODE_TEST')) {
    define('ALMA_MODE_TEST', 'test');
}

if (!defined('ALMA_MODE_LIVE')) {
    define('ALMA_MODE_LIVE', 'live');
}

class AlmaSettings
{
    public static function l($str)
    {
        return Translate::getModuleTranslation('alma', $str, 'almasettings');
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
        // echo "configkey : ".$configKey;
        // echo 'value : '.$value;
        Configuration::updateValue($configKey, $value, false, $idShopGroup, $idShop);
    }

    public static function deleteAllValues()
    {
        $configKeys = array(
            'ALMA_FULLY_CONFIGURED',
            'ALMA_ACTIVATE_LOGGING',
            'ALMA_API_MODE',
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
            'ALMA_EXCLUDE_CATEGORIES',
        );

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
        // $this->l('Your cart is eligible for monthly installments.', 'almasettings');
        $default = self::l('Your cart is eligible for monthly installments.');

        return self::get('ALMA_IS_ELIGIBLE_MESSAGE', $default);
    }

    public static function getNonEligibilityMessage()
    {
        // Allow PrestaShop's translation feature to detect those strings
        // $this->l('Your cart is not eligible for monthly installments.', 'almasettings');
        $default = self::l('Your cart is not eligible for monthly installments.');

        return self::get('ALMA_NOT_ELIGIBLE_MESSAGE', $default);
    }

    public static function showEligibilityMessage()
    {
        return (bool) (int) self::get('ALMA_SHOW_ELIGIBILITY_MESSAGE', true);
    }

    public static function getPaymentButtonTitle()
    {
        // Allow PrestaShop's translation feature to detect those strings
        // $this->l('Pay in %d installments', 'almasettings');
        $default = self::l('Pay in %d installments');

        return self::get('ALMA_PAYMENT_BUTTON_TITLE', $default);
    }

    public static function getPaymentButtonDescription()
    {
        // Allow PrestaShop's translation feature to detect those strings
        // $this->l('Pay in %d monthly installments with your credit card.', 'almasettings');
        $default = self::l('Pay in %d monthly installments with your credit card.');

        return self::get('ALMA_PAYMENT_BUTTON_DESC', $default);
    }

    public static function displayOrderConfirmation()
    {
        return (bool) (int) self::get('ALMA_DISPLAY_ORDER_CONFIRMATION', false);
    }

    public static function isInstallmentPlanEnabled($n)
    {
        return (bool) (int) self::get("ALMA_P${n}X_ENABLED", $n == 3);
    }

    public static function installmentPlansMaxN()
    {
        return (int) self::get('ALMA_PNX_MAX_N', 3);
    }

    public static function installmentPlanMinAmount($n, $merchant = null)
    {
        $default = $merchant ? $merchant->minimum_purchase_amount : 10000;

        return (int) self::get("ALMA_P${n}X_MIN_AMOUNT", $default);
    }

    public static function installmentPlanMaxAmount($n, $merchant = null)
    {
        $default = $merchant ? $merchant->maximum_purchase_amount : 100000;

        return (int) self::get("ALMA_P${n}X_MAX_AMOUNT", $default);
    }

    public static function getRefundState()
    {
        return (int) self::get('ALMA_STATE_REFUND', 7);
    }

    public static function isRefundEnabledByState()
    {
        return (bool) self::get('ALMA_STATE_REFUND_ENABLED', 0);
    }

    public static function getExcludeCategories()
    {
        $categories = self::get('ALMA_EXCLUDE_CATEGORIES', null);        
        if (null !== $categories) {
            return json_decode($categories);
        }
        return [];
    }

    public static function getExcludeNameCategories()
    {
        $categories = self::getExcludeCategories();
        if (!$categories) {
            return '';
        }
        $categories = Category::getCategories(false, false, false, sprintf('AND c.`id_category` IN (%s)', implode(',', $categories)));
        $categoriesName = [];
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $categoriesName[] = $category['name'];
            }
        }        
        return implode(', ', $categoriesName);
    }

    public static function addExcludeCategories($idCategory)
    {        
        $categories = self::getExcludeCategories();            
        if (!in_array($idCategory, $categories)) {
            $categories[] = $idCategory;
        }        
        self::updateExcludeCategories($categories);        
    }

    public static function removeExcludeCategories($idCategory)
    {
        $categories = self::getExcludeCategories();
        if (($key = array_search($idCategory, $categories)) !== false) {        
            unset($categories[$key]);
        }
        self::updateExcludeCategories(array_values($categories));
    }

    /**
     * Update ALMA_EXCLUDE_CATEGORIES value
     *
     * @param array $categories
     * @return void
     */
    private static function updateExcludeCategories($categories){
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            self::updateValue('ALMA_EXCLUDE_CATEGORIES', Tools::jsonEncode($categories));
        }
        else{
            self::updateValue('ALMA_EXCLUDE_CATEGORIES', json_encode($categories));
        }
    }

    /**
     * Check if some products in cart are in the excludes listing
     *
     * @param object $params
     * @return array
     */
    public static function getCartExclusion($params){
        $products = $params['cart']->getProducts(true);
        $cartProductsCategories = array();        
        foreach($products as $k => $p){            
            $cartProductsCategories[] =  $p['id_category_default'];                
        }
        $excludeListing = self::getExcludeCategories();                        
        return array_intersect($cartProductsCategories, $excludeListing);        
        
    }
}
