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
use Tools;

final class DisplayPaymentHookController extends FrontendHookController
{
    public function run($params)
    {
        // Check if some products in cart are in the excludes listing
        $diff = CartData::getCartExclusion($params['cart']);
        if (!empty($diff)) {
            return false;
        }

        $idLang = $this->context->language->id;

        $installmentPlans = EligibilityHelper::eligibilityCheck($this->context);

        if (empty($installmentPlans)) {
            return;
        }

        $feePlans = json_decode(Settings::getFeePlans());
        $paymentOptions = [];
        $sortOptions = [];
        $totalCart = (float) almaPriceToCents(
            Tools::ps_round((float) $this->context->cart->getOrderTotal(true, Cart::BOTH), 2)
        );

        foreach ($installmentPlans as $plan) {
            $n = $plan->installmentsCount;
            $key = "general_{$n}_{$plan->deferredDays}_{$plan->deferredMonths}";
            $plans = $plan->paymentPlan;
            $disabled = false;
            $creditInfo = [
                'totalCart' => $totalCart,
                'costCredit' => $plan->customerTotalCostAmount,
                'totalCredit' => $plan->customerTotalCostAmount + $totalCart,
                'taeg' => $plan->annualInterestRate,
            ];

            if (Settings::isDeferred($plan)) {
                if ($n == 1) {
                    if (!$plan->isEligible && $feePlans->$key->enabled && Settings::showDisabledButton()) {
                        $disabled = true;
                        $plans = null;
                    } elseif (!$plan->isEligible) {
                        continue;
                    }
                    $duration = Settings::getDuration($plan);
                    $logo = $this->getAlmaLogo(true, $duration);
                    $paymentOption = [
                    'link' => $this->context->link->getModuleLink(
                        $this->module->name,
                        'payment',
                        ['key' => $key],
                        true
                    ),
                    'disabled' => $disabled,
                    'duration' => $duration,
                    'key' => $key,
                    'pnx' => $n,
                    'logo' => $logo,
                    'plans' => $plans,
                    'isDeferred' => true,
                    'text' => sprintf(Settings::getPaymentButtonTitleDeferred($idLang), $duration),
                    'desc' => sprintf(Settings::getPaymentButtonDescriptionDeferred($idLang), $duration),
                    'creditInfo' => $creditInfo,
                    ];
                    $paymentOptions[$key] = $paymentOption;
                    $sortOptions[$key] = $feePlans->$key->order;
                }
            } else {
                if ($n != 1) {
                    if (!$plan->isEligible && $feePlans->$key->enabled && Settings::showDisabledButton()) {
                        $disabled = true;
                        $plans = null;
                    } elseif (!$plan->isEligible) {
                        continue;
                    }
                    $logo = $this->getAlmaLogo(false, $n);
                    $paymentOption = [
                        'link' => $this->context->link->getModuleLink(
                            $this->module->name,
                            'payment',
                            ['key' => $key],
                            true
                        ),
                        'disabled' => $disabled,
                        'plans' => $plans,
                        'pnx' => $n,
                        'logo' => $logo,
                        'isDeferred' => false,
                        'text' => sprintf(Settings::getPaymentButtonTitle($idLang), $n),
                        'desc' => sprintf(Settings::getPaymentButtonDescription($idLang), $n),
                        'creditInfo' => $creditInfo,
                    ];
                    $paymentOptions[$key] = $paymentOption;
                    $sortOptions[$key] = $feePlans->$key->order;
                }
            }
        }

        asort($sortOptions);
        $payment = [];
        foreach ($sortOptions as $key => $option) {
            $payment[] = $paymentOptions[$key];
        }

        return $this->displayAlmaPaymentOption($payment);
    }

    private function displayAlmaPaymentOption($paymentOption)
    {
        $this->context->smarty->assign(
            [
                'options' => $paymentOption,
                'old_prestashop_version' => version_compare(_PS_VERSION_, '1.6', '<'),
                'apiMode' => Settings::getActiveMode(),
                'merchantId' => Settings::getMerchantId(),
            ]
        );

        return $this->module->display($this->module->file, 'displayPayment.tpl');
    }

    private function getAlmaLogo($isDeferred, $value)
    {
        if ($isDeferred) {
            $logoName = "${value}j_logo.svg";
        } else {
            $logoName = "p${value}x_logo.svg";
        }

        if (is_callable('Media::getMediaPath')) {
            $logo = Media::getMediaPath(
                _PS_MODULE_DIR_ . $this->module->name . "/views/img/logos/${logoName}"
            );
        } else {
            $logo = $this->module->getPathUri() . "/views/img/logos/${logoName}";
        }

        return $logo;
    }
}
