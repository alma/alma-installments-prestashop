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

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\API\ClientHelper;
use Alma\PrestaShop\Model\PaymentData;
use Alma\PrestaShop\Utils\Logger;
use Alma\PrestaShop\Utils\Settings;

class AlmaPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
    }

    protected function ajaxFail($msg = null, $statusCode = 500)
    {
        header("X-PHP-Response-Code: $statusCode", true, $statusCode);

        $json = ['error' => true, 'message' => $msg];
        method_exists(get_parent_class($this), 'ajaxDie')
            ? $this->ajaxDie(json_encode($json))
            : die(Tools::jsonEncode($json));
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
        // `l` method call isn't detected by translation tool if multiline
        // phpcs:ignore
        $msg = $this->module->l('There was an error while generating your payment request. Please try again later or contact us if the problem persists.', 'payment');
        $this->context->cookie->__set('alma_error', $msg);
        $this->ajaxFail();
    }

    public function postProcess()
    {
        // Check if cart exists and all fields are set
        if (!$this->module->active) {
            $this->ajaxFail();

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
            Logger::instance()->warning('[Alma] Not authorized!');
            $this->ajaxFail();

            return;
        }

        if (!$this->checkCurrency()) {
            $msg = $this->module->l('Alma Monthly Installments are not available for this currency', 'payment');
            $this->context->cookie->__set('alma_error', $msg);
            $this->ajaxFail();

            return;
        }

        $key = Tools::getValue('key', 'general_3_0_0');
        $feePlans = json_decode(Settings::getFeePlans());
        $dataFromKey = Settings::getDataFromKey($key);

        $cart = $this->context->cart;
        $data = PaymentData::dataFromCart($cart, $this->context, $dataFromKey, true);
        $alma = ClientHelper::defaultInstance();

        if (!$data || !$alma) {
            $this->genericErrorAndRedirect();

            return;
        }

        // Check that the selected installments count is indeed enabled
        $disabled = !$feePlans->$key->enabled
            || $feePlans->$key->min > $data['payment']['purchase_amount']
            || $feePlans->$key->max < $data['payment']['purchase_amount'];

        if ($disabled) {
            $this->genericErrorAndRedirect();

            return;
        }

        method_exists(get_parent_class($this), 'ajaxDie')
        ? $this->ajaxDie(json_encode($data))
        : die(Tools::jsonEncode($data));
    }
}
