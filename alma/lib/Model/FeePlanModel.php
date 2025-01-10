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

namespace Alma\PrestaShop\Model;

use Alma\PrestaShop\Builders\Helpers\PriceHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\SettingsHelperBuilder;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Proxy\ConfigurationProxy;
use Alma\PrestaShop\Proxy\ToolsProxy;

if (!defined('_PS_VERSION_')) {
    exit;
}

class FeePlanModel
{
    /**
     * @var SettingsHelper
     */
    private $settingsHelper;
    /**
     * @var \Alma\PrestaShop\Helpers\PriceHelper
     */
    private $priceHelper;
    /**
     * @var \Alma\PrestaShop\Proxy\ToolsProxy
     */
    private $toolsProxy;
    /**
     * @var \Alma\PrestaShop\Proxy\ConfigurationProxy
     */
    private $configurationProxy;

    public function __construct(
        $settingsHelper = null,
        $priceHelper = null,
        $toolsProxy = null,
        $configurationProxy = null
    ) {
        if (!$settingsHelper) {
            $settingsHelper = (new SettingsHelperBuilder())->getInstance();
        }
        $this->settingsHelper = $settingsHelper;
        if (!$priceHelper) {
            $priceHelper = (new PriceHelperBuilder())->getInstance();
        }
        $this->priceHelper = $priceHelper;
        if (!$toolsProxy) {
            $toolsProxy = new ToolsProxy();
        }
        $this->toolsProxy = $toolsProxy;
        if (!$configurationProxy) {
            $configurationProxy = new ConfigurationProxy();
        }
        $this->configurationProxy = $configurationProxy;
    }

    /**
     * Get the fee plans ordered by Pnx installment then Pay Later duration
     *
     * @param $feePlans
     *
     * @return array
     */
    public function getFeePlansOrdered($feePlans)
    {
        $feePlansOrdered = [];
        // sort fee plans by pnx then by pay later duration
        $feePlanDeferred = [];

        foreach ($feePlans as $feePlan) {
            if (!$this->settingsHelper->isDeferred($feePlan)) {
                $feePlansOrdered[$feePlan->installments_count] = $feePlan;
                continue;
            }

            $duration = $this->settingsHelper->getDuration($feePlan);
            $feePlanDeferred[$feePlan->installments_count . $duration] = $feePlan;
        }

        ksort($feePlanDeferred);

        return array_merge($feePlansOrdered, $feePlanDeferred);
    }

    /**
     * Get the field values from the fee plans and sort it
     *
     * @param array $feePlans
     *
     * @return array
     */
    public function getFieldsValueFromFeePlans($feePlans)
    {
        $currentFeePlans = json_decode($this->configurationProxy->get('ALMA_FEE_PLANS'));
        $fieldsValue = [];
        $sortOrder = 1;
        foreach ($feePlans as $feePlan) {
            $key = $this->settingsHelper->keyForFeePlan($feePlan);

            $fieldsValue["ALMA_{$key}_ENABLED_ON"] = isset($currentFeePlans->$key->enabled)
                ? $currentFeePlans->$key->enabled
                : 0;

            $minAmount = isset($currentFeePlans->$key->min)
                ? $currentFeePlans->$key->min
                : $feePlan->min_purchase_amount;

            $fieldsValue["ALMA_{$key}_MIN_AMOUNT"] = (int) round(
                $this->priceHelper->convertPriceFromCents($minAmount)
            );
            $maxAmount = isset($currentFeePlans->$key->max)
                ? $currentFeePlans->$key->max
                : $feePlan->max_purchase_amount;

            $fieldsValue["ALMA_{$key}_MAX_AMOUNT"] = (int) $this->priceHelper->convertPriceFromCents($maxAmount);

            $order = isset($currentFeePlans->$key->order)
                ? $currentFeePlans->$key->order
                : $sortOrder;

            $fieldsValue["ALMA_{$key}_SORT_ORDER"] = $order;

            ++$sortOrder;
        }

        return $fieldsValue;
    }

    /**
     * Get array fee plans to save in DB configuration
     *
     * @param $feePlans
     *
     * @return array
     */
    public function getFeePlanForSave($feePlans)
    {
        $almaPlans = [];
        $position = 1;

        foreach ($feePlans as $feePlan) {
            $n = $feePlan->installments_count;
            $key = $this->settingsHelper->keyForFeePlan($feePlan);

            if (1 != $n && $this->settingsHelper->isDeferred($feePlan)) {
                continue;
            }

            $min = (int) $this->toolsProxy->getValue("ALMA_{$key}_MIN_AMOUNT");
            $max = (int) $this->toolsProxy->getValue("ALMA_{$key}_MAX_AMOUNT");
            $order = (int) $this->toolsProxy->getValue("ALMA_{$key}_SORT_ORDER");

            // In case merchant inverted min & max values, correct it
            if ($min > $max) {
                $realMin = $max;
                $max = $min;
                $min = $realMin;
            }

            // in case of difference between sandbox and production feeplans
            if (0 == $min
                && 0 == $max
                && 0 == $order
            ) {
                $almaPlans[$key]['enabled'] = '0';
                $almaPlans[$key]['min'] = $feePlan->min_purchase_amount;
                $almaPlans[$key]['max'] = $feePlan->max_purchase_amount;
                $almaPlans[$key]['deferred_trigger_limit_days'] = $feePlan->deferred_trigger_limit_days;
                $almaPlans[$key]['order'] = (int) $position;
                ++$position;
            } else {
                $enablePlan = (bool) $this->toolsProxy->getValue("ALMA_{$key}_ENABLED_ON");
                $almaPlans[$key]['enabled'] = $enablePlan ? '1' : '0';
                $almaPlans[$key]['min'] = $this->priceHelper->convertPriceToCents($min);
                $almaPlans[$key]['max'] = $this->priceHelper->convertPriceToCents($max);
                $almaPlans[$key]['deferred_trigger_limit_days'] = $feePlan->deferred_trigger_limit_days;
                $almaPlans[$key]['order'] = (int) $this->toolsProxy->getValue("ALMA_{$key}_SORT_ORDER");
            }
        }

        return $almaPlans;
    }
}
