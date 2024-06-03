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

use Alma\PrestaShop\Factories\EligibilityFactory;

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

    public function __construct($settingsHelper, $eligibilityFactory)
    {
        $this->settingsHelper = $settingsHelper;
        $this->eligibilityFactory = $eligibilityFactory;
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
                $purchaseAmount > $feePlan->min
                && $purchaseAmount < $feePlan->max
            ) {
                $activePlans[] = $getDataFromKey;
            }
        }

        return $activePlans;
    }
}
