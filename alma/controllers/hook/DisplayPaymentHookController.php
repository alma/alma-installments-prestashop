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

        if (empty($installmentPlans)) {
            return;
        }

        $feePlans = json_decode(Settings::getFeePlans());
        $paymentOptionPnx = [];
        $paymentOptionDeferred = [];

        foreach ($installmentPlans as $plan) {
            if (!$plan->isEligible) {
                continue;
            }
            $n = $plan->installmentsCount;
            $key = "general_{$n}_{$plan->deferredDays}_{$plan->deferredMonths}";

            $plans = $plan->paymentPlan;
            if (Settings::isDeferred($plan)) {
                if ($n == 1) {
                    $duration = Settings::getDuration($plan);
                    // `l` method call isn't detected by translation tool if multiline
                    // phpcs:ignore
                    $label = sprintf(
                        $this->module->l('+ %d days', 'DisplayPaymentHookController'),
                        $duration
                    );
                    $paymentOption = [
                    'link' => $this->context->link->getModuleLink(
                        $this->module->name,
                        'payment',
                        ['key' => $key],
                        true
                    ),
                    'duration' => $duration,
                    'key' => $key,
                    'pnx' => $n,
                    'label' => $label,
                    'plans' => $plans,
                    ];
                    $paymentOptionDeferred[$key] = $paymentOption;
                }
            } else {
                if ($n != 1) {
                    if (is_callable('Media::getMediaPath')) {
                        $logoPnx = Media::getMediaPath(
                            _PS_MODULE_DIR_ . $this->module->name . "/views/img/logos/p${n}x_logo.svg"
                        );
                    } else {
                        $logoPnx = $this->module->getPathUri() . "/views/img/logos/p${n}x_logo.svg";
                    }

                    $paymentOption = [
                        'link' => $this->context->link->getModuleLink(
                            $this->module->name,
                            'payment',
                            ['key' => $key],
                            true
                        ),
                        'plans' => $plans,
                        'logo_pnx' => $logoPnx,
                        'pnx' => $n,
                    ];
                    $paymentOptionPnx[$n] = $paymentOption;
                }
            }
        }
        $paymentOptionPnx = array_values($paymentOptionPnx);
        usort($paymentOptionDeferred, function ($a, $b) {
            return $a['duration'] - $b['duration'];
        });

        $pnx = null;
        if ($paymentOptionPnx) {
            $pnx = $this->displayAlmaPaymentOption('pnx', $paymentOptionPnx, false);
        } else {
            $disabled = false;
            foreach ($installmentPlans as $plan) {
                $n = $plan->installmentsCount;
                $key = "general_{$n}_{$plan->deferredDays}_{$plan->deferredMonths}";
                if (!$plan->isEligible && $feePlans->$key->enabled && !Settings::isDeferred($plan)) {
                    $disabled = true;
                    break;
                }
            }
            if (Settings::showDisabledButton() && $disabled) {
                $pnx = $this->displayAlmaPaymentOption('pnx', null, true);
            }
        }

        $deferred = null;
        if ($paymentOptionDeferred) {
            $deferred = $this->displayAlmaPaymentOption('deferred', $paymentOptionDeferred, false);
        } else {
            $disabled = false;
            foreach ($installmentPlans as $plan) {
                $n = $plan->installmentsCount;
                $key = "general_{$n}_{$plan->deferredDays}_{$plan->deferredMonths}";
                if (!$plan->isEligible && $feePlans->$key->enabled && Settings::isDeferred($plan)) {
                    $disabled = true;
                    break;
                }
            }
            if (Settings::showDisabledButton() && $disabled) {
                $deferred = $this->displayAlmaPaymentOption('deferred', null, true);
            }
        }

        $payment = Settings::getPaymentButtonPosition() <= Settings::getPaymentButtonPositionDeferred()
            ? $pnx . $deferred
            : $deferred . $pnx;

        return $payment;
    }

    private function displayAlmaPaymentOption($type, $paymentOption, $disabled)
    {
        $this->context->smarty->assign(
            [
                'logo' => $this->getAlmaLogo(),
                'title' => $type == 'pnx'
                    ? Settings::getPaymentButtonTitle()
                    : Settings::getPaymentButtonTitleDeferred(),
                'desc' => $type == 'pnx'
                    ? Settings::getPaymentButtonDescription()
                    : Settings::getPaymentButtonDescriptionDeferred(),
                'options' => $paymentOption,
                'disabled' => $disabled,
                'old_prestashop_version' => version_compare(_PS_VERSION_, '1.6', '<'),
            ]
        );

        return $this->module->display($this->module->file, "displayPayment_{$type}.tpl");
    }

    private function getAlmaLogo()
    {
        if (is_callable('Media::getMediaPath')) {
            $logo = Media::getMediaPath(
                _PS_MODULE_DIR_ . $this->module->name . '/views/img/logos/alma_payment_logos.svg'
            );
        } else {
            $logo = $this->module->getPathUri() . '/views/img/logos/alma_payment_logos.svg';
        }

        return $logo;
    }
}
