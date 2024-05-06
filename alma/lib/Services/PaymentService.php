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

use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Helpers\CartHelper;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ContextHelper;
use Alma\PrestaShop\Helpers\CustomFieldsHelper;
use Alma\PrestaShop\Helpers\DateHelper;
use Alma\PrestaShop\Helpers\EligibilityHelper;
use Alma\PrestaShop\Helpers\LocaleHelper;
use Alma\PrestaShop\Helpers\MediaHelper;
use Alma\PrestaShop\Helpers\PaymentOptionHelper;
use Alma\PrestaShop\Helpers\PaymentOptionTemplateHelper;
use Alma\PrestaShop\Helpers\PlanHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Helpers\TranslationHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Model\CartData;

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
     * @var PlanHelper
     */
    protected $planHelper;

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
     * @var \Context
     */
    protected $context;

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

    /**
     * @var ConfigurationHelper
     */
    protected $configurationHelper;

    /**
     * @var TranslationHelper
     */
    protected $translationHelper;

    /**
     * @var PaymentOptionTemplateHelper
     */
    protected $paymentOptionTemplateHelper;

    /**
     * @var PaymentOptionHelper
     */
    protected $paymentOptionHelper;

    /**
     * @var CartHelper
     */
    protected $cartHelper;

    /**
     * @param ContextFactory $contextFactory
     * @param ModuleFactory $moduleFactory
     * @param SettingsHelper $settingsHelper
     * @param LocaleHelper $localeHelper
     * @param ToolsHelper $toolsHelper
     * @param EligibilityHelper $eligibilityHelper
     * @param PriceHelper $priceHelper
     * @param DateHelper $dateHelper
     * @param CustomFieldsHelper $customFieldsHelper
     * @param CartData $cartData
     * @param ContextHelper $contextHelper
     * @param MediaHelper $mediaHelper
     * @param PlanHelper $planHelper
     * @param ConfigurationHelper $configurationHelper
     * @param TranslationHelper $translationHelper
     * @param CartHelper $cartHelper
     * @param PaymentOptionTemplateHelper $paymentOptionTemplateHelper
     * @param PaymentOptionHelper $paymentOptionHelper
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        $contextFactory,
        $moduleFactory,
        $settingsHelper,
        $localeHelper,
        $toolsHelper,
        $eligibilityHelper,
        $priceHelper,
        $dateHelper,
        $customFieldsHelper,
        $cartData,
        $contextHelper,
        $mediaHelper,
        $planHelper,
        $configurationHelper,
        $translationHelper,
        $cartHelper,
        $paymentOptionTemplateHelper,
        $paymentOptionHelper
    ) {
        $this->context = $contextFactory->getContext();
        $this->module = $moduleFactory->getModule();
        $this->settingsHelper = $settingsHelper;
        $this->localeHelper = $localeHelper;
        $this->toolsHelper = $toolsHelper;
        $this->eligibilityHelper = $eligibilityHelper;
        $this->priceHelper = $priceHelper;
        $this->dateHelper = $dateHelper;
        $this->customFieldsHelper = $customFieldsHelper;
        $this->cartData = $cartData;
        $this->contextHelper = $contextHelper;
        $this->mediaHelper = $mediaHelper;
        $this->planHelper = $planHelper;
        $this->configurationHelper = $configurationHelper;
        $this->translationHelper = $translationHelper;
        $this->cartHelper = $cartHelper;
        $this->paymentOptionTemplateHelper = $paymentOptionTemplateHelper;
        $this->paymentOptionHelper = $paymentOptionHelper;
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

            $installmentPlans = $this->eligibilityHelper->eligibilityCheck();

            $locale = $this->localeHelper->getLocaleByIdLangForWidget($this->context->language->id);

            if (empty($installmentPlans)) {
                return [];
            }

            $forEUComplianceModule = $this->paymentOptionHelper->getEuCompliance($params);

            $paymentOptions = [];
            $sortOptions = [];
            $feePlans = json_decode($this->settingsHelper->getAlmaFeePlans());
            $countIteration = 1;

            $this->totalCart = $this->cartHelper->getCartTotal($this->context->cart);

            foreach ($installmentPlans as $plan) {
                if (!$plan->isEligible) {
                    continue;
                }

                $first = 1 == $countIteration;
                ++$countIteration;

                $key = $this->settingsHelper->keyForInstallmentPlan($plan);
                $plans = $plan->paymentPlan;

                $this->isPayNow = $this->configurationHelper->isPayNow($key);
                $this->isDeferred = $this->planHelper->isDeferred($plan);
                $this->isPnxPlus4 = $this->planHelper->isPnxPlus4($plan);

                $plans = $this->planHelper->buildDates($plans, $locale, $feePlans, $key, $this->isPayNow);
                $duration = $this->settingsHelper->getDuration($plan);

                list($textPaymentButton, $descPaymentButton) = $this->paymentOptionHelper->getTextsByTypes(
                    $plan->installmentsCount,
                    $duration,
                    $this->isPnxPlus4,
                    $this->isDeferred,
                    $this->isPayNow
                );

                list($fileTemplate, $valueBNPL) = $this->paymentOptionTemplateHelper->getTemplateAndBnpl(
                    $plan->installmentsCount,
                    $duration,
                    $this->isDeferred
                );

                $action = $this->contextHelper->getModuleLink(
                    'payment',
                    ['key' => $key],
                    true
                );

                $paymentOption = $this->paymentOptionHelper->createPaymentOption(
                    $forEUComplianceModule,
                    $textPaymentButton,
                    $action,
                    $valueBNPL,
                    $this->isDeferred
                );

                if (!$forEUComplianceModule) {
                    $templateParams = $this->paymentOptionTemplateHelper->buildTemplateVar(
                        $plan->installmentsCount . '-' . $duration,
                        $action,
                        $descPaymentButton,
                        $plans,
                        $feePlans->$key->deferred_trigger_limit_days,
                        $plan,
                        $first,
                        $locale,
                        $this->totalCart,
                        $this->isDeferred
                    );

                    $template = $this->paymentOptionTemplateHelper->buildSmartyTemplate($templateParams, $fileTemplate);

                    $paymentOption = $this->paymentOptionHelper->setAdditionalInformationForEuCompliance(
                        $paymentOption,
                        $template,
                        $plan->installmentsCount
                    );
                }

                $sortOptions[$key] = $feePlans->$key->order;
                $paymentOptions[$key] = $paymentOption;
            }

            return $this->paymentOptionHelper->sortPaymentsOptions($sortOptions, $paymentOptions);
        } catch (\Exception $e) {
            Logger::instance()->error(
                sprintf(
                'An error occured when displaying options payments - message : %s, %s',
                $e->getMessage(),
                $e->getTraceAsString()
                ));
        }
    }
}
