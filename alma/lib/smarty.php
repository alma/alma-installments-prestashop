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

use Alma\PrestaShop\Builders\Helpers\PriceHelperBuilder;

if (!defined('_PS_VERSION_')) {
    exit;
}

if (class_exists('\Context') && PHP_SAPI != 'cli') {
    $smarty = \Context::getContext()->smarty;

    /**
     * @param $source
     * @param $template
     *
     * @return array|string|string[]|null
     */
    function smarty_prefilter_almaDisplayHtml($source, $template)
    {
        return preg_replace(
            ['/{almaDisplayHtml}/', '/{\/almaDisplayHtml}/'],
            [
                "{capture name='alma_html'}",
                "{/capture}\n{\$smarty.capture.alma_html|unescape:'html'}",
            ],
            $source
        );
    }

    $smarty->registerFilter('pre', 'smarty_prefilter_almaDisplayHtml');

    /**
     * @param $params
     * @param $smarty
     *
     * @return string
     */
    function smarty_function_almaFormatPrice($params, $smarty)
    {
        $priceHelperBuilder = new PriceHelperBuilder();
        $priceHelper = $priceHelperBuilder->getInstance();

        return $priceHelper->formatPriceToCentsByCurrencyId($params['cents'], isset($params['currency']) ? $params['currency'] : null);
    }

    smartyRegisterFunction($smarty, 'function', 'almaFormatPrice', 'smarty_function_almaFormatPrice');

    /**
     * @param $value
     *
     * @return false|string
     */
    function smarty_modifier_almaJsonEncode($value)
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            return Tools::jsonEncode($value);
        } else {
            return json_encode($value);
        }
    }

    smartyRegisterFunction($smarty, 'modifier', 'almaJsonEncode', 'smarty_modifier_almaJsonEncode');

    function smarty_function_almaCmsReference($params, $smarty)
    {
        $static_price = round($params['static_price'] * 100);

        if ($params['product_attribute_id'] === '0') {
            return $params['product_id'] . '-' . $static_price;
        }

        return $params['product_id'] . '-' . $params['product_attribute_id'] . '-' . $static_price;
    }

    smartyRegisterFunction($smarty, 'function', 'almaCmsReference', 'smarty_function_almaCmsReference');
}
