<?php
/**
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

use Alma\PrestaShop\Forms\ShareOfCheckoutAdminFormBuilder;
use PrestaShop\PrestaShop\Adapter\Entity\Order;

class AdminAlmaShareOfCheckoutController extends ModuleAdminController
{
    public const TOTAL_COUNT_KEY = "total_order_count";
    public const TOTAL_AMOUNT_KEY = "total_amount";
    public const CURRENCY_KEY = "currency";
    public const PAYMENT_METHOD_KEY = "payment_method_name";

    public function initContent()
    {
        $this->getPayload();
    }

    /**
     * Total Orders to send
     *
     * @return array
     */
    public function getTotalOrders()
    {
        $ordersByCurrency = [];
        $count = 0;

        foreach ($this->getOrderIds() as $orderId) {
            $order = new Order($orderId);
            $currency = new Currency();
            $isoCodeCurrency = $currency->getIsoCodeById($order->id_currency);

            if (!isset($ordersByCurrency[$isoCodeCurrency])){
                $ordersByCurrency[$isoCodeCurrency] = $this->initOrderResult($isoCodeCurrency);
            }

            $ordersByCurrency[$isoCodeCurrency][self::TOTAL_COUNT_KEY] = ++$count;
            // phpcs:ignore
            $ordersByCurrency[$isoCodeCurrency][self::TOTAL_AMOUNT_KEY] += almaPriceToCents($order->total_paid_tax_incl);
        }

        return array_values($ordersByCurrency);
    }

    /**
     * Order Ids validated by date
     *
     * @return array
     */
    public function getOrderIds()
    {
        if (empty($this->getFromDate()) || empty($this->getToDate())) {
            return [];
        }
        $orderIds = [];
        $statesPayed = [2, 3, 4, 5, 9];
        $orderIdsByDate = self::getOrdersIdByDate($this->getFromDate(), $this->getToDate());
        foreach($orderIdsByDate as $orderId) {
            $currentOrder = new Order($orderId);
            if (in_array((int) $currentOrder->current_state, $statesPayed)) {
                $orderIds[] = $currentOrder->id;
            }
        }

        return $orderIds;
    }

    /**
     * Payment methods to send
     *
     * @return array
     */
    public function getTotalPaymentMethods()
    {
        $ordersByCheckout = [];

        foreach ($this->getOrderIds() as $orderId) {
            $order = new Order($orderId);
            $currency = new Currency();
            $paymentMethod = $order->module;
            $isoCodeCurrency = $currency->getIsoCodeById($order->id_currency);

            if(!isset($ordersByCheckout[$paymentMethod])){
                $ordersByCheckout[$paymentMethod]=['orders'=>[]];
            }

            if(!isset($ordersByCheckout[$paymentMethod]['orders'][$isoCodeCurrency])){
                // phpcs:ignore
                $ordersByCheckout[$paymentMethod]['orders'][$isoCodeCurrency] = $this->initOrderResult($isoCodeCurrency);
            }

            $ordersByCheckout[$paymentMethod][self::PAYMENT_METHOD_KEY] = $paymentMethod;
            // phpcs:ignore
            $ordersByCheckout[$paymentMethod]['orders'][$isoCodeCurrency][self::TOTAL_AMOUNT_KEY] += almaPriceToCents($order->total_paid_tax_incl);
            $ordersByCheckout[$paymentMethod]['orders'][$isoCodeCurrency][self::TOTAL_COUNT_KEY] ++;
        }
        foreach ($ordersByCheckout as $paymentKey => $paymentMethodOrders) {
            $ordersByCheckout[$paymentKey]['orders']= array_values($paymentMethodOrders['orders']);
        }

        return array_values($ordersByCheckout);
    }

    /**
     * Array structure to send
     *
     * @param array $currency
     * @return array
     */
    private function initOrderResult($currency)
    {
        return [
            self::TOTAL_AMOUNT_KEY => 0,
            self::TOTAL_COUNT_KEY => 0,
            self::CURRENCY_KEY => $currency
        ];
    }

    /**
     * Date From
     *
     * @return string
     */
    public function getFromDate()
    {
        return $this->activatedDate();
    }

    /**
     * Date To
     *
     * @return string
     */
    public function getToDate()
    {
        return $this->activatedDate();
    }

    /**
     * Date to send for Share of Checkout
     *
     * @return string
     */
    public function activatedDate()
    {
        $dateToSend = date("Y-m-d", strtotime("-1 days"));
        $today = date("Y-m-d");
        $activatedDate = Configuration::get(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_DATE);
        if (empty($activatedDate) || $activatedDate >= $today){
            $dateToSend = '';
        }

        return $dateToSend;
    }

    /**
     * Order by date
     *
     * @param string $date_from
     * @param string $date_to
     * @return array
     */
    public static function getOrdersIdByDate($date_from, $date_to)
    {
        $sql = 'SELECT `id_order`
                FROM `' . _DB_PREFIX_ . 'orders`
                WHERE DATE_ADD(date_add, INTERVAL -1 DAY) <= \'' . pSQL($date_to) . '\'
                AND date_add >= \'' . pSQL($date_from) . '\'
                    ' . Shop::addSqlRestriction();
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $orders = [];
        foreach ($result as $order) {
            $orders[] = (int) $order['id_order'];
        }

        return $orders;
    }

    /**
     * Payload Share of Checkout
     *
     * @return array
     */
    public function getPayload()
    {
        return [
            "start_time"=> $this->getFromDate(),
            "end_time"  => $this->getToDate(),
            "orders"    => $this->getTotalOrders(),
            "payment_methods" => $this->getTotalPaymentMethods()
        ];
    }
}