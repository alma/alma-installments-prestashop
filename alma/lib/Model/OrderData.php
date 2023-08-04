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

class OrderData
{
    public static function getCurrentOrderPayment($order)
    {
        if ('alma' != $order->module && 1 == $order->valid) {
            return false;
        }
        $orderPayments = \OrderPayment::getByOrderReference($order->reference);
        if ($orderPayments && isset($orderPayments[0])) {
            return $orderPayments[0];
        }

        return false;
    }

    /**
     * Get customer orders.
     *
     * @param int $idCustomer Customer id
     * @param int $limit
     *
     * @return array Customer orders
     *
     * @throws \PrestaShopDatabaseException
     */
    public static function getCustomerOrders($idCustomer, $limit)
    {
        $res = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT
                o.id_cart,
                o.date_add,
                o.payment,
                o.current_state,
                o.module,
                op.transaction_id
            FROM
                `' . _DB_PREFIX_ . 'orders` o
                LEFT JOIN `' . _DB_PREFIX_ . 'order_payment` op ON op.`order_reference` = o.`reference`
            WHERE
                o.`id_customer` = ' . (int) $idCustomer
                . \Shop::addSqlRestriction(\Shop::SHARE_ORDER) . '
            ORDER BY
                o.`date_add` DESC
            LIMIT ' . (int) $limit
        );

        if (!$res) {
            return [];
        }

        return $res;
    }
}
