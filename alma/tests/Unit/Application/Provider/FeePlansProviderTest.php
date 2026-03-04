<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Provider;

use Alma\Client\Application\Endpoint\MerchantEndpoint;
use Alma\Client\Application\Exception\Endpoint\MerchantEndpointException;
use Alma\Client\Domain\Entity\FeePlanList;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;

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
        $feePlanListFiltered = $this->createMock(FeePlanList::class);
        $feePlanList = $this->createMock(FeePlanList::class);
        $feePlanList->expects($this->once())
            ->method('filterAllowed')
            ->willReturn($feePlanListFiltered);

        $this->merchantEndpoint->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);

        $this->assertInstanceOf(FeePlanList::class, $this->feePlansProvider->getFeePlansAllowed());
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetFeesPlansAllowedReturnFeePlanListOrdered(): void
    {
        $feePlanPayNow = FeePlansMock::feePlan(1);
        $feePlan30D = FeePlansMock::feePlan(1, 30);
        $feePlanP2x = FeePlansMock::feePlan(2);
        $feePlanP6x = FeePlansMock::feePlan(6);
        $expectedFeePlanListOrdered = new FeePlanList();
        $expectedFeePlanListOrdered->add($feePlanPayNow);
        $expectedFeePlanListOrdered->add($feePlanP2x);
        $expectedFeePlanListOrdered->add($feePlanP6x);
        $expectedFeePlanListOrdered->add($feePlan30D);
        $feePlanList = new FeePlanList();
        $feePlanList->add($feePlanPayNow);
        $feePlanList->add($feePlan30D);
        $feePlanList->add($feePlanP2x);
        $feePlanList->add($feePlanP6x);

        $this->merchantEndpoint->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);

        $this->assertEquals($expectedFeePlanListOrdered, $this->feePlansProvider->getFeePlansAllowed());
    }
}
