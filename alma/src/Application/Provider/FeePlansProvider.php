<?php

namespace PrestaShop\Module\Alma\Application\Provider;

use Alma\Client\Application\Endpoint\MerchantEndpoint;
use Alma\Client\Application\Exception\Endpoint\MerchantEndpointException;
use Alma\Client\Domain\Entity\FeePlan;
use Alma\Client\Domain\Entity\FeePlanList;
use Alma\Client\Domain\ValueObject\PaymentMethod;
use Alma\Plugin\Application\Port\FeePlanProviderInterface;
use Alma\Plugin\Infrastructure\Adapter\FeePlanListInterface;

class FeePlansProvider implements FeePlanProviderInterface
{
    /**
     * @var \Alma\Client\Application\Endpoint\MerchantEndpoint
     */
    private MerchantEndpoint $merchantEndpoint;
    /**
     * @var \Alma\Plugin\Infrastructure\Adapter\FeePlanListInterface
     */
    private FeePlanListInterface $feePlanList;

    public function __construct(
        MerchantEndpoint $merchantEndpoint
    ) {
        $this->merchantEndpoint = $merchantEndpoint;
    }

    /**
     * @param bool $forceRefresh
     * @return \Alma\Client\Domain\Entity\FeePlanList
     */
    public function getFeePlanList(bool $forceRefresh = false): FeePlanList
    {
        if (!isset($this->feePlanList) || $forceRefresh) {
            $this->feePlanList = $this->getFeePlansAllowed();
        }

        return $this->feePlanList;
    }

    /**
     * @return \Alma\Plugin\Infrastructure\Adapter\FeePlanListInterface
     */
    private function getFeePlansAllowed(): FeePlanListInterface
    {
        try {
            $feePlanList = $this->merchantEndpoint->getFeePlanList(FeePlan::KIND_GENERAL, 'all', true)->filterAllowed();
        } catch (MerchantEndpointException $e) {
            // TODO : Add Log here
            $feePlanList = new FeePlanList();
        }

        return $this->orderFeePlanList($feePlanList);
    }

    /**
     * Order the fee plan list by payment method.
     * Pay Now, PNX, Credit, Deferred Plans.
     * TODO : I think we should move this logic to the FeePlanList class in the PHP-Client
     *
     * @param FeePlanList $feePlanList
     * @return FeePlanList
     */
    private function orderFeePlanList(FeePlanList $feePlanList): FeePlanList
    {
        $orderedFeePlanList = new FeePlanList();
        $orderedMethods = [
            PaymentMethod::PAY_NOW,
            PaymentMethod::PNX,
            PaymentMethod::CREDIT,
            PaymentMethod::PAY_LATER,
        ];

        foreach ($orderedMethods as $method) {
            foreach ($feePlanList->filterFeePlanList([$method]) as $plan) {
                $orderedFeePlanList->add($plan);
            }
        }

        return $orderedFeePlanList;
    }
}
