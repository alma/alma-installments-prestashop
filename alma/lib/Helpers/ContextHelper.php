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
 * Class ContextHelper.
 *
 * Currency formatting/localization has been handled differently through PS versions, as they moved from a simple
 * enum (<1.7) to using CLDR data in PS 1.7.
 * Until PS 1.7.6, the IcanBoogie/CLDR library was being used; since PS 1.7.6, the PrestaShop team has implemented their
 * own CLDR data handling.
 *
 * This class is meant to help handle currency- and other locale-related data throughout PrestaShop versions, making
 * those differences transparent.
 */
class ContextHelper
{
    /**
     * Create a link to a module.
     *
     * @since    1.5.0
     *
     * @param string $module Module name
     * @param string $controller
     * @param array $params
     * @param bool|null $ssl
     * @param int|null $idLang
     * @param int|null $idShop
     * @param bool $relativeProtocol
     *
     * @return string
     */
    public function getModuleLink(
        $context,
        $module,
        $controller = 'default',
        array $params = [],
        $ssl = null,
        $idLang = null,
        $idShop = null,
        $relativeProtocol = false)
    {
        return $context->link->getModuleLink(
            $module,
            $controller,
            $params,
            $ssl,
            $idLang,
            $idShop,
            $relativeProtocol
        );
    }
}
