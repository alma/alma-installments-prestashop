<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Alma\Client\Application\DTO\EligibilityDto;
use Alma\Client\Application\DTO\EligibilityQueryDto;
use Alma\Client\Domain\Entity\EligibilityList;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use PrestaShop\Module\Alma\Application\Exception\EligibilityException;
use PrestaShop\Module\Alma\Application\Helper\FeePlanHelper;
use PrestaShop\Module\Alma\Application\Helper\PriceHelper;
use PrestaShop\Module\Alma\Application\Provider\EligibilityProvider;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class EligibilityService
{
    /**
     * @var EligibilityProvider
     */
    private EligibilityProvider $eligibilityProvider;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;

    public function __construct(
        EligibilityProvider $eligibilityProvider,
        ConfigurationRepository $configurationRepository
    ) {
        $this->eligibilityProvider = $eligibilityProvider;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\EligibilityException
     */
    public function getLocalEligibilityForCheckout(\Cart $cart): array
    {
        try {
            $purchaseAmount = PriceHelper::priceToCent($cart->getOrderTotal());
            $eligibilityList = [
                'purchase_amount' => $purchaseAmount,
                'queries' => [],
            ];
        } catch (\Exception $e) {
            throw new EligibilityException('Error calculating purchase amount: ' . $e->getMessage(), 0, $e);
        }
        $feePlanFromDb = $this->configurationRepository->getFeePlanList();
        foreach ($feePlanFromDb as $planKey => $feePlan) {
            if ($feePlan['state'] === '1' && $feePlan['min_amount'] <= $purchaseAmount && $feePlan['max_amount'] >= $purchaseAmount) {
                $eligibilityList['queries'][] = [
                    'installments_count' => FeePlanHelper::getPlanFromPlanKey($planKey)['installments_count'],
                    'deferred_days' => FeePlanHelper::getPlanFromPlanKey($planKey)['deferred_days'],
                    'deferred_months' => FeePlanHelper::getPlanFromPlanKey($planKey)['deferred_months'],
                ];
            }
        }

        if (empty($eligibilityList['queries'])) {
            throw new EligibilityException('No eligible fee plans found from local eligibility');
        }

        return $eligibilityList;
    }

    /**
     * @param \Cart $cart
     * @return \Alma\Client\Domain\Entity\EligibilityList
     */
    public function getEligibilityForCheckout(\Cart $cart): EligibilityList
    {
        try {
            $localEligibility = $this->getLocalEligibilityForCheckout($cart);
            $purchaseAmount = $localEligibility['purchase_amount'];
            $eligibilityDto = new EligibilityDto($purchaseAmount);

            foreach ($localEligibility['queries'] as $eligibilityQuery) {
                $eligibilityQueryDto = new EligibilityQueryDto($eligibilityQuery['installments_count']);
                $eligibilityQueryDto->setDeferredDays($eligibilityQuery['deferred_days']);
                $eligibilityQueryDto->setDeferredMonths($eligibilityQuery['deferred_months']);
                $eligibilityDto->addQuery($eligibilityQueryDto);
            }

            return $this->eligibilityProvider->getEligibilityList($eligibilityDto);
        } catch (EligibilityException $e) {
            // TODO: Add log
            return new EligibilityList();
        }
    }
}
