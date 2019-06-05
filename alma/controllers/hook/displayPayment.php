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

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaProtectedHookController.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaClient.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaSettings.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaEligibilityHelper.php';

class AlmaDisplayPaymentController extends AlmaProtectedHookController
{
    public function run($params)
    {
        // First check that we can offer Alma for this payment
        $eligibility = AlmaEligibilityHelper::eligibilityCheck($this->context);

        $error = false;
        if (!$eligibility) {
            $error = true;
        }

        if (isset($eligibility) && $eligibility->isEligible) {
            $disabled = false;
        } else {
            if (AlmaSettings::showDisabledButton()) {
                $disabled = true;
            } else {
                return null;
            }
        }

        if (is_callable('Media::getMediaPath')) {
            $logo = Media::getMediaPath(_PS_MODULE_DIR_ . $this->module->name . '/views/img/alma_payment_logos.svg');
        } else {
            $logo = $this->module->getPathUri() . '/views/img/alma_payment_logos.svg';
        }

        $cart = $this->context->cart;
        $this->context->smarty->assign(
            array(
                'logo' => $logo,
                'disabled' => $disabled,
                'error' => $error,
                'title' => AlmaSettings::getPaymentButtonTitle(),
                'desc' => sprintf(AlmaSettings::getPaymentButtonDescription(), 3),
                'order_total' => (float) $cart->getOrderTotal(true, Cart::BOTH),
            )
        );

        return $this->module->display($this->module->file, 'displayPayment.tpl');
    }
}
