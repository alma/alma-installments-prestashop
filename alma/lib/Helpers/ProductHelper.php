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
 * Class ProductHelper.
 *
 * Use for Product
 */
class ProductHelper
{
    /**
     * @param array $productRow
     *
     * @return string
     */
    public function getImageLink($productRow)
    {
        $link = \Context::getContext()->link;

        return $link->getImageLink(
            $productRow['link_rewrite'],
            $productRow['id_image'],
            self::getFormattedImageTypeName('large')
        );
    }

    /**
     * @param $product
     * @param array $productRow
     * @param \Cart $cart
     *
     * @return string
     *
     * @throws \PrestaShopException
     */
    public function getProductLink($product, $productRow, $cart)
    {
        $link = \Context::getContext()->link;

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

    /**
     * @param string $name
     *
     * @return string
     */
    private static function getFormattedImageTypeName($name)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return \ImageType::getFormattedName($name);
        }

        return \ImageType::getFormatedName($name);
    }

    /**
     * @param $productId
     * @param $productAttributeId
     * @param $quantity
     *
     * @return float
     */
    public function getPriceStatic($productId, $productAttributeId, $quantity = 1)
    {
        return \Product::getPriceStatic(
            $productId,
            true,
            $productAttributeId,
            6,
            null,
            false,
            true,
            $quantity
        );
    }

    /**
     * @param $productId
     * @param $productAttributeId
     *
     * @return float|int
     */
    public function getRegularPrice($productId, $productAttributeId)
    {
        $product = new \Product((int) $productId);
        $combination = new \Combination($productAttributeId);

        $combinationPrice = $combination->price;
        $productRegularPrice = $product->price;

        return $combinationPrice + $productRegularPrice;
    }

    /**
     * @param $productParams
     *
     * @return int|mixed
     */
    public function getQuantity($productParams)
    {
        if (!isset($productParams['quantity_wanted']) && !isset($productParams['minimal_quantity'])) {
            $quantity = 1;
        } elseif (!isset($productParams['quantity_wanted'])) {
            $quantity = (int) $productParams['minimal_quantity'];
        } elseif (!isset($productParams['minimal_quantity'])) {
            $quantity = (int) $productParams['quantity_wanted'];
        } else {
            $quantity = max((int) $productParams['minimal_quantity'], (int) $productParams['quantity_wanted']);
        }
        if ($quantity === 0) {
            $quantity = 1;
        }

        return $quantity;
    }

    /**
     * @param \CartCore $cart
     *
     * @return array
     */
    public function getCmsReferencesByCart($cart)
    {
        $cmsReferences = [];

        $products = $cart->getProducts();
        foreach ($products as $product) {
            for ($qty = 1; $qty <= $product['cart_quantity']; ++$qty) {
                $cmsReferences[] = sprintf(
                    '%s-%s',
                    $product['id_product'],
                    $product['id_product_attribute']
                );
            }
        }

        return $cmsReferences;
    }
}
