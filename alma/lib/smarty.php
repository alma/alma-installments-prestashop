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
$smarty = Context::getContext()->smarty;

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
    return \Alma\PrestaShop\Helpers\PriceHelper::formatPriceToCentsByCurrencyId($params['cents'], isset($params['currency']) ? $params['currency'] : null);
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
