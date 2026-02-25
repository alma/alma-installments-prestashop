<?php

namespace PrestaShop\Module\Alma\Application\Provider;

use Alma\Client\Application\Endpoint\MerchantEndpoint;
use Alma\Client\Application\Exception\Endpoint\MerchantEndpointException;
use Alma\Client\Domain\Entity\FeePlan;
use Alma\Plugin\Infrastructure\Adapter\FeePlanListInterface;
use PrestaShop\Module\Alma\Application\Exception\FeePlansException;

class FeePlansProvider
{
    /**
     * @var \Alma\Client\Application\Endpoint\MerchantEndpoint
     */
    private MerchantEndpoint $merchantEndpoint;

    public function __construct(
        MerchantEndpoint $merchantEndpoint
    ) {
        $this->merchantEndpoint = $merchantEndpoint;
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\FeePlansException
     */
    public function getFeePlansAllowed(): FeePlanListInterface
    {
        try {
            // TODO : By default we don't get the defered. Why ?
            $feePlanList = $this->merchantEndpoint->getFeePlanList(FeePlan::KIND_GENERAL, 'all', true)->filterAllowed();
        } catch (MerchantEndpointException $e) {
            throw new FeePlansException($e->getMessage());
        }

        return $feePlanList;
    }
}
