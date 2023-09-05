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

use Alma\PrestaShop\Model\OrderData;
use Alma\PrestaShop\Traits\AjaxTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class OrderHelper.
 *
 * Use for Order
 */
class OrderHelper
{
    use AjaxTrait;

    /**
     * @var array
     */
    public $defaultStatesExcluded;

    /**
     * @var array
     */
    private $orders;

    public function __construct()
    {
        $this->defaultStatesExcluded = [6, 7, 8];
        $this->orders = [];
    }

    /**
     * Order Ids validated by date.
     *
     * @param string $startDate
     * @param string $endDate
     *
     * @return \Order[]
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getOrdersByDate($startDate, $endDate)
    {
        if (!empty($this->orders)) {
            return $this->orders;
        }
        $newOrders = [];
        $orderIdsByDate = $this->getOrdersIdByDate($startDate, $endDate);
        foreach ($orderIdsByDate as $orderId) {
            $currentOrder = new \Order($orderId);
            $newOrders[] = $currentOrder;
        }
        $this->orders = $newOrders;

        return $newOrders;
    }

    /**
     * Order by date.
     *
     * @param string $startDate
     * @param string $endDate
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     */
    public function getOrdersIdByDate($startDate, $endDate)
    {
        $sql = 'SELECT `id_order`
                FROM `' . _DB_PREFIX_ . 'orders`
                WHERE date_add >= \'' . pSQL($startDate) . '\'
                AND date_add <= \'' . pSQL($endDate) . '\'
                AND current_state NOT IN (' . implode(', ', array_map('intval', $this->defaultStatesExcluded)) . ')
                    ' . \Shop::addSqlRestriction();
        $result = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $orderIds = [];
        foreach ($result as $order) {
            $orderIds[] = (int) $order['id_order'];
        }

        return $orderIds;
    }

    /**
     * @return void
     */
    public function resetOrderList()
    {
        $this->orders = [];
    }

    /**
     * @param $order
     *
     * @return false|mixed
     *
     * @throws \PrestaShopException
     */
    public function getOrderPaymentOrFail($order)
    {
        $orderPayment = OrderData::getCurrentOrderPayment($order);
        if (!$orderPayment) {
            $this->ajaxRenderAndExit(
                $this->module->l('Error: Could not find Alma transaction', 'OrderDataTrait')
            );
        }

        return $orderPayment;
    }
}
