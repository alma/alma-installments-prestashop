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

use Alma\API\RequestError;
use Alma\PrestaShop\Exceptions\RenderPaymentException;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Model\OrderData;

class DisplayPaymentReturnHookController extends FrontendHookController
{
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
                Logger::instance()->error("[Alma] DisplayPaymentReturn Error fetching payment with ID {$almaPaymentId}: {$e->getMessage()}");
                throw new RenderPaymentException($e->getMessage());
            }
        }

        $this->context->smarty->assign([
            'order_reference' => $order->reference,
            'payment_order' => $orderPayment,
            'payment' => $payment,
        ]);

        return $this->module->display($this->module->file, 'displayPaymentReturn.tpl');
    }
}
