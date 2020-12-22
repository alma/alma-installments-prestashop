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

namespace Alma\PrestaShop\Utils;

use Context;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class LocaleHelper.
 *
 * Currency formatting/localization has been handled differently through PS versions, as they moved from a simple
 * enum (<1.7) to using CLDR data in PS 1.7.
 * Until PS 1.7.6, the IcanBoogie/CLDR library was being used; since PS 1.7.6, the PrestaShop team has implemented their
 * own CLDR data handling.
 *
 * This class is meant to help handle currency- and other locale-related data throughout PrestaShop versions, making
 * those differences transparent.
 */
class LocaleHelper
{
    /**
     * Find the current decimal separator depending on the current context.
     *
     * @return string The decimal separator used for the current context (locale)
     */
    public static function decimalSeparator()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $currencyFormat = Context::getContext()->currency->format;

            switch ($currencyFormat) {
                case 1: 		// X0,000.00
                case 4:			// 0,000.00X
                case 5:			// 0'000.00X
                    return '.';
                case 2:			// 0 000,00X
                case 3:			// X0.000,00
                    return ',';
            }
        } elseif (version_compare(_PS_VERSION_, '1.7.6', '<')) {
            $cldr = Tools::getCldr(Context::getContext());
            $culture = $cldr->getCulture();
            $locale = $cldr->getRepository()->locales[$culture];

            return $locale['numbers']['symbols-numberSystem-latn']['decimal'];
        } else {
            $currentLocale = Context::getContext()->getCurrentLocale();
            $numberSpec = $currentLocale->getNumberSpecification();
            $symbols = $numberSpec->getSymbolsByNumberingSystem('latn');

            return $symbols->getDecimal();
        }
    }

    public static function thousandSeparator()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $currencyFormat = Context::getContext()->currency->format;

            switch ($currencyFormat) {
                case 1: 		// X0,000.00
                case 4:			// 0,000.00X
                    return ',';
                case 2:			// 0 000,00X
                    return ' ';
                case 3:			// X0.000,00
                    return '.';
                case 5:			// 0'000.00X
                    return "'";
            }
        } elseif (version_compare(_PS_VERSION_, '1.7.6', '<')) {
            $cldr = Tools::getCldr(Context::getContext());
            $culture = $cldr->getCulture();
            $locale = $cldr->getRepository()->locales[$culture];

            return $locale['numbers']['symbols-numberSystem-latn']['group'];
        } else {
            $currentLocale = Context::getContext()->getCurrentLocale();
            $numberSpec = $currentLocale->getNumberSpecification();
            $symbols = $numberSpec->getSymbolsByNumberingSystem('latn');

            return $symbols->getGroup();
        }
    }
}
