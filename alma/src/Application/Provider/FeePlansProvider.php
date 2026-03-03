<?php

namespace PrestaShop\Module\Alma\Application\Provider;

use Alma\Client\Application\Endpoint\MerchantEndpoint;
use Alma\Client\Application\Exception\Endpoint\MerchantEndpointException;
use Alma\Client\Domain\Entity\FeePlan;
use Alma\Client\Domain\Entity\FeePlanList;
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
    public function getFeePlansAllowed(): FeePlanListInterface
    {
        try {
            $feePlanList = $this->merchantEndpoint->getFeePlanList(FeePlan::KIND_GENERAL, 'all', true)->filterAllowed();
        } catch (MerchantEndpointException $e) {
            // TODO : Add Log here
            $feePlanList = new FeePlanList();
        }

        return $feePlanList;
    }
}
