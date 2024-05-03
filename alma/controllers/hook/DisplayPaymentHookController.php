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

use Alma\PrestaShop\Builders\CartDataBuilder;
use Alma\PrestaShop\Builders\CustomFieldHelperBuilder;
use Alma\PrestaShop\Builders\EligibilityHelperBuilder;
use Alma\PrestaShop\Builders\LocaleHelperBuilder;
use Alma\PrestaShop\Builders\PriceHelperBuilder;
use Alma\PrestaShop\Builders\SettingsHelperBuilder;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\CustomFieldsHelper;
use Alma\PrestaShop\Helpers\DateHelper;
use Alma\PrestaShop\Helpers\EligibilityHelper;
use Alma\PrestaShop\Helpers\LocaleHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Model\CartData;

class DisplayPaymentHookController extends FrontendHookController
{
    /**
     * @var LocaleHelper
     */
    protected $localeHelper;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @var ToolsHelper
     */
    protected $toolsHelper;

    /**
     * @var EligibilityHelper
     */
    protected $eligibilityHelper;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var CartData
     */
    protected $cartData;

    /**
     * @var DateHelper
     */
    protected $dateHelper;

    /**
     * @var CustomFieldsHelper
     */
    protected $customFieldsHelper;

    /**
     * HookController constructor.
     *
     * @param $module Alma
     * @codeCoverageIgnore
     */
    public function __construct($module)
    {
        parent::__construct($module);

        $settingsHelperBuilder = new SettingsHelperBuilder();
        $this->settingsHelper  = $settingsHelperBuilder->getInstance();

        $localeHelperBuilder = new LocaleHelperBuilder();
        $this->localeHelper = $localeHelperBuilder->getInstance();

        $priceHelperBuilder = new PriceHelperBuilder();
        $this->priceHelper = $priceHelperBuilder->getInstance();

        $customFieldHelperBuilder = new CustomFieldHelperBuilder();
        $this->customFieldsHelper= $customFieldHelperBuilder->getInstance();

        $cartDataBuilder = new CartDataBuilder();
        $this->cartData = $cartDataBuilder->getInstance();

        $eligibilityHelperBuilder = new EligibilityHelperBuilder();
        $this->eligibilityHelper = $eligibilityHelperBuilder->getInstance();

        $this->dateHelper = new DateHelper();
        $this->toolsHelper = new ToolsHelper();
    }

    /**
     * Payment option for Hook DisplayPayment (Prestashop 1.6).
     *
     * @param array $params
     *
     * @return string
     */
    public function run($params)
    {
        // Check if some products in cart are in the excludes listing
        $diff = $this->cartData->getCartExclusion($params['cart']);
        if (!empty($diff)) {
            return false;
        }

        $idLang = $this->context->language->id;
        $locale = $this->localeHelper->getLocaleByIdLangForWidget($idLang);

        $installmentPlans = $this->eligibilityHelper->eligibilityCheck();

        if (empty($installmentPlans)) {
            return;
        }

        $feePlans = json_decode(SettingsHelper::getFeePlans());
        $paymentOptions = [];
        $sortOptions = [];
        $totalCart = (float) $this->priceHelper->convertPriceToCents(
            $this->toolsHelper->psRound((float) $this->context->cart->getOrderTotal(true, \Cart::BOTH), 2)
        );

        foreach ($installmentPlans as $keyPlan => $plan) {
            $installment = $plan->installmentsCount;
            $key = $this->settingsHelper->keyForInstallmentPlan($plan);
            $plans = $plan->paymentPlan;
            $disabled = false;
            $creditInfo = [
                'totalCart' => $totalCart,
                'costCredit' => $plan->customerTotalCostAmount,
                'totalCredit' => $plan->customerTotalCostAmount + $totalCart,
                'taeg' => $plan->annualInterestRate,
            ];

            $isDeferred = $this->settingsHelper->isDeferred($plan);
            $isPayNow = ConstantsHelper::ALMA_KEY_PAYNOW === $key;

            if (!$plan->isEligible) {
                if ($feePlans->$key->enabled && SettingsHelper::showDisabledButton()) {
                    $disabled = true;
                    $plans = null;
                } else {
                    continue;
                }
            }
            $duration = $this->settingsHelper->getDuration($plan);
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
                'deferredDays' => $plan->deferredDays,
                'deferredMonths' => $plan->deferredMonths,
                'logo' => $logo,
                'plans' => $plans,
                'installmentText' => $this->getInstallmentText(
                    $plans,
                    $idLang,
                    $this->settingsHelper->isDeferredTriggerLimitDays($feePlans, $key),
                    $isPayNow
                ),
                'deferred_trigger_limit_days' => $feePlans->$key->deferred_trigger_limit_days,
                'isDeferred' => $isDeferred,
                'text' => sprintf(
                    $this->customFieldsHelper->getBtnValueByLang(
                        $idLang,
                        PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE
                    ),
                    $installment
                ),
                'desc' => sprintf(
                    $this->customFieldsHelper->getBtnValueByLang(
                        $idLang,
                        PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC
                    ),
                    $installment
                ),
                'creditInfo' => $creditInfo,
                'isInPageEnabled' => $this->settingsHelper->isInPageEnabled(),
                'paymentOptionKey' => $keyPlan,
                'locale' => $locale,
            ];

            if ($installment > 4) {
                $paymentOption['text'] = sprintf(
                    $this->customFieldsHelper->getBtnValueByLang(
                        $idLang,
                        PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE
                    ),
                    $installment
                );
                $paymentOption['desc'] = sprintf(
                    $this->customFieldsHelper->getBtnValueByLang(
                        $idLang,
                        PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC
                    ),
                    $installment
                );
                $paymentOption['isInPageEnabled'] = false;
            }

            if ($isDeferred) {
                $paymentOption['duration'] = $duration;
                $paymentOption['key'] = $key;
                $paymentOption['text'] = sprintf(
                    $this->customFieldsHelper->getBtnValueByLang(
                        $idLang,
                        PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE
                    ),
                    $duration
                );
                $paymentOption['desc'] = sprintf(
                    $this->customFieldsHelper->getBtnValueByLang(
                        $idLang,
                        PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC
                    ),
                    $duration
                );
            }

            if ($isPayNow) {
                $paymentOption['text'] = $this->customFieldsHelper->getBtnValueByLang(
                    $idLang,
                    PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_TITLE
                );

                $paymentOption['desc'] = $this->customFieldsHelper->getBtnValueByLang(
                    $idLang,
                    PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_DESC
                );
            }

            $paymentOptions[$key] = $paymentOption;
            $sortOptions[$key] = $feePlans->$key->order;
        }

