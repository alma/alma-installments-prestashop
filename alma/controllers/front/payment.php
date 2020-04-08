<?php
/**
 * 2018-2020 Alma SAS
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
 * @copyright 2018-2020 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

use Alma\API\RequestError;

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'alma/includes/PaymentData.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaClient.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaLogger.php';

class AlmaPaymentModuleFrontController extends ModuleFrontController
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

    private function genericErrorAndRedirect()
    {
        $msg = $this->module->l(
            'There was an error while generating your payment request. ' .
            'Please try again later or contact us if the problem persists.',
            'payment'
        );
        $this->context->cookie->__set('alma_error', $msg);
        Tools::redirect('index.php?controller=order&step=1');
    }

    public function postProcess()
    {
        // Check if cart exists and all fields are set
        if (!$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');

            return;
        }

        // Check if module is enabled
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == $this->module->name) {
                $authorized = true;
            }
        }

        if (!$authorized) {
            AlmaLogger::instance()->warning('[Alma] Not authorized!');
            Tools::redirect('index.php?controller=order&step=1');

            return;
        }

        if (!$this->checkCurrency()) {
            $msg = $this->module->l('Alma Monthly Installments are not available for this currency', 'payment');
            $this->context->cookie->__set('alma_error', $msg);
            Tools::redirect('index.php?controller=order&step=1');

            return;
        }

        $cart = $this->context->cart;
        $data = PaymentData::dataFromCart($cart, $this->context, Tools::getValue('n', '3'));
        $alma = AlmaClient::defaultInstance();
        if (!$data || !$alma) {
            $this->genericErrorAndRedirect();

            return;
        }

        try {
            $payment = $alma->payments->create($data);
        } catch (RequestError $e) {
            $msg = "[Alma] ERROR when creating payment for Cart {$cart->id}: {$e->getMessage()}";
            AlmaLogger::instance()->error($msg);
            $this->genericErrorAndRedirect();

            return;
        }

        Tools::redirect($payment->url);
    }
}
