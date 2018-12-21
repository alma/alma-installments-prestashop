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

use Alma\API\RequestError;

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_ . 'alma/includes/AlmaProtectedHookController.php');
include_once(_PS_MODULE_DIR_ . 'alma/includes/functions.php');
include_once(_PS_MODULE_DIR_ . 'alma/includes/PaymentData.php');
include_once(_PS_MODULE_DIR_ . 'alma/includes/AlmaClient.php');
include_once(_PS_MODULE_DIR_ . 'alma/includes/AlmaSettings.php');

class AlmaDisplayShoppingCartFooterController extends AlmaProtectedHookController
{
    public function run($params)
    {
        if (!AlmaSettings::isFullyConfigured() || !AlmaSettings::showEligibilityMessage()) {
            return null;
        }

        $payment_data = PaymentData::dataFromCart($this->context->cart, $this->context);
        if (!$payment_data) {
            AlmaLogger::instance()->error('Cannot check cart eligibility: no data extracted from cart');
            return null;
        }

        try {
            $alma = AlmaClient::defaultInstance();
            $eligibility = $alma->payments->eligibility($payment_data);
        } catch (RequestError $e) {
            AlmaLogger::instance()->error("Error when checking cart {$this->context->cart->id} eligibility: " . $e->getMessage());
            return null;
        }

        $eligibility_msg = AlmaSettings::getEligibilityMessage();

        if (!$eligibility->isEligible) {
            $eligibility_msg = AlmaSettings::getNonEligibilityMessage();

            try {
                $merchant = $alma->merchants->me();
            } catch (RequestError $e) {
                AlmaLogger::instance()->error('Error fetching merchant information: ' . $e->getMessage());
            }

            if (isset($merchant) && $merchant) {
                $cart       = $this->context->cart;
                $cart_total = alma_price_to_cents((float)$cart->getOrderTotal(true, Cart::BOTH));
                $min_amount = $merchant->minimum_purchase_amount;
                $max_amount = $merchant->maximum_purchase_amount;

                if ($cart_total < $min_amount || $cart_total > $max_amount) {
                    if ($cart_total > $max_amount) {
                        $eligibility_msg .= ' ' . sprintf(
                            $this->module->l('(Maximum amount: %s)', 'displayShoppingCartFooter'),
                            Tools::displayPrice(alma_price_from_cents($max_amount))
                        );
                    } else {
                        $eligibility_msg .= ' ' . sprintf(
                            $this->module->l('(Minimum amount: %s)', 'displayShoppingCartFooter'),
                            Tools::displayPrice(alma_price_from_cents($min_amount))
                        );
                    }
                }
            }
        }

        $this->context->smarty->assign(array(
            'eligibility_msg' => $eligibility_msg,
            'logo' => Media::getMediaPath(_PS_MODULE_DIR_.$this->module->name.'/views/img/tiny_logo.png'),
        ));

        return $this->module->display($this->module->file, 'displayShoppingCartFooter.tpl');
    }
}
