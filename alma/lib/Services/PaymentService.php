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

namespace Alma\PrestaShop\Services;

use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\ContextHelper;
use Alma\PrestaShop\Helpers\CurrencyHelper;
use Alma\PrestaShop\Helpers\CustomFieldsHelper;
use Alma\PrestaShop\Helpers\DateHelper;
use Alma\PrestaShop\Helpers\EligibilityHelper;
use Alma\PrestaShop\Helpers\LanguageHelper;
use Alma\PrestaShop\Helpers\LocaleHelper;
use Alma\PrestaShop\Helpers\MediaHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ShopHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Model\CartData;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class PaymentService.
 */
class PaymentService
{
    /**
     * @var LocaleHelper
     */
    protected $localeHelper;

    /**
     * @var ToolsHelper
     */
    protected $toolsHelper;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @var EligibilityHelper
     */
    protected $eligibilityHelper;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var DateHelper
     */
    protected $dateHelper;

    /**
     * @var CartData
     */
    protected $cartData;

    /**
     * @var CustomFieldsHelper
     */
    protected $customFieldsHelper;

    /**
     * @var float
     */
    protected $totalCart;

    /**
     * @var ContextHelper
     */
    protected $contextHelper;

    protected $module;

    /**
     * @var bool
     */
    protected $isPayNow;

    /**
     * @var bool
     */
    protected $isDeferred;

    /**
     * @var bool
     */
    protected $isPnxPlus4;

    /**
     * @var MediaHelper
     */
    protected $mediaHelper;

    public function __construct($context, $module)
    {
        $this->context = $context;
        $this->module = $module;
        $this->settingsHelper = new SettingsHelper(new ShopHelper(), new ConfigurationHelper());
        $this->localeHelper = new LocaleHelper(new LanguageHelper());
        $this->toolsHelper = new ToolsHelper();
        $this->eligibilityHelper = new EligibilityHelper();
        $this->priceHelper = new PriceHelper(new ToolsHelper(), new CurrencyHelper());
        $this->dateHelper = new DateHelper();
        $this->customFieldsHelper = new CustomFieldsHelper(new LanguageHelper(), $this->localeHelper);
        $this->cartData = new CartData(new ProductHelper(), $this->settingsHelper);
        $this->contextHelper = new ContextHelper();
        $this->mediaHelper = new MediaHelper();
    }

    /**
     * @param $params
     * @param $module
     *
     * @return array
     */
    public function createPaymentOptions($params)
    {
        try {
            //  Check if some products in cart are in the excludes listing
            if (!empty($this->cartData->getCartExclusion($params['cart']))) {
                return [];
            }

            $installmentPlans = $this->eligibilityHelper->eligibilityCheck($this->context);

            $locale = $this->localeHelper->getLocaleByIdLangForWidget($this->context->language->id);

            if (empty($installmentPlans)) {
                return [];
            }

            $forEUComplianceModule = $this->getEuCompliance($params);

            $paymentOptions = [];
            $sortOptions = [];
            $feePlans = json_decode($this->settingsHelper->getAlmaFeePlans());
            $countIteration = 1;

            $this->totalCart = $this->getCartTotal();

            foreach ($installmentPlans as $plan) {
                if (!$plan->isEligible) {
                    continue;
                }

                $first = 1 == $countIteration;
                ++$countIteration;

                $key = $this->settingsHelper->keyForInstallmentPlan($plan);
                $plans = $plan->paymentPlan;

                $this->isPayNow = $this->isPayNow($key);
                $this->isDeferred = $this->settingsHelper->isDeferred($plan);
                $this->isPnxPlus4 = $this->isPnxPlus4($plan);

                $plans = $this->buildDates($plans, $locale, $feePlans, $key);
                $duration = $this->settingsHelper->getDuration($plan);

                list($textPaymentButton, $descPaymentButton) = $this->getTextsByTypes(
                    $plan->installmentsCount,
                    $duration
                );

                list($fileTemplate, $valueBNPL) = $this->getTemplateAndBnpl(
                    $plan->installmentsCount,
                    $duration
                );

                $action = $this->contextHelper->getModuleLink(
                    $this->context,
                    $this->module->name,
                    'payment',
                    ['key' => $key],
                    true
                );

                $paymentOption = $this->createPaymentOption(
                    $forEUComplianceModule,
                    $textPaymentButton,
                    $action,
                    $valueBNPL
                );

                if (!$forEUComplianceModule) {
                    $templateParams = $this->buildTemplateVar(
                        $plan->installmentsCount . '-' . $duration,
                        $action,
                        $descPaymentButton,
                        $plans,
                        $feePlans->$key->deferred_trigger_limit_days,
                        $plan,
                        $first,
                        $locale
                    );

                    $template = $this->buildSmartyTemplate($templateParams, $fileTemplate);

                    $paymentOption->setAdditionalInformation($template);

                    if ($this->isInPageEnabled($plan->installmentsCount)) {
                        $paymentOption->setForm($this->getTemplateInPage());
                    }
                }

                $sortOptions[$key] = $feePlans->$key->order;
                $paymentOptions[$key] = $paymentOption;
            }

            return $this->sortPaymentsOptions($sortOptions, $paymentOptions);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            Logger::instance()->error(
                sprintf(
                'An error occured when displaying options payments - message : %s, %s',
                $e->getMessage(),
                $e->getTraceAsString()
                ));
        }
    }

