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
include_once _PS_MODULE_DIR_ . 'alma/includes/functions.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaSettings.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaEligibilityHelper.php';

class AlmaDisplayShoppingCartFooterController extends AlmaProtectedHookController
{
    public function run($params)
    {
        if (!AlmaSettings::isFullyConfigured() || !AlmaSettings::showEligibilityMessage()) {
            return null;
        }

        $eligibility = AlmaEligibilityHelper::eligibilityCheck($this->context);
        $eligibilityMsg = AlmaSettings::getEligibilityMessage();

        $isEligible = false;
        if ($eligibility instanceof Eligibility) {
            $isEligible = $eligibility->isEligible();
        } else if (is_array($eligibility)) {
            foreach ($eligibility as $eli) {
                if (true == $eli->isEligible()) {
                     $isEligible = true;
                }
            }
        }

        if (!$isEligible) {
            $eligibilityMsg = AlmaSettings::getNonEligibilityMessage();

            $cart = $this->context->cart;
            $cartTotal = almaPriceToCents((float) $cart->getOrderTotal(true, Cart::BOTH));
            $minAmount = AlmaSettings::installmentPlanMinAmount($n);
            $maxAmount = AlmaSettings::installmentPlanMaxAmount($n);

            if ($cartTotal < $minAmount || $cartTotal > $maxAmount) {
                if ($cartTotal > $maxAmount) {
                    $eligibilityMsg .= ' ' . sprintf(
                        $this->module->l('(Maximum amount: %s)', 'displayShoppingCartFooter'),
                        Tools::displayPrice(almaPriceFromCents($maxAmount))
                    );
                } else {
                    $eligibilityMsg .= ' ' . sprintf(
                        $this->module->l('(Minimum amount: %s)', 'displayShoppingCartFooter'),
                        Tools::displayPrice(almaPriceFromCents($minAmount))
                    );
                }
            }
        }

        if (is_callable('Media::getMediaPath')) {
            $logo = Media::getMediaPath(_PS_MODULE_DIR_ . $this->module->name . '/views/img/alma_logo.svg');
        } else {
            $logo = $this->module->getPathUri() . '/views/img/alma_logo.svg';
        }

        $this->context->smarty->assign(array(
            'eligibility_msg' => $eligibilityMsg,
            'logo' => $logo,
        ));

        return $this->module->display($this->module->file, 'displayShoppingCartFooter.tpl');
    }
}
