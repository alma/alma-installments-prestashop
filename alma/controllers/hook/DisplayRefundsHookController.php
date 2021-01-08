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

use Media;
use Order;
use Tools;
use Currency;
use OrderHistory;
use OrderPayment;
use Alma\PrestaShop\API\ClientHelper;
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

        $this->order = new Order($params['id_order']);

        if ($this->order->module === "alma" && 1 == $this->order->valid) {
            $this->process();
            $this->displayRefunds();
        }
    }

    private function displayRefunds()
    {
        if (is_callable('Media::getMediaPath')) {
            $iconPath = Media::getMediaPath(_PS_MODULE_DIR_ . $this->module->name . '/views/img/logos/alma_tiny.svg');
        } else {
            $iconPath = $this->module->getPathUri() . '/views/img/logos/alma_tiny.svg';
        }

        $currency = new Currency($this->order->id_currency);

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $refundTpl  = "order_refund_15";
        } else {
            $refundTpl  = "order_refund";
        }

        $orderData = [
            'id'                => $this->order->id,
            'reference'         => $this->order->reference,
            'maxAmount'         => almaFormatPrice(almaPriceToCents($this->order->total_paid_tax_incl), (int)$this->order->id_currency),
            'currencySymbole'   => $currency->sign,
        ];

        $tpl = $this->context->smarty->createTemplate(
            "{$this->module->local_path}views/templates/hook/{$refundTpl}.tpl"
        );

        $tpl->assign([
            'iconPath'  => $iconPath,
            'order'     => $orderData,
        ]);

        echo $tpl->fetch();
    }


    private function getCurrentOrderPayment(Order $order)
    {

        $order_payments = OrderPayment::getByOrderReference($order->reference);
        if ($order_payments && isset($order_payments[0])) {
            return $order_payments[0];
        }

        return false;
    }


    protected function runRefund($id_payment, $amount, $is_total)
    {
        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            return false;
        }
        try {
            $alma->payments->refund($id_payment, $is_total, almaPriceToCents($amount));
            $this->success = sprintf($this->module->l('Your refund for Order %d has been made !', 'displayRefunds'), $this->order->id);
            return true;
        } catch (RequestError $e) {
            $msg = "[Alma] ERROR when creating refund for Order {$this->sorder->id}: {$e->getMessage()}";
            $this->error = sprintf($this->module->l('ERROR when creating refund for order %d', 'displayRefunds'), $this->order->id);
            AlmaLogger::instance()->error($msg);

            return false;
        }
    }

    private function process()
    {

        if (Tools::isSubmit('refundType') && Tools::isSubmit('orderID')) {
            $refundType = Tools::getValue('refundType');
            if (!$order_payment = $this->getCurrentOrderPayment($this->order)) {
                return;
            }

            $id_payment = $order_payment->transaction_id;
            if ($refundType == "partial") {
                $amount = Tools::getValue('amount');
                $is_total = false;
                if ($amount > $this->order->total_paid_tax_incl) {
                    $this->error = $this->module->l('ERROR: Amount is too high', 'displayRefunds');

                    return;
                } elseif ($amount === $this->order->total_paid_tax_incl) {
                    $is_total = true;
                }
            } else {
                $amount = $this->order->total_paid_tax_incl;
                $is_total = true;
            }
            if ($this->runRefund($id_payment, $amount, $is_total) && $is_total === true) {
                $current_order_state = $this->order->getCurrentOrderState();
                if ($current_order_state->id !== 7) {
                    $history = new OrderHistory();
                    $history->id_order = (int)$this->order->id;
                    $history->changeIdOrderState(7, (int)($this->order->id));
                }
            }

            Tools::redirectAdmin("{$this->context->link->getAdminLink('AdminOrders')}&id_order={$this->order->id}&vieworder");
        }
    }
}
