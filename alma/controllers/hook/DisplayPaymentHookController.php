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

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\API\EligibilityHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Utils\Settings;
use Cart;
use Media;

final class DisplayPaymentHookController extends FrontendHookController
{
    public function run($params)
    {
        // Check if some products in cart are in the excludes listing
        $diff = CartData::getCartExclusion($params['cart']);
        if (!empty($diff)) {
            return false;
        }

        $installmentPlans = EligibilityHelper::eligibilityCheck($this->context);

        //print_r($installmentPlans);
        //return;
        $options = [];
        if (empty($installmentPlans)) {
            if (Settings::showDisabledButton()) {
                echo 'on est la';
                foreach (Settings::activeInstallmentsCounts() as $n) {
                    $paymentOption = [
                        'text' => sprintf(Settings::getPaymentButtonTitle(), $n),
                        'link' => $this->context->link->getModuleLink(
                            $this->module->name,
                            'payment',
                            ['n' => $n],
                            true
                        ),
                        'plans' => null,
                        'disabled' => true,
                        'error' => true,
                    ];
                    $options[] = $paymentOption;
                }
            }

            return $options;
        }

        $paymentButtonDescription = Settings::getPaymentButtonDescription();
        $sortOrders = [];
        $feePlans = json_decode(Settings::getFeePlans());
        foreach ($installmentPlans as $plan) {
            $n = $plan->installmentsCount;
            // @todo change that when eligibility endpoint will be update
            $key = "general_{$plan->installmentsCount}_0_0";
            //if (!$plan->isEligible && Settings::isInstallmentPlanEnabled($n)) {
            if (!$plan->isEligible && $feePlans->$key->enabled) {
                if (Settings::showDisabledButton()) {
                    $disabled = true;
                    $plans = null;
                } else {
                    continue;
                }
            } else {
                $disabled = false;
                $plans = $plan->paymentPlan;
            }

            if (is_callable('Media::getMediaPath')) {
                $logo = Media::getMediaPath(_PS_MODULE_DIR_ . $this->module->name . "/views/img/logos/alma_p${n}x.svg");
            } else {
                $logo = $this->module->getPathUri() . "/views/img/logos/alma_p${n}x.svg";
            }

            $paymentOption = [
                'text' => sprintf(Settings::getPaymentButtonTitle(), $n),
                'link' => $this->context->link->getModuleLink($this->module->name, 'payment', ['key' => $key], true),
                'plans' => $plans,
                'disabled' => $disabled,
                'error' => false,
                'logo' => $logo,
            ];

            if (!empty($paymentButtonDescription)) {
                $paymentOption['desc'] = sprintf($paymentButtonDescription, $n);
            }

            //$sortOrder = Settings::installmentPlanSortOrder($n);
            $sortOrder = $feePlans->$key->sort;
            $options[$sortOrder] = $paymentOption;
            $sortOrders[] = $sortOrder;
        }

        $sortedOptions = [];
        sort($sortOrders);
        foreach ($sortOrders as $order) {
            $sortedOptions[] = $options[$order];
        }

        $cart = $this->context->cart;
        $this->context->smarty->assign(
            [
                'title' => sprintf(Settings::getPaymentButtonTitle(), 3),
                'desc' => sprintf($paymentButtonDescription, 3),
                'order_total' => (float) $cart->getOrderTotal(true, Cart::BOTH),
                'options' => $sortedOptions,
                'old_prestashop_version' => version_compare(_PS_VERSION_, '1.6', '<'),
            ]
        );

        return $this->module->display($this->module->file, 'displayPayment.tpl');
    }
}
