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

use Alma\API\RequestError;
use Alma\PrestaShop\API\ClientHelper;
use Alma\PrestaShop\Exceptions\RenderPaymentException;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Model\OrderData;
use Alma\PrestaShop\Utils\Logger;
use Alma\PrestaShop\Utils\OrderDataTrait;

class DisplayPaymentReturnHookController extends FrontendHookController
{
    use OrderDataTrait;

    public function run($params)
    {
        $this->context->controller->addCSS($this->module->_path . 'views/css/alma.css', 'all');

        $payment = null;
        $order = array_key_exists('objOrder', $params) ? $params['objOrder'] : $params['order'];
        $orderPayment = OrderData::getCurrentOrderPayment($order);
        if (!$orderPayment) {
            $msg = '[Alma] orderPayment not found';
            Logger::instance()->error($msg);
            throw new RenderPaymentException($msg);
        }
        $almaPaymentId = $orderPayment->transaction_id;
        if (!$almaPaymentId) {
            $msg = '[Alma] Payment_id not found';
            Logger::instance()->error($msg);
            throw new RenderPaymentException($msg);
        }
        $alma = ClientHelper::defaultInstance();
        if ($alma) {
            try {
                $payment = $alma->payments->fetch($almaPaymentId);
            } catch (RequestError $e) {
                Logger::instance()->error("[Alma] Error fetching payment with ID {$almaPaymentId}: {$e->getMessage()}");
                throw new RenderPaymentException($e->getMessage());
            }
        }

        $wording = [
            'paymentAlmaSuccessful' => $this->module->l('Your payment with Alma was successful', 'DisplayPaymentReturnHookController'),
            'orderReference' => sprintf(
                $this->module->l('Here is your order reference: %1$s', 'DisplayPaymentReturnHookController'),
                $order->reference
            ),
            'detailsPayment' => sprintf(
                $this->module->l('Details for your payment: %1$s%2$s%3$s', 'DisplayPaymentReturnHookController'),
                '<b>',
                $orderPayment->payment_method,
                '</b>'
            ),
            'today' => $this->module->l('Today', 'DisplayPaymentReturnHookController'),
            'atShipping' => $this->module->l('At shipping', 'DisplayPaymentReturnHookController'),
            'youReceiveConfirmationEmail' => $this->module->l('You should receive a confirmation email shortly', 'DisplayPaymentReturnHookController'),
            'toFollowPaymentLinkAlma' => sprintf(
                // phpcs:ignore Generic.Files.LineLength
                $this->module->l('To check your payment\'s progress, change you card or pay in advance: %1$sclick here%2$s', 'DisplayPaymentReturnHookController'),
                // phpcs:ignore Generic.Files.LineLength
                '<a href="' . $payment->url . '" target="_blank" title="' . $this->module->l('follow its deadlines', 'DisplayPaymentReturnHookController') . '">',
                '</a>'
            ),
            'weAppreciateBusiness' => $this->module->l('We appreciate your business', 'DisplayPaymentReturnHookController'),
        ];

        foreach($payment->payment_plan as $plan) {
            $plan->textIncludinfFees = sprintf(
                $this->module->l('(Including fees: %1$s)', 'DisplayPaymentReturnHookController'),
                almaFormatPrice($plan->customer_fee)
            );
        }

        $this->context->smarty->assign([
            'order_reference' => $order->reference,
            'payment_order' => $orderPayment,
            'payment' => $payment,
            'wording' => $wording,
        ]);

        return $this->module->display($this->module->file, 'displayPaymentReturn.tpl');
    }
}
