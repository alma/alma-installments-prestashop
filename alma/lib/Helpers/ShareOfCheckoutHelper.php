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

use Alma\API\RequestError;
use Alma\PrestaShop\Exceptions\ShareOfCheckoutException;
use Alma\PrestaShop\Forms\ApiAdminFormBuilder;
use Alma\PrestaShop\Forms\ShareOfCheckoutAdminFormBuilder;
use Alma\PrestaShop\Logger;

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

    /**
     * @var \Context
     */
    protected $context;

    public function __construct($orderHelper)
    {
        $this->orderHelper = $orderHelper;
        $this->context = \Context::getContext();
    }

    /**
     * @var null
     */
    private $startDate;
    /**
     * @var null
     */
    private $endDate;

    /**
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function shareDays()
    {
        $shareOfCheckoutEnabledDate = $this->getEnabledDate();
        if (
            ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_CONSENT_NO === SettingsHelper::getShareOfCheckoutStatus()
            || empty($shareOfCheckoutEnabledDate)
            || !DateHelper::isValidTimeStamp($shareOfCheckoutEnabledDate)
        ) {
            Logger::instance()->info('Share Of Checkout is disabled or invalide date');

            return false;
        }
        try {
            $startShareOfCheckout = $this->getStartShareOfCheckout();
            foreach ($this->getDatesInInterval($startShareOfCheckout, $shareOfCheckoutEnabledDate) as $date) {
                $this->setStartDate($date);
                $this->putDay();
                $this->orderHelper->resetOrderList();
            }
        } catch (RequestError $e) {
            Logger::instance()->info('Get Last Update Date error - end of process - message : ' . $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Put Payload to Share of Checkout.
     *
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
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
     * Get last Share of Checkout.
     *
     * @return int
     */
    public function getStartShareOfCheckout()
    {
        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            Logger::instance()->error('Cannot get last date share of checkout: no API client');

            return strtotime('-1 day');
        }

        try {
            $startDateUpdateDates = $alma->shareOfCheckout->getLastUpdateDates();
        } catch (RequestError $e) {
            Logger::instance()->error('Cannot get last date share of checkout: ' . $e->getMessage());

            if ('404' == $e->response->responseCode) {
                Logger::instance()->info('First send to Share of checkout');
            }

            return strtotime('-1 day');
        }

        return (int) strtotime('+1 day', $startDateUpdateDates['end_time']);
    }

    /**
     * Total Orders to send.
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getTotalOrders()
    {
        $ordersByCurrency = [];

        foreach ($this->orderHelper->getOrdersByDate($this->startDate, $this->endDate) as $order) {
            $isoCodeCurrency = $this->getIsoCodeById($order->id_currency);

            if (!isset($ordersByCurrency[$isoCodeCurrency])) {
                $ordersByCurrency[$isoCodeCurrency] = $this->initTotalOrderResult($isoCodeCurrency);
            }

            ++$ordersByCurrency[$isoCodeCurrency][self::TOTAL_COUNT_KEY];
            $ordersByCurrency[$isoCodeCurrency][self::TOTAL_AMOUNT_KEY] += PriceHelper::convertPriceToCents($order->total_paid_tax_incl);
        }

        return array_values($ordersByCurrency);
    }

    /**
     * Payment methods to send.
     *
     * @return array
     */
    public function getTotalPaymentMethods()
    {
        $ordersByCheckout = [];

        foreach ($this->orderHelper->getOrdersByDate($this->startDate, $this->endDate) as $order) {
            $paymentMethod = $order->module;
            $isoCodeCurrency = $this->getIsoCodeById($order->id_currency);

            if (!isset($ordersByCheckout[$paymentMethod])) {
                $ordersByCheckout[$paymentMethod] = ['orders' => []];
            }

            if (!isset($ordersByCheckout[$paymentMethod]['orders'][$isoCodeCurrency])) {
                $ordersByCheckout[$paymentMethod]['orders'][$isoCodeCurrency] = $this->initOrderResult($isoCodeCurrency);
            }

            $ordersByCheckout[$paymentMethod][self::PAYMENT_METHOD_KEY] = $paymentMethod;
            $ordersByCheckout[$paymentMethod]['orders'][$isoCodeCurrency][self::AMOUNT_KEY] += PriceHelper::convertPriceToCents($order->total_paid_tax_incl);
            ++$ordersByCheckout[$paymentMethod]['orders'][$isoCodeCurrency][self::COUNT_KEY];
        }
        foreach ($ordersByCheckout as $paymentKey => $paymentMethodOrders) {
            $ordersByCheckout[$paymentKey]['orders'] = array_values($paymentMethodOrders['orders']);
        }

        return array_values($ordersByCheckout);
    }

    /**
     * POST add consent Alma endpoint.
     *
     * @return void
     *
     * @throws ShareOfCheckoutException
     */
    public function addConsent()
    {
        $alma = ClientHelper::defaultInstance();
        if ($alma) {
            try {
                $alma->shareOfCheckout->addConsent();
            } catch (RequestError $e) {
                $msg = 'Impossible to save the Share of Checkout settings, please try again later';
                Logger::instance()->error($msg);
                throw new ShareOfCheckoutException($msg);
            }
        }
    }

    /**
     * DELETE consent Alma endpoint.
     *
     * @return void
     *
     * @throws ShareOfCheckoutException
     */
    public function removeConsent()
    {
        $alma = ClientHelper::defaultInstance();
        if ($alma) {
            try {
                $alma->shareOfCheckout->removeConsent();
            } catch (RequestError $e) {
                $msg = 'Impossible to save the Share of Checkout settings, please try again later';
                Logger::instance()->error($msg);
                throw new ShareOfCheckoutException($msg);
            }
        }
    }

    /**
     * handle the activation of Share of Checkout feature.
     *
     * @param string $consentAttribute
     *
     * @return void
     */
    public function handleCheckoutConsent($consentAttribute)
    {
        $userConsent = \Tools::getValue($consentAttribute);

        SettingsHelper::updateValue(
            ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_STATE,
            SettingsHelper::getShareOfCheckoutStatus()
        );
        SettingsHelper::updateValue(
            ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_DATE,
            SettingsHelper::getTimeConsentShareOfCheckout()
        );

        try {
            $this->setConsent($userConsent);
        } catch (ShareOfCheckoutException $e) {
            $this->context->smarty->assign('validation_error', 'soc_api_error');
        }
    }

    /**
     * reset the activation of Share of Checkout feature.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function resetShareOfCheckoutConsent()
    {
        try {
            if (
                \Tools::getValue(ApiAdminFormBuilder::ALMA_LIVE_API_KEY) !== SettingsHelper::getLiveKey()
                && ConstantsHelper::OBSCURE_VALUE !== \Tools::getValue(ApiAdminFormBuilder::ALMA_LIVE_API_KEY)
            ) {
                $this->removeConsent();
                \Configuration::deleteByName(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_STATE);
                \Configuration::deleteByName(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_DATE);
            }
        } catch (ShareOfCheckoutException $e) {
            $this->context->smarty->assign('validation_error', 'soc_api_error');
        }
    }

    /**
     * Set the consent.
     *
     * @param string $userConsent
     *
     * @return void
     *
     * @throws ShareOfCheckoutException
     */
    private function setConsent($userConsent)
    {
        if (ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_CONSENT_YES == $userConsent) {
            $this->setAcceptConsent();

            return;
        }

        $this->setRefuseConsent();
    }

    /**
     * Set Accept Consent.
     *
     * @return void
     *
     * @throws ShareOfCheckoutException
     */
    private function setAcceptConsent()
    {
        $timeConsent = SettingsHelper::getCurrentTimestamp();

        if (SettingsHelper::getTimeConsentShareOfCheckout()) {
            $timeConsent = SettingsHelper::getTimeConsentShareOfCheckout();
        }

        $this->addConsent();
        SettingsHelper::updateValue(
            ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_STATE,
            ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_CONSENT_YES
        );
        SettingsHelper::updateValue(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_DATE, $timeConsent);
    }

    /**
     * Set refuse consent.
     *
     * @return void
     *
     * @throws ShareOfCheckoutException
     */
    private function setRefuseConsent()
    {
        $this->removeConsent();
        SettingsHelper::updateValue(
            ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_STATE,
            ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_CONSENT_NO
        );
        SettingsHelper::updateValue(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_DATE, null);
    }

    /**
     * Array structure to send total orders.
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
     * Array structure to send payment method orders.
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
     * Get Currency ISO Code by ID.
     *
     * @param string $id
     *
     * @return array|bool|object|string|null
     */
    private function getIsoCodeById($id)
    {
        $currency = new \Currency();
        if (method_exists(get_parent_class($currency), 'getIsoCodeById')) {
            return $currency->getIsoCodeById($id);
        }

        return $currency->getCurrency($id)['iso_code'];
    }

    /**
     * Payload Share of Checkout.
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
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
    public function getEnabledDate()
    {
        return \Configuration::get(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_DATE);
    }

    /**
     * @return array
     */
    public function getDatesInInterval($lastShareOfCheckout, $shareOfCheckoutEnabledDate)
    {
        return DateHelper::getDatesInInterval($lastShareOfCheckout, $shareOfCheckoutEnabledDate);
    }
}
