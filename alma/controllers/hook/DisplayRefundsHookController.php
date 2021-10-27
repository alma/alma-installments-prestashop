<?php

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

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\API\ClientHelper;
use Media;
use Order;
use OrderPayment;
use Currency;
use Alma\PrestaShop\Hooks\AdminHookController;

final class DisplayRefundsHookController extends AdminHookController
{

    /** @var Order */
    public $order;

    public function canRun()
    {
        return parent::canRun();
    }

    public function run($params)
    {
        $order = new Order($params['id_order']);
        if ($order->module !== "alma") {
            return;
        }

        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            return false;
        }

        $orderPayment = $this->getCurrentOrderPayment($order);
        if (!$orderPayment) {
            $this->ajaxFail(
                $this->module->l('Error: The Alma transaction was not found', 'AdminAlmaRefundsController')
            );
        }

        $paymentId = $orderPayment->transaction_id;

        $payment = $alma->payments->fetch($paymentId);

        if (is_callable('Media::getMediaPath')) {
            $iconPath = Media::getMediaPath(_PS_MODULE_DIR_ . $this->module->name . '/views/img/logos/alma_tiny.svg');
        } else {
            $iconPath = $this->module->getPathUri() . '/views/img/logos/alma_tiny.svg';
        }

        $refundData = null;
        $totalRefund = null;
        $percentRefund = null;
        if ($payment->refunds) {
            foreach($payment->refunds as $refund) {
                $totalRefund += $refund->amount;
            }

            $percentRefund = (100 / $order->getOrdersTotalPaid()) * almaPriceFromCents($totalRefund);
        
            $refundData = [
                'totalRefundAmount' => almaFormatPrice($totalRefund, (int)$order->id_currency),
                'percentRefund' => $percentRefund,
            ];
        }

        //multi shipping
        $ordersId = null;
        $fees = almaPriceFromCents($payment->customer_fee);
        $ordersTotalAmount = $order->total_paid_tax_incl + $fees;
        if ($order->getOrdersTotalPaid() > $order->total_paid_tax_incl) {
            $orders = Order::getByReference($order->reference);
            foreach ($orders as $o) {
                if ($o->id != $order->id) {
                    $ordersId .= "{$o->id},";
                }
            }
            $ordersId = rtrim($ordersId, ',');
            $ordersTotalAmount = $order->getOrdersTotalPaid() + $fees;
        }

        $currency = new Currency($order->id_currency);
        $orderData = [
            'id' => $order->id,
            'maxAmount' => almaFormatPrice(almaPriceToCents($order->total_paid_tax_incl), (int)$order->id_currency),
            'currencySymbol' => $currency->sign,
            'ordersId' => $ordersId,
            'ordersTotalAmount' => almaFormatPrice(almaPriceToCents($ordersTotalAmount), (int)$order->id_currency),
        ];

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $refundTpl = "order_refund_ps15";
        } elseif (version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
            $refundTpl = "order_refund_bs3";
        } else {
            $refundTpl = "order_refund_bs4";
        }

        $tpl = $this->context->smarty->createTemplate(
            "{$this->module->local_path}views/templates/hook/{$refundTpl}.tpl"
        );

        $tpl->assign([
            'iconPath' => $iconPath,
            'order' => $orderData,
            'refund' => $refundData,
            'actionUrl' => $this->context->link->getAdminLink('AdminAlmaRefunds')
        ]);

        return $tpl->fetch();
    }

    private function getCurrentOrderPayment(Order $order)
    {
        $orderPayments = OrderPayment::getByOrderReference($order->reference);
        if ($orderPayments && isset($orderPayments[0])) {
            return $orderPayments[0];
        }

        return false;
    }
}
