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

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\API\Client;
use Alma\API\RequestError;
use Alma\PrestaShop\API\ClientHelper;
use Alma\PrestaShop\Hooks\AdminHookController;
use Alma\PrestaShop\Model\OrderData;
use Alma\PrestaShop\Utils\Logger;
use Alma\PrestaShop\Utils\OrderDataTrait;
use Alma\PrestaShop\Utils\Settings;
use Configuration;
use Order;
use PrestaShopDatabaseException;
use PrestaShopException;

final class StateHookController extends AdminHookController
{
    use OrderDataTrait;

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
     * @param $params
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function run($params)
    {
        $order = new Order($params['id_order']);
        $newStatus = $params['newOrderStatus'];
        if ($newStatus->id == Configuration::get('PS_OS_REFUND')) {
            $order_payment = $this->getOrderPaymentOrFail($order);
        } else {
            $order_payment = OrderData::getCurrentOrderPayment($order);
        }
        if ($order->module !== 'alma' || !$order_payment) {
            return;
        }
        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            return;
        }
        $id_payment = $order_payment->transaction_id;
        $id_state_refund = Settings::getRefundState();
        $id_state_payment_trigger = Settings::getPaymentTriggerState();

        switch ($newStatus->id) {
            case $id_state_refund:
                if (Settings::isRefundEnabledByState()) {
                    $this->refund($alma, $id_payment, $order);
                }
                break;
            case $id_state_payment_trigger:
                if (Settings::isPaymentTriggerEnabledByState()) {
                    $this->triggerPayment($alma, $id_payment, $order);
                }
                break;
            default:
                return;
        }
    }

    /**
     * Query Refund
     *
     * @param Client $alma
     * @param string $id_payment
     * @param Order $order
     *
     * @return void
     */
    private function refund($alma, $id_payment, $order)
    {
        try {
            $alma->payments->refund($id_payment, true);
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
     * @param string $id_payment
     * @param Order $order
     *
     * @return void
     */
    private function triggerPayment($alma, $id_payment, $order)
    {
        try {
            $alma->payments->trigger($id_payment);
        } catch (RequestError $e) {
            $msg = "[Alma] ERROR when creating trigger for Order {$order->id}: {$e->getMessage()}";
            Logger::instance()->error($msg);

            return;
        }
    }
}
