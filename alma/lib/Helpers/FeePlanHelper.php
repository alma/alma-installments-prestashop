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

use Alma\PrestaShop\Exceptions\PnxFormException;
use Alma\PrestaShop\Factories\EligibilityFactory;
use Alma\PrestaShop\Proxy\ToolsProxy;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class FeePlanHelper.
 */
class FeePlanHelper
{
    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @var EligibilityFactory
     */
    protected $eligibilityFactory;
    /**
     * @var PriceHelper
     */
    private $priceHelper;
    /**
     * @var ToolsProxy
     */
    private $toolsProxy;

    public function __construct(
        $settingsHelper,
        $eligibilityFactory,
        $priceHelper,
        $toolsProxy
    ) {
        $this->settingsHelper = $settingsHelper;
        $this->eligibilityFactory = $eligibilityFactory;
        $this->priceHelper = $priceHelper;
        $this->toolsProxy = $toolsProxy;
    }

    /**
     * @return array
     */
    public function checkFeePlans()
    {
        $feePlans = array_filter((array) json_decode($this->settingsHelper->getAlmaFeePlans()), function ($feePlan) {
            return $feePlan->enabled == 1;
        });

        if (!$feePlans) {
            return [];
        }

        return $feePlans;
    }

    /**
     * @param array $feePlans
     * @param $purchaseAmount
     *
     * @return array
     */
    public function getNotEligibleFeePlans($feePlans, $purchaseAmount)
    {
        $eligibilities = [];

        foreach ($feePlans as $key => $feePlan) {
            $data = $this->settingsHelper->getDataFromKey($key);

            if (
                $purchaseAmount < $feePlan->min
                || $purchaseAmount > $feePlan->max
            ) {
                $eligibilities[] = $this->eligibilityFactory->createEligibility($data, $feePlan);
            }
        }

        return $eligibilities;
    }

    public function getEligibleFeePlans($feePlans, $purchaseAmount)
    {
        $activePlans = [];

        foreach ($feePlans as $key => $feePlan) {
            $getDataFromKey = $this->settingsHelper->getDataFromKey($key);

            if (
                $purchaseAmount >= $feePlan->min
                && $purchaseAmount <= $feePlan->max
            ) {
                $activePlans[] = $getDataFromKey;
            }
        }

        return $activePlans;
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\PnxFormException
     */
    public function checkLimitsSaveFeePlans($feePlans)
    {
        foreach ($feePlans as $feePlan) {
            $installment = $feePlan->installments_count;
            $deferred_days = $feePlan->deferred_days;
            $deferred_months = $feePlan->deferred_months;
            $key = $this->settingsHelper->keyForFeePlan($feePlan);

            if ($this->shouldSkipPlan($installment, $feePlan)) {
                continue;
            }

            $min = $this->priceHelper->convertPriceToCents((int) $this->toolsProxy->getValue("ALMA_{$key}_MIN_AMOUNT"));
            $max = $this->priceHelper->convertPriceToCents((int) $this->toolsProxy->getValue("ALMA_{$key}_MAX_AMOUNT"));
            $limitMin = $this->priceHelper->convertPriceFromCents($feePlan->min_purchase_amount);
            $limitMax = $this->priceHelper->convertPriceFromCents(min($max, $feePlan->max_purchase_amount));

            $enablePlan = (bool) $this->toolsProxy->getValue("ALMA_{$key}_ENABLED_ON");

            if ($enablePlan && !$this->isWithinLimits($min, $feePlan->min_purchase_amount, $max, $feePlan->max_purchase_amount)) {
                throw new PnxFormException($this->generateErrorMessage('Minimum', $installment, $deferred_days, $deferred_months, $limitMin, $limitMax));
            }

            if ($enablePlan && !$this->isWithinLimits($max, $min, $feePlan->max_purchase_amount, $feePlan->max_purchase_amount)) {
                throw new PnxFormException($this->generateErrorMessage('Maximum', $installment, $deferred_days, $deferred_months, $limitMin, $limitMax));
            }
        }
    }

    /**
     * Vérifie si un plan doit être ignoré.
     */
    private function shouldSkipPlan($installment, $feePlan)
    {
        return $installment !== 1 && $this->settingsHelper->isDeferred($feePlan);
    }

    /**
     * Vérifie si une valeur est dans les limites spécifiées.
     */
    private function isWithinLimits($amount, $min, $max, $feePlanMax)
    {
        return $amount >= $min && $amount <= min($max, $feePlanMax);
    }

    /**
     * Génère un message d'erreur personnalisé en fonction des limites.
     */
    private function generateErrorMessage($type, $installment, $deferred_days, $deferred_months, $limitMin, $limitMax)
    {
        if ($installment === 1) {
            if ($deferred_days > 0 && $deferred_months === 0) {
                return sprintf(
                    '%s amount for deferred + %s days plan must be within %s and %s.',
                    ucfirst($type),
                    $deferred_days,
                    $limitMin,
                    $limitMax
                );
            }

            if ($deferred_days === 0 && $deferred_months > 0) {
                return sprintf(
                    '%s amount for deferred + %s months plan must be within %s and %s.',
                    ucfirst($type),
                    $deferred_months,
                    $limitMin,
                    $limitMax
                );
            }
        }

        return sprintf(
            '%s amount for %s-installment plan must be within %s and %s.',
            ucfirst($type),
            $installment,
            $limitMin,
            $limitMax
        );
    }
}
