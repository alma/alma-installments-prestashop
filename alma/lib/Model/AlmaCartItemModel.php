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

namespace Alma\PrestaShop\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AlmaCartItemModel
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $id_product_attribute;
    /**
     * @var int
     */
    public $id_customization;
    /**
     * @var int
     */
    public $quantity;
    /**
     * @var float
     */
    public $price_without_reduction;
    /**
     * @var string
     */
    public $reference;
    /**
     * @var string
     */
    public $name;

    public function __construct($product)
    {
        $this->id = $product->id;
        $this->id_product_attribute = $product->id_product_attribute;
        $this->id_customization = $product->id_customization ?: 0;
        $this->quantity = $product->quantity;
        $this->price_without_reduction = $product->price_without_reduction;
        $this->reference = $product->reference;
        $this->name = $product->name;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getIdProductAttribute()
    {
        return $this->id_product_attribute;
    }

    /**
     * @return mixed
     */
    public function getIdCustomization()
    {
        return $this->id_customization;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}
