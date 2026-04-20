<?php

namespace PrestaShop\Module\Alma\Application\Provider;

use Alma\Client\Application\DTO\EligibilityDto;
use Alma\Client\Application\Endpoint\EligibilityEndpoint;
use Alma\Client\Application\Exception\Endpoint\EligibilityEndpointException;
use Alma\Client\Domain\Entity\EligibilityList;
use Alma\Plugin\Application\Port\EligibilityProviderInterface;
use PrestaShop\Module\Alma\Application\Exception\EligibilityException;

class EligibilityProvider implements EligibilityProviderInterface
{
    /**
     * @var \Alma\Client\Application\Endpoint\EligibilityEndpoint
     */
    private EligibilityEndpoint $eligibilityEndpoint;
    /**
     * @var \Alma\Client\Domain\Entity\EligibilityList
     */
    private EligibilityList $eligibilityList;

    public function __construct(EligibilityEndpoint $eligibilityEndpoint)
    {
        $this->eligibilityEndpoint = $eligibilityEndpoint;
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\EligibilityException
     * TODO: We can make this method private and call it from getEligibilityList
     */
    public function retrieveEligibility(EligibilityDto $eligibilityDto): void
    {
        try {
            $this->eligibilityList = $this->eligibilityEndpoint->getEligibilityList($eligibilityDto);
        } catch (EligibilityEndpointException $e) {
            // TODO: Add log
            throw new EligibilityException('Error retrieving eligibility', 0, $e);
        }
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\EligibilityException
     */
    public function getEligibilityList(EligibilityDto $eligibilityDto): EligibilityList
    {
        if (!isset($this->eligibilityList)) {
            $this->retrieveEligibility($eligibilityDto);
        }

        return $this->eligibilityList;
    }
}
