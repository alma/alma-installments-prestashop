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

use Alma\API\Endpoints\Results\Eligibility;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Services\AlmaBusinessDataService;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EligibilityHelper
{
    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var ApiHelper
     */
    protected $apiHelper;

    /**
     * @var ContextFactory
     */
    protected $contextFactory;

    /**
     * @var FeePlanHelper
     */
    protected $feePlanHelper;

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;
    /**
     * @var AlmaBusinessDataService
     */
    private $almaBusinessDataService;

    /**
     * @param PriceHelper $priceHelper
     * @param ApiHelper $apiHelper
     * @param ContextFactory $contextFactory
     * @param FeePlanHelper $feePlanHelper
     * @param PaymentHelper $paymentHelper
     * @param AlmaBusinessDataService $almaBusinessDataService
     */
    public function __construct(
        $priceHelper,
        $apiHelper,
        $contextFactory,
        $feePlanHelper,
        $paymentHelper,
        $almaBusinessDataService
    ) {
        $this->priceHelper = $priceHelper;
        $this->apiHelper = $apiHelper;
        $this->contextFactory = $contextFactory;
        $this->feePlanHelper = $feePlanHelper;
        $this->paymentHelper = $paymentHelper;
        $this->almaBusinessDataService = $almaBusinessDataService;
    }

    /**
     * Return array of all plans (enable or not)
     * Plans eligible from API
     * Plans not eligible from Db
     * Sort plans by installments count
     *
     * @return array
     *
     * @throws \Alma\API\ParamsError
     * @throws \Alma\PrestaShop\Exceptions\AlmaException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function eligibilityCheck()
    {
        $plansEligibleFromApi = [];

        $purchaseAmount = $this->priceHelper->convertPriceToCents(
            $this->contextFactory->getContextCart()->getOrderTotal(true, \Cart::BOTH)
        );

        $plansEnableInDb = $this->feePlanHelper->getFeePlansEnable();
        $plansNotEligible = $this->feePlanHelper->getNotEligibleFeePlans($plansEnableInDb, $purchaseAmount);
        $eligiblePlans = $this->feePlanHelper->getEligibleFeePlans($plansEnableInDb, $purchaseAmount);
        $paymentData = $this->paymentHelper->checkPaymentData($eligiblePlans);

        if (empty($eligiblePlans)) {
            $this->almaBusinessDataService->updateIsBnplEligible(false, $this->contextFactory->getContextCart()->id);

            return $plansEligibleFromApi;
        }

        $plansEligibleFromApi = $this->apiHelper->getPaymentEligibility($paymentData);

        if ($plansEligibleFromApi instanceof Eligibility) {
            $plansEligibleFromApi = [$plansEligibleFromApi];
        }

        $allPlans = array_merge((array) $plansNotEligible, (array) $plansEligibleFromApi);

        usort($allPlans, function ($a, $b) {
            return $a->installmentsCount - $b->installmentsCount;
        });

        $this->almaBusinessDataService->saveIsBnplEligible($allPlans, $this->contextFactory->getContextCart()->id);

        return $allPlans;
    }

    private static function checkClientInstance()
    {
        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            Logger::instance()->error('Cannot check cart eligibility: no API client');

            return [];
        }

        return $alma;
    }

    private static function checkFeePlans()
    {
        $feePlans = array_filter((array) json_decode(SettingsHelper::getFeePlans()), function ($feePlan) {
            return $feePlan->enabled == 1;
        });

        if (!$feePlans) {
            return [];
        }

        return $feePlans;
    }

    private function checkPaymentData($context, $activePlans)
    {
        $paymentData = $this->paymentData->dataFromCart($context->cart, $context, $activePlans);

        if (!$paymentData) {
            Logger::instance()->error('Cannot check cart eligibility: no data extracted from cart');

            return [];
        }

        return $paymentData;
    }

    private static function getNotEligibleFeePlans($feePlans, $purchaseAmount)
    {
        $eligibilities = [];

        foreach ($feePlans as $key => $feePlan) {
            $getDataFromKey = SettingsHelper::getDataFromKey($key);

            if (
                $purchaseAmount < $feePlan->min
                || $purchaseAmount > $feePlan->max
            ) {
                $eligibility = new Eligibility(
                    [
                        'installments_count' => $getDataFromKey['installmentsCount'],
                        'deferred_days' => $getDataFromKey['deferredDays'],
                        'deferred_months' => $getDataFromKey['deferredMonths'],
                        'eligible' => false,
                        'constraints' => [
                            'purchase_amount' => [
                                'minimum' => $feePlan->min,
                                'maximum' => $feePlan->max,
                            ],
                        ],
                    ]
                );
                $eligibilities[] = $eligibility;
            }
        }

        return $eligibilities;
    }

    private static function getEligibleFeePlans($feePlans, $purchaseAmount)
    {
        $activePlans = [];

        foreach ($feePlans as $key => $feePlan) {
            $getDataFromKey = SettingsHelper::getDataFromKey($key);

            if (
                $purchaseAmount >= $feePlan->min
                && $purchaseAmount < $feePlan->max
            ) {
                $activePlans[] = $getDataFromKey;
            }
        }

        return $activePlans;
    }
}
