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

use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Validators\PaymentValidation;
use Alma\PrestaShop\Validators\PaymentValidationError;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AlmaValidationModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
    }

    private function fail($cart, $msg = null)
    {
        if (!$msg) {
            $msg = sprintf(
                $this->module->l('There was an error while validating your payment. Please try again or contact us if the problem persists. Cart ID: %d', 'validation'),
                (int) $cart ? $cart->id : -1
            );
        }

        $this->context->cookie->__set('alma_error', $msg);

        return 'index.php?controller=order&step=1';
    }

    /**
     * @return void
     */
    public function postProcess()
    {
        parent::postProcess();

        $paymentId = Tools::getValue('pid');
        $validator = new PaymentValidation($this->context, $this->module);

        try {
            $redirect_to = $validator->validatePayment($paymentId);
        } catch (PaymentValidationError $e) {
            Logger::instance()->error('payment_validation_error - Message : ' . $e->getMessage());
            $redirect_to = $this->fail($e->cart, $e->getMessage());
        } catch (Exception $e) {
            Logger::instance()->error('payment_error - Message : ' . $e->getMessage());
            $redirect_to = $this->fail(null, $e->getMessage());
        }

        if (is_callable([$this, 'setRedirectAfter'])) {
            $this->setRedirectAfter($redirect_to);
        } else {
            Tools::redirect($redirect_to);
        }
    }
}
