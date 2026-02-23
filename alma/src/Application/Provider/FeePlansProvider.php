<?php

namespace PrestaShop\Module\Alma\Application\Provider;

use Alma\Client\Application\Endpoint\MerchantEndpoint;
use Alma\Client\Application\Exception\Endpoint\MerchantEndpointException;
use Alma\Client\Domain\Entity\FeePlanList;
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
    public function getFeePlans(): FeePlanList
    {
        try {
            $feePlanList = $this->merchantEndpoint->getFeePlanList();
            var_dump($feePlanList);
        } catch (MerchantEndpointException $e) {
            throw new FeePlansException($e->getMessage());
        }

        return $feePlanList;
    }
}
