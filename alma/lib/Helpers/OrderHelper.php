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

namespace Alma\PrestaShop\Helpers;

use Alma\PrestaShop\Exceptions\OrderException;
use Alma\PrestaShop\Factories\LoggerFactory;
use Alma\PrestaShop\Repositories\OrderRepository;
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
    /**
     * @var \Module
     */
    private $module;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    public function __construct()
    {
        $this->module = \Module::getInstanceByName(ConstantsHelper::ALMA_MODULE_NAME);
        $this->defaultStatesExcluded = [6, 7, 8];
        $this->orders = [];
        $this->orderRepository = new OrderRepository();
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
            $newOrders[] = new \Order($orderId);
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
     * @param \Order $order
     *
     * @return mixed
     *
     * @throws OrderException
     */
    public function getOrderPayment($order)
    {
        $orderPayment = $this->getCurrentOrderPayment($order);

        if (!$orderPayment) {
            $msg = '[Alma] orderPayment not found';
            LoggerFactory::instance()->error($msg);
            throw new OrderException($msg);
        }

        return $orderPayment;
    }

    /**
     * @param \Order $order
     *
     * @return false|\OrderPayment|void
     *
     * @throws \PrestaShopException
     */
    public function ajaxGetOrderPayment($order)
    {
        try {
            return $this->getOrderPayment($order);
        } catch (OrderException $e) {
            $this->ajaxRenderAndExit(
                $this->module->l('Error: Could not find Alma transaction', 'OrderHelper')
            );
        }
    }

    /**
     * @param \OrderCore $order
     * @param bool $checkIsAlma
     *
     * @return false|mixed
     */
    public function getCurrentOrderPayment($order, $checkIsAlma = true)
    {
        if (
            true === $checkIsAlma
            && 'alma' != $order->module
        ) {
            return false;
        }

        $orderPayments = $order->getOrderPayments();

        if (
            $orderPayments
            && isset($orderPayments[0])
        ) {
            return $orderPayments[0];
        }

        return false;
    }

    /**
     * @throws OrderException
     */
    public function checkIfIsOrderAlma($order)
    {
        if ($order->module !== 'alma') {
            $msg = sprintf(
                '[Alma] This order id #%s is not an order Alma',
                $order->id
            );

            LoggerFactory::instance()->error($msg);
            throw new OrderException($msg);
        }
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function getCustomerNbOrders($id)
    {
        return \Order::getCustomerNbOrders($id);
    }

    /**
     * Get ids order by customer id with limit (default = 10)
     *
     * @param int $idCustomer
     * @param int $limit
     *
     * @return array
     */
    public function getOrdersByCustomer($idCustomer, $limit)
    {
        try {
            $orders = $this->orderRepository->getCustomerOrders($idCustomer, $limit);
        } catch (\PrestaShopDatabaseException $e) {
            return [];
        }

        return $orders;
    }
}
