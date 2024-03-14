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

use Alma\API\Client;
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
    /**
     * @var \Context
     */
    protected $context;

    /**
     * @var Client
     */
    protected $almaClient;

    /**
     * @var ClientHelper
     */
    protected $almaClientHelper;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @var DatesHelper
     */
    protected $datesHelper;

    /**
     * @param OrderHelper$orderHelper
     */
    public function __construct($orderHelper)
    {
        $this->orderHelper = $orderHelper;
        $this->context = \Context::getContext();
        $this->almaClientHelper = new ClientHelper();
        $this->datesHelper = new DatesHelper();
    }

    /**
     * @codeCoverageIgnore
     * @return void
     */
    public function sendSocData()
    {
        $this->almaClient = $this->almaClientHelper->getAlmaClient();

        $lastSharingDate = \Configuration::get('ALMA_SOC_CRON_TASK');

        try {

            $date = new \DateTime();
            $timestamp = $date->getTimestamp();

            if ($this->datesHelper->isSameDay($timestamp, $lastSharingDate)) {
                // ongoing or already done , don't do anything !
                return;
            }

            SettingsHelper::updateValue('ALMA_SOC_CRON_TASK', $timestamp);

            $this->shareDays();

        } catch (\Exception $e) {
            SettingsHelper::updateValue('ALMA_SOC_CRON_TASK', $lastSharingDate);

            Logger::instance()->error(
                sprintf(
                    'An error occured when sending SOC Data - [message] %',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * @codeCoverageIgnore
     *
     * @return void
     * @throws RequestError
     */
    public function shareDays()
    {
        $shareOfCheckoutEnabledDate = $this->getEnabledDate();
        $startShareOfCheckout = $this->getStartShareOfCheckout();

        $dates = $this->getDatesInInterval($startShareOfCheckout, $shareOfCheckoutEnabledDate);

        foreach ($dates as $date) {
            $payload = $this->getPayload($date);

            $this->sendData($payload);

            $this->orderHelper->resetOrderList();
        }
    }


    /**
     * @codeCoverageIgnore
     *
     * @param array $payload
     * @return array
     * @throws RequestError
     */
    public function sendData($payload)
    {
        return $this->almaClient->shareOfCheckout->share($payload);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string $startShareOfCheckout
     * @param string$shareOfCheckoutEnabledDate
     * @return array|string[]
     */
    public function getDatesInInterval($startShareOfCheckout, $shareOfCheckoutEnabledDate)
    {
        return $this->datesHelper->getDatesInInterval($startShareOfCheckout, $shareOfCheckoutEnabledDate);
    }

    /**
     * @return bool
     */
    public function isSocActivated()
    {
        $shareOfCheckoutEnabledDate = $this->getEnabledDate();

        if (
            ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_CONSENT_NO === SettingsHelper::getShareOfCheckoutStatus()
            || empty($shareOfCheckoutEnabledDate)
            || !$this->datesHelper->isValidTimeStamp($shareOfCheckoutEnabledDate)
        ) {
            Logger::instance()->info('Share Of Checkout is disabled or invalide date');

            return false;
        }

        return true;
    }


    /**
     * Get last Share of Checkout.
     *
     * @codeCoverageIgnore
     * @return int
     */
    public function getStartShareOfCheckout()
    {
        try {
            $startDateUpdateDates = $this->almaClient->shareOfCheckout->getLastUpdateDates();
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
     * @param array $orders
     * @param string $startDate
     * @param string $endDate
     *
     * @return array
     */
    public function getTotalOrders($orders)
    {
        $ordersByCurrency = [];

        foreach ($orders as $order) {
            $isoCodeCurrency = $this->getIsoCodeById($order->id_currency);

            if (!isset($ordersByCurrency[$isoCodeCurrency])) {
                $ordersByCurrency[$isoCodeCurrency] = [
                    'total_amount' => 0,
                    'total_order_count' => 0,
                    'currency' => $isoCodeCurrency,
                ];
            }

            $ordersByCurrency[$isoCodeCurrency]['total_order_count'] += 1;
            $ordersByCurrency[$isoCodeCurrency]['total_amount'] += PriceHelper::convertPriceToCents(
                $order->total_paid_tax_incl
            );
        }

        return array_values($ordersByCurrency);
    }

    /**
     * Payment methods to send.
     *
     * @param array $orders

     * @return array
     */
    public function getTotalPaymentMethods($orders)
    {
        $paymentMethodsByCurrency = array();
        
        /**
         * @var \OrderCore $order
         */
        foreach ($orders as $order) {
            $paymentMethod = $order->module;
            $isoCodeCurrency = $this->getIsoCodeById($order->id_currency);

            if (!isset($paymentMethodsByCurrency[$paymentMethod])) {
                $paymentMethodsByCurrency[$paymentMethod] = array();
            }
            if (!isset( $paymentMethodsByCurrency[$paymentMethod][$isoCodeCurrency])) {
                $paymentMethodsByCurrency[$paymentMethod][$isoCodeCurrency] = array(
                    'order_count' => 0,
                    'amount'      => 0,
                );
            }
            
            $paymentMethodsByCurrency[$paymentMethod][$isoCodeCurrency]['order_count'] += 1;

            $paymentMethodsByCurrency[$paymentMethod][$isoCodeCurrency]['amount'] += PriceHelper::convertPriceToCents(
                $order->total_paid_tax_incl
            );
        }

        return $this->orderTotalPaymentMethods($paymentMethodsByCurrency);
    }

    /**
     * @param array $paymentMethodsByCurrency
     * @return array
     */
    protected function orderTotalPaymentMethods($paymentMethodsByCurrency)
    {
        $paymentMethods = array();

        foreach ($paymentMethodsByCurrency as $paymentMethodName => $currency_values) {
            $paymentMethod                        = array();
            $paymentMethod['payment_method_name'] = $paymentMethodName;
            $orders                                = array();

            foreach ($currency_values as $currency => $values) {
                $orders[] = array(
                    'order_count' => $values['order_count'],
                    'amount'      => $values['amount'],
                    'currency'    => $currency,
                );
            }

            $paymentMethod['orders'] = $orders;
            $paymentMethods[]        = $paymentMethod;
        }

        return $paymentMethods;
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
     * Get Currency ISO Code by ID.
     *
     * @param string $id
     *
     * @return array|bool|object|string|null
     */
    protected function getIsoCodeById($id)
    {
        $currency = new \Currency();

        if (method_exists(get_parent_class($currency), 'getIsoCodeById')) {
            return $currency->getIsoCodeById($id);
        }

        return $currency->getCurrency($id)['iso_code'];
    }

    /**
     * Payload Share of Checkout.
     * @param string $date
     *
     * @return array
     */
    public function getPayload($date)
    {
        $startDate = $date . ' 00:00:00';
        $endDate = $date . ' 23:59:59';

        $orders = $this->orderHelper->getOrdersByDate($startDate, $endDate);

        return [
            'start_time' => strtotime($startDate),
            'end_time' => strtotime($endDate),
            'orders' => $this->getTotalOrders($orders),
            'payment_methods' => $this->getTotalPaymentMethods($orders),
        ];
    }

    /**
     * @return string|false
     */
    public function getEnabledDate()
    {
        return \Configuration::get(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_DATE);
    }
}
