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

use Alma\PrestaShop\Utils\Logger;
use PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException;

/**
 * Converts a float price to its integer cents value, used by the API
 *
 * @param $price float
 *
 * @return int
 */
function almaPriceToCents($price)
{
    return (int) (round($price * 100));
}

/**
 * Same as above but with a string-based implementation, to try and kill the rounding problems subject for good
 *
 * @param $price float The price to convert to cents
 *
 * @return int
 */
function almaPriceToCents_str($price)
{
    $priceStr = (string) $price;
    $parts = explode('.', $priceStr);

    if (count($parts) == 1) {
        $parts[] = '00';
    } elseif (Tools::strlen($parts[1]) == 1) {
        $parts[1] .= '0';
    } elseif (Tools::strlen($parts[1]) > 2) {
        $parts[1] = Tools::substr($parts[1], 0, 2);
    }

    return (int) implode($parts);
}

/**
 * Converts an integer price in cents to a float price in the used currency units
 *
 * @param $price int
 *
 * @return float
 */
function almaPriceFromCents($price)
{
    return (float) ($price / 100);
}

/**
 * @param $svg string Path to the SVG file to get a data-url for
 *
 * @return string data-url for the given SVG
 */
function almaSvgDataUrl($svg)
{
    static $_dataUrlCache = [];

    if (!array_key_exists($svg, $_dataUrlCache)) {
        $content = file_get_contents($svg);

        if ($content === false) {
            return '';
        }

        $content = preg_replace('/%20/', ' ', rawurlencode(preg_replace("/[\r\n]+/", '', $content)));
        $_dataUrlCache[$svg] = 'data:image/svg+xml,' . $content;
    }

    return $_dataUrlCache[$svg];
}

/**
 * @param int $cents Price to be formatted, in cents (will be converted to currency's base)
 * @param int|null $id_currency
 *
 * @return string The formatted price, using the current locale and provided or current currency
 */
function almaFormatPrice($cents, $id_currency = null)
{
    $legacy = version_compare(_PS_VERSION_, '1.7.6.0', '<');
    $currency = Context::getContext()->currency;
    $price = almaPriceFromCents($cents);

    if ($id_currency) {
        $currency = Currency::getCurrencyInstance((int) $id_currency);
        if (!Validate::isLoadedObject($currency)) {
            $currency = Context::getContext()->currency;
        }
    }

    // We default to a naive format of the price, in case things don't work with PrestaShop localization
    $formattedPrice = sprintf('%.2f€', $price);

    try {
        if ($legacy) {
            $formattedPrice = Tools::displayPrice($price, $currency);
        } else {
            $locale = Context::getContext()->currentLocale;
            try {
                $formattedPrice = $locale->formatPrice($price, $currency->iso_code);
            } catch (LocalizationException $e) {
                // Catch LocalizationException at this level too, so that we can fallback to Tools::displayPrice if it
                // still exists. If it, itself, throws a LocalizationException, it will be caught by the outer catch.
                if (method_exists(Tools, 'displayPrice')) {
                    $formattedPrice = Tools::displayPrice($price, $currency);
                }
            }
        }
    } catch (LocalizationException $e) {
        Logger::instance()->warning("Price localization error: $e");
    }

    return $formattedPrice;
}

/**
 * Calculate percentage number by total
 *
 * @param int $number
 * @param int $total
 *
 * @return float
 */
function almaCalculatePercentage($number, $total)
{
    if (!is_numeric($total) || $total == 0) {
        return 0;
    }

    return round(($number / $total) * 100, 2);
}

/**
 * format date by locale
 *
 * @param string $locale
 * @param int $timestamp
 *
 * @return string date
 */
function getDateFormat($locale, $timestamp)
{
    try {
        if (class_exists(IntlDateFormatter::class)) {
            $formatter = new IntlDateFormatter($locale, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);

            return $formatter->format($timestamp);
        }
    } catch (Exception $e) {
        // We don't need to deal with this Exception because a fallback exists in default return statement
    }

    return getFrenchDateFormat($timestamp);
}

/**
 * fallback for when IntlDateFormatter is not available
 *
 * @param string $timestamp
 *
 * @return string
 */
function getFrenchDateFormat($timestamp)
{
    $date = new DateTime();
    $date->setTimestamp($timestamp);

    return $date->format('d/m/Y');
}
