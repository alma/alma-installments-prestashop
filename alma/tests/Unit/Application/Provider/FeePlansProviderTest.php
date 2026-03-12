<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Provider;

use Alma\Client\Application\Endpoint\MerchantEndpoint;
use Alma\Client\Application\Exception\Endpoint\MerchantEndpointException;
use Alma\Client\Domain\Entity\FeePlanList;
use Alma\Plugin\Infrastructure\Adapter\FeePlanListInterface;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\FeePlansException;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;

class FeePlansProviderTest extends TestCase
{
    public function setUp(): void
    {
        $this->merchantEndpoint = $this->createMock(MerchantEndpoint::class);
        $this->feePlansProvider = new FeePlansProvider(
            $this->merchantEndpoint,
        );
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\FeePlansException
     */
    public function testGetFeesPlansAllowedExpectException(): void
    {
        $this->merchantEndpoint->expects($this->once())
            ->method('getFeePlanList')
            ->willThrowException(new MerchantEndpointException());
        $this->expectException(FeePlansException::class);
        $this->feePlansProvider->getFeePlansAllowed();
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\FeePlansException
     */
    public function testGetFeesPlansAllowedReturnFeePlanList(): void
    {
        $feePlanListFiltered = $this->createMock(FeePlanListInterface::class);
        $feePlanList = $this->createMock(FeePlanList::class);
        $feePlanList->expects($this->once())
            ->method('filterAllowed')
            ->willReturn($feePlanListFiltered);

        $this->merchantEndpoint->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);

        $this->assertInstanceOf(FeePlanListInterface::class, $this->feePlansProvider->getFeePlansAllowed());
    }
}
