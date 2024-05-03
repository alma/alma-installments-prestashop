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

use Alma\API\ParamsError;
use Alma\PrestaShop\Builders\PaymentDataBuilder;
use Alma\PrestaShop\Builders\SettingsHelperBuilder;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Model\PaymentData;
use Alma\PrestaShop\Traits\AjaxTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AlmaPaymentModuleFrontController extends ModuleFrontController
{
    use AjaxTrait;

    /**
     * @var bool
     */
    public $ssl = true;

    /**
     * @var PaymentData
     */
    protected $paymentData;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        parent::__construct();

        $this->context = Context::getContext();

        $settingsHelperBuilder = new SettingsHelperBuilder();
        $this->settingsHelper = $settingsHelperBuilder->getInstance();

        $paymentDataBuilder = new PaymentDataBuilder();
        $this->paymentData = $paymentDataBuilder->getInstance();
    }

    /**
     * @return bool
     */
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

    /**
     * @return void
     *
     * @throws Exception
     */
    private function ajaxErrorAndDie()
    {
        // `l` method call isn't detected by translation tool if multiline
        $msg = $this->module->l('There was an error while generating your payment request. Please try again later or contact us if the problem persists.', 'payment');
        $this->context->cookie->__set('alma_error', $msg);
        $this->ajaxFailAndDie();
    }

    /**
     * @return void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ParamsError
     */
    public function postProcess()
    {
        try {
            // Check if cart exists and all fields are set
            if (!$this->module->active) {
                $this->ajaxFailAndDie();
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
                $this->ajaxFailAndDie();
            }

            if (!$this->checkCurrency()) {
                $msg = $this->module->l('Alma Monthly Installments are not available for this currency', 'payment');
                $this->context->cookie->__set('alma_error', $msg);
                $this->ajaxFailAndDie();
            }

            $key = Tools::getValue('key', 'general_3_0_0');
            $feePlans = json_decode($this->settingsHelper->getAlmaFeePlans());
            $dataFromKey = $this->settingsHelper->getDataFromKey($key);

            $cart = $this->context->cart;
            $data = $this->paymentData->dataFromCart($dataFromKey, true);
            $alma = ClientHelper::defaultInstance();

            if (!$data || !$alma) {
                $this->ajaxErrorAndDie();
            }

            // Check that the selected installments count is indeed enabled
            $disabled = !$feePlans->$key->enabled
                || $feePlans->$key->min > $data['payment']['purchase_amount']
                || $feePlans->$key->max < $data['payment']['purchase_amount'];

            if ($disabled) {
                $this->ajaxErrorAndDie();
            }

            $payment = $alma->payments->create($data);
        } catch (Exception $e) {
            $msg = sprintf(
                '[Alma] ERROR when creating payment for Cart %s: %s - Trace %s',
                $cart->id,
                $e->getMessage(),
                $e->getTraceAsString()
            );
            Logger::instance()->error($msg);
            $this->ajaxErrorAndDie();
        }

        if ($this->paymentData->isInPage($data)) {
            $this->ajaxRenderAndExit(json_encode($payment));
        }

        Tools::redirect($payment->url);
    }
}
