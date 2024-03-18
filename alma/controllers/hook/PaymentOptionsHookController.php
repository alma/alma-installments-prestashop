<?php
/**
 * 2018-2023 Alma SAS.
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
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\DateHelper;
use Alma\PrestaShop\Helpers\EligibilityHelper;
use Alma\PrestaShop\Helpers\LanguageHelper;
use Alma\PrestaShop\Helpers\LocaleHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\SettingsCustomFieldsHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Model\CartData;
use PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class PaymentOptionsHookController extends FrontendHookController
{
    /**
     * @var LocaleHelper
     */
    protected $localeHelper;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    public function __construct()
    {
        parent::__construct();

        $this->settingsHelper = new SettingsHelper();
        $this->localeHelper = new LocaleHelper(new LanguageHelper());
    }

    /**
     * Payment option for Hook PaymentOption (Prestashop 1.7).
     *
     * @param array $params
     *
     * @return array
     *
     * @throws LocalizationException
     * @throws \SmartyException
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
        $locale = $this->localeHelper->getLocaleByIdLangForWidget($idLang);

        if (empty($installmentPlans)) {
            return [];
        }

        $forEUComplianceModule = false;
        if (array_key_exists('for_eu_compliance_module', $params)) {
            $forEUComplianceModule = $params['for_eu_compliance_module'];
        }

        $paymentOptions = [];
        $sortOptions = [];
        $feePlans = json_decode(SettingsHelper::getFeePlans());
        $countIteration = 1;
        $totalCart = (float) PriceHelper::convertPriceToCents(
            \Tools::ps_round((float) $this->context->cart->getOrderTotal(true, \Cart::BOTH), 2)
        );

        foreach ($installmentPlans as $plan) {
            if (!$plan->isEligible) {
                continue;
            }

            $first = 1 == $countIteration;
            ++$countIteration;

            $installment = $plan->installmentsCount;
            $key = SettingsHelper::keyForInstallmentPlan($plan);
            $plans = $plan->paymentPlan;
            $creditInfo = [
                'totalCart' => $totalCart,
                'costCredit' => $plan->customerTotalCostAmount,
                'totalCredit' => $plan->customerTotalCostAmount + $totalCart,
                'taeg' => $plan->annualInterestRate,
            ];
            $isPayNow = ConstantsHelper::ALMA_KEY_PAYNOW === $key;
            $isInPageEnabled = SettingsHelper::isInPageEnabled();

            foreach ($plans as $keyPlan => $paymentPlan) {
                $plans[$keyPlan]['human_date'] = DateHelper::getDateFormat($locale, $paymentPlan['due_date']);
                if (0 === $keyPlan) {
                    $plans[$keyPlan]['human_date'] = $this->module->l('Today', 'PaymentOptionsHookController');
                }

                if ($isPayNow) {
                    $plans[$keyPlan]['human_date'] = $this->module->l('Total', 'PaymentOptionsHookController');
                }
                if (SettingsHelper::isDeferredTriggerLimitDays($feePlans, $key)) {
                    $plans[$keyPlan]['human_date'] = sprintf(
                        $this->module->l('%s month later', 'PaymentOptionsHookController'),
                        $keyPlan
                    );
                    if (0 === $keyPlan) {
                        $plans[$keyPlan]['human_date'] = SettingsCustomFieldsHelper::getDescriptionPaymentTriggerByLang($idLang);
                    }
                }
            }
            $isDeferred = SettingsHelper::isDeferred($plan);
            $duration = $this->settingsHelper->getDuration($plan);
            $fileTemplate = 'payment_button_pnx.tpl';
            $valueBNPL = $installment;
            $textPaymentButton = sprintf(SettingsCustomFieldsHelper::getPnxButtonTitleByLang($idLang), $installment);
            $descPaymentButton = sprintf(SettingsCustomFieldsHelper::getPnxButtonDescriptionByLang($idLang), $installment);
            if ($installment > 4) {
                $textPaymentButton = sprintf(SettingsCustomFieldsHelper::getPnxAirButtonTitleByLang($idLang), $installment);
                $descPaymentButton = sprintf(SettingsCustomFieldsHelper::getPnxAirButtonDescriptionByLang($idLang), $installment);
                $isInPageEnabled = false;
            }
            if ($isDeferred) {
                $fileTemplate = 'payment_button_deferred.tpl';
                $valueBNPL = $duration;
                $textPaymentButton = sprintf(SettingsCustomFieldsHelper::getPaymentButtonTitleDeferredByLang($idLang), $duration);
                $descPaymentButton = sprintf(SettingsCustomFieldsHelper::getPaymentButtonDescriptionDeferredByLang($idLang), $duration);
            }
            if ($isPayNow) {
                $textPaymentButton = SettingsCustomFieldsHelper::getPayNowButtonTitleByLang($idLang);
                $descPaymentButton = SettingsCustomFieldsHelper::getPayNowButtonDescriptionByLang($idLang);
            }

            $action = $this->context->link->getModuleLink(
                $this->module->name,
                'payment',
                ['key' => $key],
                true
            );

            $paymentOption = $this->createPaymentOption(
                $forEUComplianceModule,
                $textPaymentButton,
                $action,
                $isDeferred,
                $valueBNPL
            );

            if (!$forEUComplianceModule) {
                $templateVar = [
                    'keyPlan' => $installment . '-' . $duration,
                    'action' => $action,
                    'desc' => $descPaymentButton,
                    'plans' => (array) $plans,
                    'deferred_trigger_limit_days' => $feePlans->$key->deferred_trigger_limit_days,
                    'apiMode' => strtoupper(SettingsHelper::getActiveMode()),
                    'merchantId' => SettingsHelper::getMerchantId(),
                    'isInPageEnabled' => $isInPageEnabled,
                    'first' => $first,
                    'creditInfo' => $creditInfo,
                    'installment' => $installment,
                    'deferredDays' => $plan->deferredDays,
                    'deferredMonths' => $plan->deferredMonths,
                    'locale' => $locale,
                ];
                if ($isDeferred) {
                    $templateVar['installmentText'] = sprintf(
                        $this->module->l('0 € today then %1$s on %2$s', 'PaymentOptionsHookController'),
                        PriceHelper::formatPriceToCentsByCurrencyId($plans[0]['purchase_amount'] + $plans[0]['customer_fee']),
                        DateHelper::getDateFormat($locale, $plans[0]['due_date'])
                    );
                }
                $this->context->smarty->assign($templateVar);
                $template = $this->context->smarty->fetch(
                    "module:{$this->module->name}/views/templates/hook/{$fileTemplate}"
                );
                $paymentOption->setAdditionalInformation($template);
                if ($isInPageEnabled) {
                    $paymentOption->setForm($this->context->smarty->fetch(
                        "module:{$this->module->name}/views/templates/front/payment_form_inpage.tpl"
                    ));
                }
            }
            $sortOptions[$key] = $feePlans->$key->order;
            $paymentOptions[$key] = $paymentOption;
        }

        asort($sortOptions);
        $payment = [];
        foreach (array_keys($sortOptions) as $key) {
            $payment[] = $paymentOptions[$key];
        }

        return $payment;
    }

    /**
     * Create Payment option.
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
            $logo = \Media::getMediaPath("{$baseDir}/views/img/logos/alma_payment_logos_tiny.svg");
            $paymentOption = [
                'cta_text' => $ctaText,
                'action' => $action,
                'logo' => $logo,
            ];
        } else {
            $paymentOption = new PaymentOption();
            $logo = \Media::getMediaPath("{$baseDir}/views/img/logos/{$logoName}");
            $paymentOption
                ->setModuleName($this->module->name)
                ->setCallToActionText($ctaText)
                ->setAction($action)
                ->setLogo($logo);
        }

        return $paymentOption;
    }
}
