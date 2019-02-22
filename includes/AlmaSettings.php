<?php
/**
 * 2018 Alma / Nabla SAS
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
 * @author    Alma / Nabla SAS <contact@getalma.eu>
 * @copyright 2018 Alma / Nabla SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *
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
    public static function get($configKey, $default = null)
    {
        $idShop = Shop::getContextShopID();
        $idShopGroup = Shop::getContextShopGroupID();

        return Configuration::get($configKey, null, $idShopGroup, $idShop, $default);
    }

    public static function updateValue($configKey, $value)
    {
        $idShop = Shop::getContextShopID();
        $idShopGroup = Shop::getContextShopGroupID();

        Configuration::updateValue($configKey, $value, false, $idShopGroup, $idShop);
    }

    public static function deleteAllValues()
    {
        $result = true;

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
        );

        foreach ($configKeys as $configKey) {
            if (Configuration::hasKey($configKey)) {
                $result = $result && Configuration::deleteByName($configKey);
            }
        }

        return $result;
    }


    /* Getters */
    public static function isFullyConfigured()
    {
        return (bool)(int)self::get('ALMA_FULLY_CONFIGURED', false);
    }

    public static function canLog()
    {
        return (bool)(int)self::get('ALMA_ACTIVATE_LOGGING', false);
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
        return (bool)(int)self::get('ALMA_SHOW_DISABLED_BUTTON', true);
    }

    public static function needsAPIKeys()
    {
        return empty(self::get('ALMA_LIVE_API_KEY', '') . self::get('ALMA_TEST_API_KEY', ''));
    }

    public static function getEligibilityMessage($default = '')
    {
        return self::get('ALMA_IS_ELIGIBLE_MESSAGE', $default);
    }

    public static function getNonEligibilityMessage($default = '')
    {
        return self::get('ALMA_NOT_ELIGIBLE_MESSAGE', $default);
    }

    public static function showEligibilityMessage()
    {
        return (bool)(int)self::get('ALMA_SHOW_ELIGIBILITY_MESSAGE', true);
    }

    public static function getPaymentButtonTitle($default = '')
    {
        return self::get('ALMA_PAYMENT_BUTTON_TITLE', $default);
    }

    public static function getPaymentButtonDescription($default = '')
    {
        return self::get('ALMA_PAYMENT_BUTTON_DESC', $default);
    }
}
