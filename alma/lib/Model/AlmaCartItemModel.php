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
    private $id;
    /**
     * @var string
     */
    private $id_product_attribute;
    /**
     * @var int
     */
    private $id_customization;
    /**
     * @var int
     */
    private $quantity;
    /**
     * @var float
     */
    private $price_without_reduction;
    /**
     * @var string
     */
    private $reference;
    /**
     * @var string
     */
    private $name;

    public function __construct($productArray)
    {
        $this->id = $productArray['id'];
        $this->id_product_attribute = $productArray['id_product_attribute'];
        $this->id_customization = $productArray['id_customization'] ?: 0;
        $this->quantity = $productArray['quantity'];
        $this->price_without_reduction = $productArray['price_without_reduction'];
        $this->reference = $productArray['reference'];
        $this->name = $productArray['name'];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIdProductAttribute()
    {
        return $this->id_product_attribute;
    }

    /**
     * @return int
     */
    public function getIdCustomization()
    {
        return $this->id_customization;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return float
     */
    public function getPriceWithoutReduction()
    {
        return $this->price_without_reduction;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
