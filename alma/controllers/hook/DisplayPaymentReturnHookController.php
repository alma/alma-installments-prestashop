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
use Alma\PrestaShop\API\PaymentValidationError;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Utils\Logger;
use Alma\PrestaShop\Utils\Settings;
use OrderPayment;

final class DisplayPaymentReturnHookController extends FrontendHookController
{
    public function run($params)
    {
        $this->context->controller->addCSS($this->module->_path . 'views/css/alma.css', 'all');

        $order = array_key_exists('objOrder', $params) ? $params['objOrder'] : $params['order'];
        $orderPayment = new OrderPayment($order->invoice_number);
        $alma = ClientHelper::defaultInstance();
        $almaPaymentId = $orderPayment->transaction_id;

        try {
            $payment = $alma->payments->fetch($almaPaymentId);
        } catch (RequestError $e) {
            Logger::instance()->error("[Alma] Error fetching payment with ID {$almaPaymentId}: {$e->getMessage()}");
            throw new PaymentValidationError(null, $e->getMessage());
        }

        $this->context->smarty->assign([
            'order_reference' => $order->reference,
            'payment_order' => $orderPayment,
            'payment' => $payment,
        ]);

        return $this->module->display($this->module->file, 'displayPaymentReturn.tpl');
    }
}
