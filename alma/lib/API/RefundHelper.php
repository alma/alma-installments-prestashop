<?php
/**
 * 2018-2023 Alma SAS
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

namespace Alma\PrestaShop\API;

use Alma\PrestaShop\Utils\Logger;
use Cart;

if (!defined('_PS_VERSION_')) {
    exit;
}

class RefundHelper
{
    /** @var int */
    private $paymentId;
    /** @var PaymentModule */
    private $module;
    /** @var Cart */
    private $cart;

    public function __construct($paymentId, $module, $cart)
    {
        $this->paymentId = $paymentId;
        $this->module = $module;
        $this->cart = $cart;
    }

    public function forMismatch()
    {
        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            $msg = '[Alma] Error instantiating Alma API Client for Refund';
            Logger::instance()->error($msg);
            throw new RefundException($msg);
        }

        try {
            $alma->payments->fullRefund($this->paymentId);
            $msgRefund = $this->module->l('We regret to inform you that there was an issue during the payment process, your Alma payment will be fully refunded. Please retry your payment to complete your order.', 'refundhelper');
        } catch (RefundException $e) {
            Logger::instance()->error('[Alma] RefundMismatch Error - ' . $e->getMessage());
            $msgRefund = sprintf(
                $this->module->l('We apologize for the inconvenience, but there was an issue during the payment process, and we were unable to refund your Alma payment. To fix this, we kindly ask you to contact our support team with your payment reference: "%s". Our team will be happy to assist you in ensuring that you receive your full refund. Thank you for your cooperation.', 'refundhelper'),
                $this->paymentId
            );
        }

        throw new PaymentValidationError($this->cart, $msgRefund);
    }
}