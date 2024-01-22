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

namespace Alma\PrestaShop\Model;


if (!defined('_PS_VERSION_')) {
    exit;
}

class InsuranceProduct extends \ObjectModel
{
    const STATE_ACTIVE = 'active';

    /** @var int Id  */
    public $id_alma_insurance_product;

    /** @var int Id cart */
    public $id_cart;

    /** @var int Id product */
    public $id_product;

    /** @var int Id shop */
    public $id_shop;

    /** @var int Id product attribute */
    public $id_product_attribute;

    /** @var int Id customization */
    public $id_customization;

    /** @var int Id product insurance */
    public $id_product_insurance;

    /** @var int Id product attribute insurance */
    public $id_product_attribute_insurance;

    /** @var int Id address delivery */
    public $id_address_delivery;

    /** @var int Id cart */
    public $id_order;

    /** @var float Price of insurance */
    public $price;

    /** @var float Price of the product */
    public $product_price;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object validity */
    public $state;

    /** @var string Neat insurance id */
    public $subscription_id;

    /** @var string Alma cms reference */
    public $cms_reference;

    /** @var string Object cancellation date */
    public $date_of_cancellation;

    /** @var string reason of cancellation */
    public $reason_of_cancellation;

    /** @var bool Object refunded state */
    public $is_refunded;

    /** @var string Object refund date */
    public $date_of_refund;


    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'alma_insurance_product',
        'primary' => 'id_alma_insurance_product',
        'fields' => [
            'id_cart' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_product_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_customization' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_product_insurance' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_product_attribute_insurance' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_address_delivery' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'price' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'insurance_contract_id' => ['type' => self::TYPE_STRING],
            'cms_reference' => ['type' => self::TYPE_STRING],
            'product_price' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'subscription_id' => ['type' => self::TYPE_STRING],
            'state' => ['type' => self::TYPE_STRING],
            'date_of_cancellation' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'reason_of_cancellation' => ['type' => self::TYPE_STRING],
            'is_refunded' => ['type' => self::TYPE_BOOL],
            'date_of_refund' => ['type' => self::TYPE_DATE, 'validate' => 'isDate']
        ],
    ];

}