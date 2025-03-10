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

/**
 * Class ConfigurationHelper.
 */
class ConfigurationHelper
{
    /**
     * Get several configuration values (in one language only).
     *
     * @throws \PrestaShopException
     *
     * @param array $keys Keys wanted
     * @param int $idLang Language ID
     * @param int $idShopGroup
     * @param int $idShop
     *
     * @return array Values
     */
    public function getMultiple($keys, $idLang = null, $idShopGroup = null, $idShop = null)
    {
        return \Configuration::getMultiple($keys, $idLang, $idShopGroup, $idShop);
    }

    /**
     * @deprecated use the get in ConfigurationProxy instead
     * Get a single configuration value (in one language only)
     *
     * @param string $key Key wanted
     * @param int $idLang Language ID
     *
     * @return string|false Value
     */
    public function get($key, $idLang = null, $idShopGroup = null, $idShop = null, $default = false)
    {
        return \Configuration::get($key, $idLang, $idShopGroup, $idShop, $default);
    }

    /**
     * Check if key exists in configuration.
     *
     * @param string $key
     * @param int $idLang
     * @param int $idShopGroup
     * @param int $idShop
     *
     * @return bool
     */
    public function hasKey($key, $idLang = null, $idShopGroup = null, $idShop = null)
    {
        return \Configuration::hasKey($key, $idLang, $idShopGroup, $idShop);
    }

    /**
     * Delete the key in database
     *
     * @param $configKey
     *
     * @return void
     */
    public function deleteByName($configKey)
    {
        \Configuration::deleteByName($configKey);
    }

    /**
     * Delete the keys in database
     *
     * @param array $configKeys
     *
     * @return void
     */
    public function deleteByNames($configKeys)
    {
        foreach ($configKeys as $configKey) {
            $this->deleteByName($configKey);
        }
    }

    /**
     * Update configuration key and value into database (automatically insert if key does not exist).
     *
     * Values are inserted/updated directly using SQL, because using (Configuration) ObjectModel
     * may not insert values correctly (for example, HTML is escaped, when it should not be).
     *
     * @param string $key Configuration key
     * @param mixed $values $values is an array if the configuration is multilingual, a single string else
     * @param bool $html Specify if html is authorized in value
     * @param int $idShopGroup
     * @param int $idShop
     *
     * @return bool Update result
     */
    public function updateValue($key, $values, $html = false, $idShopGroup = null, $idShop = null)
    {
        return \Configuration::updateValue($key, $values, $html, $idShopGroup, $idShop);
    }

    /**
     * @param SettingsHelper $settingsHelper
     *
     * @return bool
     */
    public function isInPageEnabled($settingsHelper)
    {
        return $settingsHelper->isInPageEnabled();
    }

    /**
     * @deprecated use static function isPayNowStatic instead
     *
     * @param string $key
     *
     * @return bool
     */
    public function isPayNow($key)
    {
        return ConstantsHelper::ALMA_KEY_PAYNOW === $key;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public static function isPayNowStatic($key)
    {
        return ConstantsHelper::ALMA_KEY_PAYNOW === $key;
    }
}
