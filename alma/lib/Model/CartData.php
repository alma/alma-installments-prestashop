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

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Utils\Settings;
use Cart;
use CartRule;
use Configuration;
use PrestaShopDatabaseException;
use PrestaShopException;
use Product;
use TaxConfiguration;

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
        $productHelper = new ProductHelper();
        return [
            'items' => self::getCartItems($cart, $productHelper),
            'discounts' => self::getCartDiscounts($cart),
        ];
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
    public static function getCartItems($cart, $productHelper)
    {
        $items = [];

        $summaryDetails = $cart->getSummaryDetails($cart->id_lang, true);
        $products = array_merge($summaryDetails['products'], $summaryDetails['gift_products']);
        $productsDetails = $productHelper->getProductsDetails($products);
        $combinationsNames = $productHelper->getProductsCombinations($cart, $products);

        foreach ($products as $productRow) {
            $product = new Product(null, false, $cart->id_lang);
            $product->hydrate($productRow);
            $pid = (int) $product->id;
            $manufacturer_name = isset($productRow['manufacturer_name']) ? $productRow['manufacturer_name'] : null;
            if (!$manufacturer_name && isset($productsDetails[$pid])) {
                $manufacturer_name = $productsDetails[$pid]['manufacturer_name'];
            }

            $unitPrice = self::includeTaxes($cart) ? (float) $productRow['price_wt'] : (float) $productRow['price'];
            $linePrice = self::includeTaxes($cart) ? (float) $productRow['total_wt'] : (float) $productRow['total'];

            if (isset($productRow['gift'])) {
                $isGift = (bool) $productRow['gift'];
            } else {
                $isGift = isset($productRow['is_gift']) && (bool)$productRow['is_gift'];
            }

            $pictureUrl = $productHelper->getImageLink($productRow);

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
                'url' => $productHelper->getProductLink($product, $productRow, $cart),
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
}