    /**
     * @param $sortOptions
     * @param $paymentOptions
     *
     * @return array
     */
    protected function sortPaymentsOptions($sortOptions, $paymentOptions)
    {
        asort($sortOptions);
        $payment = [];
        foreach (array_keys($sortOptions) as $key) {
            $payment[] = $paymentOptions[$key];
        }

        return $payment;
    }

    /**
     * @return mixed
     */
    protected function getTemplateInPage()
    {
        return $this->context->smarty->fetch(
            "module:{$this->module->name}/views/templates/front/payment_form_inpage.tpl"
        );
    }

    /**
     * @param $params
     * @param $fileTemplate
     *
     * @return mixed
     */
    protected function buildSmartyTemplate($params, $fileTemplate)
    {
        $this->context->smarty->assign($params);

        return $this->context->smarty->fetch(
            "module:{$this->module->name}/views/templates/hook/{$fileTemplate}"
        );
    }

    /**
     * @param $keyPlan
     * @param $action
     * @param $desc
     * @param $plans
     * @param $deferredTrigger
     * @param $plan
     * @param $first
     * @param $locale
     *
     * @return array
     */
    protected function buildTemplateVar($keyPlan, $action, $desc, $plans, $deferredTrigger, $plan, $first, $locale)
    {
        $templateVar = [
            'keyPlan' => $keyPlan,
            'action' => $action,
            'desc' => $desc,
            'plans' => (array) $plans,
            'deferred_trigger_limit_days' => $deferredTrigger,
            'apiMode' => strtoupper($this->settingsHelper->getModeActive()),
            'merchantId' => $this->settingsHelper->getIdMerchant(),
            'isInPageEnabled' => $this->isInPageEnabled($plan->installmentsCount),
            'first' => $first,
            'creditInfo' => [
                'totalCart' => $this->totalCart,
                'costCredit' => $plan->customerTotalCostAmount,
                'totalCredit' => $plan->customerTotalCostAmount + $this->totalCart,
                'taeg' => $plan->annualInterestRate,
            ],
            'installment' => $plan->installmentsCount,
            'deferredDays' => $plan->deferredDays,
            'deferredMonths' => $plan->deferredMonths,
            'locale' => $locale,
        ];

        if ($this->isDeferred) {
            $templateVar['installmentText'] = sprintf(
                $this->getTranslation('0 € today then %1$s on %2$s', 'PaymentOptionsHookController'),
                $this->priceHelper->formatPriceToCentsByCurrencyId(
                    $plans[0]['purchase_amount'] + $plans[0]['customer_fee']
                ),
                $this->dateHelper->getDateFormat($locale, $plans[0]['due_date'])
            );
        }

        return $templateVar;
    }

    /**
     * @param $installments
     * @param $duration
     *
     * @return array
     */
    protected function getTemplateAndBnpl($installments, $duration)
    {
        if ($this->isDeferred) {
            return [
                 'payment_button_deferred.tpl',
                 $duration,
            ];
        }

        return [
             'payment_button_pnx.tpl',
             $installments,
        ];
    }

    /**
     * @param $installementCount
     *
     * @return array
     */
    protected function getTextsByTypes($installementCount, $duration)
    {
        if ($this->isPnxPlus4) {
            return $this->getTexts(
                $installementCount,
                PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE,
                PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC
            );
        }
        if ($this->isDeferred) {
            return $this->getTexts(
                $duration,
                PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE,
                PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC
            );
        }
        if ($this->isPayNow) {
            return $this->getTexts(
                $installementCount,
                PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_TITLE,
                PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_DESC
            );
        }

        return $this->getTexts(
            $installementCount,
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE,
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC
        );
    }

