<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use Alma\Client\Domain\Entity\FeePlanList;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\FeePlansException;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;
use PrestaShop\Module\Alma\Application\Service\FeePlansService;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;

class FeePlansServiceTest extends TestCase
{
    public function setUp(): void
    {
        $this->context = $this->createMock(\Context::class);
        $this->feePlansProvider = $this->createMock(FeePlansProvider::class);
        $this->feePlansService = new FeePlansService(
            $this->context,
            $this->feePlansProvider
        );
    }

    public function testFeePlansTabsExpectExceptionReturnEmptyArray()
    {
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlansAllowed')
            ->willThrowException(new FeePlansException());

        $this->assertEquals([], $this->feePlansService->feePlansTabs());
    }

    public function testFeePlansTabsWithP2And3And4X()
    {
        $expected = array_merge(
            FeePlansMock::feePlansTabsExpected(2),
            FeePlansMock::feePlansTabsExpected(3, true),
            FeePlansMock::feePlansTabsExpected(4),
        );

        $feePlan2X = FeePlansMock::feePlan(2);

        $feePlan3X = FeePlansMock::feePlan(3);

        $feePlan4X = FeePlansMock::feePlan(4);

        $feePlanList = new FeePlanList([$feePlan2X, $feePlan3X, $feePlan4X]);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlansAllowed')
            ->willReturn($feePlanList);
        $this->assertEquals($expected, $this->feePlansService->feePlansTabs());
    }

    public function testFeePlansFieldsExpectExceptionReturnEmptyArray()
    {
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlansAllowed')
            ->willThrowException(new FeePlansException());

        $this->assertEquals([], $this->feePlansService->feePlansFields());
    }

    public function testFeePlansFieldsWithP3X()
    {
        $expected = FeePlansMock::feePlanFieldsExpected(3);

        $feePlan3X = FeePlansMock::feePlan(3);

        $feePlanList = new FeePlanList([$feePlan3X]);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlansAllowed')
            ->willReturn($feePlanList);

        $this->assertEquals($expected, $this->feePlansService->feePlansFields());
    }

    public function testFieldsValue()
    {
        $expected = FeePlansMock::feePlanFieldsValueExpected(3);

        $feePlan3X = FeePlansMock::feePlan(3);

        $feePlanList = new FeePlanList([$feePlan3X]);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlansAllowed')
            ->willReturn($feePlanList);

        $this->assertEquals($expected, $this->feePlansService->fieldsValue());
    }
}
