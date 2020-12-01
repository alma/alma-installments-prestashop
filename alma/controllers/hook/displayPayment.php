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
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaClient.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaSettings.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaEligibilityHelper.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/functions.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/CartData.php';

class AlmaDisplayPaymentController extends AlmaProtectedHookController
{
    public function run($params)
    {
		// Check if some products in cart are in the excludes listing
		$diff = CartData::getCartExclusion($params['cart']);
		if(!empty($diff)){
			return false;
		}

        $installmentPlans = AlmaEligibilityHelper::eligibilityCheck($this->context);
        $options = [];
        if(empty($installmentPlans)) {
            if (AlmaSettings::showDisabledButton()) {
                foreach(AlmaSettings::activeInstallmentsCounts() as $n){
                    $paymentOption = [
                        'text' => sprintf(AlmaSettings::getPaymentButtonTitle(), $n),
                        'link' => $this->context->link->getModuleLink($this->module->name, 'payment', array('n' => $n), true),
                        'plans' => null,
                        'disabled' => true,
                        'error' => true,
                    ];
                    $options[] = $paymentOption;
                }
            }
            return $options;
        }

        foreach($installmentPlans as $plan) {
            $n = $plan->installmentsCount;
            if(!$plan->isEligible && AlmaSettings::isInstallmentPlanEnabled($n)){
                if (AlmaSettings::showDisabledButton()) {
                    $disabled = true;
                    $plans = null;
                }
                else{
                    continue;
                }
            }
            else{
                $disabled = false;
                $plans = $plan->paymentPlan;
            }

            if (is_callable('Media::getMediaPath')) {
                $logo = Media::getMediaPath(_PS_MODULE_DIR_ . $this->module->name . '/views/img/logos/alma_p'.$n.'x.svg');
            } else {
                $logo = $this->module->getPathUri() . '/views/img/logos/alma_p'.$n.'x.svg';
            }

            $paymentOption = [
                'text' => sprintf(AlmaSettings::getPaymentButtonTitle(), $n),
                'link' => $this->context->link->getModuleLink($this->module->name, 'payment', array('n' => $n), true),
                'plans' => $plans,
                'disabled' => $disabled,
                'error' => false,
                'logo' => $logo,
            ];

            if (!empty(AlmaSettings::getPaymentButtonDescription())) {
                $paymentOption['desc'] = sprintf(AlmaSettings::getPaymentButtonDescription(), $n);
            }

            $options[] = $paymentOption;
        }

        $cart = $this->context->cart;
        $this->context->smarty->assign(
            array(
                'title' => sprintf(AlmaSettings::getPaymentButtonTitle(), 3),
                'desc' => sprintf(AlmaSettings::getPaymentButtonDescription(), 3),
                'order_total' => (float) $cart->getOrderTotal(true, Cart::BOTH),
                'options' => $options,
                'old_prestashop_version' => version_compare(_PS_VERSION_, '1.6', '<'),
            )
        );

        return $this->module->display($this->module->file, 'displayPayment.tpl');
    }
}
