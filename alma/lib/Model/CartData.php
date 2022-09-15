<?php
/**
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Utils\Settings;
use Cart;
use CartRule;
use Configuration;
use Context;
use Db;
use DbQuery;
use ImageType;
use PrestaShopDatabaseException;
use PrestaShopException;
use Product;
use TaxConfiguration;
use Tools;

class CartData
{
    private static $taxCalculationMethod = [];

    /**
     * @param Cart $cart
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function cartInfo($cart)
    {
        return [
            'items' => self::getCartItems($cart),
            'discounts' => self::getCartDiscounts($cart),
        ];
    }

    /**
     * Item of Current Cart
     *
     * @param Cart $cart
     *
     * @return array
     */
    public static function cartItems($cart)
    {
        return self::getCartItems($cart);
    }

    /**
     * @param Cart $cart
     *
     * @return bool|mixed
     */
    private static function includeTaxes($cart)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $taxConfiguration = new TaxConfiguration();

            return $taxConfiguration->includeTaxes();
        } else {
            if (!Configuration::get('PS_TAX')) {
                return false;
            }

            $idCustomer = (int) $cart->id_customer;
            if (!array_key_exists($idCustomer, self::$taxCalculationMethod)) {
                self::$taxCalculationMethod[$idCustomer] = !Product::getTaxCalculationMethod($idCustomer);
            }

            return self::$taxCalculationMethod[$idCustomer];
        }
    }

    /**
     * @param Cart $cart
     *
     * @return array of items
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getCartItems($cart)
    {
        $items = [];

        $summaryDetails = $cart->getSummaryDetails($cart->id_lang, true);
        $products = array_merge($summaryDetails['products'], $summaryDetails['gift_products']);
        $productsDetails = self::getProductsDetails($products);
        $combinationsNames = self::getProductsCombinations($cart, $products);

        foreach ($products as $productRow) {
            $product = new Product(null, false, $cart->id_lang);
            $product->hydrate($productRow);

            $pid = (int) $product->id;
            $link = Context::getContext()->link;

            $manufacturer_name = isset($productRow['manufacturer_name']) ? $productRow['manufacturer_name'] : null;
            if (!$manufacturer_name and isset($productsDetails[$pid])) {
                $manufacturer_name = $productsDetails[$pid]['manufacturer_name'];
            }

            $unitPrice = self::includeTaxes($cart) ? (float) $productRow['price_wt'] : (float) $productRow['price'];
            $linePrice = self::includeTaxes($cart) ? (float) $productRow['total_wt'] : (float) $productRow['total'];

            if (isset($productRow['gift'])) {
                $isGift = (bool) $productRow['gift'];
            } else {
                $isGift = isset($productRow['is_gift']) ? (bool) $productRow['is_gift'] : null;
            }

            $pictureUrl = $link->getImageLink(
                $productRow['link_rewrite'],
                $productRow['id_image'],
                self::getFormattedImageTypeName('large')
            );

            if (isset($productRow['is_virtual'])) {
                $requiresShipping = !(bool) $productRow['is_virtual'];
            } else {
                $requiresShipping = !(bool) $productsDetails[$pid]['is_virtual'];
            }

            $data = [
                'sku' => $productRow['reference'],
                'vendor' => $manufacturer_name,
                'title' => $productRow['name'],
                'variant_title' => null,
                'quantity' => (int) $productRow['cart_quantity'],
                'unit_price' => almaPriceToCents($unitPrice),
                'line_price' => almaPriceToCents($linePrice),
                'is_gift' => $isGift,
                'categories' => [$productRow['category']],
                'url' => $link->getProductLink(
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
                ),
                'picture_url' => $pictureUrl,
                'requires_shipping' => $requiresShipping,
                'taxes_included' => self::includeTaxes($cart),
            ];

            if (isset($productRow['id_product_attribute']) && (int) $productRow['id_product_attribute']) {
                $unique_id = "$pid-{$productRow['id_product_attribute']}";

                if ($combinationName = $combinationsNames[$unique_id]) {
                    $data['variant_title'] = $combinationName;
                }
            }

            $items[] = $data;
        }

        return $items;
    }

    private static function getFormattedImageTypeName($name)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return ImageType::getFormattedName($name);
        } else {
            return ImageType::getFormatedName($name);
        }
    }

    /**
     * @param array $products
     *
     * @return array
     */
    private static function getProductsDetails($products)
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
     * @param Cart $cart
     * @param array $products
     *
     * @return array
     *
     * @throws PrestaShopException
     */
    private static function getProductsCombinations($cart, $products)
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
     * @param Cart $cart
     *
     * @return array of discount items
     */
    private static function getCartDiscounts($cart)
    {
        $discounts = [];
        $cartRules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL, false);

        foreach ($cartRules as $cartRule) {
            $amount = self::includeTaxes($cart) ? (float) $cartRule['value_real'] : (float) $cartRule['value_tax_exc'];
            $discounts[] = [
                'title' => isset($cartRule['name']) ? $cartRule['name'] : $cartRule['description'],
                'amount' => almaPriceToCents($amount),
            ];
        }

        return $discounts;
    }

    /**
     * Check if some products in cart are in the excluded listing
     *
     * @param Cart $cart
     *
     * @return array
     */
    public static function getCartExclusion($cart)
    {
        $products = $cart->getProducts(true);

        $cartProductsCategories = [];

        foreach ($products as $p) {
            $productCategories = Product::getProductCategories((int) $p['id_product']);
            foreach ($productCategories as $cat) {
                $cartProductsCategories[] = $cat;
            }
        }

        $excludedListing = Settings::getExcludedCategories();

        return array_intersect($cartProductsCategories, $excludedListing);
    }

    /**
     * Get amount of purchase
     *
     * @param Cart $cart
     *
     * @return float
     */
    public static function getPurchaseAmount($cart)
    {
        return (float) Tools::ps_round((float) $cart->getOrderTotal(true, Cart::BOTH), 2);
    }

    /**
     * Get amount of purchase in cent
     *
     * @param Cart $cart
     *
     * @return int
     */
    public static function getPurchaseAmountInCent($cart)
    {
        return (int) almaPriceToCents(self::getPurchaseAmount($cart));
    }
}
