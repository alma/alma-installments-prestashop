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

namespace Alma\PrestaShop\Validators;

use Alma\API\Entities\Payment;
use Alma\API\RequestError;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\RefundHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentValidation
{
    /** @var \Context */
    private $context;
    /** @var \PaymentModule */
    private $module;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @param $context
     * @param $module
     */
    public function __construct($context, $module)
    {
        $this->context = $context;
        $this->module = $module;
        $this->settingsHelper = new SettingsHelper();
    }

    /**
     * Check if currency is valid
     *
     * @return bool
     */
    private function isValidCurrency()
    {
        $currencyOrder = new \Currency($this->context->cart->id_currency);
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

    /**
     * @param $almaPaymentId
     *
     * @return string URL to redirect the customer to
     *
     * @throws MismatchException
     * @throws PaymentValidationError
     */
    public function validatePayment($almaPaymentId)
    {
        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            Logger::instance()->error('[Alma] Error instantiating Alma API Client');
            throw new PaymentValidationError(null, 'api_client_init');
        }

        try {
            $payment = $alma->payments->fetch($almaPaymentId);
        } catch (RequestError $e) {
            Logger::instance()->error("[Alma] PaymentValidation Error fetching payment with ID {$almaPaymentId}: {$e->getMessage()}");
            throw new PaymentValidationError(null, $e->getMessage());
        }

        // Check refund in Alma Payment
        if (count($payment->refunds) > 0) {
            $alreadyRefundMessage = '[Alma] PaymentValidation Error payment already refund';
            Logger::instance()->error($alreadyRefundMessage);
            throw new PaymentValidationError(null, $alreadyRefundMessage);
        }

        // Check if cart exists and all fields are set
        $cart = new \Cart($payment->custom_data['cart_id']);
        if (!$cart || 0 == $cart->id_customer || 0 == $cart->id_address_delivery || 0 == $cart->id_address_invoice) {
            Logger::instance()->error("[Alma] Payment validation error: Cart {$cart->id} does not look valid.");
            throw new PaymentValidationError($cart, 'cart_invalid');
        }

        if (!$this->module->active) {
            Logger::instance()->error("[Alma] Payment validation error for Cart {$cart->id}: module not active.");
            throw new PaymentValidationError($cart, 'inactive_module');
        }

        // Check if module is enabled
        $authorized = false;
        foreach (\PaymentModule::getInstalledPaymentModules() as $module) {
            if ($module['name'] == $this->module->name) {
                $authorized = true;
            }
        }

        if (!$authorized) {
            Logger::instance()->error(
                "[Alma] Payment validation error for Cart {$cart->id}: module not enabled anymore."
            );
            throw new PaymentValidationError($cart, 'disabled_module');
        }

        if (!$this->isValidCurrency()) {
            Logger::instance()->error("[Alma] Payment validation error for Cart {$cart->id}: currency mismatch.");
            $msg = $this->module->l('Alma Monthly Installments are not available for this currency', 'paymentvalidation');
            throw new PaymentValidationError($cart, $msg);
        }

        $customer = new \Customer($cart->id_customer);
        if (!\Validate::isLoadedObject($customer)) {
            Logger::instance()->error(
                "[Alma] Payment validation error for Cart {$cart->id}: cannot load Customer {$cart->id_customer}"
            );

            throw new PaymentValidationError($cart, 'cannot load customer');
        }

        if (!$cart->OrderExists()) {
            try {
                $cartTotals = (float) \Tools::ps_round((float) $this->getCartTotals($cart, $customer), 2);
            } catch (\Exception $e) {
                Logger::instance()->warning(
                    "[Alma] Payment validation error with cart total. {$e->getMessage()}"
                );
            }

            if (abs($payment->purchase_amount - PriceHelper::convertPriceToCents($cartTotals)) > 2) {
                $reason = Payment::FRAUD_AMOUNT_MISMATCH;
                $reason .= ' - ' . $cartTotals . ' * 100 vs ' . $payment->purchase_amount;

                try {
                    $alma->payments->flagAsPotentialFraud($almaPaymentId, $reason);
                } catch (RequestError $e) {
                    Logger::instance()->warning('[Alma] Failed to notify Alma of amount mismatch');
                }

                Logger::instance()->error(
                    "[Alma] Payment validation error for Cart {$cart->id}: Purchase amount mismatch!"
                );

                $clientHelper = new ClientHelper();
                $refundHelper = new RefundHelper($this->module, $cart, $payment->id, $clientHelper);
                $refundHelper->mismatchFullRefund();
            }

            $firstInstalment = $payment->payment_plan[0];
            if (!in_array($payment->state, [Payment::STATE_IN_PROGRESS, Payment::STATE_PAID])) {
                try {
                    $alma->payments->flagAsPotentialFraud($almaPaymentId, Payment::FRAUD_STATE_ERROR);
                } catch (RequestError $e) {
                    Logger::instance()->warning('[Alma] Failed to notify Alma of potential fraud');
                }

                Logger::instance()->error(
                    "Payment '{$almaPaymentId}': state error {$payment->state} & {$firstInstalment->state}"
                );

                throw new PaymentValidationError($cart, Payment::FRAUD_STATE_ERROR);
            }

            $extraVars = ['transaction_id' => $payment->id];

            $installmentCount = $payment->installments_count;

            if (SettingsHelper::isDeferred($payment)) {
                $days = $this->settingsHelper->getDuration($payment);
                $paymentMode = sprintf(
                    $this->module->l('Alma - +%d days payment', 'paymentvalidation'),
                    $days
                );
            } else {
                if (1 === $installmentCount) {
                    $paymentMode = $this->module->l('Alma - Pay now', 'paymentvalidation');
                } else {
                    $paymentMode = sprintf(
                        $this->module->l('Alma - %d monthly installments', 'paymentvalidation'),
                        $installmentCount
                    );
                }
            }

            try {
                // Place order
                $this->module->validateOrder(
                    (int) $cart->id,
                    \Configuration::get('PS_OS_PAYMENT'),
                    PriceHelper::convertPriceFromCents($payment->purchase_amount),
                    $paymentMode,
                    null,
                    $extraVars,
                    (int) $cart->id_currency,
                    false,
                    $customer->secure_key
                );
            } catch (\PrestaShopException $e) {
                Logger::instance()->warning("[Alma] Error validation Order: {$e->getMessage()}");
            }

            // Update payment's order reference
            $order = $this->getOrderByCartId((int) $cart->id);
            $customData = $payment->custom_data;
            $customData['id_order'] = $order->id;

            try {
                $alma->payments->edit($payment->id, [
                    'payment' => [
                        'custom_data' => $customData,
                    ],
                ]);
            } catch (RequestError $e) {
                $msg = "[Alma] Error updating order id {$order->id}: {$e->getMessage()}";
                Logger::instance()->error($msg);
            }

            try {
                $alma->payments->addOrder($payment->id, [
                    'merchant_reference' => $order->reference,
                ]);
            } catch (RequestError $e) {
                $msg = "[Alma] Error updating order reference {$order->reference}: {$e->getMessage()}";
                Logger::instance()->error($msg);
            }

            $extraRedirectArgs = '';
        } else {
            $this->module->currentOrder = $this->getOrderByCartId((int) $cart->id)->id;
            $tokenCart = md5(_COOKIE_KEY_ . 'recover_cart_' . $cart->id);
            $extraRedirectArgs = "&recover_cart={$cart->id}&token_cart={$tokenCart}";
        }

        return $this->context->link->getPageLink('order-confirmation', true)
            . '?id_cart=' . (int) $cart->id
            . '&id_module=' . (int) $this->module->id
            . '&id_order=' . (int) $this->module->currentOrder
            . '&key=' . $customer->secure_key
            . $extraRedirectArgs;
    }

    /**
     * @param $cartId
     *
     * @return \OrderCore|null
     */
    private function getOrderByCartId($cartId)
    {
        if (is_callable(['\Order', 'getByCartId'])) {
            return \Order::getByCartId((int) $cartId);
        } else {
            $orderId = (int) \Order::getOrderByCartId((int) $cartId);

            return new \Order($orderId);
        }
    }

    /**
     * We have to temporary update the customer object
     * in context to prevent amount_mismatch error
     * When calculating cart amount from an IPN call.
     *
     * @param \Cart $cart
     * @param \Customer $cart
     *
     * @return float
     */
    private function getCartTotals($cart, $customer)
    {
        if ((int) $this->context->customer->id === (int) $customer->id) {
            return $cart->getOrderTotal(true, \Cart::BOTH);
        }

        $ipnCustomer = $this->context->customer;
        $this->context->customer = $customer;
        $cartTotals = $cart->getOrderTotal(true, \Cart::BOTH);
        $this->context->customer = $ipnCustomer;

        return $cartTotals;
    }
}
