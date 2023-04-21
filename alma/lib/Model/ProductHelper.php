<?php
/**
 * 2018-2023 Alma SAS
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

namespace Alma\PrestaShop\Model;

use Cart;
use Context;
use Db;
use DbQuery;
use ImageType;
use PrestaShopDatabaseException;
use PrestaShopException;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class ProductHelper.
 *
 * Use for Product
 */
class ProductHelper
{
    /**
     * @param Cart $cart
     * @param array $products
     *
     * @return array
     *
     * @throws PrestaShopException
     */
    public function getProductsCombinations($cart, $products)
    {
        $sql = new DbQuery();
        $sql->select('CONCAT(p.`id_product`, "-", pa.`id_product_attribute`) as `unique_id`');

        $combinationName = new DbQuery();
        $combinationName->select('GROUP_CONCAT(DISTINCT CONCAT(agl.`name`, " - ", al.`name`) SEPARATOR ", ")');
        $combinationName->from('product_attribute', 'pa2');
        $combinationName->innerJoin(
            'product_attribute_combination',
            'pac',
            'pac.`id_product_attribute` = pa2.`id_product_attribute`'
        );
        $combinationName->innerJoin('attribute', 'a', 'a.`id_attribute` = pac.`id_attribute`');
        $combinationName->innerJoin(
            'attribute_lang',
            'al',
            'a.id_attribute = al.id_attribute AND al.`id_lang` = ' . $cart->id_lang
        );
        $combinationName->innerJoin('attribute_group', 'ag', 'ag.`id_attribute_group` = a.`id_attribute_group`');
        $combinationName->innerJoin(
            'attribute_group_lang',
            'agl',
            'ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . $cart->id_lang
        );
        $combinationName->where(
            'pa2.`id_product` = p.`id_product` AND pa2.`id_product_attribute` = pa.`id_product_attribute`'
        );

        /* @noinspection PhpUnhandledExceptionInspection */
        $sql->select("({$combinationName->build()}) as combination_name");

        $sql->from('product', 'p');
        $sql->innerJoin('product_attribute', 'pa', 'pa.`id_product` = p.`id_product`');

        // DbQuery::where joins all where clauses with `) AND (` so for ORs we need a fully built where condition
        $where = '';
        $op = '';
        foreach ($products as $productRow) {
            if (!isset($productRow['id_product_attribute']) || !(int) $productRow['id_product_attribute']) {
                continue;
            }

            $where .= "{$op}(p.`id_product` = {$productRow['id_product']}";
            $where .= " AND pa.`id_product_attribute` = {$productRow['id_product_attribute']})";
            $op = ' OR ';
        }
        $sql->where($where);

        $db = Db::getInstance();
        $combinationsNames = [];

        try {
            $results = $db->query($sql);
        } catch (PrestaShopDatabaseException $e) {
            return $combinationsNames;
        }

        while ($result = $db->nextRow($results)) {
            $combinationsNames[$result['unique_id']] = $result['combination_name'];
        }

        return $combinationsNames;
    }

    /**
     * @param array $products
     * @return array
     */
    public function getProductsDetails($products)
    {
        $sql = new DbQuery();
        $sql->select('p.`id_product`, p.`is_virtual`, m.`name` as manufacturer_name');
        $sql->from('product', 'p');
        $sql->innerJoin('manufacturer', 'm', 'm.`id_manufacturer` = p.`id_manufacturer`');

        $in = [];
        foreach ($products as $productRow) {
            $in[] = $productRow['id_product'];
        }

        $in = implode(', ', $in);
        $sql->where("p.`id_product` IN ({$in})");

        $db = Db::getInstance();
        $productsDetails = [];

        try {
            $results = $db->query($sql);
        } catch (PrestaShopDatabaseException $e) {
            return $productsDetails;
        }

        if ($results !== false) {
            while ($result = $db->nextRow($results)) {
                $productsDetails[(int) $result['id_product']] = $result;
            }
        }

        return $productsDetails;
    }

    /**
     * @param $productRow
     * @return string
     */
    public function getImageLink($productRow)
    {
        $link = Context::getContext()->link;

        return $link->getImageLink(
            $productRow['link_rewrite'],
            $productRow['id_image'],
            self::getFormattedImageTypeName('large')
        );
    }

    public function getProductLink($product, $productRow, $cart)
    {
        $link = Context::getContext()->link;

        return $link->getProductLink(
            $product,
            $productRow['link_rewrite'],
            $productRow['category'],
            null,
            $cart->id_lang,
            $cart->id_shop,
            $productRow['id_product_attribute'],
            false,
            false,
            true
        );
    }

    private static function getFormattedImageTypeName($name)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return ImageType::getFormattedName($name);
        } else {
            return ImageType::getFormatedName($name);
        }
    }
}
