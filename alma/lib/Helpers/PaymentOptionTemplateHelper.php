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

namespace Alma\PrestaShop\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class PaymentOptionTemplateHelper.
 */
class PaymentOptionTemplateHelper
{
    protected $module;

    /**
     * @var \Context
     */
    protected $context;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @var ConfigurationHelper
     */
    protected $configurationHelper;

    /**
     * @var TranslationHelper
     */
    protected $translationHelper;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var DateHelper
     */
    protected $dateHelper;

    /**
     * @param \Context $context
     * @param $module
     * @param SettingsHelper $settingsHelper
     * @param ConfigurationHelper $configurationHelper
     * @param TranslationHelper $translationHelper
     * @param PriceHelper $priceHelper
     * @param DateHelper $dateHelper
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        $context,
        $module,
        $settingsHelper,
        $configurationHelper,
        $translationHelper,
        $priceHelper,
        $dateHelper
    ) {
        $this->context = $context;
        $this->module = $module;
        $this->settingsHelper = $settingsHelper;
        $this->configurationHelper = $configurationHelper;
        $this->translationHelper = $translationHelper;
        $this->priceHelper = $priceHelper;
        $this->dateHelper = $dateHelper;
    }

    /**
     * @return mixed
     */
    public function getTemplateInPage()
    {
        return $this->context->smarty->fetch(
            "module:{$this->module->name}/views/templates/front/payment_form_inpage.tpl"
        );
    }

    /**
     * @param array $params
     * @param string $fileTemplate
     *
     * @return mixed
     */
    public function buildSmartyTemplate($params, $fileTemplate)
    {
        $this->context->smarty->assign($params);

        return $this->context->smarty->fetch(
            "module:{$this->module->name}/views/templates/hook/{$fileTemplate}"
        );
    }

    /**
     * @param string $keyPlan
     * @param string $action
     * @param string $desc
     * @param array $plans
     * @param int $deferredTrigger
     * @param $plan
     * @param $first
     * @param $locale
     * @param $cartTotal
     * @param bool $isDeferred
     *
     * @return array
     */
    public function buildTemplateVar(
        $keyPlan,
        $action,
        $desc,
        $plans,
        $deferredTrigger,
        $plan,
        $first,
        $locale,
        $cartTotal,
        $isDeferred
    ) {
        $templateVar = [
            'keyPlan' => $keyPlan,
            'action' => $action,
            'desc' => $desc,
            'plans' => (array) $plans,
            'deferred_trigger_limit_days' => $deferredTrigger,
            'apiMode' => strtoupper($this->settingsHelper->getModeActive()),
            'merchantId' => $this->settingsHelper->getIdMerchant(),
            'isInPageEnabled' => $this->configurationHelper->isInPageEnabled(
                $plan->installmentsCount,
                $this->settingsHelper
            ),
            'first' => $first,
            'creditInfo' => [
                'totalCart' => $cartTotal,
                'costCredit' => $plan->customerTotalCostAmount,
                'totalCredit' => $plan->customerTotalCostAmount + $cartTotal,
                'taeg' => $plan->annualInterestRate,
            ],
            'installment' => $plan->installmentsCount,
            'deferredDays' => $plan->deferredDays,
            'deferredMonths' => $plan->deferredMonths,
            'locale' => $locale,
        ];

        if ($isDeferred) {
            $templateVar['installmentText'] = sprintf(
                $this->translationHelper->getTranslation('0 € today then %1$s on %2$s', 'PaymentOptionsHookController'),
                $this->priceHelper->formatPriceToCentsByCurrencyId(
                    $plans[0]['purchase_amount'] + $plans[0]['customer_fee']
                ),
                $this->dateHelper->getDateFormat($locale, $plans[0]['due_date'])
            );
        }

        return $templateVar;
    }

    /**
     * @param int $installments
     * @param int $duration
     * @param bool $isDeferred
     *
     * @return array
     */
    public function getTemplateAndBnpl($installments, $duration, $isDeferred)
    {
        if ($isDeferred) {
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
}
