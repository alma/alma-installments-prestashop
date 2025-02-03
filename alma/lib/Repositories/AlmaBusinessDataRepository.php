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

namespace Alma\PrestaShop\Repositories;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AlmaBusinessDataRepository
{
    /**
     * Creates table ps_alma_business_data
     *
     * @return bool
     */
    public function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'alma_business_data` (
            `id_alma_business_data` int(10) NOT NULL AUTO_INCREMENT,
            `id_cart` int(10) NOT NULL,
            `id_order` int(10) DEFAULT NULL,
            `alma_payment_id` varchar(255) DEFAULT NULL,
            `is_bnpl_eligible` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
            `plan_key` varchar(255) NOT NULL,
            PRIMARY KEY (`id_alma_business_data`),
            UNIQUE KEY `unique_id_cart` (`id_cart`),
            UNIQUE KEY `unique_alma_payment_id` (`alma_payment_id`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return \Db::getInstance()->execute($sql);
    }

    /**
     * Update the table ps_alma_business_data for field set with value where id_cart is equal to cartId
     *
     * @param string $field
     * @param string $value
     * @param string $cartId
     *
     * @return bool
     */
    public function updateByCartId($field, $value, $cartId)
    {
        return $this->update($field, $value, 'id_cart = ' . (int) $cartId);
    }

    /**
     * Update the table ps_alma_business_data for field set with value
     *
     * @param string $field
     * @param string $value
     * @param string $where
     *
     * @return bool
     */
    private function update($field, $value, $where = '')
    {
        return \Db::getInstance()->update(
            'alma_business_data',
            [
                $field => pSQL($value),
            ],
            $where
        );
    }
}
