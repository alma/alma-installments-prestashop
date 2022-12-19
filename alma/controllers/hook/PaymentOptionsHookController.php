<?php
/**
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
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
use Language;
use Media;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Tools;

class PaymentOptionsHookController extends FrontendHookController
{
    /**
     * Payment option for Hook PaymentOption (Prestashop 1.7)
     *
     * @param array $params
     *
     * @return string
     */
    public function run($params)
    {
        //  Check if some products in cart are in the excludes listing
        $diff = CartData::getCartExclusion($params['cart']);

        if (!empty($diff)) {
            return [];
        }

        $installmentPlans = EligibilityHelper::eligibilityCheck($this->context);
        $idLang = $this->context->language->id;
        $locale = Language::getIsoById($idLang);

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

            $installment = $plan->installmentsCount;
            $key = "general_{$installment}_{$plan->deferredDays}_{$plan->deferredMonths}";
            $plans = $plan->paymentPlan;
            $creditInfo = [
                'totalCart' => $totalCart,
                'costCredit' => $plan->customerTotalCostAmount,
                'totalCredit' => $plan->customerTotalCostAmount + $totalCart,
                'taeg' => $plan->annualInterestRate,
            ];

            foreach ($plans as $keyPlan => $paymentPlan) {
                $plans[$keyPlan]['human_date'] = getDateFormat($locale, $paymentPlan['due_date']);
                if ($keyPlan === 0) {
                    $plans[$keyPlan]['human_date'] = $this->module->l('Today', 'PaymentOptionsHookController');
                }
                if (Settings::isDeferredTriggerLimitDays($feePlans, $key)) {
                    $plans[$keyPlan]['human_date'] = sprintf(
                        $this->module->l('%s month later', 'PaymentOptionsHookController'),
                        $keyPlan
                    );
                    if ($keyPlan === 0) {
                        $plans[$keyPlan]['human_date'] = SettingsCustomFields::getDescriptionPaymentTriggerByLang($idLang);
                    }
                }
            }
            $isDeferred = Settings::isDeferred($plan);
            $duration = Settings::getDuration($plan);
            $isInstallmentAccordingToDeferred = $installment !== 1;
            $fileTemplate = 'payment_button_pnx.tpl';
            $valueBNPL = $installment;
            $textPaymentButton = sprintf(SettingsCustomFields::getPnxButtonTitleByLang($idLang), $installment);
            $descPaymentButton = sprintf(SettingsCustomFields::getPnxButtonDescriptionByLang($idLang), $installment);
            if ($installment > 4) {
                $textPaymentButton = sprintf(SettingsCustomFields::getPnxAirButtonTitleByLang($idLang), $installment);
                $descPaymentButton = sprintf(SettingsCustomFields::getPnxAirButtonDescriptionByLang($idLang), $installment);
            }
            if ($isDeferred) {
                $isInstallmentAccordingToDeferred = $installment === 1;
                $fileTemplate = 'payment_button_deferred.tpl';
                $valueBNPL = $duration;
                $textPaymentButton = sprintf(SettingsCustomFields::getPaymentButtonTitleDeferredByLang($idLang), $duration);
                $descPaymentButton = sprintf(SettingsCustomFields::getPaymentButtonDescriptionDeferredByLang($idLang), $duration);
            }

            if ($isInstallmentAccordingToDeferred) {
                $paymentOption = $this->createPaymentOption(
                    $forEUComplianceModule,
                    $textPaymentButton,
                    $this->context->link->getModuleLink(
                        $this->module->name,
                        'payment',
                        ['key' => $key],
                        true
                    ),
                    $isDeferred,
                    $valueBNPL
                );
                if (!$forEUComplianceModule) {
                    $templateVar = [
                        'desc' => $descPaymentButton,
                        'plans' => (array) $plans,
                        'deferred_trigger_limit_days' => $feePlans->$key->deferred_trigger_limit_days,
                        'apiMode' => Settings::getActiveMode(),
                        'merchantId' => Settings::getMerchantId(),
                        'first' => $first,
                        'creditInfo' => $creditInfo,
                    ];
                    if ($isDeferred) {
                        $templateVar['installmentText'] = sprintf(
                            $this->module->l('0 € today then %1$s on %2$s', 'PaymentOptionsHookController'),
                            almaFormatPrice($plans[0]['purchase_amount'] + $plans[0]['customer_fee']),
                            getDateFormat($locale, $plans[0]['due_date'])
                        );
                    }
                    $this->context->smarty->assign($templateVar);
                    $template = $this->context->smarty->fetch(
                        "module:{$this->module->name}/views/templates/hook/{$fileTemplate}"
                    );
                    $paymentOption->setAdditionalInformation($template);
                }
                $sortOptions[$key] = $feePlans->$key->order;
                $paymentOptions[$key] = $paymentOption;
            }
        }

        asort($sortOptions);
        $payment = [];
        foreach (array_keys($sortOptions) as $key) {
            $payment[] = $paymentOptions[$key];
        }

        return $payment;
    }

    /**
     * Create Payment option
     *
     * @param bool $forEUComplianceModule
     * @param string $ctaText
     * @param string $action
     * @param bool $isDeferred
     * @param int $valueBNPL
     *
     * @return PaymentOption
     */
    private function createPaymentOption($forEUComplianceModule, $ctaText, $action, $isDeferred, $valueBNPL)
    {
        $baseDir = _PS_MODULE_DIR_ . $this->module->name;

        if ($isDeferred) {
            $logoName = "{$valueBNPL}j_logo.svg";
        } else {
            $logoName = "p{$valueBNPL}x_logo.svg";
        }

        if ($forEUComplianceModule) {
            $logo = Media::getMediaPath("{$baseDir}/views/img/logos/alma_payment_logos_tiny.svg");
            $paymentOption = [
                'cta_text' => $ctaText,
                'action' => $action,
                'logo' => $logo,
            ];
        } else {
            $paymentOption = new PaymentOption();
            $logo = Media::getMediaPath("{$baseDir}/views/img/logos/{$logoName}");
            $paymentOption
                ->setModuleName($this->module->name)
                ->setCallToActionText($ctaText)
                ->setAction($action)
                ->setLogo($logo);
        }

        return $paymentOption;
    }
}