    /**
     * @param $plan
     *
     * @return bool
     */
    protected function isPnxPlus4($plan)
    {
        return $plan->installmentsCount > 4;
    }

    /**
     * @param $plans
     * @param $locale
     * @param $feePlans
     * @param $key
     *
     * @return array
     */
    protected function buildDates($plans, $locale, $feePlans, $key)
    {
        foreach ($plans as $keyPlan => $paymentPlan) {
            $plans[$keyPlan]['human_date'] = $this->dateHelper->getDateFormat($locale, $paymentPlan['due_date']);

            if (0 === $keyPlan) {
                $plans[$keyPlan]['human_date'] = $this->getTranslation('Today', 'PaymentService');
                continue;
            }

            if ($this->isPayNow) {
                $plans[$keyPlan]['human_date'] = $this->getTranslation('Total', 'PaymentService');
                continue;
            }

            if ($this->settingsHelper->isDeferredTriggerLimitDays($feePlans, $key)) {
                $plans[$keyPlan]['human_date'] = sprintf(
                    $this->getTranslation('%s month later', 'PaymentService'),
                    $keyPlan
                );

                if (0 === $keyPlan) {
                    $plans[$keyPlan]['human_date'] = $this->customFieldsHelper->getDescriptionPaymentTriggerByLang(
                        $this->context->language->id
                    );
                }
            }
        }

        return $plans;
    }

    /**
     * @param $string
     * @param $file
     *
     * @return mixed
     */
    protected function getTranslation($string, $file)
    {
        return $this->module->l($string, $file);
    }

    /**
     * @param $key
     *
     * @return bool
     */
    protected function isPayNow($key)
    {
        return ConstantsHelper::ALMA_KEY_PAYNOW === $key;
    }

    /**
     * @param $installments
     *
     * @return bool
     */
    protected function isInPageEnabled($installments)
    {
        $isInPageEnabled = $this->settingsHelper->isInPageEnabled();

        if ($installments > 4) {
            $isInPageEnabled = false;
        }

        return $isInPageEnabled;
    }

    /**
     * @param $installment
     * @param $keyTitle
     * @param $keyDescription
     *
     * @return array
     */
    protected function getTexts($installment, $keyTitle, $keyDescription)
    {
        return [
             $this->customFieldsHelper->getTextButton(
                    $this->context->language->id,
                    $keyTitle,
                    $installment
                ),
             $this->customFieldsHelper->getTextButton(
                $this->context->language->id,
                $keyDescription,
                $installment
            ),
        ];
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
    protected function createPaymentOption($forEUComplianceModule, $ctaText, $action, $valueBNPL)
    {
        $logoName = $this->getLogoName($valueBNPL);

        if ($forEUComplianceModule) {
            $logo = $this->mediaHelper->getMediaPath(
                 '/views/img/logos/alma_payment_logos_tiny.svg',
                $this->module
            );

            return [
                'cta_text' => $ctaText,
                'action' => $action,
                'logo' => $logo,
            ];
        }

        $paymentOption = new PaymentOption();
        $logo = $this->mediaHelper->getMediaPath(
             '/views/img/logos/' . $logoName,
            $this->module
        );

        return $paymentOption
                ->setModuleName($this->module->name)
                ->setCallToActionText($ctaText)
                ->setAction($action)
                ->setLogo($logo);
    }

    /**
     * @param $valueBNPL
     *
     * @return string
     */
    protected function getLogoName($valueBNPL)
    {
        if ($this->isDeferred) {
            return "{$valueBNPL}j_logo.svg";
        }

        return "p{$valueBNPL}x_logo.svg";
    }

    /**
     * @return float
     */
    protected function getCartTotal()
    {
        return (float) $this->priceHelper->convertPriceToCents(
            $this->toolsHelper->psRound((float) $this->context->cart->getOrderTotal(true, \Cart::BOTH), 2)
        );
    }

    /**
     * @param $params
     *
     * @return false|mixed
     */
    protected function getEuCompliance($params)
    {
        $forEUComplianceModule = false;

        if (array_key_exists('for_eu_compliance_module', $params)) {
            $forEUComplianceModule = $params['for_eu_compliance_module'];
        }

        return $forEUComplianceModule;
    }
}
