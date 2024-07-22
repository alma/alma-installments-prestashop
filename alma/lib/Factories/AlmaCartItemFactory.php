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
use stdClass;

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
        if (!is_object($product) && (!is_array($product) || empty($product))) {
            throw new AlmaCartItemFactoryException('Product must be an object or an array');
        }

        if (is_array($product)) {
            $objProduct = new stdClass();

            foreach ($product as $key => $value) {
                $objProduct->$key = $value;
            }
        } else {
            $objProduct = $product;
        }

        if (property_exists($objProduct, 'id_product')) {
            $objProduct->id = $objProduct->id_product;
        }

        if (
            isset($objProduct->id) &&
            isset($objProduct->id_product_attribute) &&
            isset($objProduct->quantity)
        ) {
            return new AlmaCartItemModel($objProduct);
        }

        throw new AlmaCartItemFactoryException('Product must have id, id_product_attribute, id_customization and quantity');
    }
}
