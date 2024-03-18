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

use Alma\PrestaShop\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PriceHelper
{
    /**
     * Converts a float price to its integer cents value, used by the API
     *
     * @param float|int $price
     *
     * @return int
     */
    public function convertPriceToCents($price)
    {
        return (int) round($price * 100);
    }

    /**
     * Same as above but with a string-based implementation, to try and kill the rounding problems subject for good
     *
     * @param float|int $price The price to convert to cents
     *
     * @return int
     */
    public static function convertPriceToCentsStr($price)
    {
        $priceStr = (string) $price;
        $parts = explode('.', $priceStr);

        if (count($parts) == 1) {
            $parts[] = '00';
        } elseif (\Tools::strlen($parts[1]) == 1) {
            $parts[1] .= '0';
        } else {
            if (\Tools::strlen($parts[1]) > 2) {
                $parts[1] = \Tools::substr($parts[1], 0, 2);
            }
        }

        return (int) implode($parts);
    }

    /**
     * Converts an integer price in cents to a float price in the used currency units
     *
     * @param int $price
     *
     * @return float
     */
    public static function convertPriceFromCents($price)
    {
        return (float) ($price / 100);
    }

    /**
     * @param int $cents Price to be formatted, in cents (will be converted to currency's base)
     * @param null $idCurrency
     *
     * @return string The formatted price, using the current locale and provided or current currency
     *
     * @throws \PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException
     */
    public static function formatPriceToCentsByCurrencyId($cents, $idCurrency = null)
    {
        $legacy = version_compare(_PS_VERSION_, '1.7.6.0', '<');
        $currency = \Context::getContext()->currency;
        $price = PriceHelper::convertPriceFromCents($cents);

        if ($idCurrency) {
            $currency = \Currency::getCurrencyInstance((int) $idCurrency);

            if (!\Validate::isLoadedObject($currency)) {
                $currency = \Context::getContext()->currency;
            }
        }

        // We default to a naive format of the price, in case things don't work with PrestaShop localization
        $formattedPrice = sprintf('%.2f€', $price);

        try {
            if ($legacy) {
                $formattedPrice = \Tools::displayPrice($price, $currency);
            } else {
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
            }
        } catch (\Exception $e) {
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
    public static function calculatePercentage($number, $total)
    {
        if (
            !is_numeric($total)
            || $total == 0
        ) {
            return 0;
        }

        return round(($number / $total) * 100, 2);
    }
}
