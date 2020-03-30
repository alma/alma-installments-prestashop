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
 */
function almaPriceToCents_str($price)
{
    $priceStr = (string) $price;
    $parts = explode(".", $priceStr);

    if (count($parts) == 1) {
        $parts[] = "00";
    } elseif (Tools::strlen($parts[1]) == 1) {
        $parts[1] .= "0";
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
