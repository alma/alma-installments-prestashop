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

use PrestaShop\PrestaShop\Adapter\Entity\Order;

class AdminAlmaShareOfCheckoutController extends ModuleAdminController
{
    public const TOTAL_COUNT_KEY = "total_order_count";
    public const TOTAL_AMOUNT_KEY = "total_amount";
    public const CURRENCY_KEY = "currency";
    public const PAYMENT_METHOD_KEY = "payment_method_name";

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

    public function getOrderIds()
    {
        $order = new Order();

        return $order->getOrdersIdByDate($this->getFromDate(), $this->getToDate());
    }

    public function getTotalCheckouts()
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

    private function initOrderResult($currency)
    {
        return [
            self::TOTAL_AMOUNT_KEY => 0,
            self::TOTAL_COUNT_KEY => 0,
            self::CURRENCY_KEY => $currency
        ];
    }

    public function getFromDate()
    {
        return '2022-03-18';
    }

    public function getToDate()
    {
        return '2022-03-18';
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
            "checkouts" => $this->getTotalCheckouts()
        ];
    }
}