        asort($sortOptions);

        $payment = [];
        foreach (array_keys($sortOptions) as $key) {
            $payment[] = $paymentOptions[$key];
        }

        return $this->displayAlmaPaymentOption($payment);
    }

    /**
     * Text of one liner installment.
     *
     * @param array $plans
     * @param int $idLang
     *
     * @return string text one liner option
     */
    private function getInstallmentText($plans, $idLang, $isDeferredTriggerLimitDays, $isPayNow)
    {
        $nbPlans = count($plans);
        $locale = \Language::getIsoById($idLang);

        if ($isDeferredTriggerLimitDays) {
            return sprintf(
                $this->module->l('%1$s then %2$d x %3$s', 'DisplayPaymentHookController'),
                $this->priceHelper->formatPriceToCentsByCurrencyId($plans[0]['total_amount']) . ' ' . $this->customFieldsHelper->getDescriptionPaymentTriggerByLang($idLang),
                $nbPlans - 1,
                $this->priceHelper->formatPriceToCentsByCurrencyId($plans[1]['total_amount'])
            );
        }
        if ($nbPlans > 1) {
            return sprintf(
                $this->module->l('%1$s today then %2$d x %3$s', 'DisplayPaymentHookController'),
                $this->priceHelper->formatPriceToCentsByCurrencyId($plans[0]['total_amount']),
                $nbPlans - 1,
                $this->priceHelper->formatPriceToCentsByCurrencyId($plans[1]['total_amount'])
            );
        }
        if ($isPayNow) {
            return '';
        }

        return sprintf(
            $this->module->l('0 € today then %1$s on %2$s', 'DisplayPaymentHookController'),
            $this->priceHelper->formatPriceToCentsByCurrencyId($plans[0]['purchase_amount'] + $plans[0]['customer_fee']),
            $this->dateHelper->getDateFormat($locale, $plans[0]['due_date'])
        );
    }

    private function displayAlmaPaymentOption($paymentOption)
    {
        $this->context->smarty->assign(
            [
                'options' => $paymentOption,
                'old_prestashop_version' => version_compare(_PS_VERSION_, '1.6', '<'),
                'apiMode' => strtoupper(SettingsHelper::getActiveMode()),
                'merchantId' => SettingsHelper::getMerchantId(),
            ]
        );

        return $this->module->display($this->module->file, 'displayPayment.tpl');
    }

    private function getAlmaLogo($isDeferred, $value)
    {
        $logoName = "p{$value}x_logo.svg";

        if ($isDeferred) {
            $logoName = "{$value}j_logo.svg";
        }

        if (is_callable('\Media::getMediaPath')) {
            $logo = \Media::getMediaPath(
                _PS_MODULE_DIR_ . $this->module->name . "/views/img/logos/{$logoName}"
            );
        } else {
            $logo = $this->module->getPathUri() . "/views/img/logos/{$logoName}";
        }

        return $logo;
    }
}
