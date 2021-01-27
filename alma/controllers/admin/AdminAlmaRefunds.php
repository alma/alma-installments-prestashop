<?php

use Alma\API\Entities\Payment;
use Alma\API\RequestError;
use Alma\PrestaShop\API\ClientHelper;
use Alma\PrestaShop\Utils\Logger;

/**
 * 2018-2021 Alma SAS
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
 * @copyright 2018-2021 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

class AdminAlmaRefundsController extends ModuleAdminController
{
    protected $json = true;

    protected function ajaxFail($msg = null, $statusCode = 500)
    {
        header("X-PHP-Response-Code: $statusCode", true, $statusCode);
        $this->ajaxDie(json_encode(['error' => true, 'message' => $msg]));
    }

    public function ajaxProcessRefund()
    {
        $refundType = Tools::getValue('refundType');
        $order = new Order(Tools::getValue('orderId'));

        $orderPayment = $this->getCurrentOrderPayment($order);
        if (!$orderPayment) {
            $this->ajaxFail(
                $this->module->l('Error: Could not find Alma transaction', 'AdminAlmaRefundsController')
            );
        }

        $paymentId = $orderPayment->transaction_id;
        if ($refundType == "partial") {
            $isTotal = false;
            $amount = str_replace(',', '.', Tools::getValue('amount'));

            if ($amount > $order->total_paid_tax_incl) {
                $this->ajaxFail(
                    $this->module->l('Error: Amount is higher than maximum refundable', 'AdminAlmaRefundsController'),
                    400
                );
            } elseif ($amount === $order->total_paid_tax_incl) {
                $isTotal = true;
            }
        } else {
            $isTotal = true;
            $amount = $order->total_paid_tax_incl;
        }

        $refundResult = false;
        try {
            $refundResult = $this->runRefund($paymentId, $amount, $isTotal);
        } catch (RequestError $e) {
            $msg = "[Alma] ERROR when creating refund for Order {$order->id}: {$e->getMessage()}";
            Logger::instance()->error($msg);
        }

        if ($refundResult === false) {
            $this->ajaxFail(
                $this->module->l('There was an error while processing the refund', 'AdminAlmaRefundsController')
            );
        } else if ($isTotal) {
            // Mark order as refunded if this was a total refund action
            $current_order_state = $order->getCurrentOrderState();

            if ($current_order_state->id !== 7) {
                $history = new OrderHistory();
                $history->id_order = (int)$order->id;
                $history->changeIdOrderState(7, (int)($order->id));
            }
        }

        $this->ajaxDie(json_encode([
            'success' => true,
            'message' => $this->module->l('Refund has been processed', 'AdminAlmaRefundsController'),
            'paymentData' => $refundResult
        ]));
    }

    private function getCurrentOrderPayment(Order $order)
    {
        $orderPayments = OrderPayment::getByOrderReference($order->reference);
        if ($orderPayments && isset($orderPayments[0])) {
            return $orderPayments[0];
        }

        return false;
    }

    /**
     * @param string $paymentId
     * @param float $amount
     * @param bool $isTotal
     *
     * @return bool | Payment
     *
     * @throws RequestError
     */
    protected function runRefund($paymentId, $amount, $isTotal)
    {
        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            return false;
        }

        return $alma->payments->refund($paymentId, $isTotal, almaPriceToCents($amount));
    }
}
