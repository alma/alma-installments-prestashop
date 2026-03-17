<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Provider;

use Alma\Client\Application\Endpoint\MerchantEndpoint;
use Alma\Client\Application\Exception\Endpoint\MerchantEndpointException;
use Alma\Client\Domain\Entity\FeePlanList;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;

class FeePlansProviderTest extends TestCase
{
    /**
     * @var \PrestaShop\Module\Alma\Application\Provider\FeePlansProvider
     */
    private FeePlansProvider $feePlansProvider;

    public function setUp(): void
    {
        $this->merchantEndpoint = $this->createMock(MerchantEndpoint::class);
        $this->feePlansProvider = new FeePlansProvider(
            $this->merchantEndpoint,
        );
    }

    public function testGetFeePlanListWithoutForceRefreshReturnsCachedValue(): void
    {
        $feePlanList = new FeePlanList();

        $this->merchantEndpoint->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);

        $result1 = $this->feePlansProvider->getFeePlanList();

        $result2 = $this->feePlansProvider->getFeePlanList(false);

        $this->assertSame($result1, $result2);
    }

    public function testGetFeePlanListWithForceRefreshCallsEndpoint(): void
    {
        $feePlanList = new FeePlanList();

        $this->merchantEndpoint->expects($this->exactly(2))
            ->method('getFeePlanList')
            ->willReturn($feePlanList);

        $this->feePlansProvider->getFeePlanList();

        $this->feePlansProvider->getFeePlanList(true);
    }

    public function testGetFeesPlansAllowedExpectExceptionReturnFeePlanListEmpty(): void
    {
        $this->merchantEndpoint->expects($this->once())
            ->method('getFeePlanList')
            ->willThrowException(new MerchantEndpointException());

        $this->assertEquals(new FeePlanList(), $this->feePlansProvider->getFeePlanList());
    }
}
