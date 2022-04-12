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

namespace Alma\PrestaShop\Utils;

use Alma\API\RequestError;
use Alma\PrestaShop\API\ClientHelper;
use Alma\PrestaShop\Forms\ShareOfCheckoutAdminFormBuilder;
use Configuration;
use Currency;
use Db;
use Order;
use Shop;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class ShareOfCheckoutHelper.
 *
 * Use for method date
 */
class ShareOfCheckoutHelper
{
    const TOTAL_COUNT_KEY = 'total_order_count';
    const TOTAL_AMOUNT_KEY = 'total_amount';
    const CURRENCY_KEY = 'currency';
    const PAYMENT_METHOD_KEY = 'payment_method_name';

    /**
     * @var null
     */
    private $startTime;
    /**
     * @var null
     */
    private $endTime;

    public function shareDays()
    {
        ini_set('max_execution_time', 30);

        if (!Settings::canShareOfCheckout()) {
            Logger::instance()->info('Share Of Checkout is not enabled');

            return;
        }
        $shareOfCheckoutEnabledDate = Configuration::get(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_DATE);

        if ($shareOfCheckoutEnabledDate == '') {
            Logger::instance()->info('No enable date in config');

            return;
        }

        try {
            $lastUpdateDate = self::getLastShareOfCheckout();
        } catch (RequestError $e) {
            Logger::instance()->info('Get Last Update Date error - end of process - message : ' . $e->getMessage());

            return;
        }

        $DatesToShare = DateHelper::getDatesInInterval($lastUpdateDate, $shareOfCheckoutEnabledDate);
        foreach ($DatesToShare as $date) {
            try {
                $this->setShareOfCheckoutFromDate($date);
                $this->putDay();
            } catch (RequestError $e) {
                Logger::instance()->info('Share of checkout error - end of process - message : ' . $e->getMessage());

                return;
            }
        }
    }

    public function putDay()
    {
        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            Logger::instance()->error('Cannot put share of checkout: no API client');

            return;
        }

        try {
            $alma->shareOfCheckout->share(json_encode($this->getPayload()));
        } catch (RequestError $e) {
            Logger::instance()->error('AdminAlmaShareOfCheckout::share error get message :' . $e->getMessage());
        }
    }

    /**
     * Get last Share of Checkout
     *
     * @return object
     */
    public function getLastShareOfCheckout()
    {
        $lastDateShareOfCheckout = null;
        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            Logger::instance()->error('Cannot get last date share of checkout: no API client');

            return;
        }

        try {
            $lastDateShareOfCheckout = $alma->shareOfCheckout->getLastUpdateDate();
            //TODO : See format get date share of checkout
            return $lastDateShareOfCheckout;
        } catch (RequestError $e) {
            Logger::instance()->error('Cannot get last date share of checkout: ' . $e->getMessage());
        }
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

            if (!isset($ordersByCurrency[$isoCodeCurrency])) {
                $ordersByCurrency[$isoCodeCurrency] = $this->initOrderResult($isoCodeCurrency);
            }

            $ordersByCurrency[$isoCodeCurrency][self::TOTAL_COUNT_KEY] = ++$count;
            // phpcs:ignore
            $ordersByCurrency[$isoCodeCurrency][self::TOTAL_AMOUNT_KEY] += almaPriceToCents($order->total_paid_tax_incl);
        }

        return array_values($ordersByCurrency);
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

            if (!isset($ordersByCheckout[$paymentMethod])) {
                $ordersByCheckout[$paymentMethod] = ['orders' => []];
            }

            if (!isset($ordersByCheckout[$paymentMethod]['orders'][$isoCodeCurrency])) {
                // phpcs:ignore
                $ordersByCheckout[$paymentMethod]['orders'][$isoCodeCurrency] = $this->initOrderResult($isoCodeCurrency);
            }

            $ordersByCheckout[$paymentMethod][self::PAYMENT_METHOD_KEY] = $paymentMethod;
            // phpcs:ignore
            $ordersByCheckout[$paymentMethod]['orders'][$isoCodeCurrency][self::TOTAL_AMOUNT_KEY] += almaPriceToCents($order->total_paid_tax_incl);
            ++$ordersByCheckout[$paymentMethod]['orders'][$isoCodeCurrency][self::TOTAL_COUNT_KEY];
        }
        foreach ($ordersByCheckout as $paymentKey => $paymentMethodOrders) {
            $ordersByCheckout[$paymentKey]['orders'] = array_values($paymentMethodOrders['orders']);
        }

        return array_values($ordersByCheckout);
    }

    /**
     * Array structure to send
     *
     * @param array $currency
     *
     * @return array
     */
    private function initOrderResult($currency)
    {
        return [
            self::TOTAL_AMOUNT_KEY => 0,
            self::TOTAL_COUNT_KEY => 0,
            self::CURRENCY_KEY => $currency,
        ];
    }

    /**
     * Order Ids validated by date
     *
     * @return array
     */
    public function getOrderIds()
    {
        $orderIds = [];
        $statesPayed = [2, 3, 4, 5, 9];
        $orderIdsByDate = self::getOrdersIdByDate($this->startTime, $this->endTime);
        foreach ($orderIdsByDate as $orderId) {
            $currentOrder = new Order($orderId);
            if (in_array((int) $currentOrder->current_state, $statesPayed)) {
                $orderIds[] = $currentOrder->id;
            }
        }

        return $orderIds;
    }

    /**
     * Order by date
     *
     * @param string $date_from
     * @param string $date_to
     *
     * @return array
     */
    public static function getOrdersIdByDate($date_from, $date_to)
    {
        $sql = 'SELECT `id_order`
                FROM `' . _DB_PREFIX_ . 'orders`
                WHERE date_add >= \'' . pSQL($date_from) . '\'
                AND date_add <= \'' . pSQL($date_to) . '\'
                    ' . Shop::addSqlRestriction();
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $orders = [];
        foreach ($result as $order) {
            $orders[] = (int) $order['id_order'];
        }

        return $orders;
    }

    /**
     * @param $startTime
     *
     * @return void
     */
    public function setShareOfCheckoutFromDate($startTime)
    {
        $this->startTime = $startTime . ' 00:00:00';
        $this->setShareOfCheckoutToDate($startTime);
    }

    /**
     * @param $endTime
     *
     * @return void
     */
    public function setShareOfCheckoutToDate($endTime)
    {
        $this->endTime = $endTime . ' 23:59:59';
    }

    /**
     * @return string
     */
    private function getShareOfCheckoutFromDate()
    {
        if (isset($this->startTime)) {
            return $this->startTime;
        }

        return date('Y-m-d', strtotime('yesterday')) . ' 00:00:00';
    }

    /**
     * @return string
     */
    private function getShareOfCheckoutToDate()
    {
        if (isset($this->endTime)) {
            return $this->endTime;
        }

        return date('Y-m-d', strtotime('yesterday')) . ' 23:59:59';
    }

    /**
     * Payload Share of Checkout
     *
     * @return array
     */
    public function getPayload()
    {
        return [
            'start_time' => strtotime($this->getShareOfCheckoutFromDate()),
            'end_time' => strtotime($this->getShareOfCheckoutToDate()),
            'orders' => $this->getTotalOrders(),
            'payment_methods' => $this->getTotalPaymentMethods(),
        ];
    }
}
