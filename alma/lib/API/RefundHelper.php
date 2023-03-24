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
use \Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class RefundHelper.
 *
 * Use for refund
 */
class RefundHelper
{
    /** @var Alma */
    private $module;
    /** @var Cart */
    private $cart;
    /** @var ClientHelper */
    private $almaClient;
    /** @var int */
    private $paymentId;

    /**
     * construct RefundHelper
     *
     * @param Alma $module
     * @param Cart $cart
     * @param int $paymentId
     */
    public function __construct($module, $cart, $paymentId)
    {
        $this->module = $module;
        $this->cart = $cart;
        $this->almaClient = new ClientHelper();
        $this->paymentId = $paymentId;
    }

    /**
     * Refund for Mismatch
     *
     * @return void
     *
     * @throws RefundException
     * @throws PaymentValidationError
     */
    public function mismatchFullRefund()
    {
        try {
            $msgRefund = $this->module->l('We regret to inform you that there was an issue during the payment process, your Alma payment will be fully refunded. Please retry your payment to complete your order.', 'refundhelper');
            $this->fullRefund($this->paymentId, '', 'Refund after Mismatch - cart ID : ' . $this->cart->id);
        } catch (RefundException $e) {
            Logger::instance()->error('[Alma] RefundMismatch Error - ' . $e->getMessage());
            $msgRefund = sprintf(
                $this->module->l('We apologize for the inconvenience, but there was an issue during the payment process, and we were unable to refund your Alma payment. To fix this, we kindly ask you to contact our support team with your payment reference: %s. Our team will be happy to assist you in ensuring that you receive your full refund. Thank you for your cooperation.', 'refundhelper'),
                $e->getPaymentId()
            );
        }

        throw new PaymentValidationError($this->cart, $msgRefund);
    }

    /**
     * Make fullRefund
     *
     * @param $id
     * @param string $merchantReference
     * @param string $comment
     * @return void
     *
     * @throws RefundException
     */
    public function fullRefund($id, $merchantReference = "", $comment = "")
    {
        try {
            $this->almaClient->getAlmaClient()->payments->fullRefund($id, $merchantReference, $comment);
        } catch (Exception $e) {
            Logger::instance()->error('[Alma] fullRefund Error - ' . $e->getMessage());

            throw new RefundException($id, $e->getMessage(), $e);
        }
    }
}
