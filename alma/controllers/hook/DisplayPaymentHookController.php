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
use Tools;

class DisplayPaymentHookController extends FrontendHookController
{
    /**
     * Payment option for Hook DisplayPayment (Prestashop 1.6)
     *
     * @param array $params
     *
     * @return string
     */
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
            $installment = $plan->installmentsCount;
            $key = "general_{$installment}_{$plan->deferredDays}_{$plan->deferredMonths}";
            $plans = $plan->paymentPlan;
            $disabled = false;
            $creditInfo = [
                'totalCart' => $totalCart,
                'costCredit' => $plan->customerTotalCostAmount,
                'totalCredit' => $plan->customerTotalCostAmount + $totalCart,
                'taeg' => $plan->annualInterestRate,
            ];

            $isDeferred = Settings::isDeferred($plan);
            $isInstallmentAccordingToDeferred = $isDeferred ? $installment === 1 : $installment !== 1;

            if ($isInstallmentAccordingToDeferred) {
                if (isset($paymentOptions[$key]) && isset($paymentOptions[$key]['disabled']) && $paymentOptions[$key]['disabled'] === true) {
                    continue;
                }
                if (!$plan->isEligible && $feePlans->$key->enabled && Settings::showDisabledButton()) {
                    $disabled = true;
                    $plans = null;
                } elseif (!$plan->isEligible) {
                    continue;
                }
                $duration = Settings::getDuration($plan);
                $valueLogo = $isDeferred ? $duration : $installment;
                $logo = $this->getAlmaLogo($isDeferred, $valueLogo);
                $paymentOption = [
                    'link' => $this->context->link->getModuleLink(
                        $this->module->name,
                        'payment',
                        ['key' => $key],
                        true
                    ),
                    'disabled' => $disabled,
                    'pnx' => $installment,
                    'logo' => $logo,
                    'plans' => $plans,
                    'installmentText' => $this->getInstallmentText($plans, $idLang, Settings::isDeferredTriggerLimitDays($feePlans, $key)),
                    'deferred_trigger_limit_days' => $feePlans->$key->deferred_trigger_limit_days,
                    'isDeferred' => $isDeferred,
                    'text' => sprintf(SettingsCustomFields::getPnxButtonTitleByLang($idLang), $installment),
                    'desc' => sprintf(SettingsCustomFields::getPnxButtonDescriptionByLang($idLang), $installment),
                    'creditInfo' => $creditInfo,
                ];
                if ($installment > 4) {
                    $paymentOption['text'] = sprintf(SettingsCustomFields::getPnxAirButtonTitleByLang($idLang), $installment);
                    $paymentOption['desc'] = sprintf(SettingsCustomFields::getPnxAirButtonDescriptionByLang($idLang), $installment);
                }
                if ($isDeferred) {
                    $paymentOption['duration'] = $duration;
                    $paymentOption['key'] = $key;
                    $paymentOption['text'] = sprintf(SettingsCustomFields::getPaymentButtonTitleDeferredByLang($idLang), $duration);
                    $paymentOption['desc'] = sprintf(SettingsCustomFields::getPaymentButtonDescriptionDeferredByLang($idLang), $duration);
                }
                $paymentOptions[$key] = $paymentOption;
                $sortOptions[$key] = $feePlans->$key->order;
            }
        }

        asort($sortOptions);
        $payment = [];
        foreach (array_keys($sortOptions) as $key) {
            $payment[] = $paymentOptions[$key];
        }

        return $this->displayAlmaPaymentOption($payment);
    }

    /**
     * Text of one liner installment
     *
     * @param array $plans
     * @param int $idLang
     *
     * @return string text one liner option
     */
    private function getInstallmentText($plans, $idLang, $isDeferredTriggerLimitDays)
    {
        $nbPlans = count($plans);
        $locale = Language::getIsoById($idLang);

        if ($isDeferredTriggerLimitDays) {
            return sprintf(
                $this->module->l('%1$s then %2$d x %3$s', 'DisplayPaymentHookController'),
                almaFormatPrice($plans[0]['total_amount']) . ' ' . SettingsCustomFields::getDescriptionPaymentTriggerByLang($idLang),
                $nbPlans - 1,
                almaFormatPrice($plans[1]['total_amount'])
            );
        }
        if ($nbPlans > 1) {
            return sprintf(
                $this->module->l('%1$s today then %2$d x %3$s', 'DisplayPaymentHookController'),
                almaFormatPrice($plans[0]['total_amount']),
                $nbPlans - 1,
                almaFormatPrice($plans[1]['total_amount'])
            );
        }

        return sprintf(
            $this->module->l('0 € today then %1$s on %2$s', 'DisplayPaymentHookController'),
            almaFormatPrice($plans[0]['purchase_amount'] + $plans[0]['customer_fee']),
            getDateFormat($locale, $plans[0]['due_date'])
        );
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
