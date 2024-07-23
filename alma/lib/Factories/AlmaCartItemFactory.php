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

namespace Alma\PrestaShop\Factories;

use Alma\PrestaShop\Exceptions\AlmaCartItemFactoryException;
use Alma\PrestaShop\Model\AlmaCartItemModel;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AlmaCartItemFactory
{
    /**
     * @throws AlmaCartItemFactoryException
     */
    public function create($product)
    {
        if (!is_object($product) && !is_array($product)) {
            throw new AlmaCartItemFactoryException('Product must be an object or an array');
        }
        $cartItemData = [];
        if (is_object($product)) {
            $cartItemData['id'] = isset($product->id) ? $product->id : null;
            $cartItemData['id_product_attribute'] = isset($product->id_product_attribute) ? $product->id_product_attribute : null;
            $cartItemData['id_customization'] = isset($product->id_customization) ? $product->id_customization : null;
            $cartItemData['quantity'] = isset($product->quantity) ? $product->quantity : null;
            $cartItemData['price_with_reduction'] = isset($product->price_with_reduction) ? $product->price_with_reduction : null;
            $cartItemData['reference'] = isset($product->reference) ? $product->reference : null;
            $cartItemData['name'] = isset($product->name) ? $product->name : null;
        }
        if (is_array($product)) {
            $cartItemData = $product;
        }
        if (
             empty($cartItemData) ||
            (
                !isset($cartItemData['id']) ||
                !isset($cartItemData['id_product_attribute']) ||
                !isset($cartItemData['quantity']) ||
                !isset($cartItemData['price_with_reduction']) ||
                !isset($cartItemData['reference']) ||
                !isset($cartItemData['name'])
            )
        ) {
            throw new AlmaCartItemFactoryException('Product array must contain Id, id_product_attribute and quantity');
        }

        return new AlmaCartItemModel($cartItemData);
    }
}
