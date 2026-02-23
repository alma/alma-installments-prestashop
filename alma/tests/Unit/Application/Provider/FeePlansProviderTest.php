<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Provider;

use Alma\Client\Application\Endpoint\MerchantEndpoint;
use Alma\Client\Domain\Entity\FeePlanList;
use PHPUnit\Framework\TestCase;
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
    public function testGetFeesPlans(): void
    {
        $feePlanList = $this->createMock(FeePlanList::class);
        $this->merchantEndpoint->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);

        $this->assertInstanceOf(FeePlanList::class, $this->feePlansProvider->getFeePlans());
    }
}
