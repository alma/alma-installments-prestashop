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

use Alma\API\Endpoints\Results\Eligibility;
use Alma\API\Entities\FeePlan;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Model\PaymentData;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EligibilityHelper
{
    /**
     * @var PaymentData
     */
    protected $paymentData;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var ClientHelper
     */
    protected $clientHelper;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @var ApiHelper
     */
    protected $apiHelper;

    /**
     * @var \Context
     */
    protected $context;

    /**
     * @param PaymentData $paymentData
     * @param PriceHelper $priceHelper
     * @param ClientHelper $clientHelper
     * @param SettingsHelper $settingsHelper
     * @param ApiHelper $apiHelper
     * @param ContextFactory $contextFactory
     *
     * @codeCoverageIgnore
     */
    public function __construct($paymentData, $priceHelper, $clientHelper, $settingsHelper, $apiHelper, $contextFactory)
    {
        $this->paymentData = $paymentData;
        $this->priceHelper = $priceHelper;
        $this->clientHelper = $clientHelper;
        $this->settingsHelper = $settingsHelper;
        $this->apiHelper = $apiHelper;
        $this->context = $contextFactory->getContext();
    }

    /**
     * @return array
     *
     * @throws \Alma\API\ParamsError
     */
    public function eligibilityCheck()
    {
        $almaEligibilities = [];
        $purchaseAmount = $this->priceHelper->convertPriceToCents(
            $this->context->cart->getOrderTotal(true, \Cart::BOTH)
        );
        $feePlans = $this->checkFeePlans();
        $eligibilities = $this->getNotEligibleFeePlans($feePlans, $purchaseAmount);
        $activePlans = $this->getEligibleFeePlans($feePlans, $purchaseAmount);
        $paymentData = $this->checkPaymentData($activePlans);

        if (empty($activePlans)) {
            return $almaEligibilities;
        }

        $almaEligibilities = $this->apiHelper->getPaymentEligibility($paymentData);

        if ($almaEligibilities instanceof Eligibility) {
            $almaEligibilities = [$almaEligibilities];
        }

        $eligibilities = array_merge((array) $eligibilities, (array) $almaEligibilities);

        usort($eligibilities, function ($a, $b) {
            return $a->installmentsCount - $b->installmentsCount;
        });

        return $eligibilities;
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
     * @param $activePlans
     *
     * @return array
     *
     * @throws \Alma\API\ParamsError
     */
    public function checkPaymentData($activePlans)
    {
        $paymentData = $this->paymentData->dataFromCart($activePlans);

        if (!$paymentData) {
            Logger::instance()->error('Cannot check cart eligibility: no data extracted from cart');

            return [];
        }

        return $paymentData;
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
                $eligibilities[] = $this->createEligibility($data, $feePlan);
            }
        }

        return $eligibilities;
    }

    /**
     * @param array $data
     * @param FeePlan $feePlan
     *
     * @return Eligibility
     */
    public function createEligibility($data, $feePlan, $eligible = false)
    {
        return new Eligibility(
            [
                'installments_count' => $data['installmentsCount'],
                'deferred_days' => $data['deferredDays'],
                'deferred_months' => $data['deferredMonths'],
                'eligible' => $eligible,
                'constraints' => [
                    'purchase_amount' => [
                        'minimum' => $feePlan->min,
                        'maximum' => $feePlan->max,
                    ],
                ],
            ]
        );
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
