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

use Alma\API\RequestError;
use Alma\PrestaShop\API\ClientHelper;
use Alma\PrestaShop\Utils\AjaxTrait;
use Alma\PrestaShop\Utils\Logger;
use Alma\PrestaShop\Utils\OrderDataTrait;

// TODO: Autoloader Trait Automatically
if (version_compare(_PS_VERSION_, '1.7', '<')) {
    require_once(_PS_MODULE_DIR_ . 'alma/lib/Utils/AjaxTrait.php');
    require_once(_PS_MODULE_DIR_ . 'alma/lib/Utils/OrderDataTrait.php');
}
class AdminAlmaRefundsController extends ModuleAdminController
{
    use AjaxTrait, OrderDataTrait {
        AjaxTrait::ajaxFail insteadof OrderDataTrait;
    }

    /**
     * If set to true, page content and messages will be encoded to JSON before responding to AJAX request.
     *
     * @var bool
     * @override
     */
    protected $json = true;

    /**
     * Make refund over ajax request and display json on std output
     *
     * @return void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function ajaxProcessRefund()
    {
        $refundType = Tools::getValue('refundType');
        $order = new Order(Tools::getValue('orderId'));
        $orderPayment = $this->getOrderPaymentOrFail($order);
        $paymentId = $orderPayment->transaction_id;

        switch ($refundType) {
            case 'partial_multi':
                $isTotal = false;
                $amount = $order->total_paid_tax_incl;
                break;
            case 'partial':
                $isTotal = false;
                $amount = str_replace(',', '.', Tools::getValue('amount'));

                if ($amount > $order->getOrdersTotalPaid()) {
                    $this->ajaxFail(
                        $this->module->l('Error: Amount is higher than maximum refundable', 'AdminAlmaRefunds'),
                        400
                    );
                }
                break;
            case 'total':
                $isTotal = true;
                $amount = $order->getOrdersTotalPaid();
                break;
            default:
                $msg = sprintf(
                    $this->module->l('Error: unknow refund type (%s)', 'AdminAlmaRefunds'),
                    $refundType
                );
                Logger::instance()->error($msg);
                $this->ajaxFail($msg, 400);
        }

        $refundResult = false;
        $percentRefund = null;
        $totalRefund = null;
        $totalRefundAmount = null;
        $totalOrder = null;
        $totalOrderAmount = null;
        try {
            $refundResult = $this->runRefund($paymentId, $amount, $isTotal);
        } catch (RequestError $e) {
            $msg = "[Alma] ERROR when creating refund for Order {$order->id}: {$e->getMessage()}";
            Logger::instance()->error($msg);
        }

        if ($refundResult === false) {
            $this->ajaxFail(
                $this->module->l('There was an error while processing the refund', 'AdminAlmaRefunds')
            );
        } else {
            $fees = $refundResult->customer_fee;
            $totalOrder = $refundResult->purchase_amount + $fees;
            $totalOrderAmount = almaFormatPrice($totalOrder, (int) $order->id_currency);
            foreach ($refundResult->refunds as $refund) {
                $totalRefund += $refund->amount;
            }
            $totalRefundAmount = almaFormatPrice($totalRefund, (int) $order->id_currency);
            $percentRefund = (100 / $totalOrder) * $totalRefund;
        }

        if ($isTotal) {
            $orders = Order::getByReference($order->reference);
            foreach ($orders as $o) {
                $current_order_state = $o->getCurrentOrderState();
                if ($current_order_state->id !== (int) Configuration::get('PS_OS_REFUND')) {
                    $o->setCurrentState(Configuration::get('PS_OS_REFUND'));
                }
            }
        }

        $jsonReturn = [
            'success' => true,
            'message' => $this->module->l('Refund has been processed', 'AdminAlmaRefunds'),
            'paymentData' => $refundResult,
            'percentRefund' => $percentRefund,
            'totalRefund' => $totalRefund,
            'totalRefundAmount' => $totalRefundAmount,
            'totalOrder' => $totalOrder,
            'totalOrderAmount' => $totalOrderAmount,
        ];

        method_exists(get_parent_class($this), 'ajaxDie')
            ? $this->ajaxDie(json_encode($jsonReturn))
            : die(Tools::jsonEncode($jsonReturn));
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
