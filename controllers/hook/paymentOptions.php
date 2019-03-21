<?php
/**
 * 2018-2019 Alma SAS
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
 * @copyright 2018-2019 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

use Alma\API\RequestError;

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaProtectedHookController.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/PaymentData.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaClient.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaSettings.php';

class AlmaPaymentOptionsController extends AlmaProtectedHookController
{
    public function run($params)
    {
        // First check that we can offer Alma for this payment
        $payment_data = PaymentData::dataFromCart($this->context->cart, $this->context);
        if (!$payment_data) {
            AlmaLogger::instance()->error('Cannot check cart eligibility: no data extracted from cart');

            return array();
        }

        $alma = AlmaClient::defaultInstance();
        if (!$alma) {
            AlmaLogger::instance()->error('Cannot check cart eligibility: no API client');

            return array();
        }

        try {
            $eligibility = $alma->payments->eligibility($payment_data);
        } catch (RequestError $e) {
            AlmaLogger::instance()->error(
                "Error when checking cart {$this->context->cart->id} eligibility: " . $e->getMessage()
            );

            return array();
        }

        if (isset($eligibility) && !$eligibility->isEligible) {
            return array();
        }

        $options = array();
        $n = 1;
        while ($n < AlmaSettings::installmentPlansMaxN()) {
            ++$n;

            if (!AlmaSettings::isInstallmentPlanEnabled($n)) {
                continue;
            } else {
                $min = AlmaSettings::installmentPlanMinAmount($n);
                $max = AlmaSettings::installmentPlanMaxAmount($n);

                if ($payment_data['payment']['purchase_amount'] < $min
                    || $payment_data['payment']['purchase_amount'] >= $max
                ) {
                    continue;
                }
            }

            $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $this->context->smarty->assign(array(
                'desc' => sprintf(AlmaSettings::getPaymentButtonDescription(), $n),
            ));
            $template = $this->context->smarty->fetch(
                "module:{$this->module->name}/views/templates/hook/payment_button_desc.tpl"
            );

            $paymentOption
                ->setModuleName($this->module->name)
                ->setCallToActionText(sprintf(AlmaSettings::getPaymentButtonTitle(), $n))
                ->setAdditionalInformation($template)
                ->setAction($this->context->link->getModuleLink($this->module->name, 'payment', array('n' => $n), true))
                ->setLogo(
                    Media::getMediaPath(
                        _PS_MODULE_DIR_ . $this->module->name . '/views/img/tiny_alma_payment_logos.svg'
                    )
                );

            $options[] = $paymentOption;
        }

        return $options;
    }
}
