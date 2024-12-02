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
     * @var \Alma\PrestaShop\Helpers\PriceHelper|mixed|null
     */
    private $priceHelper;

    public function __construct($settingsHelper = null, $priceHelper = null)
    {
        if (!$settingsHelper) {
            $settingsHelper = (new SettingsHelperBuilder())->getInstance();
        }
        $this->settingsHelper = $settingsHelper;
        if (!$priceHelper) {
            $priceHelper = (new PriceHelperBuilder())->getInstance();
        }
        $this->priceHelper = $priceHelper;
    }

    /**
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
        $installmentsPlans = json_decode(SettingsHelper::getFeePlans());
        $fieldsValue = [];
        $sortOrder = 1;
        foreach ($feePlans as $feePlan) {
            $key = $this->settingsHelper->keyForFeePlan($feePlan);

            $fieldsValue["ALMA_{$key}_ENABLED_ON"] = isset($installmentsPlans->$key->enabled)
                ? $installmentsPlans->$key->enabled
                : 0;

            $minAmount = isset($installmentsPlans->$key->min)
                ? $installmentsPlans->$key->min
                : $feePlan->min_purchase_amount;

            $fieldsValue["ALMA_{$key}_MIN_AMOUNT"] = (int) round(
                $this->priceHelper->convertPriceFromCents($minAmount)
            );
            $maxAmount = isset($installmentsPlans->$key->max)
                ? $installmentsPlans->$key->max
                : $feePlan->max_purchase_amount;

            $fieldsValue["ALMA_{$key}_MAX_AMOUNT"] = (int) $this->priceHelper->convertPriceFromCents($maxAmount);

            $order = isset($installmentsPlans->$key->order)
                ? $installmentsPlans->$key->order
                : $sortOrder;

            $fieldsValue["ALMA_{$key}_SORT_ORDER"] = $order;

            ++$sortOrder;
        }

        return $fieldsValue;
    }
}
