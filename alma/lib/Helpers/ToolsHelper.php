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

namespace Alma\PrestaShop\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class ToolsHelper.
 */
class ToolsHelper
{
    /**
     * returns the rounded value of $value to specified precision, according to your configuration;.
     *
     * @note : PHP 5.3.0 introduce a 3rd parameter mode in round function
     *
     * @param float $value
     * @param int $precision
     *
     * @return float
     */
    public function psRound($value, $precision = 0, $roundMode = null)
    {
        return \Tools::ps_round($value, $precision, $roundMode);
    }

    /**
     * Return price with currency sign for a given product.
     *
     * @see \PrestaShop\PrestaShop\Core\Localization\Locale
     *
     * @param bool $legacy
     * @param float $price Product price
     * @param int|\Currency|array|null $currency Current currency (object, id_currency, NULL => context currency)
     *
     * @return string Price correctly formatted (sign, decimal separator...)
     *                if you modify this function, don't forget to modify the Javascript function formatCurrency (in tools.js)
     *
     * @throws \LocalizationException
     */
    public function displayPrice($legacy, $price, $currency = null)
    {
        if ($legacy) {
            return \Tools::displayPrice($price, $currency);
        }

        $locale = \Context::getContext()->currentLocale;

        try {
            $formattedPrice = $locale->formatPrice($price, $currency->iso_code);
        } catch (\PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException $e) {
            // Catch LocalizationException at this level too, so that we can fallback to \Tools::displayPrice if it
            // still exists. If it, itself, throws a LocalizationException, it will be caught by the outer catch.
            if (method_exists('\Tools', 'displayPrice')) {
                $formattedPrice = \Tools::displayPrice($price, $currency);
            }
        }

        return $formattedPrice;
    }

    /**
     * @param string $version2
     * @param string $operator
     * @param string $version1
     *
     * @return bool|int
     */
    public function psVersionCompare($version2, $operator, $version1 = _PS_VERSION_)
    {
        return version_compare($version1, $version2, $operator);
    }

    /**
     * @param $string
     *
     * @return false|int
     */
    public function strlen($string)
    {
        return \Tools::strlen($string);
    }

    /**
     * @param string $str
     * @param int $start
     * @param bool $length
     * @param string $encoding
     *
     * @return false|string
     */
    public function substr($str, $start, $length = false, $encoding = 'utf-8')
    {
        return \Tools::substr($str, $start, $length, $encoding);
    }

    /**
     * @param $array
     * @param $key
     *
     * @return false|string
     */
    public function getJsonValues($array, $key)
    {
        $return = [];

        if (!is_array($array)) {
            return json_encode($return);
        }

        foreach ($array as $value) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                continue;
            }
            $return[] = $value[$key];
        }

        return json_encode($return);
    }
}
