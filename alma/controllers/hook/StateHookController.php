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

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\API\Client;
use Alma\API\Entities\Insurance\Subscriber;
use Alma\API\Entities\Insurance\Subscription;
use Alma\API\Exceptions\ParamsException;
use Alma\API\RequestError;
use Alma\PrestaShop\Exceptions\OrderException;
use Alma\PrestaShop\Helpers\Admin\InsuranceHelper;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\OrderHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Hooks\AdminHookController;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Model\OrderData;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\ProductRepository;
use Alma\PrestaShop\Services\InsuranceService;

final class StateHookController extends AdminHookController
{
    /**
     * Checks if user is logged in as Employee or is an API Webservice call
     *
     * When we check if is an API user, we assume that the API user has already
     * the good rights because when canRun is called, actions linked to the hook
     * were already well authenticated by PrestaShop.
     *
     * @return bool
     */
    public function canRun()
    {
        return parent::canRun() || $this->isKnownApiUser();
    }

    /**
     * Execute refund or trigger payment on change state
     *
     * @param array $params
     *
     * @throws OrderException
     * @throws ParamsException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function run($params)
    {
        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            return;
        }

        $order = new \Order($params['id_order']);
        $newStatus = $params['newOrderStatus'];
        $isReturnAjaxError = $newStatus->id == \Configuration::get('PS_OS_REFUND');

        $orderHelper = new OrderHelper();

        $idStateRefund = SettingsHelper::getRefundState();
        $idStatePaymentTrigger = SettingsHelper::getPaymentTriggerState();
        $idStatePayed = 2;

        switch ($newStatus->id) {
            case $idStateRefund:
                $orderPayment = $orderHelper->getOrderPaymentOrFail($order, $isReturnAjaxError);
                $idPayment = $orderPayment->transaction_id;
                $orderHelper->checkOrderAlma($order);
                if (SettingsHelper::isRefundEnabledByState()) {
                    $this->refund($alma, $idPayment, $order);
                }
                break;
            case $idStatePaymentTrigger:
                $orderPayment = $orderHelper->getOrderPaymentOrFail($order, $isReturnAjaxError);
                $idPayment = $orderPayment->transaction_id;
                $orderHelper->checkOrderAlma($order);
                if (SettingsHelper::isPaymentTriggerEnabledByState()) {
                    $this->triggerPayment($alma, $idPayment, $order);
                }
                break;
            case $idStatePayed:
                $orderPayment = $orderHelper->getOrderPayment($order);
                $idPayment = $orderPayment->transaction_id;
                $this->triggerInsuranceSubscription($alma, $idPayment, $order);
                break;
            default:
                break;
        }
    }

    /**
     * Query Refund
     *
     * @param Client $alma
     * @param string $idPayment
     * @param \Order $order
     *
     * @return void
     */
    private function refund($alma, $idPayment, $order)
    {
        try {
            $alma->payments->refund($idPayment, true);
        } catch (RequestError $e) {
            $msg = "[Alma] ERROR when creating refund for Order {$order->id}: {$e->getMessage()}";
            Logger::instance()->error($msg);

            return;
        }
    }

    /**
     * Query Trigger Payment
     *
     * @param Client $alma
     * @param string $idPayment
     * @param \Order $order
     *
     * @return void
     */
    private function triggerPayment($alma, $idPayment, $order)
    {
        try {
            $alma->payments->trigger($idPayment);
        } catch (RequestError $e) {
            $msg = "[Alma] ERROR when creating trigger for Order {$order->id}: {$e->getMessage()}";
            Logger::instance()->error($msg);

            return;
        }
    }

    /**
     * @param Client $alma
     * @param string $idPayment
     * @param \Order $order
     *
     * @return void
     *
     * @throws ParamsException
     * @throws \PrestaShopDatabaseException
     */
    private function triggerInsuranceSubscription($alma, $idPayment, $order)
    {
        $almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
        $insuranceContracts = $almaInsuranceProductRepository->getContractsInfosByCartIdAndShopId($order->id_cart, $order->id_shop);

        $cart = new \Cart((int) $order->id_cart);

        $insuranceService = new InsuranceService();
        $subscriptionData = $insuranceService->createSubscriptionData($insuranceContracts, $cart);

        try {
            $alma->insurance->subscription($subscriptionData, $idPayment);
        } catch (RequestError $e) {
            $msg = "[Alma] ERROR when creating subscription insurance for Order {$order->id}: {$e->getMessage()}";
            Logger::instance()->error($msg);

            return;
        }
    }
}
