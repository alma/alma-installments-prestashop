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
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

final class PaymentOptionsHookController extends FrontendHookController
{
    public function run($params)
    {
        //  Check if some products in cart are in the excludes listing
        $diff = CartData::getCartExclusion($params['cart']);

        if (!empty($diff)) {
            return [];
        }

        $installmentPlans = EligibilityHelper::eligibilityCheck($this->context);

        if (empty($installmentPlans)) {
            return [];
        }

        $paymentOptionPnxData = [];
        $paymentOptionDeferredData = [];

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
                    $logoDeferred = Media::getMediaPath(
                        _PS_MODULE_DIR_ . $this->module->name . "/views/img/logos/${duration}j_logo.svg"
                    );
                    // `l` method call isn't detected by translation tool if multiline
                    // phpcs:ignore
                    $label = sprintf(
                        $this->module->l('+ %d days', 'PaymentOptionsHookController'),
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
                        'logo_deferred' => $logoDeferred,
                        'plans' => $plans,
                    ];
                    $paymentOptionDeferredData[$duration] = $paymentOption;
                }
            } else {
                if ($n != 1) {
                    $logoPnx = Media::getMediaPath(
                        _PS_MODULE_DIR_ . $this->module->name . "/views/img/logos/p${n}x_logo.svg"
                    );
                    $paymentOption = [
                        'link' => $this->context->link->getModuleLink(
                            $this->module->name,
                            'payment',
                            ['key' => $key],
                            true
                        ),
                        'plans' => $plans,
                        'error' => false,
                        'logo_pnx' => $logoPnx,
                        'pnx' => $n,
                    ];
                    $paymentOptionPnxData[$n] = $paymentOption;
                }
            }
        }

        $paymentOptionPnxData = array_values($paymentOptionPnxData);
        usort($paymentOptionDeferredData, function ($a, $b) {
            return $a['duration'] - $b['duration'];
        });

        $forEUComplianceModule = false;
        if (array_key_exists('for_eu_compliance_module', $params)) {
            $forEUComplianceModule = $params['for_eu_compliance_module'];
        }

        if ($paymentOptionPnxData) {
            $paymentOptionPnx = $this->createPaymentOption(
                $forEUComplianceModule,
                Settings::getPaymentButtonTitle(),
                $paymentOptionPnxData[0]['link']
            );
        }

        if ($paymentOptionDeferredData) {
            $paymentOptionDeferred = $this->createPaymentOption(
                $forEUComplianceModule,
                Settings::getPaymentButtonTitleDeferred(),
                $paymentOptionDeferredData[0]['link']
            );
        }

        $paymentButtonDescriptionPnx = Settings::getPaymentButtonDescription();
        $paymentButtonDescriptionDeferred = Settings::getPaymentButtonDescriptionDeferred();

        if (!$forEUComplianceModule) {
            if ($paymentOptionPnxData) {
                $this->context->smarty->assign([
                    'desc' => $paymentButtonDescriptionPnx,
                    'options_pnx' => (array) $paymentOptionPnxData,
                ]);

                $template = $this->context->smarty->fetch(
                    "module:{$this->module->name}/views/templates/hook/payment_button_pnx.tpl"
                );

                $paymentOptionPnx->setAdditionalInformation($template);
            }

            if ($paymentOptionDeferredData) {
                $this->context->smarty->assign([
                    'desc' => $paymentButtonDescriptionDeferred,
                    'options' => (array) $paymentOptionDeferredData,
                ]);

                $template = $this->context->smarty->fetch(
                    "module:{$this->module->name}/views/templates/hook/payment_button_deferred.tpl"
                );

                $paymentOptionDeferred->setAdditionalInformation($template);
            }
        }

        if ($paymentOptionPnxData && !$paymentOptionDeferredData) {
            $payment = [$paymentOptionPnx];
        } elseif (!$paymentOptionPnxData && $paymentOptionDeferredData) {
            $payment = [$paymentOptionDeferred];
        } else {
            $payment = Settings::getPaymentButtonPosition() <= Settings::getPaymentButtonPositionDeferred()
            ? [$paymentOptionPnx, $paymentOptionDeferred]
            : [$paymentOptionDeferred, $paymentOptionPnx];
        }

        return $payment;
    }

    private function createPaymentOption($forEUComplianceModule, $ctaText, $action)
    {
        $baseDir = _PS_MODULE_DIR_ . $this->module->name;

        if ($forEUComplianceModule) {
            $logo = Media::getMediaPath("${baseDir}/views/img/logos/alma_payment_logos_tiny.svg");
            $paymentOption = [
                'cta_text' => $ctaText,
                'action' => $action,
                'logo' => $logo,
            ];
        } else {
            $paymentOption = new PaymentOption();
            $logo = Media::getMediaPath("${baseDir}/views/img/logos/alma_payment_logos_tiny.svg");
            $paymentOption
                ->setModuleName($this->module->name)
                ->setCallToActionText($ctaText)
                ->setAction($action)
                ->setLogo($logo);
        }

        return $paymentOption;
    }
}
