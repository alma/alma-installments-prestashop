<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Provider;

use Alma\Client\Application\Endpoint\MerchantEndpoint;
use Alma\Client\Application\Exception\Endpoint\MerchantEndpointException;
use Alma\Client\Domain\Entity\FeePlanList;
use Alma\Plugin\Infrastructure\Adapter\FeePlanListInterface;
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

        $this->assertEquals(new FeePlanList(), $this->feePlansProvider->getFeePlansAllowed());
    }

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
