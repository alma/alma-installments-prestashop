<?php
/**
 * 2018-2024 Alma SAS.
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
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Helpers;

use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\ModuleFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class DateHelper.
 *
 * Use for method date
 */
class PlanHelper
{
    /**
     * @var DateHelper
     */
    protected $dateHelper;

    /**
     * @var CustomFieldsHelper
     */
    protected $customFieldsHelper;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @var TranslationHelper
     */
    protected $translationHelper;

    protected $module;

    /**
     * @var \Context
     */
    protected $context;

    /**
     * @param ModuleFactory $moduleFactory
     * @param ContextFactory $contextFactory
     * @param DateHelper $dateHelper
     * @param SettingsHelper $settingsHelper
     * @param CustomFieldsHelper $customFieldsHelper
     * @param TranslationHelper $translationHelper
     */
    public function __construct(
        $moduleFactory,
        $contextFactory,
        $dateHelper,
        $settingsHelper,
        $customFieldsHelper,
        $translationHelper
    ) {
        $this->dateHelper = $dateHelper;
        $this->settingsHelper = $settingsHelper;
        $this->customFieldsHelper = $customFieldsHelper;
        $this->context = $contextFactory->getContext();
        $this->translationHelper = $translationHelper;
        $this->module = $moduleFactory->getModule();
    }

    /**
     * @param $plan
     *
     * @return bool
     */
    public function isCredit($plan)
    {
        return $plan->installmentsCount > 4;
    }

    /**
     * @param \Alma\API\Entities\FeePlan $plan
     *
     * @return bool
     */
    public function isDeferred($plan)
    {
        if (isset($plan->deferred_days)) {
            return 0 < $plan->deferred_days || 0 < $plan->deferred_months;
        }

        return 0 < $plan->deferredDays || 0 < $plan->deferredMonths;
    }

    /**
     * @param array $plans
     * @param string $locale
     * @param object|array $feePlans
     * @param string $key
     * @param bool $isPayNow
     *
     * @return array
     */
    public function buildDates($plans, $locale, $feePlans, $key, $isPayNow)
    {
        foreach ($plans as $keyPlan => $paymentPlan) {
            $plans[$keyPlan]['human_date'] = $this->dateHelper->getDateFormat($locale, $paymentPlan['due_date']);

            if (0 === $keyPlan) {
                $plans[$keyPlan]['human_date'] = $this->translationHelper->l('Today', 'PlanHelper');
                continue;
            }

            if ($isPayNow) {
                $plans[$keyPlan]['human_date'] = $this->translationHelper->l('Total', 'PlanHelper');
                continue;
            }

            if ($this->settingsHelper->isDeferredTriggerLimitDays($feePlans, $key)) {
                $plans[$keyPlan]['human_date'] = sprintf(
                    $this->translationHelper->l('%s month later', 'PaymentService'),
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
}
