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
        $eligibility_msg = AlmaSettings::getEligibilityMessage();

        if (!$eligibility->isEligible) {
            $eligibility_msg = AlmaSettings::getNonEligibilityMessage();

            $cart = $this->context->cart;
            $cart_total = alma_price_to_cents((float) $cart->getOrderTotal(true, Cart::BOTH));
            $min_amount = $eligibility->constraints['purchase_amount']['minimum'];
            $max_amount = $eligibility->constraints['purchase_amount']['maximum'];

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

        if (is_callable('Media::getMediaPath')) {
            $logoPath = Media::getMediaPath(_PS_MODULE_DIR_ . $this->module->name . '/views/img/alma_logo.svg');
        } else {
            $logoPath = $this->module->getPathUri() . '/views/img/alma_logo.svg';
        }

        $this->context->smarty->assign(array(
            'eligibility_msg' => $eligibility_msg,
            'logo' => $logoPath,
        ));

        return $this->module->display($this->module->file, 'displayShoppingCartFooter.tpl');
    }
}
