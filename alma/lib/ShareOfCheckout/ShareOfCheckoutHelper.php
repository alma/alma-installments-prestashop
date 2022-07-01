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
use Alma\PrestaShop\ShareOfCheckout\OrderHelper;
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
    const COUNT_KEY = 'order_count';
    const AMOUNT_KEY = 'amount';
    const CURRENCY_KEY = 'currency';
    const PAYMENT_METHOD_KEY = 'payment_method_name';

    public function __construct(
        OrderHelper $orderHelper
    )
    {
        $this->orderHelper = $orderHelper;
    }

    /**
     * @var null
     */
    private $startDate;
    /**
     * @var null
     */
    private $endDate;

    public function shareDays()
    {
        $shareOfCheckoutEnabledDate = $this->getEnabledDate();
        if (!Settings::canShareOfCheckout() || empty($shareOfCheckoutEnabledDate)) {
            Logger::instance()->info('Share Of Checkout is disabled or invalide date');
            return ;
        }
        try {
            $lastShareOfCheckout = $this->getLastShareOfCheckout();
            foreach ($this->getDatesInInterval($lastShareOfCheckout, $shareOfCheckoutEnabledDate) as $date) {
                $this->setStartDate($date);
                $this->putDay();
            }
        } catch (RequestError $e) {
            Logger::instance()->info('Get Last Update Date error - end of process - message : ' . $e->getMessage());
            return;
        }
        return true;
    }

    public function putDay()
    {
        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            Logger::instance()->error('Cannot put share of checkout: no API client');

            return;
        }

        try {
            $alma->shareOfCheckout->share($this->getPayload());
        } catch (RequestError $e) {
            Logger::instance()->error('AdminAlmaShareOfCheckout::share error get message :' . $e->getMessage());
        }
    }

    /**
     * Get last Share of Checkout
     *
     * @return array|null
     */
    public function getLastShareOfCheckout()
    {
        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            Logger::instance()->error('Cannot get last date share of checkout: no API client');

            return null;
        }

        try {
            $lastDate = $alma->shareOfCheckout->getLastUpdateDates();
        } catch (RequestError $e) {
            if ($e->response->responseCode == '404') {
                Logger::instance()->info('First send to Share of checkout');

                $lastDate = date('Y-m-d', strtotime('-1 day'));
                return $lastDate;
            }
            Logger::instance()->error('Cannot get last date share of checkout: ' . $e->getMessage());

            return null;
        }

        return $lastDate['end_time'];
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
            $isoCodeCurrency = $this->getIsoCodeById($order->id_currency);

            if (!isset($ordersByCurrency[$isoCodeCurrency])) {
                $ordersByCurrency[$isoCodeCurrency] = $this->initTotalOrderResult($isoCodeCurrency);
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
            $paymentMethod = $order->module;
            $isoCodeCurrency = $this->getIsoCodeById($order->id_currency);

            if (!isset($ordersByCheckout[$paymentMethod])) {
                $ordersByCheckout[$paymentMethod] = ['orders' => []];
            }

            if (!isset($ordersByCheckout[$paymentMethod]['orders'][$isoCodeCurrency])) {
                // phpcs:ignore
                $ordersByCheckout[$paymentMethod]['orders'][$isoCodeCurrency] = $this->initOrderResult($isoCodeCurrency);
            }

            $ordersByCheckout[$paymentMethod][self::PAYMENT_METHOD_KEY] = $paymentMethod;
            // phpcs:ignore
            $ordersByCheckout[$paymentMethod]['orders'][$isoCodeCurrency][self::AMOUNT_KEY] += almaPriceToCents($order->total_paid_tax_incl);
            ++$ordersByCheckout[$paymentMethod]['orders'][$isoCodeCurrency][self::COUNT_KEY];
        }
        foreach ($ordersByCheckout as $paymentKey => $paymentMethodOrders) {
            $ordersByCheckout[$paymentKey]['orders'] = array_values($paymentMethodOrders['orders']);
        }

        return array_values($ordersByCheckout);
    }

    /**
     * Array structure to send total orders
     *
     * @param array $currency
     *
     * @return array
     */
    private function initTotalOrderResult($currency)
    {
        return [
            self::TOTAL_AMOUNT_KEY => 0,
            self::TOTAL_COUNT_KEY => 0,
            self::CURRENCY_KEY => $currency,
        ];
    }

    /**
     * Array structure to send payment method orders
     *
     * @param array $currency
     *
     * @return array
     */
    private function initOrderResult($currency)
    {
        return [
            self::AMOUNT_KEY => 0,
            self::COUNT_KEY => 0,
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
        $defaultStatesExcluded = [6, 7, 8];
        $orderIdsByDate = self::getOrdersIdByDate($this->startDate, $this->endDate);
        foreach ($orderIdsByDate as $orderId) {
            $currentOrder = new Order($orderId);
            if (!in_array((int) $currentOrder->current_state, $defaultStatesExcluded)) {
                $orderIds[] = $currentOrder->id;
            }
        }

        return $orderIds;
    }

    /**
     * Order by date
     *
     * @param string $date_start
     * @param string $date_end
     *
     * @return array
     */
    public static function getOrdersIdByDate($date_start, $date_end)
    {
        $sql = 'SELECT `id_order`
                FROM `' . _DB_PREFIX_ . 'orders`
                WHERE date_add >= \'' . pSQL($date_start) . '\'
                AND date_add <= \'' . pSQL($date_end) . '\'
                    ' . Shop::addSqlRestriction();
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $orders = [];
        foreach ($result as $order) {
            $orders[] = (int) $order['id_order'];
        }

        return $orders;
    }

    /**
     * @param string $startDate
     *
     * @return void
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate . ' 00:00:00';
        $this->setEndDate($startDate);
    }

    /**
     * @param string $endDate
     *
     * @return void
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate . ' 23:59:59';
    }

    /**
     * @return string
     */
    private function getStartDateTime()
    {
        if (isset($this->startDate)) {
            return $this->startDate;
        }

        return date('Y-m-d', strtotime('yesterday')) . ' 00:00:00';
    }

    /**
     * @return string
     */
    private function getEndDateTime()
    {
        if (isset($this->endDate)) {
            return $this->endDate;
        }

        return date('Y-m-d', strtotime('yesterday')) . ' 23:59:59';
    }

    /**
     * Get Currency ISO Code by ID
     *
     * @param string $id
     *
     * @return string
     */
    private function getIsoCodeById($id)
    {
        $currency = new Currency();
        if (method_exists(get_parent_class($currency), 'getIsoCodeById')) {
            return $currency->getIsoCodeById($id);
        }

        return $currency->getCurrency($id)['iso_code'];
    }

    /**
     * Payload Share of Checkout
     *
     * @return array
     */
    public function getPayload()
    {
        return [
            'start_time' => strtotime($this->getStartDateTime()),
            'end_time' => strtotime($this->getEndDateTime()),
            'orders' => $this->getTotalOrders(),
            'payment_methods' => $this->getTotalPaymentMethods(),
        ];
    }

    /**
     * @return string|false 
     */
    protected function getEnabledDate()
    {
        return Configuration::get(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_DATE);
    }

    /**
     * @return array 
     */
    protected function getDatesInInterval($lastShareOfCheckout, $shareOfCheckoutEnabledDate)
    {
        return DateHelper::getDatesInInterval($lastShareOfCheckout, $shareOfCheckoutEnabledDate);
    }
}
