<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Provider;

use Alma\Client\Application\DTO\EligibilityDto;
use Alma\Client\Application\Endpoint\EligibilityEndpoint;
use Alma\Client\Application\Exception\Endpoint\EligibilityEndpointException;
use Alma\Client\Domain\Entity\EligibilityList;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\EligibilityException;
use PrestaShop\Module\Alma\Application\Provider\EligibilityProvider;

class EligibilityProviderTest extends TestCase
{
    /**
     * @var \Alma\Client\Application\Endpoint\EligibilityEndpoint
     */
    private $eligibilityEndpoint;

    public function setUp(): void
    {
        $this->eligibilityEndpoint = $this->createMock(EligibilityEndpoint::class);
        $this->eligibilityProvider = new EligibilityProvider(
            $this->eligibilityEndpoint,
        );
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\EligibilityException
     */
    public function testRetrieveEligibilityThrowException()
    {
        $eligibilityDto = new EligibilityDto(4200);
        $this->eligibilityEndpoint->expects($this->once())
            ->method('getEligibilityList')
            ->with($eligibilityDto)
            ->willThrowException(new EligibilityEndpointException());
        $this->expectException(EligibilityException::class);
        $this->eligibilityProvider->retrieveEligibility($eligibilityDto);
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\EligibilityException
     */
    public function testRetrieveEligibilityReturnEligibilityList()
    {
        $eligibilityDto = new EligibilityDto(4200);
        $eligibilityList = $this->createMock(EligibilityList::class);
        $this->eligibilityEndpoint->expects($this->once())
            ->method('getEligibilityList')
            ->with($eligibilityDto)
            ->willReturn($eligibilityList);
        $this->eligibilityProvider->retrieveEligibility($eligibilityDto);
        $this->assertEquals($eligibilityList, $this->eligibilityProvider->getEligibilityList($eligibilityDto));
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\EligibilityException
     */
    public function testGetEligibilityListCallRetrieveEligibilityOnce()
    {
        $eligibilityDto = new EligibilityDto(4200);
        $eligibilityList = $this->createMock(EligibilityList::class);
        $this->eligibilityEndpoint->expects($this->once())
            ->method('getEligibilityList')
            ->with($eligibilityDto)
            ->willReturn($eligibilityList);
        $this->eligibilityProvider->getEligibilityList($eligibilityDto);
        $this->eligibilityProvider->getEligibilityList($eligibilityDto);
    }
}
