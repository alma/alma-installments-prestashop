<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Helper;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\FeePlansException;
use PrestaShop\Module\Alma\Application\Helper\FeePlanHelper;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;

class FeePlanHelperTest extends TestCase
{
    public function testGetTitleWithPayNow()
    {
        $this->assertEquals('Pay Now', FeePlanHelper::getTitle(
            FeePlansMock::feePlan(1)
        ));
    }

    public function testGetTitleWithPnx()
    {
        $this->assertEquals('2-installment payments', FeePlanHelper::getTitle(
            FeePlansMock::feePlan(2)
        ));
    }

    public function testGetTitleWithDeferredDays()
    {
        $this->assertEquals('Deferred payments + 30 days', FeePlanHelper::getTitle(
            FeePlansMock::feePlan(1, 30)
        ));
    }

    public function testGetLabelWithPayNow()
    {
        $this->assertEquals('Enable pay now', FeePlanHelper::getLabel(
            FeePlansMock::feePlan(1)
        ));
    }

    public function testGetLabelWithPnx()
    {
        $this->assertEquals('Enable 2-installment payments', FeePlanHelper::getLabel(
            FeePlansMock::feePlan(2)
        ));
    }

    public function testGetLabelWithDeferredDays()
    {
        $this->assertEquals('Enable deferred payments +30 days', FeePlanHelper::getLabel(
            FeePlansMock::feePlan(1, 30, 0)
        ));
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testCheckLimitAmountPlanMaxAmountExceededThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The maximum purchase amount cannot be higher than the maximum allowed by Alma.');
        FeePlanHelper::checkLimitAmountPlan($feePlan, 10000, 200100);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     * @throws \PrestaShop\Module\Alma\Application\Exception\FeePlansException
     */
    public function testCheckLimitAmountPlanMinAmountExceededThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The minimum purchase amount cannot be lower than the minimum allowed by Alma.');
        FeePlanHelper::checkLimitAmountPlan($feePlan, 5000, 200000);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testCheckLimitAmountPlanMinAmountExceededMaxAmountThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The minimum purchase amount cannot be higher than the maximum.');
        FeePlanHelper::checkLimitAmountPlan($feePlan, 200100, 200000);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testCheckLimitAmountPlanMaxAmountExceededMinAmountThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The maximum purchase amount cannot be lower than the minimum.');
        FeePlanHelper::checkLimitAmountPlan($feePlan, 10000, 5000);
    }

    public function testCheckLimitAmountPlanWithRightAmountReturnVoid()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->assertNull(FeePlanHelper::checkLimitAmountPlan($feePlan, 10000, 200000));
    }
}
