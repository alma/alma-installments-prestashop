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

use Alma\API\Entities\Payment;
use Alma\API\RequestError;
use Alma\PrestaShop\Builders\Helpers\PriceHelperBuilder;
use Alma\PrestaShop\Exceptions\PaymentNotFoundException;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\OrderHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\RefundHelper;
use Alma\PrestaShop\Hooks\AdminHookController;
use Alma\PrestaShop\Logger;

final class DisplayRefundsHookController extends AdminHookController
{
    /** @var \Order */
    public $order;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @codeCoverageIgnore
     *
     * @param $module
     */
    public function __construct($module)
    {
        parent::__construct($module);

        $priceHelperBuilder = new PriceHelperBuilder();
        $this->priceHelper = $priceHelperBuilder->getInstance();
    }

    /**
     * Run Hook for show block Refund in Order page if is payment Alma
     *
     * @param array $params
     *
     * @return string|null as the template fetched
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \SmartyException
     */
    public function run($params)
    {
        $order = new \Order($params['id_order']);
        try {
            $payment = $this->getPayment($order);
        } catch (PaymentNotFoundException $e) {
            // if we can't have the payment, log why and return null
            Logger::instance()->warning('[Alma] DisplayRefounds Error - ' . $e->getMessage());

            return null;
        }

        $refundData = [
            'totalRefundPrice' => 0,
            'percentRefund' => 0,
        ];
        $totalRefundInCents = null;
        $percentRefund = null;
        $orderTotalPaid = $order->getOrdersTotalPaid();
        $paymentTotalAmount = $order->total_paid_tax_incl;

        // multi shipping
        $ordersId = null;
        if ($orderTotalPaid > $order->total_paid_tax_incl) {
            $orders = \Order::getByReference($order->reference);
            foreach ($orders as $o) {
                if ($o->id != $order->id) {
                    $ordersId .= "{$o->id},";
                }
            }
            $ordersId = rtrim($ordersId, ',');
            $paymentTotalAmount = $orderTotalPaid;
        }

        $totalOrderInCents = $this->priceHelper->convertPriceToCents($paymentTotalAmount);
        if ($payment->refunds) {
            $totalRefundInCents = RefundHelper::buildTotalRefund($payment->refunds, $totalOrderInCents);
            $percentRefund = PriceHelper::calculatePercentage($totalRefundInCents, $totalOrderInCents);

            $refundData = [
                'totalRefundPrice' => $this->priceHelper->formatPriceToCentsByCurrencyId(
                    $totalRefundInCents,
                    (int) $order->id_currency
                ),
                'percentRefund' => $percentRefund,
            ];
        }

        $currency = new \Currency($order->id_currency);
        $orderData = [
            'id' => $order->id,
            'maxAmount' => $this->priceHelper->formatPriceToCentsByCurrencyId(
                $this->priceHelper->convertPriceToCents($order->total_paid_tax_incl),
                (int) $order->id_currency
            ),
            'currencySymbol' => $currency->sign,
            'ordersId' => $ordersId,
            'paymentTotalPrice' => $this->priceHelper->formatPriceToCentsByCurrencyId(
                $totalOrderInCents,
                (int) $order->id_currency
            ),
        ];
        $wording = [
            'title' => $this->module->l('Alma refund', 'DisplayRefundsHookController'),
            'description' => sprintf(
                $this->module->l('Refund this order thanks to the Alma module. This will be applied in your Alma dashboard automatically. The maximum refundable amount includes client fees. %1$sSee documentation%2$s', 'DisplayRefundsHookController'),
                '<a href="https://docs.getalma.eu/docs/prestashop-refund" target="_blank">',
                '</a>'
            ),
            'labelTypeRefund' => $this->module->l('Refund type:', 'DisplayRefundsHookController'),
            'labelRadioRefundOneOrder' => sprintf(
                $this->module->l('Only this order (%s)', 'DisplayRefundsHookController'),
                $orderData['maxAmount']
            ),
            'labelRadioRefundAllOrder' => $this->module->l('Refund the entire order', 'DisplayRefundsHookController'),
            'labelRadioRefundAllOrderInfoId' => sprintf(
                $this->module->l('Refund this order (id: %1$d) and all those linked to the same payment (id: %2$s)', 'DisplayRefundsHookController'),
                $orderData['id'],
                $orderData['ordersId']
            ),
            'labelRadioRefundAllOrderInfoAmount' => sprintf(
                $this->module->l('Total amount: %s', 'DisplayRefundsHookController'),
                $orderData['paymentTotalPrice']
            ),
            'labelRadioRefundTotalAmout' => $this->module->l('Total amount', 'DisplayRefundsHookController'),
            'labelRadioRefundPartial' => $this->module->l('Partial', 'DisplayRefundsHookController'),
            'labelAmoutRefundPartial' => sprintf(
                $this->module->l('Amount (Max. %s):', 'DisplayRefundsHookController'),
                $orderData['paymentTotalPrice']
            ),
            'placeholderInputRefundPartial' => $this->module->l('Amount to refund...', 'DisplayRefundsHookController'),
            'buttonRefund' => $this->module->l('Proceed the refund', 'DisplayRefundsHookController'),
        ];

        $tpl = $this->context->smarty->createTemplate(
            "{$this->module->local_path}views/templates/hook/" . $this->getTemplateName() . '.tpl'
        );

        $tpl->assign([
            'iconPath' => $this->module->getPathUri() . '/views/img/logos/alma_tiny.svg',
            'wording' => $wording,
            'order' => $orderData,
            'refund' => $refundData,
            'actionUrl' => $this->context->link->getAdminLink('AdminAlmaRefunds'),
        ]);

        return $tpl->fetch();
    }

    /**
     * Runs Hook for show block Refund in Order page if is payment Alma.
     *
     * @param \Order $order
     *
     * @return Payment
     *
     * @throws PaymentNotFoundException
     * @throws \PrestaShopException
     */
    private function getPayment($order)
    {
        $alma = ClientHelper::defaultInstance();
        if ($order->module !== 'alma' || !$alma) {
            throw new PaymentNotFoundException('Alma is not available');
        }
        $orderHelper = new OrderHelper();
        $orderPayment = $orderHelper->ajaxGetOrderPayment($order);
        $paymentId = $orderPayment->transaction_id;
        if (empty($paymentId)) {
            throw new PaymentNotFoundException("[Alma] paymentId doesn't exist");
        }
        try {
            return $alma->payments->fetch($paymentId);
        } catch (RequestError $e) {
            throw new PaymentNotFoundException("[Alma] Can't get payment with this payment_id : $paymentId");
        }
    }

    /**
     * Get the name of file for template block refund order page
     *
     * @return string as template name
     */
    private function getTemplateName()
    {
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            return 'order_refund_ps15';
        }
        if (version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
            return 'order_refund_bs3';
        }

        return 'order_refund_bs4';
    }
}
