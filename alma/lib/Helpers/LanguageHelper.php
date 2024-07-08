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
 * Class LanguageHelper.
 */
class LanguageHelper
{
    /**
     * Return iso code from id (installed languages only).
     *
     * @param int $id_lang Language ID
     *
     * @return string 2-letter ISO code
     */
    public function getIsoById($idLang)
    {
        return \Language::getIsoById($idLang);
    }

    /**
     * Returns installed languages.
     *
     * @see loadLanguages()
     *
     * @param bool $active Select only active languages
     * @param int|false $id_shop Shop ID
     * @param bool $ids_only If true, returns an array of language IDs
     *
     * @return array<int|array> Language information
     */
    public function getLanguages($active = true, $id_shop = false, $ids_only = false)
    {
        return \Language::getLanguages($active, $id_shop, $ids_only);
    }
}
