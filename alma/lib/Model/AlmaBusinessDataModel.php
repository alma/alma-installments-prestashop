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

use Alma\PrestaShop\Traits\ObjectModelTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AlmaBusinessDataModel extends \ObjectModel
{
    use ObjectModelTrait;

    /** @var int */
    public $id_alma_business_data;

    /** @var int */
    public $id_cart;

    /** @var int */
    public $id_order;

    /** @var string */
    public $alma_payment_id;

    /** @var bool */
    public $is_bnpl_eligible;

    /** @var string */
    public $plan_key;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'alma_business_data',
        'primary' => 'id_alma_business_data',
        'fields' => [
            'id_cart' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'alma_payment_id' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'is_bnpl_eligible' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'plan_key' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
        ],
    ];

    /**
     * @param $null_values
     * @param $auto_date
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    public function save($null_values = false, $auto_date = true)
    {
        if (version_compare(_PS_VERSION_, '1.7.1.0', '<')) {
            return $this->updateWithFullyQualifiedNamespace($null_values);
        } else {
            return parent::save($null_values, $auto_date);
        }
    }

    /**
     * @return array|bool|object|null
     */
    public function getByCartId($cartId)
    {
        $db = \Db::getInstance();
        $query = new \DbQuery();
        $query->select('*')
            ->from(static::$definition['table'])
            ->where('id_cart = ' . (int) $cartId);

        return $db->getRow($query);
    }
}
