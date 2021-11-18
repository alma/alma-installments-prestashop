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
use Alma\PrestaShop\Utils\SettingsCustomFields;
use Cart;
use Media;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Tools;

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

        $idLang = $this->context->language->id;

        if (empty($installmentPlans)) {
            return [];
        }

        $forEUComplianceModule = false;
        if (array_key_exists('for_eu_compliance_module', $params)) {
            $forEUComplianceModule = $params['for_eu_compliance_module'];
        }

        $paymentOptions = [];
        $sortOptions = [];
        $feePlans = json_decode(Settings::getFeePlans());
        $i = 1;
        $totalCart = (float) almaPriceToCents(
            Tools::ps_round((float) $this->context->cart->getOrderTotal(true, Cart::BOTH), 2)
        );

        foreach ($installmentPlans as $plan) {
            if (!$plan->isEligible) {
                continue;
            }

            // call almaFragments once
            $first = 1 == $i;
            ++$i;

            $n = $plan->installmentsCount;
            $key = "general_{$n}_{$plan->deferredDays}_{$plan->deferredMonths}";
            $plans = $plan->paymentPlan;
            $creditInfo = [
                'totalCart' => $totalCart,
                'costCredit' => $plan->customerTotalCostAmount,
                'totalCredit' => $plan->customerTotalCostAmount + $totalCart,
                'taeg' => $plan->annualInterestRate,
            ];
            if (Settings::isDeferred($plan)) {
                if ($n == 1) {
                    $duration = Settings::getDuration($plan);
                    $paymentOptionDeferred = $this->createPaymentOption(
                        $forEUComplianceModule,
                        sprintf(SettingsCustomFields::getPaymentButtonTitleDeferredByLang($idLang), $duration),
                        $this->context->link->getModuleLink(
                            $this->module->name,
                            'payment',
                            ['key' => $key],
                            true
                        ),
                        true,
                        $duration
                    );
                    if (!$forEUComplianceModule) {
                        $this->context->smarty->assign([
                            'desc' => sprintf(SettingsCustomFields::getPaymentButtonDescriptionDeferredByLang($idLang), $duration),
                            'plans' => (array) $plans,
                            'apiMode' => Settings::getActiveMode(),
                            'merchantId' => Settings::getMerchantId(),
                            'first' => $first,
                            'creditInfo' => $creditInfo,
                        ]);
                        $template = $this->context->smarty->fetch(
                            "module:{$this->module->name}/views/templates/hook/payment_button_deferred.tpl"
                        );
                        $paymentOptionDeferred->setAdditionalInformation($template);
                    }
                    $sortOptions[$key] = $feePlans->$key->order;
                    $paymentOptions[$key] = $paymentOptionDeferred;
                }
            } else {
                if ($n != 1) {
                    $paymentOptionPnx = $this->createPaymentOption(
                        $forEUComplianceModule,
                        sprintf(SettingsCustomFields::getPaymentButtonTitleByLang($idLang), $n),
                        $this->context->link->getModuleLink(
                            $this->module->name,
                            'payment',
                            ['key' => $key],
                            true
                        ),
                        false,
                        $n
                    );

                    if (!$forEUComplianceModule) {
                        $this->context->smarty->assign([
                            'desc' => sprintf(SettingsCustomFields::getPaymentButtonDescriptionByLang($idLang), $n),
                            'plans' => (array) $plans,
                            'apiMode' => Settings::getActiveMode(),
                            'merchantId' => Settings::getMerchantId(),
                            'first' => $first,
                            'creditInfo' => $creditInfo,
                        ]);

                        $template = $this->context->smarty->fetch(
                            "module:{$this->module->name}/views/templates/hook/payment_button_pnx.tpl"
                        );

                        $paymentOptionPnx->setAdditionalInformation($template);
                    }
                    $sortOptions[$key] = $feePlans->$key->order;
                    $paymentOptions[$key] = $paymentOptionPnx;
                }
            }
        }

        asort($sortOptions);
        $payment = [];
        foreach ($sortOptions as $key => $option) {
            $payment[] = $paymentOptions[$key];
        }

        return $payment;
    }

    private function createPaymentOption($forEUComplianceModule, $ctaText, $action, $deferred, $value)
    {
        $baseDir = _PS_MODULE_DIR_ . $this->module->name;

        if ($deferred) {
            $logoName = "${value}j_logo.svg";
        } else {
            $logoName = "p${value}x_logo.svg";
        }

        if ($forEUComplianceModule) {
            $logo = Media::getMediaPath("${baseDir}/views/img/logos/alma_payment_logos_tiny.svg");
            $paymentOption = [
                'cta_text' => $ctaText,
                'action' => $action,
                'logo' => $logo,
            ];
        } else {
            $paymentOption = new PaymentOption();
            $logo = Media::getMediaPath("${baseDir}/views/img/logos/${logoName}");
            $paymentOption
                ->setModuleName($this->module->name)
                ->setCallToActionText($ctaText)
                ->setAction($action)
                ->setLogo($logo);
        }

        return $paymentOption;
    }
}
