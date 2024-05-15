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
 * Class MediaHelper.
 *
 * Use for Media
 */
class MediaHelper
{
    /**
     * @param object $module
     *
     * @return string
     */
    public static function getIconPathAlmaTiny($module)
    {
        if (is_callable('\Media::getMediaPath')) {
            return \Media::getMediaPath(_PS_MODULE_DIR_ . $module->name . '/views/img/logos/alma_tiny.svg');
        }

        return $module->getPathUri() . '/views/img/logos/alma_tiny.svg';
    }

    /**
     * @param $path
     * @param $module
     *
     * @return mixed
     */
    public function getMediaPath($path, $module)
    {
        return \Media::getMediaPath(_PS_MODULE_DIR_ . $module->name . $path);
    }

    /**
     * @param string $valueBNPL
     * @param bool $isDeferred
     *
     * @return string
     */
    public function getLogoName($valueBNPL, $isDeferred)
    {
        if ($isDeferred) {
            return "{$valueBNPL}j_logo.svg";
        }

        return "p{$valueBNPL}x_logo.svg";
    }

    /**
     * Add a new javascript definition at bottom of page.
     *
     * @param mixed $jsDef
     * @codeCoverageIgnore
     */
    public function addJSDef($jsDef) {
        \Media::addJsDef($jsDef);
    }
}
