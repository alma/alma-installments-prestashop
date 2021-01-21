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

use Alma\API\RequestError;
use Alma\PrestaShop\API\ClientHelper;
use Alma\PrestaShop\Hooks\AdminHookController;
use Alma\PrestaShop\Utils\Logger;
use Order;
use OrderDetail;
use OrderPayment;
use Tax;
use TaxCalculator;
use Tools;

final class RefundHookController extends AdminHookController
{
    public function run($params)
    {
        // When a discount refund is generated, we *must not* refund the customer via Alma
        if (Tools::isSubmit('generateDiscountRefund') || Tools::isSubmit('generateDiscount')) {
            return;
        }

        $order = $params['order'];
        if (!$order_payment = $this->getCurrentOrderPayment($order)) {
            return;
        }
        $id_payment = $order_payment->transaction_id;
        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $this->runAfter16($params, $id_payment);
        } elseif (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->runBefore16($params, $id_payment);
        }
    }

    protected function runAfter16($params, $id_payment)
    {
        $order = $params['order'];
        $amount = 0;
        $order_detail_list = [];

        $refunds = Tools::getValue('partialRefundProduct');
        foreach ($refunds as $id_order_detail => $amount_detail) {
            $quantity = Tools::getValue('partialRefundProductQuantity');
            if (!$quantity[$id_order_detail]) {
                continue;
            }

            $order_detail_list[$id_order_detail] = [
                'quantity' => (int) $quantity[$id_order_detail],
                'id_order_detail' => (int) $id_order_detail,
            ];

            $order_detail = new OrderDetail((int) $id_order_detail);
            if (empty($amount_detail)) {
                $order_quantity = $order_detail_list[$id_order_detail]['quantity'];
                $order_detail_list[$id_order_detail]['amount'] = $order_detail->unit_price_tax_incl * $order_quantity;
            } else {
                $order_detail_list[$id_order_detail]['amount'] = (float) str_replace(',', '.', $amount_detail);
            }
            $amount += $order_detail_list[$id_order_detail]['amount'];
        }

        if ((int) Tools::getValue('refund_voucher_off') == 1) {
            $amount -= (float) Tools::getValue('order_discount_price');
        } elseif ((int) Tools::getValue('refund_voucher_off') == 2) {
            $amount = (float) Tools::getValue('refund_voucher_choose');
        }

        $shipping_cost_amount = (float) str_replace(',', '.', Tools::getValue('partialRefundShippingCost')) ?: false;

        if ($shipping_cost_amount > 0) {
            if (!Tools::getValue('TaxMethod')) {
                $tax = new Tax();
                $tax->rate = $order->carrier_tax_rate;
                $tax_calculator = new TaxCalculator([$tax]);
                $amount += $tax_calculator->addTaxes($shipping_cost_amount);
            } else {
                $amount += $shipping_cost_amount;
            }
        }

        $is_total = $amount == $order->total_paid_tax_incl;
        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            return;
        }
        try {
            $alma->payments->refund($id_payment, $is_total, almaPriceToCents($amount));
        } catch (RequestError $e) {
            $msg = "[Alma] ERROR when creating refund for Order {$order->id}: {$e->getMessage()}";
            Logger::instance()->error($msg);

            return;
        }
    }

    protected function runBefore16($params, $id_payment)
    {
        $order = $params['order'];
        $qtyList = $params['qtyList'];
        $products = $order->getProducts();
        $amount = 0;

        foreach ($qtyList as $id_order_detail => $qty) {
            $amount = $products[$id_order_detail]['unit_price_tax_incl'] * $qty;
        }
        $alma = ClientHelper::defaultInstance();

        if (!$alma) {
            return;
        }

        $is_total = $amount == $order->total_paid_tax_incl;

        try {
            $alma->payments->refund($id_payment, $is_total, almaPriceToCents($amount));
        } catch (RequestError $e) {
            $msg = "[Alma] ERROR when creating refund for Order {$order->id}: {$e->getMessage()}";
            Logger::instance()->error($msg);

            return;
        }
    }

    private function getCurrentOrderPayment(Order $order)
    {
        if ('alma' != $order->module && 1 == $order->valid) {
            return false;
        }
        $order_payments = OrderPayment::getByOrderReference($order->reference);
        if ($order_payments && isset($order_payments[0])) {
            return $order_payments[0];
        }

        return false;
    }
}
