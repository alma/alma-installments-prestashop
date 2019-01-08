<?php
/**
 * 2018 Alma / Nabla SAS
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
 * @author    Alma / Nabla SAS <contact@getalma.eu>
 * @copyright 2018 Alma / Nabla SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_ . 'alma/includes/functions.php');
include_once(_PS_MODULE_DIR_ . 'alma/includes/AlmaLogger.php');
include_once(_PS_MODULE_DIR_ . 'alma/includes/AlmaClient.php');

use Alma\API\Entities\Payment;
use Alma\Api\Entities\Instalment;
use Alma\API\RequestError;

class AlmaValidationModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
    }

    private function checkCurrency()
    {
        $currencyOrder = new Currency($this->context->cart->id_currency);
        $currenciesModule = $this->module->getCurrency($this->context->cart->id_currency);

        // Check if cart currency is one of the enabled currencies
        if (is_array($currenciesModule)) {
            foreach ($currenciesModule as $currencyModule) {
                if ($currencyOrder->id == $currencyModule['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    private function fail($cart, $msg = null)
    {
        if (!$msg) {
            $msg = sprintf(
                $this->module->l(
                    'There was an error while validating your payment. ' .
                    'Please try again or contact us if the problem persists. Cart ID: %d',
                    'validation'
                ),
                (int)$cart ? $cart->id : -1
            );
        }
        
        $this->context->cookie->__set('alma_error', $msg);
        Tools::redirect('index.php?controller=order&step=1');
    }

    public function initContent()
    {
        parent::initContent();

        $alma = AlmaClient::defaultInstance();
        if (!$alma) {
            AlmaLogger::instance()->error("[Alma] Error instantiating Alma API Client");
            $this->fail(null);
            return;
        }

        $paymentId = Tools::getValue('pid');
        try {
            $payment = $alma->payments->fetch($paymentId);
        } catch (RequestError $e) {
            AlmaLogger::instance()->error("[Alma] Error fetching payment with ID {$paymentId}: {$e->getMessage()}");
            $this->fail(null);
            return;
        }

        // Check if cart exists and all fields are set
        $cart = new Cart($payment->custom_data['cart_id']);
        if (!$cart || $cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0) {
            AlmaLogger::instance()->error("[Alma] Payment validation error: Cart {$cart->id} does not look valid.");
            $this->fail($cart);
            return;
        }

        if (!$this->module->active) {
            AlmaLogger::instance()->error("[Alma] Payment validation error for Cart {$cart->id}: module not active.");
            $this->fail($cart);
            return;
        }

        // Check if module is enabled
        $authorized = false;
        foreach (PaymentModule::getInstalledPaymentModules() as $module) {
            if ($module['name'] == $this->module->name) {
                $authorized = true;
            }
        }

        if (!$authorized) {
            AlmaLogger::instance()->error("[Alma] Payment validation error for Cart {$cart->id}: module not enabled anymore.");
            $this->fail($cart);
            return;
        }

        if (!$this->checkCurrency()) {
            AlmaLogger::instance()->error("[Alma] Payment validation error for Cart {$cart->id}: currency mismatch.");
            $msg = $this->module->l('Alma monthly payments are not available for this currency', 'payment');
            $this->fail($cart, $msg);
            return;
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            AlmaLogger::instance()->error(
                "[Alma] Payment validation error for Cart {$cart->id}: " .
                "cannot load Customer {$cart->id_customer}"
            );

            $this->fail($cart);
            return;
        }

        if (!$cart->OrderExists()) {
            $purchaseAmount = alma_price_to_cents((float)$cart->getOrderTotal(true, Cart::BOTH));
            if ($payment->purchase_amount !== $purchaseAmount) {
                AlmaLogger::instance()->error("[Alma] Payment validation error for Cart {$cart->id}: Purchase amount mismatch!");
                $this->fail($cart);
                return;
            }

            $first_instalment = $payment->payment_plan[0];
            if (!in_array($payment->state, array(Payment::STATE_IN_PROGRESS, Payment::STATE_PAID)) || $first_instalment->state !== Instalment::STATE_PAID) {
                AlmaLogger::instance()->error("Payment '{$paymentId}': state incorrect {$payment->state} & {$first_instalment->state}");
                $this->fail($cart);
                return;
            }

            $extra_vars = array('payment_id' => $payment->id);
            $payment_mode =  sprintf(
                $this->module->l('Alma - %d monthly payments', 'validation'),
                count($payment->payment_plan)
            );

            $this->module->validateOrder(
                (int)$cart->id,
                Configuration::get('PS_OS_PAYMENT'),
                alma_price_from_cents($purchaseAmount),
                $payment_mode,
                null,
                $extra_vars,
                (int)$cart->id_currency,
                false,
                $customer->secure_key
            );

            $extraRedirectArgs = '';
        } else {
            if (is_callable(array('Order', 'getByCartId'))) {
                $order = Order::getByCartId((int)$cart->id);
                $this->module->currentOrder = (int)$order->id;
            } else {
                $this->module->currentOrder = (int)Order::getOrderByCartId((int)$cart->id);
            }

            $token_cart = md5(_COOKIE_KEY_ . 'recover_cart_' . $cart->id);
            $extraRedirectArgs = "&recover_cart={$cart->id}&token_cart={$token_cart}";
        }

        Tools::redirect(
            $this->context->link->getPageLink('order-confirmation', true) .
            '?id_cart='   . (int)$cart->id .
            '&id_module=' . (int)$this->module->id .
            '&id_order='  . (int)$this->module->currentOrder .
            '&key='       . $customer->secure_key .
            $extraRedirectArgs
        );
    }
}
