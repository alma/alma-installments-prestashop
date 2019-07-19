<?php
/**
 * 2018-2019 Alma SAS
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
 * @copyright 2018-2019 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

class AlmaProduct
{

    public static function productIsInCategory($idProducts, $idCategory, $childs = false)
    {
        $cache_key = implode('-', $idProducts) . '_' . $idCategory . '_c' . ($childs ? 'y' : 'n');
        if (Cache::isStored($cache_key)) {
            return Cache::retrieve($cache_key);
        } else {
            $id_lang = Context::getContext()->language->id;
            $ret = [];
            $sql = 'SELECT cp.`id_category` FROM `' . _DB_PREFIX_ . 'category_product` cp
                LEFT JOIN `' . _DB_PREFIX_ . 'category` c ON (c.id_category = cp.id_category)
                LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (cp.`id_category` = cl.`id_category`' . Shop::addSqlRestrictionOnLang('cl') . ')
                ' . Shop::addSqlAssociation('category', 'c') . '
                WHERE cl.`id_lang` = ' . (int) $id_lang . ' ';
            if (is_array($idProducts)) {
                $sql .= 'AND cp.`id_product` IN (' . implode(',', $idProducts) . ');';
            } else {
                $sql .= 'AND cp.`id_product` = ' . (int) $idProducts . ';';
            }
            $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $product_categories = [];
            foreach ($rows as $val) {
                if ($val['id_category'] == $idCategory) {
                    Cache::store(
                        $cache_key,
                        true
                    );
                    return true;
                } else {
                    $product_categories[] = $val['id_category'];
                }
            }
            if ($childs) {
                $child_categories = [];
                $category = new Category($idCategory);
                foreach ($categories = $category->getAllChildren() as $child_category) {
                    if (in_array($child_category->id, $product_categories)) {
                        Cache::store(
                            $cache_key,
                            true
                        );
                        return true;
                    }
                }
            }
            Cache::store(
                $cache_key,
                false
            );
            return false;
        }
    }

}