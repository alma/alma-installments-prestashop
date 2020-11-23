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

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaProtectedHookController.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/functions.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaSettings.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaEligibilityHelper.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/CartData.php';

class AlmaDisplayShoppingCartFooterController extends AlmaProtectedHookController
{
    public function run($params)
    {
        if (!AlmaSettings::isFullyConfigured() || !AlmaSettings::showEligibilityMessage()) {
            return null;
        }

        $eligibilities = AlmaEligibilityHelper::eligibilityCheck($this->context);
        $eligible = false;
        foreach($eligibilities as $eligibility){
            if($eligibility->isEligible){
                $eligible = true;
            }
        }
        if(!$eligible){
            $cart = $this->context->cart;
            $cartTotal = almaPriceToCents((float) $cart->getOrderTotal(true, Cart::BOTH));
            $minimum = 9999999;
            $maximum = 0;
            foreach($eligibilities as $eligibility){
                if(!$eligibility->isEligible){
                    $minAmount = $eligibility->constraints['purchase_amount']['minimum'];
                    $maxAmount = $eligibility->constraints['purchase_amount']['maximum'];
                    if ($cartTotal < $minAmount || $cartTotal > $maxAmount) {
                        if ($cartTotal > $maxAmount && $maxAmount > $maximum) {
                            $eligibilityMsg = ' ' . sprintf(
                                $this->module->l('(Maximum amount: %s)', 'displayShoppingCartFooter'),
                                Tools::displayPrice(almaPriceFromCents($maxAmount))
                            );
                            $maximum = $maxAmount;
                        }
                        if ($cartTotal < $minAmount && $minAmount < $minimum) {
                            $eligibilityMsg = ' ' . sprintf(
                                $this->module->l('(Minimum amount: %s)', 'displayShoppingCartFooter'),
                                Tools::displayPrice(almaPriceFromCents($minAmount))
                            );
                            $minimum = $minAmount;
                        }
                    }
                }
            }
            $eligibilityMsg = AlmaSettings::getNonEligibilityMessage().$eligibilityMsg;
        }
        else{
            $eligibilityMsg = AlmaSettings::getEligibilityMessage();
        }

        // Check if some products in cart are in the excludes listing
        $diff = CartData::getCartExclusion($params['cart']);
        if(!empty($diff)){
            $eligibilityMsg = AlmaSettings::getNonEligibilityCategoriesMessage();
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
